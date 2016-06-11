<?php
/**
 * Post Deletion Handler.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Post Deletion Handler.
 *
 * @since 141111 First documented version.
 */
class PostDelete extends AbsBase
{
    /**
     * @type int Post ID.
     *
     * @since 141111 First documented version.
     */
    protected $post_id;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $post_id Post ID.
     */
    public function __construct($post_id)
    {
        parent::__construct();

        $this->post_id = (integer) $post_id;

        $this->maybePurgeSubs();
    }

    /**
     * Purges subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybePurgeSubs()
    {
        if (!$this->post_id) {
            return; // Nothing to do.
        }
        new SubPurger($this->post_id);
    }
}
