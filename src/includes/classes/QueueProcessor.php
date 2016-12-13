<?php
/**
 * Queue Processor.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Queue Processor.
 *
 * @since 141111 First documented version.
 */
class QueueProcessor extends AbsBase
{
    /**
     * @type bool A CRON job?
     *
     * @since 141111 First documented version.
     */
    protected $is_cron;

    /**
     * @type bool A manual run?
     *
     * @since 161213 Manual queue processing.
     */
    protected $is_manual;

    /**
     * @type int Start time.
     *
     * @since 141111 First documented version.
     */
    protected $start_time;

    /**
     * @type int Max time (in seconds).
     *
     * @since 141111 First documented version.
     */
    protected $max_time;

    /**
     * @type int Delay (in milliseconds).
     *
     * @since 141111 First documented version.
     */
    protected $delay;

    /**
     * @type int Max entries to process.
     *
     * @since 141111 First documented version.
     */
    protected $max_limit;

    /**
     * @type Template Subject template.
     *
     * @since 141111 First documented version.
     */
    protected $subject_template;

    /**
     * @type Template Message template.
     *
     * @since 141111 First documented version.
     */
    protected $message_template;

    /**
     * @type \stdClass[] Entries being processed.
     *
     * @since 141111 First documented version.
     */
    protected $entries;

    /**
     * @type int Total entries.
     *
     * @since 141111 First documented version.
     */
    protected $total_entries;

    /**
     * @type int Processed entry counter.
     *
     * @since 141111 First documented version.
     */
    protected $processed_entry_counter;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param bool|string $is_cron  Is this a CRON job? Boolean or `manual`.
     * @param int|null    $max_time Max time (in seconds).
     *
     *    This cannot be less than `10` seconds.
     *    This cannot be greater than `300` seconds.
     *
     *    * A default value is taken from the plugin options.
     * @param int|null $delay Delay (in milliseconds).
     *
     *    This cannot be less than `0` milliseconds.
     *    This (converted to seconds) cannot be greater than `$max_time` - `5`.
     *
     *    * A default value is taken from the plugin options.
     * @param int|null $max_limit Max entries to process.
     *
     *    This cannot be less than `1`.
     *    This cannot be greater than `1000` (filterable).
     *
     *    * A default value is taken from the plugin options.
     */
    public function __construct($is_cron = true, $max_time = null, $delay = null, $max_limit = null)
    {
        parent::__construct();

        if ($is_cron === 'manual') {
            $this->is_manual = true;
            $this->is_cron   = false;
        } else {
            $this->is_manual = false;
            $this->is_cron   = (bool) $is_cron;
        }
        $this->start_time = time(); // Start time.

        if (isset($max_time)) { // Argument is set?
            $this->max_time = (int) $max_time; // This takes precedence.
        } else {
            $this->max_time = (int) $this->plugin->options['queue_processor_max_time'];
        }
        if ($this->max_time < 10) {
            $this->max_time = 10;
        }
        if ($this->max_time > 300) {
            $this->max_time = 300;
        }
        if (isset($delay)) { // Argument is set?
            $this->delay = (int) $delay; // This takes precedence.
        } else {
            $this->delay = (int) $this->plugin->options['queue_processor_delay'];
        }
        if ($this->delay < 0) {
            $this->delay = 0;
        }
        if ($this->delay && $this->delay / 1000 > $this->max_time - 5) {
            $this->delay = 250; // Cannot be greater than max time - 5 seconds.
        }
        if (isset($max_limit)) { // Argument is set?
            $this->max_limit = (int) $max_limit; // This takes precedence.
        } else {
            $this->max_limit = (int) $this->plugin->options['queue_processor_max_limit'];
        }
        if ($this->max_limit < 1) {
            $this->max_limit = 1;
        }
        $upper_max_limit = (int) apply_filters(__CLASS__.'_upper_max_limit', 1000);
        if ($this->max_limit > $upper_max_limit) {
            $this->max_limit = $upper_max_limit;
        }
        $this->subject_template = new Template('email/comment-notification/subject.php');
        $this->message_template = new Template('email/comment-notification/message.php');

        $this->entries                 = []; // Initialize.
        $this->total_entries           = 0; // Initialize; zero for now.
        $this->processed_entry_counter = 0; // Initialize; zero for now.

        $this->maybePrepCronJob();
        $this->maybeProcess();
    }

    /**
     * Processed entry counter.
     *
     * @since 161202 Processed entry counter.
     *
     * @return int Processed entry counter.
     */
    public function processedEntries()
    {
        return $this->processed_entry_counter;
    }

    /**
     * Prep CRON job.
     *
     * @since 141111 First documented version.
     */
    protected function maybePrepCronJob()
    {
        if (!$this->is_cron) {
            return; // Not applicable.
        }
        ignore_user_abort(true);

        @set_time_limit($this->max_time); // Max time only (first).
        // Doing this first in case the times below exceed an upper limit.
        // i.e. hosts may prevent this from being set higher than `$max_time`.

        // The following may not work, but we can try :-)
        if ($this->delay) { // Allow some extra time for the delay?
            @set_time_limit(min(300, ceil($this->max_time + ($this->delay / 1000) + 30)));
        } else {
            @set_time_limit(min(300, $this->max_time + 30));
        }
    }

    /**
     * Queue processor.
     *
     * @since 141111 First documented version.
     */
    protected function maybeProcess()
    {
        if (!$this->plugin->options['enable']) {
            return; // Disabled currently.
        } elseif (!$this->plugin->options['queue_processing_enable']) {
            return; // Disabled currently.
        } elseif (apply_filters(__CLASS__.'_manual_only', false) && !$this->is_manual) {
            return; // Manual processing only.
        }
        if (!($this->entries = $this->entries())) {
            return; // Nothing to do.
        }
        $this->total_entries = count($this->entries);

        foreach ($this->entries as $_entry_id_key => $_entry) {
            if ($this->processEntry($_entry)) {
                $this->deleteEntry($_entry);
            } // Do not delete those being held over.
            // See: <https://github.com/websharks/comment-mail/issues/173>

            ++$this->processed_entry_counter;

            if ($this->isOutOfTime() || $this->isDelayOutOfTime()) {
                break; // Out of time now; or after a possible delay.
            }
        }
        unset($_entry_id_key, $_entry); // Housekeeping.
    }

    /**
     * Process entry.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry Queue entry.
     *
     * @return bool True if OK to delete; i.e., if entry was logged in some way.
     *              This will return `false` if it is not OK to delete; e.g., being held over.
     */
    protected function processEntry(\stdClass $entry)
    {
        if ($entry->dby_queue_id || $entry->logged) {
            return true; // Already processed this.
        }
        if (!($entry_props = $this->validatedEntryProps($entry))) {
            return true; // Bypass; unable to validate entry props.
        }
        if ($this->checkEntryHoldUntilTime($entry_props)) {
            return false; // Holding (for now).
        }
        $this->checkCompileEntryDigestableEntries($entry_props);

        if (!($entry_headers = $this->entryHeaders($entry_props))) {
            $entry_props->event     = 'invalidated'; // Invalidate.
            $entry_props->note_code = 'comment_notification_headers_empty';

            $this->logEntry($entry_props); // Log invalidation.
            return true; // Not possible; headers are empty.
        }
        if (!($entry_subject = $this->entrySubject($entry_props))) {
            $entry_props->event     = 'invalidated'; // Invalidate.
            $entry_props->note_code = 'comment_notification_subject_empty';

            $this->logEntry($entry_props); // Log invalidation.
            return true; // Not possible; subject line is empty.
        }
        if (!($entry_message = $this->entryMessage($entry_props))) {
            $entry_props->event     = 'invalidated'; // Invalidate.
            $entry_props->note_code = 'comment_notification_message_empty';

            $this->logEntry($entry_props); // Log invalidation.
            return true; // Not possible; message body is empty.
        }
        $entry_props->event     = 'notified'; // Notifying now.
        $entry_props->note_code = 'comment_notification_sent_successfully';

        $this->logEntry($entry_props); // Log successful processing.
        $this->plugin->utils_mail->send($entry_props->sub->email, $entry_subject, $entry_message, $entry_headers);
        return true; // Notified; OK to delete this entry now.
    }

    /**
     * Log entry event w/ note code.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props Entry properties.
     */
    protected function logEntry(\stdClass $entry_props)
    {
        if ($entry_props->logged) {
            return; // Already logged this.
        }
        if (!$entry_props->entry) {
            return; // Nothing to log; no entry.
        }
        $log_entry = [
            'queue_id'     => $entry_props->entry->ID,
            'dby_queue_id' => $entry_props->dby_queue_id, // Digested?

            'sub_id' => $entry_props->sub ? $entry_props->sub->ID : $entry_props->entry->sub_id,

            'user_id'           => $entry_props->sub ? $entry_props->sub->user_id : 0, // Default; no user; not possible.
            'post_id'           => $entry_props->post ? $entry_props->post->ID : ($entry_props->comment ? $entry_props->comment->comment_post_ID : ($entry_props->sub ? $entry_props->sub->post_id : 0)),
            'comment_id'        => $entry_props->comment ? $entry_props->comment->comment_ID : $entry_props->entry->comment_id,
            'comment_parent_id' => $entry_props->comment ? $entry_props->comment->comment_parent : $entry_props->entry->comment_parent_id,

            'fname' => $entry_props->sub ? $entry_props->sub->fname : '',
            'lname' => $entry_props->sub ? $entry_props->sub->lname : '',
            'email' => $entry_props->sub ? $entry_props->sub->email : '',

            'ip'      => $entry_props->sub ? $entry_props->sub->last_ip : '',
            'region'  => $entry_props->sub ? $entry_props->sub->last_region : '',
            'country' => $entry_props->sub ? $entry_props->sub->last_country : '',

            'status' => $entry_props->sub ? $entry_props->sub->status : '',

            'event'     => $entry_props->event,
            'note_code' => $entry_props->note_code,
        ];
        new QueueEventLogInserter($log_entry);

        $entry_props->logged        = true; // Flag as `TRUE`.
        $entry_props->entry->logged = true; // Flag as `TRUE`.

        if (isset($this->entries[$entry_props->entry->ID])) {
            $this->entries[$entry_props->entry->ID]->logged = true;
        }
        $this->maybeLogDeleteEntryDigestables($entry_props);
    }

    /**
     * Log/record entry digestables.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props Entry properties.
     */
    protected function maybeLogDeleteEntryDigestables(\stdClass $entry_props)
    {
        if (!$entry_props->entry) {
            return; // Nothing to log; no entry.
        }
        if (!$entry_props->props) {
            return; // Nothing to log; no props.
        }
        if (!$entry_props->event || $entry_props->event !== 'notified') {
            return; // Nothing to do. No event, or was NOT notified.
        }
        if (count($entry_props->props) <= 1 && isset($entry_props->props[$entry_props->entry->ID])) {
            return; // Nothing to do; only one (i.e. itself).
        }
        foreach ($entry_props->props as $_entry_digestable_entry_id_key => $_entry_digestable_entry_props) {
            if ($_entry_digestable_entry_props->entry->ID !== $entry_props->entry->ID) {
                $_entry_digestable_entry_props->event     = $entry_props->event;
                $_entry_digestable_entry_props->note_code = $entry_props->note_code;

                $_entry_digestable_entry_props->dby_queue_id        = $entry_props->entry->ID;
                $_entry_digestable_entry_props->entry->dby_queue_id = $entry_props->entry->ID;

                if (isset($this->entries[$_entry_digestable_entry_props->entry->ID])) {
                    $this->entries[$_entry_digestable_entry_props->entry->ID]->dby_queue_id = $entry_props->entry->ID;
                }
                $this->logEntry($_entry_digestable_entry_props);
                $this->deleteEntry($_entry_digestable_entry_props->entry);
            }
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
    protected function deleteEntry(\stdClass $entry)
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
     *                        If unable to validate, returns `NULL`.
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
     * @see   UtilsEvent::queueNoteCodeDesc()
     */
    protected function validatedEntryProps(\stdClass $entry)
    {
        $sub_comment = null; // Initialize this.
        /*
         * Check primary IDs for validity.
         */
        if (!$entry->sub_id) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'entry_sub_id_empty', $entry);
        } elseif (!$entry->post_id) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'entry_post_id_empty', $entry);
        } elseif (!$entry->comment_id) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'entry_comment_id_empty', $entry);
        } /*
         * Now we check some basics in the subscription itself.
         */
        elseif (!($sub = $this->plugin->utils_sub->get($entry->sub_id))) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'entry_sub_id_missing', $entry);
        } elseif (!$sub->email) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_email_empty', $entry, $sub);
        } elseif ($sub->status !== 'subscribed') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_status_not_subscribed', $entry, $sub);
        } /*
         * Make sure the subscription still matches up with the same post/comment IDs.
         */
        elseif ($sub->post_id !== $entry->post_id) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_post_id_mismtach', $entry, $sub);
        } elseif ($sub->comment_id && $sub->comment_id !== $entry->comment_parent_id) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_comment_id_mismatch', $entry, $sub);
        } /*
         * Now we check the subscription's post ID.
         */
        elseif (!($sub_post = get_post($sub->post_id))) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_post_id_missing', $entry, $sub);
        } elseif (!$sub_post->post_title) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_post_title_empty', $entry, $sub, $sub_post);
        } elseif ($sub_post->post_status !== 'publish') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_post_status_not_publish', $entry, $sub, $sub_post);
        } elseif (in_array($sub_post->post_type, ['revision', 'nav_menu_item'], true)) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_post_type_auto_excluded', $entry, $sub, $sub_post);
        } /*
         * Now we check the subscription's comment ID; if applicable.
         */
        elseif ($sub->comment_id && !($sub_comment = get_comment($sub->comment_id))) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_comment_id_missing', $entry, $sub, $sub_post);
        } elseif ($sub_comment && $sub_comment->comment_type && $sub_comment->comment_type !== 'comment') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_comment_type_not_comment', $entry, $sub, $sub_post, $sub_comment);
        } elseif ($sub_comment && !$sub_comment->comment_content) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_comment_content_empty', $entry, $sub, $sub_post, $sub_comment);
        } elseif ($sub_comment && $this->plugin->utils_db->commentStatusI18n($sub_comment->comment_approved) !== 'approve') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_comment_status_not_approve', $entry, $sub, $sub_post, $sub_comment);
        } /*
         * Make sure the comment we are notifying about still exists; and check validity.
         */
        elseif (!($comment = get_comment($entry->comment_id))) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'entry_comment_id_missing', $entry, $sub, $sub_post, $sub_comment);
        } elseif ($comment->comment_type && $comment->comment_type !== 'comment') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'comment_type_not_comment', $entry, $sub, $sub_post, $sub_comment, null, $comment);
        } elseif (!$comment->comment_content) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'comment_content_empty', $entry, $sub, $sub_post, $sub_comment, null, $comment);
        } elseif ($this->plugin->utils_db->commentStatusI18n($comment->comment_approved) !== 'approve') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'comment_status_not_approve', $entry, $sub, $sub_post, $sub_comment, null, $comment);
        } /*
         * Make sure the post containing the comment we are notifying about still exists; and check validity.
         */
        elseif (!($post = get_post($comment->comment_post_ID))) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'comment_post_id_missing', $entry, $sub, $sub_post, $sub_comment, null, $comment);
        } elseif (!$post->post_title) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'post_title_empty', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
        } elseif ($post->post_status !== 'publish') {
            $invalidated_entry_props = $this->entryProps('invalidated', 'post_status_not_publish', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
        } elseif (in_array($post->post_type, ['revision', 'nav_menu_item'], true)) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'post_type_auto_excluded', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
        } /*
         * Again, make sure the subscription still matches up with the same post/comment IDs; and that both still exist.
         */
        elseif ($sub->post_id !== (int) $comment->comment_post_ID) {
            $invalidated_entry_props = $this->entryProps('invalidated', 'sub_post_id_comment_mismtach', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
        } /*
         * Else, we can return the full set of entry properties for this queue entry.
         */
        else { // Validated entry props.
            return $this->entryProps('', '', $entry, $sub, $sub_post, $sub_comment, $post, $comment);
        }
        /*
         * Otherwise (i.e. if we get down here); we need to log the invalidation.
         */
        if (isset($invalidated_entry_props)) { // Unable to validate/initialize entry props?
            $this->logEntry($invalidated_entry_props); // Log invalidation.
        }
        return null; // Unable to validate/initialize entry props.
    }

    /**
     * Structured entry props.
     *
     * @since 141111 First documented version.
     *
     * @param string         $event        Event type; `invalidated` or `notified`.
     * @param string         $note_code    See {@link UtilsEvent::queueNoteCode()}.
     * @param \stdClass      $entry        Queue entry.
     * @param \stdClass|null $sub          Subscription.
     * @param \WP_Post|null  $sub_post     Subscription post.
     * @param \stdClass|null $sub_comment  Subscription comment.
     * @param \WP_Post|null  $post         Post we are notifying about.
     * @param \stdClass|null $comment      Comment we are notifying about.
     * @param \stdClass[]    $props        Digestable entry props.
     * @param \stdClass[]    $comments     Digestable comments.
     * @param bool           $held         Held? Defaults to `FALSE`.
     * @param int            $dby_queue_id Digested by queue ID.
     * @param bool           $logged       Logged? Defaults to `FALSE`.
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
     * @see   UtilsEvent::queueNoteCodeDesc()
     */
    protected function entryProps(
        $event = '',
        $note_code = '',
        //
        \stdClass $entry = null,
        //
        \stdClass $sub = null,
        \WP_Post $sub_post = null,
        \WP_Comment $sub_comment = null,
        //
        \WP_Post $post = null,
        \WP_Comment $comment = null,
        //
        array $props = [],
        array $comments = [],
        //
        $held = false,
        $dby_queue_id = 0,
        $logged = false
    ) {
        $event     = (string) $event;
        $note_code = (string) $note_code;

        if (!$comments && $comment) { // Not passed in?
            $comments = [$comment->comment_ID => $comment];
        }
        $held         = (bool) $held;
        $dby_queue_id = (int) $dby_queue_id;
        $logged       = (bool) $logged;

        $entry_props = (object) compact(
            'event',
            'note_code',
            //
            'entry',
            //
            'sub',
            'sub_post',
            'sub_comment',
            //
            'post',
            'comment',
            //
            'props',
            'comments',
            //
            'held',
            'dby_queue_id',
            'logged'
        );
        if (!$props && !$entry_props->props) {
            $entry_props->props = [$entry ? $entry->ID : 0 => $entry_props];
        }
        return $entry_props;
    }

    /**
     * Check hold until time.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props Entry properties.
     *
     * @return bool TRUE if the notification should be held, for now.
     */
    protected function checkEntryHoldUntilTime(\stdClass $entry_props)
    {
        if ($entry_props->sub->deliver === 'asap') {
            return false; // Do not hold; n/a.
        }
        if (time() >= ($entry_hold_until_time = $this->entryHoldUntilTime($entry_props))) {
            return false; // Don't hold any longer.
        }
        $this->updateEntryHoldUntilTime($entry_props, $entry_hold_until_time);

        return true; // Yes, holding this entry (for now).
    }

    /**
     * Entry hold until time.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props Entry properties.
     *
     * @return int Hold until time; UNIX timestamp.
     */
    protected function entryHoldUntilTime(\stdClass $entry_props)
    {
        switch ($entry_props->sub->deliver) {
            case 'hourly': // Delivery option = hourly digest.
                if (($entry_last_notified_time = $this->entryLastNotifiedTime($entry_props))) {
                    return $entry_last_notified_time + 3600;
                }
                break;

            case 'daily': // Delivery option = daily digest.
                if (($entry_last_notified_time = $this->entryLastNotifiedTime($entry_props))) {
                    return $entry_last_notified_time + 86400;
                }
                break;

            case 'weekly': // Delivery option = weekly digest.
                if (($entry_last_notified_time = $this->entryLastNotifiedTime($entry_props))) {
                    return $entry_last_notified_time + 604800;
                }
                break;
        }
        return $entry_props->entry->hold_until_time ? $entry_props->entry->hold_until_time : $entry_props->entry->insertion_time;
    }

    /**
     * Update hold until time.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props           Entry properties.
     * @param int       $entry_hold_until_time Hold until time; UNIX timestamp.
     *
     * @throws \exception If a DB update failure occurs.
     */
    protected function updateEntryHoldUntilTime(\stdClass $entry_props, $entry_hold_until_time)
    {
        if ($entry_props->held) {
            return; // Already did this.
        }
        $entry_hold_until_time = (int) $entry_hold_until_time;

        $sql = 'UPDATE `'.esc_sql($this->plugin->utils_db->prefix().'queue').'`'.

               " SET `last_update_time` = '".esc_sql(time())."', `hold_until_time` = '".esc_sql($entry_hold_until_time)."'".

               " WHERE `ID` = '".esc_sql($entry_props->entry->ID)."'";

        if ($this->plugin->utils_db->wp->query($sql) === false) {
            throw new \exception(__('Update failure.', 'comment-mail'));
        }
        $entry_props->entry->hold_until_time = $entry_hold_until_time;
        $entry_props->held                   = true; // Flag as `TRUE` now.
    }

    /**
     * Entry last notified time.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props Entry properties.
     *
     * @return int Last notified time; UNIX timestamp.
     */
    protected function entryLastNotifiedTime(\stdClass $entry_props)
    {
        $sql = 'SELECT `time` FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.

               " WHERE `post_id` = '".esc_sql($entry_props->post->ID)."'".

               (!$entry_props->sub->comment_id ? '' // If all comments; include everything.
                   : " AND `comment_parent_id` = '".esc_sql($entry_props->comment->comment_parent)."'").

               " AND `sub_id` = '".esc_sql($entry_props->sub->ID)."'".
               " AND `event` = 'notified'".

               ' ORDER BY `time` DESC'.

               ' LIMIT 1'; // Only need the last time.

        return (int) $this->plugin->utils_db->wp->get_var($sql);
    }

    /**
     * Compile digestable entries.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $entry_props entry properties.
     *
     * @return bool TRUE if the entry has other digestable entries.
     */
    protected function checkCompileEntryDigestableEntries(\stdClass $entry_props)
    {
        if ($entry_props->sub->deliver === 'asap') {
            return false; // Not applicable; i.e. no other digestables.
        }
        if (!($entry_digestable_entries = $this->entryDigestableEntries($entry_props))) {
            return false; // Not applicable; i.e. no other digestables.
        }
        if (count($entry_digestable_entries) <= 1 && isset($entry_digestable_entries[$entry_props->entry->ID])) {
            return false; // Only itself; i.e. no other digestables.
        }
        foreach ($entry_digestable_entries as $_entry_digestable_entry_id_key => $_entry_digestable_entry) {
            if ($_entry_digestable_entry->ID === $entry_props->entry->ID) {
                $_entry_digestable_entry_props = $entry_props;
            } else {
                $_entry_digestable_entry_props = $this->validatedEntryProps($_entry_digestable_entry);
            }
            if ($_entry_digestable_entry_props) { // Include this one? i.e. do we have valid entry props?
                $entry_props->props[$_entry_digestable_entry_props->entry->ID]              = $_entry_digestable_entry_props;
                $entry_props->comments[$_entry_digestable_entry_props->comment->comment_ID] = $_entry_digestable_entry_props->comment;
            }
        }
        unset($_entry_digestable_entry_id_key, $_entry_digestable_entry, $_entry_digestable_entry_props); // Housekeeping.

        return true; // Yes, this entry has at least one other digestable entry.
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
    protected function entryDigestableEntries(\stdClass $entry_props)
    {
        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue').'`'.

               " WHERE `post_id` = '".esc_sql($entry_props->post->ID)."'".

               (!$entry_props->sub->comment_id ? '' // If all comments; include everything.
                   : " AND `comment_parent_id` = '".esc_sql($entry_props->comment->comment_parent)."'").

               " AND `sub_id` = '".esc_sql($entry_props->sub->ID)."'".

               ' ORDER BY `insertion_time` ASC'; // In chronological order.

        if (($entry_digestable_entries = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $entry_digestable_entries = $this->plugin->utils_db->typifyDeep($entry_digestable_entries);
        } else {
            $entry_digestable_entries = []; // Default; empty array.
        }
        foreach ($entry_digestable_entries as $_entry_digestable_entry_id_key => $_entry_digestable_entry) {
            if ($_entry_digestable_entry->ID === $entry_props->entry->ID) {
                $entry_digestable_entries[$_entry_digestable_entry_id_key] = $entry_props->entry;
            } else { // Create dynamic properties for the new digestable entries compiled here.
                $_entry_digestable_entry->dby_queue_id = $entry_props->entry->ID; // Dynamic property.
                $_entry_digestable_entry->logged       = false; // Dynamic property; default value: `FALSE`.
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
        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue').'`'.

               " WHERE `hold_until_time` < '".esc_sql(time())."'".

               ' ORDER BY `insertion_time` ASC'.// Oldest get priority.

               ' LIMIT '.$this->max_limit; // Max limit for this class instance.

        if (($entries = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $entries = $this->plugin->utils_db->typifyDeep($entries);
        } else {
            $entries = []; // Default; empty array.
        }
        foreach ($entries as $_entry_id_key => $_entry) {
            $_entry->dby_queue_id = 0; // Dynamic property; default value: `0`.
            $_entry->logged       = false; // Dynamic property; default value: `FALSE`.
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
    protected function entryHeaders(\stdClass $entry_props)
    {
        $is_digest = count($entry_props->comments) > 1;

        $entry_headers[] = 'X-Post-Id: '.$entry_props->post->ID;

        if (!$is_digest) { // Applicable only w/ single comment notifications.
            $entry_headers[] = 'X-Comment-Id: '.$entry_props->comment->comment_ID;
        }
        $entry_headers[] = 'X-Sub-Key: '.$entry_props->sub->key;

        if ($this->plugin->options['replies_via_email_enable']) {
            switch ($this->plugin->options['replies_via_email_handler']) {
                case 'sparkpost': // SparkPost (free, recommended).

                    if ($this->plugin->options['rve_sparkpost_reply_to_email']) {
                        $rve_sparkpost_reply_to_email = $this->plugin->options['rve_sparkpost_reply_to_email'];

                        if ($is_digest) { // In digests, we only want a post ID and sub key. A comment ID will need to be given by the end-user.
                            $rve_sparkpost_reply_to_email = $this->plugin->utils_rve->irtSuffix($rve_sparkpost_reply_to_email, $entry_props->post->ID, null, $entry_props->sub->key);
                        } else {
                            $rve_sparkpost_reply_to_email = $this->plugin->utils_rve->irtSuffix($rve_sparkpost_reply_to_email, $entry_props->post->ID, $entry_props->comment->comment_ID, $entry_props->sub->key);
                        }
                        $entry_headers[] = 'Reply-To: '.$rve_sparkpost_reply_to_email;
                    }
                    break; // Break switch handler.

                case 'mandrill': // Mandrill (SparkPost alternative).

                    if ($this->plugin->options['rve_mandrill_reply_to_email']) {
                        $rve_mandrill_reply_to_email = $this->plugin->options['rve_mandrill_reply_to_email'];

                        if ($is_digest) { // In digests, we only want a post ID and sub key. A comment ID will need to be given by the end-user.
                            $rve_mandrill_reply_to_email = $this->plugin->utils_rve->irtSuffix($rve_mandrill_reply_to_email, $entry_props->post->ID, null, $entry_props->sub->key);
                        } else {
                            $rve_mandrill_reply_to_email = $this->plugin->utils_rve->irtSuffix($rve_mandrill_reply_to_email, $entry_props->post->ID, $entry_props->comment->comment_ID, $entry_props->sub->key);
                        }
                        $entry_headers[] = 'Reply-To: '.$rve_mandrill_reply_to_email;
                    }
                    break; // Break switch handler.
            }
        }
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
    protected function entrySubject(\stdClass $entry_props)
    {
        $template_vars = (array) $entry_props;

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
    protected function entryMessage(\stdClass $entry_props)
    {
        $template_vars = (array) $entry_props;

        $email_rve_end_divider = null; // Initialize.
        if ($this->plugin->options['replies_via_email_enable']) {
            $email_rve_end_divider = $this->plugin->utils_rve->endDivider();
        }
        $template_vars = array_merge($template_vars, compact('email_rve_end_divider'));

        return $this->message_template->parse($template_vars);
    }

    /**
     * Out of time yet?
     *
     * @since 141111 First documented version.
     *
     * @return bool TRUE if out of time.
     */
    protected function isOutOfTime()
    {
        if ((time() - $this->start_time) >= ($this->max_time - 5)) {
            return true; // Out of time.
        }
        return false; // Let's keep mailing!
    }

    /**
     * Out of time after a possible delay?
     *
     * @since 141111 First documented version.
     *
     * @return bool TRUE if out of time.
     */
    protected function isDelayOutOfTime()
    {
        if (!$this->delay) { // No delay?
            return false; // Nope; nothing to do here.
        }
        if ($this->processed_entry_counter >= $this->total_entries) {
            return false; // No delay on last entry.
        }
        usleep($this->delay * 1000); // Delay.

        return $this->isOutOfTime();
    }
}
