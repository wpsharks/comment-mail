<?php
/**
 * Queue Processor
 *
 * @package queue_processor
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\queue_processor'))
	{
		/**
		 * Queue Processor
		 *
		 * @package queue_processor
		 * @since 14xxxx First documented version.
		 */
		class queue_processor // Queue processor.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var boolean Is a CRON job?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_cron; // Set by constructor.

			/**
			 * @var integer Start time.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $start_time; // Set by constructor.

			/**
			 * @var integer Max time (in seconds).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $max_time; // Set by constructor.

			/**
			 * @var integer Delay (in milliseconds).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $delay; // Set by constructor.

			/**
			 * @var integer Max entries to process.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $max_limit; // Set by constructor.

			/**
			 * @var template Subject template.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $subject_template; // Set by constructor.

			/**
			 * @var template Message template.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $message_template; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param boolean      $is_cron Is a CRON job?
			 *
			 * @param integer|null $max_time Max time (in seconds).
			 *
			 *    This cannot be less than `10` seconds.
			 *    This cannot be greater than `300` seconds.
			 *
			 *    * A default value is taken from the plugin options.
			 *
			 * @param integer|null $delay Delay (in milliseconds).
			 *
			 *    This cannot be less than `0` milliseconds.
			 *    This (converted to seconds) cannot be greater than `$max_time` - `5`.
			 *
			 *    * A default value is taken from the plugin options.
			 *
			 * @param integer|null $max_limit Max entries to process.
			 *
			 *    This cannot be less than `1`.
			 *    This cannot be greater than `1000` (filterable).
			 *
			 *    * A default value is taken from the plugin options.
			 */
			public function __construct($is_cron = TRUE, $max_time = NULL, $delay = NULL, $max_limit = NULL)
			{
				$this->plugin = plugin();

				$this->is_cron = (boolean)$is_cron;

				$this->start_time = time(); // Start time.

				if(isset($max_time)) // Argument is set?
					$this->max_time = (integer)$max_time; // This takes precedence.
				else $this->max_time = (integer)$this->plugin->options['queue_processor_max_time'];

				if($this->max_time < 10) $this->max_time = 10;
				if($this->max_time > 300) $this->max_time = 300;

				if(isset($delay)) // Argument is set?
					$this->delay = (integer)$delay; // This takes precedence.
				else $this->delay = (integer)$this->plugin->options['queue_processor_delay'];

				if($this->delay < 0) $this->delay = 0;
				if($this->delay && $this->delay / 1000 > $this->max_time - 5)
					$this->delay = 250; // Cannot be greater than max time.

				if(isset($max_limit)) // Argument is set?
					$this->max_limit = (integer)$max_limit; // This takes precedence.
				else $this->max_limit = (integer)$this->plugin->options['queue_processor_max_limit'];

				if($this->max_limit < 1) $this->max_limit = 1;
				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				if($this->max_limit > $upper_max_limit) $this->max_limit = $upper_max_limit;

				$this->subject_template = new template('email/comment-notification-subject.php');
				$this->message_template = new template('email/comment-notification-message.php');

				$this->maybe_prep_cron_job();
				$this->maybe_process();
			}

			/**
			 * Prep CRON job.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_prep_cron_job()
			{
				if(!$this->is_cron)
					return; // Nothing to do.

				ignore_user_abort(TRUE);
				@set_time_limit($this->max_time);
				@set_time_limit($this->max_time + 30);
			}

			/**
			 * Queue processor.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_process()
			{
				if(!($entries = $this->entries()))
					return; // Nothing to do.

				$total_entries = count($entries);

				foreach($entries as $_key => $_entry)
				{
					$this->maybe_process_entry($_entry);
					$this->delete_entry($_entry);

					if($this->is_out_of_time())
						return; // Out of time.

					if($this->delay && $_key + 1 < $total_entries)
						usleep($this->delay * 1000);

					if($this->is_out_of_time())
						return; // Out of time.
				}
				unset($_key, $_entry); // Housekeeping.
			}

			/**
			 * Entry processor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 */
			protected function maybe_process_entry(\stdClass $entry)
			{
				if(!$entry->sub_id) // Not possible; data missing.
					return $this->maybe_process_log_entry('invalidated', 'entry_sub_id_empty', $entry);

				if(!$entry->comment_id) // Not possible; data missing.
					return $this->maybe_process_log_entry('invalidated', 'entry_comment_id_empty', $entry);

				if(!($sub = $this->plugin->utils_sub->get($entry->sub_id))) // Deleted?
					return $this->maybe_process_log_entry('invalidated', 'entry_sub_id_missing', $entry);

				if(!$sub->email) // Missing an email address?
					return $this->maybe_process_log_entry('invalidated', 'sub_email_empty', $entry, $sub);

				if($sub->status !== 'subscribed') // Subscriber no longer `subscribed`?
					return $this->maybe_process_log_entry('invalidated', 'sub_status_not_subscribed', $entry, $sub);

				if(!($comment = get_comment($entry->comment_id))) // Comment is missing?
					return $this->maybe_process_log_entry('invalidated', 'entry_comment_id_missing', $entry, $sub);

				if($comment->comment_type !== 'comment') // It's a pingback or a trackback?
					return $this->maybe_process_log_entry('invalidated', 'comment_type_not_comment', $entry, $sub, NULL, $comment);

				if(!$comment->comment_content) // An empty commen; i.e. no content?
					return $this->maybe_process_log_entry('invalidated', 'comment_content_empty', $entry, $sub, NULL, $comment);

				if($this->plugin->comment_status__($comment->comment_approved) !== 'approve')
					return $this->maybe_process_log_entry('invalidated', 'comment_status_not_approve', $entry, $sub, NULL, $comment);

				if(!($post = get_post($comment->post_ID))) // Post is missing?
					return $this->maybe_process_log_entry('invalidated', 'comment_post_id_missing', $entry, $sub, NULL, $comment);

				if(!$post->post_title) // An empty post title; i.e. we have nothing for a subject line?
					return $this->maybe_process_log_entry('invalidated', 'post_title_empty', $entry, $sub, $post, $comment);

				if($post->post_status !== 'publish') // Unavailable; i.e. not published?
					return $this->maybe_process_log_entry('invalidated', 'post_status_not_publish', $entry, $sub, $post, $comment);

				if(in_array($post->post_type, array('revision', 'nav_menu_item'), TRUE))
					return $this->maybe_process_log_entry('invalidated', 'post_type_auto_excluded', $entry, $sub, $post, $comment);

				if(!($subject = $this->entry_subject($entry, $sub, $post, $comment)))
					return $this->maybe_process_log_entry('invalidated', 'comment_notification_subject_empty', $entry, $sub, $post, $comment);

				if(!($message = $this->entry_message($entry, $sub, $post, $comment)))
					return $this->maybe_process_log_entry('invalidated', 'comment_notification_message_empty', $entry, $sub, $post, $comment);

				$this->plugin->utils_mail->send($sub->email, $subject, $message); // Send notification to subscriber.
				return $this->maybe_process_log_entry('notified', 'comment_notification_sent_successfully', $entry, $sub, $post, $comment);
			}

			/**
			 * Event log entry pprocessor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string    $event Event type; `invalidated` or `notified`.
			 * @param string    $note_code Optional. See {@link utils_event::queue_note_code()}.
			 *
			 * @param \stdClass $entry Queue entry.
			 * @param \stdClass $sub Subscriber.
			 * @param \WP_Post  $post Post.
			 * @param \stdClass $comment Comment.
			 *
			 * @see utils_event::queue_note_code()
			 */
			protected function maybe_process_log_entry($event, $note_code = '', \stdClass $entry, \stdClass $sub = NULL, \WP_Post $post = NULL, \stdClass $comment = NULL)
			{
				if(!$entry->ID)
					return; // Not possible.

				if(!($event = trim((string)$event)))
					return; // Not possible.

				$entry = array(
					'queue_id'   => $entry->ID,
					'sub_id'     => $entry->sub_id,
					'user_id'    => $sub ? $sub->user_id : 0,
					'post_id'    => $post ? $post->ID : ($sub ? $sub->post_id : 0),
					'comment_id' => $entry->comment_id,// @TODO Consider comment parent ID.

					'fname'      => $sub ? $sub->fname : '',
					'lname'      => $sub ? $sub->lname : '',
					'email'      => $sub ? $sub->email : '',
					'ip'         => $sub ? $sub->last_ip : '',

					'event'      => (string)$event,
					'note_code'  => (string)$note_code
				);
				new queue_event_log_inserter($entry);
			}

			/**
			 * Entry subject.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 * @param \stdClass $sub Subscriber.
			 * @param \WP_Post  $post Post.
			 * @param \stdClass $comment Comment.
			 *
			 * @return string Subject template content.
			 */
			protected function entry_subject(\stdClass $entry, \stdClass $sub, \WP_Post $post, \stdClass $comment)
			{
				return $this->subject_template->parse(compact('sub', 'post', 'comment'));
			}

			/**
			 * Entry message.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 * @param \stdClass $sub Subscriber.
			 * @param \WP_Post  $post Post.
			 * @param \stdClass $comment Comment.
			 *
			 * @return string Message template content.
			 */
			protected function entry_message(\stdClass $entry, \stdClass $sub, \WP_Post $post, \stdClass $comment)
			{
				return $this->message_template->parse(compact('sub', 'post', 'comment'));
			}

			/**
			 * Delete entry.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 *
			 * @throws \exception If unable to delete entry.
			 */
			protected function delete_entry(\stdClass $entry)
			{
				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".

				       " WHERE `ID` = '".esc_sql($entry->ID)."'";

				if(!$this->plugin->utils_db->wp->query($sql)) // Deletion failure.
					throw new \exception(sprintf(__('Queue entry deletion failure. ID: `%1$s`.', $this->plugin->text_domain), $entry->ID));
			}

			/**
			 * Get all queued entries.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of up to `$this->max_limit` entries.
			 */
			protected function entries()
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".
				       " ORDER BY `insertion_time` ASC LIMIT ".$this->max_limit;

				if(($entries = $this->plugin->utils_db->wp->get_results($sql)))
					$entries = $this->plugin->utils_db->typify_deep($entries);

				return $entries ? $entries : array();
			}

			/**
			 * Out of time yet?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function is_out_of_time()
			{
				if((time() - $this->start_time) >= ($this->max_time - 5))
					return TRUE; // Out of time.

				return FALSE; // Let's keep mailing!
			}
		}
	}
}