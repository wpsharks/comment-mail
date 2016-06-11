<?php
/**
 * Front Scripts.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Front Scripts.
 *
 * @since 141111 First documented version.
 */
class FrontScripts extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        if (is_admin()) {
            return; // Not applicable.
        }
        $this->maybeEnqueueLoginFormSsoScripts();
        $this->maybeEnqueueCommentFormSsoScripts();
        $this->maybeEnqueueCommentFormSubScripts();
    }

    /**
     * Enqueue front-side scripts for login form SSO.
     *
     * @since 141111 First documented version.
     */
    protected function maybeEnqueueLoginFormSsoScripts()
    {
        if (!$this->plugin->options['sso_enable']) {
            return; // Disabled currently.
        }
        if (!$this->plugin->options['login_form_sso_scripts_enable']) {
            if (!$this->plugin->options['login_form_sso_template_enable']) {
                return; // Nothing to do here.
            }
        }
        if (!preg_match('/\/wp\-login\.php(?:[?&#]|$)/', $this->plugin->utils_url->currentUri())) {
            return; // Not applicable.
        }
        wp_enqueue_script('jquery'); // Need jQuery.

        add_action(// Very low priority; after footer scripts!
            'login_footer',
            function () {
                $template = new Template('site/login-form/sso-op-scripts.php');
                echo $template->parse(); // Inline `<script></script>`.
            },
            PHP_INT_MAX - 10
        );
    }

    /**
     * Enqueue front-side scripts for comment form SSO.
     *
     * @since 141111 First documented version.
     */
    protected function maybeEnqueueCommentFormSsoScripts()
    {
        if (!$this->plugin->options['sso_enable']) {
            return; // Disabled currently.
        }
        if (!$this->plugin->options['comment_form_sso_scripts_enable']) {
            if (!$this->plugin->options['comment_form_sso_template_enable']) {
                return; // Nothing to do here.
            }
        }
        if (!is_singular() || !comments_open()) {
            return; // Not applicable.
        }
        wp_enqueue_script('jquery'); // Need jQuery.

        add_action(// Very low priority; after footer scripts!
            'wp_footer',
            function () {
                $template = new Template('site/comment-form/sso-op-scripts.php');
                echo $template->parse(); // Inline `<script></script>`.
            },
            PHP_INT_MAX - 10
        );
    }

    /**
     * Enqueue front-side scripts for comment form subs.
     *
     * @since 141111 First documented version.
     */
    protected function maybeEnqueueCommentFormSubScripts()
    {
        if (!$this->plugin->options['enable']) {
            return; // Nothing to do.
        }
        if (!$this->plugin->options['new_subs_enable']) {
            return; // Nothing to do.
        }
        if (!$this->plugin->options['comment_form_sub_scripts_enable']) {
            if (!$this->plugin->options['comment_form_sub_template_enable']) {
                return; // Nothing to do here.
            }
        }
        if (!is_singular() || !comments_open()) {
            return; // Not applicable.
        }
        wp_enqueue_script('jquery'); // Need jQuery.

        add_action(// Very low priority; after footer scripts!
            'wp_footer',
            function () {
                $template = new Template('site/comment-form/sub-op-scripts.php');
                echo $template->parse(); // Inline `<script></script>`.
            },
            PHP_INT_MAX - 10
        );
    }
}
