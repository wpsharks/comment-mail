<?php
namespace comment_mail;

/**
 * @var plugin      $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string      $email_header Parsed email header template.
 * @var string      $email_footer Parsed email footer template.
 *
 * @var \stdClass   $sub Subscription object data.
 * @var \stdClass[] $comments An array of all comment objects.
 */
?>
<?php echo str_replace('%%title%%', __('Comment Notification(s)', $plugin->text_domain), $email_header); ?>

<?php $post = get_post($sub->post_id); ?>
<?php $post_title_clip = $plugin->utils_string->clip($post->post_title); ?>
<?php $comment = $sub->comment_id ? get_comment($sub->comment_id) : NULL; ?>
<?php $is_digest = count($comments) > 1; ?>

	<h3>
		<?php if($is_digest): // Multiple comments/replies in this notification? ?>

			<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

				<?php if($comment && strcasecmp($comment->comment_author_email, $sub->email) === 0): ?>
					<?php echo sprintf(__('New Replies to <a href="%1$s">your Comment</a> on "%2$s"', $plugin->text_domain), esc_attr(get_comment_link($sub->comment_id)), esc_html($post_title_clip)); ?>
				<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
					<?php echo sprintf(__('New Replies to <a href="%1$s">Comment ID# %2$s</a> on "%3$s"', $plugin->text_domain), esc_attr(get_comment_link($sub->comment_id)), esc_html($sub->comment_id), esc_html($post_title_clip)); ?>
				<?php endif; ?>

			<?php else: // All comments/replies on this post ID. ?>
				<?php echo sprintf(__('New Comments on "<a href="%1$s">%2$s</a>"', $plugin->text_domain), esc_attr(get_comments_link($sub->post_id)), esc_html($post_title_clip)); ?>
			<?php endif; ?>

		<?php else: // There's just a single comment/reply in this notification. ?>

			<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

				<?php if($comment && strcasecmp($comment->comment_author_email, $sub->email) === 0): ?>
					<?php echo sprintf(__('New Reply to <a href="%1$s">your Comment</a> on "%2$s"', $plugin->text_domain), esc_attr(get_comment_link($sub->comment_id)), esc_html($post_title_clip)); ?>
				<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
					<?php echo sprintf(__('New Reply to <a href="%1$s">Comment ID# %2$s</a> on "%3$s"', $plugin->text_domain), esc_attr(get_comment_link($sub->comment_id)), esc_html($sub->comment_id), esc_html($post_title_clip)); ?>
				<?php endif; ?>

			<?php else: // All comments/replies on this post ID. ?>
				<?php echo sprintf(__('New Comment on "<a href="%1$s">%2$s</a>"', $plugin->text_domain), esc_attr(get_comments_link($sub->post_id)), esc_html($post_title_clip)); ?>
			<?php endif; ?>

		<?php endif; ?>
	</h3>

	<hr />

	<ul>
		<?php foreach($comments as $_comment): ?>
			<li>
				<?php if($_comment->comment_parent && ($_comment_parent = get_comment($_comment->comment_parent))): ?>

					<p style="margin-bottom:0;">
						<?php echo sprintf(__('In response to <a href="%1$s">comment ID# %2$s</a>;', $plugin->text_domain), esc_attr(get_comment_link($_comment_parent->comment_ID)), esc_html($_comment_parent->comment_ID)); ?>
						<?php if($_comment_parent->comment_author): ?>
							<?php echo sprintf(__('by %1$s', $plugin->text_domain), esc_html($_comment_parent->comment_author)); ?>
						<?php endif; ?>
					</p>
					<p style="margin-top:0; font-style:italic; font-size:90%;">
						<?php echo esc_html($plugin->utils_markup->comment_content_mid_clip($_comment_parent, 'notification_parent')); ?>
					</p>
					<ul>
						<li>
							<p style="font-weight:bold;">
								<?php if($_comment->comment_author): ?>
									<?php echo sprintf(__('%1$s replies from new', $plugin->text_domain), esc_html($_comment->comment_author)); ?>
								<?php else: // The site is not collecting comment author names. ?>
									<?php echo __('New', $plugin->text_domain); ?>
								<?php endif; ?>

								<?php echo sprintf(__('<a href="%1$s">comment ID# %2$s</a> posted %3$s;', $plugin->text_domain), esc_attr(get_comment_link($_comment->comment_ID)), esc_html($_comment->comment_ID), esc_html($plugin->utils_date->approx_time_difference($_comment->comment_date_gmt))); ?>
							</p>
							<p>
								<?php echo esc_html($plugin->utils_markup->comment_content_clip($_comment, 'notification')); ?>

								<a href="<?php echo esc_attr(get_comment_link($_comment->comment_ID)); ?>">
									<?php echo __('continue reading', $plugin->text_domain); ?>
								</a>
								<?php if(comments_open($_comment->comment_post_ID)): ?>
									<a href="<?php echo esc_attr(get_comment_link($_comment->comment_ID)); ?>">
										<?php echo '| '.__('add reply', $plugin->text_domain); ?>
									</a>
								<?php endif; ?>
							</p>
						</li>
					</ul>

				<?php else: // A new comment; i.e. not a reply to someone else. ?>

					<p style="font-weight:bold;">
						<?php if($_comment->comment_author): ?>
							<?php echo sprintf(__('%1$s writes from new', $plugin->text_domain), esc_html($_comment->comment_author)); ?>
						<?php else: // The site is not collecting comment author names. ?>
							<?php echo __('New', $plugin->text_domain); ?>
						<?php endif; ?>

						<?php echo sprintf(__('<a href="%1$s">comment ID# %2$s</a> posted %3$s;', $plugin->text_domain), esc_attr(get_comment_link($_comment->comment_ID)), esc_html($_comment->comment_ID), esc_html($plugin->utils_date->approx_time_difference($_comment->comment_date_gmt))); ?>
					</p>
					<p>
						<?php echo esc_html($plugin->utils_markup->comment_content_clip($_comment, 'notification')); ?>

						<a href="<?php echo esc_attr(get_comment_link($_comment->comment_ID)); ?>">
							<?php echo __('continue reading', $plugin->text_domain); ?>
						</a>
						<?php if(comments_open($_comment->comment_post_ID)): ?>
							<a href="<?php echo esc_attr(get_comment_link($_comment->comment_ID)); ?>">
								<?php echo '| '.__('add reply', $plugin->text_domain); ?>
							</a>
						<?php endif; ?>
					</p>

				<?php endif; ?>

			</li>
		<?php endforeach; ?>
	</ul>

<?php echo $email_footer; ?>