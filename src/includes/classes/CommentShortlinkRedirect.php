<?php
/**
 * Comment Shortlink Redirect.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Comment Shortlink Redirect.
 *
 * @since 141111 First documented version.
 */
class CommentShortlinkRedirect extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->maybeRedirect();
    }

    /**
     * Handle redirect.
     *
     * @since 141111 First documented version.
     */
    protected function maybeRedirect()
    {
        if (empty($_REQUEST['c']) || is_admin()) {
            return; // Nothing to do.
        }
        if (!($comment_id = (integer) $_REQUEST['c'])) {
            return; // Not applicable.
        }
        if (!($comment = get_comment($comment_id))) {
            return; // Not possible.
        }
        if (!($comment_link = get_comment_link($comment_id))) {
            return; // Not possible.
        }
        wp_redirect($comment_link, 301);
        exit();
    }
}
