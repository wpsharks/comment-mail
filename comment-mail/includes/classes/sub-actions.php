<?php
/**
 * Subscriber Actions
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_actions'))
	{
		/**
		 * Subscriber Actions
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_actions extends abs_base
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

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Not applicable.

				foreach((array)$_REQUEST[__NAMESPACE__] as $_action => $_request_args)
					if($_action && is_string($_action) && method_exists($this, $_action))
						$this->{$_action}($this->plugin->utils_string->trim_strip_deep($_request_args));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * Confirm handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function confirm($request_args)
			{
				$sub_key = ''; // Initialize.

				// Initialize others needed by template.
				$sub = $sub_post = $sub_comment = NULL;

				$error_codes = array(); // Initialize.

				if(!($sub_key = $this->plugin->utils_sub->sanitize_key($request_args)))
					$error_codes[] = 'missing_sub_key';

				else if(!($sub = $this->plugin->utils_sub->get($sub_key)))
					$error_codes[] = 'invalid_sub_key';

				else if(!($sub_post = get_post($sub->post_id)))
					$error_codes[] = 'sub_post_id_missing';

				else if($sub->comment_id && !($sub_comment = get_comment($sub->comment_id)))
					$error_codes[] = 'sub_comment_id_missing';

				$confirm_args = array('user_initiated' => TRUE); // Confirmation args.
				if(!$error_codes && !($confirmed = $this->plugin->utils_sub->confirm($sub->ID, $confirm_args)))
					$error_codes[] = $confirmed === NULL ? 'invalid_sub_key' : 'sub_already_confirmed';

				if(!$error_codes) // If not errors; set current email.
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sub-actions/confirmed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Unsubscribe handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function unsubscribe($request_args)
			{
				$sub_key = ''; // Initialize.

				// Initialize others needed by template.
				$sub = $sub_post = $sub_comment = NULL;

				$error_codes = array(); // Initialize.

				if(!($sub_key = $this->plugin->utils_sub->sanitize_key($request_args)))
					$error_codes[] = 'missing_sub_key';

				else if(!($sub = $this->plugin->utils_sub->get($sub_key)))
					$error_codes[] = 'invalid_sub_key';

				if($sub && !$error_codes)
					$sub_post = get_post($sub->post_id);

				if($sub && !$error_codes && $sub->comment_id)
					$sub_comment = get_comment($sub->comment_id);

				$delete_args = array('user_initiated' => TRUE); // Deletion args.
				if(!$error_codes && !($deleted = $this->plugin->utils_sub->delete($sub->ID, $delete_args)))
					$error_codes[] = $deleted === NULL ? 'invalid_sub_key' : 'sub_already_unsubscribed';

				if(!$error_codes) // If not errors; set current email.
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sub-actions/unsubscribed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			// @TODO Add an unsubscribe_all handler.

			/**
			 * Manage handler w/ sub. actions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function manage($request_args)
			{
				$sub_key = ''; // Initialize.

				if(is_string($request_args)) // Key sanitizer.
					$sub_key = $this->plugin->utils_sub->sanitize_key($request_args);

				if($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key)))
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				if(!is_array($request_args)) // If NOT a sub action, redirect to one.
					wp_redirect($this->plugin->utils_url->sub_manage_summary_url($sub_key)).exit();

				new sub_manage_actions(); // Handle sub. manage actions.
			}
		}
	}
}