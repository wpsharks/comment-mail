<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin           $plugin      Plugin class.
 * @var Template         $template    Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string           $site_header Parsed site header template.
 * @var string           $site_footer Parsed site footer template.
 *
 * @var \stdClass|null   $sub         Subscription object data.
 *
 * @var \WP_Post|null    $sub_post    Post they were subscribed to.
 *    This will be `NULL` if there were any `$error_codes` during processing.
 *    This will also be `NULL` if you deleted the post before they unsubscribed.
 *
 * @var \WP_Comment|null $sub_comment Comment they were subcribed to; if applicable.
 *
 * @var array            $error_codes An array of any/all error codes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Unsubscribe', 'comment-mail'), $site_header); ?>

    <div class="unsubscribe">

        <?php if ($error_codes) : // Any processing errors? ?>

            <div class="alert alert-danger" style="margin:0;">
                <h4>
                    <?php echo __('Please review the following error(s):', 'comment-mail'); ?>
                </h4>
                <ul class="list-unstyled">
                    <?php foreach ($error_codes as $_error_code) : ?>
                        <li>
                            <i class="fa fa-warning fa-fw" aria-hidden="true"></i>
                            <?php
                            switch ($_error_code) {
                                case 'missing_sub_key':
                                    echo __('Subscription key is missing; unable to unsubscribe.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'invalid_sub_key':
                                    // echo __('Invalid subscription key; unable to unsubscribe (or already unsubscribed).', 'comment-mail');
                                    echo __('Looks like you\'ve already unsubscribed! Sorry to see you go.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'sub_already_unsubscribed':
                                    echo __('Already unsubscribed! Sorry to see you go.', 'comment-mail');
                                    break; // Break switch handler.

                                default: // Anything else that is unexpected/unknown at this time.
                                    echo __('Unknown error; unable to unsubscribe. Sorry!', 'comment-mail').
                                         ' '.sprintf(
                                             __('Please contact &lt;%1$s&gt; for assistance.', 'comment-mail'),
                                             esc_html($plugin->options['can_spam_postmaster'])
                                         );
                            } ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php else : // Unsubscribed successfully. ?>

        <?php
        /*
         * Here we define a few more variables of our own.
         * All based on what the template makes available to us;
         * ~ as documented at the top of this file.
         *
         * Note: you CANNOT rely on the post still existing!
         * Always be sure to provide a fallback w/ just the `$sub->post_id`
         *    in case you deleted this post since they subscribed to it.
         */
        // URL to comments on the post they were subscribed to.
        $sub_post_comments_url = $sub_post ? get_comments_link($sub_post->ID) : '';

        // Are comments still open on this post?
        $sub_post_comments_open = $sub_post ? comments_open($sub_post->ID) : false;

        // A shorter clip of the full post title.
        $sub_post_title_clip = $sub_post ? $plugin->utils_string->clip($sub_post->post_title, 70) : '';

        // URL to comment they were subscribed to; if applicable.
        $sub_comment_url = $sub_comment ? get_comment_link($sub_comment->comment_ID) : '';

        // They were subscribed to their own comment?
        $subscribed_to_own_comment = $sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;

        // Former subscription delivery option label; i.e. a translated display of the option value.
        $sub_deliver_label = $plugin->utils_i18n->deliverLabel($sub->deliver);

        // Subscriber's `"name" <email>` w/ HTML markup enhancements.
        $sub_name_email_markup = $plugin->utils_markup->nameEmail($sub->fname.' '.$sub->lname, $sub->email);

        // Subscriber's last known IP address.
        $sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', 'comment-mail');

        // Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
        $sub_last_update_time_ago = $plugin->utils_date->i18nUtc('M jS, Y @ g:i a T', $sub->last_update_time);

        // Unsubscribes (deletes) ALL subscriptions associated w/ their email address.
        $sub_unsubscribe_all_url = $plugin->utils_url->subUnsubscribeAllUrl($sub->email);

        // Subscription creation URL; i.e. so they can add a new subscription if they like.
        $sub_new_url = $plugin->utils_url->subManageSubNewUrl();
        ?>

        <?php echo $template->snippet(
            'unsubscribed.php',
            [
                'sub_post'                  => $sub_post,
                'sub_comment'               => $sub_comment,
                'subscribed_to_own_comment' => $subscribed_to_own_comment,

                '[sub_fname]' => esc_html($sub->fname),
                '[sub_email]' => esc_html($sub->email),

                '[sub_post_comments_url]' => esc_attr($sub_post_comments_url),
                '[sub_post_title_clip]'   => esc_html($sub_post_title_clip),
                '[sub_post_id]'           => esc_html($sub_post ? $sub_post->ID : $sub->post_id),

                '[sub_comment_url]' => esc_attr($sub_comment_url),
                '[sub_comment_id]'  => esc_html($sub_comment ? $sub_comment->comment_ID : 0),

                '[sub_unsubscribe_all_url]' => esc_attr($sub_unsubscribe_all_url),
                '[sub_new_url]'             => esc_attr($sub_new_url),
            ]
        ); ?>

        <?php
        /* Javascript used in this template.
         ------------------------------------------------------------------------------------------------------------------------ */
        ?>
            <script type="text/javascript">
                (function($) // Primary closure w/ jQuery; strict standards.
                {
                    'use strict'; // Strict standards enable.

                    var plugin = {}, $window = $(window), $document = $(document),

                        namespace = '<?php echo $plugin->utils_string->escJsSq(GLOBAL_NS); ?>',
                        namespaceSlug = '<?php echo $plugin->utils_string->escJsSq(str_replace('_', '-', GLOBAL_NS)); ?>',

                        ajaxEndpoint = '<?php echo $plugin->utils_string->escJsSq(home_url('/')); ?>',
                        pluginUrl = '<?php echo $plugin->utils_string->escJsSq(rtrim($plugin->utils_url->to('/'), '/')); ?>';

                    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

                    plugin.onReady = function() // On DOM ready handler.
                    {
                        /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

                        $('.unsubscribe').find('[data-action]').on('click', function(e)
                        {
                            e.preventDefault(), e.stopImmediatePropagation();

                            var $this = $(this), data = $this.data();
                            if(typeof data.confirmation !== 'string' || confirm(data.confirmation))
                                location.href = data.action;
                        });
                        /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
                    };
                    $document.ready(plugin.onReady); // On DOM ready handler.
                })(jQuery);
            </script>
            <?php /* ---------------------------------------------------------------------------------------------------------- */ ?>

        <?php endif; // END: if unsubscribed successfully w/ no major errors. ?>

    </div>

<?php echo $site_footer; ?>
