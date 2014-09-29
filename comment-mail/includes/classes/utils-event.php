<?php
/**
 * Event Utilities
 *
 * @package utils_event
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_event'))
	{
		/**
		 * Event Utilities
		 *
		 * @package utils_event
		 * @since 14xxxx First documented version.
		 */
		class utils_event // Event utilities.
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
			}

			/**
			 * Queue event log; note code to full description.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $note_code Note code to convert.
			 *
			 * @return string Full description for the code; else an empty string.
			 *
			 * @see queue_processor::process_log_entry()
			 */
			public function queue_note_code($note_code)
			{
				switch(strtolower(trim((string)$note_code)))
				{
					case 'entry_sub_id_empty':
						$note = __('Not possible; `$entry->sub_id` was empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_comment_id_empty':
						$note = __('Not possible; `$entry->comment_id` was empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_sub_id_missing':
						$note = __('Not possible; `$entry->sub_id` was missing. The subscriber was deleted (or unsubscribed) before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_email_empty':
						$note = __('Not possible; `$sub->email` was empty. Could not notify the subscriber due to the lack of an email address.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_status_not_subscribed':
						$note = __('Not applicable; `$sub->status` was no longer `subscribed`. The user may have been unsubscribed (or suspended) before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_comment_id_missing':
						$note = __('Not possible; `$entry->comment_id` was missing. The comment may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_type_not_comment':
						$note = __('Not applicable; `$comment->comment_type` was not `comment`. Perhaps it was a pingback/trackback.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_content_empty':
						$note = __('Not applicable; `$comment->comment_content` was empty. There was nothing to say or do.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_status_not_approve':
						$note = __('Not applicable; `$comment->comment_approved` was not `approve`. The comment may have been marked as spam, or held for moderation before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_post_id_missing':
						$note = __('Not possible; `$comment->post_ID` was missing. The post may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'post_title_empty':
						$note = __('Not possible; `$post->post_title` was empty. There was nothing to use in a notification subject line.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'post_status_not_publish':
						$note = __('Not applicable; `$post->post_status` was not `publish`. The post may have been set to a `draft` (or another unpublished status) before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'post_type_auto_excluded':
						$note = __('Not applicable; `$post->post_type` was automatically excluded as unnotifiable. Note that revisions, nav menu items, etc; these are automatically bypassed.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_subject_empty':
						$note = __('Not possible; the comment notification subject was empty. Perhaps a missing template file/option. Please check your configuration.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_message_empty':
						$note = __('Not possible; the comment notification message was empty. Perhaps a missing template file/option. Please check your configuration.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_sent_successfully':
						$note = __('Notification processed successfully. Email sent to subscriber.', $this->plugin->text_domain);
						break; // Break switch handler.

					default: // Default case handler.
						$note = ''; // No note in this case.
						break; // Break switch handler.
				}
				return $note; // Code translated to description.
			}
		}
	}
}