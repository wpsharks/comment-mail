<?php
/**
 * Sub. Management Sub. Edit Form.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub. Management Sub. Edit Form.
 *
 * @since 141111 First documented version.
 */
class SubManageSubEditForm extends SubManageSubFormBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int $sub_key Unique subscription key.
     */
    public function __construct($sub_key)
    {
        parent::__construct((string) $sub_key);
    }
}
