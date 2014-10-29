<?php
/**
 * Sub. Management Actions
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_manage_actions'))
	{
		/**
		 * Sub. Management Actions
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_manage_actions extends abs_base
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
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]['manage']))
					return; // Not applicable.

				foreach((array)$_REQUEST[__NAMESPACE__]['manage'] as $_action => $_request_args)
					if($_action && is_string($_action) && method_exists($this, $_action))
						$this->{$_action}($this->plugin->utils_string->trim_strip_deep($_request_args));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * Summary handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function summary($request_args)
			{
				$sub_key = ''; // Initialize.

				if(is_string($request_args)) // A string indicates a sub key.
					$sub_key = trim($request_args); // Use as current key.

				if($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key)))
					$this->plugin->utils_sub->set_current_email($sub->email);

				new sub_manage_summary($sub_key);
				exit(); // Stop after display; always.
			}

			/**
			 * Acquires comment ID row via AJAX.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 *
			 * @see sub_manage_form_base::comment_id_row_via_ajax()
			 */
			protected function sub_form_comment_id_row_via_ajax($request_args)
			{
				if(!($request_args = (array)$request_args))
					return; // Empty request args.

				if(!isset($request_args['post_id']))
					return; // Missing post ID.

				if(($post_id = (integer)$request_args['post_id']) < 0)
					return; // Invalid post ID.

				exit(sub_manage_sub_form_base::comment_id_row_via_ajax($post_id));
			}

			/**
			 * Form handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sub_form($request_args)
			{
				if(!($request_args = (array)$request_args))
					return; // Empty request args.

				sub_manage_sub_form_base::process($request_args);
				// Do NOT stop; allow `edit|new` action to run also.
			}

			/**
			 * New subscription handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sub_new($request_args)
			{
				$request_args = NULL; // N/A.

				new sub_manage_sub_new_form();
				exit(); // Stop after display; always.
			}

			/**
			 * Edit subscription handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sub_edit($request_args)
			{
				$sub_key = ''; // Initialize.

				if(is_string($request_args)) // A string indicates a sub key.
					$sub_key = trim($request_args); // Use as current.

				new sub_manage_sub_edit_form($sub_key);
				exit(); // Stop after display; always.
			}
		}
	}
}