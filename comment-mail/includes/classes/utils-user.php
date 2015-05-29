<?php
/**
 * User Utilities
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class utils_user extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Screen option value.
			 *
			 * @since 141111 First documented version.
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
				$user_id       = $this->isset_or($user_id, (integer)get_current_user_id(), 'integer');
				$value         = get_user_meta($user_id, $screen->get_option($option, 'option'), TRUE);
				$default_value = $screen->get_option($option, 'default');

				if(!$value || gettype($value) !== gettype($default_value))
					$value = $default_value;

				return $value;
			}

			/**
			 * Is the current user?
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \WP_User|integer A user to check; object or ID.
			 * @param boolean          $allow_0 Allow `0`-based checks also?
			 *
			 * @return boolean `TRUE` if `$user` is the current user.
			 */
			public function is_current($user, $allow_0 = FALSE)
			{
				if(is_integer($user))
					$user_id = (integer)$user;

				else if($user instanceof \WP_User)
					$user_id = (integer)$user->ID;

				if(!isset($user_id))
					return FALSE; // Not possible.

				return ($user_id || ($user_id === 0 && $allow_0))
				       && get_current_user_id() === $user_id;
			}

			/**
			 * Email exists on this blog?
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string  $email The email address to check.
			 * @param boolean $no_cache Refresh a previously cached value?
			 *
			 * @return boolean `TRUE` if `$email` exists on current blog.
			 */
			public function email_exists_on_blog($email, $no_cache = FALSE)
			{
				if(!($email = trim(strtolower((string)$email))))
					return FALSE; // Not possible.

				$blog_id    = get_current_blog_id();
				$cache_keys = compact('email', 'blog_id');

				if(!is_null($exists = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $exists; // Already cached this.

				if(!($user_id = email_exists($email)))
					return ($exists = FALSE); // Not on any blog.

				if(!($user = new \WP_User($user_id)) || !$user->exists())
					return ($exists = FALSE); // Not on any blog.

				return ($exists = !is_multisite() || !empty($user->roles));
			}

			/**
			 * Can users register?
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean `TRUE` if users can register on the current blog.
			 */
			public function can_register()
			{
				if(is_multisite()) // Check network options for this.
					return in_array(get_site_option('registration'), array('all', 'user'), TRUE);

				return (boolean)get_option('users_can_register');
			}
		}
	}
}
