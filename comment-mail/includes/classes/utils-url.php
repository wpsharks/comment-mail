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
				if(!isset($this->static[__FUNCTION__]['plugin_dir']))
					$this->static[__FUNCTION__]['plugin_dir'] = rtrim(plugin_dir_url($this->plugin->file), '/');

				$url = $this->static[__FUNCTION__]['plugin_dir'].(string)$file;
				$url = set_url_scheme($url, $scheme);

				return $url;
			}
		}
	}
}