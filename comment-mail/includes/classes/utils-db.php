<?php
/**
 * DB Utilities
 *
 * @package utils_db
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_db'))
	{
		/**
		 * DB Utilities
		 *
		 * @package utils_db
		 * @since 14xxxx First documented version.
		 */
		class utils_db // DB utilities.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
			}

			/**
			 * Current DB prefix for this plugin.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current DB table prefix.
			 */
			public function prefix()
			{
				return $this->plugin->wpdb->prefix.__NAMESPACE__.'_';
			}
		}
	}
}