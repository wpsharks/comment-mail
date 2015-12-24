<?php
namespace comment_mail;
/**
 * @var plugin           $plugin       Plugin class.
 * @var template         $template     Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string           $email_header Parsed email header template.
 * @var string           $email_footer Parsed email footer template.
 *
 * @var \stdClass        $sub          Subscription object data.
 *
 * @var \WP_Post         $sub_post     Post they're subscribed to.
 *
 * @var \WP_Comment|null $sub_comment  Comment they're subcribed to; if applicable.
 *
 * @var \WP_Comment[]    $comments     An array of all WP comment objects we are notifying about.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Comment Notification(s)', 'comment-mail'), $email_header); ?>

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

// Subscriber's `"name" <email>` w/ HTML markup enhancements.
$sub_name_email_markup = $plugin->utils_markup->name_email($sub->fname.' '.$sub->lname, $sub->email);

// Subscriber's last known IP address.
$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', 'comment-mail');

// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
$sub_last_update_time_ago = $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time);

// A notification may contain one (or more) comments. Is this a digest?
$is_digest = count($comments) > 1; // `TRUE`, if more than one comment in the notification.

// Plugin is configured to allow replies via email? If so, this will be `TRUE`.
$replies_via_email_enable = $sub_post_comments_open && $plugin->options['replies_via_email_enable'];
?>
<?php echo $template->snippet(
  'message-heading.php', array(
  'is_digest'                 => $is_digest,
  'sub_comment'               => $sub_comment,
  'subscribed_to_own_comment' => $subscribed_to_own_comment,

  '[sub_post_comments_url]' => esc_attr($sub_post_comments_url),
  '[sub_post_title_clip]'   => esc_html($sub_post_title_clip),

  '[sub_comment_url]' => esc_attr($sub_comment_url),
  '[sub_comment_id]'  => esc_html($sub_comment ? $sub_comment->comment_ID : 0),
)); ?>

<?php foreach($comments as $_comment): // Comments in this notification. ?>
    <hr />
    <?php
    // Parent comment, if applicable; i.e. if this comment is a reply to another.
    $_comment_parent = $_comment->comment_parent ? get_comment($_comment->comment_parent) : null;

    // Parent comment URL, if applicable.
    $_comment_parent_url = $_comment_parent ? get_comment_link($_comment_parent->comment_ID) : '';

    // A shorter clip of the full parent comment message body; in plain text.
    $_comment_parent_clip = $_comment_parent ? $plugin->utils_markup->comment_content_clip($_comment_parent, 'notification_parent') : '';

    // A reply to their own comment?
    $_comment_reply_to_own_comment = $_comment_parent && strcasecmp($_comment_parent->comment_author_email, $sub->email) === 0;

    // URL to this comment; i.e. the one we're notifying about.
    $_comment_url = get_comment_link($_comment->comment_ID);

    // URL to the reply link for this comment
    $_comment_reply_url = get_permalink($_comment->comment_post_ID).'?replytocom='.$_comment->comment_ID.'#respond';

    // How long ago the comment was posted on the site (human readable).
    $_comment_time_ago = $plugin->utils_date->approx_time_difference(strtotime($_comment->comment_date_gmt));

    // A shorter clip of the full comment message body; in plain text.
    $_comment_clip = $plugin->utils_markup->comment_content_clip($_comment, 'notification', false);

    // Reply via email marker; if applicable. Only needed for digests, and only if replies via email are enabled currently.
    // ~ Note: This marker is not necessary for single comment notifications. A `Reply-To:` header already handles single-comment notifications.
    $_comment_rve_irt_marker = $plugin->is_pro ? $plugin->utils_rve->irt_marker($_comment->comment_post_ID, $_comment->comment_ID) : ''; // e.g. `~rve#779-84`.
    ?>
    <?php if ($_comment_parent): // This is a reply to someone? ?>

        <?php echo $template->snippet(
          'message-in-response-to.php', array(
          '[comment_parent_url]'         => esc_attr($_comment_parent_url),
          '[comment_parent_id]'          => esc_html($_comment_parent->comment_ID),
          '[comment_parent_author]'      => esc_html($_comment_parent->comment_author),
          '[comment_parent_clip]'        => esc_html($_comment_parent_clip),
          'comment_reply_to_own_comment' => $_comment_reply_to_own_comment,
        )); ?>

        <?php echo $template->snippet(
          'message-reply-from.php', array(
          '[comment_url]'      => esc_attr($_comment_url),
          '[comment_id]'       => esc_html($_comment->comment_ID),
          '[comment_time_ago]' => esc_html($_comment_time_ago),
          '[comment_author]'   => esc_html($_comment->comment_author),
          '[comment_clip]'     => esc_html($_comment_clip),
        )); ?>
        <p>
            <a href="<?php echo esc_attr($_comment_url); ?>">
                <?php echo __('Continue reading', 'comment-mail'); ?>
            </a>
            <?php if($sub_post_comments_open): ?>
                | <a href="<?php echo esc_attr($_comment_reply_url); ?>">
                    <?php if($_comment->comment_author) : ?>
                        <?php echo __('Reply to', 'comment-mail').' '.esc_html($_comment->comment_author); ?>
                    <?php else : ?>
                        <?php echo __('Reply', 'comment-mail'); ?>
                    <?php endif; ?>
                </a>
                <?php if($replies_via_email_enable): ?>
                    <?php if($is_digest): // Marker only needed in digests. ?>
                        <small><em><?php echo sprintf(__('— or reply to this email &amp; start your message with: <code>%1$s</code>', 'comment-mail'), esc_html($_comment_rve_irt_marker)); ?></em></small>
                    <?php else: // The `Reply-To:` field in the email will suffice in other cases; i.e. there is only one comment in this notification. ?>
                        <small><em><?php echo __('— or simply reply to this email', 'comment-mail'); ?></em></small>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </p>

    <?php else: // A new comment; i.e. not a reply to someone. ?>

        <?php echo $template->snippet(
          'message-comment-from.php', array(
          '[comment_url]'      => esc_attr($_comment_url),
          '[comment_id]'       => esc_html($_comment->comment_ID),
          '[comment_time_ago]' => esc_html($_comment_time_ago),
          '[comment_author]'   => esc_html($_comment->comment_author),
          '[comment_clip]'     => esc_html($_comment_clip),
				)); ?>
        <p>
            <a href="<?php echo esc_attr($_comment_url); ?>">
                <?php echo __('Continue reading', 'comment-mail'); ?>
            </a>
            <?php if($sub_post_comments_open): ?>
                | <a href="<?php echo esc_attr($_comment_reply_url); ?>">
                    <?php if($_comment->comment_author) : ?>
                        <?php echo __('Reply to', 'comment-mail').' '.esc_html($_comment->comment_author); ?>
                    <?php else : ?>
                        <?php echo __('Reply', 'comment-mail'); ?>
                    <?php endif; ?>
                </a>
                <?php if($replies_via_email_enable): ?>
                    <?php if($is_digest): // Marker only needed in digests. ?>
                        <small><em><?php echo sprintf(__('— or reply to this email &amp; start your message with: <code>%1$s</code>', 'comment-mail'), esc_html($_comment_rve_irt_marker)); ?></em></small>
                    <?php else: // The `Reply-To:` field in the email will suffice in other cases; i.e. there is only one comment in this notification. ?>
                        <small><em><?php echo __('— or simply reply to this email | ', 'comment-mail'); ?></em></small>
                        <small><strong><?php echo __('Please Note:', 'comment-mail'); ?></strong> <em><?php echo __('Your reply will be posted publicly and immediately.', 'comment-mail'); ?></em></small>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </p>

    <?php endif; ?>

<?php endforeach; ?>

<?php echo $email_footer; ?>
