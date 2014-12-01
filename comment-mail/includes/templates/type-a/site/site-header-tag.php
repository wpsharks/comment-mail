<?php
namespace comment_mail;

/**
 * @var plugin   $plugin Plugin class.
 * @var template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string   $template_file Relative path to the current template file.
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

// Icon URL; defaults to the plugin's icon image.
$icon_bubbles_url = $plugin->utils_url->to('/client-s/images/icon-bubbles.png');
?>

<header class="center-block clearfix">
	<h1>
		<?php echo esc_html($blog_name_clip); ?><br />
		<a href="<?php echo esc_attr($home_url); ?>">
			<small><i class="fa fa-link"></i> <?php echo esc_html($current_host_path); ?></small>
		</a>
	</h1>
	<img src="<?php echo esc_attr($icon_bubbles_url); ?>" class="icon-bubbles" />
</header>