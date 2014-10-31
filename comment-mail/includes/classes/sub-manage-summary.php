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
			protected $sub_key;

			/**
			 * @var string Email address.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_email;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_key Unique subscription key (optional).
			 *    If this is empty, we use the sub's current email address.
			 */
			public function __construct($sub_key = '')
			{
				parent::__construct();

				if(($this->sub_key = trim((string)$sub_key)))
					$this->sub_email = $this->plugin->utils_sub->key_to_email($this->sub_key);
				else $this->sub_email = $this->plugin->utils_sub->current_email();

				$this->maybe_display();
			}

			/**
			 * Displays summary.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				$sub_key     = $this->sub_key;
				$sub_email   = $this->sub_email;
				$error_codes = array(); // Initialize.

				if(!$this->sub_email && $this->sub_key)
					$error_codes[] = 'invalid_sub_key';

				else if(!$this->sub_email)
					$error_codes[] = 'unknown_sub';

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sub-actions/manage-summary.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			public static function delete($sub_key)
			{
				$delete_args = array('user_initiated' => TRUE); // Deletion args.
				if(!$error_codes && !($deleted = $this->plugin->utils_sub->delete($sub->ID, $delete_args)))
					$error_codes[] = $deleted === NULL ? 'invalid_sub_key' : 'sub_already_unsubscribed';
			}
		}
	}
}