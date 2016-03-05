<?php
namespace comment_mail;
/**
 * @var plugin    $plugin Plugin class.
 * @var template  $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var integer   $post_id Current post ID; where this is being displayed.
 *
 * @var \stdClass $current An object w/ `sub_email`, `sub_type`, and `sub_deliver`.
 *    These properties are also provided as variables below; same thing.
 *
 * @var string    $sub_email The current subscriber's email address; if available.
 *    Note: a link to the summary page (i.e. the My Subscriptions page) should not be displayed if this is empty.
 *
 * @var string    $sub_type The current subscriber's last known option for the subscription type select menu.
 *    Or, if we don't know for sure, this will be filled w/ the default value configured in plugin options.
 *
 * @var string    $sub_deliver The current subscriber's last known option for the deliver option select menu.
 *    Or, if we don't know for sure, this will be filled w/ the default value configured in plugin options.
 *
 * @var string    $sub_type_id The `id=""` value for the subscription type select menu.
 * @var string    $sub_type_name The `name=""` value for the subscription type select menu.
 *
 * @var string    $sub_deliver_id The `id=""` value for the subscription delivery option select menu.
 * @var string    $sub_deliver_name The `name=""` value for the subscription delivery option select menu.
 *
 * @var string    $sub_summary_url A URL leading the subscription summary page (i.e. the My Subscriptions page).
 *    A link to the summary page (i.e. the My Subscriptions page) should not be displayed if `$sub_email` is empty.
 *
 * @var string    $sub_new_url A URL leading to the "Add Subscription" page. This allows a visitor to subscribe w/o commenting even.
 *
 * @var string    $inline_icon_svg Inline SVG icon that inherits the color and width of it's container automatically.
 *    Note, this is a scalable vector graphic that will look great at any size >= 16x16 pixels.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>

<?php ob_start(); ?>
	<style type="text/css">
		.comment-sub-ops
		{
			margin : 1em 0 1em 0;
		}
		.comment-sub-ops label
		{
			display : block;
		}
		.comment-sub-ops select
		{
			box-sizing : border-box;
			display    : inline-block;
		}
		.comment-sub-ops select.cso-sub-type
		{
			width : 70%;
			float : left;
		}
		.comment-sub-ops select.cso-sub-deliver
		{
			width : 28%;
			float : right;
		}
		.comment-sub-ops select.cso-sub-deliver[disabled]
		{
			opacity : 0.3;
		}
		.comment-sub-ops .cso-links
		{
			font-size   : 80%;
			line-height : 1.5em;
			margin      : 0 0 0 .5em;
			clear       : both;
		}
		.comment-sub-ops .cso-links .cso-link-summary
		{
			display     : block;
			line-height : 1em;
		}
	</style>
<?php $css_styles = ob_get_clean(); ?>

<?php ob_start(); ?>
	<select id="<?php echo esc_attr($sub_type_id); ?>" name="<?php echo esc_attr($sub_type_name); ?>" class="cso-sub-type form-control" title="<?php _e('Receive Notifications?', 'comment-mail'); ?>">
		<option value=""<?php selected('', $current->sub_type); ?>><?php _e('no, do not subscribe', 'comment-mail'); ?></option>
		<option value="comment"<?php selected('comment', $current->sub_type); ?>><?php _e('yes, replies to my comment', 'comment-mail'); ?></option>
		<option value="comments"<?php selected('comments', $current->sub_type); ?>><?php _e('yes, all comments/replies', 'comment-mail'); ?></option>
	</select>
<?php $sub_type_options = ob_get_clean(); ?>

<?php ob_start(); ?>
	<select id="<?php echo esc_attr($sub_deliver_id); ?>" name="<?php echo esc_attr($sub_deliver_name); ?>" class="cso-sub-deliver form-control" title="<?php _e('Notify Me', 'comment-mail'); ?>">
		<option value="asap"<?php selected('asap', $current->sub_deliver); ?>><?php _e('instantly', 'comment-mail'); ?></option>
		<option value="hourly"<?php selected('hourly', $current->sub_deliver); ?>><?php _e('hourly digest', 'comment-mail'); ?></option>
		<option value="daily"<?php selected('daily', $current->sub_deliver); ?>><?php _e('daily digest', 'comment-mail'); ?></option>
		<option value="weekly"<?php selected('weekly', $current->sub_deliver); ?>><?php _e('weekly digest', 'comment-mail'); ?></option>
	</select>
<?php $sub_deliver_options = ob_get_clean(); ?>

<?php echo $template->snippet(
	'sub-ops.php', array(
		'[css_styles]'          => $css_styles,
		'[inline_icon_svg]'     => $inline_icon_svg,
		'[sub_type_options]'    => $sub_type_options,
		'[sub_deliver_options]' => $sub_deliver_options,
		'[sub_type_id]'         => esc_html($sub_type_id),
		'[current_sub_email]'   => esc_html($current->sub_email),
		'[sub_new_url]'         => esc_attr($sub_new_url),
		'[sub_summary_url]'     => esc_attr($sub_summary_url),
	)); ?>
