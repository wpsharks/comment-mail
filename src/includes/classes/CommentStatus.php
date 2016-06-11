<?php
/**
 * Comment Status.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Comment Status.
 *
 * @since 141111 First documented version.
 */
class CommentStatus extends AbsBase
{
    /**
     * @type \stdClass|null Comment.
     *
     * @since 141111 First documented version.
     */
    protected $comment;

    /**
     * @type string New comment status applied now.
     *             One of: `approve`, `hold`, `trash`, `spam`, `delete`.
     *
     * @since 141111 First documented version.
     */
    protected $new_comment_status;

    /**
     * @type string Old comment status from before.
     *             One of: `approve`, `hold`, `trash`, `spam`, `delete`.
     *
     * @since 141111 First documented version.
     */
    protected $old_comment_status;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $new_comment_status New comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     * @param int|string $old_comment_status Old comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     * @param \WP_Comment|null $comment Comment object (now).
     */
    public function __construct($new_comment_status, $old_comment_status, \WP_Comment $comment = null)
    {
        parent::__construct();

        $this->comment            = $comment; // \WP_Comment|null.
        $this->new_comment_status = $this->plugin->utils_db->commentStatusI18n($new_comment_status);
        $this->old_comment_status = $this->plugin->utils_db->commentStatusI18n($old_comment_status);

        $this->maybeInjectQueue();
        $this->maybePurgeSubs();
    }

    /**
     * Inject/queue emails.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInjectQueue()
    {
        if (!isset($this->comment)) {
            return; // Not applicable.
        }
        if ($this->new_comment_status === 'approve' && $this->old_comment_status === 'hold') {
            new QueueInjector($this->comment->comment_ID);
        }
    }

    /**
     * Purges subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybePurgeSubs()
    {
        if (!isset($this->comment)) {
            return; // Not applicable.
        }
        if ($this->new_comment_status === 'delete' && $this->old_comment_status !== 'delete') {
            new SubPurger($this->comment->comment_post_ID, $this->comment->comment_ID);
        }
    }
}
