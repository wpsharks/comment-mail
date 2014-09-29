<?php
/**
 * Subscriber Actions
 *
 * @package sub_actions
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_actions'))
	{
		/**
		 * Subscriber Actions
		 *
		 * @package sub_actions
		 * @since 14xxxx First documented version.
		 */
		class sub_actions // Subscriber actions.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_handle()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Not applicable.

				foreach((array)$_REQUEST[__NAMESPACE__] as $action => $args)
					if($action && is_string($action) && method_exists($this, $action))
						$this->{$action}($this->plugin->utils_string->trim_strip_deep($args));
			}

			/**
			 * Confirm subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function confirm($args)
			{
				$key        = '';
				$sub        = NULL;
				$error_code = '';

				if(!$error_code && !($key = trim((string)$args)))
					$error_code = 'missing_key';

				if(!$error_code && !($sub = $this->plugin->utils_sub->get($key)))
					$error_code = 'invalid_key';

				if(!$error_code && !($confirm = $this->plugin->utils_sub->confirm($sub->ID, TRUE, $this->plugin->utils_env->user_ip())))
					$error_code = $confirm === NULL ? 'invalid_key' : 'already_confirmed';

				$template_vars = compact('sub', 'error_code');
				$template      = new template('site/sub-actions/confirmed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Unsubscribe.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function unsubscribe($args)
			{
				$key        = '';
				$sub        = NULL;
				$error_code = '';

				if(!$error_code && !($key = trim((string)$args)))
					$error_code = 'missing_key';

				if(!$error_code && !($sub = $this->plugin->utils_sub->get($key)))
					$error_code = 'invalid_key';

				if(!$error_code && !($delete = $this->plugin->utils_sub->delete($sub->ID, TRUE, $this->plugin->utils_env->user_ip())))
					$error_code = $delete === NULL ? 'invalid_key' : 'already_unsubscribed';

				$template_vars = compact('sub', 'error_code');
				$template      = new template('site/sub-actions/unsubscribed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}
		}
	}
}