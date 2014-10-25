<?php
/**
 * Sub Purger
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_purger'))
	{
		/**
		 * Sub Purger
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_purger extends abs_base
		{
			/**
			 * @var integer Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_id;

			/**
			 * @var integer Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_id;

			/**
			 * @var integer User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_id;

			/**
			 * @var integer Total subs purged.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $purged;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $post_id Post ID.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @param integer|string $user_id User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($post_id, $comment_id = 0, $user_id = 0)
			{
				parent::__construct();

				$this->post_id    = (integer)$post_id;
				$this->comment_id = (integer)$comment_id;
				$this->user_id    = (integer)$user_id;

				$this->purged = 0; // Initialize.

				$this->maybe_purge(); // If applicable.
			}

			/**
			 * Total subs purged.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function purged()
			{
				return $this->purged;
			}

			/**
			 * Purges subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_purge()
			{
				$this->maybe_purge_post_comment();
				$this->maybe_purge_user();
			}

			/**
			 * Purges subscriptions on post/comment deletions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_purge_post_comment()
			{
				if(!$this->post_id)
					return; // Not applicable.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->post_id)."'".
				       ($this->comment_id ? " AND `comment_id` = '".esc_sql($this->comment_id)."'" : '');

				if(($ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->purged += $this->plugin->utils_sub->bulk_delete($ids, array('purging' => TRUE));
			}

			/**
			 * Purges subscriptions on user deletions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_purge_user()
			{
				if(!$this->user_id)
					return; // Not applicable.

				$user = new \WP_User($this->user_id);
				if(!$user->ID) return; // Not possible.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `user_id` = '".esc_sql($user->ID)."'".
				       ($user->user_email ? " OR `email` = '".esc_sql($user->user_email)."'" : '');

				if(($ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->purged += $this->plugin->utils_sub->bulk_delete($ids, array('purging' => TRUE));
			}
		}
	}
}