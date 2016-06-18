<?php
/**
 * API Functions.
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

class comment_mail
{
	public static function sub_ops()
	{
		new \comment_mail\comment_form_after(TRUE);
	}
}
