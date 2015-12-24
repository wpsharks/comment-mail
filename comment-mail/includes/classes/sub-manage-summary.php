<?php
/**
 * Sub. Management Summary
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class sub_manage_summary extends abs_base
		{
			/*
			 * Instance-based properties.
			 */

			/**
			 * @var string Unique subscription key.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub_key;

			/**
			 * @var string Email address via key.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub_email;

			/**
			 * @var array WP user IDs associated w/ email address.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub_user_ids;

			/**
			 * @var array WP user ID-based list of email addresses.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub_user_id_emails;

			/**
			 * @var \stdClass Query vars.
			 *
			 * @since 141111 First documented version.
			 */
			protected $query_vars;

			/**
			 * @var \stdClass[] Subscriptions.
			 *
			 * @since 141111 First documented version.
			 */
			protected $subs; // Array of subs.

			/**
			 * @var \stdClass|null Pagination vars.
			 *
			 * @since 141111 First documented version.
			 */
			protected $pagination_vars;

			/*
			 * Static properties.
			 */

			/**
			 * @var boolean Processing form?
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing = FALSE;

			/**
			 * @var array Any processing errors.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_errors = array();

			/**
			 * @var array Any processing error codes.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_error_codes = array();

			/**
			 * @var array Any processing errors w/ HTML markup.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_errors_html = array();

			/**
			 * @var array Any processing successes.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_successes = array();

			/**
			 * @var array Any processing success codes.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_success_codes = array();

			/**
			 * @var array Any processing successes w/ HTML markup.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_successes_html = array();

			/*
			 * Public static properties.
			 */

			/**
			 * @var array Default nav vars.
			 *
			 * @since 141111 First documented version.
			 */
			public static $default_nav_vars = array(
				'page'    => 1,
				'post_id' => NULL,
				'status'  => '',
			);

			/*
			 * Instance-based constructor.
			 */

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $sub_key Unique subscription key (optional).
			 *    If this is empty (or invalid), we use the sub's current email address.
			 *
			 * @param array  $request_args An array of any nav request args.
			 */
			public function __construct($sub_key = '', array $request_args = array())
			{
				parent::__construct();

				if(($this->sub_key = trim((string)$sub_key)))
					$this->sub_email = $this->plugin->utils_sub->key_to_email($this->sub_key);

				if(!$this->sub_email) // Fallback on current email address.
				{
					$this->sub_key   = ''; // Key empty/invalid in this case.
					$this->sub_email = $this->plugin->utils_sub->current_email();
				}
				$this->sub_user_ids       = array(); // Initialize.
				$this->sub_user_id_emails = array(); // Initialize.

				if($this->sub_email) // Do we have an email address?
					$this->sub_user_ids = $this->plugin->utils_sub->email_user_ids($this->sub_email);

				if($this->sub_email) // Do we have an email address?
					$this->sub_user_id_emails = $this->plugin->utils_sub->email_user_id_emails($this->sub_email);

				$default_request_args = static::$default_nav_vars;
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->query_vars = new \stdClass; // Initialize.

				$this->query_vars->current_page = max(1, (integer)$request_args['page']);
				$upper_max_limit                = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				$this->query_vars->per_page     = (integer)$this->plugin->options['sub_manage_summary_max_limit'];
				if($this->query_vars->per_page > $upper_max_limit) $this->query_vars->per_page = $upper_max_limit;

				$this->query_vars->post_id = $this->isset_or($request_args['post_id'], NULL, 'integer');
				$this->query_vars->status  = trim(strtolower((string)$request_args['status']));

				$this->subs            = array(); // Initialize.
				$this->pagination_vars = NULL; // Initialize.

				$this->maybe_display();
			}

			/*
			 * Instance-based summary generation.
			 */

			/**
			 * Displays summary.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_display()
			{
				$sub_key            = $this->sub_key;
				$sub_email          = $this->sub_email;
				$sub_user_ids       = $this->sub_user_ids;
				$sub_user_id_emails = $this->sub_user_id_emails;
				$sub_emails         = $this->sub_user_id_emails;

				$query_vars = $this->query_vars;

				$subs            = $this->subs;
				$pagination_vars = $this->pagination_vars;

				$processing = static::$processing;

				$processing_errors      = static::$processing_errors;
				$processing_error_codes = static::$processing_error_codes;
				$processing_errors_html = static::$processing_errors_html;

				$processing_successes      = static::$processing_successes;
				$processing_success_codes  = static::$processing_success_codes;
				$processing_successes_html = static::$processing_successes_html;

				$error_codes = array(); // Initialize.

				if(!$this->sub_email && $this->sub_key)
					$error_codes[] = 'invalid_sub_key';

				else if(!$this->sub_email)
					$error_codes[] = 'missing_sub_key';

				if(!$error_codes) // i.e. have email?
				{
					$this->prepare_subs();
					$subs            = $this->subs;
					$pagination_vars = $this->pagination_vars;
				}
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

			protected function prepare_subs()
			{
				$post_id = $this->query_vars->post_id;
				$status  = $this->query_vars->status;

				$current_offset = $this->current_offset();
				$max_limit      = $this->query_vars->per_page;

				$calc_found_rows = 0; // Initialize.
				$this->subs      = array(); // Initialize.

				$sql = "SELECT SQL_CALC_FOUND_ROWS *". // w/ calc enabled.
				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE (`email` = '".esc_sql($this->sub_email)."'".
				       // See `assets/sma-diagram.png` for further details on this.
				       ($this->sub_user_ids ? // Only if we DO have user IDs to look for here.
				       "    OR `user_id` IN('".implode("','", array_map('esc_sql', $this->sub_user_ids))."')"
				           : '').')'.

				       (isset($post_id) // Specific post ID?
					       ? " AND `post_id` = '".esc_sql($post_id)."'" : '').

				       ($status // Specific status in the request?
					       ? " AND `status` = '".esc_sql($status)."'" : '').
				       " AND `status` NOT IN('unconfirmed', 'trashed')".

				       " ORDER BY".
				       " `post_id` ASC,".
				       " `comment_id` ASC,".
				       " `email` ASC,".
				       " `status` ASC".

				       " LIMIT ".esc_sql($current_offset).",".esc_sql($max_limit);

				if(($results = $this->plugin->utils_db->wp->get_results($sql)))
				{
					$this->subs      = $results = $this->plugin->utils_db->typify_deep($results);
					$calc_found_rows = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()");
				}
				$this->set_pagination_vars($calc_found_rows);
			}

			/**
			 * Gets current SQL offset.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return integer Current SQL offset value.
			 */
			protected function current_offset()
			{
				return ($this->query_vars->current_page - 1) * $this->query_vars->per_page;
			}

			/**
			 * Set pagination vars.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $calc_found_rows `SQL_CALC_FOUND_ROWS`.
			 */
			protected function set_pagination_vars($calc_found_rows)
			{
				$current_page = $this->query_vars->current_page;
				$per_page     = $this->query_vars->per_page;

				$total_subs  = (integer)$calc_found_rows;
				$total_pages = ceil($total_subs / $per_page);

				$this->pagination_vars = (object)get_defined_vars();
			}

			/*
			 * Public static processors.
			 */

			/**
			 * Deletion processor.
			 *
			 * @param string $sub_key Unique subscription key.
			 *
			 * @since 141111 First documented version.
			 */
			public static function delete($sub_key)
			{
				$plugin = plugin(); // Needed below.

				static::$processing = TRUE; // Flag as `TRUE`.

				$errors = $successes = array(); // Initialize.

				$sub_key = $plugin->utils_sub->sanitize_key($sub_key);

				$delete_args = array('user_initiated' => TRUE);
				$deleted     = $plugin->utils_sub->delete($sub_key, $delete_args);
				$deleted === NULL ? 'invalid_sub_key' : 'sub_already_unsubscribed';

				if($deleted === NULL) // Invalid sub key?
					$errors['sub_key'] = __('Invalid subscription key; unable to delete.', 'comment-mail');

				else if(!$deleted) // Subscription has already been deleted?
					$errors['sub_key'] = __('Already deleted; thanks.', 'comment-mail');

				else $successes['deleted_successfully'] = __('Subscription deleted successfully.', 'comment-mail');

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
