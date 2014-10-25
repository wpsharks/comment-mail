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
			/**
			 * @var \stdClass|null Subscription.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub;

			/**
			 * @var boolean Editing?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_edit;

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
					$sub_id        = (integer)$sub_id; // Force integer.
					$this->sub     = $this->plugin->utils_sub->get($sub_id);
					$this->is_edit = TRUE; // Flag as `TRUE`.
				}
				$this->maybe_display();
			}

			/**
			 * Displays edit form.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				echo '<table class="form-table">';
				echo '   <tbody>';

				echo static::select_field_row( // Select menu if possible; else input field row.
					__('Post ID', $this->plugin->text_domain), 'post_id', TRUE, '%%posts%%', $this->current_value_for('post_id'),
					__('Required; the Post ID they are subscribed to.', $this->plugin->text_domain));

				echo static::select_field_row( // Select menu if possible; else input field row.
					__('Comment ID', $this->plugin->text_domain), 'comment_id', FALSE, '%%comments%%', $this->current_value_for('comment_id'),
					__('If empty, they\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $this->plugin->text_domain),
					array('post_id' => $this->current_value_for('post_id'))); // With behavioral args.

				// echo static::select_field_row( // Select menu if possible; else input field row.
				//	__('WP User ID', $this->plugin->text_domain), 'user_id', FALSE, '%%users%%', $this->current_value_for('user_id'),
				//	__('Associates subscription w/ a WP User ID (if applicable) to improve statistical reporting.', $this->plugin->text_domain));

				/* -------------------------------------------------------------------- */
				echo $this->horizontal_line_row(/* -------------------------------------------------------------------- */);
				/* -------------------------------------------------------------------- */

				echo static::input_field_row(__('Email', $this->plugin->text_domain), 'email', TRUE, $this->current_value_for('email'));
				echo static::input_field_row(__('First Name', $this->plugin->text_domain), 'fname', FALSE, $this->current_value_for('fname'));
				echo static::input_field_row(__('Last Name', $this->plugin->text_domain), 'lname', FALSE, $this->current_value_for('lname'));
				echo static::input_field_row(__('IP Address', $this->plugin->text_domain), 'insertion_ip', FALSE, $this->current_value_for('insertion_ip'));

				/* -------------------------------------------------------------------- */
				echo static::horizontal_line_row(/* -------------------------------------------------------------------- */);
				/* -------------------------------------------------------------------- */

				echo static::select_field_row( // Select menu if possible; else input field row.
					__('Status', $this->plugin->text_domain), 'status', TRUE, '%%status%%', $this->current_value_for('status'),
					'', array('include_checkbox' => 'process_confirmation')); // + checkbox.

				echo static::select_field_row( // Select menu if possible; else input field row.
					__('Deliver', $this->plugin->text_domain), 'deliver', TRUE, '%%deliver%%', $this->current_value_for('deliver'),
					__('Any value that is not <code>asap</code> results in a digest instead of instant notifications.', $this->plugin->text_domain));

				echo '   </tbody>';
				echo '</table>';

				echo '<hr />';

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

				if(isset($_REQUEST[__NAMESPACE__.'_sub_form'][$property]))
					return trim(stripslashes((string)$_REQUEST[__NAMESPACE__.'_sub_form'][$property]));

				if($this->is_edit && $this->sub && isset($this->sub->{$property}))
					return trim((string)$this->sub->{$property});

				return NULL; // Default value.
			}

			/**
			 * Constructs an input field row.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $label Field label.
			 * @param string      $name Field name; formulates `id=""` also.
			 * @param boolean     $required Is this field required?
			 * @param string|null $current_value Current value.
			 * @param string      $notes Any additional notes w/ HTML markup.
			 *
			 * @param array       $args Any additional behavior args.
			 *
			 * @return string HTML markup for this input field row.
			 *
			 * @see process_confirmation_checkbox()
			 */
			protected static function input_field_row($label, $name, $required = FALSE, $current_value = NULL, $notes = '', array $args = array())
			{
				$plugin = plugin(); // Needed below.

				$label         = (string)$label; // Force string.
				$name          = (string)$name; // Force string name.
				$slug          = str_replace('_', '-', $name); // Slugified name.
				$current_value = $plugin->isset_or($current_value, NULL, 'string');
				$notes         = trim((string)$notes); // Force string value.

				$default_args = array(
					'post_id'          => NULL,
					'include_checkbox' => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$post_id          = $plugin->isset_or($args['post_id'], NULL, 'integer');
				$include_checkbox = (string)$args['include_checkbox']; // The type of checkbox.

				$field = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' pmp-sub-form-'.$slug).'">';

				$field .= ' <th scope="row">';
				$field .= '    <label for="'.esc_attr(__NAMESPACE__.'-sub-form-'.$slug).'">'.
				          '       '.$label.($required ?// Change the short description based on this boolean.
						'           <span class="description">'.__('(required) *', $plugin->text_domain).'</span>' : '').
				          '    </label>';
				$field .= ' </th>';

				$field .= ' <td>';
				$field .= '    <input type="text" id="'.esc_attr(__NAMESPACE__.'-sub-form-'.$slug).'"'.
				          '     name="'.esc_attr(__NAMESPACE__.'_sub_form['.$name.']').'"'.
				          '     '.($required ? ' aria-required="true"' : '').
				          '     value="'.esc_attr((string)$current_value).'" />'.

				          '    '.($notes ? '<p class="description">'.$notes.'</p>' : '').

				          ($include_checkbox === 'process_confirmation' // Include checkbox?
					          ? '<p class="checkbox">'.static::process_confirmation_checkbox().'</p>' : '');
				$field .= ' </td>';

				$field .= '</tr>';

				return $field; // HTML markup.
			}

			/**
			 * Constructs a select field row.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string       $label Field label.
			 * @param string       $name Field name; formulates `id=""` also.
			 * @param boolean      $required Is this field required?
			 *
			 * @param string|array $options HTML markup for options; or an associative array of options.
			 *    If this is an array, each key is an option value; each value is an option label.
			 *    There are a few `%%replacement codes%%` supported here also.
			 *
			 * @param string|null  $current_value Current value.
			 * @param string       $notes Any additional notes w/ HTML markup.
			 *
			 * @param array        $args Any additional behavior args.
			 *
			 * @return string HTML markup for this select field row.
			 *    If no options (or too many options; this returns an input field instead.
			 *
			 * @see process_confirmation_checkbox()
			 */
			protected static function select_field_row($label, $name, $required, $options, $current_value = NULL, $notes = '', array $args = array())
			{
				$plugin = plugin(); // Needed below.

				$label         = (string)$label; // Force string.
				$name          = (string)$name; // Force string name.
				$slug          = str_replace('_', '-', $name); // Slugified name.
				$current_value = $plugin->isset_or($current_value, NULL, 'string');
				$notes         = trim((string)$notes); // Force string value.

				$default_args = array(
					'post_id'          => NULL,
					'include_checkbox' => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$post_id          = $plugin->isset_or($args['post_id'], NULL, 'integer');
				$include_checkbox = (string)$args['include_checkbox']; // The type of checkbox.

				if($options === '%%users%%') $options = $plugin->utils_markup->user_select_options($current_value);
				else if($options === '%%posts%%') $options = $plugin->utils_markup->post_select_options($current_value, array('for_comments_only'));
				else if($options === '%%comments%%') $options = $plugin->utils_markup->comment_select_options($post_id, $current_value);
				else if($options === '%%deliver%%') $options = $plugin->utils_markup->deliver_select_options($current_value);
				else if($options === '%%status%%') $options = $plugin->utils_markup->status_select_options($current_value);
				else if(is_array($options)) $options = $plugin->utils_markup->select_options($options, $current_value);

				if(!($options = (string)$options)) // Use an input field instead?
					return static::input_field_row($label, $name, $required, $current_value, $notes, $args);

				$field = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' pmp-sub-form-'.$slug).'">';

				$field .= ' <th scope="row">';
				$field .= '    <label for="'.esc_attr(__NAMESPACE__.'-sub-form-'.$slug).'">'.
				          '       '.$label.($required ?// Change the short description based on this boolean.
						'           <span class="description">'.__('(required) *', $plugin->text_domain).'</span>' : '').
				          '    </label>';
				$field .= ' </th>';

				$field .= ' <td>';
				$field .= '    <select id="'.esc_attr(__NAMESPACE__.'-sub-form-'.$slug).'"'.
				          '     name="'.esc_attr(__NAMESPACE__.'_sub_form['.$name.']').'"'.
				          '     '.($required ? ' aria-required="true"' : '').'>'.
				          '       '.$options.
				          '    </select>'.

				          '    '.($notes ? '<p class="description">'.$notes.'</p>' : '').

				          ($include_checkbox === 'process_confirmation' // Include checkbox?
					          ? '<p class="checkbox">'.static::process_confirmation_checkbox().'</p>' : '');
				$field .= ' </td>';

				$field .= '</tr>';

				return $field; // HTML markup.
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

				$id   = __NAMESPACE__.'-sub-form-process-confirmation';
				$name = __NAMESPACE__.'_sub_form[process_confirmation]';

				$checked = ''; // Initialize; unchecked by default.
				if(!empty($_REQUEST[__NAMESPACE__.'_sub_form']['process_confirmation']))
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
				$field = '<tr>';

				$field .= ' <td colspan="2">';
				$field .= '    <hr />';
				$field .= ' </td>';

				$field .= '</tr>';

				return $field; // HTML markup.
			}

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

				return static::select_field_row(// Select menu if possible; else input field row.
					__('Comment ID', $plugin->text_domain), 'comment_id', FALSE, '%%comments%%', NULL,
					__('If empty they\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $plugin->text_domain),
					array('post_id' => $post_id)); // With behavioral args.
			}
		}
	}
}