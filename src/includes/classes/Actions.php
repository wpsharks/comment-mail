<?php
/**
 * Actions.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Actions.
 *
 * @since 141111 First documented version.
 *
 * @note (front|back)-end actions share the SAME namespace.
 *    i.e. `$_REQUEST[GLOBAL_NS][action]`, where `action` should be unique
 *    across any/all (front|back)-end action handlers.
 *
 *    This limitation applies only within each classification (context).
 *    Front-end actions CAN have the same `[action]` name as a back-end action,
 *    since they're already called from completely different contexts on-site.
 */
class Actions extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        
        $this->maybeDoSubActions();
        $this->maybeDoWebhookActions();
        $this->maybeDoMenuPageActions();
    }

    

    /**
     * Subscriber actions.
     *
     * @since 141111 First documented version.
     */
    protected function maybeDoSubActions()
    {
        if (is_admin()) {
            return; // Not applicable.
        }
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Nothing to do.
        }
        new SubActions();
    }

    /**
     * Webhook actions.
     *
     * @since 141111 First documented version.
     */
    protected function maybeDoWebhookActions()
    {
        if (is_admin()) {
            return; // Not applicable.
        }
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Nothing to do.
        }
        new WebhookActions();
    }

    /**
     * Menu page actions.
     *
     * @since 141111 First documented version.
     */
    protected function maybeDoMenuPageActions()
    {
        if (!is_admin()) {
            return; // Not applicable.
        }
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Nothing to do.
        }
        new MenuPageActions();
    }
}
