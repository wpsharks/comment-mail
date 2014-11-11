<?php
/**
 * Menu Page Sub. Edit Form
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_sub_edit_form'))
	{
		/**
		 * Menu Page Sub. Edit Form
		 *
		 * @since 141111 First documented version.
		 */
		class menu_page_sub_edit_form extends menu_page_sub_form_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $sub_id Subscription ID.
			 */
			public function __construct($sub_id)
			{
				parent::__construct((integer)$sub_id);
			}
		}
	}
}