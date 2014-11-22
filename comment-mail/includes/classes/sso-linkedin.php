<?php
/**
 * SSO for LinkedIn
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sso_linkedin'))
	{
		/**
		 * SSO for LinkedIn
		 *
		 * @since 141111 First documented version.
		 */
		class sso_linkedin extends sso_service_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Incoming request args.
			 */
			public function __construct(array $request_args)
			{
				parent::__construct('linkedin', $request_args);
			}

			/**
			 * Handle SSO authorization redirection.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_redirect_to_authorize()
			{
				try // Catch exceptions generated here and log them for debugging.
				{
					$redirect_to = $this->request_args['redirect_to'];

					$service_factory = new \OAuth\ServiceFactory();
					$credentials     = new \OAuth\Common\Consumer\Credentials(
						$this->plugin->options['sso_'.$this->service.'_key'],
						$this->plugin->options['sso_'.$this->service.'_secret'],
						$this->plugin->utils_url->sso_action_url($this->service, 'callback', $redirect_to)
					);
					$service         = $service_factory->createService($this->service, $credentials, $this->storage, array('r_basicprofile', 'r_emailaddress'));
					/** @var $service \OAuth\OAuth2\Service\Linkedin */

					if(($url = $service->getAuthorizationUri()))
						wp_redirect($url).exit(); // Redirect to service and request authorization.

					throw new \exception(__('Failed to acquire authorization URL.', $this->plugin->text_domain));
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->plugin->utils_log->maybe_debug($exception);
				}
			}

			/**
			 * Handle SSO; i.e. account generation or login.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle_callback()
			{
				if(!$this->request_args['code'])
					return; // Not applicable; i.e. no data from service.

				try // Catch exceptions generated here and log them for debugging.
				{
					$redirect_to = $this->request_args['redirect_to'];

					$service_factory = new \OAuth\ServiceFactory();
					$credentials     = new \OAuth\Common\Consumer\Credentials(
						$this->plugin->options['sso_'.$this->service.'_key'],
						$this->plugin->options['sso_'.$this->service.'_secret'],
						$this->plugin->utils_url->sso_action_url($this->service, 'callback', $redirect_to)
					);
					$service         = $service_factory->createService($this->service, $credentials, $this->storage, array('r_basicprofile', 'r_emailaddress'));
					/** @var $service \OAuth\OAuth2\Service\Linkedin */

					$token = $service->requestAccessToken($this->request_args['code'], $this->request_args['state']);

					# Acquire and validate data received from this service.

					if(!is_object($service_user = json_decode($service->request('/people/~?format=json'))))
						throw new \exception(__('Failed to verify user.', $this->plugin->text_domain));

					if(empty($service_user->id)) // Must have a unique ID reference.
						throw new \exception(__('Failed to obtain user.', $this->plugin->text_domain));

					foreach(array('firstName', 'lastName', 'formattedName', 'emailAddress') as $_prop)
					{
						if(!isset($service_user->{$_prop}))
							$service_user->{$_prop} = '';

						if(strcasecmp($service_user->{$_prop}, 'private') === 0)
							$service_user->{$_prop} = ''; // If `private`; empty.
					}
					unset($_prop); // Just a little housekeeping.

					if(!($fname = $this->request_args['fname']))
						$fname = $this->plugin->utils_string->first_name(
							$this->coalesce($service_user->firstName, $service_user->formattedName),
							$this->coalesce($this->request_args['email'], $service_user->emailAddress)
						);
					if(!($lname = $this->request_args['lname']))
						$lname = $this->plugin->utils_string->last_name(
							$this->coalesce($service_user->lastName, $service_user->formattedName),
							$this->coalesce($this->request_args['email'], $service_user->emailAddress)
						);
					$email = $this->coalesce($this->request_args['email'], $service_user->emailAddress);

					if(!$fname || !$email) // Do we have minimum requirements?
					{
						$request_completion_args = compact('fname', 'lname', 'email');
						exit($this->plugin->utils_sso->request_completion($this->request_args, $request_completion_args));
					}
					# Process and perform redirection.

					$sso_id                = (string)$service_user->id;
					$process_redirect_args = compact('fname', 'lname', 'email', 'redirect_to');

					if(!$this->plugin->utils_sso->process_redirect($this->service, $sso_id, $process_redirect_args))
						throw new \exception(__('Failed to redirect user.', $this->plugin->text_domain));

					exit; // Always stop here; assuming a redirection success in this case.
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->plugin->utils_log->maybe_debug($exception);
				}
			}
		}
	}
}