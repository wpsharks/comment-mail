<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin         $plugin Plugin class.
 * @var Template       $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string         $site_header Parsed site header template.
 * @var string         $site_footer Parsed site footer template.
 *
 * @var string         $sub_key Key granting access to summary; if applicable.
 *
 *    Note that a `$sub_key` is only present if the summary is accessed w/ a key.
 *    The summary is allowed to be accessed w/o a key also, and in this case
 *    the summary for the current user (and/or the current email cookie)
 *    is display instead. Therefore, this value is mostly irrelevant
 *    for templates — only provided for the sake of being thorough.
 *
 * @var string         $sub_email Primary email address in this summary.
 *
 *    Note that we may also display a summary of any comment subscriptions
 *    that are indirectly related to this email address, but still belong to the
 *    current user. e.g. if the `$sub_email` has been associated with one or more user IDs
 *    within WordPress, subscriptions for those user IDs will be summarized also.
 *    See `$sub_user_ids` for access to the array of associated WP user IDs.
 *
 * @var integer[]      $sub_user_ids An array of any WP user IDs associated w/ the email address.
 *    See also `$sub_user_id_emails` for access to the array of all emails derived from this list of WP user IDs.
 *
 * @var string[]       $sub_user_id_emails An array of all emails that belong to this user; based on `$sub_email`
 *    and also on the array of `$sub_user_ids`. This is a complete list of all emails displayed by the summary.
 *    See also: `$sub_emails`; which is a simpler/cleaner alias for this variable (same thing).
 *
 * @var string[]       $sub_emails A simpler/cleaner alias for `$sub_user_id_emails`; same exact thing.
 *    See also: <https://github.com/websharks/comment-mail/blob/000000-dev/assets/sma-diagram.png> for a diagram
 *    that helps to clarify how this works; i.e. how a single key can be associated w/ multiple emails.
 *
 * @var \stdClass      $query_vars Nav/query vars; consisting of: `current_page`, `per_page`, `post_id`, `status`.
 *    Note that `post_id` will be `NULL` when there is no specific post ID filter applied to the list of `$subs`.
 *    Note that `status` will be empty when there is no specific status filter applied to the list of `$subs`.
 *
 * @var \stdClass[]    $subs An array of all subscriptions to display as part of the summary on this `$query_vars->current_page`.
 *    Note that all query vars/filters/etc. will have already been applied; a template simply needs to iterate and display a table row for each of these.
 *    Subscriptions are ordered by `post_id` ASC, `comment_id` ASC, `email` ASC, and finally by `status` ASC.
 *    Note: this array will be empty if there are any `$error_codes`.
 *
 * @var \stdClass|null $pagination_vars Pagination vars; consisting of: `current_page`, `per_page`, `total_subs`, `total_pages`.
 *    Note that `current_page` and `per_page` are simply duplicated here for convenience; same as you'll find in `$query_vars`.
 *    The `per_page` value is configured by plugin options from the dashboard; it cannot be modified here; these are read-only.
 *    Note: this will be `NULL` if there are any `$error_codes`.
 *
 * @var boolean        $processing Are we (i.e. did we) process an action? e.g. a deletion from the list perhaps.
 *
 * @var array          $processing_errors An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_error_codes An array of any/all processing error codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_errors_html An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $processing_successes An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_success_codes An array of any/all processing success codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_successes_html An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $error_codes An array of any/all major error codes; excluding processing error codes.
 *    Note that you should NOT display the summary at all, if any major error exist here.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo // Sets document <title> tag via `%%title%%` replacement code in header.
str_replace('%%title%%', __('My Comment Subscriptions', 'comment-mail'), $site_header); ?>

    <div class="manage-summary">

        <?php if ($error_codes) : // Any major errors? ?>

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
                                    echo __('Missing subscription key; unable to display summary.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'invalid_sub_key':
                                    echo __('Invalid subscription key; unable to display summary.', 'comment-mail');
                                    break; // Break switch handler.

                                default: // Anything else that is unexpected/unknown at this time.
                                    echo __('Unknown error; unable to display summary. Sorry!', 'comment-mail');
                            } ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php else : // Display summary; there are no major errors. ?>

            <?php
            /*
             * Here we define a few more variables of our own.
             * All based on what the template makes available to us;
             * ~ as documented at the top of this file.
             */
            // Site home page URL; i.e. back to main site.
            $home_url = home_url('/'); // Multisite compatible.

            // Subscription creation URL; user may create a new subscription.
            $sub_new_url = $plugin->utils_url->subManageSubNewUrl(null, true);

            // Unsubscribes (deletes) ALL subscriptions in the summary, at the same time.
            $sub_unsubscribe_all_url = $plugin->utils_url->subUnsubscribeAllUrl($sub_email);
            ?>

            <?php if ($processing && $processing_errors) : // Any processing errors? ?>

                <div class="alert alert-danger">
                    <h4>
                        <?php echo __('Please review the following error(s):', 'comment-mail'); ?>
                    </h4>
                    <ul class="list-unstyled">
                        <?php foreach ($processing_errors_html as $_error_code => $_error_html) : ?>
                            <li>
                                <i class="fa fa-warning fa-fw" aria-hidden="true"></i> <?php echo $_error_html; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>

            <?php if ($processing && $processing_successes) : // Any processing successes? ?>

                <div class="alert alert-success">
                    <h4>
                        <?php echo __('Submission accepted; thank you :-)', 'comment-mail'); ?>
                    </h4>
                    <ul class="list-unstyled">
                        <?php foreach ($processing_successes_html as $_success_code => $_success_html) : ?>
                            <li>
                                <i class="fa fa-check fa-fw" aria-hidden="true"></i> <?php echo $_success_html; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>

            <h2 style="margin-top:0;">
                <?php // Disable this functionality for now; see http://bit.ly/1OYd4ie ?>
                <?php // @todo Remove completely, or reconsider, Add New Subscription from front-end ?>
                <?php /*
                <a href="<?php echo esc_attr($sub_new_url); ?>" title="<?php echo __('Add New Subscription', 'comment-mail'); ?>">
                    <i class="fa fa-plus-square text-success pull-right" aria-hidden="true" style="margin-left:.5em;"></i>
                </a>
                */?>
                <a href="<?php echo esc_attr($sub_unsubscribe_all_url); ?>"
                   data-action="<?php echo esc_attr($sub_unsubscribe_all_url); ?>"
                   data-confirmation="<?php echo __('Delete (unsubscribe) ALL subscriptions associated with your email address? Are you absolutely sure?', 'comment-mail'); ?>"
                   title="<?php echo __('Delete (unsubscribe) ALL subscriptions associated with your email address?', 'comment-mail'); ?>">
                    <i class="fa fa-times-circle text-danger pull-right" aria-hidden="true"></i>
                </a>
                <?php echo __('My Comment Subscriptions', 'comment-mail'); ?><br />
                <em style="margin-left:.5em;">
                    <small>&lt;<?php echo esc_html(implode('&gt;, &lt;', array_slice($sub_emails, 0, 100))); ?>&gt;</small>
                </em>
            </h2>

            <hr />

            <?php if (empty($subs)) : ?>
                <h4>
                    <?php echo sprintf(__('No subscriptions at this time. You may <a href="%1$s">click here</a> to create one <i class="fa fa-smile-o" aria-hidden="true"></i>', 'comment-mail'), esc_attr($sub_new_url)); ?>
                </h4>
            <?php endif; ?>

            <div class="subs-table table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="manage-summary-subscr-email">
                                <?php echo __('Email Address', 'comment-mail'); ?>
                            </th>
                            <th class="manage-summary-subscr-to">
                                <?php echo __('Subscribed To', 'comment-mail'); ?>
                            </th>
                            <th class="manage-summary-subscr-type">
                                <?php echo __('Type', 'comment-mail'); ?>
                            </th>
                            <th class="manage-summary-subscr-status">
                                <?php echo __('Status', 'comment-mail'); ?>
                            </th>
                            <th class="manage-summary-subscr-delivery-op">
                                <?php echo __('Deliver', 'comment-mail'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subs as $_sub) : ?>
                            <tr>
                                <td>
                                    <?php
                                    /*
                                     * Here we define a few more variables of our own, for each subscription we iterate.
                                     * All of this data is based on what's already provided in the array of `$subs`;
                                     *    ~ as documented at the top of this file.
                                     */
                                    // Post they are subscribed to.
                                    //    Note: you CANNOT rely on the post still existing!
                                    //    Always be sure to provide a fallback w/ just the `$_sub->post_id`
                                    //    in case you deleted this post since they subscribed to it.
                                    $_sub_post              = get_post($_sub->post_id);
                                    $_sub_post_url          = $_sub_post ? get_permalink($_sub_post->ID) : '';
                                    $_sub_post_comments_url = $_sub_post ? get_comments_link($_sub_post->ID) : '';
                                    $_sub_post_title_clip   = $_sub_post ? $plugin->utils_string->clip($_sub_post->post_title, 45) : '';
                                    $_sub_post_type         = $_sub_post ? get_post_type_object($_sub_post->post_type) : null;
                                    $_sub_post_type_label   = $_sub_post_type ? $_sub_post_type->labels->singular_name : '';

                                    // Comment they are subscribed to; if applicable.
                                    //    Note: you CANNOT rely on the comment still existing!
                                    //    Always be sure to provide a fallback w/ just the `$_sub->comment_id`
                                    //    in case you deleted this comment since they subscribed to it.
                                    $_sub_comment            = $_sub->comment_id ? get_comment($_sub->comment_id) : null;
                                    $_sub_comment_url        = $_sub_comment ? get_comment_link($_sub_comment->comment_ID) : '';
                                    $_sub_comment_date_utc   = $_sub_comment ? $plugin->utils_date->i18nUtc('M jS, Y @ g:i a T', strtotime($_sub_comment->comment_date_gmt)) : '';
                                    $_sub_comment_date_local = $_sub_comment ? $plugin->utils_date->i18n('M jS, Y @ g:i a T', strtotime($_sub_comment->comment_date_gmt)) : '';
                                    $_sub_comment_time_ago   = $_sub_comment ? $plugin->utils_date->approxTimeDifference(strtotime($_sub_comment->comment_date_gmt)) : '';

                                    // URLs that allow for actions to be performed against the subscription.
                                    $_sub_edit_url   = $plugin->utils_url->subManageSubEditUrl($_sub->key, null, true);
                                    $_sub_delete_url = $plugin->utils_url->subManageSubDeleteUrl($_sub->key, null, true);

                                    // Type of subscription; one of `comment` or `comments`.
                                    $_sub_type = $_sub->comment_id ? 'comment' : 'comments';

                                    $_sub_name_email_args = ['anchor_to' => $_sub_edit_url];
                                    // This is the subscriber's `"name" <email>` w/ HTML markup enhancements.
                                    $_sub_name_email_markup = $plugin->utils_markup->nameEmail($_sub->fname.' '.$_sub->lname, $_sub->email, $_sub_name_email_args);

                                    // Subscribed to their own comment?
                                    $_subscribed_to_own_comment = $_sub_comment && in_array(strtolower($_sub_comment->comment_author_email), $sub_emails, true);
                                    ?>
                                    <i class="<?php echo esc_attr('si si-'.SLUG_TD.'-one'); ?>"></i> <?php echo $_sub_name_email_markup; ?><br />

                                    <div class="hover-links">
                                        <a href="<?php echo esc_attr($_sub_edit_url); ?>"
                                           title="<?php echo esc_attr(__('Edit Subscription', 'comment-mail')); ?>"
                                            ><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <?php echo __('Edit Subscr.', 'comment-mail'); ?></a>

                                        <span class="text-muted">|</span>

                                        <a data-action="<?php echo esc_attr($_sub_delete_url); ?>" href="<?php echo esc_attr($_sub_delete_url); ?>"
                                           data-confirmation="<?php echo esc_attr(__('Delete subscription? Are you sure?', 'comment-mail')); ?>"
                                           title="<?php echo esc_attr(__('Delete Subscription', 'comment-mail')); ?>" class="text-danger"
                                            ><?php echo __('Delete', 'comment-mail'); ?> <i class="fa fa-times-circle" aria-hidden="true"></i></a>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($_sub_post && $_sub_post_type_label) : ?>
                                        <?php echo sprintf(__('%1$s ID <a href="%2$s">#<code>%3$s</code></a> <a href="%4$s">%5$s</a>', 'comment-mail'), esc_html($_sub_post_type_label), esc_attr($_sub_post_url), esc_html($_sub_post->ID), esc_attr($_sub_post_comments_url), esc_html($_sub_post_title_clip)); ?>
                                    <?php else : // Post no longer exists for whatever reason; display post ID only in this case. ?>
                                        <?php echo sprintf(__('Post ID #<code>%1$s</code>', 'comment-mail'), esc_html($_sub->post_id)); ?>
                                    <?php endif; ?>

                                    <?php if ($_sub->comment_id) : ?><br />
                                        <i class="fa fa-level-up fa-rotate-90" aria-hidden="true"></i>

                                        <?php if ($_sub_comment) : ?>
                                            <?php if ($_subscribed_to_own_comment) : ?>
                                                <?php echo sprintf(__('Replies to <a href="%1$s">your comment</a>; ID <a href="%1$s">#<code>%2$s</code></a> posted %3$s', 'comment-mail'), esc_attr($_sub_comment_url), esc_html($_sub_comment->comment_ID), esc_html($_sub_comment_time_ago)); ?>
                                            <?php else : // It's not their own comment; i.e. it's by someone else. ?>
                                                <?php echo sprintf(__('Replies to <a href="%1$s">comment ID #<code>%2$s</code></a> posted %3$s', 'comment-mail'), esc_attr($_sub_comment_url), esc_html($_sub_comment->comment_ID), esc_html($_sub_comment_time_ago)); ?>
                                            <?php endif; ?>
                                            <?php if ($_sub_comment->comment_author) : ?>
                                                <?php echo sprintf(__('by: <a href="%1$s">%2$s</a>', 'comment-mail'), esc_attr($_sub_comment_url), esc_html($_sub_comment->comment_author)); ?>
                                            <?php endif; ?>
                                        <?php else : // Comment no longer exists for whatever reason; display comment ID only in this case. ?>
                                            <?php echo sprintf(__('Comment ID #<code>%1$s</code>', 'comment-mail'), esc_html($_sub->comment_id)); ?>
                                        <?php endif; ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($plugin->utils_i18n->subTypeLabel($_sub_type)); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($plugin->utils_i18n->statusLabel($_sub->status)); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($plugin->utils_i18n->deliverLabel($_sub->deliver)); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination_vars->total_pages > 1) : ?>
                <hr />
                <div class="row subs-pagination">
                    <div class="col-md-3 text-left">
                                <span class="label label-default" style="font-size:110%; vertical-align:bottom;">
                                    <?php echo sprintf(__('Page %1$s of %2$s', 'comment-mail'), esc_html($pagination_vars->current_page), esc_html($pagination_vars->total_pages)); ?>
                                </span>
                    </div>
                    <div class="col-md-9 text-right">
                        <nav>
                            <ul class="pagination" style="margin:0;">

                                <?php if ($pagination_vars->current_page > 1) : // Create a previous page link? ?>
                                    <?php $_prev_page_url = $plugin->utils_url->subManageSummaryUrl($sub_key, null, ['page' => $pagination_vars->current_page - 1]); ?>
                                    <li><a href="<?php echo esc_attr($_prev_page_url); ?>">&laquo;</a></li>
                                <?php else : // Not possible; this is the first page. ?>
                                    <li class="disabled"><a href="#">&laquo;</a></li>
                                <?php endif; ?>

                                <?php // Individual page links now.
                                $_max_page_links           = 5; // Max individual page links to show on each page.
                                $_page_links_start_at_page = // This is a mildly complex calculation that we can do w/ help from the plugin class.
                                    $plugin->utils_db->paginationLinksStartPage($pagination_vars->current_page, $pagination_vars->total_pages, $_max_page_links);

                                for ($_i = 1, $_page = $_page_links_start_at_page;
                                    $_i <= $_max_page_links && $_page <= $pagination_vars->total_pages; $_i++ && $_page++) :
                                    $_page_url = $plugin->utils_url->subManageSummaryUrl($sub_key, null, ['page' => $_page]); ?>

                                    <li <?php if ($_page === $pagination_vars->current_page) : ?>
                                            class="active"
                                        <?php endif; ?>
                                        >
                                        <a href="<?php echo esc_attr($_page_url); ?>"><?php echo esc_html($_page); ?></a>
                                    </li>
                                <?php // End loop.
                                endfor; ?>

                                <?php if ($pagination_vars->current_page < $pagination_vars->total_pages) : // Create a next page link? ?>
                                    <?php $_next_page_url = $plugin->utils_url->subManageSummaryUrl($sub_key, null, ['page' => $pagination_vars->current_page + 1]); ?>
                                    <li><a href="<?php echo esc_attr($_next_page_url); ?>">&raquo;</a></li>
                                <?php else : // Not possible; this is the last page. ?>
                                    <li class="disabled"><a href="#">&raquo;</a></li>
                                <?php endif; ?>

                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; // END: pagination total pages check. ?>

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

                        $('.manage-summary').find('[data-action]').on('click', function(e)
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

        <?php endif; // END: display summary when no major errors. ?>

    </div>

<?php echo $site_footer; ?>
