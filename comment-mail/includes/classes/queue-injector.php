<?php
/**
 * Queue Injector
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\queue_injector'))
	{
		/**
		 * Queue Injector
		 *
		 * @since 14xxxx First documented version.
		 */
		class queue_injector extends abstract_base
		{
			/**
			 * @var \stdClass|null Comment object.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($comment_id)
			{
				parent::__construct();

				$comment_id = (integer)$comment_id;

				if($comment_id) // If possible.
					$this->comment = get_comment($comment_id);

				$this->maybe_inject();
			}

			/**
			 * Queue injections.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If an insertion failure occurs.
			 */
			protected function maybe_inject()
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
				        " (`sub_id`, `comment_id`, `comment_parent_id`, `insertion_time`, `last_update_time`, `hold_until_time`) VALUES";

				foreach($sub_ids as $_key => $_sub_id)
					$sql .= "('".esc_sql($_sub_id)."', '".esc_sql($this->comment->comment_ID)."', '".esc_sql($this->comment->comment_parent)."', '".esc_sql($time)."', '".esc_sql($time)."', '0'),";
				$sql = rtrim($sql, ','); // Trim leftover delimiter.
				unset($_key, $_sub_id); // Housekeeping.

				if(!$this->plugin->utils_db->wp->query($sql))
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));
			}

			/**
			 * Get subscribers IDs.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array All subscriber IDs.
			 */
			protected function sub_ids()
			{
				$emails = $sub_ids = array(); // Initialize.

				$sql = "SELECT `ID`, `email` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->comment->post_ID)."'".
				       " AND (`comment_id` = '0' OR `comment_id` = '".esc_sql($this->comment->comment_parent)."')".
				       " AND `status` = 'subscribed'";

				if(($subs = $this->plugin->utils_db->wp->get_results($sql)))
					$subs = $this->plugin->utils_db->typify_deep($subs);
				else $subs = array(); // Default; empty array.

				foreach($subs as $_key => $_sub)
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