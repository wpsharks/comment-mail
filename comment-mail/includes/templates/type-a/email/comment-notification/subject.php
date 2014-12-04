<?php
namespace comment_mail;

/**
 * @var plugin         $plugin Plugin class.
 * @var template       $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass      $sub Subscription object data.
 *
 * @var \WP_Post       $sub_post Post they're subscribed to.
 *
 * @var \stdClass|null $sub_comment Comment they're subcribed to; if applicable.
 *
 * @var \stdClass[]    $comments An array of all WP comment objects we are notifying about.
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

// A notification may contain one (or more) comments. Is this a digest?
$is_digest = count($comments) > 1; // `TRUE`, if more than one comment in the notification.
?>
<?php if($is_digest): // Multiple comments/replies in this notification? ?>

	<?php if($sub_comment): // Subscribed to a specific comment? ?>

		<?php if($subscribed_to_own_comment): ?>
			<?php echo sprintf(__('New Replies to your Comment on “%1$s”', $plugin->text_domain), $sub_post_title_clip); ?>
		<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
			<?php echo sprintf(__('New Replies to Comment ID #%1$s on “%2$s”', $plugin->text_domain), $sub_comment->comment_ID, $sub_post_title_clip); ?>
		<?php endif; ?>

	<?php else: // All comments/replies on this post ID. ?>
		<?php echo sprintf(__('New Comments on “%1$s”', $plugin->text_domain), $sub_post_title_clip); ?>
	<?php endif; ?>

<?php else: // There's just a single comment/reply in this notification. ?>

	<?php if($sub_comment): // Subscribed to a specific comment? ?>

		<?php if($subscribed_to_own_comment): ?>
			<?php echo sprintf(__('New Reply to your Comment on “%1$s”', $plugin->text_domain), $sub_post_title_clip); ?>
		<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
			<?php echo sprintf(__('New Reply to Comment ID #%1$s on “%2$s”', $plugin->text_domain), $sub_comment->comment_ID, $sub_post_title_clip); ?>
		<?php endif; ?>

	<?php else: // All comments/replies on this post ID. ?>
		<?php echo sprintf(__('New Comment on “%1$s”', $plugin->text_domain), $sub_post_title_clip); ?>
	<?php endif; ?>

<?php endif; ?>