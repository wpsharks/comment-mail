<?php
/**
 * Sub. Management Sub. Edit Form
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_manage_sub_edit_form'))
	{
		/**
		 * Sub. Management Sub. Edit Form
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_manage_sub_edit_form extends sub_manage_sub_form_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $sub_id Subscription ID.
			 *
			 * @param integer $sub_key Unique subscription key.
			 *    This MUST match the key for the subscription ID.
			 *    This is validated as an extra security measure.
			 */
			public function __construct($sub_id, $sub_key)
			{
				parent::__construct((integer)$sub_id, (string)$sub_key);
			}
		}
	}
}