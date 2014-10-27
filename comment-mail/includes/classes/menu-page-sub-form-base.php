<?php
/**
 * Menu Page Sub. Form Base
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_sub_form_base'))
	{
		/**
		 * Menu Page Sub. Form Base
		 *
		 * @since 14xxxx First documented version.
		 */
		class menu_page_sub_form_base extends abs_base
		{
			/*
			 * Instance-based properties.
			 */

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
				'ns_id_suffix'   => '-sub-form',
				'ns_name_suffix' => '[sub_form]',
				'class_prefix'   => 'pmp-sub-form-',
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
			 */
			public function __construct($sub_id = NULL)
			{
				parent::__construct();

				if(isset($sub_id)) // Editing?
				{
					$this->is_edit = TRUE; // Flag as `TRUE`.
					$sub_id        = (integer)$sub_id; // Force integer.
					$this->sub     = $this->plugin->utils_sub->get($sub_id);

					if(!$this->sub) // Unexpected scenario; fail w/ message.
						wp_die(__('Subscription ID not found.', $this->plugin->text_domain));
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
				echo '<table class="form-table">';
				echo '   <tbody>';

				echo $this->form_fields->select_row(
					array(
						'placeholder'         => __('Select a Post ID...', $this->plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-thumb-tack"></i> Post ID#', $this->plugin->text_domain),
						'name'                => 'post_id', 'required' => TRUE, 'options' => '%%posts%%', 'current_value' => $this->current_value_for('post_id'),
						'notes'               => __('Required; the Post ID they are subscribed to.', $this->plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'placeholder' => ''),
					));
				echo $this->form_fields->select_row(
					array(
						'placeholder'         => __('— All Comments/Replies —', $this->plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID#', $this->plugin->text_domain),
						'name'                => 'comment_id', 'required' => FALSE, 'options' => '%%comments%%', 'post_id' => $this->current_value_for('post_id'), 'current_value' => $this->current_value_for('comment_id'),
						'notes'               => __('If empty, they\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $this->plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'),
					));
				echo $this->form_fields->select_row(
				// @TODO auto-populate email and names when a user ID is selected and the fields are currently empty.
					array(
						'placeholder'         => __('— N/A; no WP User ID —', $this->plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-user"></i> WP User ID#', $this->plugin->text_domain),
						'name'                => 'user_id', 'required' => FALSE, 'options' => '%%users%%', 'current_value' => $this->current_value_for('user_id'),
						'notes'               => __('Associates subscription w/ a WP User ID (if applicable) to improve statistical reporting.', $this->plugin->text_domain).
						                         ' '.__('If empty, the system will automatically try to find a matching user ID for the email address.', $this->plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'),
					));
				/* -------------------------------------------------------------------- */
				echo $this->form_fields->horizontal_line_row(/* -------------------------------------------------------------------- */);
				/* -------------------------------------------------------------------- */

				echo $this->form_fields->input_row(
					array(
						'type'  => 'email', // For `<input>` type.
						'label' => __('<i class="fa fa-fw fa-envelope-o"></i> Email', $this->plugin->text_domain),
						'name'  => 'email', 'required' => TRUE, 'maxlength' => 100, 'current_value' => $this->current_value_for('email'),
					));
				echo $this->form_fields->input_row(
					array(
						'label' => __('<i class="fa fa-fw fa-pencil-square-o"></i> First Name', $this->plugin->text_domain),
						'name'  => 'fname', 'required' => TRUE, 'maxlength' => 50, 'current_value' => $this->current_value_for('fname'),
					));
				echo $this->form_fields->input_row(
					array(
						'label' => __('<i class="fa fa-fw fa-level-up fa-rotate-90" style="margin-left:1px;"></i> Last Name', $this->plugin->text_domain),
						'name'  => 'lname', 'required' => FALSE, 'maxlength' => 100, 'current_value' => $this->current_value_for('lname'),
					));
				echo $this->form_fields->input_row(
					array(
						'label' => __('<i class="fa fa-fw fa-bullseye"></i> IP Address', $this->plugin->text_domain),
						'name'  => 'insertion_ip', 'required' => FALSE, 'maxlength' => 39, 'current_value' => $this->current_value_for('insertion_ip'),
						'notes' => __('If empty, this is filled automatically when a subscriber confirms or updates their subscription.', $this->plugin->text_domain),
					));
				/* -------------------------------------------------------------------- */
				echo $this->form_fields->horizontal_line_row(/* -------------------------------------------------------------------- */);
				/* -------------------------------------------------------------------- */

				echo $this->form_fields->select_row(
					array(
						'placeholder'          => __('Select a Status...', $this->plugin->text_domain),
						'label'                => __('<i class="fa fa-fw fa-flag-o"></i> Status', $this->plugin->text_domain),
						'name'                 => 'status', 'required' => TRUE, 'options' => '%%status%%', 'current_value' => $this->current_value_for('status'),

						'nested_checkbox_args' => array('name'          => 'process_confirmation', // With additional checkbox option too.
						                                'label'         => __('Request confirmation via email', $this->plugin->text_domain).' <i class="fa fa-envelope-o"></i>',
						                                'current_value' => $this->current_value_for('process_confirmation')),
					));
				echo $this->form_fields->select_row(
					array(
						'placeholder' => __('Select a Delivery Option...', $this->plugin->text_domain),
						'label'       => __('<i class="fa fa-fw fa-paper-plane-o"></i> Deliver', $this->plugin->text_domain),
						'name'        => 'deliver', 'required' => TRUE, 'options' => '%%deliver%%', 'current_value' => $this->current_value_for('deliver'),
						'notes'       => __('Any value that is not <code>asap</code> results in a digest instead of instant notifications.', $this->plugin->text_domain),
					));

				echo '   </tbody>';
				echo '</table>';

				echo '<hr />';

				echo '<p class="submit">';

				if($this->is_edit) // Include the ID and `subscription` we're updating.
					echo $this->form_fields->hidden_input(array('name' => 'ID', 'current_value' => $this->sub->ID));

				echo '   <input type="submit"'.
				     ($this->is_edit  // Are we editing?
					     ? ' value="'.esc_attr(__('Update Subscription', $this->plugin->text_domain)).'"'
					     : ' value="'.esc_attr(__('Create Subscription', $this->plugin->text_domain)).'"').
				     '    class="button button-primary" />';

				echo '</p>';
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
			protected function current_value_for($property)
			{
				if(!($property = (string)$property))
					return NULL; // Not possible.

				if(isset($_REQUEST[__NAMESPACE__]['sub_form'][$property]))
					return trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['sub_form'][$property]));

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
			 * @see menu_page_actions::comment_id_row_via_ajax()
			 */
			public static function comment_id_row_via_ajax($post_id)
			{
				$plugin = plugin();

				if(!current_user_can($plugin->manage_cap))
					if(!current_user_can($plugin->cap))
						return ''; // Unauthenticated; ignore.

				$post_id     = (integer)$post_id;
				$form_fields = new form_fields(static::$form_field_args);

				return $form_fields->select_row(
					array(
						'placeholder'         => __('— All Comments/Replies —', $plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID#', $plugin->text_domain),
						'name'                => 'comment_id', 'required' => FALSE, 'options' => '%%comments%%', 'post_id' => $post_id, 'current_value' => NULL,
						'notes'               => __('If empty, they\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'),
					));
			}

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

				if(!current_user_can($plugin->manage_cap))
					if(!current_user_can($plugin->cap))
						return; // Unauthenticated; ignore.

				$reporting_errors     = FALSE; // Initialize.
				$process_confirmation = !empty($request_args['process_confirmation']);
				$args                 = compact('process_confirmation');

				if(isset($request_args['ID'])) // Updating an existing subscription via ID?
				{
					$sub_updater = new sub_updater($request_args, $args); // Run updater.

					if($sub_updater->did_update()) // Updated successfully?
					{
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