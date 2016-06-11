<?php
/**
 * Queue Injector.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Queue Injector.
 *
 * @since 141111 First documented version.
 */
class QueueInjector extends AbsBase
{
    /**
     * @type \stdClass|null Comment object.
     *
     * @since 141111 First documented version.
     */
    protected $comment;

    /**
     * Class constructor.
     *
     * @param int|string $comment_id Comment ID.
     *
     * @since 141111 First documented version.
     */
    public function __construct($comment_id)
    {
        parent::__construct();

        $comment_id = (integer) $comment_id;

        if ($comment_id) { // If possible.
            $this->comment = get_comment($comment_id);
        }
        $this->maybeInject();
    }

    /**
     * Queue injections.
     *
     * @since 141111 First documented version.
     *
     * @throws \exception If an insertion failure occurs.
     */
    protected function maybeInject()
    {
        if (!$this->comment) {
            return; // Not possible.
        }
        if (!$this->comment->comment_post_ID) {
            return; // Not possible.
        }
        if (!$this->comment->comment_ID) {
            return; // Not possible.
        }
        if (!($subscribed_subs = $this->subscribedSubs())) {
            return; // No subscriptions.
        }
        $time = time(); // Current timestamp.
        $sql  = 'INSERT INTO `'.esc_sql($this->plugin->utils_db->prefix().'queue').'`'.
                ' (`sub_id`, `user_id`, `post_id`, `comment_parent_id`, `comment_id`, `insertion_time`, `last_update_time`, `hold_until_time`) VALUES';

        foreach ($subscribed_subs as $_sub_id_key => $_sub) {
            $sql .= "('".esc_sql($_sub->ID)."', '".esc_sql($_sub->user_id)."', '".esc_sql($this->comment->comment_post_ID)."',".
                    " '".esc_sql($this->comment->comment_parent)."', '".esc_sql($this->comment->comment_ID)."',".
                    " '".esc_sql($time)."', '".esc_sql($time)."', '0'),";
        }
        unset($_sub_id_key, $_sub); // Housekeeping.

        $sql = $this->plugin->utils_string->trim($sql, '', ','); // Trim leftover delimiter.

        if (!$this->plugin->utils_db->wp->query($sql)) { // Insert failure?
            throw new \exception(__('Insertion failure.', 'comment-mail'));
        }
    }

    /**
     * Get subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @return \stdClass[] All subscriptions.
     */
    protected function subscribedSubs()
    {
        $sub_emails = $subs = []; // Initialize.

        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `post_id` = '".esc_sql($this->comment->comment_post_ID)."'".
               " AND (`comment_id` = '0' OR `comment_id` = '".esc_sql($this->comment->comment_parent)."')".
               " AND `status` = 'subscribed'"; // Only those that are `subscribed` currently.

        if (($sub_results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $sub_results = $this->plugin->utils_db->typifyDeep($sub_results);
        } else {
            $sub_results = []; // Default; empty array.
        }
        foreach ($sub_results as $_sub_id_key => $_sub) {
            if (!$_sub->email) { // Email empty?
                continue; // Missing email address.
            }
            $_sub->email = strtolower($_sub->email);

            if (isset($sub_emails[$_sub->email])) {
                continue; // Email duplicate.
            }
            if (strcasecmp($_sub->email, $this->comment->comment_author_email) === 0) {
                continue; // Don't send an email to the comment author.
            }
            $sub_emails[$_sub->email] = -1;
            $subs[$_sub->ID]          = $_sub;
        }
        unset($_sub_id_key, $_sub); // Housekeeping.

        return $subs; // All valid/unique subscriptions.
    }
}
