<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin           $plugin      Plugin class.
 * @var Template         $template    Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass        $sub         Subscription object data.
 *
 * @var \WP_Post         $sub_post    Post they're subscribed to.
 *
 * @var \WP_Comment|null $sub_comment Comment they're subcribed to; if applicable.
 *
 * -------------------------------------------------------------------
 * @note Extra whitespace in subject templates is stripped automatically.
 * That's why this template is able to break things down into multiple lines.
 * In the end, the email will contain a one-line subject of course.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo __('Confirm subscription', 'comment-mail'); ?>

<?php
/*
 * Here we define a few more variables of our own.
 * All based on what the template makes available to us;
 * ~ as documented at the top of this file.
 */
// A shorter clip of the full post title.
$sub_post_title_clip = $plugin->utils_string->clip($sub_post->post_title, 30);

// Subscribed to their own comment?
$subscribed_to_own_comment = $sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;
?>
<?php if ($sub_comment) : // Subscribed to a specific comment? ?>

    <?php if ($subscribed_to_own_comment) : ?>
        <?php echo sprintf(__('to your comment on: %1$s', 'comment-mail'), $sub_post_title_clip); ?>
    <?php else : // The comment was not authored by this subscriber; i.e. it's not their own. ?>
        <?php echo sprintf(__('to comment ID #%1$s on: %2$s', 'comment-mail'), $sub_comment->comment_ID, $sub_post_title_clip); ?>
    <?php endif; ?>

<?php else : // All comments/replies to this post. ?>
    <?php echo sprintf(__('to: %1$s', 'comment-mail'), $sub_post_title_clip); ?>
<?php endif; ?>
