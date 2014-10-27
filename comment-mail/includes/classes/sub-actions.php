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
				$key        = ''; // Initialize.
				$sub        = NULL; // Initialize.
				$error_code = ''; // Initialize.

				if(!($key = trim((string)$request_args)))
					$error_code = 'missing_key';

				else if(!($sub = $this->plugin->utils_sub->get($key)))
					$error_code = 'invalid_key';

				$confirm_args = array('user_initiated' => TRUE); // Confirmation args.
				if(!$error_code && !($confirm = $this->plugin->utils_sub->confirm($sub->ID, $confirm_args)))
					$error_code = $confirm === NULL ? 'invalid_key' : 'already_confirmed';

				if(!$error_code) // If not errors; set current email.
					$this->plugin->utils_sub->set_current_email($sub->email);

				$template_vars = compact('key', 'sub', 'error_code');
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
				$key        = ''; // Initialize.
				$sub        = NULL; // Initialize.
				$error_code = ''; // Initialize.

				if(!($key = trim((string)$request_args)))
					$error_code = 'missing_key';

				else if(!($sub = $this->plugin->utils_sub->get($key)))
					$error_code = 'invalid_key';

				$delete_args = array('user_initiated' => TRUE); // Deletion args.
				if(!$error_code && !($delete = $this->plugin->utils_sub->delete($sub->ID, $delete_args)))
					$error_code = $delete === NULL ? 'invalid_key' : 'already_unsubscribed';

				if(!$error_code) // If not errors; set current email.
					$this->plugin->utils_sub->set_current_email($sub->email);

				$template_vars = compact('key', 'sub', 'error_code');
				$template      = new template('site/sub-actions/unsubscribed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Manage handler w/ sub. actions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function manage($request_args)
			{
				$key = ''; // Initialize.

				if(is_string($request_args)) // A string indicates a key.
					$key = trim($request_args); // Use as current key.

				if($key && ($sub = $this->plugin->utils_sub->get($key)))
					$this->plugin->utils_sub->set_current_email($sub->email);

				if(!is_array($request_args)) // If NOT a sub action, redirect to one.
					wp_redirect($this->plugin->utils_url->sub_manage_summary_url($key)).exit();

				new sub_manage_actions(); // Handle sub. manage actions.
			}
		}
	}
}