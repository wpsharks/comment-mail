<?php
/**
 * Post Small Meta Box.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Post Small Meta Box.
 *
 * @since 141111 First documented version.
 */
class PostSmallMetaBox extends AbsBase
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
     * Display meta box.
     *
     * @since 141111 First documented version.
     */
    protected function display()
    {
        $post_comment_status = $this->plugin->utils_db->postCommentStatusI18n($this->post->comment_status);

        $total_subs        = $this->plugin->utils_sub->queryTotal($this->post->ID);
        $total_subs_bubble = $this->plugin->utils_markup->subsCount(
            $this->post->ID,
            $total_subs,
            [
                'subscriptions' => true,
                'style'         => 'display:block; font-size:1em; background:#2EA2CC; margin-top: 1em; padding: 0.5em 1em;',
            ]
        );
        echo '<div class="'.esc_attr(SLUG_TD.'-menu-page-area').'">'.

             '   <h4 style="margin:1em 0 1em 0;">'.__('Most Recent Subscriptions', 'comment-mail').'</h4>'.

             '   '.$this->plugin->utils_markup->lastXSubs(5, $this->post->ID, ['group_by_email' => true]).

             '  '.$total_subs_bubble.// In block format; i.e. 100% width.

             '</div>';

        if ($post_comment_status !== 'open' && !$this->post->comment_count) {
            return; // For future implementation.
        }
    }
}
