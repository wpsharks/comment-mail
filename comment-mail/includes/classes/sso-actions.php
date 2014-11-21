<?php
/**
 * SSO Actions
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sso_actions'))
	{
		/**
		 * SSO Actions
		 *
		 * @since 141111 First documented version.
		 */
		class sso_actions extends abs_base
		{
			/**
			 * @var array Valid actions.
			 *
			 * @since 141111 First documented version.
			 */
			protected $valid_actions;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->valid_actions
					= array(
					'sso',
				);
				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Not applicable.

				$cb_r_args = array(); // Initialize callback request args.
				$_r        = $this->plugin->utils_string->trim_strip_deep($_REQUEST);

				foreach(array('oauth_token', 'oauth_verifier') as $_cb_r_arg_key)
					if(isset($_r[$_cb_r_arg_key])) $cb_r_args[$_cb_r_arg_key] = $_r[$_cb_r_arg_key];
				unset($_cb_r_arg_key); // Housekeeping.

				foreach((array)$_REQUEST[__NAMESPACE__] as $_action => $_request_args)
					if($_action && in_array($_action, $this->valid_actions, TRUE) && is_array($_request_args))
						$this->{$_action}(array_merge($cb_r_args, $this->plugin->utils_string->trim_strip_deep($_request_args)));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * SSO actions for various services.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sso(array $request_args)
			{
				if(empty($request_args['service']))
					return; // Empty service identifier.

				if(!in_array($request_args['service'], array('twitter', 'facebook', 'google', 'linkedin'), TRUE))
					return; // Invalid import type.

				if(!class_exists($class = '\\'.__NAMESPACE__.'\\sso_'.$request_args['service']))
					return; // Invalid service identifier.

				new $class($request_args);

				exit(); // Stop; always.
			}
		}
	}
}