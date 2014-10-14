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
		class utils_env extends abstract_base
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
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$ip = &$this->static[__FUNCTION__];

				return ($ip = !empty($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '');
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
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$is = &$this->static[__FUNCTION__];

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
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$is = &$this->static[__FUNCTION__];

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
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$is = &$this->static[__FUNCTION__];

				if(!$this->is_menu_page(__NAMESPACE__.'*'))
					return ($is = FALSE);

				return ($is = !empty($_REQUEST[__NAMESPACE__.'_pro_preview']));
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

				if(isset($this->static[__FUNCTION__][$page_to_check]))
					return $this->static[__FUNCTION__][$page_to_check];

				$is = &$this->static[__FUNCTION__][$page_to_check];

				if(!is_admin()) // In an admin area?
					return ($is = FALSE); // Nope.

				$current_page = !empty($_REQUEST['page'])
					? stripslashes((string)$_REQUEST['page'])
					: (!empty($GLOBALS['pagenow']) ? (string)$GLOBALS['pagenow'] : '');

				if(!$current_page) return ($is = FALSE); // Not on a menu page.
				if(!$page_to_check) return ($is = TRUE); // Any page; and it is.

				$page_to_check_regex = '/^'.preg_replace('/\\\\\*/', '.*?', preg_quote($page_to_check, '/')).'$/i';
				return ($is = (boolean)preg_match($page_to_check_regex, $current_page));
			}
		}
	}
}