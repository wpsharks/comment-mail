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
			 * Processes SSO and redirects.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $service The service name we are dealing with.
			 * @param string $sso_id The SSO service's ID for this user.
			 *
			 * @param array  $args Add additional specs and/or behavioral args.
			 *
			 * @return boolean `TRUE` on success, or `FALSE` on failure.
			 */
			public function process_redirect($service, $sso_id, array $args = array())
			{
				if(!($service = trim(strtolower((string)$service))))
					return FALSE; // Not possible.

				if(!($sso_id = trim((string)$sso_id)))
					return FALSE; // Not possible.

				$default_args = array(
					'fname'       => '',
					'lname'       => '',
					'email'       => '',

					'redirect_to' => '',
					'no_cache'    => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($redirect_to = trim((string)$args['redirect_to'])))
					$redirect_to = home_url('/'); // Default location.

				$user_exists = $this->user_exists($service, $sso_id, $args);

				if($user_exists) // Log them in.
					$auto_success = $this->auto_login($service, $sso_id, array_merge($args, array('no_cache' => FALSE)));
				else $auto_success = $this->auto_register_login($service, $sso_id, array_merge($args, array('no_cache' => FALSE)));

				wp_safe_redirect($redirect_to); // Require safe host to prevent offsite redirections.

				return $auto_success && $redirect_to ? TRUE : FALSE;
			}

			/**
			 * Attempt to log a user in automatically.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $service The service name we are dealing with.
			 * @param string $sso_id The SSO service's ID for this user.
			 *
			 * @param array  $args Add additional specs and/or behavioral args.
			 *
			 * @return boolean `TRUE` on a successful/automatic login.
			 */
			public function auto_login($service, $sso_id, array $args = array())
			{
				if(!($service = trim(strtolower((string)$service))))
					return FALSE; // Not possible.

				if(!($sso_id = trim((string)$sso_id)))
					return FALSE; // Not possible.

				$default_args = array(
					'no_cache' => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($user_id = $this->user_exists($service, $sso_id, $args)))
					return FALSE; // Not possible.

				wp_set_auth_cookie($user_id); // Set cookie.

				return TRUE; // User is now logged into their account.
			}

			/**
			 * Attempt to register and log a user in automatically.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $service The service name we are dealing with.
			 * @param string $sso_id The SSO service's ID for this user.
			 *
			 * @param array  $args Add additional specs and/or behavioral args.
			 *
			 * @return boolean `TRUE` on a successful/automatic registration & login.
			 */
			public function auto_register_login($service, $sso_id, array $args = array())
			{
				if(!($service = trim(strtolower((string)$service))))
					return FALSE; // Not possible.

				if(!($sso_id = trim((string)$sso_id)))
					return FALSE; // Not possible.

				$default_args = array(
					'fname'    => '',
					'lname'    => '',
					'email'    => '',

					'no_cache' => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if($this->user_exists($service, $sso_id, $args)) // Exists already?
					return $this->auto_login($service, $sso_id, array_merge($args, array('no_cache' => FALSE)));

				$fname = trim((string)$args['fname']);
				$lname = trim((string)$args['lname']);
				$email = trim((string)$args['email']);

				$fname = $this->plugin->utils_string->first_name($fname, $email);
				$lname = $this->plugin->utils_string->last_name($lname);

				if(!$fname || !$email || !is_email($email))
					return FALSE; // Invalid data.

				# Handle the insertion of this user now.

				$first_name   = $fname; // Data from above.
				$last_name    = $lname; // Data from above.
				$display_name = $fname; // Data from above.

				$user_email = $email; // Data from above.
				$user_login = 'sso'.$this->plugin->utils_enc->uunnci_key_20_max();
				$user_pass  = wp_generate_password();

				$user_data = compact('first_name', 'last_name', 'display_name', 'user_email', 'user_login', 'user_pass');
				if(is_wp_error($user_id = wp_insert_user($user_data)) || !$user_id)
					return FALSE; // Insertion failure.

				update_user_option($user_id, __NAMESPACE__.'_'.$service.'_sso_id', $sso_id);

				return $this->auto_login($service, $sso_id, array_merge($args, array('no_cache' => FALSE)));
			}

			/**
			 * Check if an account exists already.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $service The service name we are dealing with.
			 * @param string $sso_id The SSO service's ID for this user.
			 *
			 * @param array  $args Add additional specs and/or behavioral args.
			 *
			 * @return integer A matching WP user ID, else `0` on failure.
			 */
			public function user_exists($service, $sso_id, array $args = array())
			{
				if(!($service = trim(strtolower((string)$service))))
					return 0; // Not possible.

				if(!($sso_id = trim((string)$sso_id)))
					return 0; // Not possible.

				$default_args = array(
					'no_cache' => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$no_cache = (boolean)$args['no_cache'];

				$cache_keys = compact('service', 'sso_id');
				if(!is_null($user_id = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $user_id; // Already cached this.

				$meta_key = $this->plugin->utils_db->wp->prefix.__NAMESPACE__.'_'.$service.'_sso_id';

				$matching_user_ids_sql = // Find a matching SSO ID in the `wp_users` table; for this blog.

					"SELECT `user_id` FROM `".esc_sql($this->plugin->utils_db->wp->usermeta)."`".
					" WHERE `meta_key` = '".esc_sql($meta_key)."'".
					" AND `meta_value` = '".esc_sql($sso_id)."'";

				$sql = // Find a user ID matching the SSO ID; if possible.

					"SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->wp->users)."`".
					" WHERE `ID` IN(".$matching_user_ids_sql.") LIMIT 1";

				return ($user_id = (integer)$this->plugin->utils_db->wp->get_var($sql));
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
					'fname' => '',
					'lname' => '',
					'email' => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$action_url = $this->plugin->utils_url->current();

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