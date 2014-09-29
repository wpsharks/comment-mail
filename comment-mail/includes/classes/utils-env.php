<?php
/**
 * Environment Utilities
 *
 * @package utils_env
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
		 * @package utils_env
		 * @since 14xxxx First documented version.
		 */
		class utils_env // Environment utilities.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var array Global static cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $static = array();

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
			 * Is the current OS running at least a 64-bit architecture?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if the current OS is running at least a 64-bit architecture.
			 *
			 * @note 32-bit systems will have `PHP_INT_SIZE` = `4`.
			 * @note 32-bit systems have `PHP_INT_MAX` = `2147483647`.
			 *
			 * @note 64-bit systems will have `PHP_INT_SIZE` = `8`.
			 * @note 64-bit systems have `PHP_INT_MAX` = `9223372036854775807`.
			 */
			public function is_64_bit_os()
			{
				return PHP_INT_SIZE >= 8;
			}

			/**
			 * Current request is for a pro version preview?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if the current request is for a pro preview.
			 */
			public function is_pro_preview()
			{
				if(isset(static::$static[__FUNCTION__]))
					return static::$static[__FUNCTION__];

				$is = &static::$static[__FUNCTION__];

				if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
					return ($is = TRUE);

				return ($is = FALSE);
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
				return !empty($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
			}
		}
	}
}