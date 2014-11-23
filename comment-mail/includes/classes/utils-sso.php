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

				$user_exists = $user_id = $this->user_exists($service, $sso_id, $args);

				if(!$user_exists || !$user_id)
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

				$args_no_cache_false = array_merge($args, array('no_cache' => FALSE));
				$args_no_cache_true  = array_merge($args, array('no_cache' => TRUE));

				$user_exists = $user_id = $this->user_exists($service, $sso_id, $args);

				if($user_exists) // If so, just log them in now.
					return $this->auto_login($service, $sso_id, $args_no_cache_false);

				$fname = trim((string)$args['fname']);
				$lname = trim((string)$args['lname']);
				$email = trim((string)$args['email']);

				$fname = $this->plugin->utils_string->first_name($fname, $email);

				$no_cache = (boolean)$args['no_cache']; // Fresh check(s)?

				if(!$fname || !$email || !is_email($email)
				   || $this->plugin->utils_user->email_exists_on_blog($email, $no_cache)
				) return FALSE; // Invalid; or email exists on this blog already.

				# Handle the insertion of this user now.

				$first_name   = $fname; // Data from above.
				$last_name    = $lname; // Data from above.
				$display_name = $fname; // Data from above.

				$user_email = $email; // Data from above.
				$user_login = strtolower('sso'.$this->plugin->utils_enc->uunnci_key_20_max());
				$user_pass  = wp_generate_password();

				if(is_multisite()) // On networks, there are other considerations.
				{
					$user_data = compact('first_name', 'last_name', 'display_name', 'user_login', 'user_pass');
					if(is_wp_error($user_id = wp_insert_user($user_data)) || !$user_id)
						return FALSE; // Insertion failure.

					// So WP will allow duplicate email addresses across child blogs.
					$user_data_update = array_merge(array('ID' => $user_id), compact('user_email'));
					if(is_wp_error(wp_update_user($user_data_update))) // Update email address.
						return FALSE; // Update failure on email address.

					if(!add_user_to_blog(get_current_blog_id(), $user_id, get_option('default_role')))
						return FALSE;// Failed to add the user to this blog.
				}
				else // Just a single DB query will do; i.e. we can set the email address on insertion.
				{
					$user_data = compact('first_name', 'last_name', 'display_name', 'user_email', 'user_login', 'user_pass');
					if(is_wp_error($user_id = wp_insert_user($user_data)) || !$user_id)
						return FALSE; // Insertion failure.
				}
				update_user_option($user_id, __NAMESPACE__.'_'.$service.'_sso_id', $sso_id);

				return $this->auto_login($service, $sso_id, $args_no_cache_true);
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
					'service'     => NULL,
					'action'      => NULL,
					'redirect_to' => NULL,

					'sso_id'      => NULL,
					'_wpnonce'    => NULL,

					'fname'       => NULL,
					'lname'       => NULL,
					'email'       => NULL,
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$default_args = array(
					'service'     => '',
					'action'      => '',
					'redirect_to' => '',

					'sso_id'      => '',
					'_wpnonce'    => '',

					'fname'       => '',
					'lname'       => '',
					'email'       => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($service = trim((string)$request_args['service'])))
					$service = trim((string)$args['service']);

				if(!($action = trim((string)$request_args['action'])))
					$action = trim((string)$args['action']);

				if(!($redirect_to = trim((string)$request_args['redirect_to'])))
					$redirect_to = trim((string)$args['redirect_to']);

				if(!($sso_id = trim((string)$request_args['sso_id'])))
					$sso_id = trim((string)$args['sso_id']);

				if(!($_wpnonce = trim((string)$request_args['_wpnonce'])))
					$_wpnonce = trim((string)$args['_wpnonce']);

				if(!($fname = trim((string)$request_args['fname'])))
					$fname = trim((string)$args['fname']);

				if(!($lname = trim((string)$request_args['lname'])))
					$lname = trim((string)$args['lname']);

				if(!($email = trim((string)$request_args['email'])))
					$email = trim((string)$args['email']);

				$form_fields   = new form_fields(
					array(
						'ns_name_suffix' => '[sso]',
						'ns_id_suffix'   => '-sso-complete-form',
						'class_prefix'   => 'sso-complete-form-',
					));
				$_this         = $this; // Needed by this closure.
				$hidden_inputs = function () use ($_this, $form_fields, $service, $redirect_to, $sso_id)
				{
					return $_this->hidden_inputs_for_completion(get_defined_vars());
				};
				$error_codes   = array(); // Initialize error codes array.

				if($action === 'complete') // Processing completion?
				{
					if(!$service) // Service is missing?
						$error_codes[] = 'missing_service';

					else if(!$sso_id) // SSO ID is missing?
						$error_codes[] = 'missing_sso_id';

					else if(!wp_verify_nonce($_wpnonce, __NAMESPACE__.'_sso_complete'))
						$error_codes[] = 'invalid_wpnonce';

					if(!$fname) // First name is missing?
						$error_codes[] = 'missing_fname';

					if(!$email) // Email address is missing?
						$error_codes[] = 'missing_email';

					else if(!is_email($email)) // Invalid email?
						$error_codes[] = 'invalid_email';

					else if($this->plugin->utils_user->email_exists_on_blog($email))
						$error_codes[] = 'email_exists'; // Exists on this blog already.
				}
				else if($action === 'callback') // Handle duplicate email on callback.
					// Note: only occurs if an account exists w/ a different underlying SSO ID.
					// Otherwise, for existing accounts w/ a matching SSO ID, we automatically log them in.
				{
					if($email && $this->plugin->utils_user->email_exists_on_blog($email))
						$error_codes[] = 'email_exists'; // Exists on this blog already.
				}
				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sso-actions/complete.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Hidden inputs for a completion request form.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Specs and/or behavioral args.
			 *
			 * @return string Hidden inputs for a completion request form.
			 */
			public function hidden_inputs_for_completion(array $args = array())
			{
				$default_args = array(
					'form_fields' => NULL,

					'service'     => '',
					'redirect_to' => '',

					'sso_id'      => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				/** @var $form_fields form_fields Reference for IDEs. */
				if(!(($form_fields = $args['form_fields']) instanceof form_fields))
					return ''; // Not possible.

				$service     = trim(strtolower((string)$args['service']));
				$redirect_to = trim((string)$args['redirect_to']);
				$sso_id      = trim((string)$args['sso_id']);

				$hidden_inputs = ''; // Initialize.

				$hidden_inputs .= $form_fields->hidden_input(
						array(
							'name'          => 'service',
							'current_value' => $service,
						))."\n";
				$hidden_inputs .= $form_fields->hidden_input(
						array(
							'name'          => 'action',
							'current_value' => 'complete',
						))."\n";
				$hidden_inputs .= $form_fields->hidden_input(
						array(
							'name'          => 'redirect_to',
							'current_value' => $redirect_to,
						))."\n";
				$hidden_inputs .= $form_fields->hidden_input(
						array(
							'name'          => 'sso_id', // Encrypted for security.
							'current_value' => $this->plugin->utils_enc->encrypt($sso_id),
						))."\n";
				$hidden_inputs .= $form_fields->hidden_input(
						array(
							'name'          => '_wpnonce',
							'current_value' => wp_create_nonce(__NAMESPACE__.'_sso_complete'),
						))."\n";
				$sso_get_vars = !empty($_GET[__NAMESPACE__]['sso']) ? (array)$_GET[__NAMESPACE__]['sso'] : array();
				$sso_get_vars = $this->plugin->utils_string->trim_strip_deep($sso_get_vars);

				foreach($sso_get_vars as $_sso_var_key => $_sso_var_value)
				{
					if(!in_array($_sso_var_key, array('action', 'service', 'redirect_to', 'sso_id', '_wpnonce'), TRUE))
						$hidden_inputs .= $form_fields->hidden_input(
								array(
									'name'          => $_sso_var_key,
									'current_value' => (string)$_sso_var_value,
								))."\n";
				}
				unset($_sso_var_key, $_sso_var_value); // Housekeeping.

				return $hidden_inputs;
			}
		}
	}
}