<?php
/**
 * Sub. Management Sub. Form Base
 *
 * @since 14xxxx First documented version.
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
		 * @since 14xxxx First documented version.
		 */
		class sub_manage_sub_form_base extends abs_base
		{
			/*
			 * Instance-based properties.
			 */

			/**
			 * @var string|null Unique subscription ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_id;

			/**
			 * @var string|null Unique subscription key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_key;

			/**
			 * @var boolean Editing?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_edit;

			/**
			 * @var \stdClass|null Subscription.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub;

			/**
			 * @var form_fields Class instance.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $form_fields;

			/*
			 * Static properties.
			 */

			/**
			 * @var array Form field config. args.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $form_field_args = array(
				'ns_id_suffix'   => '-manage-sub-form',
				'ns_name_suffix' => '[manage][sub_form]',
				'class_prefix'   => 'manage-sub-form-',
			);

			/*
			 * Instance-based constructor.
			 */

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $sub_id Subscription ID.
			 *
			 * @param integer $sub_key Unique subscription key.
			 *    This MUST match the key for the subscription ID.
			 *    This is validated as an added security measure.
			 */
			public function __construct($sub_id = NULL, $sub_key = NULL)
			{
				parent::__construct();

				if(isset($sub_id) || isset($sub_key))
				{
					$this->is_edit = TRUE;
					$this->sub_id  = (integer)$sub_id;
					$this->sub_key = trim((string)$sub_key);
					$this->sub     = $this->plugin->utils_sub->get($this->sub_id);
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
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				$sub_id      = $this->sub_id;
				$sub_key     = $this->sub_key;
				$is_edit     = $this->is_edit;
				$sub         = $this->sub;
				$form_fields = $this->form_fields;
				$error_code  = ''; // Initialize.

				if($this->is_edit && !$this->sub_id)
					$error_code = 'missing_sub_id';

				else if($this->is_edit && !$this->sub_key)
					$error_code = 'missing_sub_key';

				else if($this->is_edit && !$this->sub)
					$error_code = 'invalid_sub_id';

				else if($this->is_edit && $this->sub_key !== $this->sub->key)
					$error_code = 'invalid_sub_key';

				$template_vars = compact('sub_id', 'sub_key', 'is_edit', 'sub', 'form_fields', 'error_code');
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
			 * @since 14xxxx First documented version.
			 *
			 * @param string $property The property to acquire.
			 *
			 * @return string|null The property value; else `NULL`.
			 */
			public function current_value_for($property)
			{
				if(!($property = (string)$property))
					return NULL; // Not possible.

				if(isset($_REQUEST[__NAMESPACE__]['manage']['sub_form'][$property]))
					return trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['manage']['sub_form'][$property]));

				if($this->is_edit && isset($this->sub->{$property}))
					return trim((string)$this->sub->{$property});

				return NULL; // Default value.
			}

			/*
			 * Public static processors.
			 */

			/**
			 * Constructs a comment ID row via AJAX.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id A post ID.
			 *
			 * @return string HTML markup for this select field row.
			 *    If no options (or too many options; this returns an input field instead.
			 *
			 * @see sub_manage_actions::comment_id_row_via_ajax()
			 */
			public static function comment_id_row_via_ajax($post_id)
			{
				$plugin      = plugin();
				$post_id     = (integer)$post_id;
				$form_fields = new form_fields(static::$form_field_args);

				return $form_fields->select_row(
					array(
						'placeholder'         => __('— All Comments/Replies —', $plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID#', $plugin->text_domain),
						'name'                => 'comment_id', 'required' => FALSE, 'options' => '%%comments%%', 'post_id' => $post_id, 'current_value' => NULL,
						'notes'               => __('If empty, you\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'),
					));
				// @ TODO this should conceal the email addresses associated w/ each comment for privacy reasons.
			}

			// @TODO everything below.

			/**
			 * Form processor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $request_args Incoming action request args.
			 *
			 * @see menu_page_actions::sub_form()
			 */
			public static function process(array $request_args)
			{
				$plugin = plugin(); // Needed below.

				$reporting_errors = FALSE; // Initialize.

				$args = array(
					'process_confirmation'          => TRUE,
					'user_initiated'                => TRUE,
					'user_initiated_user'           => wp_get_current_user(),
					'ui_protected_data_keys_enable' => TRUE,
				); // Behavioral args.

				if(isset($request_args['ID'])) // Updating an existing subscription via ID?
				{
					$sub_updater = new sub_updater($request_args, $args); // Run updater.

					if($sub_updater->did_update()) // Updated successfully?
					{
						// @TODO this needs to change for sub. management.
						// Errors/successes should be reported for use in the template file.

						$plugin->enqueue_user_notice( // Queue notice.
							sprintf(__('Subscription ID# <code>%1$s</code> updated successfully.', $plugin->text_domain), esc_html($request_args['ID'])),
							array('transient' => TRUE, 'for_page' => $plugin->utils_env->current_menu_page()));

						$redirect_to = $plugin->utils_url->page_table_nav_vars_only();
					}
					else // There were errors; display those errors to the current user.
					{
						$plugin->enqueue_user_error( // Queue error notice.
							sprintf(__('Failed to update subscription ID# <code>%1$s</code>. Please review the following error(s):', $plugin->text_domain), esc_html($request_args['ID'])).
							'<ul class="pmp-list-items"><li>'.implode('</li><li>', $sub_updater->errors_html()).'</li></ul>',
							array('transient' => TRUE, 'for_page' => $plugin->utils_env->current_menu_page()));
					}
				}
				else // We are doing a new insertion; i.e. a new subscription is being added here.
				{
					$sub_inserter = new sub_inserter($request_args, $args); // Run inserter.

					if($sub_inserter->did_insert()) // Inserted successfully?
					{
						// @TODO this needs to change for sub. management.
						// Errors/successes should be reported for use in the template file.

						$plugin->enqueue_user_notice( // Queue notice.
							sprintf(__('Subscription ID# <code>%1$s</code> created successfully.', $plugin->text_domain), esc_html($sub_inserter->insert_id())),
							array('transient' => TRUE, 'for_page' => $plugin->utils_env->current_menu_page()));

						$redirect_to = $plugin->utils_url->page_table_nav_vars_only();
					}
					else // There were errors; display those errors to the current user.
					{
						$plugin->enqueue_user_error( // Queue error notice.
							__('Failed to create new subscription. Please review the following error(s):', $plugin->text_domain).
							'<ul class="pmp-list-items"><li>'.implode('</li><li>', $sub_inserter->errors_html()).'</li></ul>',
							array('transient' => TRUE, 'for_page' => $plugin->utils_env->current_menu_page()));
					}
				}
				if(!empty($redirect_to)) // If applicable.
				{
					if(headers_sent()) // Output started already?
						exit('      <script type="text/javascript">'.
						     "         document.getElementsByTagName('body')[0].style.display = 'none';".
						     "         location.href = '".$plugin->utils_string->esc_js_sq($redirect_to)."';".
						     '      </script>'.
						     '   </body>'.
						     '</html>');
					wp_redirect($redirect_to).exit();
				}
			}
		}
	}
}