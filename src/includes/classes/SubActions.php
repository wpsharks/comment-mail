<?php
/**
 * Subscriber Actions.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Subscriber Actions.
 *
 * @since 141111 First documented version.
 */
class SubActions extends AbsBase
{
    /**
     * @type array Valid actions.
     *
     * @since 141111 First documented version.
     */
    protected $valid_actions;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->valid_actions = [
            'confirm',
            'unsubscribe',
            'unsubscribe_all',

            'manage',
        ];
        $this->maybeHandle();
    }

    /**
     * Action handler.
     *
     * @since 141111 First documented version.
     */
    protected function maybeHandle()
    {
        if (is_admin()) {
            return; // Not applicable.
        }
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Not applicable.
        }
        foreach ((array) $_REQUEST[GLOBAL_NS] as $_action => $_request_args) {
            if ($_action && in_array($_action, $this->valid_actions, true)) {
                $_method = preg_replace_callback('/_(.)/', function ($m) {
                    return strtoupper($m[1]);
                }, strtolower($_action));
                $this->{$_method}($this->plugin->utils_string->trimStripDeep($_request_args));
            }
        }
        unset($_action, $_method, $_request_args); // Housekeeping.
    }

    /**
     * Confirm handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function confirm($request_args)
    {
        $sub_key             = '';
        $user_initiated      = true;
        $process_list_server = false;

        // Initialize others needed by template.
        $sub = $sub_post = $sub_comment = null;

        $error_codes = []; // Initialize.

        $sub_key = (string) $request_args;
        if (stripos($sub_key, '.pls') !== false) {
            list($sub_key)       = explode('.pls', $sub_key, 2);
            $process_list_server = true; // Processing.
        }
        if (!($sub_key = $this->plugin->utils_sub->sanitizeKey($sub_key))) {
            $error_codes[] = 'missing_sub_key';
        } elseif (!($sub = $this->plugin->utils_sub->get($sub_key))) {
            $error_codes[] = 'invalid_sub_key';
        } elseif (!($sub_post = get_post($sub->post_id))) {
            $error_codes[] = 'sub_post_id_missing';
        } elseif ($sub->comment_id && !($sub_comment = get_comment($sub->comment_id))) {
            $error_codes[] = 'sub_comment_id_missing';
        }
        if (!$error_codes) { // If no errors; set current email.
            $this->plugin->utils_sub->setCurrentEmail($sub_key, $sub->email);
        }
        if (!$error_codes && !($confirmed = $this->plugin->utils_sub->confirm($sub->ID, compact('user_initiated', 'process_list_server')))) {
            $error_codes[] = $confirmed === null ? 'invalid_sub_key' : 'sub_already_confirmed';
        }
        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/sub-actions/confirmed.php');

        status_header(200); // Status header.
        nocache_headers(); // Disallow caching.
        header('Content-Type: text/html; charset=UTF-8');

        exit($template->parse($template_vars));
    }

    /**
     * Unsubscribe handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function unsubscribe($request_args)
    {
        $sub_key        = '';
        $user_initiated = true;

        // Initialize others needed by template.
        $sub = $sub_post = $sub_comment = null;

        $error_codes = []; // Initialize.

        if (!($sub_key = $this->plugin->utils_sub->sanitizeKey($request_args))) {
            $error_codes[] = 'missing_sub_key';
        } elseif (!($sub = $this->plugin->utils_sub->get($sub_key))) {
            $error_codes[] = 'invalid_sub_key';
        }
        if (!$error_codes) {
            $sub_post = get_post($sub->post_id);
        }
        if (!$error_codes && $sub->comment_id) {
            $sub_comment = get_comment($sub->comment_id);
        }
        if (!$error_codes) { // Note: this MUST come before deletion.
            $this->plugin->utils_sub->setCurrentEmail($sub_key, $sub->email);
        }
        if (!$error_codes && !($deleted = $this->plugin->utils_sub->delete($sub->ID, compact('user_initiated')))) {
            $error_codes[] = $deleted === null ? 'invalid_sub_key' : 'sub_already_unsubscribed';
        }
        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/sub-actions/unsubscribed.php');

        status_header(200); // Status header.
        nocache_headers(); // Disallow caching.
        header('Content-Type: text/html; charset=UTF-8');

        exit($template->parse($template_vars));
    }

    /**
     * Unsubscribe ALL handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function unsubscribeAll($request_args)
    {
        $sub_email = ''; // Initialize.

        $error_codes = []; // Initialize.

        if (!($sub_email = $this->plugin->utils_enc->decrypt($request_args))) {
            $error_codes[] = 'missing_sub_email';
        }
        $delete_args = ['user_initiated' => true]; // Deletion args.
        if (!$error_codes && !($deleted = $this->plugin->utils_sub->deleteEmailUserAll($sub_email, $delete_args))) {
            $error_codes[] = 'sub_already_unsubscribed_all';
        }
        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/sub-actions/unsubscribed-all.php');

        status_header(200); // Status header.
        nocache_headers(); // Disallow caching.
        header('Content-Type: text/html; charset=UTF-8');

        exit($template->parse($template_vars));
    }

    /**
     * Manage handler w/ sub. actions.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function manage($request_args)
    {
        $sub_key = ''; // Initialize.

        if (is_string($request_args)) { // Key sanitizer.
            $sub_key = $this->plugin->utils_sub->sanitizeKey($request_args);
        }
        if ($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key))) {
            $this->plugin->utils_sub->setCurrentEmail($sub_key, $sub->email);
        }
        if (!is_array($request_args)) { // If NOT a sub action, redirect to one.
            wp_redirect($this->plugin->utils_url->subManageSummaryUrl($sub_key));
            exit();
        }
        new SubManageActions(); // Handle sub. manage actions.
    }
}
