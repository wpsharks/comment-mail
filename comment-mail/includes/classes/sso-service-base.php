<?php
/**
 * SSO Service Base Abstraction
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sso_service_base'))
	{
		/**
		 * SSO Service Base Abstraction
		 *
		 * @since 141111 First documented version.
		 */
		abstract class sso_service_base extends abs_base
		{
			/*
			 * Properties.
			 */

			/**
			 * @var string Service slug.
			 *
			 * @since 141111 First documented version.
			 */
			protected $service;

			/**
			 * @var array Incoming request args.
			 *
			 * @since 141111 First documented version.
			 */
			protected $request_args;

			/**
			 * @var sso_storage Storage class instance.
			 *
			 * @since 141111 First documented version.
			 */
			protected $storage;

			/*
			 * Constructor.
			 */

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $service Service identifier/slug.
			 * @param array  $request_args Incoming request args.
			 */
			public function __construct($service, array $request_args)
			{
				parent::__construct();

				$this->service = trim((string)$service);

				$default_request_args = array(
					'service'        => NULL,
					'action'         => NULL,
					'redirect_to'    => NULL,

					'oauth_token'    => NULL,
					'oauth_verifier' => NULL,
					'oauth_problem'  => NULL,
					'code'           => NULL,
					'state'          => NULL,

					'sso_id'         => NULL,
					'_wpnonce'       => NULL,

					'fname'          => NULL,
					'lname'          => NULL,
					'email'          => NULL,
				);
				$this->request_args   = array_merge($default_request_args, $request_args);
				$this->request_args   = array_intersect_key($this->request_args, $default_request_args);

				foreach($this->request_args as $_key => &$_value)
					if(isset($_value)) $_value = trim((string)$_value);
				unset($_key, $_value); // Housekeeping.

				# Initialize storage class.

				$this->storage = new sso_storage();

				# Initialize extra data in storage.

				if($this->storage->hasExtra($this->service))
					$extra = $this->storage->retrieveExtra($this->service);
				else $extra = array(); // Initialize.

				# Fill `redirect_to` using request; else extra data in storage.

				if($this->request_args['redirect_to'])
					$extra['redirect_to'] = $this->request_args['redirect_to'];

				else if(!$this->request_args['redirect_to'] && !empty($extra['redirect_to']))
					$this->request_args['redirect_to'] = $extra['redirect_to'];

				if(!$this->request_args['redirect_to']) // Use default value?
					$this->request_args['redirect_to'] = home_url('/');

				# Unencrypt the SSO ID if it was passed in the request; i.e if we expect it to be encrypted.

				if($this->request_args['sso_id']) // Encrypted by completion handler(s).
					$this->request_args['sso_id'] = $this->plugin->utils_enc->decrypt($this->request_args['sso_id']);

				# Store any extra data collected in the routines above.

				$this->storage->storeExtra($this->service, $extra);

				# Handle primary SSO action.

				$this->maybe_handle();
			}

			/*
			 * Primary action handler.
			 */

			/**
			 * Calls secondary SSO actions.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle()
			{
				if(!$this->plugin->options['sso_enable'])
					return; // Disabled currently.

				if(!$this->plugin->options['sso_'.$this->service.'_key'] || !$this->plugin->options['sso_'.$this->service.'_secret'])
					return; // Not configured properly.

				if($this->request_args['service'] !== $this->service)
					return; // Not applicable.

				# Call a secondary action handler for this service.

				if($this->request_args['action'] === 'authorize')
					$this->maybe_handle_authorize();

				else if($this->request_args['action'] === 'callback')
					$this->maybe_handle_callback();

				else if($this->request_args['action'] === 'complete')
					$this->maybe_handle_complete();
			}

			/*
			 * Secondary action handlers.
			 */

			/**
			 * Handle SSO authorization redirection.
			 *
			 * @since 141111 First documented version.
			 *
			 * @extenders Extenders must implement this.
			 */
			abstract protected function maybe_handle_authorize();

			/**
			 * Handle SSO account generation or login.
			 *
			 * @since 141111 First documented version.
			 *
			 * @extenders Extenders must implement this.
			 */
			abstract protected function maybe_handle_callback();

			/**
			 * Handle SSO registration completion.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle_complete()
			{
				try // Catch exceptions and log them for debugging.
				{
					$this->process_callback_complete_redirect($this->request_args);
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->process_exception($exception);
				}
			}

			/*
			 * Processors.
			 */

			/**
			 * Process SSO authorization redirection.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $url Authorization endpoint URL.
			 *
			 * @throws \exception If `$url` is empty.
			 */
			protected function process_authorization_redirect($url)
			{
				try // Catch exceptions and log them for debugging.
				{
					if(!($url = trim((string)$url)))
						throw new \exception(__('Empty authorization URL.', $this->plugin->text_domain));

					wp_redirect($url).exit(); // Redirect and stop here.
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->process_exception($exception).exit();
				}
			}

			/**
			 * Process SSO account generation or login.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Specs and/or behavioral args.
			 *
			 * @throws \exception If auto register/login fails unexpectedly.
			 *    Should not occur given the validations performed here.
			 */
			protected function process_callback_complete_redirect(array $args = array())
			{
				try // Catch exceptions and log them for debugging.
				{
					$default_args = array(
						'sso_id'   => '',
						'_wpnonce' => '',

						'fname'    => '',
						'lname'    => '',
						'email'    => '',
					);
					$args         = array_merge($default_args, $args);
					$args         = array_intersect_key($args, $default_args);

					$sso_id   = trim((string)$args['sso_id']);
					$_wpnonce = trim((string)$args['_wpnonce']);

					$fname = trim((string)$args['fname']);
					$lname = trim((string)$args['lname']);
					$email = trim((string)$args['email']);

					$user_exists = // Do they exist already? i.e. just logging in?
						$sso_id && $this->plugin->utils_sso->user_exists($this->service, $sso_id);

					if(!$sso_id || !$user_exists)
						// If the user exists, we can skip this validation entirely;
						//    i.e. if they exist, we simply log them in.
						if(!$sso_id // Hmm, the SSO ID is missing?

						   || !$this->plugin->utils_user->can_register()

						   || ($this->request_args['action'] !== 'callback'
						       && !wp_verify_nonce($_wpnonce, __NAMESPACE__.'_sso_complete'))

						   || !$fname || !$email || !is_email($email)

						   || $this->plugin->utils_user->email_exists_on_blog($email)

						) // Something is missing or invalid; request manual completion by user.
						{
							$request_completion_args = compact('sso_id', 'fname', 'lname', 'email');
							exit($this->plugin->utils_sso->request_completion($this->request_args, $request_completion_args));
						}
					if($user_exists) // This user already exists?
					{
						if(!$this->plugin->utils_sso->auto_login($this->service, $sso_id, $args))
							throw new \exception(__('Auto login failure.', $this->plugin->text_domain));
						goto redirect; // Perform redirection, the user is now logged-in.
					}
					if(!$this->plugin->utils_sso->auto_register_login($this->service, $sso_id, $args))
						throw new \exception(__('Auto register/login failure.', $this->plugin->text_domain));

					redirect: // Target point; perform redirection.

					if(!($redirect_to = $this->request_args['redirect_to']))
						$redirect_to = home_url('/'); // Default redirection URL.

					echo '<!DOCTYPE html>';

					echo '<html>';
					echo '   <head>';
					echo '      <meta charset="UTF-8" />';
					echo '      <meta name="viewport" content="width=device-width, initial-scale=1.0" />';
					echo '      <title>'.__('Redirecting...', $this->plugin->text_domain).'</title>';
					echo '      <script type="text/javascript">';

					echo "         if(window.parent && window.parent !== window)".
					     "            window.parent.location = '".$this->plugin->utils_string->esc_js_sq($redirect_to)."';".

					     "         else if(window.opener && window.opener !== window)". // Most common scenario.
					     "            window.opener.location = '".$this->plugin->utils_string->esc_js_sq($redirect_to)."', window.close();".

					     "         else". // Redirect in the current window.
					     "            window.location = '".$this->plugin->utils_string->esc_js_sq($redirect_to)."';";

					echo '      </script>';
					echo '   </head>';

					echo '   <body style="margin:30px; background:#EEEEEE;>'.
					     '      <div style="text-align:center; margin:0 auto 0 auto;">'.
					     '         <img src="'.esc_attr($this->plugin->utils_url->to('client-s/images/tiny-progress-bar.gif')).'" />'.
					     '      </div>'.
					     '   </body>';

					exit('</html>'); // Redirect and stop here.
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->process_exception($exception).exit();
				}
			}

			/**
			 * Process SSO exceptions.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \exception $exception The exception to process.
			 */
			protected function process_exception(\exception $exception)
			{
				$this->plugin->utils_log->maybe_debug($exception);

				exit(__('An unexpected error ocurred. Please start over and try again. Sorry!', $this->plugin->text_domain));
			}
		}
	}
}