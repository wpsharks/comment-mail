<?php
/**
 * Event Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Event Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsEvent extends AbsBase
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
     * Queue event log; provide notified details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A queue event log entry row from the DB.
     *
     * @return string Details about why a notified event occurs; in the form of a `[?]` link.
     */
    public function queueNotifiedQLink(\stdClass $row)
    {
        $details = '<h3 style="margin-top:0;">'.sprintf(__('Queue Entry ID #%1$s was processed (notified) successfully on %2$s', 'comment-mail'), esc_html($row->queue_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p>'.__('When a queue entry is processed (notified) it means that an email notification was sent successfully; i.e. the subscriber was sent an email with details about a new reply on a specific post (or comment) they subscribed to.', 'comment-mail').'</p>'.
                   '<p style="font-weight:bold;">'.__('This email notification was sent to:', 'comment-mail').'</p>'.
                   '<ul style="margin-bottom:0;">'.
                   ' <li><code>'.esc_html($row->email).'</code></li>'.
                   '</ul>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
    }

    /**
     * Queue event log; provide invalidated details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A queue event log entry row from the DB.
     *
     * @return string Details about why an invalidated event occurs; in the form of a `[?]` link.
     */
    public function queueInvalidatedQLink(\stdClass $row)
    {
        $note = $this->plugin->utils_event->queueNoteCodeDesc($row->note_code);
        $note = $this->plugin->utils_string->markdownNoP($note);

        $details = '<h3 style="margin-top:0;">'.sprintf(__('Queue Entry ID #%1$s was invalidated %2$s', 'comment-mail'), esc_html($row->queue_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p>'.__('An invalidation occurs whenever there is an unexpected scenario encountered during queue processing. This happens from time-to-time; i.e. it\'s usually not something to be alarmed about. For example, an invalidation may occur because you deleted a post, or comment, before a notification (already in the queue) was actually processed. It\'s not possible to send a notification regarding a post/comment that no longer exists, so an invalidation is actually a good thing in case like this. That\'s just one example, but it gives an idea of what can cause an invalidation.', 'comment-mail').'</p>'.
                   '<p style="font-weight:bold;">'.__('This particular invalidation occured because:', 'comment-mail').'</p>'.
                   '<ul class="pmp-list-items" style="margin-bottom:0;">'.
                   ' <li><code>'.esc_html($row->note_code).'</code> — '.$note.'</li>'.
                   '</ul>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
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
     * @see   QueueProcessor::logEntry()
     */
    public function queueNoteCodeDesc($note_code)
    {
        switch (strtolower(trim((string) $note_code))) {
            /*
             * Check primary IDs for validity.
             */
            case 'entry_sub_id_empty':
                $note = __('Not possible; `$entry->sub_id` empty.', 'comment-mail');
                break; // Break switch handler.

            case 'entry_post_id_empty':
                $note = __('Not possible; `$entry->post_id` empty.', 'comment-mail');
                break; // Break switch handler.

            case 'entry_comment_id_empty':
                $note = __('Not possible; `$entry->comment_id` empty.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Now we check some basics in the subscription itself.
             */
            case 'entry_sub_id_missing':
                $note = __('Not possible; `$entry->sub_id` missing. The subscription may have been deleted before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_email_empty':
                $note = __('Not possible; `$sub->email` empty. Could not notify (obviously) due to the lack of an email address.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_status_not_subscribed':
                $note = __('Not applicable; `$sub->status` not `subscribed`. The subscription status may have changed before processing began.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Make sure the subscription still matches up with the same post/comment IDs.
             */
            case 'sub_post_id_mismtach':
                $note = __('Not applicable; `$sub->post_id` mismatch against `$entry->post_id`. This subscription may have been altered before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_comment_id_mismatch':
                $note = __('Not applicable; `$sub->comment_id` mismatch against `$entry->comment_parent_id`. This subscription may have been altered before processing began.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Now we check the subscription's post ID.
             */
            case 'sub_post_id_missing':
                $note = __('Not possible; `$sub->post_id` is missing. The underlying post may have been deleted before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_post_title_empty':
                $note = __('Not possible; `$sub_post->post_title` empty. Nothing to use in a notification subject line; or elsewhere, because the post has no title.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_post_status_not_publish':
                $note = __('Not applicable; `$sub_post->post_status` not `publish`. The post may have been set to a `draft` (or another unpublished status) before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_post_type_auto_excluded':
                $note = __('Not applicable; `$sub_post->post_type` automatically excluded as unnotifiable. Note that revisions, nav menu items, etc; these are automatically bypassed.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Now we check the subscription's comment ID; if applicable.
             */
            case 'sub_comment_id_missing':
                $note = __('Not possible; `$sub->comment_id` missing. The comment they subscribed to may have been deleted before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_comment_type_not_comment':
                $note = __('Not applicable; `$sub_comment->comment_type` not empty, and not `comment`. Perhaps a pingback/trackback.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_comment_content_empty':
                $note = __('Not applicable; `$sub_comment->comment_content` empty. Not a problem in an of itself; but stopping since the comment they subscribed to is empty.', 'comment-mail');
                break; // Break switch handler.

            case 'sub_comment_status_not_approve':
                $note = __('Not applicable; `$sub_comment->comment_approved` not `approve`. The comment they subscribed to may have been marked as spam, or held for moderation before processing began.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Make sure the comment we are notifying about still exists; and check validity.
             */
            case 'entry_comment_id_missing':
                $note = __('Not possible; `$entry->comment_id` missing. The comment may have been deleted before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'comment_type_not_comment':
                $note = __('Not applicable; `$comment->comment_type` not empty, and not `comment`. Perhaps a pingback/trackback.', 'comment-mail');
                break; // Break switch handler.

            case 'comment_content_empty':
                $note = __('Not applicable; `$comment->comment_content` empty. Nothing to say or do because the comment message is empty.', 'comment-mail');
                break; // Break switch handler.

            case 'comment_status_not_approve':
                $note = __('Not applicable; `$comment->comment_approved` not `approve`. The comment may have been marked as spam, or held for moderation before processing began.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Make sure the post containing the comment we are notifying about still exists; and check validity.
             */
            case 'comment_post_id_missing':
                $note = __('Not possible; `$comment->comment_post_ID` missing. The post may have been deleted before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'post_title_empty':
                $note = __('Not possible; `$post->post_title` empty. Nothing to use in a notification subject line; or elsewhere, because the post has no title.', 'comment-mail');
                break; // Break switch handler.

            case 'post_status_not_publish':
                $note = __('Not applicable; `$post->post_status` not `publish`. The post may have been set to a `draft` (or another unpublished status) before processing began.', 'comment-mail');
                break; // Break switch handler.

            case 'post_type_auto_excluded':
                $note = __('Not applicable; `$post->post_type` automatically excluded as unnotifiable. Note that revisions, nav menu items, etc; these are automatically bypassed.', 'comment-mail');
                break; // Break switch handler.
            /*
             * Again, make sure the subscription still matches up with the same post/comment IDs; and that both still exist.
             */
            case 'sub_post_id_comment_mismtach':
                $note = __('Not applicable; `$sub->post_id` mismatch against `$comment->comment_post_ID`. This subscription may have been altered before processing began.', 'comment-mail');
                break; // Break switch handler.
            /*
             * These cover issues w/ headers, subject and/or message templates.
             */
            case 'comment_notification_headers_empty':
                $note = __('Not possible; comment notification headers empty. Unknown error on headers generation.', 'comment-mail');
                break; // Break switch handler.

            case 'comment_notification_subject_empty':
                $note = __('Not possible; comment notification subject empty. Perhaps a missing template file/option. Please check your configuration.', 'comment-mail');
                break; // Break switch handler.

            case 'comment_notification_message_empty':
                $note = __('Not possible; comment notification message empty. Perhaps a missing template file/option. Please check your configuration.', 'comment-mail');
                break; // Break switch handler.
            /*
             * This covers a successfull processing.
             */
            case 'comment_notification_sent_successfully':
                $note = __('Notification processed successfully. Email sent to subscriber.', 'comment-mail');
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
     * Sub event log; provide inserted details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Details about why an insertion occurs; in the form of a `[?]` link.
     */
    public function subInsertedQLink(\stdClass $row)
    {
        $details = '<h3 style="margin-top:0;">'.sprintf(__('Subscr. ID #%1$s was inserted %2$s', 'comment-mail'), esc_html($row->sub_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p style="margin-bottom:0;">'.__('An insertion occurs whenever a new subscription is added to the database.', 'comment-mail').'</p>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
    }

    /**
     * Sub event log; provide a summary of updates.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Summary of the updates that occured in this event.
     */
    public function subUpdatedSummary(\stdClass $row)
    {
        if (is_null($keys = &$this->cacheKey(__FUNCTION__, 'keys'))) {
            $keys = [
                'key' => __('Subscr. Key', 'comment-mail'),

                'user_id'    => __('WP User ID', 'comment-mail'),
                'post_id'    => __('Post ID', 'comment-mail'),
                'comment_id' => __('Comment ID', 'comment-mail'),

                'status'  => __('Status', 'comment-mail'),
                'deliver' => __('Delivery', 'comment-mail'),

                'fname' => __('First Name', 'comment-mail'),
                'lname' => __('Last Name', 'comment-mail'),
                'email' => __('Email Address', 'comment-mail'),

                'ip'      => __('IP Address', 'comment-mail'),
                'region'  => __('IP Region', 'comment-mail'),
                'country' => __('IP Country', 'comment-mail'),
            ];
        }
        $change_counter = 1; // Initialize; last update time always changes.

        foreach ($keys as $_key => $_label) {
            if (isset($row->{$_key}, $row->{$_key.'_before'})) {
                if ($row->{$_key} !== $row->{$_key.'_before'}) {
                    ++$change_counter;
                }
            }
        }
        unset($_key, $_label); // Housekeeping.

        return '<span>'.sprintf(_n('%1$s change', '%1$s changes', $change_counter, 'comment-mail'), esc_html($change_counter)).'</span>';
    }

    /**
     * Sub event log; provide a dynamic changelog for updates.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Dynamic changelog for updates; in the form of a `[?]` link.
     */
    public function subUpdatedQLink(\stdClass $row)
    {
        if (is_null($keys = &$this->cacheKey(__FUNCTION__, 'keys'))) {
            $keys = [
                'key' => __('Subscr. Key', 'comment-mail'),

                'user_id'    => __('WP User ID', 'comment-mail'),
                'post_id'    => __('Post ID', 'comment-mail'),
                'comment_id' => __('Comment ID', 'comment-mail'),

                'status'  => __('Status', 'comment-mail'),
                'deliver' => __('Delivery', 'comment-mail'),

                'fname' => __('First Name', 'comment-mail'),
                'lname' => __('Last Name', 'comment-mail'),
                'email' => __('Email Address', 'comment-mail'),

                'ip'      => __('IP Address', 'comment-mail'),
                'region'  => __('IP Region', 'comment-mail'),
                'country' => __('IP Country', 'comment-mail'),
            ];
        }
        $change_lis = []; // Initialize.

        foreach ($keys as $_key => $_label) {
            if (isset($row->{$_key}, $row->{$_key.'_before'})) {
                if ($row->{$_key} !== $row->{$_key.'_before'}) {
                    $change_lis[] = '<li>'.// Details what was changed, and what it was changed to.
                                    ' '.sprintf(__('%1$s was changed from <code>%2$s</code> to: <code>%3$s</code>', 'comment-mail'), esc_html($_label), esc_html($row->{$_key.'_before'}), esc_html($row->{$_key})).
                                    '</li>';
                }
            }
        }
        unset($_key, $_label); // Housekeeping.

        $change_lis[] = '<li>'.// Show this to avoid an empty set of results in cases where nothing else changed at all.
                        ' '.sprintf(__('%1$s was changed to: <code>%2$s</code>', 'comment-mail'), esc_html(__('Last Update Time', 'comment-mail')), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).
                        '</li>';

        $changelog = '<h3 style="margin-top:0;">'.sprintf(__('Subscr. ID #%1$s was updated %2$s', 'comment-mail'), esc_html($row->sub_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                     '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                     '<p style="font-weight:bold;">'._n('The following change occurred:', 'The following changes occurred:', count($change_lis), 'comment-mail').'</p>'.
                     '<ul class="pmp-list-items" style="margin-bottom:0;">'.
                     ' '.implode('', $change_lis).
                     '</ul>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($changelog).'">'.__('[?]', 'comment-mail').'</a>';
    }

    /**
     * Sub event log; provide overwritten details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Details about why an overwrite may occur from time-to-time; in the form of a `[?]` link.
     */
    public function subOverwrittenQLink(\stdClass $row)
    {
        $details = '<h3 style="margin-top:0;">'.sprintf(__('Subscr. ID #%1$s was overwritten by Subscr. ID #%2$s', 'comment-mail'), esc_html($row->sub_id), esc_html($row->oby_sub_id)).'</h3>'.
                   '<p>'.__('An overwrite occurs automatically whenever a subscription is a duplicate (or in conflict) with another.', 'comment-mail').
                   ' '.sprintf(__('Nothing to be alarmed about. It\'s common for this to occur from time-to-time. It\'s %1$s&trade; doing it\'s job to prevent duplicate and/or conflicting subscriptions.', 'comment-mail'), esc_html(NAME)).'</p>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p style="font-weight:bold;">'.__('Here are a few examples of why an overwrite may occur:', 'comment-mail').'</p>'.
                   '<ul class="pmp-list-items" style="margin-bottom:0;">'.
                   ' <li>'.__('Same email, same post ID, same comment ID. For instance, if a new subscription is created (or an existing subscription is updated), where it becomes an exact duplicate of another; the subscription being created/updated will take precedence.', 'comment-mail').'</li>'.
                   ' <li>'.__('Same email, same post ID, comment ID indicates a specific comment. In this case, if there is an existing subscription that is for all comments on the post; adding a new one where the comment ID is specific, overwrites a previous subscription that was for the entire post; implying that the underlying subscriber wants notifications regarding a specific comment, not all comments anymore.', 'comment-mail').'</li>'.
                   ' <li>'.__('Same email, same post ID, comment ID is not specific. Same as the previous example, but in reverse. If a subscription is created (or an existing subscription is updated), where it will now cover all comments on the post; any others that were for specific comments on the same post, will be overwritten to avoid duplicate emails.', 'comment-mail').'</li>'.
                   '</ul>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
    }

    /**
     * Sub event log; provide purged details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Details about why a purge occurs; in the form of a `[?]` link.
     */
    public function subPurgedQLink(\stdClass $row)
    {
        $details = '<h3 style="margin-top:0;">'.sprintf(__('Subscr. ID #%1$s was purged %2$s', 'comment-mail'), esc_html($row->sub_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p style="margin-bottom:0;">'.__('A purge occurs automatically whenever data connected to a subscription is deleted or becomes invalid. For example, if a subscription is connected to a post or comment ID that is later deleted, any subscriptions connected that post or comment ID will be purged automatically. The same would be true if a subscription was connected to a specific WordPress user ID. If the user is deleted, so are all of their subscriptions.', 'comment-mail').'</p>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
    }

    /**
     * Sub event log; provide cleaned details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Details about why a cleaning occurs; in the form of a `[?]` link.
     */
    public function subCleanedQLink(\stdClass $row)
    {
        $details = '<h3 style="margin-top:0;">'.sprintf(__('Subscr. ID #%1$s was cleaned %2$s', 'comment-mail'), esc_html($row->sub_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p>'.__('A cleaning occurs automatically whenever data connected to a subscription becomes corrupted, or when an expiration time is reached, as established by your config. options. For instance, if you configure an expiration time of <code>60 days</code> for unconfirmed or trashed subscriptions, once a subscription goes unconfirmed, or is left in the trash for <code>60 days</code> it will be cleaned automatically; i.e. deleted from the database.', 'comment-mail').'</p>'.
                   '<p style="margin-bottom:0;">'.sprintf(__('In terms of data corruption, a cleaning may also occur when you have a subscription connected to a nonexistent WP user ID. %1$s will periodically scan the database for invalid data sequences and clean those up to avoid clutter and/or confusion.', 'comment-mail'), esc_html(NAME)).'</p>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
    }

    /**
     * Sub event log; provide deleted details.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row A sub event log entry row from the DB.
     *
     * @return string Details about why a deletion occurs; in the form of a `[?]` link.
     */
    public function subDeletedQLink(\stdClass $row)
    {
        $details = '<h3 style="margin-top:0;">'.sprintf(__('Subscr. ID #%1$s was deleted %2$s', 'comment-mail'), esc_html($row->sub_id), esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $row->time))).'</h3>'.

                   '<i class="fa fa-info-circle fa-5x pmp-right"></i>'.
                   '<p style="margin-bottom:0;">'.__('Deletions occur as a result of you manually deleting a subscription, or in response to a subscription being unsubscribed; i.e. an end-user chooses to unsubscribe, and they click a link to remove themselves from the mailing list.', 'comment-mail').'</p>';

        return '<a href="#" class="pmp-q-link" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('[?]', 'comment-mail').'</a>';
    }
}
