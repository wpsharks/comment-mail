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
					'code'           => NULL,

					'fname'          => NULL,
					'lname'          => NULL,
					'email'          => NULL,
				);
				$this->request_args   = array_merge($default_request_args, $request_args);
				$this->request_args   = array_intersect_key($this->request_args, $default_request_args);

				if(!$this->request_args['redirect_to'])
					$this->request_args['redirect_to'] = home_url('/');

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

				if(!$this->options['sso_'.$this->service.'_key'] || !$this->options['sso_'.$this->service.'_secret'])
					return; // Not configured properly.

				if($this->request_args['service'] !== $this->service)
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
			abstract protected function maybe_redirect_to_authorize();

			/**
			 * Handle SSO; i.e. account generation or login.
			 *
			 * @since 141111 First documented version.
			 */
			abstract protected function maybe_handle_callback();
		}
	}
}