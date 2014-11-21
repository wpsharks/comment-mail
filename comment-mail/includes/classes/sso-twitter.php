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
					'go'             => '',
					'oauth_token'    => '',
					'oauth_verifier' => '',
				);
				$this->request_args   = array_merge($default_request_args, $request_args);
				$this->request_args   = array_intersect_key($this->request_args, $default_request_args);

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

				if($this->request_args['go'])
					$this->maybe_redirect();

				else if($this->request_args['oauth_token'] && $this->request_args['oauth_verifier'])
					$this->maybe_do_sso();
			}

			/**
			 * Handle SSO authorization redirection. @TODO
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_redirect()
			{
				$service_factory = new \OAuth\ServiceFactory();
				$credentials     = new \OAuth\Common\Consumer\Credentials(
					$this->plugin->options['sso_twitter_key'],
					$this->plugin->options['sso_twitter_secret'],
					$this->plugin->utils_url->current()
				);
				$storage         = new sso_storage(); // Custom storage; via transients.
				$twitter         = $service_factory->createService('twitter', $credentials, $storage);
			}

			/**
			 * Handle SSO; i.e. account generation or login. @TODO
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_do_sso()
			{
				$service_factory = new \OAuth\ServiceFactory();
				$credentials     = new \OAuth\Common\Consumer\Credentials(
					$this->plugin->options['sso_twitter_key'],
					$this->plugin->options['sso_twitter_secret'],
					$this->plugin->utils_url->current()
				);
				$storage         = new sso_storage(); // Custom storage; via transients.
				$twitter         = $service_factory->createService('twitter', $credentials, $storage);
			}
		}
	}
}