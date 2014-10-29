<?php
namespace comment_mail;

/**
 * @var plugin    $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass $sub Subscription object data.
 */
?>
<?php echo __('Confirm subscription', $plugin->text_domain); ?>

<?php $post = get_post($sub->post_id); ?>
<?php $post_title_clip = $plugin->utils_string->clip($post->post_title, 30); ?>
<?php $comment = $sub->comment_id ? get_comment($sub->comment_id) : NULL; ?>

<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

	<?php if($comment && strcasecmp($comment->comment_author_email, $sub->email) === 0): ?>
		<?php echo sprintf(__('to your comment on "%1$s"', $plugin->text_domain), $post_title_clip); ?>
	<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
		<?php echo sprintf(__('to comment ID# %1$s on "%2$s"', $plugin->text_domain), $sub->comment_id, $post_title_clip); ?>
	<?php endif; ?>

<?php else: // All comments/replies on this post ID. ?>
	<?php echo sprintf(__('to "%1$s"', $plugin->text_domain), $post_title_clip); ?>
<?php endif; ?>