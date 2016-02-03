<?php
/**
 * Sub. Management Sub. Form Base
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{

	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_manage_sub_form_base'))
	{
		/**
		 * Sub. Management Sub. Form Base
		 *
		 * @since 141111 First documented version.
		 */
		class sub_manage_sub_form_base extends abs_base
		{
			/*
			 * Instance-based properties.
			 */

			/**
			 * @var string|null Unique subscription key.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub_key;

			/**
			 * @var boolean Editing?
			 *
			 * @since 141111 First documented version.
			 */
			protected $is_edit;

			/**
			 * @var \stdClass|null Subscription.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub;

			/**
			 * @var form_fields Class instance.
			 *
			 * @since 141111 First documented version.
			 */
			protected $form_fields;

			/*
			 * Static properties.
			 */

			/**
			 * @var array Form field config. args.
			 *
			 * @since 141111 First documented version.
			 */
			protected static $form_field_args = array(
				'ns_id_suffix'   => '-manage-sub-form',
				'ns_name_suffix' => '[manage][sub_form]',
				'class_prefix'   => 'manage-sub-form-',
			);

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

			/**
			 * @var boolean Processing email change?
			 *
			 * @since 141111 First documented version.
			 */
			protected static $processing_email_key_change = FALSE;

			/*
			 * Instance-based constructor.
			 */

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $sub_key Unique subscription key.
			 */
			public function __construct($sub_key = NULL)
			{
				parent::__construct();

				if(isset($sub_key)) // Editing?
				{
					$this->is_edit = TRUE;
					$this->sub_key = trim((string)$sub_key);
					$this->sub     = $this->plugin->utils_sub->get($this->sub_key);
				}
				$this->form_fields = new form_fields(static::$form_field_args);

				$this->maybe_display();
			}

			/*
			 * Instance-based form generation.
			 */

			/**
			 * Displays form.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_display()
			{
				$_this   = $this;
				$sub_key = $this->sub_key;
				$is_edit = $this->is_edit;
				$sub     = $this->sub;

				$form_fields       = $this->form_fields;
				$current_value_for = function ($key_prop) use ($_this)
				{
					return $_this->current_value_for($key_prop);
				};
				$hidden_inputs     = function () use ($_this)
				{
					return $_this->hidden_inputs();
				};
				$processing        = static::$processing;

				$processing_errors      = static::$processing_errors;
				$processing_error_codes = static::$processing_error_codes;
				$processing_errors_html = static::$processing_errors_html;

				$processing_successes        = static::$processing_successes;
				$processing_success_codes    = static::$processing_success_codes;
				$processing_successes_html   = static::$processing_successes_html;
				$processing_email_key_change = static::$processing_email_key_change;

				$error_codes = array(); // Initialize.

				if($this->is_edit && !$this->sub_key)
					$error_codes[] = 'missing_sub_key';

				else if($this->is_edit && !$this->sub
				        && static::$processing
				        && static::$processing_successes
				        && static::$processing_email_key_change
				) $error_codes[] = 'invalid_sub_key_after_email_key_change';

				else if($this->is_edit && !$this->sub)
					$error_codes[] = 'invalid_sub_key';

				else if($this->is_edit && $this->sub_key !== $this->sub->key)
					$error_codes[] = 'invalid_sub_key';

				else if(!$this->is_edit && !$this->plugin->options['enable'])
					$error_codes[] = 'new_subs_disabled';

				else if(!$this->is_edit && !$this->plugin->options['new_subs_enable'])
					$error_codes[] = 'new_subs_disabled';

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sub-actions/manage-sub-form.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/*
			 * Instance-based helpers.
			 */

			/**
			 * Collects current value for a particular property.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $key_prop The key/property to acquire.
			 *
			 * @return string|null The property value; else `NULL`.
			 */
			public function current_value_for($key_prop)
			{
				if(!($key_prop = (string)$key_prop))
					return NULL; // Not possible.

				if(!static::$processing || static::$processing_error_codes)
					if(isset($_REQUEST[__NAMESPACE__]['manage']['sub_form'][$key_prop]))
						return trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['manage']['sub_form'][$key_prop]));

				if($this->is_edit && isset($this->sub->{$key_prop}))
					return trim((string)$this->sub->{$key_prop});

				if(is_null($current_email_latest_info = &$this->cache_key(__FUNCTION__, 'current_email_latest_info')))
					$current_email_latest_info = $this->plugin->utils_sub->current_email_latest_info();

				if(!$this->is_edit && !static::$processing && in_array($key_prop, array('fname', 'lname', 'email'), TRUE))
					// We can try to autofill fname, lname, email for new subscriptions.
					if(!empty($current_email_latest_info->{$key_prop}))
						return trim((string)$current_email_latest_info->{$key_prop});

				if(!$this->is_edit && !static::$processing && in_array($key_prop, array('fname', 'lname', 'email'), TRUE)) {
				    // We can try to autofill fname, lname, email for new subscriptions.
				    $current = wp_get_current_commenter();

				    switch($key_prop) {
				        case 'fname':
				            if(!empty($current['comment_author'])) {
				                return $this->plugin->utils_string->first_name((string)$current['comment_author']);
				            }
				            break;

				        case 'lname':
				            if(!empty($current['comment_author'])) {
				                return $this->plugin->utils_string->last_name((string)$current['comment_author']);
				            }
				            break;

				        case 'email':
				            if(!empty($current['comment_author_email'])) {
				                return (string)$current['comment_author_email'];
				            }
				            break;
				    }
				}
				if(!$this->is_edit && !static::$processing && in_array($key_prop, array('fname', 'lname', 'email'), TRUE)) {
				    // We can try to autofill fname, lname, email for new subscriptions.
				    $current = wp_get_current_user();

				    switch($key_prop) {
				        case 'fname':
				            if(!empty($current->first_name)) {
				                return $this->plugin->utils_string->first_name((string)$current->first_name);
				            }
				            break;

				        case 'lname':
				            if(!empty($current->last_name)) {
				                return $this->plugin->utils_string->last_name('- '.(string)$current->last_name);
				            }
				            break;

				        case 'email':
				            if(!empty($current->email)) {
				                return (string)$current->email;
				            }
				            break;
				    }
				}

				return NULL; // Default value.
			}

			/**
			 * Hidden inputs needed for form processing.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Hidden inputs needed for form processing.
			 *
			 * @TODO Add an nonce to these fields.
			 */
			public function hidden_inputs()
			{
				/* Important for this to come first!
				 * We want form processing to take place first.
				 * i.e. Array keys need to be submitted in a specific order. */
				$hidden_inputs = $this->form_fields->hidden_input(array('name' => '_'))."\n";

				if($this->is_edit && $this->sub)
				{
					$hidden_inputs .= $this->form_fields->hidden_input(
							array(
								'name'          => 'ID',
								'current_value' => $this->sub->ID,
							))."\n";
					$hidden_inputs .= $this->form_fields->hidden_input(
							array(
								'name'          => 'key',
								'current_value' => $this->sub->key,
							))."\n";
					$hidden_inputs .= $this->form_fields->hidden_input(
							array(
								'name'          => 'post_id',
								'current_value' => $this->sub->post_id,
							))."\n";
					$hidden_inputs .= $this->form_fields->hidden_input(
							array(
								'name'          => 'comment_id',
								'current_value' => $this->sub->comment_id,
							))."\n";
					$hidden_inputs .= $this->form_fields->hidden_input(
							array(
								'root_name'     => TRUE,
								'name'          => __NAMESPACE__.'[manage][sub_edit]',
								'current_value' => $this->sub->key,
							))."\n";
				}
				else // Adding a new subscription in this default case.
				{
					$hidden_inputs .= $this->form_fields->hidden_input(
							array(
								'root_name'     => TRUE,
								'name'          => __NAMESPACE__.'[manage][sub_new]',
								'current_value' => 0,
							))."\n";
				}
				$current_summary_nav_vars // Include these too.
					= $this->plugin->utils_url->sub_manage_summary_nav_vars();

				foreach(array_keys(sub_manage_summary::$default_nav_vars) as $_summary_nav_var_key)
					if(isset($current_summary_nav_vars[$_summary_nav_var_key]))
					{
						$hidden_inputs .= $this->form_fields->hidden_input(
								array(
									'root_name'     => TRUE,
									'name'          => __NAMESPACE__.'[manage][summary_nav]['.$_summary_nav_var_key.']',
									'current_value' => (string)$current_summary_nav_vars[$_summary_nav_var_key],
								))."\n";
					}
				unset($_summary_nav_var_key); // Housekeeping.

				return $hidden_inputs;
			}

			/*
			 * Public static processors.
			 */

			/**
			 * Constructs a comment ID row via AJAX.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id A post ID.
			 *
			 * @return string HTML markup for this select field row.
			 *    If no options (or too many options; this returns an input field instead.
			 *
			 * @see sub_manage_actions::sub_form_comment_id_row_via_ajax()
			 */
			public static function comment_id_row_via_ajax($post_id)
			{
				$post_id     = (integer)$post_id;
				$form_fields = new form_fields(static::$form_field_args);

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/sub-actions/manage-sub-form-comment-id-row-via-ajax.php');

				return $template->parse($template_vars);
			}

			/**
			 * Form processor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Incoming action request args.
			 *
			 * @see sub_manage_actions::sub_form()
			 */
			public static function process(array $request_args)
			{
				$plugin = plugin(); // Needed below.

				$args = array(
					'process_confirmation'          => TRUE,
					'user_initiated'                => TRUE,
					'ui_protected_data_keys_enable' => TRUE,
					'ui_protected_data_user'        => wp_get_current_user(),
				); // Behavioral args.

				static::$processing = TRUE; // Flag as `TRUE`; along w/ other statics below.

				if(isset($request_args['key'])) // Key sanitizer; for added security.
					$request_args['key'] = $sub_key = $plugin->utils_sub->sanitize_key($request_args['key']);

				if(isset($request_args['ID'])) // Updating an existing subscription via ID?
				{
					$sub_updater = new sub_updater($request_args, $args); // Run updater.

					if($sub_updater->has_errors()) // Updater has errors?
					{
						static::$processing_errors      = $sub_updater->errors();
						static::$processing_error_codes = $sub_updater->error_codes();
						static::$processing_errors_html = $sub_updater->errors_html();
					}
					else if($sub_updater->did_update()) // Updated?
					{
						static::$processing_successes        = $sub_updater->successes();
						static::$processing_success_codes    = $sub_updater->success_codes();
						static::$processing_successes_html   = $sub_updater->successes_html();
						static::$processing_email_key_change = $sub_updater->email_key_changed();
					}
				}
				else if($plugin->options['enable'] && $plugin->options['new_subs_enable'])
					// This check is for added security only. The form should not be available.
				{
					$sub_inserter = new sub_inserter($request_args, $args); // Run inserter.

					if($sub_inserter->has_errors()) // Inserter has errors?
					{
						static::$processing_errors      = $sub_inserter->errors();
						static::$processing_error_codes = $sub_inserter->error_codes();
						static::$processing_errors_html = $sub_inserter->errors_html();
					}
					else if($sub_inserter->did_insert()) // Inserted?
					{
						static::$processing_successes      = $sub_inserter->successes();
						static::$processing_success_codes  = $sub_inserter->success_codes();
						static::$processing_successes_html = $sub_inserter->successes_html();
					}
				}
			}
		}
	}
}
