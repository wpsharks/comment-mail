<?php
/**
 * URL Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_url'))
	{
		/**
		 * URL Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_url extends abstract_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Current scheme; lowercase.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current scheme; lowercase.
			 */
			public function current_scheme()
			{
				return is_ssl() ? 'https' : 'http';
			}

			/**
			 * Current host name; lowercase.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current host name; lowercase.
			 */
			public function current_host()
			{
				return strtolower((string)$_SERVER['HTTP_HOST']);
			}

			/**
			 * Current URI; with a leading `/`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URI; with a leading `/`.
			 */
			public function current_uri()
			{
				return '/'.ltrim((string)$_SERVER['REQUEST_URI'], '/');
			}

			/**
			 * Current URI/path; with a leading `/`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URI/path; with a leading `/`.
			 */
			public function current_path()
			{
				return '/'.ltrim((string)parse_url($this->current_uri(), PHP_URL_PATH), '/');
			}

			/**
			 * Current URL; i.e. scheme.host.URI put together.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; i.e. scheme.host.URI put together.
			 */
			public function current()
			{
				return $this->current_scheme().'://'.$this->current_host().$this->current_uri();
			}

			/**
			 * Current URL; without a query string.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; without a query string.
			 */
			public function current_no_query()
			{
				$current = $this->current(); // Current URL w/ possible query string.

				return strpos($current, '?') !== FALSE ? (string)strstr($current, '?', TRUE) : $current;
			}

			/**
			 * Current URL; with `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; with `_wpnonce`.
			 */
			public function current_nonce()
			{
				return add_query_arg('_wpnonce', urlencode(wp_create_nonce()), $this->current());
			}

			/**
			 * Current URL; with only a `page` var (if applicable).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; with only a `page` var (if applicable).
			 */
			public function current_page_only()
			{
				$page = !empty($_REQUEST['page']) ? stripslashes((string)$_REQUEST['page']) : ''; // On page?

				return $page ? add_query_arg('page', urlencode($page), $this->current_no_query()) : $this->current_no_query();
			}

			/**
			 * Current URL; with only a `page` var (if applicable) and `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; with only a `page` var (if applicable) and `_wpnonce`.
			 */
			public function current_page_nonce_only()
			{
				return add_query_arg('_wpnonce', urlencode(wp_create_nonce()), $this->current_page_only());
			}

			/**
			 * Main menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Main menu page URL.
			 */
			public function main_menu_page_only()
			{
				return add_query_arg('page', urlencode(__NAMESPACE__), self_admin_url('/edit-comments.php'));
			}

			/**
			 * Main menu page URL; w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Main menu page URL; w/ `_wpnonce`.
			 */
			public function main_menu_page_nonce_only()
			{
				return add_query_arg('_wpnonce', urlencode(wp_create_nonce()), $this->main_menu_page_only());
			}

			/**
			 * Add pro preview action to a given URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to preview (optional).
			 *    If empty, defaults to the main menu page.
			 *
			 * @return string The input `$url` with a pro preview action.
			 */
			public function pro_preview($url = '')
			{
				return add_query_arg(__NAMESPACE__.'_pro_preview', '1', $url ? (string)$url : $this->main_menu_page_only());
			}

			/**
			 * Notice dimissal URL, for current URL w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key The notice key to dismiss.
			 *
			 * @return string Notice dimissal URL, for current URL w/ `_wpnonce`.
			 */
			public function dismiss_notice($key)
			{
				$args = array(__NAMESPACE__ => array('dismiss_notice' => array('key' => (string)$key)));

				return add_query_arg(urlencode_deep($args), $this->current_nonce());
			}

			/**
			 * Error dimissal URL, for current URL w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key The notice key to dismiss.
			 *
			 * @return string Error dimissal URL, for current URL w/ `_wpnonce`.
			 */
			public function dismiss_error($key)
			{
				$args = array(__NAMESPACE__ => array('dismiss_error' => array('key' => (string)$key)));

				return add_query_arg(urlencode_deep($args), $this->current_nonce());
			}

			/**
			 * Restore default options URL; for main menu page w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Restore default options URL; for main menu page w/ `_wpnonce`.
			 */
			public function restore_default_options()
			{
				$args = array(__NAMESPACE__ => array('restore_default_options' => '1'));

				return add_query_arg(urlencode_deep($args), $this->main_menu_page_nonce_only());
			}

			/**
			 * Product page URL; normally at WebSharks™.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Product page URL; normally at WebSharks™.
			 */
			public function product_page()
			{
				return 'http://www.websharks-inc.com/product/'.urlencode($this->plugin->slug).'/';
			}

			/**
			 * Subscribe page URL; normally at WebSharks™.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Subscribe page URL; normally at WebSharks™.
			 */
			public function subscribe_page()
			{
				return 'http://www.websharks-inc.com/r/'.urlencode($this->plugin->slug).'-subscribe/';
			}

			/**
			 * URL to a plugin file.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $file Optional file path; relative to plugin directory.
			 * @param string|null $scheme Optional URL scheme. Defaults to the current scheme.
			 *
			 * @return string URL to plugin directory; or to the specified `$file` if applicable.
			 */
			public function to($file = '', $scheme = NULL)
			{
				if(!isset($this->static[__FUNCTION__]['plugin_dir_url']))
					$this->static[__FUNCTION__]['plugin_dir_url'] = rtrim(plugin_dir_url($this->plugin->file), '/');

				return set_url_scheme($this->static[__FUNCTION__]['plugin_dir_url'].(string)$file, $scheme);
			}
		}
	}
}