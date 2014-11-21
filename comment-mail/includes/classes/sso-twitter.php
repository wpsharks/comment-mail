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
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Incoming request args.
			 */
			public function __construct(array $request_args)
			{
				parent::__construct();

				$this->maybe_handle();
			}

			public function maybe_handle()
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