<?php
/**
 * Form Fields
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\form_fields'))
	{
		/**
		 * Form Fields
		 *
		 * @since 14xxxx First documented version.
		 */
		class form_fields extends abs_base
		{
			/**
			 * @var string Namespaced ID suffix.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $ns_id_suffix;

			/**
			 * @var string Namespaced name suffix.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $ns_name_suffix;

			/**
			 * @var string Class prefix.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $class_prefix;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Configuration args.
			 */
			public function __construct(array $args = array())
			{
				parent::__construct();

				$default_args = array(
					'ns_id_suffix'   => '',
					'ns_name_suffix' => '',
					'class_prefix'   => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$this->ns_id_suffix   = trim((string)$args['ns_id_suffix']);
				$this->ns_name_suffix = trim((string)$args['ns_name_suffix']);
				$this->class_prefix   = trim((string)$args['class_prefix']);
			}

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
			public function input_row(array $args = array())
			{
				$default_args = array(
					'type'                 => 'text',
					'label'                => '',
					'placeholder'          => '',

					'name'                 => '',
					'root_name'            => FALSE,

					'required'             => FALSE,
					'maxlength'            => 0,
					'current_value'        => NULL,

					'notes'                => '',
					'post_id'              => NULL,
					'nested_checkbox_args' => array(),
					'other_attrs'          => '',
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

				$id   = __NAMESPACE__.$this->ns_id_suffix.'-'.$slug;
				$name = $root_name ? $name : __NAMESPACE__.$this->ns_name_suffix.'['.$name.']';

				$required      = (boolean)$args['required'];
				$maxlength     = (integer)$args['maxlength'];
				$current_value = $this->isset_or($args['current_value'], NULL, 'string');

				$notes                = trim((string)$args['notes']);
				$post_id              = $this->isset_or($args['post_id'], NULL, 'integer');
				$nested_checkbox_args = (array)$args['nested_checkbox_args'];
				$other_attrs          = trim((string)$args['other_attrs']);

				$row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' '.$this->class_prefix.$slug).'">';

				$row .= ' <th scope="row">';
				$row .= '    <label for="'.esc_attr($id).'">'.
				        '       '.$label.($required ? // Change the short description based on this boolean.
						'           <span class="description">'.__('(required) *', $this->plugin->text_domain).'</span>' : '').
				        '    </label>';
				$row .= ' </th>';

				$row .= ' <td>';

				if($type === 'hidden') // Special case.
					$row .= $this->hidden_input($args);

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

				             ($nested_checkbox_args // Include a nested checkbox?
					             ? '<p class="checkbox">'.$this->nested_checkbox($nested_checkbox_args).'</p>' : '');

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
			public function select_row(array $args = array())
			{
				$default_args = array(
					'type'                 => 'text',
					'label'                => '',
					'placeholder'          => '',

					'name'                 => '',
					'root_name'            => FALSE,

					'required'             => FALSE,
					'maxlength'            => 0,
					'options'              => '',
					'current_value'        => NULL,

					'notes'                => '',
					'post_id'              => NULL,
					'nested_checkbox_args' => array(),
					'other_attrs'          => '',

					'input_fallback_args'  => array(),
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

				$id   = __NAMESPACE__.$this->ns_id_suffix.'-'.$slug;
				$name = $root_name ? $name : __NAMESPACE__.$this->ns_name_suffix.'['.$name.']';

				$required      = (boolean)$args['required'];
				$maxlength     = (integer)$args['maxlength'];
				$options       = !is_array($args['options']) ? trim((string)$args['options']) : $args['options'];
				$current_value = $this->isset_or($args['current_value'], NULL, 'string');

				$notes                = trim((string)$args['notes']);
				$post_id              = $this->isset_or($args['post_id'], NULL, 'integer');
				$nested_checkbox_args = (array)$args['nested_checkbox_args'];
				$other_attrs          = trim((string)$args['other_attrs']);

				$input_fallback_args = array_merge($args, (array)$args['input_fallback_args']);
				unset($input_fallback_args['input_fallback_args']); // Unset self reference.

				if($options === '%%users%%') $options = $this->plugin->utils_markup->user_select_options($current_value);
				else if($options === '%%posts%%') $options = $this->plugin->utils_markup->post_select_options($current_value, array('for_comments_only' => TRUE));
				else if($options === '%%comments%%') $options = $this->plugin->utils_markup->comment_select_options($post_id, $current_value);
				else if($options === '%%deliver%%') $options = $this->plugin->utils_markup->deliver_select_options($current_value);
				else if($options === '%%status%%') $options = $this->plugin->utils_markup->status_select_options($current_value);
				else if(is_array($options)) $options = $this->plugin->utils_markup->select_options($options, $current_value);

				if(!($options = trim((string)$options))) // No options available?
					return $this->input_row($input_fallback_args);

				$row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' '.$this->class_prefix.$slug).'">';

				$row .= ' <th scope="row">';
				$row .= '    <label for="'.esc_attr($id).'">'.
				        '       '.$label.($required ? // Change the short description based on this boolean.
						'           <span class="description">'.__('(required) *', $this->plugin->text_domain).'</span>' : '').
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

				        ($nested_checkbox_args // Include a nested checkbox?
					        ? '<p class="checkbox">'.$this->nested_checkbox($nested_checkbox_args).'</p>' : '');

				$row .= ' </td>';

				$row .= '</tr>';

				return $row; // HTML markup.
			}

			/**
			 * Constructs an HR field row.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string HTML markup for this field row.
			 */
			public function horizontal_line_row()
			{
				$field = '<tr class="'.esc_attr($this->class_prefix.'hr-row').'">';

				$field .= ' <td colspan="2">';
				$field .= '    <hr />';
				$field .= ' </td>';

				$field .= '</tr>';

				return $field; // HTML markup.
			}

			/**
			 * Nested checkbox.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Specs and behavorial args.
			 *
			 * @return string HTML markup for this checkbox.
			 */
			public function nested_checkbox(array $args = array())
			{
				$default_args = array(
					'label'         => '',

					'name'          => '',
					'root_name'     => FALSE,

					'current_value' => NULL,

					'other_attrs'   => '',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$label = trim((string)$args['label']);

				$name      = trim((string)$args['name']);
				$root_name = (boolean)$args['root_name'];

				$slug = str_replace('_', '-', $name);
				$slug = $root_name ? 'root-'.$slug : $slug;

				$id   = __NAMESPACE__.$this->ns_id_suffix.'-'.$slug;
				$name = $root_name ? $name : __NAMESPACE__.$this->ns_name_suffix.'['.$name.']';

				$current_value = $this->isset_or($args['current_value'], NULL, 'string');
				$checked       = $current_value ? ' checked="checked"' : '';

				$other_attrs = trim((string)$args['other_attrs']);

				return '<label for="'.esc_attr($id).'" style="margin-left:10px;">'.

				       ' <i class="fa fa-level-up fa-rotate-90"></i>'.
				       ' &nbsp;'. // Double-space after icon.

				       ' <input type="checkbox"'.

				       ' id="'.esc_attr($id).'"'.
				       ' name="'.esc_attr($name).'"'.

				       ' value="1"'.$checked.

				       ' '.$other_attrs.' />'.

				       ' '.$label.

				       '</label>';
			}

			/**
			 * Constructs a hidden input.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Specs and behavorial args.
			 *
			 * @return string HTML markup for this hidden input.
			 */
			public function hidden_input(array $args = array())
			{
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

				$id   = __NAMESPACE__.$this->ns_id_suffix.'-'.$slug;
				$name = $root_name ? $name : __NAMESPACE__.$this->ns_name_suffix.'['.$name.']';

				$current_value = $this->isset_or($args['current_value'], NULL, 'string');

				$other_attrs = trim((string)$args['other_attrs']);

				$field = '<input type="hidden"'. // Hidden input var.

				         ' id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

				         ' value="'.esc_attr(trim((string)$current_value)).'"'.

				         ' '.$other_attrs.' />';

				return $field;
			}
		}
	}
}