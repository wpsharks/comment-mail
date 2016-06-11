<?php
/**
 * Logging Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Logging Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsLog extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Debug logger.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $data Input data to log.
     */
    public function maybeDebug($data)
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return; // Nothing to do.
        }
        if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return; // Nothing to do.
        }
        error_log(print_r($data, true));
    }
}
