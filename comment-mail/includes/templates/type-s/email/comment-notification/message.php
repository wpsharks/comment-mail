<?php
namespace comment_mail;

/**
 * @var plugin         $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string         $email_header Parsed email header template.
 * @var string         $email_footer Parsed email footer template.
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
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
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
$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', $plugin->text_domain);

// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
$sub_last_update_time_ago = $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time);

// A notification may contain one (or more) comments. Is this a digest?
$is_digest = count($comments) > 1; // `TRUE`, if more than one comment in the notification.

// Plugin is configured to allow replies via email? If so, this will be `TRUE`.
$replies_via_email_enable = $sub_post_comments_open && $plugin->options['replies_via_email_enable'];
?>
	<h2 style="margin-top:0; font-family:serif;">
		<?php if($is_digest): // Multiple comments/replies in this notification? ?>

			<?php if($sub_comment): // Subscribed to a specific comment? ?>

				<?php if($subscribed_to_own_comment): ?>
					<?php echo sprintf(__('New Replies to <a href="%1$s">your Comment</a> on &ldquo;%2$s&rdquo;', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub_post_title_clip)); ?>
				<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
					<?php echo sprintf(__('New Replies to <a href="%1$s">Comment ID #%2$s</a> on &ldquo;%3$s&rdquo;', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub_comment->comment_ID), esc_html($sub_post_title_clip)); ?>
				<?php endif; ?>

			<?php else: // All comments/replies on this post. ?>
				<?php echo sprintf(__('New Comments on &ldquo;<a href="%1$s">%2$s</a>&rdquo;', $plugin->text_domain), esc_attr($sub_post_comments_url), esc_html($sub_post_title_clip)); ?>
			<?php endif; ?>

		<?php else: // There's just a single comment/reply in this notification. ?>

			<?php if($sub_comment): // Subscribed to a specific comment? ?>

				<?php if($subscribed_to_own_comment): ?>
					<?php echo sprintf(__('New Reply to <a href="%1$s">your Comment</a> on &ldquo;%2$s&rdquo;', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub_post_title_clip)); ?>
				<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
					<?php echo sprintf(__('New Reply to <a href="%1$s">Comment ID #%2$s</a> on &ldquo;%3$s&rdquo;', $plugin->text_domain), esc_attr($sub_comment_url), esc_html($sub_comment->comment_ID), esc_html($sub_post_title_clip)); ?>
				<?php endif; ?>

			<?php else: // All comments/replies on this post ID. ?>
				<?php echo sprintf(__('New Comment on &ldquo;<a href="%1$s">%2$s</a>&rdquo;', $plugin->text_domain), esc_attr($sub_post_comments_url), esc_html($sub_post_title_clip)); ?>
			<?php endif; ?>

		<?php endif; ?>
	</h2>

	<hr />

	<ul>
		<?php foreach($comments as $_comment): // Comments in this notification. ?>
			<?php
			// Parent comment, if applicable; i.e. if this comment is a reply to another.
			$_comment_parent = $_comment->comment_parent ? get_comment($_comment->comment_parent) : NULL;

			// Parent comment URL, if applicable.
			$_comment_parent_url = $_comment_parent ? get_comment_link($_comment_parent->comment_ID) : '';

			// A shorter clip of the full parent comment message body; in plain text.
			$_comment_parent_clip = $_comment_parent ? $plugin->utils_markup->comment_content_mid_clip($_comment_parent, 'notification_parent') : '';

			// URL to this comment; i.e. the one we're notifying about.
			$_comment_url = get_comment_link($_comment->comment_ID);

			// How long ago the comment was posted on the site (human readable).
			$_comment_time_ago = $plugin->utils_date->approx_time_difference(strtotime($_comment->comment_date_gmt));

			// A shorter clip of the full comment message body; in plain text.
			$_comment_clip = $plugin->utils_markup->comment_content_clip($_comment, 'notification', TRUE);

			// Reply via email marker; if applicable. Only needed for digests, and only if replies via email are enabled currently.
			// ~ Note: This marker is not necessary for single comment notifications. A `Reply-To:` header already handles single-comment notifications.
			$_comment_rve_irt_marker = $plugin->utils_rve->irt_marker($_comment->comment_post_ID, $_comment->comment_ID); // e.g. `~rve#779-84`.
			?>
			<li>
				<?php if($_comment_parent): // This is a reply to someone? ?>

					<p style="margin-bottom:0;">
						<?php echo sprintf(__('In response to comment ID <a href="%1$s">#%2$s</a>', $plugin->text_domain), esc_attr($_comment_parent_url), esc_html($_comment_parent->comment_ID)); ?>
						<?php if($_comment_parent->comment_author): ?>
							<?php echo sprintf(__('— posted by %1$s', $plugin->text_domain), esc_html($_comment_parent->comment_author)); ?>
						<?php endif; ?>
					</p>
					<p style="font-style:italic; font-size:90%; margin-top:0;">
						<?php echo esc_html($_comment_parent_clip); ?>
					</p>
					<ul>
						<li>
							<p style="font-size:110%; font-weight:bold;">
								<?php if($_comment->comment_author): ?>
									<?php echo sprintf(__('%1$s added this reply %2$s.', $plugin->text_domain), esc_html($_comment->comment_author), esc_html($_comment_time_ago)); ?>
								<?php else: // The site is not collecting comment author names. ?>
									<?php echo sprintf(__('This reply was posted %1$s.', $plugin->text_domain), esc_html($_comment_time_ago)); ?>
								<?php endif; ?>
							</p>
							<p style="font-size:130%; font-family:serif; max-width:800px;">
								<?php echo esc_html($_comment_clip); ?>
							</p>
							<p style="margin-bottom:0;">
								<a href="<?php echo esc_attr($_comment_url); ?>">
									<?php echo __('continue reading', $plugin->text_domain); ?>
								</a>
								<?php if($sub_post_comments_open): ?>
									| <a href="<?php echo esc_attr($_comment_url); ?>">
										<?php echo __('add reply', $plugin->text_domain); ?>
									</a>
									<?php if($replies_via_email_enable): ?>
										<?php if($is_digest): // Marker only needed in digests. ?>
											<small><em><?php echo sprintf(__('— or reply to this email &amp; start your message with: <code>%1$s</code>', $plugin->text_domain), esc_html($_comment_rve_irt_marker)); ?></em></small>
										<?php else: // The `Reply-To:` field in the email will suffice in other cases; i.e. there is only one comment in this notification. ?>
											<small><em><?php echo __('— or simply reply to this email', $plugin->text_domain); ?></em></small>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							</p>
						</li>
					</ul>

				<?php else: // A new comment; i.e. not a reply to someone. ?>

					<p style="font-size:110%; font-weight:bold;">
						<?php if($_comment->comment_author): ?>
							<?php echo sprintf(__('%1$s left this comment %2$s.', $plugin->text_domain), esc_html($_comment->comment_author), esc_html($_comment_time_ago)); ?>
						<?php else: // The site is not collecting comment author names. ?>
							<?php echo sprintf(__('This comment was posted %1$s.', $plugin->text_domain), esc_html($_comment_time_ago)); ?>
						<?php endif; ?>
					</p>
					<p style="font-size:130%; font-family:serif; max-width:800px;">
						<?php echo esc_html($_comment_clip); ?>
					</p>
					<p style="margin-bottom:0;">
						<a href="<?php echo esc_attr($_comment_url); ?>">
							<?php echo __('continue reading', $plugin->text_domain); ?>
						</a>
						<?php if($sub_post_comments_open): ?>
							| <a href="<?php echo esc_attr($_comment_url); ?>">
								<?php echo __('add reply', $plugin->text_domain); ?>
							</a>
							<?php if($replies_via_email_enable): ?>
								<?php if($is_digest): // Marker only needed in digests. ?>
									<small><em><?php echo sprintf(__('— or reply to this email &amp; start your message with: <code>%1$s</code>', $plugin->text_domain), esc_html($_comment_rve_irt_marker)); ?></em></small>
								<?php else: // The `Reply-To:` field in the email will suffice in other cases; i.e. there is only one comment in this notification. ?>
									<small><em><?php echo __('— or simply reply to this email', $plugin->text_domain); ?></em></small>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
					</p>

				<?php endif; ?>

			</li>
		<?php endforeach; ?>
	</ul>

<?php echo $email_footer; ?>