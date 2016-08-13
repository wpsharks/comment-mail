<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin           $plugin              Plugin class.
 * @var Template         $template            Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string           $email_header        Parsed email header template.
 * @var string           $email_footer        Parsed email footer template.
 *
 * @var \stdClass        $sub                 Subscription object data.
 *
 * @var \WP_Post         $sub_post            Post they're subscribed to.
 *
 * @var boolean          $process_list_server Process list server?
 *
 * @var \WP_Comment|null $sub_comment         Comment they're subcribed to; if applicable.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Confirmation Request', 'comment-mail'), $email_header); ?>

<?php
/*
 * Here we define a few more variables of our own.
 * All based on what the template makes available to us;
 * ~ as documented at the top of this file.
 */
// URL to comments on the post they're subscribed to.
$sub_post_comments_url = get_comments_link($sub_post->ID);

// Are comments still open on this post?
$sub_post_comments_open = comments_open($sub_post->ID);

// A shorter clip of the full post title.
$sub_post_title_clip = $plugin->utils_string->clip($sub_post->post_title, 70);

// URL to comment they're subscribed to; if applicable.
$sub_comment_url = $sub_comment ? get_comment_link($sub_comment->comment_ID) : '';

// Subscribed to their own comment?
$subscribed_to_own_comment = $sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;

// Confirmation URL; they need to click this.
$sub_confirm_url = $plugin->utils_url->subConfirmUrl($sub->key, $process_list_server);

// Subscriber's `"name" <email>` w/ HTML markup enhancements.
$sub_name_email_markup = $plugin->utils_markup->nameEmail($sub->fname.' '.$sub->lname, $sub->email);

// Subscriber's last known IP address.
$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', 'comment-mail');

// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
$sub_last_update_time_ago = $plugin->utils_date->i18nUtc('M jS, Y @ g:i a T', $sub->last_update_time);
?>

    <h2 style="margin-top:0; font-weight:normal;">

        <?php if ($sub->fname) : // We can call them by name? ?>
            <?php echo esc_html(sprintf(__('%1$s, please', 'comment-mail'), esc_html($sub->fname))); ?>
        <?php else : echo __('Please', 'comment-mail'); ?>
        <?php endif; ?>

        <a href="<?php echo esc_attr($sub_confirm_url); ?>">
            <strong><?php echo __('click here to confirm', 'comment-mail'); ?></strong>
        </a>

        <?php echo __('your subscription.', 'comment-mail'); ?>

    </h2>

    <hr />

    <p>

        <?php if ($sub_comment) : // Subscribing to a specific comment? ?>

            <?php if ($subscribed_to_own_comment) : ?>
                <?php echo sprintf(__('You are receiving this email because you asked to be notified about replies to <a href="%1$s">your comment</a>; on:', 'comment-mail'), esc_html($sub_comment_url)); ?>
            <?php else : // The comment was not authored by this subscriber; i.e. it's not their own. ?>
                <?php echo sprintf(__('You are receiving this email because you asked to be notified about replies to <a href="%1$s">this comment</a>; on:', 'comment-mail'), esc_html($sub_comment_url)); ?>
            <?php endif; ?>

        <?php else : // All comments/replies on this post. ?>
            <?php echo __('You are receiving this email because you asked to be notified about all comments/replies to:', 'comment-mail'); ?>
        <?php endif; ?>

    </p>

    <p>

        <span style="font-size:120%;">
            <?php echo esc_html($sub_post_title_clip); ?>
        </span>

    </p>

    <p>

        <?php if ($sub_comment) : // A specific comment? ?>
            <a href="<?php echo esc_attr($sub_comment_url); ?>">
                <?php echo esc_html($sub_comment_url); ?>
            </a>
        <?php else : // Subscribing to all comments/replies. ?>
            <a href="<?php echo esc_attr($sub_post_comments_url); ?>">
                <?php echo esc_html($sub_post_comments_url); ?>
            </a>
        <?php endif; ?>

    </p>

    <p style="color:#888888; font-style:italic;">
        <?php echo __('Note: if you did not make this request, please ignore this email. You will only be subscribed if you confirm.', 'comment-mail'); ?>
        <?php echo sprintf(__('This subscription was requested by %1$s on %2$s.', 'comment-mail'), $sub_name_email_markup, esc_html($sub_last_update_time_ago)); ?>
        <?php echo __('If you need to report any continued abuse, please use the contact info at the bottom of this email.', 'comment-mail'); ?>
    </p>

<?php echo $email_footer; ?>
