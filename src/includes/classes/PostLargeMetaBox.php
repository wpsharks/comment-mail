<?php
/**
 * Post Large Meta Box.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Post Large Meta Box.
 *
 * @since 141111 First documented version.
 */
class PostLargeMetaBox extends AbsBase
{
    /**
     * @type \WP_Post A WP post object.
     *
     * @since 141111 First documented version.
     */
    protected $post;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Post $post A WP post object reference.
     */
    public function __construct(\WP_Post $post)
    {
        parent::__construct();

        $this->post = $post;

        $this->display();
    }

    /**
     * Display meta box. @TODO.
     *
     * @since 141111 First documented version.
     */
    protected function display()
    {
        $post_comment_status = $this->plugin->utils_db->postCommentStatusI18n($this->post->comment_status);

        echo '<div class="'.esc_attr(SLUG_TD.'-menu-page-area').'">'."\n";
        echo __('Coming soon...', 'comment-mail');
        echo '</div>';

        if ($post_comment_status !== 'open' && !$this->post->comment_count) {
            return; // For future implementation.
        }
    }
}
