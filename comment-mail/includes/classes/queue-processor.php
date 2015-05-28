<?php
/**
 * Queue Processor
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class queue_processor extends abs_base
		{
			/**
			 * @var boolean A CRON job?
			 *
			 * @since 141111 First documented version.
			 */
			protected $is_cron;

			/**
			 * @var integer Start time.
			 *
			 * @since 141111 First documented version.
			 */
			protected $start_time;

			/**
			 * @var integer Max time (in seconds).
			 *
			 * @since 141111 First documented version.
			 */
			protected $max_time;

			/**
			 * @var integer Delay (in milliseconds).
			 *
			 * @since 141111 First documented version.
			 */
			protected $delay;

			/**
			 * @var integer Max entries to process.
			 *
			 * @since 141111 First documented version.
			 */
			protected $max_limit;

			/**
			 * @var template Subject template.
			 *
			 * @since 141111 First documented version.
			 */
			protected $subject_template;

			/**
			 * @var template Message template.
			 *
			 * @since 141111 First documented version.
			 */
			protected $message_template;

			/**
			 * @var \stdClass[] Entries being processed.
			 *
			 * @since 141111 First documented version.
			 */
			protected $entries;

			/**
			 * @var integer Total entries.
			 *
			 * @since 141111 First documented version.
			 */
			protected $total_entries;

			/**
			 * @var integer Processed entry counter.
			 *
			 * @since 141111 First documented version.
			 */
			protected $processed_entry_counter;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param boolean      $is_cron Is this a CRON job?
			 *    Defaults to a `TRUE` value. If calling directly pass `FALSE`.
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
				parent::__construct();

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
					$this->delay = 250; // Cannot be greater than max time - 5 seconds.

				if(isset($max_limit)) // Argument is set?
					$this->max_limit = (integer)$max_limit; // This takes precedence.
				else $this->max_limit = (integer)$this->plugin->options['queue_processor_max_limit'];

				if($this->max_limit < 1) $this->max_limit = 1;
				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				if($this->max_limit > $upper_max_limit) $this->max_limit = $upper_max_limit;

				$this->subject_template = new template('email/comment-notification/subject.php');
				$this->message_template = new template('email/comment-notification/message.php');

				$this->entries                 = array(); // Initialize.
				$this->total_entries           = 0; // Initialize; zero for now.
				$this->processed_entry_counter = 0; // Initialize; zero for now.

				$this->maybe_prep_cron_job();
				$this->maybe_process();
			}

			/**
			 * Prep CRON job.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_prep_cron_job()
			{
				if(!$this->is_cron)
					return; // Not applicable.

				ignore_user_abort(TRUE);

				@set_time_limit($this->max_time); // Max time only (first).
				// Doing this first in case the times below exceed an upper limit.
				// i.e. hosts may prevent this from being set higher than `$max_time`.

				// The following may not work, but we can try :-)
				if($this->delay) // Allow some extra time for the delay?
					@set_time_limit(min(300, ceil($this->max_time + ($this->delay / 1000) + 30)));
				else @set_time_limit(min(300, $this->max_time + 30));
			}

			/**
			 * Queue processor.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_process()
			{
				if(!$this->plugin->options['enable'])
					return; // Disabled currently.

				if(!$this->plugin->options['queue_processing_enable'])
					return; // Disabled currently.

				if(!($this->entries = $this->entries()))
					return; // Nothing to do.

				$this->total_entries = count($this->entries);

				foreach($this->entries as $_entry_id_key => $_entry)
				{
					$this->process_entry($_entry);
					$this->delete_entry($_entry);

					$this->processed_entry_counter++;

					if($this->is_out_of_time() || $this->is_delay_out_of_time())
						break; // Out of time now; or after a possible delay.
				}
				unset($_entry_id_key, $_entry); // Housekeeping.
			}

			/**
			 * Process entry.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 */
			protected function process_entry(\stdClass $entry)
			{
				if($entry->dby_queue_id || $entry->logged)
					return; // Already processed this.

				if(!($entry_props = $this->validated_entry_props($entry)))
					return; // Bypass; unable to validate entry props.

				if($this->check_entry_hold_until_time($entry_props))
					return; // Holding (for now); nothing more.

				$this->check_compile_entry_digestable_entries($entry_props);

				if(!($entry_headers = $this->entry_headers($entry_props)))
				{
					$entry_props->event     = 'invalidated'; // Invalidate.
					$entry_props->note_code = 'comment_notification_headers_empty';
					$this->log_entry($entry_props); // Log invalidation.

					return; // Not possible; headers are empty.
				}
				if(!($entry_subject = $this->entry_subject($entry_props)))
				{
					$entry_props->event     = 'invalidated'; // Invalidate.
					$entry_props->note_code = 'comment_notification_subject_empty';
					$this->log_entry($entry_props); // Log invalidation.

					return; // Not possible; subject line is empty.
				}
				if(!($entry_message = $this->entry_message($entry_props)))
				{
					$entry_props->event     = 'invalidated'; // Invalidate.
					$entry_props->note_code = 'comment_notification_message_empty';
					$this->log_entry($entry_props); // Log invalidation.

					return; // Not possible; message body is empty.
				}
				$entry_props->event     = 'notified'; // Notifying now.
				$entry_props->note_code = 'comment_notification_sent_successfully';
				$this->log_entry($entry_props); // Log successful processing.

				$this->plugin->utils_mail->send($entry_props->sub->email, $entry_subject, $entry_message, $entry_headers);
			}

			/**
			 * Log entry event w/ note code.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 */
			protected function log_entry(\stdClass $entry_props)
			{
				if($entry_props->logged)
					return; // Already logged this.

				if(!$entry_props->entry)
					return; // Nothing to log; no entry.

				$log_entry = array(
					'queue_id'          => $entry_props->entry->ID,
					'dby_queue_id'      => $entry_props->dby_queue_id, // Digested?

					'sub_id'            => $entry_props->sub ? $entry_props->sub->ID : $entry_props->entry->sub_id,

					'user_id'           => $entry_props->sub ? $entry_props->sub->user_id : 0, // Default; no user; not possible.
					'post_id'           => $entry_props->post ? $entry_props->post->ID : ($entry_props->comment ? $entry_props->comment->comment_post_ID : ($entry_props->sub ? $entry_props->sub->post_id : 0)),
					'comment_id'        => $entry_props->comment ? $entry_props->comment->comment_ID : $entry_props->entry->comment_id,
					'comment_parent_id' => $entry_props->comment ? $entry_props->comment->comment_parent : $entry_props->entry->comment_parent_id,

					'fname'             => $entry_props->sub ? $entry_props->sub->fname : '',
					'lname'             => $entry_props->sub ? $entry_props->sub->lname : '',
					'email'             => $entry_props->sub ? $entry_props->sub->email : '',

					'ip'                => $entry_props->sub ? $entry_props->sub->last_ip : '',
					'region'            => $entry_props->sub ? $entry_props->sub->last_region : '',
					'country'           => $entry_props->sub ? $entry_props->sub->last_country : '',

					'status'            => $entry_props->sub ? $entry_props->sub->status : '',

					'event'             => $entry_props->event,
					'note_code'         => $entry_props->note_code,
				);
				new queue_event_log_inserter($log_entry);

				$entry_props->logged        = TRUE; // Flag as `TRUE`.
				$entry_props->entry->logged = TRUE; // Flag as `TRUE`.
				if(isset($this->entries[$entry_props->entry->ID]))
					$this->entries[$entry_props->entry->ID]->logged = TRUE;

				$this->maybe_log_delete_entry_digestables($entry_props);
			}

			/**
			 * Log/record entry digestables.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 */
			protected function maybe_log_delete_entry_digestables(\stdClass $entry_props)
			{
				if(!$entry_props->entry)
					return; // Nothing to log; no entry.

				if(!$entry_props->props)
					return; // Nothing to log; no props.

				if(!$entry_props->event || $entry_props->event !== 'notified')
					return; // Nothing to do. No event, or was NOT notified.

				if(count($entry_props->props) <= 1 && isset($entry_props->props[$entry_props->entry->ID]))
					return; // Nothing to do; only one (i.e. itself).

				foreach($entry_props->props as $_entry_digestable_entry_id_key => $_entry_digestable_entry_props)
					if($_entry_digestable_entry_props->entry->ID !== $entry_props->entry->ID)
					{
						$_entry_digestable_entry_props->event     = $entry_props->event;
						$_entry_digestable_entry_props->note_code = $entry_props->note_code;

						$_entry_digestable_entry_props->dby_queue_id        = $entry_props->entry->ID;
						$_entry_digestable_entry_props->entry->dby_queue_id = $entry_props->entry->ID;

						if(isset($this->entries[$_entry_digestable_entry_props->entry->ID])) // Update these too.
							$this->entries[$_entry_digestable_entry_props->entry->ID]->dby_queue_id = $entry_props->entry->ID;

						$this->log_entry($_entry_digestable_entry_props);
						$this->delete_entry($_entry_digestable_entry_props->entry);
					}
				unset($_entry_digestable_entry_id_key, $_entry_digestable_entry_props); // Housekeeping.
			}

			/**
			 * Delete entry.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 */
			protected function delete_entry(\stdClass $entry)
			{
				$this->plugin->utils_queue->delete($entry->ID);
			}

			/**
			 * Validated entry properties.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry Queue entry.
			 *
			 * @return \stdClass|null Structured entry properties.
			 *    If unable to validate, returns `NULL`.
			 *
			 *    Object properties will include:
			 *
			 *    - `event` the event type.
			 *    - `note_code` the note code.
			 *
			 *    - `entry` the entry.
			 *
			 *    - `sub` the subscription.
			 *    - `sub_post` subscription post.
			 *    - `sub_comment` subscription comment.
			 *
			 *    - `post` the post we are notifying about.
			 *    - `comment` the comment we are notifying about.
			 *
			 *    - `props` digestable entry props.
			 *    - `comments` digestable comments.
			 *
			 *    - `held` held?
			 *    - `dby_queue_id` digested?
			 *    - `logged` logged?
			 *
			 * @see utils_event::queue_note_code_desc()
			 */
			protected function validated_entry_props(\stdClass $entry)
			{
				$sub_comment = NULL; // Initialize this.
				/*
				 * Check primary IDs for validity.
				 */
				if(!$entry->sub_id)
					$invalidated_entry_props = $this->entry_props('invalidated', 'entry_sub_id_empty', $entry);

				else if(!$entry->post_id)
					$invalidated_entry_props = $this->entry_props('invalidated', 'entry_post_id_empty', $entry);

				else if(!$entry->comment_id)
					$invalidated_entry_props = $this->entry_props('invalidated', 'entry_comment_id_empty', $entry);
				/*
				 * Now we check some basics in the subscription itself.
				 */
				else if(!($sub = $this->plugin->utils_sub->get($entry->sub_id)))
					$invalidated_entry_props = $this->entry_props('invalidated', 'entry_sub_id_missing', $entry);

				else if(!$sub->email)
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_email_empty', $entry, $sub);

				else if($sub->status !== 'subscribed')
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_status_not_subscribed', $entry, $sub);
				/*
				 * Make sure the subscription still matches up with the same post/comment IDs.
				 */
				else if($sub->post_id !== $entry->post_id)
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_post_id_mismtach', $entry, $sub);

				else if($sub->comment_id && $sub->comment_id !== $entry->comment_parent_id)
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_comment_id_mismatch', $entry, $sub);
				/*
				 * Now we check the subscription's post ID.
				 */
				else if(!($sub_post = get_post($sub->post_id)))
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_post_id_missing', $entry, $sub);

				else if(!$sub_post->post_title)
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_post_title_empty', $entry, $sub, $sub_post);

				else if($sub_post->post_status !== 'publish')
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_post_status_not_publish', $entry, $sub, $sub_post);

				else if(in_array($sub_post->post_type, array('revision', 'nav_menu_item'), TRUE))
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_post_type_auto_excluded', $entry, $sub, $sub_post);
				/*
				 * Now we check the subscription's comment ID; if applicable.
				 */
				else if($sub->comment_id && !($sub_comment = get_comment($sub->comment_id)))
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_comment_id_missing', $entry, $sub, $sub_post);

				else if($sub_comment && $sub_comment->comment_type && $sub_comment->comment_type !== 'comment')
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_comment_type_not_comment', $entry, $sub, $sub_post, $sub_comment);

				else if($sub_comment && !$sub_comment->comment_content)
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_comment_content_empty', $entry, $sub, $sub_post, $sub_comment);

				else if($sub_comment && $this->plugin->utils_db->comment_status__($sub_comment->comment_approved) !== 'approve')
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_comment_status_not_approve', $entry, $sub, $sub_post, $sub_comment);
				/*
				 * Make sure the comment we are notifying about still exists; and check validity.
				 */
				else if(!($comment = get_comment($entry->comment_id)))
					$invalidated_entry_props = $this->entry_props('invalidated', 'entry_comment_id_missing', $entry, $sub, $sub_post, $sub_comment);

				else if($comment->comment_type && $comment->comment_type !== 'comment')
					$invalidated_entry_props = $this->entry_props('invalidated', 'comment_type_not_comment', $entry, $sub, $sub_post, $sub_comment, NULL, $comment);

				else if(!$comment->comment_content)
					$invalidated_entry_props = $this->entry_props('invalidated', 'comment_content_empty', $entry, $sub, $sub_post, $sub_comment, NULL, $comment);

				else if($this->plugin->utils_db->comment_status__($comment->comment_approved) !== 'approve')
					$invalidated_entry_props = $this->entry_props('invalidated', 'comment_status_not_approve', $entry, $sub, $sub_post, $sub_comment, NULL, $comment);
				/*
				 * Make sure the post containing the comment we are notifying about still exists; and check validity.
				 */
				else if(!($post = get_post($comment->comment_post_ID)))
					$invalidated_entry_props = $this->entry_props('invalidated', 'comment_post_id_missing', $entry, $sub, $sub_post, $sub_comment, NULL, $comment);

				else if(!$post->post_title)
					$invalidated_entry_props = $this->entry_props('invalidated', 'post_title_empty', $entry, $sub, $sub_post, $sub_comment, $post, $comment);

				else if($post->post_status !== 'publish')
					$invalidated_entry_props = $this->entry_props('invalidated', 'post_status_not_publish', $entry, $sub, $sub_post, $sub_comment, $post, $comment);

				else if(in_array($post->post_type, array('revision', 'nav_menu_item'), TRUE))
					$invalidated_entry_props = $this->entry_props('invalidated', 'post_type_auto_excluded', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
				/*
				 * Again, make sure the subscription still matches up with the same post/comment IDs; and that both still exist.
				 */
				else if($sub->post_id !== (integer)$comment->comment_post_ID)
					$invalidated_entry_props = $this->entry_props('invalidated', 'sub_post_id_comment_mismtach', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
				/*
				 * Else, we can return the full set of entry properties for this queue entry.
				 */
				else return $this->entry_props('', '', $entry, $sub, $sub_post, $sub_comment, $post, $comment); // Validated entry props.
				/*
				 * Otherwise (i.e. if we get down here); we need to log the invalidation.
				 */
				if(isset($invalidated_entry_props)) // Unable to validate/initialize entry props?
					$this->log_entry($invalidated_entry_props); // Log invalidation.

				return NULL; // Unable to validate/initialize entry props.
			}

			/**
			 * Structured entry props.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string         $event Event type; `invalidated` or `notified`.
			 * @param string         $note_code See {@link utils_event::queue_note_code()}.
			 *
			 * @param \stdClass      $entry Queue entry.
			 *
			 * @param \stdClass|null $sub Subscription.
			 * @param \WP_Post|null  $sub_post Subscription post.
			 * @param \stdClass|null $sub_comment Subscription comment.
			 *
			 * @param \WP_Post|null  $post Post we are notifying about.
			 * @param \stdClass|null $comment Comment we are notifying about.
			 *
			 * @param \stdClass[]    $props Digestable entry props.
			 * @param \stdClass[]    $comments Digestable comments.
			 *
			 * @param boolean        $held Held? Defaults to `FALSE`.
			 * @param integer        $dby_queue_id Digested by queue ID.
			 * @param boolean        $logged Logged? Defaults to `FALSE`.
			 *
			 * @return \stdClass Structured entry properties.
			 *
			 *    Object properties will include:
			 *
			 *    - `event` the event type.
			 *    - `note_code` the note code.
			 *
			 *    - `entry` the entry.
			 *
			 *    - `sub` the subscription.
			 *    - `sub_post` subscription post.
			 *    - `sub_comment` subscription comment.
			 *
			 *    - `post` the post we are notifying about.
			 *    - `comment` the comment we are notifying about.
			 *
			 *    - `props` digestable entry props.
			 *    - `comments` digestable comments.
			 *
			 *    - `held` held?
			 *    - `dby_queue_id` digested?
			 *    - `logged` logged?
			 *
			 * @see utils_event::queue_note_code_desc()
			 */
			protected function entry_props($event = '',
			                               $note_code = '',

			                               \stdClass $entry,

			                               \stdClass $sub = NULL,
			                               \WP_Post $sub_post = NULL,
			                               \stdClass $sub_comment = NULL,

			                               \WP_Post $post = NULL,
			                               \stdClass $comment = NULL,

			                               array $props = array(),
			                               array $comments = array(),

			                               $held = FALSE,
			                               $dby_queue_id = 0,
			                               $logged = FALSE)
			{
				$event     = (string)$event;
				$note_code = (string)$note_code;

				if(!$comments && $comment) // Not passed in?
					$comments = array($comment->comment_ID => $comment);

				$held         = (boolean)$held;
				$dby_queue_id = (integer)$dby_queue_id;
				$logged       = (boolean)$logged;

				$entry_props = (object)compact(
					'event',
					'note_code',

					'entry',

					'sub',
					'sub_post',
					'sub_comment',

					'post',
					'comment',

					'props',
					'comments',

					'held',
					'dby_queue_id',
					'logged'
				);
				if(!$props && !$entry_props->props)
					$entry_props->props = array($entry ? $entry->ID : 0 => $entry_props);

				return $entry_props;
			}

			/**
			 * Check hold until time.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return boolean TRUE if the notification should be held, for now.
			 */
			protected function check_entry_hold_until_time(\stdClass $entry_props)
			{
				if($entry_props->sub->deliver === 'asap')
					return FALSE; // Do not hold; n/a.

				if(time() >= ($entry_hold_until_time = $this->entry_hold_until_time($entry_props)))
					return FALSE; // Don't hold any longer.

				$this->update_entry_hold_until_time($entry_props, $entry_hold_until_time);

				return TRUE; // Yes, holding this entry (for now).
			}

			/**
			 * Entry hold until time.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return integer Hold until time; UNIX timestamp.
			 */
			protected function entry_hold_until_time(\stdClass $entry_props)
			{
				switch($entry_props->sub->deliver) // Check for digests.
				{
					case 'hourly': // Delivery option = hourly digest.
						if(($entry_last_notified_time = $this->entry_last_notified_time($entry_props)))
							return $entry_last_notified_time + 3600;

					case 'daily': // Delivery option = daily digest.
						if(($entry_last_notified_time = $this->entry_last_notified_time($entry_props)))
							return $entry_last_notified_time + 86400;

					case 'weekly': // Delivery option = weekly digest.
						if(($entry_last_notified_time = $this->entry_last_notified_time($entry_props)))
							return $entry_last_notified_time + 604800;
				}
				return $entry_props->entry->hold_until_time ? $entry_props->entry->hold_until_time : $entry_props->entry->insertion_time;
			}

			/**
			 * Update hold until time.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @param integer   $entry_hold_until_time Hold until time; UNIX timestamp.
			 *
			 * @throws \exception If a DB update failure occurs.
			 */
			protected function update_entry_hold_until_time(\stdClass $entry_props, $entry_hold_until_time)
			{
				if($entry_props->held)
					return; // Already did this.

				$entry_hold_until_time = (integer)$entry_hold_until_time;

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".

				       " SET `last_update_time` = '".esc_sql(time())."', `hold_until_time` = '".esc_sql($entry_hold_until_time)."'".

				       " WHERE `ID` = '".esc_sql($entry_props->entry->ID)."'";

				if(!$this->plugin->utils_db->wp->query($sql))
					throw new \exception(__('Update failure.', $this->plugin->text_domain));

				$entry_props->entry->hold_until_time = $entry_hold_until_time;
				$entry_props->held                   = TRUE; // Flag as `TRUE` now.
			}

			/**
			 * Entry last notified time.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return integer Last notified time; UNIX timestamp.
			 */
			protected function entry_last_notified_time(\stdClass $entry_props)
			{
				$sql = "SELECT `time` FROM `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".

				       " WHERE `post_id` = '".esc_sql($entry_props->post->ID)."'".

				       (!$entry_props->sub->comment_id ? '' // If all comments; include everything.
					       : " AND `comment_parent_id` = '".esc_sql($entry_props->comment->comment_parent)."'").

				       " AND `sub_id` = '".esc_sql($entry_props->sub->ID)."'".
				       " AND `event` = 'notified'".

				       " ORDER BY `time` DESC".

				       " LIMIT 1"; // Only need the last time.

				return (integer)$this->plugin->utils_db->wp->get_var($sql);
			}

			/**
			 * Compile digestable entries.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props entry properties.
			 *
			 * @return boolean TRUE if the entry has other digestable entries.
			 */
			protected function check_compile_entry_digestable_entries(\stdClass $entry_props)
			{
				if($entry_props->sub->deliver === 'asap')
					return FALSE; // Not applicable; i.e. no other digestables.

				if(!($entry_digestable_entries = $this->entry_digestable_entries($entry_props)))
					return FALSE; // Not applicable; i.e. no other digestables.

				if(count($entry_digestable_entries) <= 1 && isset($entry_digestable_entries[$entry_props->entry->ID]))
					return FALSE; // Only itself; i.e. no other digestables.

				foreach($entry_digestable_entries as $_entry_digestable_entry_id_key => $_entry_digestable_entry)
				{
					if($_entry_digestable_entry->ID === $entry_props->entry->ID)
						$_entry_digestable_entry_props = $entry_props; // Reference original obj. props.
					else $_entry_digestable_entry_props = $this->validated_entry_props($_entry_digestable_entry);

					if($_entry_digestable_entry_props) // Include this one? i.e. do we have valid entry props?
					{
						$entry_props->props[$_entry_digestable_entry_props->entry->ID]              = $_entry_digestable_entry_props;
						$entry_props->comments[$_entry_digestable_entry_props->comment->comment_ID] = $_entry_digestable_entry_props->comment;
					}
				}
				unset($_entry_digestable_entry_id_key, $_entry_digestable_entry, $_entry_digestable_entry_props); // Housekeeping.

				return TRUE; // Yes, this entry has at least one other digestable entry.
			}

			/**
			 * Queued digestable entries.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props entry properties.
			 *
			 * @return array An array of all queued digestable entries.
			 */
			protected function entry_digestable_entries(\stdClass $entry_props)
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".

				       " WHERE `post_id` = '".esc_sql($entry_props->post->ID)."'".

				       (!$entry_props->sub->comment_id ? '' // If all comments; include everything.
					       : " AND `comment_parent_id` = '".esc_sql($entry_props->comment->comment_parent)."'").

				       " AND `sub_id` = '".esc_sql($entry_props->sub->ID)."'".

				       " ORDER BY `insertion_time` ASC"; // In chronological order.

				if(($entry_digestable_entries = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					$entry_digestable_entries = $this->plugin->utils_db->typify_deep($entry_digestable_entries);
				else $entry_digestable_entries = array(); // Default; empty array.

				foreach($entry_digestable_entries as $_entry_digestable_entry_id_key => $_entry_digestable_entry)
				{
					if($_entry_digestable_entry->ID === $entry_props->entry->ID) // Original entry?
						$entry_digestable_entries[$_entry_digestable_entry_id_key] = $entry_props->entry;

					else // Create dynamic properties for the new digestable entries compiled here.
					{
						$_entry_digestable_entry->dby_queue_id = $entry_props->entry->ID; // Dynamic property.
						$_entry_digestable_entry->logged       = FALSE; // Dynamic property; default value: `FALSE`.
					}
				}
				unset($_entry_digestable_entry_id_key, $_entry_digestable_entry); // Housekeeping.

				return $entry_digestable_entries; // All queued digestable entries.
			}

			/**
			 * Queued entries.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of up to `$this->max_limit` entries.
			 */
			protected function entries()
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".

				       " WHERE `hold_until_time` < '".esc_sql(time())."'".

				       " ORDER BY `insertion_time` ASC". // Oldest get priority.

				       " LIMIT ".$this->max_limit; // Max limit for this class instance.

				if(($entries = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					$entries = $this->plugin->utils_db->typify_deep($entries);
				else $entries = array(); // Default; empty array.

				foreach($entries as $_entry_id_key => $_entry) // Dynamic properties.
				{
					$_entry->dby_queue_id = 0; // Dynamic property; default value: `0`.
					$_entry->logged       = FALSE; // Dynamic property; default value: `FALSE`.
				}
				unset($_entry_id_key, $_entry); // Housekeeping.

				return $entries; // Up to `$this->max_limit` entries.
			}

			/**
			 * Construct entry headers.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $entry_props Entry properties.
			 *
			 * @return array Email headers for this entry.
			 */
			protected function entry_headers(\stdClass $entry_props)
			{
				$is_digest = count($entry_props->comments) > 1;

				$entry_headers[] = 'X-Post-Id: '.$entry_props->post->ID;

				if(!$is_digest) // Applicable only w/ single comment notifications.
					$entry_headers[] = 'X-Comment-Id: '.$entry_props->comment->comment_ID;

				$entry_headers[] = 'X-Sub-Key: '.$entry_props->sub->key;

				return $entry_headers; // Pass them back out now.
			}

			/**
			 * Process entry subject.
			 *
			 * @since 141111 first documented version.
			 *
			 * @param \stdClass $entry_props entry properties.
			 *
			 * @return string subject template content.
			 */
			protected function entry_subject(\stdClass $entry_props)
			{
				$template_vars = (array)$entry_props;

				return trim(preg_replace('/\s+/', ' ', $this->subject_template->parse($template_vars)));
			}

			/**
			 * Process entry message.
			 *
			 * @since 141111 first documented version.
			 *
			 * @param \stdClass $entry_props entry properties.
			 *
			 * @return string message template content.
			 */
			protected function entry_message(\stdClass $entry_props)
			{
				$template_vars = (array)$entry_props;

				$email_rve_end_divider = NULL; // Initialize.
				$template_vars         = array_merge($template_vars, compact('email_rve_end_divider'));

				return $this->message_template->parse($template_vars);
			}

			/**
			 * Out of time yet?
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean TRUE if out of time.
			 */
			protected function is_out_of_time()
			{
				if((time() - $this->start_time) >= ($this->max_time - 5))
					return TRUE; // Out of time.

				return FALSE; // Let's keep mailing!
			}

			/**
			 * Out of time after a possible delay?
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean TRUE if out of time.
			 */
			protected function is_delay_out_of_time()
			{
				if(!$this->delay) // No delay?
					return FALSE; // Nope; nothing to do here.

				if($this->processed_entry_counter >= $this->total_entries)
					return FALSE; // No delay on last entry.

				usleep($this->delay * 1000); // Delay.

				return $this->is_out_of_time();
			}
		}
	}
}
