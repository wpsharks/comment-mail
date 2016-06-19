<?php
/**
 * Comment Form After.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Comment Form After.
 *
 * @since 141111 First documented version.
 */
class CommentFormAfter extends AbsBase
{
    /**
     * @type bool Via API call?
     *
     * @since 141111 First documented version.
     */
    protected $via_api = false;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param bool $via_api Defaults to a FALSE value.
     */
    public function __construct($via_api = false)
    {
        parent::__construct();

        $this->via_api = (boolean) $via_api;

        $this->maybeDisplaySubOps();
    }

    /**
     * Display subscription options.
     *
     * @since 141111 First documented version.
     */
    public function maybeDisplaySubOps()
    {
        if (!$this->plugin->options['enable']) {
            return; // Disabled currently.
        }
        if (!$this->plugin->options['new_subs_enable']) {
            return; // Disabled currently.
        }
        if (!$this->via_api && !$this->plugin->options['comment_form_sub_template_enable']) {
            return; // Disabled currently.
        }
        if (empty($GLOBALS['post']) || !($GLOBALS['post'] instanceof \WP_Post)) {
            return; // Not possible here.
        }
        $post_id   = $GLOBALS['post']->ID; // Current post ID.
        $post_type = $GLOBALS['post']->post_type; // Current post type.

        $enabled_post_types = strtolower($this->plugin->options['enabled_post_types']);
        $enabled_post_types = preg_split('/[\s;,]+/', $enabled_post_types, null, PREG_SPLIT_NO_EMPTY);

        if ($enabled_post_types && !in_array($post_type, $enabled_post_types, true)) {
            return;
        } // Ignore; not enabled for this post type.

        $current_info = // Current info; for this post ID.
            $this->plugin->utils_sub->currentEmailLatestInfo(
                ['post_id' => $post_id, 'comment_form_defaults' => true]
            );
        // @TODO What if they have a subscription, but not on this post?
        $current = (object) [
            'sub_email'   => $current_info->email,
            'sub_type'    => $current_info->type,
            'sub_deliver' => $current_info->deliver,
        ];
        unset($current_info); // Ditch this now.

        $sub_email   = $current->sub_email;
        $sub_type    = $current->sub_type;
        $sub_deliver = $current->sub_deliver;

        $sub_type_id   = str_replace('_', '-', GLOBAL_NS.'_sub_type');
        $sub_type_name = GLOBAL_NS.'_sub_type';

        $sub_deliver_id   = str_replace('_', '-', GLOBAL_NS.'_sub_deliver');
        $sub_deliver_name = GLOBAL_NS.'_sub_deliver';

        $sub_list_id   = str_replace('_', '-', GLOBAL_NS.'_sub_list');
        $sub_list_name = GLOBAL_NS.'_sub_list';

        $sub_summary_url = $this->plugin->utils_url->subManageSummaryUrl();
        $sub_new_url     = $this->plugin->utils_url->subManageSubNewUrl(null, null, compact('post_id'));
        $inline_icon_svg = $this->plugin->utils_fs->inlineIconSvg();

        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/comment-form/sub-ops.php');

        echo $template->parse($template_vars);
    }
}
