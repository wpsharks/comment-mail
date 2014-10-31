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
			/*
			 * Instance-based properties.
			 */

			/**
			 * @var string Unique subscription key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_key;

			/**
			 * @var string Email address via key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_email;

			/*
			 * Static properties.
			 */

			/**
			 * @var boolean Processing form?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing = FALSE;

			/**
			 * @var array Any processing successes.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing_successes = array();

			/**
			 * @var array Any processing success codes.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing_success_codes = array();

			/**
			 * @var array Any processing successes w/ HTML markup.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing_successes_html = array();

			/**
			 * @var array Any processing errors.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing_errors = array();

			/**
			 * @var array Any processing error codes.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing_error_codes = array();

			/**
			 * @var array Any processing errors w/ HTML markup.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $processing_errors_html = array();

			/*
			 * Instance-based constructor.
			 */

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

			/*
			 * Instance-based summary generation.
			 */

			/**
			 * Displays summary.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				$sub_key   = $this->sub_key;
				$sub_email = $this->sub_email;

				$user_ids = array(); // Initialize.
				if($this->sub_email) // Do we have an email address?
					$user_ids = $this->plugin->utils_sub->email_user_ids($this->sub_email);

				$processing = static::$processing; // & related vars.

				$processing_successes      = static::$processing_successes;
				$processing_success_codes  = static::$processing_success_codes;
				$processing_successes_html = static::$processing_successes_html;

				$processing_errors      = static::$processing_errors;
				$processing_error_codes = static::$processing_error_codes;
				$processing_errors_html = static::$processing_errors_html;

				$error_codes = array(); // Initialize.

				if(!$this->sub_email && $this->sub_key)
					$error_codes[] = 'invalid_sub_key';

				else if(!$this->sub_email)
					$error_codes[] = 'unknown_sub';

				// @TODO collect data for summary generation here.

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sub-actions/manage-summary.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/*
			 * Instance-based helpers.
			 */

			/*
			 * Public static processors.
			 */

			/**
			 * Deletion processor.
			 *
			 * @param string $sub_key Unique subscription key.
			 *
			 * @since 14xxxx First documented version.
			 */
			public static function delete($sub_key)
			{
				$sub_key = (string)$sub_key;

				$plugin = plugin(); // Needed below.

				static::$processing = TRUE; // Flag as `TRUE`.

				$errors = $successes = array(); // Initialize.

				$delete_args = array('user_initiated' => TRUE);
				$deleted     = $plugin->utils_sub->delete($sub_key, $delete_args);
				$deleted === NULL ? 'invalid_sub_key' : 'sub_already_unsubscribed';

				if($deleted === NULL) // Invalid sub key?
					$errors['sub_key'] = __('Invalid subscription key; unable to delete.', $plugin->text_domain);

				else if(!$deleted) // Subscription has already been deleted?
					$errors['sub_key'] = __('Already deleted; thanks.', $plugin->text_domain);

				else $successes['deleted_successfully'] = __('Subscription deleted successfully.', $plugin->text_domain);

				if($errors) // We have deletion errors to report back?
				{
					static::$processing_errors      = array_merge(static::$processing_errors, $errors);
					static::$processing_error_codes = array_merge(static::$processing_error_codes, array_keys($errors));
					static::$processing_errors_html = array_merge(static::$processing_errors_html, array_map(array($plugin->utils_string, 'markdown_no_p'), $errors));
				}
				else if($successes) // Deleted successfully?
				{
					static::$processing_successes      = array_merge(static::$processing_successes, $successes);
					static::$processing_success_codes  = array_merge(static::$processing_success_codes, array_keys($successes));
					static::$processing_successes_html = array_merge(static::$processing_successes_html, array_map(array($plugin->utils_string, 'markdown_no_p'), $successes));
				}
			}
		}
	}
}