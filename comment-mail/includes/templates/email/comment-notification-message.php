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
 * @var \stdClass[] $comments An array of all WP comment objects.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Comment Notification(s)', $plugin->text_domain), $email_header); ?>

<?php
/*
 * Here we define a few more variables of our own.
 * All based on what the template makes available to us;
 * ~ as documented at the top of this file.
 */
// Post they're subscribed to.
$sub_post               = get_post($sub->post_id);
$sub_post_comments_url  = get_comments_link($sub->post_id);
$sub_post_comments_open = comments_open($sub->post_id);
$sub_post_title_clip    = $sub_post ? $plugin->utils_string->clip($sub_post->post_title) : '';

// Comment they're subscribed to; if applicable;
$sub_comment     = $sub->comment_id ? get_comment($sub->comment_id) : NULL;
$sub_comment_url = $sub->comment_id ? get_comment_link($sub->comment_id) : '';

$subscribed_to_own_comment = // Subscribed to their own comment?
	$sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;

// A notification may contain one (or more) comments. Is this a digest?
$is_digest = count($comments) > 1; // `TRUE`, if more than one comment.
?>
	<h3>
		<?php if($is_digest): // Multiple comments/replies in this notification? ?>

			<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

				<?php if($subscribed_to_own_comment): ?>
					<?php echo sprintf(__('New Replies to <a href="%1$s">your Comment</a> on "%2$s"', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub_post_title_clip)); ?>
				<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
					<?php echo sprintf(__('New Replies to <a href="%1$s">Comment ID# %2$s</a> on "%3$s"', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub->comment_id), esc_html($sub_post_title_clip)); ?>
				<?php endif; ?>

			<?php else: // All comments/replies on this post ID. ?>
				<?php echo sprintf(__('New Comments on "<a href="%1$s">%2$s</a>"', $plugin->text_domain), esc_attr($sub_post_comments_url), esc_html($sub_post_title_clip)); ?>
			<?php endif; ?>

		<?php else: // There's just a single comment/reply in this notification. ?>

			<?php if($sub->comment_id): // Subscribed to a specific comment ID? ?>

				<?php if($subscribed_to_own_comment): ?>
					<?php echo sprintf(__('New Reply to <a href="%1$s">your Comment</a> on "%2$s"', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub_post_title_clip)); ?>
				<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
					<?php echo sprintf(__('New Reply to <a href="%1$s">Comment ID# %2$s</a> on "%3$s"', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub->comment_id), esc_html($sub_post_title_clip)); ?>
				<?php endif; ?>

			<?php else: // All comments/replies on this post ID. ?>
				<?php echo sprintf(__('New Comment on "<a href="%1$s">%2$s</a>"', $plugin->text_domain), esc_attr($sub_post_comments_url), esc_html($sub_post_title_clip)); ?>
			<?php endif; ?>

		<?php endif; ?>
	</h3>

	<hr />

	<ul>
		<?php foreach($comments as $_comment): // Comments in this notification. ?>
			<?php
			$_comment_parent      = $_comment->comment_parent ? get_comment($_comment->comment_parent) : NULL;
			$_comment_parent_url  = $_comment_parent ? get_comment_link($_comment_parent->comment_ID) : '';
			$_comment_parent_clip = $_comment_parent ? $plugin->utils_markup->comment_content_mid_clip($_comment_parent, 'notification_parent') : '';

			$_comment_url      = get_comment_link($_comment->comment_ID);
			$_comment_time_ago = $plugin->utils_date->approx_time_difference(strtotime($_comment->comment_date_gmt));
			$_comment_clip     = $plugin->utils_markup->comment_content_clip($_comment, 'notification');
			?>
			<li>
				<?php if($_comment_parent): // This is a reply to someone? ?>

					<p style="margin-bottom:0;">
						<?php echo sprintf(__('In response to <a href="%1$s">comment ID# %2$s</a>;', $plugin->text_domain), esc_attr($_comment_parent_url), esc_html($_comment_parent->comment_ID)); ?>
						<?php if($_comment_parent->comment_author): ?>
							<?php echo sprintf(__('by %1$s', $plugin->text_domain), esc_html($_comment_parent->comment_author)); ?>
						<?php endif; ?>
					</p>
					<p style="margin-top:0; font-style:italic; font-size:90%;">
						<?php echo esc_html($_comment_parent_clip); ?>
					</p>
					<ul>
						<li>
							<p style="font-weight:bold;">
								<?php if($_comment->comment_author): ?>
									<?php echo sprintf(__('%1$s replies from new', $plugin->text_domain), esc_html($_comment->comment_author)); ?>
								<?php else: // The site is not collecting comment author names. ?>
									<?php echo __('New', $plugin->text_domain); ?>
								<?php endif; ?>
								<?php echo sprintf(__('<a href="%1$s">comment ID# %2$s</a> posted %3$s;', $plugin->text_domain), esc_attr($_comment_url), esc_html($_comment->comment_ID), esc_html($_comment_time_ago)); ?>
							</p>
							<p>
								<?php echo esc_html($_comment_clip); ?>

								<a href="<?php echo esc_attr($_comment_url); ?>">
									<?php echo __('continue reading', $plugin->text_domain); ?>
								</a>
								<?php if($sub_post_comments_open): ?>
									<a href="<?php echo esc_attr($_comment_url); ?>">
										<?php echo '| '.__('add reply', $plugin->text_domain); ?>
									</a>
								<?php endif; ?>
							</p>
						</li>
					</ul>

				<?php else: // A new comment; i.e. not a reply to someone. ?>

					<p style="font-weight:bold;">
						<?php if($_comment->comment_author): ?>
							<?php echo sprintf(__('%1$s replies from new', $plugin->text_domain), esc_html($_comment->comment_author)); ?>
						<?php else: // The site is not collecting comment author names. ?>
							<?php echo __('New', $plugin->text_domain); ?>
						<?php endif; ?>
						<?php echo sprintf(__('<a href="%1$s">comment ID# %2$s</a> posted %3$s;', $plugin->text_domain), esc_attr($_comment_url), esc_html($_comment->comment_ID), esc_html($_comment_time_ago)); ?>
					</p>
					<p>
						<?php echo esc_html($_comment_clip); ?>

						<a href="<?php echo esc_attr($_comment_url); ?>">
							<?php echo __('continue reading', $plugin->text_domain); ?>
						</a>
						<?php if($sub_post_comments_open): ?>
							<a href="<?php echo esc_attr($_comment_url); ?>">
								<?php echo '| '.__('add reply', $plugin->text_domain); ?>
							</a>
						<?php endif; ?>
					</p>

				<?php endif; ?>

			</li>
		<?php endforeach; ?>
	</ul>

<?php echo $email_footer; ?>