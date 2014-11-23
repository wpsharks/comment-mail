<?php
/**
 * SSO for Google
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sso_google'))
	{
		/**
		 * SSO for Google
		 *
		 * @since 141111 First documented version.
		 */
		class sso_google extends sso_service_base
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
				parent::__construct('google', $request_args);
			}

			/**
			 * Handle SSO authorization redirection.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle_authorize()
			{
				try // Catch exceptions and log them for debugging.
				{
					$service_factory = new \OAuth\ServiceFactory();
					$credentials     = new \OAuth\Common\Consumer\Credentials(
						$this->plugin->options['sso_'.$this->service.'_key'],
						$this->plugin->options['sso_'.$this->service.'_secret'],
						$this->plugin->utils_url->sso_action_url($this->service, 'callback')
					);
					/** @var $service \OAuth\OAuth2\Service\Google */
					$service = $service_factory->createService($this->service, $credentials, $this->storage, array('userinfo_email', 'userinfo_profile'));

					$this->process_authorization_redirect($service->getAuthorizationUri());
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->process_exception($exception);
				}
			}

			/**
			 * Handle SSO; i.e. account generation or login.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle_callback()
			{
				try // Catch exceptions and log them for debugging.
				{
					if(!$this->request_args['code']) // Must have this.
						throw new \exception(__('Missing oAuth code.', $this->plugin->text_domain));

					$service_factory = new \OAuth\ServiceFactory();
					$credentials     = new \OAuth\Common\Consumer\Credentials(
						$this->plugin->options['sso_'.$this->service.'_key'],
						$this->plugin->options['sso_'.$this->service.'_secret'],
						$this->plugin->utils_url->sso_action_url($this->service, 'callback')
					);
					/** @var $service \OAuth\OAuth2\Service\Google */
					$service = $service_factory->createService($this->service, $credentials, $this->storage, array('userinfo_email', 'userinfo_profile'));

					# Request access token via oAuth API provided by this service.

					$service->requestAccessToken($this->request_args['code']);

					# Acquire and validate data received from this service.

					if(!is_object($service_user = json_decode($service->request('https://www.googleapis.com/oauth2/v1/userinfo'))))
						throw new \exception(__('Failed to acquire user.', $this->plugin->text_domain));

					if(empty($service_user->id) || !($sso_id = (string)$service_user->id))
						throw new \exception(__('Failed to obtain user.', $this->plugin->text_domain));

					foreach(array('name', 'given_name', 'email') as $_prop)
					{
						if(!isset($service_user->{$_prop}))
							$service_user->{$_prop} = '';

						if(strcasecmp($service_user->{$_prop}, 'private') === 0)
							$service_user->{$_prop} = ''; // If `private`; empty.
					}
					unset($_prop); // Just a little housekeeping.

					if(!($fname = $this->request_args['fname']))
						$fname = $this->plugin->utils_string->first_name(
							$this->coalesce($service_user->name, $service_user->given_name),
							$this->coalesce($this->request_args['email'], $service_user->email)
						);
					if(!($lname = $this->request_args['lname']))
						$lname = $this->plugin->utils_string->last_name($service_user->name);
					if(!$lname) $lname = $this->plugin->utils_string->last_name($service_user->given_name);

					$email = $this->coalesce($this->request_args['email'], $service_user->email);

					$this->process_callback_complete_redirect(compact('sso_id', 'fname', 'lname', 'email'));
				}
				catch(\exception $exception) // Log for debugging.
				{
					$this->process_exception($exception);
				}
			}
		}
	}
}