<?php
/**
 * Sub Deleter
 *
 * @package sub_deleter
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_deleter'))
	{
		/**
		 * Sub Deleter
		 *
		 * @package sub_deleter
		 * @since 14xxxx First documented version.
		 */
		class sub_deleter // Sub deleter.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var integer Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_id; // Set by constructor.

			/**
			 * @var integer Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_id; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $post_id Post ID.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($post_id, $comment_id = 0)
			{
				$this->plugin = plugin();

				$this->post_id    = (integer)$post_id;
				$this->comment_id = (integer)$comment_id;

				$this->maybe_delete(); // If applicable.
			}

			/**
			 * Deletes subscribers.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_delete()
			{
				if(!$this->post_id)
					return; // Not applicable.

				$sql = "DELETE FROM `".esc_sql($this->plugin->db_prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->post_id)."'".
				       ($this->comment_id ? " AND `comment_id` = '".esc_sql($this->comment_id)."'" : '');

				$this->plugin->wpdb->query($sql); // Delete any existing subscription(s).
			}
		}
	}
}