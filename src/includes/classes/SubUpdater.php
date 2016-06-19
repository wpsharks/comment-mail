<?php
/**
 * Sub Updater.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Updater.
 *
 * @since 141111 First documented version.
 */
class SubUpdater extends SubInserter
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Arguments to the constructor.
     *                            These should NOT be trusted; they come from a `$_REQUEST` action.
     * @param array $args         Any additional behavioral args.
     */
    public function __construct(array $request_args, array $args = [])
    {
        if (!isset($request_args['ID'])) {
            $request_args['ID'] = -1;
        }
        parent::__construct($request_args, $args);
    }
}
