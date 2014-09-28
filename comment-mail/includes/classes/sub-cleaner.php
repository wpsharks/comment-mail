<?php
/**
 * Sub Cleaner
 *
 * @package sub_cleaner
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_cleaner'))
	{
		/**
		 * Sub Cleaner
		 *
		 * @package sub_cleaner
		 * @since 14xxxx First documented version.
		 */
		class sub_cleaner // Sub cleaner.
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

				$this->clean_nonexistent_users();
				$this->maybe_clean_unconfirmed_expirations();
			}

			/**
			 * Cleanup nonexistent users.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This does NOT cover multisite `capabilities`.
			 *    That's intentional. There is too much room for error in that case.
			 *    We have `wpmu_delete_user` and `remove_user_from_blog` hooks for this anyway.
			 *    We also have a `delete_user` hook too, for normal WP installs.
			 *
			 *    This routine is just here to help keep things extra tidy on normal WP installs.
			 */
			protected function clean_nonexistent_users()
			{
				$user_ids = "SELECT `ID` FROM `".esc_sql($this->plugin->wpdb->users)."`";

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `user_id` != '0' AND `user_id` NOT IN(".$user_ids.")";

				$this->plugin->wpdb->query($sql); // Delete nonexistent users.
			}

			/**
			 * Cleanup nonexistent users.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_clean_unconfirmed_expirations()
			{
				if(!$this->plugin->options['unconfirmed_expiration_time'])
					return; // Not applicable; functionality disabled.

				if(!($exp_time = strtotime('-'.$this->plugin->options['unconfirmed_expiration_time'])))
					return; // Invalid time. Not compatible with `strtotime()`.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `status` = 'unconfirmed'".
				       " AND `last_update_time` < '".esc_sql($exp_time)."'";

				$this->plugin->wpdb->query($sql); // Delete unconfirmed expirations.
			}
		}
	}
}