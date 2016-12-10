<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin   $plugin Plugin class.
 * @var Template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string   $site_header Parsed site header template.
 * @var string   $site_footer Parsed site footer template.
 *
 * @var string   $sub_email The email address unsubscribed completely.
 *
 * @var array    $error_codes An array of any/all error codes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Unsubscribe All', 'comment-mail'), $site_header); ?>

    <div class="unsubscribe-all">

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
                                case 'missing_sub_email':
                                    echo __('Subscription email is missing; unable to unsubscribe all.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'sub_already_unsubscribed_all':
                                    echo __('Already unsubscribed all! Sorry to see you go.', 'comment-mail');
                                    break; // Break switch handler.

                                default: // Anything else that is unexpected/unknown at this time.
                                    echo __('Unknown error; unable to unsubscribe all. Sorry!', 'comment-mail').
                                         ' '.sprintf(
                                             __('Please contact &lt;%1$s&gt; for assistance.', 'comment-mail'),
                                             esc_html($plugin->options['can_spam_postmaster'])
                                         );
                            } ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php else : // Unsubscribed all successfully. ?>

            <?php
            /*
             * Here we define a few more variables of our own.
             * All based on what the template makes available to us;
             * ~ as documented at the top of this file.
             */
            // Subscription creation URL; i.e. so they can add a new subscription if they like.
            $sub_new_url = $plugin->utils_url->subManageSubNewUrl();
            ?>

            <div class="alert alert-success" style="margin:0;">
                <h4 style="margin:0;">
                    <i class="fa fa-check fa-fw" aria-hidden="true"></i> <?php echo __('Unsubscribed all successfully. Sorry to see you go!', 'comment-mail'); ?>
                </h4>
            </div>

            <h4>
                <?php echo sprintf(__('&lt;%1$s&gt; will no longer be notified about any comments/replies.', 'comment-mail'), esc_html($sub_email)); ?>
            </h4>

            <hr style="margin:0 0 1em 0;" />

            <h5 style="font-style:italic; margin:0;">
                <i class="fa fa-frown-o" aria-hidden="true"></i> <?php echo sprintf(__('Too many emails? ~ Please feel free to <a href="%1$s">add a new/different subscription</a> if you like!', 'comment-mail'), esc_attr($sub_new_url)); ?>
            </h5>

        <?php endif; // END: if unsubscribed all successfully w/ no major errors. ?>

    </div>

<?php echo $site_footer; ?>
