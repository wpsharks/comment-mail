<?php
/**
 * Comment Form Login.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Comment Form Login.
 *
 * @since 141111 First documented version.
 */
class CommentFormLogin extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->maybeDisplaySsoOps();
    }

    /**
     * Display SSO options.
     *
     * @since 141111 First documented version.
     */
    public function maybeDisplaySsoOps()
    {
        if (!$this->plugin->options['sso_enable']) {
            return; // Disabled currently.
        }
        if (!$this->plugin->options['comment_form_sso_template_enable']) {
            return; // Disabled currently.
        }
        if (is_user_logged_in()) {
            return; // Not unnecessary.
        }
        // if(!get_option('comment_registration') || is_user_logged_in()) {
        // 	return; // Not applicable; i.e. unnecessary.
        // }
        if (empty($GLOBALS['post']) || !($GLOBALS['post'] instanceof \WP_Post)) {
            return; // Not possible here.
        }
        $post_id   = $GLOBALS['post']->ID; // Current post ID.
        $post_type = $GLOBALS['post']->post_type; // Current post type.

        $enabled_post_types = strtolower($this->plugin->options['enabled_post_types']);
        $enabled_post_types = preg_split('/[\s;,]+/', $enabled_post_types, null, PREG_SPLIT_NO_EMPTY);

        if ($enabled_post_types && !in_array($post_type, $enabled_post_types, true)) {
            return; // Ignore; not enabled for this post type.
        }
        foreach (($sso_services = SsoActions::$valid_services) as $_key => $_service) {
            if (!$this->plugin->options['sso_'.$_service.'_key'] || !$this->plugin->options['sso_'.$_service.'_secret']) {
                unset($sso_services[$_key]); // Remove from the array.
            }
        }
        unset($_key, $_service); // Housekeeping.

        if (!$sso_services) {
            return; // No configured services.
        }
        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/comment-form/sso-ops.php');

        echo $template->parse($template_vars);
    }
}
