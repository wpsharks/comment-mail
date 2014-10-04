<?php
/**
 * PHP v5.3 Handlers
 *
 * @since 141004 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
if(!function_exists('wp_php53'))
{
	/**
	 * Running PHP v5.3+?
	 *
	 * @return boolean TRUE if PHP v5.3+.
	 */
	function wp_php53()
	{
		return version_compare(PHP_VERSION, '5.3', '>=');
	}
}
if(!function_exists('wp_php53_notice'))
{
	/**
	 * Creates a WP Dashboard notice regarding PHP requirements.
	 *
	 * @param string $software_name Optional. Name of the calling theme/plugin. Defaults to `this software`.
	 * @param string $software_text_domain Optional i18n text domain. Defaults to slugified `$plugin_name`.
	 * @param string $notice_cap Optional. Capability to view notice. Defaults to `activate_plugins`.
	 * @param string $notice_hook Optional. Action hook. Defaults to `all_admin_notices`.
	 * @param string $notice Optional. Custom notice HTML; instead of default markup.
	 */
	function wp_php53_notice($software_name = '', $software_text_domain = '', $notice_cap = '', $notice_hook = '', $notice = '')
	{
		$software_name        = trim((string)$software_name);
		$software_text_domain = trim((string)$software_text_domain);
		$notice_cap           = trim((string)$notice_cap);
		$notice_hook          = trim((string)$notice_hook);
		$notice               = trim((string)$notice);

		if(!$software_name)
			$software_name = 'this software';

		if(!$software_text_domain) // We can build this dynamically.
			$software_text_domain = trim(preg_replace('/[^a-z0-9\-]/i', '-', strtolower($software_name)), '-');

		if(!$notice_cap) // WP capability.
			$notice_cap = 'activate_plugins';

		if(!$notice_hook) // Action hook.
			$notice_hook = 'all_admin_notices';

		if(!$notice) // Only if there is NOT a custom `$notice` defined already.
		{
			$notice = sprintf(__('<strong>%1$s is NOT active. %1$s requires PHP v5.3 (or higher).</strong>', $software_text_domain),
			                  strpos($software_name, '-') !== FALSE ? '<code>'.esc_html($software_name).'</code>' : esc_html($software_name));

			$notice .= ' '.sprintf(__('You\'re currently running <code>PHP v%1$s</code>.', $software_text_domain), esc_html(PHP_VERSION));
			$notice .= ' '.__('A simple update is necessary. Please ask your web hosting company to do this for you.', $software_text_domain);

			$notice .= ' '.sprintf(__('To remove this message, please deactivate %1$s.', $software_text_domain),
			                       strpos($software_name, '-') !== FALSE ? '<code>'.esc_html($software_name).'</code>' : esc_html($software_name));
		}
		$notice_handler = create_function('', 'if(current_user_can(\''.str_replace("'", "\\'", $notice_cap).'\'))'.
		                                      '  echo \'<div class="error"><p>'.str_replace("'", "\\'", $notice).'</p></div>\';');

		add_action($notice_hook, $notice_handler); // Attach WP Dashboard notice.
	}
}
if(!function_exists('wp_php53_custom_notice'))
{
	/**
	 * Creates a WP Dashboard notice regarding PHP requirements.
	 *
	 * @param string $notice Optional. Custom notice HTML; instead of default markup.
	 * @param string $notice_cap Optional. Capability to view notice. Defaults to `activate_plugins`.
	 * @param string $notice_hook Optional. Action hook. Defaults to `all_admin_notices`.
	 */
	function wp_php53_custom_notice($notice = '', $notice_cap = '', $notice_hook = '')
	{
		wp_php53_notice('', '', $notice_cap, $notice_hook, $notice);
	}
}
/*
 * Return on `include/require`.
 */
return wp_php53(); // TRUE if PHP v5.3+; FALSE otherwise.