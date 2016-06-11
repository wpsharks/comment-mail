<?php
/**
 * User Deletion Handler.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * User Deletion Handler.
 *
 * @since 141111 First documented version.
 */
class UserDelete extends AbsBase
{
    /**
     * @type int User ID.
     *
     * @since 141111 First documented version.
     */
    protected $user_id;

    /**
     * @type int Blog ID.
     *
     * @since 141111 First documented version.
     */
    protected $blog_id;

    /**
     * @type bool Switched blog?
     *
     * @since 141111 First documented version.
     */
    protected $switched_blog;

    /**
     * Class constructor.
     *
     * @param int|string $user_id User ID.
     * @param int|string $blog_id Blog ID. Defaults to `0` (current blog).
     *
     * @since 141111 First documented version.
     */
    public function __construct($user_id, $blog_id = 0)
    {
        parent::__construct();

        $this->switched_blog = false;
        $this->user_id       = (integer) $user_id;
        $this->blog_id       = (integer) $blog_id;

        $this->maybePurgeSubs();
    }

    /**
     * Purges subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybePurgeSubs()
    {
        if (!$this->user_id) {
            return; // Nothing to do.
        }
        if ($this->blog_id && $this->blog_id !== $GLOBALS['blog_id']) {
            switch_to_blog($this->blog_id);
            $this->switched_blog = true;
        }
        new SubPurger(0, 0, $this->user_id);

        if ($this->blog_id && $this->switched_blog) {
            restore_current_blog();
            $this->switched_blog = false;
        }
    }
}
