<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
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
// Site home page URL; i.e. back to the main site.
$home_url = home_url('/'); // Multisite compatible.

// A clip of the blog's name; as configured in WordPress.
$blog_name_clip = $plugin->utils_string->clip(get_bloginfo('name'));

// Summary return URL; w/ all summary navigation vars preserved.
$sub_summary_return_url = $plugin->utils_url->sub_manage_summary_url(!empty($sub_key) ? $sub_key : '', NULL, TRUE);

// Current `host[/path]` with support for multisite network and child blogs.
$current_host_path = $plugin->utils_url->current_host_path();

// Logo URL; defaults to the plugin's logo image.
$logo_url         = $plugin->utils_url->to('/client-s/images/logo.png');
$logo_image_width = 936; // Width; in pixels.
?>

<header class="text-center" style="margin-bottom:30px;">
	<h1>
		<?php echo esc_html($blog_name_clip); ?><br />
		<span class="text-muted">
			<small><?php echo esc_html($current_host_path); ?></small>
		</span>
	</h1>
	<img src="<?php echo esc_attr($logo_url); ?>" class="center-block img-responsive" style="width:<?php echo esc_attr($logo_image_width.'px'); ?>;" />
</header>