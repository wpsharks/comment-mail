<?php
/**
 * SSO for Twitter
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sso_twitter'))
	{
		/**
		 * SSO for Twitter
		 *
		 * @since 141111 First documented version.
		 */
		class sso_twitter extends sso_service_base
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
				parent::__construct('twitter', $request_args);
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
					$service         = $service_factory->createService($this->service, $credentials, $this->storage);
					/** @var $service \OAuth\OAuth1\Service\Twitter */

					if(($token = $service->requestRequestToken())) // Must obtain from Twitter.
						if(($url = $service->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()))))
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
				if(!$this->request_args['oauth_token'] || !$this->request_args['oauth_verifier'])
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
					$service         = $service_factory->createService($this->service, $credentials, $this->storage);
					/** @var $service \OAuth\OAuth1\Service\Twitter */

					$token = $service->requestAccessToken(
						$this->request_args['oauth_token'], $this->request_args['oauth_verifier'],
						$this->storage->retrieveAccessToken($this->service)->getRequestTokenSecret()
					);
					# Acquire and validate data received from this service.

					if(!is_object($service_user = json_decode($service->request('account/verify_credentials.json'))))
						throw new \exception(__('Failed to verify user.', $this->plugin->text_domain));

					if(!isset($service_user->id, $service_user->name) || empty($service_user->id))
						throw new \exception(__('Failed to obtain user.', $this->plugin->text_domain));

					if(!($fname = $this->request_args['fname']))
						$fname = $this->plugin->utils_string->first_name(
							$service_user->name, $this->request_args['email']
						);
					if(!($lname = $this->request_args['lname']))
						$lname = $this->plugin->utils_string->last_name(
							$service_user->name, $this->request_args['email']
						);
					$email = $this->request_args['email']; // From request args only.

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