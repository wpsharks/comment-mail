<?php
namespace comment_mail;
/**
 * @var plugin   $plugin Plugin class.
 * @var template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var template $parent_template Parent template class reference.
 *
 * @note This file is automatically included as a child of other templates.
 *    Therefore, this template will ALSO receive any variable(s) passed to the parent template file,
 *    where the parent automatically calls upon this template. In short, if you see a variable documented in
 *    another template file, that particular variable will ALSO be made available in this file too;
 *    as this file is automatically included as a child of other parent templates.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php
/*
 * Here we define a few variables of our own.
 */
// Site home page URL; i.e. back to main site.
$home_url = home_url('/'); // Multisite compatible.

// A clip of the blog's name; as configured in WordPress.
$blog_name_clip = $plugin->utils_string->clip(get_bloginfo('name'));

// Summary return URL; w/ all summary navigation vars preserved.
$sub_summary_return_url = $plugin->utils_url->sub_manage_summary_url(!empty($sub_key) ? $sub_key : '', NULL, TRUE);

// Current `host[/path]` with support for multisite network child blogs.
$current_host_path = $plugin->utils_url->current_host_path();

// Privacy policy URL; as configured in plugin options via the dashboard.
$can_spam_privacy_policy_url = $plugin->options['can_spam_privacy_policy_url'];
?>

<?php echo $template->snippet(
	'footer-tag.php', array(
		'[home_url]'                    => esc_attr($home_url),
		'[blog_name_clip]'              => esc_html($blog_name_clip),
		'[can_spam_privacy_policy_url]' => esc_attr($can_spam_privacy_policy_url),
		'[sub_summary_return_url]'      => $parent_template->file() !== 'site/sub-actions/manage-summary.php' ? esc_attr($sub_summary_return_url) : '',
		'[powered_by]'                  => $plugin->options['site_footer_powered_by_enable'] ? $plugin->utils_markup->powered_by() : '',
	)); ?>