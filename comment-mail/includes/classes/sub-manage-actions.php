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
		class sub_manage_actions extends abstract_base
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
				$error_code = ''; // Initialize.

				if(is_string($request_args)) // String indicates an email address.
					if(($email = $this->plugin->utils_sub->decrypt_email($request_args)))
						$this->plugin->utils_sub->set_current_email($email);

				$email = $this->plugin->utils_sub->current_email();

				if(!$error_code && !$email)
					$error_code = 'missing_email';

				$template_vars = compact('email', 'error_code');
				$template      = new template('site/sub-actions/manage-summary.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}
		}
	}
}