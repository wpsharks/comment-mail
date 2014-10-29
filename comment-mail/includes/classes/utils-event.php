<?php
/**
 * Event Utilities
 *
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
		 * @since 14xxxx First documented version.
		 */
		class utils_event extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
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
			 * @see queue_processor::log_entry()
			 */
			public function queue_note_code($note_code)
			{
				switch(strtolower(trim((string)$note_code)))
				{
					case 'entry_sub_id_empty':
						$note = __('Not possible; `$entry->sub_id` empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_comment_id_empty':
						$note = __('Not possible; `$entry->comment_id` empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_post_id_empty':
						$note = __('Not possible; `$entry->post_id` empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_sub_id_missing':
						$note = __('Not possible; `$entry->sub_id` missing. The subscription may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_email_empty':
						$note = __('Not possible; `$sub->email` empty. Could not notify (obviously) due to the lack of an email address.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_status_not_subscribed':
						$note = __('Not applicable; `$sub->status` not `subscribed`. The subscription status may have changed before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_post_id_mismtach':
						$note = __('Not applicable; `$sub->post_id` mismatch against `$entry->post_id`. This subscription may have been altered before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_comment_id_mismatch':
						$note = __('Not applicable; `$sub->comment_id` mismatch against `$entry->comment_parent_id`. This subscription may have been altered before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_comment_id_missing':
						$note = __('Not possible; `$entry->comment_id` missing. The comment may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_type_not_comment':
						$note = __('Not applicable; `$comment->comment_type` not empty, and not `comment`. Perhaps a pingback/trackback.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_content_empty':
						$note = __('Not applicable; `$comment->comment_content` empty. Nothing to say or do because the comment message is empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_status_not_approve':
						$note = __('Not applicable; `$comment->comment_approved` not `approve`. The comment may have been marked as spam, or held for moderation before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_post_id_missing':
						$note = __('Not possible; `$comment->comment_post_ID` missing. The post may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'post_title_empty':
						$note = __('Not possible; `$post->post_title` empty. Nothing to use in a notification subject line; or elsewhere, because the post has no title.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'post_status_not_publish':
						$note = __('Not applicable; `$post->post_status` not `publish`. The post may have been set to a `draft` (or another unpublished status) before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'post_type_auto_excluded':
						$note = __('Not applicable; `$post->post_type` automatically excluded as unnotifiable. Note that revisions, nav menu items, etc; these are automatically bypassed.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_subject_empty':
						$note = __('Not possible; comment notification subject empty. Perhaps a missing template file/option. Please check your configuration.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_message_empty':
						$note = __('Not possible; comment notification message empty. Perhaps a missing template file/option. Please check your configuration.', $this->plugin->text_domain);
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