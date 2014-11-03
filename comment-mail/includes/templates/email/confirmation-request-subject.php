<?php
namespace comment_mail;

/**
 * @var plugin    $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass $sub Subscription object data.
 *
 * @note Extra whitespace in subject templates is stripped automatically.
 * That's why this template is able to break things down into multiple lines.
 * In the end, the email will contain a one-line subject of course.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo __('Confirm subscription', $plugin->text_domain); ?>

<?php
/*
 * Here we define a few more variables of our own.
 * All based on what the template makes available to us;
 * ~ as documented at the top of this file.
 */
// Post they're subscribed to.
$sub_post            = get_post($sub->post_id);
$sub_post_title_clip = $sub_post ? $plugin->utils_string->clip($sub_post->post_title, 30) : '';

// Comment they're subscribed to; if applicable;
$sub_comment = $sub->comment_id ? get_comment($sub->comment_id) : NULL;

$subscribed_to_own_comment = // Subscribed to their own comment?
	$sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;
?>
<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

	<?php if($subscribed_to_own_comment): ?>
		<?php echo sprintf(__('to your comment on "%1$s"', $plugin->text_domain), $sub_post_title_clip); ?>
	<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
		<?php echo sprintf(__('to comment ID# %1$s on "%2$s"', $plugin->text_domain), $sub->comment_id, $sub_post_title_clip); ?>
	<?php endif; ?>

<?php else: // All comments/replies on this post ID. ?>
	<?php echo sprintf(__('to "%1$s"', $plugin->text_domain), $sub_post_title_clip); ?>
<?php endif; ?>