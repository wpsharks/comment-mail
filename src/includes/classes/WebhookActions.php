<?php
/**
 * Webhook Actions.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Webhook Actions.
 *
 * @since 141111 First documented version.
 */
class WebhookActions extends AbsBase
{
    /**
     * @var array Valid actions.
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
            'rve_mandrill',
            'rve_sparkpost',
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
     * RVE Webhook for SparkPost.
     *
     * @since 161118 Adding SparkPost integration.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function rveSparkPost($request_args)
    {
        $key = trim((string) $request_args);

        new RveSparkPost($key);

        exit(); // Stop; always.
    }

    /**
     * RVE Webhook for Mandrill.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function rveMandrill($request_args)
    {
        $key = trim((string) $request_args);

        new RveMandrill($key);

        exit(); // Stop; always.
    }
}
