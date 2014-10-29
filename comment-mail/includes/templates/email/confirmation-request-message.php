<?php
namespace comment_mail;

/**
 * @var plugin    $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string    $email_header Parsed email header template.
 * @var string    $email_footer Parsed email footer template.
 *
 * @var \stdClass $sub Subscription object data.
 */
?>
<?php echo str_replace('%%title%%', __('Confirmation Request', $plugin->text_domain), $email_header); ?>

<?php $post = get_post($sub->post_id); ?>
<?php $comment = $sub->comment_id ? get_comment($sub->comment_id) : NULL; ?>


<?php if($sub->fname): ?>
	<p style="margin-top:0; font-family:serif; font-size:140%;">
		<?php echo esc_html(sprintf(__('Hi %1$s :-)', $plugin->text_domain), esc_html($sub->fname))); ?>
	</p>
<?php endif; ?>

	<p style="font-size:120%;">
		<?php echo __('Please', $plugin->text_domain); ?>
		<a href="<?php echo esc_attr($plugin->utils_url->sub_confirm_url($sub->key)); ?>">
			<strong><?php echo __('click here to confirm', $plugin->text_domain); ?></strong></a>
		<?php echo __('your subscription', $plugin->text_domain); ?>.
	</p>

	<p style="margin-left:10px;">

		<?php if($sub->comment_id): // Subscribing to a specific comment? ?>

			<?php if($comment && strcasecmp($comment->comment_author_email, $sub->email) === 0): ?>
				<?php echo sprintf(__('You\'ll be notified about replies to <a href="%1$s">your comment</a> on:', $plugin->text_domain),
				                   esc_html(get_comment_link($sub->comment_id))); ?>
			<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
				<?php echo sprintf(__('You\'ll be notified about replies to <a href="%1$s">comment ID# %2$s</a> on:', $plugin->text_domain),
				                   esc_html(get_comment_link($sub->comment_id)), esc_html($sub->comment_id)); ?>
			<?php endif; ?>

		<?php else: // All comments/replies on this post ID. ?>
			<?php echo __('You\'ll be notified about all comments/replies on:', $plugin->text_domain); ?>
		<?php endif; ?><br />

		<span style="font-size:120%;">
			"<?php echo esc_html($post->post_title); ?>"
		</span><br />

		<?php if($sub->comment_id): // A specific comment ID? ?>
			<a href="<?php echo esc_attr(get_comment_link($sub->comment_id)); ?>">
				<?php echo esc_html(get_comment_link($sub->comment_id)); ?>
			</a>
		<?php else: // Subscribing to all comments/replies on this post ID. ?>
			<a href="<?php echo esc_attr(get_comments_link($sub->post_id)); ?>">
				<?php echo esc_html(get_comments_link($sub->post_id)); ?>
			</a>
		<?php endif; ?>

	</p>

	<p style="margin-left:10px; font-style:italic;">
		<?php echo __('Note: if you did not make this request, please ignore this email. You will only be subscribed if you confirm.', $plugin->text_domain); ?>
		<?php echo sprintf(__('This subscription was requested by %1$s; from IP address: <code>%2$s</code> on %3$s.', $plugin->text_domain),
		                   $plugin->utils_markup->name_email($sub->fname.' '.$sub->lname, $sub->email),
		                   esc_html($sub->last_ip ? $sub->last_ip : __('unknown', $plugin->text_domain)),
		                   esc_html($plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time))); ?>
		<?php echo __('If you need to report any continued abuse, please use the contact info at the bottom of this email.', $plugin->text_domain); ?>
	</p>

<?php echo $email_footer; ?>