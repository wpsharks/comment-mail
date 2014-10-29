<?php
/**
 * Environment Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_env'))
	{
		/**
		 * Environment Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_env extends abs_base
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
			 * Current user IP address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current user's IP address; else an empty string.
			 */
			public function user_ip()
			{
				if(!is_null($ip = &$this->static_key(__FUNCTION__)))
					return $ip; // Cached this already.

				$ip = ''; // Initialize.
				if(!empty($_SERVER['REMOTE_ADDR']))
					$ip = $_SERVER['REMOTE_ADDR'];

				return ($ip = (string)substr($ip, 0, 39));
			}

			/**
			 * Have plugin options been restored?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if plugin options have been restored.
			 */
			public function is_options_restored()
			{
				if(!is_null($is = &$this->static_key(__FUNCTION__)))
					return $is; // Cached this already.

				if(!$this->is_menu_page(__NAMESPACE__.'*'))
					return ($is = FALSE);

				return ($is = !empty($_REQUEST[__NAMESPACE__.'_options_restored']));
			}

			/**
			 * Have plugin options been updated?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if plugin options have been updated.
			 */
			public function is_options_updated()
			{
				if(!is_null($is = &$this->static_key(__FUNCTION__)))
					return $is; // Cached this already.

				if(!$this->is_menu_page(__NAMESPACE__.'*'))
					return ($is = FALSE);

				return ($is = !empty($_REQUEST[__NAMESPACE__.'_options_updated']));
			}

			/**
			 * Current request is for a pro version preview?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if the current request is for a pro preview.
			 */
			public function is_pro_preview()
			{
				if(!is_null($is = &$this->static_key(__FUNCTION__)))
					return $is; // Cached this already.

				if(!$this->is_menu_page(__NAMESPACE__.'*'))
					return ($is = FALSE);

				return ($is = !empty($_REQUEST[__NAMESPACE__.'_pro_preview']));
			}

			/**
			 * Current admin menu page.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current admin menu page.
			 */
			public function current_menu_page()
			{
				if(!is_null($page = &$this->static_key(__FUNCTION__)))
					return $page; // Cached this already.

				if(!is_admin()) return ($page = '');

				$page = !empty($_REQUEST['page'])
					? stripslashes((string)$_REQUEST['page'])
					: (!empty($GLOBALS['pagenow']) ? (string)$GLOBALS['pagenow'] : '');

				return $page; // Current menu page.
			}

			/**
			 * Checks if current page is a menu page.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $page_to_check A specific page to check (optional).
			 *    If empty, this returns `TRUE` for any admin menu page.
			 *
			 *    `*` wildcard characters are supported in the page to check.
			 *       Also note, the check is caSe insensitive.
			 *
			 * @return boolean TRUE if current page is a menu page.
			 *    Pass `$page_to_check` to check a specific page.
			 */
			public function is_menu_page($page_to_check = '')
			{
				$page_to_check = (string)$page_to_check;

				if(!is_null($is = &$this->static_key(__FUNCTION__, $page_to_check)))
					return $is; // Cached this already.

				if(!is_admin()) // Not admin area?
					return ($is = FALSE); // Nope!

				if(!($current_page = $this->current_menu_page()))
					return ($is = FALSE); // Not a menu page.

				if(!$page_to_check) // Any menu page?
					return ($is = TRUE); // Yep, it is!

				$page_to_check_regex = '/^'.preg_replace('/\\\\\*/', '.*?', preg_quote($page_to_check, '/')).'$/i';

				return ($is = (boolean)preg_match($page_to_check_regex, $current_page));
			}

			/**
			 * Maxmizes available memory.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function maximize_memory()
			{
				if(is_admin()) // In an admin area?
					@ini_set('memory_limit', // Maximize memory.
					         apply_filters('admin_memory_limit', WP_MAX_MEMORY_LIMIT));
				else @ini_set('memory_limit', WP_MAX_MEMORY_LIMIT);
			}

			/**
			 * Prepares for output delivery.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function prep_for_output()
			{
				@set_time_limit(0);

				@ini_set('zlib.output_compression', 0);
				if(function_exists('apache_setenv'))
					@apache_setenv('no-gzip', '1');

				while(@ob_end_clean()) ;
			}

			/**
			 * Prepares for large output delivery.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function prep_for_large_output()
			{
				$this->maximize_memory();
				$this->prep_for_output();
			}
		}
	}
}