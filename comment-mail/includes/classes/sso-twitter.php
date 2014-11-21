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
		class sso_twitter extends abs_base
		{
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

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Incoming request args.
			 */
			public function __construct(array $request_args)
			{
				parent::__construct();

				$default_request_args = array(
					'service'        => NULL,
					'action'         => NULL,

					'oauth_token'    => NULL,
					'oauth_verifier' => NULL,

					'fname'          => NULL,
					'lname'          => NULL,
					'email'          => NULL,
				);
				$this->request_args   = array_merge($default_request_args, $request_args);
				$this->request_args   = array_intersect_key($this->request_args, $default_request_args);

				foreach($this->request_args as $_key => &$_value)
					if(isset($_value)) $_value = trim((string)$_value);
				unset($_key, $_value); // Housekeeping.

				$this->storage = new sso_storage();

				$this->maybe_handle();
			}

			/**
			 * Handle SSO actions.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle()
			{
				if(!$this->plugin->options['enable'])
					return; // Disabled currently.

				if(!$this->plugin->options['new_subs_enable'])
					return; // Disabled currently.

				if(!$this->options['sso_enable'])
					return; // Disabled currently.

				if(!$this->options['sso_twitter_key'] || !$this->options['sso_twitter_secret'])
					return; // Not configured properly.

				if($this->request_args['service'] !== 'twitter')
					return; // Not applicable.

				if($this->request_args['action'] === 'authorize')
					$this->maybe_redirect_to_authorize();

				else if($this->request_args['action'] === 'callback')
					$this->maybe_handle_callback();
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
					$service_factory = new \OAuth\ServiceFactory();
					$credentials     = new \OAuth\Common\Consumer\Credentials(
						$this->plugin->options['sso_twitter_key'],
						$this->plugin->options['sso_twitter_secret'],
						$this->plugin->utils_url->sso_action_url('twitter', 'callback')
					);
					$twitter         = $service_factory->createService('twitter', $credentials, $this->storage);
					/** @var $twitter \OAuth\OAuth1\Service\Twitter */

					if(($token = $twitter->requestRequestToken()))
						if(($url = $twitter->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()))))
							wp_redirect($url).exit();

					throw new \exception(__('Failed to generate authorization URL.', $this->plugin->text_domain));
				}
				catch(\exception $exception)
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
					$service_factory = new \OAuth\ServiceFactory();
					$credentials     = new \OAuth\Common\Consumer\Credentials(
						$this->plugin->options['sso_twitter_key'],
						$this->plugin->options['sso_twitter_secret'],
						$this->plugin->utils_url->current()
					);
					$twitter         = $service_factory->createService('twitter', $credentials, $this->storage);
					/** @var $twitter \OAuth\OAuth1\Service\Twitter */

					$twitter->requestAccessToken(
						$this->request_args['oauth_token'],
						$this->request_args['oauth_verifier'],
						$this->storage->retrieveAccessToken('twitter')->getRequestTokenSecret()
					);
					if(!is_object($twitter_user = json_decode($twitter->request('account/verify_credentials.json'))))
						throw new \exception(__('Failed to verify credentials.', $this->plugin->text_domain));

					$process_redirect_args = array(
						'fname'       => $fname,
						'lname'       => $lname,
						'email'       => $email,

						'redirect_to' => '',
					);
					$this->plugin->utils_sso->process_redirect('twitter', $twitter_user->id_str, $process_redirect_args);
				}
				catch(\exception $exception)
				{
					$this->plugin->utils_log->maybe_debug($exception);
				}
			}
		}
	}
}