<?php
/**
 * Event Utilities
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class utils_event extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Queue event log; note code to full description.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $note_code Note code to convert.
			 *
			 * @return string Full description for the code; else an empty string.
			 *
			 * @see queue_processor::log_entry()
			 */
			public function queue_note_code_desc($note_code)
			{
				switch(strtolower(trim((string)$note_code)))
				{
					/*
					 * Check primary IDs for validity.
					 */
					case 'entry_sub_id_empty':
						$note = __('Not possible; `$entry->sub_id` empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_post_id_empty':
						$note = __('Not possible; `$entry->post_id` empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'entry_comment_id_empty':
						$note = __('Not possible; `$entry->comment_id` empty.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * Now we check some basics in the subscription itself.
					 */
					case 'entry_sub_id_missing':
						$note = __('Not possible; `$entry->sub_id` missing. The subscription may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_email_empty':
						$note = __('Not possible; `$sub->email` empty. Could not notify (obviously) due to the lack of an email address.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_status_not_subscribed':
						$note = __('Not applicable; `$sub->status` not `subscribed`. The subscription status may have changed before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * Make sure the subscription still matches up with the same post/comment IDs.
					 */
					case 'sub_post_id_mismtach':
						$note = __('Not applicable; `$sub->post_id` mismatch against `$entry->post_id`. This subscription may have been altered before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_comment_id_mismatch':
						$note = __('Not applicable; `$sub->comment_id` mismatch against `$entry->comment_parent_id`. This subscription may have been altered before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * Now we check the subscription's post ID.
					 */
					case 'sub_post_id_missing':
						$note = __('Not possible; `$sub->post_id` is missing. The underlying post may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_post_title_empty':
						$note = __('Not possible; `$sub_post->post_title` empty. Nothing to use in a notification subject line; or elsewhere, because the post has no title.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_post_status_not_publish':
						$note = __('Not applicable; `$sub_post->post_status` not `publish`. The post may have been set to a `draft` (or another unpublished status) before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_post_type_auto_excluded':
						$note = __('Not applicable; `$sub_post->post_type` automatically excluded as unnotifiable. Note that revisions, nav menu items, etc; these are automatically bypassed.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * Now we check the subscription's comment ID; if applicable.
					 */
					case 'sub_comment_id_missing':
						$note = __('Not possible; `$sub->comment_id` missing. The comment they subscribed to may have been deleted before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_comment_type_not_comment':
						$note = __('Not applicable; `$sub_comment->comment_type` not empty, and not `comment`. Perhaps a pingback/trackback.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_comment_content_empty':
						$note = __('Not applicable; `$sub_comment->comment_content` empty. Not a problem in an of itself; but stopping since the comment they subscribed to is empty.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'sub_comment_status_not_approve':
						$note = __('Not applicable; `$sub_comment->comment_approved` not `approve`. The comment they subscribed to may have been marked as spam, or held for moderation before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * Make sure the comment we are notifying about still exists; and check validity.
					 */
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
					/*
					 * Make sure the post containing the comment we are notifying about still exists; and check validity.
					 */
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
					/*
					 * Again, make sure the subscription still matches up with the same post/comment IDs; and that both still exist.
					 */
					case 'sub_post_id_comment_mismtach':
						$note = __('Not applicable; `$sub->post_id` mismatch against `$comment->comment_post_ID`. This subscription may have been altered before processing began.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * These cover issues w/ headers, subject and/or message templates.
					 */
					case 'comment_notification_headers_empty':
						$note = __('Not possible; comment notification headers empty. Unknown error on headers generation.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_subject_empty':
						$note = __('Not possible; comment notification subject empty. Perhaps a missing template file/option. Please check your configuration.', $this->plugin->text_domain);
						break; // Break switch handler.

					case 'comment_notification_message_empty':
						$note = __('Not possible; comment notification message empty. Perhaps a missing template file/option. Please check your configuration.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * This covers a successfull processing.
					 */
					case 'comment_notification_sent_successfully':
						$note = __('Notification processed successfully. Email sent to subscriber.', $this->plugin->text_domain);
						break; // Break switch handler.
					/*
					 * Anything else not covered here returns no message.
					 */
					default: // Default case handler.
						$note = ''; // No note in this case.
						break; // Break switch handler.
				}
				return $note; // Code translated to description.
			}

			/**
			 * Sub event log; determine `overwritten` reason.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $row A sub event log entry row from the DB.
			 *
			 * @return string The reason (human readable) why the overwrite occurred.
			 */
			public function sub_overwritten_reason(\stdClass $row)
			{
				return '';
				// @TODO Work out a description for why it was overwritten here.
			}
		}
	}
}