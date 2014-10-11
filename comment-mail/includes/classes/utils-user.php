<?php
/**
 * User Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_user'))
	{
		/**
		 * User Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_user extends abstract_base
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
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \WP_Screen $screen A screen object instance.
			 * @param string     $option The screen option to get.
			 *
			 * @param integer    $user_id A specific user ID. Defaults to `NULL`.
			 *    A `NULL` value indicates the current user.
			 *
			 * @return mixed The screen option value; only if not empty; and only it has a valid data type.
			 *    If empty, or not the same data type as the default value; returns the default value.
			 */
			public function screen_option(\WP_Screen $screen, $option, $user_id = NULL)
			{
				$user_id       = !isset($user_id) ? (integer)get_current_user_id() : (integer)$user_id;
				$value         = get_user_meta($user_id, $screen->get_option($option, 'option'), TRUE);
				$default_value = $screen->get_option($option, 'default');

				if(!$value || gettype($value) !== gettype($default_value))
					$value = $default_value;

				return $value;
			}
		}
	}
}