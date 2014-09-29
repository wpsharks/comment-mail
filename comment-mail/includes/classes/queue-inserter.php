<?php
/**
 * Queue Inserter
 *
 * @package queue_inserter
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\queue_inserter'))
	{
		/**
		 * Queue Inserter
		 *
		 * @package queue_inserter
		 * @since 14xxxx First documented version.
		 */
		class queue_inserter // Queue inserter.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var \stdClass|null Comment object.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($comment_id)
			{
				$this->plugin = plugin();

				$comment_id = (integer)$comment_id;

				if($comment_id) // If possible.
					$this->comment = get_comment($comment_id);

				$this->maybe_insert();
			}

			/**
			 * Queue insertions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert()
			{
				if(!$this->comment)
					return; // Not applicable.

				if(!$this->comment->post_ID)
					return; // Not applicable.

				if(!$this->comment->comment_ID)
					return; // Not applicable.

				if(!($sub_ids = $this->sub_ids()))
					return; // No subscribers.

				$time = time(); // Current timestamp.
				$sql  = "INSERT INTO `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".
				        " (`sub_id`, `comment_id`, `insertion_time`) VALUES";

				foreach($sub_ids as $_key => $_sub_id)
					$sql .= "('".esc_sql($_sub_id)."', '".esc_sql($this->comment->comment_ID)."', '".esc_sql($time)."'),";
				$sql = rtrim($sql, ','); // Trim leftover delimiter.
				unset($_key, $_sub_id); // Housekeeping.

				$this->plugin->utils_db->wp->query($sql); // Bulk insertions.
			}

			/**
			 * Get subscribers IDs.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array All subscriber IDs.
			 */
			protected function sub_ids()
				// @TODO Consider comment parent ID.
				// @TODO Add a daily digest option here; perhaps a new DB column for this.
			{
				$emails = $sub_ids = array(); // Initialize.

				$sql = "SELECT `ID`, `email` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->comment->post_ID)."'".
				       " AND (`comment_id` = '0' OR `comment_id` = '".esc_sql($this->comment->comment_ID)."')".
				       " AND `status` = 'subscribed'".

				       " ORDER BY `last_update_time` DESC";

				if(($subs = $this->plugin->utils_db->wp->get_results($sql)))
					$subs = $this->plugin->utils_db->typify_deep($subs);

				if($subs) foreach($subs as $_key => $_sub)
				{
					if(!$_sub->email) // Email empty?
						continue; // Missing email address.

					$_email_lowercase = strtolower($_sub->email);

					if(isset($emails[$_email_lowercase]))
						continue; // Email duplicate.

					if(strcasecmp($_email_lowercase, $this->comment->comment_author_email) === 0)
						continue; // Don't send an email to the comment author.

					$emails[$_email_lowercase] = -1;
					$sub_ids[]                 = $_sub->ID;
				}
				unset($_key, $_sub, $_email_lowercase); // Housekeeping.

				return $sub_ids; // All valid/unique subscriber IDs.
			}
		}
	}
}