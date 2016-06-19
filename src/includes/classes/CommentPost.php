<?php
/**
 * Comment Post.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Comment Post.
 *
 * @since 141111 First documented version.
 */
class CommentPost extends AbsBase
{
    /**
     * @type int Comment ID.
     *
     * @since 141111 First documented version.
     */
    protected $comment_id;

    /**
     * @type string Current/initial comment status.
     *             One of: `approve`, `hold`, `trash`, `spam`, `delete`.
     *
     * @since 141111 First documented version.
     */
    protected $comment_status;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $comment_id     Comment ID.
     * @param int|string $comment_status Initial comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     */
    public function __construct($comment_id, $comment_status)
    {
        parent::__construct();

        $this->comment_id     = (integer) $comment_id;
        $this->comment_status = $this->plugin->utils_db->commentStatusI18n($comment_status);

        $this->maybeInjectSub();
        $this->maybeInjectQueue();
        $this->maybeProcessQueueInRealtime();
    }

    /**
     * Inject subscription.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInjectSub()
    {
        if (!$this->plugin->options['enable']) {
            return; // Disabled currently.
        }
        if (!$this->plugin->options['new_subs_enable']) {
            return; // Disabled currently.
        }
        if (!$this->comment_id) {
            return; // Not applicable.
        }
        if ($this->comment_status === 'spam') {
            return; // Not applicable.
        }
        if (empty($_POST[GLOBAL_NS.'_sub_type'])) {
            return; // Not applicable.
        }
        $sub_type = (string) $_POST[GLOBAL_NS.'_sub_type'];
        if (!($sub_type = $this->plugin->utils_string->trimStrip($sub_type))) {
            return; // Not applicable.
        }
        $sub_deliver = !empty($_POST[GLOBAL_NS.'_sub_deliver'])
            ? (string) $_POST[GLOBAL_NS.'_sub_deliver']
            : $this->plugin->options['comment_form_default_sub_deliver_option'];

        $sub_list = (boolean) @$_POST[GLOBAL_NS.'_sub_list'];

        new SubInjector(
            wp_get_current_user(),
            $this->comment_id,
            [
                'type'                => $sub_type,
                'deliver'             => $sub_deliver,
                'process_list_server' => $sub_list,
                'user_initiated'      => true,
                'keep_existing'       => true,
            ]
        );
    }

    /**
     * Inject/queue emails.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInjectQueue()
    {
        if (!$this->comment_id) {
            return; // Not applicable.
        }
        if ($this->comment_status !== 'approve') {
            return; // Not applicable.
        }
        new QueueInjector($this->comment_id);
    }

    /**
     * Process queued emails in real-time.
     *
     * @since 141111 First documented version.
     */
    protected function maybeProcessQueueInRealtime()
    {
        if (!$this->comment_id) {
            return; // Not applicable.
        }
        if ($this->comment_status !== 'approve') {
            return; // Not applicable.
        }
        if (($realtime_max_limit = (integer) $this->plugin->options['queue_processor_realtime_max_limit']) <= 0) {
            return; // Real-time queue processing is not enabled right now.
        }
        $upper_max_limit = (integer) apply_filters(__CLASS__.'_upper_max_limit', 100);
        if ($realtime_max_limit > $upper_max_limit) {
            $realtime_max_limit = $upper_max_limit;
        }
        new QueueProcessor(false, 10, 0, $realtime_max_limit); // No delay.
    }
}
