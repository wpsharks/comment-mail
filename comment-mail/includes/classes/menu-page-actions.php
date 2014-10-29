<?php
/**
 * Menu Page Actions
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_actions'))
	{
		/**
		 * Menu Page Actions
		 *
		 * @since 14xxxx First documented version.
		 */
		class menu_page_actions extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_handle()
			{
				if(!is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Not applicable.

				if(!$this->plugin->utils_url->has_valid_nonce())
					return; // Unauthenticated; ignore.

				foreach((array)$_REQUEST[__NAMESPACE__] as $_action => $_request_args)
					if($_action && is_string($_action) && method_exists($this, $_action))
						$this->{$_action}($this->plugin->utils_string->trim_strip_deep($_request_args));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * Restores defaults options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function restore_default_options($request_args)
			{
				$request_args = NULL; // Not used here.

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				delete_option(__NAMESPACE__.'_options');
				$this->plugin->options = $this->plugin->default_options;

				wp_redirect($this->plugin->utils_url->options_restored()).exit();
			}

			/**
			 * Saves options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function save_options($request_args)
			{
				$request_args = (array)$request_args;

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				$this->plugin->options = array_merge($this->plugin->default_options, $this->plugin->options, $request_args);
				$this->plugin->options = array_intersect_key($this->plugin->options, $this->plugin->default_options);
				update_option(__NAMESPACE__.'_options', $this->plugin->options); // Update.

				wp_redirect($this->plugin->utils_url->options_updated()).exit();
			}

			/**
			 * Dismisses a persistent notice.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function dismiss_notice($request_args)
			{
				$request_args = (array)$request_args;

				if(empty($request_args['key']))
					return; // Not possible.

				if(!current_user_can($this->plugin->manage_cap))
					if(!current_user_can($this->plugin->cap))
						return; // Unauthenticated; ignore.

				$notices = get_option(__NAMESPACE__.'_notices');
				if(!is_array($notices)) $notices = array();

				unset($notices[$request_args['key']]);
				update_option(__NAMESPACE__.'_notices', $notices);

				wp_redirect($this->plugin->utils_url->notice_dismissed()).exit();
			}

			/**
			 * Runs a specific import type.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function import($request_args)
			{
				$request_args = (array)$request_args;

				if(empty($request_args['type']) || !is_string($request_args['type']))
					return; // Missing and/or invalid import type.

				if(!in_array($request_args['type'], array('subs', 'stcr'), TRUE))
					return; // Invalid import type.

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				$class    = 'import_'.$request_args['type'];
				$importer = new $class($request_args); // Instantiate.
			}

			/**
			 * Runs a specific export type.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function export($request_args)
			{
				$request_args = (array)$request_args;

				if(empty($request_args['type']) || !is_string($request_args['type']))
					return; // Missing and/or invalid import type.

				if(!in_array($request_args['type'], array('subs'), TRUE))
					return; // Invalid import type.

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				$class    = 'export_'.$request_args['type'];
				$exporter = new $class($request_args); // Instantiate.
			}

			/**
			 * Acquires comment ID row via AJAX.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 *
			 * @see menu_page_sub_form_base::comment_id_row_via_ajax()
			 */
			protected function sub_form_comment_id_row_via_ajax($request_args)
			{
				$request_args = (array)$request_args;

				if(!isset($request_args['post_id']))
					exit; // Missing post ID.

				if(($post_id = (integer)$request_args['post_id']) < 0)
					exit; // Invalid post ID.

				if(!current_user_can($this->plugin->manage_cap))
					if(!current_user_can($this->plugin->cap))
						exit; // Unauthenticated; ignore.

				exit(menu_page_sub_form_base::comment_id_row_via_ajax($post_id));
			}

			/**
			 * Acquires user ID info via AJAX.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 *
			 * @see menu_page_sub_form_base::user_id_info_via_ajax()
			 */
			protected function sub_form_user_id_info_via_ajax($request_args)
			{
				$request_args = (array)$request_args;

				if(!isset($request_args['user_id']))
					exit; // Missing user ID.

				if(($user_id = (integer)$request_args['user_id']) < 0)
					exit; // Invalid user ID.

				if(!current_user_can($this->plugin->manage_cap))
					if(!current_user_can($this->plugin->cap))
						exit; // Unauthenticated; ignore.

				header('Content-Type: application/json; charset=UTF-8');
				exit(menu_page_sub_form_base::user_id_info_via_ajax($user_id));
			}

			/**
			 * Processes sub. form inserts/updates.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 *
			 * @see menu_page_sub_form_base::process()
			 */
			protected function sub_form($request_args)
			{
				if(!($request_args = (array)$request_args))
					return; // Empty request args.

				if(!current_user_can($this->plugin->manage_cap))
					if(!current_user_can($this->plugin->cap))
						return; // Unauthenticated; ignore.

				menu_page_sub_form_base::process($request_args);
			}
		}
	}
}