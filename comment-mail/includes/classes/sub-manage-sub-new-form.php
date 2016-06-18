<?php
/**
 * Sub. Management Sub. New Form
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_manage_sub_new_form'))
	{
		/**
		 * Sub. Management Sub. New Form
		 *
		 * @since 141111 First documented version.
		 */
		class sub_manage_sub_new_form extends sub_manage_sub_form_base
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
		}
	}
}