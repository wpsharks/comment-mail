<?php
/**
 * Login Form After.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Login Form After.
 *
 * @since 141111 First documented version.
 */
class LoginFormAfter extends AbsBase
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
        if (!$this->plugin->options['login_form_sso_template_enable']) {
            return; // Disabled currently.
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
        $template      = new Template('site/login-form/sso-ops.php');

        echo $template->parse($template_vars);
    }
}
