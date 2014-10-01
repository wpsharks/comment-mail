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

				@set_time_limit($this->max_time); // Max time only (first).
				// Doing this first in case the times below exceed an upper limit.
				// i.e. hosts may prevent this from being set higher than `$max_time`.

				// The following may not work, but we can try :-)
				if($this->delay) // Allow some extra time for the delay?
					@set_time_limit(ceil($this->max_time + ($this->delay / 1000) + 30));
				else @set_time_limit($this->max_time + 30);
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
					$this->process_entry($_entry);
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
			 * Process entry.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 */
			protected function process_entry(\stdClass $entry)
			{
				$entry_props = $this->compile_check_entry_props($entry);

				if($entry_props->event === 'invalidated')
				{
					return $this->process_log_entry($entry_props);
				}
				if(($entry_hold_until_time = $this->check_entry_hold_until_time($entry_props)))
				{
					return $this->update_entry_hold_until_time($entry_props, $entry_hold_until_time);
				}
				if(!($entry_subject = $this->entry_subject($entry_props)))
				{
					$entry_props->event     = 'invalidated';
					$entry_props->note_code = 'comment_notification_subject_empty';

					return $this->process_log_entry($entry_props);
				}
				if(!($entry_message = $this->entry_message($entry_props)))
				{
					$entry_props->event     = 'invalidated';
					$entry_props->note_code = 'comment_notification_message_empty';

					return $this->process_log_entry($entry_props);
				}
				$this->plugin->utils_mail->send($entry_props->sub->email, $entry_subject, $entry_message);

				$entry_props->event     = 'notified';
				$entry_props->note_code = 'comment_notification_sent_successfully';

				return $this->process_log_entry($entry_props);
			}

			/**
			 * Event log entry processor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 */
			protected function process_log_entry(\stdClass $entry_props)
			{
				$entry = array(
					'queue_id'   => $entry_props->entry->ID,
					'sub_id'     => $entry_props->sub ? $entry_props->sub->ID : $entry_props->entry->sub_id,
					'user_id'    => $entry_props->sub ? $entry_props->sub->user_id : 0, // Default; no user.
					'post_id'    => $entry_props->post ? $entry_props->post->ID : ($entry_props->comment ? $entry_props->comment->post_ID : ($entry_props->sub ? $entry_props->sub->post_id : 0)),
					'comment_id' => $entry_props->comment ? $entry_props->comment->comment_ID : $entry_props->entry->comment_id,

					'fname'      => $entry_props->sub ? $entry_props->sub->fname : '',
					'lname'      => $entry_props->sub ? $entry_props->sub->lname : '',
					'email'      => $entry_props->sub ? $entry_props->sub->email : '',
					'ip'         => $entry_props->sub ? $entry_props->sub->last_ip : '',

					'event'      => $entry_props->event,
					'note_code'  => $entry_props->note_code
				);
				new queue_event_log_inserter($entry);
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
			 * Compile/check entry properties.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 *
			 * @return object Object with properties.
			 *
			 *    Object properties will include:
			 *
			 *    - `event` the event type.
			 *    - `note_code` the note code.
			 *    - `entry` the entry.
			 *    - `sub` the subscriber.
			 *    - `post` the post.
			 *    - `comment` the comment.
			 *
			 * @see utils_event::queue_note_code()
			 */
			protected function compile_check_entry_props(\stdClass $entry)
			{
				if(!$entry->sub_id) // Not possible; data missing.
					return $this->checked_entry_props('invalidated', 'entry_sub_id_empty', $entry);

				if(!$entry->comment_id) // Not possible; data missing.
					return $this->checked_entry_props('invalidated', 'entry_comment_id_empty', $entry);

				if(!($sub = $this->plugin->utils_sub->get($entry->sub_id))) // Unsubscribed?
					return $this->checked_entry_props('invalidated', 'entry_sub_id_missing', $entry);

				if(!$sub->email) // Missing the subscriber's email address?
					return $this->checked_entry_props('invalidated', 'sub_email_empty', $entry, $sub);

				if($sub->status !== 'subscribed') // Subscriber no longer `subscribed`?
					return $this->checked_entry_props('invalidated', 'sub_status_not_subscribed', $entry, $sub);

				if(!($comment = get_comment($entry->comment_id))) // Comment is missing?
					return $this->checked_entry_props('invalidated', 'entry_comment_id_missing', $entry, $sub);

				if($comment->comment_type !== 'comment') // It's a pingback or a trackback?
					return $this->checked_entry_props('invalidated', 'comment_type_not_comment', $entry, $sub, NULL, $comment);

				if(!$comment->comment_content) // An empty commen; i.e. no content?
					return $this->checked_entry_props('invalidated', 'comment_content_empty', $entry, $sub, NULL, $comment);

				if($this->plugin->comment_status__($comment->comment_approved) !== 'approve')
					return $this->checked_entry_props('invalidated', 'comment_status_not_approve', $entry, $sub, NULL, $comment);

				if(!($post = get_post($comment->post_ID))) // Post is missing?
					return $this->checked_entry_props('invalidated', 'comment_post_id_missing', $entry, $sub, NULL, $comment);

				if(!$post->post_title) // An empty post title; i.e. we have nothing for a subject line?
					return $this->checked_entry_props('invalidated', 'post_title_empty', $entry, $sub, $post, $comment);

				if($post->post_status !== 'publish') // Unavailable; i.e. not published?
					return $this->checked_entry_props('invalidated', 'post_status_not_publish', $entry, $sub, $post, $comment);

				if(in_array($post->post_type, array('revision', 'nav_menu_item'), TRUE))
					return $this->checked_entry_props('invalidated', 'post_type_auto_excluded', $entry, $sub, $post, $comment);

				return $this->checked_entry_props('', '', $entry, $sub, $post, $comment);
			}

			/**
			 * Structure entry props.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string    $event Event type; `invalidated` or `notified`.
			 * @param string    $note_code See {@link utils_event::queue_note_code()}.
			 *
			 * @param \stdClass $entry Queue entry.
			 * @param \stdClass $sub Subscriber.
			 * @param \WP_Post  $post Post.
			 * @param \stdClass $comment Comment.
			 *
			 * @return object Object with properties.
			 *
			 *    Object properties will include:
			 *
			 *    - `event` the event type.
			 *    - `note_code` the note code.
			 *    - `entry` the entry.
			 *    - `sub` the subscriber.
			 *    - `post` the post.
			 *    - `comment` the comment.
			 *
			 * @see utils_event::queue_note_code()
			 */
			protected function checked_entry_props($event = '', $note_code = '', \stdClass $entry, \stdClass $sub = NULL, \WP_Post $post = NULL, \stdClass $comment = NULL)
			{
				$event     = (string)$event;
				$note_code = (string)$note_code;

				return (object)compact('event', 'note_code', 'entry', 'sub', 'post', 'comment');
			}

			/**
			 * Check hold until time.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return boolean TRUE if the notification should be hold, for now.
			 */
			protected function check_entry_hold_until_time(\stdClass $entry_props)
			{
				if($entry_props->sub->deliver !== 'asap')
					if($entry_props->entry->time < ($entry_hold_until_time = $this->entry_hold_until_time($entry_props)))
						return $entry_hold_until_time;

				return 0; // No need to hold this one.
			}

			/**
			 * Entry hold until time.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return integer Hold until time; UNIX timestamp.
			 */
			protected function entry_hold_until_time(\stdClass $entry_props)
			{
				switch($entry_props->sub->deliver) // Check for digests.
				{
					case 'hourly': // Delivery cycle/format = hourly digest.
						if(($entry_post_sub_last_notified_time = $this->entry_post_sub_last_notified_time($entry_props)))
							return $entry_post_sub_last_notified_time + 3600;

					case 'daily': // Delivery cycle/format = daily digest.
						if(($entry_post_sub_last_notified_time = $this->entry_post_sub_last_notified_time($entry_props)))
							return $entry_post_sub_last_notified_time + 86400;

					case 'weekly': // Delivery cycle/format = weekly digest.
						if(($entry_post_sub_last_notified_time = $this->entry_post_sub_last_notified_time($entry_props)))
							return $entry_post_sub_last_notified_time + 604800;
				}
				return $entry_props->entry->hold_until_time ? $entry_props->entry->hold_until_time : $entry_props->entry->time;
			}

			/**
			 * Update hold until time.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @param integer   $entry_hold_until_time Hold until time; UNIX timestamp.
			 *
			 * @throws \exception If a DB update failure occurs.
			 */
			protected function update_entry_hold_until_time(\stdClass $entry_props, $entry_hold_until_time)
			{
				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".
				       " SET `last_update_time` = '".esc_sql(time())."', `hold_until_time` = '".esc_sql((integer)$entry_hold_until_time)."'".

				       " WHERE `ID` = '".esc_sql($entry_props->entry->ID)."'";

				if(!$this->plugin->utils_db->wp->query($sql)) // Update failure.
					throw new \exception(sprintf(__('Dntry update failure. ID: `%1$s`.', $this->plugin->text_domain), $entry_props->entry->ID));

				$entry_props->entry->hold_until_time = (integer)$entry_hold_until_time;
			}

			/**
			 * Post sub. last notified time.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return integer Last notified time; UNIX timestamp.
			 */
			protected function entry_post_sub_last_notified_time(\stdClass $entry_props)
			{
				$sql = "SELECT `time` FROM `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".

				       " WHERE `post_id` = '".esc_sql($entry_props->post->ID)."'".

				       ($entry_props->sub->user_id // Sub. has a user ID?
					       ? " AND (`user_id` = '".esc_sql($entry_props->sub->user_id)."'".
					         "       OR `email` = '".esc_sql($entry_props->sub->email)."')"
					       : " AND `email` = '".esc_sql($entry_props->sub->email)."'").

				       " AND `event` = 'notified'". // Event type.

				       " ORDER BY `time` DESC LIMIT 1";

				return (integer)$this->plugin->utils_db->wp->get_var($sql);
			}

			/**
			 * process entry subject.
			 *
			 * @since 14xxxx first documented version.
			 *
			 * @param \stdclass $entry_props entry properties.
			 *
			 * @return string subject template content.
			 */
			protected function entry_subject(\stdclass $entry_props)
			{
				return $this->subject_template->parse(array('sub' => $entry_props->sub, 'post' => $entry_props->post, 'comment' => $entry_props->comment));
			}

			/**
			 * process entry message.
			 *
			 * @since 14xxxx first documented version.
			 *
			 * @param \stdclass $entry_props entry properties.
			 *
			 * @return string message template content.
			 */
			protected function entry_message(\stdclass $entry_props)
			{
				return $this->message_template->parse(array('sub' => $entry_props->sub, 'post' => $entry_props->post, 'comment' => $entry_props->comment));
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

				       " WHERE `hold_until_time` < '".esc_sql(time())."'".

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