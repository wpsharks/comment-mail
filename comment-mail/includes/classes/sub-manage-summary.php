<?php
/**
 * Sub. Management Summary
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_manage_summary'))
	{
		/**
		 * Sub. Management Summary
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_manage_summary extends abs_base
		{
			/**
			 * @var string Unique subscription key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $key;

			/**
			 * @var string Email address.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $email;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key Unique subscription key (optional).
			 *    If this is empty, we use the sub's current email address.
			 */
			public function __construct($key = '')
			{
				parent::__construct();

				if(($this->key = trim((string)$key)))
					$this->email = $this->plugin->utils_sub->key_to_email($this->key);
				else $this->email = $this->plugin->utils_sub->current_email();

				$this->maybe_display();
			}

			/**
			 * Displays summary.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				$key        = $this->key;
				$email      = $this->email;
				$error_code = ''; // Initialize.

				if(!$this->email && $this->key)
					$error_code = 'invalid_key';

				else if(!$this->email)
					$error_code = 'unknown_user';

				$template_vars = compact('key', 'email', 'error_code');
				$template      = new template('site/sub-actions/manage-summary.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}
		}
	}
}