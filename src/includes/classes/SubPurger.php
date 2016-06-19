<?php
/**
 * Sub Purger.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Purger.
 *
 * @since 141111 First documented version.
 */
class SubPurger extends AbsBase
{
    /**
     * @type int Post ID.
     *
     * @since 141111 First documented version.
     */
    protected $post_id;

    /**
     * @type int Comment ID.
     *
     * @since 141111 First documented version.
     */
    protected $comment_id;

    /**
     * @type int User ID.
     *
     * @since 141111 First documented version.
     */
    protected $user_id;

    /**
     * @type int Total subs purged.
     *
     * @since 141111 First documented version.
     */
    protected $purged;

    /**
     * Class constructor.
     *
     * @param int|string $post_id    Post ID.
     * @param int|string $comment_id Comment ID.
     * @param int|string $user_id    User ID.
     *
     * @since 141111 First documented version.
     */
    public function __construct($post_id, $comment_id = 0, $user_id = 0)
    {
        parent::__construct();

        $this->post_id    = (integer) $post_id;
        $this->comment_id = (integer) $comment_id;
        $this->user_id    = (integer) $user_id;

        $this->purged = 0; // Initialize.

        $this->maybePurge(); // If applicable.
    }

    /**
     * Total subs purged.
     *
     * @since 141111 First documented version.
     */
    public function purged()
    {
        return $this->purged;
    }

    /**
     * Purges subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybePurge()
    {
        $this->maybePurgePostComment();
        $this->maybePurgeUser();
    }

    /**
     * Purges subscriptions on post/comment deletions.
     *
     * @since 141111 First documented version.
     */
    protected function maybePurgePostComment()
    {
        if (!$this->post_id) {
            return; // Not applicable.
        }
        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `post_id` = '".esc_sql($this->post_id)."'".
               ($this->comment_id ? " AND `comment_id` = '".esc_sql($this->comment_id)."'" : '');

        if (($ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->purged += $this->plugin->utils_sub->bulkDelete($ids, ['purging' => true]);
        }
    }

    /**
     * Purges subscriptions on user deletions.
     *
     * @since 141111 First documented version.
     */
    protected function maybePurgeUser()
    {
        if (!$this->user_id) {
            return; // Not applicable.
        }
        $user = new \WP_User($this->user_id);
        if (!$user->ID) {
            return; // Not possible.
        }
        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `user_id` = '".esc_sql($user->ID)."'".
               ($user->user_email ? " OR `email` = '".esc_sql($user->user_email)."'" : '');

        if (($ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->purged += $this->plugin->utils_sub->bulkDelete($ids, ['purging' => true]);
        }
    }
}
