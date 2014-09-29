<?php
/**
 * Actions
 *
 * @package actions
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\actions'))
	{
		/**
		 * Actions
		 *
		 * @package actions
		 * @since 14xxxx First documented version.
		 *
		 * @note (front|back)-end actions share the SAME namespace.
		 *    i.e. `$_REQUEST[__NAMESPACE__][action]`, where `action` should be unique
		 *    across any/all (front|back)-end action handlers.
		 *
		 *    This limitation applies only within each classification (context).
		 *    Front-end actions CAN have the same `[action]` name as a back-end action,
		 *    since they're already called from completely different contexts on-site.
		 */
		class actions // Action handlers.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				$this->maybe_do_sub_actions();
				$this->maybe_do_menu_page_actions();
			}

			/**
			 * Subscriber actions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_do_sub_actions()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do.

				new sub_actions();
			}

			/**
			 * Menu page actions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_do_menu_page_actions()
			{
				if(!is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do.

				new menu_page_actions();
			}
		}
	}
}