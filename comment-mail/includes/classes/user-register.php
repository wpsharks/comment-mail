<?php
/**
 * User Register
 *
 * @package user_register
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\user_register'))
	{
		/**
		 * User Register
		 *
		 * @package user_register
		 * @since 14xxxx First documented version.
		 */
		class user_register // User registration handler.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var integer User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_id; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $user_id User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($user_id)
			{
				$this->plugin = plugin();

				$this->user_id = (integer)$user_id;

				$this->maybe_update_subs();
			}

			/**
			 * Update subscribers; set user ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_update_subs()
			{
				if(!$this->user_id)
					return; // Nothing to do.

				$user = new \WP_User($this->user_id);

				if(!$user->exists() || !$user->ID)
					return; // Not applicable.

				$sql = "UPDATE `".esc_sql($this->plugin->db_prefix().'subs')."`".

				       " SET `user_id` = '".esc_sql($user->ID)."'".

				       " WHERE `user_id` <= '0'".
				       " AND `email` = '".esc_sql($user->user_email)."'";

				$this->plugin->wpdb->query($sql); // Update subscribers; set user ID.
			}
		}
	}
}