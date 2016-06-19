<?php
/**
 * Sub. Management Actions.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub. Management Actions.
 *
 * @since 141111 First documented version.
 */
class SubManageActions extends AbsBase
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
            'summary',
            'summary_nav',

            'sub_form',
            'sub_form_comment_id_row_via_ajax',

            'sub_new',
            'sub_edit',
            'sub_delete',
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
        if (empty($_REQUEST[GLOBAL_NS]['manage'])) {
            return; // Not applicable.
        }
        foreach ((array) $_REQUEST[GLOBAL_NS]['manage'] as $_action => $_request_args) {
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
     * Summary handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function summary($request_args)
    {
        $sub_key = ''; // Initialize.

        if (is_string($request_args)) { // Key sanitizer.
            $sub_key = $this->plugin->utils_sub->sanitizeKey($request_args);
        }
        if ($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key))) {
            $this->plugin->utils_sub->setCurrentEmail($sub_key, $sub->email);
        }
        $nav_vars = $this->plugin->utils_url->subManageSummaryNavVars();

        new SubManageSummary($sub_key, $nav_vars);

        exit(); // Stop after display; always.
    }

    /**
     * Summary nav vars handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function summaryNav($request_args)
    {
        return; // Simply a placeholder.
        // Summary navigation vars are used by other actions.
    }

    /**
     * Form handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function subForm($request_args)
    {
        if (!($request_args = (array) $request_args)) {
            return; // Empty request args.
        }
        if (isset($request_args['key'])) { // Key sanitizer.
            $request_args['key'] = $this->plugin->utils_sub->sanitizeKey($request_args['key']);
        }
        SubManageSubFormBase::process($request_args);
        // Do NOT stop; allow `edit|new` action to run also.
    }

    /**
     * Acquires comment ID row via AJAX.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     *
     * @see   SubManageSubFormBase::commentIdRowViaAjax()
     */
    protected function subFormCommentIdRowViaAjax($request_args)
    {
        if (!($request_args = (array) $request_args)) {
            exit; // Empty request args.
        }
        if (!isset($request_args['post_id'])) {
            exit; // Missing post ID.
        }
        if (($post_id = (integer) $request_args['post_id']) < 0) {
            exit; // Invalid post ID.
        }
        exit(SubManageSubFormBase::commentIdRowViaAjax($post_id));
    }

    /**
     * New subscription handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function subNew($request_args)
    {
        $request_args = null; // N/A.

        new SubManageSubNewForm();

        exit(); // Stop after display; always.
    }

    /**
     * Edit subscription handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function subEdit($request_args)
    {
        $sub_key = $this->plugin->utils_sub->sanitizeKey($request_args);

        if ($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key))) {
            $this->plugin->utils_sub->setCurrentEmail($sub_key, $sub->email);
        }
        new SubManageSubEditForm($sub_key);

        exit(); // Stop after display; always.
    }

    /**
     * Subscription deletion handler.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function subDelete($request_args)
    {
        $sub_key = $this->plugin->utils_sub->sanitizeKey($request_args);

        if ($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key))) {
            $this->plugin->utils_sub->setCurrentEmail($sub_key, $sub->email);
        }
        SubManageSummary::delete($sub_key);
        // Do NOT stop; allow `summary` action to run also.
    }
}
