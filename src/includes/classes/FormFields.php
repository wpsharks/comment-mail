<?php
/**
 * Form Fields.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Form Fields.
 *
 * @since 141111 First documented version.
 */
class FormFields extends AbsBase
{
    /**
     * @type string Namespaced ID suffix.
     *
     * @since 141111 First documented version.
     */
    protected $ns_id_suffix;

    /**
     * @type string Namespaced name suffix.
     *
     * @since 141111 First documented version.
     */
    protected $ns_name_suffix;

    /**
     * @type string Class prefix.
     *
     * @since 141111 First documented version.
     */
    protected $class_prefix;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Configuration args.
     */
    public function __construct(array $args = [])
    {
        parent::__construct();

        $default_args = [
            'ns_id_suffix'   => '',
            'ns_name_suffix' => '',
            'class_prefix'   => '',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $this->ns_id_suffix   = trim((string) $args['ns_id_suffix']);
        $this->ns_name_suffix = trim((string) $args['ns_name_suffix']);
        $this->class_prefix   = trim((string) $args['class_prefix']);
    }

    /**
     * Constructs an input field row.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Specs and behavorial args.
     *
     * @return string HTML markup for this input field row.
     */
    public function inputRow(array $args = [])
    {
        $default_args = [
            'type'           => 'text',
            'label'          => '',
            'checkbox_label' => '',
            'radio_label'    => '',
            'placeholder'    => '',

            'name'      => '',
            'root_name' => false,

            'required'                 => false,
            'maxlength'                => 0,
            'current_value'            => null,
            'current_value_empty_on_0' => false,

            'notes_before' => '',
            'notes_after'  => '',

            'post_id'              => null,
            'nested_checkbox_args' => [],
            'field_class'          => '',
            'other_attrs'          => '',
            'exclude_th'           => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $type           = trim((string) $args['type']);
        $label          = trim((string) $args['label']);
        $checkbox_label = trim((string) $args['checkbox_label']);
        $radio_label    = trim((string) $args['radio_label']);
        $placeholder    = trim((string) $args['placeholder']);

        $name      = trim((string) $args['name']);
        $root_name = (boolean) $args['root_name'];

        $slug = trim(preg_replace('/[^a-z0-9]/i', '-', $name), '-');
        $slug = $root_name ? 'root-'.$slug : $slug;

        $id   = SLUG_TD.$this->ns_id_suffix.'-'.$slug;
        $name = $root_name ? $name : GLOBAL_NS.$this->ns_name_suffix.'['.$name.']';

        $required                 = (boolean) $args['required'];
        $maxlength                = (integer) $args['maxlength'];
        $current_value            = $this->issetOr($args['current_value'], null, 'string');
        $current_value_empty_on_0 = (boolean) $args['current_value_empty_on_0'];

        if ($current_value_empty_on_0 && in_array($current_value, [0, '0'], true)) {
            $current_value = ''; // Empty value.
        }
        $notes_before = trim((string) $args['notes_before']);
        $notes_after  = trim((string) $args['notes_after']);

        $post_id              = $this->issetOr($args['post_id'], null, 'integer');
        $nested_checkbox_args = (array) $args['nested_checkbox_args'];
        $field_class          = trim((string) $args['field_class']);
        $other_attrs          = trim((string) $args['other_attrs']);
        $exclude_th           = (boolean) $args['exclude_th'];

        $row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' '.$this->class_prefix.$slug).'">';

        if (!$exclude_th) {
            // Only if not excluding the table header.

            $row .= '<th scope="row">';
            $row .= '   <label for="'.esc_attr($id).'">'.
                    '      '.$label.($required ? // Change the short description based on this boolean.
                    '        <span class="description">'.__('(required) *', 'comment-mail').'</span>' : '').
                    '   </label>';
            $row .= '</th>';
        }
        $row .= ' <td>';

        if ($type === 'hidden') {
            $row .= $this->hiddenInput($args);
        } else {
            $row .= ($notes_before ? // Display notes before?
                    '        <div class="notes notes-before">'.$notes_before.'</div>' : '').

                    '    <input type="'.esc_attr($type).'"'.

                    '     class="'.esc_attr('form-control '.$field_class).'"'.

                    '     id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

                    '     aria-required="'.esc_attr($required ? 'true' : 'false').'"'.
                    '     '.($required ? ' required="required"' : '').// JS validation.

                    '     '.($maxlength ? ' maxlength="'.esc_attr($maxlength).'"' : '').

                    '     data-placeholder="'.esc_attr($placeholder).'"'.
                    '     placeholder="'.esc_attr($placeholder).'"'.

                    '     value="'.esc_attr(trim((string) $current_value)).'"'.

                    '     autocomplete="new-password"'.

                    '     '.$other_attrs.' />'.

                    ($type === 'checkbox' && $checkbox_label
                        ? '<label for="'.esc_attr($id).'">'.$checkbox_label.'</label>' : '').

                    ($type === 'radio' && $radio_label
                        ? '<label for="'.esc_attr($id).'">'.$radio_label.'</label>' : '').

                    ($notes_after ? // Display notes after?
                        '<div class="notes notes-after">'.$notes_after.'</div>' : '').

                    ($nested_checkbox_args // Include a nested checkbox?
                        ? '<p class="checkbox">'.$this->nestedCheckbox($nested_checkbox_args).'</p>' : '');
        }
        $row .= ' </td>';

        $row .= '</tr>';

        return $row; // HTML markup.
    }

    /**
     * Constructs a textarea field row.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Specs and behavorial args.
     *
     * @return string HTML markup for this textarea field row.
     */
    public function textareaRow(array $args = [])
    {
        $default_args = [
            'label'       => '',
            'placeholder' => '',

            'name'      => '',
            'root_name' => false,

            'rows'                     => 3,
            'required'                 => false,
            'maxlength'                => 0,
            'current_value'            => null,
            'current_value_empty_on_0' => false,

            'cm_mode'    => '',
            'cm_height'  => 500,
            'cm_details' => '',

            'notes_before' => '',
            'notes_after'  => '',

            'post_id'              => null,
            'nested_checkbox_args' => [],
            'field_class'          => '',
            'other_attrs'          => '',
            'exclude_th'           => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $label       = trim((string) $args['label']);
        $placeholder = trim((string) $args['placeholder']);

        $name      = trim((string) $args['name']);
        $root_name = (boolean) $args['root_name'];

        $slug = trim(preg_replace('/[^a-z0-9]/i', '-', $name), '-');
        $slug = $root_name ? 'root-'.$slug : $slug;

        $id   = SLUG_TD.$this->ns_id_suffix.'-'.$slug;
        $name = $root_name ? $name : GLOBAL_NS.$this->ns_name_suffix.'['.$name.']';

        $rows                     = (integer) $args['rows'];
        $required                 = (boolean) $args['required'];
        $maxlength                = (integer) $args['maxlength'];
        $current_value            = $this->issetOr($args['current_value'], null, 'string');
        $current_value_empty_on_0 = (boolean) $args['current_value_empty_on_0'];

        if ($current_value_empty_on_0 && in_array($current_value, [0, '0'], true)) {
            $current_value = ''; // Empty value.
        }
        $cm_mode    = trim((string) $args['cm_mode']);
        $cm_height  = (integer) $args['cm_height'];
        $cm_details = trim((string) $args['cm_details']);

        $notes_before = trim((string) $args['notes_before']);
        $notes_after  = trim((string) $args['notes_after']);

        $post_id              = $this->issetOr($args['post_id'], null, 'integer');
        $nested_checkbox_args = (array) $args['nested_checkbox_args'];
        $field_class          = trim((string) $args['field_class']);
        $other_attrs          = trim((string) $args['other_attrs']);
        $exclude_th           = (boolean) $args['exclude_th'];

        $row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' '.$this->class_prefix.$slug).'">';

        if (!$exclude_th) { // Only if not excluding the table header.
            $row .= '<th scope="row">';
            $row .= '   <label for="'.esc_attr($id).'">'.
                    '      '.$label.($required ? // Change the short description based on this boolean.
                    '        <span class="description">'.__('(required) *', 'comment-mail').'</span>' : '').
                    ($cm_mode ? '<span class="description" style="margin-left:2em;">'.
                                '   <small>'.__('(<code>F11</code> toggles fullscreen editing)', 'comment-mail').'</small>'.
                                ($cm_details ? '<small style="margin-left:2em;">'.$cm_details.'</small>' : '').
                                '</span>' : '').
                    '   </label>';
            $row .= '</th>';
        }
        $row .= ' <td>';

        $row .= ($notes_before ? // Display notes before?
                '     <div class="notes notes-before">'.$notes_before.'</div>' : '').

                ($cm_mode ? // For a CodeMirror?
                    '<div data-cm-mode="'.esc_attr($cm_mode).'" data-cm-height="'.esc_attr($cm_height).'">' : '').

                '    <textarea'.// Possibly wrapped by a div.

                '     class="'.esc_attr('form-control '.$field_class).'"'.

                '     id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

                '     rows="'.esc_attr($rows).'"'.// Height of area.

                '     aria-required="'.esc_attr($required ? 'true' : 'false').'"'.
                '     '.($required ? ' required="required"' : '').// JS validation.

                '     '.($maxlength ? ' maxlength="'.esc_attr($maxlength).'"' : '').

                '     data-placeholder="'.esc_attr($placeholder).'"'.
                '     placeholder="'.esc_attr($placeholder).'"'.

                '     autocomplete="new-password"'.

                '     '.$other_attrs.'>'.esc_textarea(trim((string) $current_value)).'</textarea>'.

                ($cm_mode ? // For a CodeMirror?
                    '</div>' : '').// Close div wrapper in this case.

                ($notes_after ? // Display notes after?
                    '<div class="notes notes-after">'.$notes_after.'</div>' : '').

                ($nested_checkbox_args // Include a nested checkbox?
                    ? '<p class="checkbox">'.$this->nestedCheckbox($nested_checkbox_args).'</p>' : '');

        $row .= ' </td>';

        $row .= '</tr>';

        return $row; // HTML markup.
    }

    /**
     * Constructs a select field row.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Specs and behavorial args.
     *
     * @return string HTML markup for this select field row.
     *                If no options (or too many options; this returns an input field instead.
     */
    public function selectRow(array $args = [])
    {
        $default_args = [
            'type'        => 'text',
            'label'       => '',
            'placeholder' => '',

            'name'      => '',
            'root_name' => false,

            'required'                 => false,
            'maxlength'                => 0,
            'options'                  => '',
            'current_value'            => null,
            'current_value_empty_on_0' => false,

            'notes_before' => '',
            'notes_after'  => '',

            'post_id'              => null,
            'nested_checkbox_args' => [],
            'field_class'          => '',
            'other_attrs'          => '',
            'exclude_th'           => false,

            'allow_empty'         => true,
            'allow_arbitrary'     => true,
            'input_fallback_args' => [],
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $type        = trim((string) $args['type']);
        $label       = trim((string) $args['label']);
        $placeholder = trim((string) $args['placeholder']);

        $name      = trim((string) $args['name']);
        $root_name = (boolean) $args['root_name'];

        $slug = trim(preg_replace('/[^a-z0-9]/i', '-', $name), '-');
        $slug = $root_name ? 'root-'.$slug : $slug;

        $id   = SLUG_TD.$this->ns_id_suffix.'-'.$slug;
        $name = $root_name ? $name : GLOBAL_NS.$this->ns_name_suffix.'['.$name.']';

        $required                 = (boolean) $args['required'];
        $maxlength                = (integer) $args['maxlength'];
        $options                  = !is_array($args['options']) ? trim((string) $args['options']) : $args['options'];
        $current_value            = $this->issetOr($args['current_value'], null, 'string');
        $current_value_empty_on_0 = (boolean) $args['current_value_empty_on_0'];

        if ($current_value_empty_on_0 && in_array($current_value, [0, '0'], true)) {
            $current_value = ''; // Empty value.
        }
        $notes_before = trim((string) $args['notes_before']);
        $notes_after  = trim((string) $args['notes_after']);

        $post_id              = $this->issetOr($args['post_id'], null, 'integer');
        $nested_checkbox_args = (array) $args['nested_checkbox_args'];
        $field_class          = trim((string) $args['field_class']);
        $other_attrs          = trim((string) $args['other_attrs']);
        $exclude_th           = (boolean) $args['exclude_th'];

        $allow_empty         = (boolean) $args['allow_empty'];
        $allow_arbitrary     = (boolean) $args['allow_arbitrary'];
        $select_options_args = compact('allow_empty', 'allow_arbitrary');

        $input_fallback_args = array_merge($args, (array) $args['input_fallback_args']);
        unset($input_fallback_args['input_fallback_args']); // Unset self reference.

        if ($options === '%%users%%') {
            $options = $this->plugin->utils_markup->userSelectOptions($current_value, $select_options_args);
        } elseif ($options === '%%posts%%') {
            $options = $this->plugin->utils_markup->postSelectOptions($current_value, array_merge($select_options_args, ['for_comments_only' => true]));
        } elseif ($options === '%%comments%%') {
            $options = $this->plugin->utils_markup->commentSelectOptions($post_id, $current_value, $select_options_args);
            if ($options) {
                $placeholder = '';
            }
        } elseif ($options === '%%deliver%%') {
            $options = $this->plugin->utils_markup->deliverSelectOptions($current_value, $select_options_args);
        } elseif ($options === '%%status%%') {
            $options = $this->plugin->utils_markup->statusSelectOptions($current_value, $select_options_args);
        } elseif (is_array($options)) {
            $options = $this->plugin->utils_markup->selectOptions($options, $current_value, $select_options_args);
        }
        if (!($options = trim((string) $options)) && $allow_empty && $allow_arbitrary) {
            return $this->inputRow($input_fallback_args);
        }
        $row = '<tr class="'.esc_attr('form-field'.($required ? ' form-required' : '').' '.$this->class_prefix.$slug).'">';

        if (!$exclude_th) { // Only if not excluding the table header.
            $row .= '<th scope="row">';
            $row .= '   <label for="'.esc_attr($id).'">'.
                    '      '.$label.($required ? // Change the short description based on this boolean.
                    '        <span class="description">'.__('(required) *', 'comment-mail').'</span>' : '').
                    '   </label>';
            $row .= '</th>';
        }
        $row .= ' <td>';

        $row .= ($notes_before ? // Display notes before?
                '     <div class="notes notes-before">'.$notes_before.'</div>' : '').

                '    <select'.// Select menu options.

                '     class="'.esc_attr('form-control '.$field_class).'"'.

                '     id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

                '     aria-required="'.esc_attr($required ? 'true' : 'false').'"'.
                '     '.($required ? ' required="required"' : '').// JS validation.

                '     '.($maxlength ? ' maxlength="'.esc_attr($maxlength).'"' : '').

                '     data-placeholder="'.esc_attr($placeholder).'"'.
                '     placeholder="'.esc_attr($placeholder).'"'.

                '     autocomplete="new-password"'.

                '     '.$other_attrs.'>'.

                '       '.$options.

                '    </select>'.

                ($notes_after ? // Display notes after?
                    '<div class="notes notes-after">'.$notes_after.'</div>' : '').

                ($nested_checkbox_args // Include a nested checkbox?
                    ? '<p class="checkbox">'.$this->nestedCheckbox($nested_checkbox_args).'</p>' : '');

        $row .= ' </td>';

        $row .= '</tr>';

        return $row; // HTML markup.
    }

    /**
     * Constructs an HR field row.
     *
     * @since 141111 First documented version.
     *
     * @return string HTML markup for this field row.
     */
    public function horizontalLineRow()
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
     * @since 141111 First documented version.
     *
     * @param array $args Specs and behavorial args.
     *
     * @return string HTML markup for this checkbox.
     */
    public function nestedCheckbox(array $args = [])
    {
        $default_args = [
            'label' => '',

            'name'      => '',
            'root_name' => false,

            'current_value' => null,

            'field_class' => '',
            'other_attrs' => '',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $label = trim((string) $args['label']);

        $name      = trim((string) $args['name']);
        $root_name = (boolean) $args['root_name'];

        $slug = trim(preg_replace('/[^a-z0-9]/i', '-', $name), '-');
        $slug = $root_name ? 'root-'.$slug : $slug;

        $id   = SLUG_TD.$this->ns_id_suffix.'-'.$slug;
        $name = $root_name ? $name : GLOBAL_NS.$this->ns_name_suffix.'['.$name.']';

        $current_value = $this->issetOr($args['current_value'], null, 'string');
        $checked       = $current_value ? ' checked="checked"' : '';

        $field_class = trim((string) $args['field_class']);
        $other_attrs = trim((string) $args['other_attrs']);

        return '<label for="'.esc_attr($id).'" style="margin-left:10px;">'.

               ' <i class="fa fa-level-up fa-rotate-90"></i>'.
               ' &nbsp;'.// Double-space after icon.

               ' <input type="checkbox"'.

               ' class="'.esc_attr($field_class).'"'.

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
     * @since 141111 First documented version.
     *
     * @param array $args Specs and behavorial args.
     *
     * @return string HTML markup for this hidden input.
     */
    public function hiddenInput(array $args = [])
    {
        $default_args = [
            'name'      => '',
            'root_name' => false,

            'current_value' => null,

            'field_class' => '',
            'other_attrs' => '',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $name      = trim((string) $args['name']);
        $root_name = (boolean) $args['root_name'];

        $slug = trim(preg_replace('/[^a-z0-9]/i', '-', $name), '-');
        $slug = $root_name ? 'root-'.$slug : $slug;

        $id   = SLUG_TD.$this->ns_id_suffix.'-'.$slug;
        $name = $root_name ? $name : GLOBAL_NS.$this->ns_name_suffix.'['.$name.']';

        $current_value = $this->issetOr($args['current_value'], null, 'string');

        $field_class = trim((string) $args['field_class']);
        $other_attrs = trim((string) $args['other_attrs']);

        $field = '<input type="hidden"'.// Hidden input var.

                 ' class="'.esc_attr($field_class).'"'.

                 ' id="'.esc_attr($id).'" name="'.esc_attr($name).'"'.

                 ' value="'.esc_attr(trim((string) $current_value)).'"'.

                 ' '.$other_attrs.' />';

        return $field;
    }
}
