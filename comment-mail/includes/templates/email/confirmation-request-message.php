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
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Confirmation Request', $plugin->text_domain), $email_header); ?>

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

// Confirmation URL; they need to click this.
$sub_confirm_url = $plugin->utils_url->sub_confirm_url($sub->key);

// Subscriber's `"name" <email>` w/ HTML markup enhancements.
$sub_name_email_markup = $plugin->utils_markup->name_email($sub->fname.' '.$sub->lname, $sub->email);

// Subscriber's last known IP address.
$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', $plugin->text_domain);

// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
$sub_last_update_time_ago = $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time);
?>
<?php if($sub->fname): ?>
	<p style="margin-top:0; font-family:serif; font-size:140%;">
		<?php echo esc_html(sprintf(__('Hi %1$s :-)', $plugin->text_domain), esc_html($sub->fname))); ?>
	</p>
<?php endif; ?>

	<p style="font-size:120%;">
		<?php echo __('Please', $plugin->text_domain); ?>
		<a href="<?php echo esc_attr($sub_confirm_url); ?>">
			<strong><?php echo __('click here to confirm', $plugin->text_domain); ?></strong></a>
		<?php echo __('your subscription.', $plugin->text_domain); ?>
	</p>

	<p style="margin-left:10px;">

		<?php if($sub->comment_id): // Subscribing to a specific comment? ?>

			<?php if($subscribed_to_own_comment): ?>
				<?php echo sprintf(__('You\'ll be notified about replies to <a href="%1$s">your comment</a> on:', $plugin->text_domain), esc_html($sub_comment_url)); ?>
			<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
				<?php echo sprintf(__('You\'ll be notified about replies to <a href="%1$s">comment ID# %2$s</a> on:', $plugin->text_domain), esc_html($sub_comment_url), esc_html($sub->comment_id)); ?>
			<?php endif; ?>

		<?php else: // All comments/replies on this post ID. ?>
			<?php echo __('You\'ll be notified about all comments/replies on:', $plugin->text_domain); ?>
		<?php endif; ?><br />

		<span style="font-size:120%;">
			"<?php echo esc_html($sub_post->post_title); ?>"
		</span><br />

		<?php if($sub->comment_id): // A specific comment ID? ?>
			<a href="<?php echo esc_attr($sub_comment_url); ?>">
				<?php echo esc_html($sub_comment_url); ?>
			</a>
		<?php else: // Subscribing to all comments/replies on this post ID. ?>
			<a href="<?php echo esc_attr($sub_post_comments_url); ?>">
				<?php echo esc_html($sub_post_comments_url); ?>
			</a>
		<?php endif; ?>

	</p>

	<p style="margin-left:10px; font-style:italic;">
		<?php echo __('Note: if you did not make this request, please ignore this email. You will only be subscribed if you confirm.', $plugin->text_domain); ?>
		<?php echo sprintf(__('This subscription was requested by %1$s; from IP address: <code>%2$s</code> on %3$s.', $plugin->text_domain), $sub_name_email_markup, esc_html($sub_last_ip), esc_html($sub_last_update_time_ago)); ?>
		<?php echo __('If you need to report any continued abuse, please use the contact info at the bottom of this email.', $plugin->text_domain); ?>
	</p>

<?php echo $email_footer; ?>