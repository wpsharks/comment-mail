<?php
/**
 * Sub. Management Actions
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class sub_manage_actions extends abs_base
		{
			/**
			 * @var array Valid actions.
			 *
			 * @since 141111 First documented version.
			 */
			protected $valid_actions;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->valid_actions
					= array(
					'summary',
					'summary_nav',

					'sub_form',
					'sub_form_comment_id_row_via_ajax',

					'sub_new',
					'sub_edit',
					'sub_delete',
				);
				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]['manage']))
					return; // Not applicable.

				foreach((array)$_REQUEST[__NAMESPACE__]['manage'] as $_action => $_request_args)
					if($_action && in_array($_action, $this->valid_actions, TRUE))
						$this->{$_action}($this->plugin->utils_string->trim_strip_deep($_request_args));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * Summary handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function summary($request_args)
			{
				$sub_key = ''; // Initialize.

				if(is_string($request_args)) // Key sanitizer.
					$sub_key = $this->plugin->utils_sub->sanitize_key($request_args);

				if($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key)))
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				$nav_vars = $this->plugin->utils_url->sub_manage_summary_nav_vars();

				new sub_manage_summary($sub_key, $nav_vars);

				exit(); // Stop after display; always.
			}

			/**
			 * Summary nav vars handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function summary_nav($request_args)
			{
				return; // Simply a placeholder.
				// Summary navigation vars are used by other actions.
			}

			/**
			 * Form handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sub_form($request_args)
			{
				if(!($request_args = (array)$request_args))
					return; // Empty request args.

				if(isset($request_args['key'])) // Key sanitizer.
					$request_args['key'] = $this->plugin->utils_sub->sanitize_key($request_args['key']);

				sub_manage_sub_form_base::process($request_args);
				// Do NOT stop; allow `edit|new` action to run also.
			}

			/**
			 * Acquires comment ID row via AJAX.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 *
			 * @see sub_manage_form_base::comment_id_row_via_ajax()
			 */
			protected function sub_form_comment_id_row_via_ajax($request_args)
			{
				if(!($request_args = (array)$request_args))
					exit; // Empty request args.

				if(!isset($request_args['post_id']))
					exit; // Missing post ID.

				if(($post_id = (integer)$request_args['post_id']) < 0)
					exit; // Invalid post ID.

				exit(sub_manage_sub_form_base::comment_id_row_via_ajax($post_id));
			}

			/**
			 * New subscription handler.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sub_edit($request_args)
			{
				$sub_key = $this->plugin->utils_sub->sanitize_key($request_args);

				if($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key)))
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				new sub_manage_sub_edit_form($sub_key);

				exit(); // Stop after display; always.
			}

			/**
			 * Subscription deletion handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sub_delete($request_args)
			{
				$sub_key = $this->plugin->utils_sub->sanitize_key($request_args);

				if($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key)))
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				sub_manage_summary::delete($sub_key);
				// Do NOT stop; allow `summary` action to run also.
			}
		}
	}
}