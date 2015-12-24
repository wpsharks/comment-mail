<?php
namespace comment_mail;
/**
 * @var plugin           $plugin       Plugin class.
 * @var template         $template     Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string           $email_header Parsed email header template.
 * @var string           $email_footer Parsed email footer template.
 *
 * @var \stdClass        $sub          Subscription object data.
 *
 * @var \WP_Post         $sub_post     Post they're subscribed to.
 *
 * @var \WP_Comment|null $sub_comment  Comment they're subcribed to; if applicable.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
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

// Confirmation URL; they need to click this.
$sub_confirm_url = $plugin->utils_url->sub_confirm_url($sub->key);

// Subscriber's `"name" <email>` w/ HTML markup enhancements.
$sub_name_email_markup = $plugin->utils_markup->name_email($sub->fname.' '.$sub->lname, $sub->email);

// Subscriber's last known IP address.
$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', $plugin->text_domain);

// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
$sub_last_update_time_ago = $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time);
?>

	<?php echo $template->snippet(
		'message.php', array(
			'sub_comment'               => $sub_comment,
			'subscribed_to_own_comment' => $subscribed_to_own_comment,
			'sub_post_comments_open'    => $sub_post_comments_open,

			'[sub_fname]'               => esc_html($sub->fname),
			'[sub_confirm_url]'         => esc_attr($sub_confirm_url),

			'[sub_post_comments_url]'   => esc_attr($sub_post_comments_url),
			'[sub_post_title_clip]'     => esc_html($sub_post_title_clip),

			'[sub_comment_url]'         => esc_attr($sub_comment_url),
			'[sub_comment_id]'          => esc_html($sub_comment ? $sub_comment->comment_ID : 0),
		)); ?>

	<p style="color:#888888; font-style:italic;">
		<?php echo __('Note: if you did not make this request, please ignore this email. You will only be subscribed if you confirm.', $plugin->text_domain); ?>
		<?php echo sprintf(__('This subscription was requested by %1$s; from IP address: <code>%2$s</code> on %3$s.', $plugin->text_domain), $sub_name_email_markup, esc_html($sub_last_ip), esc_html($sub_last_update_time_ago)); ?>
		<?php echo __('If you need to report any continued abuse, please use the contact info at the bottom of this email.', $plugin->text_domain); ?>
	</p>

<?php echo $email_footer; ?>
