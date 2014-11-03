<?php
/**
 * User Register
 *
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
		 * @since 14xxxx First documented version.
		 */
		class user_register extends abs_base
		{
			/**
			 * @var \WP_User|null
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $user_id User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($user_id)
			{
				parent::__construct();

				if(($user_id = (integer)$user_id))
					$this->user = new \WP_User($user_id);

				$this->maybe_update_subs();
			}

			/**
			 * Update subscriptions; set user ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			protected function maybe_update_subs()
			{
				if(!$this->user)
					return; // Not possible.

				if(!$this->user->ID)
					return; // Not possible.

				if(!$this->user->user_email)
					return; // Not possible.

				# Update the subs table; i.e. associate w/ this user where applicable.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `email` = '".esc_sql($this->user->user_email)."'".
				       " AND `user_id` = '0'"; // Not yet associated w/ a user ID.

				if(($sub_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql))))
					foreach($sub_ids as $_sub_id) // Update the `user_id` on each of these.
						new sub_updater(array('ID' => $_sub_id, 'user_id' => $this->user->ID));
				unset($_sub_id); // Housekeeping.

				# Update event logs too; i.e. associate w/ this user where applicable.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".
				       " SET `user_id` = '".esc_sql($this->user->ID)."'". // Update.

				       " WHERE `email` = '".esc_sql($this->user->user_email)."'".
				       " AND `user_id` = '0'"; // Not yet associated w/ a user ID.

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".
				       " SET `user_id` = '".esc_sql($this->user->ID)."'". // Update.

				       " WHERE `email` = '".esc_sql($this->user->user_email)."'".
				       " AND `user_id` = '0'"; // Not yet associated w/ a user ID.

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
			}
		}
	}
}