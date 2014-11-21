<?php
/**
 * SSO Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_sso'))
	{
		/**
		 * SSO Utilities
		 *
		 * @since 141111 First documented version.
		 */
		class utils_sso extends abs_base
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

			/**
			 * Request registration completion.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Data in the current SSO request action.
			 * @param array $args Any data we already have; or behavior args.
			 */
			public function request_completion(array $request_args = array(), array $args = array())
			{
				$default_request_args = array(
					'fname' => NULL,
					'lname' => NULL,
					'email' => NULL,
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$default_args = array(
					'fname' => NULL,
					'lname' => NULL,
					'email' => NULL,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($fname = trim((string)$request_args['fname'])))
					$fname = trim((string)$args['fname']);

				if(!($lname = trim((string)$request_args['lname'])))
					$lname = trim((string)$args['lname']);

				if(!($email = trim((string)$request_args['email'])))
					$email = trim((string)$args['email']);

				$error_codes = array(); // Initialize.

				if(isset($request_args['fname']) && !$request_args['fname'])
					$error_codes[] = 'missing_fname';

				if(isset($request_args['lname']) && !$request_args['lname'])
					$error_codes[] = 'missing_lname';

				if(isset($request_args['email']) && !$request_args['email'])
					$error_codes[] = 'missing_email';

				else if(isset($request_args['email']) && !is_email($request_args['email']))
					$error_codes[] = 'invalid_email';

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sso-actions/complete.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}
		}
	}
}