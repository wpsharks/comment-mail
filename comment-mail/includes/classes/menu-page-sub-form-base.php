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
				}
				$this->maybe_display();
			}

			/*
			 * Instance-based form generation methods.
			 */

			/**
			 * Displays edit form.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				if($this->is_edit && !$this->sub)
					return; // Not possible.

				echo '<table class="form-table">';
				echo '   <tbody>';

				echo static::select_field_row(
					array(
						'placeholder'         => __('Select a Post ID...', $this->plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-thumb-tack"></i> Post ID#', $this->plugin->text_domain),
						'name'                => 'post_id', 'required' => TRUE, 'options' => '%%posts%%', 'current_value' => $this->current_value_for('post_id'),
						'notes'               => __('Required; the Post ID they are subscribed to.', $this->plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'placeholder' => ''),
					));
				echo static::select_field_row(
					array(
						'placeholder'         => __('— All Comments/Replies —', $this->plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID#', $this->plugin->text_domain),
						'name'                => 'comment_id', 'required' => FALSE, 'options' => '%%comments%%', 'post_id' => $this->current_value_for('post_id'), 'current_value' => $this->current_value_for('comment_id'),
						'notes'               => __('If empty, they\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $this->plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'),
					));
				echo static::select_field_row(
					array(
						'placeholder'         => __('— N/A; no WP User ID —', $this->plugin->text_domain),
						'label'               => __('<i class="fa fa-fw fa-user"></i> WP User ID#', $this->plugin->text_domain),
						'name'                => 'user_id', 'required' => FALSE, 'options' => '%%users%%', 'current_value' => $this->current_value_for('user_id'),
						'notes'               => __('Associates subscription w/ a WP User ID (if applicable) to improve statistical reporting.', $this->plugin->text_domain),
						'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'),
					));
				/* -------------------------------------------------------------------- */
				echo $this->horizontal_line_row(/* -------------------------------------------------------------------- */);
				/* -------------------------------------------------------------------- */

				echo static::input_field_row(
					array(
						'type'  => 'email', // For `<input>` type.
						'label' => __('<i class="fa fa-fw fa-envelope-o"></i> Email', $this->plugin->text_domain),
						'name'  => 'email', 'required' => TRUE, 'maxlength' => 100, 'current_value' => $this->current_value_for('email'),
					));
				echo static::input_field_row(
					array(
						'label' => __('<i class="fa fa-fw fa-pencil-square-o"></i> First Name', $this->plugin->text_domain),
						'name'  => 'fname', 'required' => TRUE, 'maxlength' => 50, 'current_value' => $this->current_value_for('fname'),
					));
				echo static::input_field_row(
					array(
						'label' => __('<i class="fa fa-fw fa-level-up fa-rotate-90" style="margin-left:1px;"></i> Last Name', $this->plugin->text_domain),
						'name'  => 'lname', 'required' => FALSE, 'maxlength' => 100, 'current_value' => $this->current_value_for('lname'),
					));
				echo static::input_field_row(
					array(
						'label' => __('<i class="fa fa-fw fa-bullseye"></i> IP Address', $this->plugin->text_domain),
						'name'  => 'insertion_ip', 'required' => FALSE, 'maxlength' => 39, 'current_value' => $this->current_value_for('insertion_ip'),
						'notes' => __('If empty, this is filled automatically when a subscriber confirms or updates their subscription.', $this->plugin->text_domain),
					));
				/* -------------------------------------------------------------------- */
				echo static::horizontal_line_row(/* -------------------------------------------------------------------- */);
				/* -------------------------------------------------------------------- */

				echo static::select_field_row(
					array(
						'placeholder'   => __('Select a Status...', $this->plugin->text_domain),
						'label'         => __('<i class="fa fa-fw fa-flag-o"></i> Status', $this->plugin->text_domain),
						'name'          => 'status', 'required' => TRUE, 'options' => '%%status%%', 'current_value' => $this->current_value_for('status'),
						'checkbox_type' => 'process_confirmation', // With additional checkbox option too.
					));
				echo static::select_field_row(
					array(
						'placeholder' => __('Select a Delivery Option...', $this->plugin->text_domain),
						'label'       => __('<i class="fa fa-fw fa-paper-plane-o"></i> Deliver', $this->plugin->text_domain),
						'name'        => 'deliver', 'required' => TRUE, 'options' => '%%deliver%%', 'current_value' => $this->current_value_for('deliver'),
						'notes'       => __('Any value that is not <code>asap</code> results in a digest instead of instant notifications.', $this->plugin->text_domain),
					));

				echo '   </tbody>';
				echo '</table>';

				echo '<hr />';

				if($this->is_edit) // Include the ID and `subscription` we're updating.
					echo static::hidden_input_field(array('name' => 'ID', 'current_value' => $this->sub ? $this->sub->ID : 0));

				echo '<p class="submit">'.
				     '   <input type="submit"'.
				     ($this->is_edit  // Are we editing?
					     ? ' value="'.esc_attr(__('Update Subscription', $this->plugin->text_domain)).'"'
					     : ' value="'.esc_attr(__('Create Subscription', $this->plugin->text_domain)).'"').
				     '    class="button button-primary" />'.
				     '</p>';
			}

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
			 * Protected static field/row generators.
			 */

			/**
			 * Constructs an input field row.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Specs and behavorial args.
			 *
			 * @return string HTML markup for this input field row.
			 *
			 * @see process_confirmation_checkbox()
			 */
			protected static function input_field_row(array $args = array())
			{
				$plugin = plugin(); // Needed below.

				$default_args = array(
					'type'          => 'text',
					'label'         => '',
					'placeholder'   => '',

					'name'          => '',
					'root_name'     => FALSE,

					'required'      => FALSE,
					'maxlength'     => 0,
					'current_value' => NULL,

					'notes'         => '',
					'post_id'       => NULL,
					'checkbox_type' => '',
					'other_attrs'   => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$type        = trim((string)$args['type']);
				$label       = trim((string)$args['label']);
				$placeholder = trim((string)$args['placeholder']);

				$name      = trim((string)$args['name']);
				$root_name = (boolean)$args['root_name'];

				$slug = str_replace('_', '-', $name);
				$slug = $root_name ? 'root-'.$slug : $slug;
				$id   = __NAMESPACE__.'-sub-form-'.$slug; // Prefixed always.
				$name = $root_name ? $name : __NAMESPACE__.'[sub_form]['.$name.']';

				$required      = (boolean)$args['required'];
				$maxlength     = (integer)$args['maxlength'];
				$current_value = $plugin->isset_or($args['current_value'], NULL, 'string');

				$notes         = trim((string)$args['notes']);
				$post_id       = $plugin->isset_or($args['post_id'], NULL, 'integer');
				$checkbox_type = trim((string)$args['checkbox_type']); // Checkbox type.
				$other_attrs   = trim((string)$args['other_attrs']);

				$row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' pmp-sub-form-'.$slug).'">';

				$row .= ' <th scope="row">';
				$row .= '    <label for="'.esc_attr($id).'">'.
				        '       '.$label.($required ? // Change the short description based on this boolean.
						'           <span class="description">'.__('(required) *', $plugin->text_domain).'</span>' : '').
				        '    </label>';
				$row .= ' </th>';

				$row .= ' <td>';

				if($type === 'hidden') // Special case.
					$row .= static::hidden_input_field($args);

				else $row .= '    <input type="'.esc_attr($type).'"'.

				             '     id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

				             '     aria-required="'.esc_attr($required ? 'true' : 'false').'"'.
				             '     '.($required ? ' required="required"' : ''). // JS validation.

				             '     '.($maxlength ? ' maxlength="'.esc_attr($maxlength).'"' : '').

				             '     data-placeholder="'.esc_attr($placeholder).'"'.
				             '     placeholder="'.esc_attr($placeholder).'"'.

				             '     value="'.esc_attr(trim((string)$current_value)).'"'.

				             '     '.$other_attrs.' />'.

				             '    '.($notes ? '<p class="description">'.$notes.'</p>' : '').

				             ($checkbox_type === 'process_confirmation' // Include checkbox?
					             ? '<p class="checkbox">'.static::process_confirmation_checkbox().'</p>' : '');

				$row .= ' </td>';

				$row .= '</tr>';

				return $row; // HTML markup.
			}

			/**
			 * Constructs a select field row.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Specs and behavorial args.
			 *
			 * @return string HTML markup for this select field row.
			 *    If no options (or too many options; this returns an input field instead.
			 *
			 * @see process_confirmation_checkbox()
			 */
			protected static function select_field_row(array $args = array())
			{
				$plugin = plugin(); // Needed below.

				$default_args = array(
					'type'                => 'text',
					'label'               => '',
					'placeholder'         => '',

					'name'                => '',
					'root_name'           => FALSE,

					'required'            => FALSE,
					'maxlength'           => 0,
					'options'             => '',
					'current_value'       => NULL,

					'notes'               => '',
					'post_id'             => NULL,
					'checkbox_type'       => '',
					'other_attrs'         => '',

					'input_fallback_args' => array(),
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$type        = trim((string)$args['type']);
				$label       = trim((string)$args['label']);
				$placeholder = trim((string)$args['placeholder']);

				$name      = trim((string)$args['name']);
				$root_name = (boolean)$args['root_name'];

				$slug = str_replace('_', '-', $name);
				$slug = $root_name ? 'root-'.$slug : $slug;
				$id   = __NAMESPACE__.'-sub-form-'.$slug; // Prefixed always.
				$name = $root_name ? $name : __NAMESPACE__.'[sub_form]['.$name.']';

				$required      = (boolean)$args['required'];
				$maxlength     = (integer)$args['maxlength'];
				$options       = !is_array($args['options']) ? trim((string)$args['options']) : $args['options'];
				$current_value = $plugin->isset_or($args['current_value'], NULL, 'string');

				$notes         = trim((string)$args['notes']);
				$post_id       = $plugin->isset_or($args['post_id'], NULL, 'integer');
				$checkbox_type = trim((string)$args['checkbox_type']); // Checkbox type.
				$other_attrs   = trim((string)$args['other_attrs']);

				$input_fallback_args = array_merge($args, (array)$args['input_fallback_args']);
				unset($input_fallback_args['input_fallback_args']); // Unset self reference.

				if($options === '%%users%%') $options = $plugin->utils_markup->user_select_options($current_value);
				else if($options === '%%posts%%') $options = $plugin->utils_markup->post_select_options($current_value, array('for_comments_only' => TRUE));
				else if($options === '%%comments%%') $options = $plugin->utils_markup->comment_select_options($post_id, $current_value);
				else if($options === '%%deliver%%') $options = $plugin->utils_markup->deliver_select_options($current_value);
				else if($options === '%%status%%') $options = $plugin->utils_markup->status_select_options($current_value);
				else if(is_array($options)) $options = $plugin->utils_markup->select_options($options, $current_value);

				if(!($options = trim((string)$options))) // No options available?
					return static::input_field_row($input_fallback_args);

				$row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' pmp-sub-form-'.$slug).'">';

				$row .= ' <th scope="row">';
				$row .= '    <label for="'.esc_attr($id).'">'.
				        '       '.$label.($required ? // Change the short description based on this boolean.
						'           <span class="description">'.__('(required) *', $plugin->text_domain).'</span>' : '').
				        '    </label>';
				$row .= ' </th>';

				$row .= ' <td>';
				$row .= '    <select'. // Select menu options.

				        '     id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

				        '     aria-required="'.esc_attr($required ? 'true' : 'false').'"'.
				        '     '.($required ? ' required="required"' : ''). // JS validation.

				        '     '.($maxlength ? ' maxlength="'.esc_attr($maxlength).'"' : '').

				        '     data-placeholder="'.esc_attr($placeholder).'"'.
				        '     placeholder="'.esc_attr($placeholder).'"'.

				        '     '.$other_attrs.'>'.

				        '       '.$options.

				        '    </select>'.

				        '    '.($notes ? '<p class="description">'.$notes.'</p>' : '').

				        ($checkbox_type === 'process_confirmation' // Include checkbox?
					        ? '<p class="checkbox">'.static::process_confirmation_checkbox().'</p>' : '');
				$row .= ' </td>';

				$row .= '</tr>';

				return $row; // HTML markup.
			}

			/**
			 * Constructs a hidden input field.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Specs and behavorial args.
			 *
			 * @return string HTML markup for this hidden input field.
			 */
			protected static function hidden_input_field(array $args = array())
			{
				$plugin = plugin(); // Needed below.

				$default_args = array(
					'name'          => '',
					'root_name'     => FALSE,
					'current_value' => NULL,

					'other_attrs'   => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$name      = trim((string)$args['name']);
				$root_name = (boolean)$args['root_name'];

				$slug = str_replace('_', '-', $name);
				$slug = $root_name ? 'root-'.$slug : $slug;
				$id   = __NAMESPACE__.'-sub-form-'.$slug; // Prefixed always.
				$name = $root_name ? $name : __NAMESPACE__.'[sub_form]['.$name.']';

				$current_value = $plugin->isset_or($args['current_value'], NULL, 'string');

				$other_attrs = trim((string)$args['other_attrs']);

				$field = '<div style="display:none;">'; // Wrapper.

				$field .= '  <input type="hidden"'. // Hidden input var.

				          '   id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

				          '   value="'.esc_attr(trim((string)$current_value)).'"'.

				          '   '.$other_attrs.' />';

				$field .= '</div>';

				return $field;
			}

			/**
			 * Checkbox for processing confirmation.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string HTML markup for this checkbox.
			 *
			 * @see input_field_row()
			 * @see select_field_row()
			 */
			protected static function process_confirmation_checkbox()
			{
				$plugin = plugin(); // Needed below.

				$name = 'process_confirmation';
				$slug = str_replace('_', '-', $name);
				$id   = __NAMESPACE__.'-sub-form-'.$slug;
				$name = __NAMESPACE__.'[sub_form]['.$name.']';

				$checked = ''; // Initialize; unchecked by default.
				if(!empty($_REQUEST[__NAMESPACE__]['sub_form']['process_confirmation']))
					$checked = ' checked="checked"';

				return '<label for="'.esc_attr($id).'" style="margin-left:10px;">'.

				       ' <i class="fa fa-level-up fa-rotate-90"></i>'.
				       ' &nbsp;<input type="checkbox" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="1"'.$checked.' />'.
				       ' '.__('Request confirmation via email', $plugin->text_domain).' <i class="fa fa-envelope-o"></i>'.

				       '</label>';
			}

			/**
			 * Constructs an HR field row.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string HTML markup for this field row.
			 */
			protected static function horizontal_line_row()
			{
				$field = '<tr class="pmp-sub-form-hr-row">';

				$field .= ' <td colspan="2">';
				$field .= '    <hr />';
				$field .= ' </td>';

				$field .= '</tr>';

				return $field; // HTML markup.
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
				$plugin = plugin(); // Needed below.

				$post_id = (integer)$post_id;

				return static::select_field_row(
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