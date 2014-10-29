<?php
namespace comment_mail;

/**
 * @var plugin      $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass   $sub Subscription object data.
 * @var \stdClass[] $comments An array of all comment objects.
 */
?>
<?php $post = get_post($sub->post_id); ?>
<?php $post_title_clip = $plugin->utils_string->clip($post->post_title, 30); ?>
<?php $comment = $sub->comment_id ? get_comment($sub->comment_id) : NULL; ?>
<?php $is_digest = count($comments) > 1; ?>

<?php if($is_digest): // Multiple comments/replies in this notification? ?>

	<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

		<?php if($comment && strcasecmp($comment->comment_author_email, $sub->email) === 0): ?>
			<?php echo sprintf(__('New Replies to your Comment on "%1$s"', $plugin->text_domain), $post_title_clip); ?>
		<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
			<?php echo sprintf(__('New Replies to Comment ID# %1$s on "%2$s"', $plugin->text_domain), $sub->comment_id, $post_title_clip); ?>
		<?php endif; ?>

	<?php else: // All comments/replies on this post ID. ?>
		<?php echo sprintf(__('New Comments on "%1$s"', $plugin->text_domain), $post_title_clip); ?>
	<?php endif; ?>

<?php else: // There's just a single comment/reply in this notification. ?>

	<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

		<?php if($comment && strcasecmp($comment->comment_author_email, $sub->email) === 0): ?>
			<?php echo sprintf(__('New Reply to your Comment on "%1$s"', $plugin->text_domain), $post_title_clip); ?>
		<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
			<?php echo sprintf(__('New Reply to Comment ID# %1$s on "%2$s"', $plugin->text_domain), $sub->comment_id, $post_title_clip); ?>
		<?php endif; ?>

	<?php else: // All comments/replies on this post ID. ?>
		<?php echo sprintf(__('New Comment on "%1$s"', $plugin->text_domain), $post_title_clip); ?>
	<?php endif; ?>

<?php endif; ?>