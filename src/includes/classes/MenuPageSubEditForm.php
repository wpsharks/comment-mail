<?php
/**
 * Menu Page Sub. Edit Form.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Menu Page Sub. Edit Form.
 *
 * @since 141111 First documented version.
 */
class MenuPageSubEditForm extends MenuPageSubFormBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int $sub_id Subscription ID.
     */
    public function __construct($sub_id)
    {
        parent::__construct((integer) $sub_id);
    }
}
