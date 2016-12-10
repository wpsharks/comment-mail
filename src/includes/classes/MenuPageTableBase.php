<?php
/**
 * Menu Page Table Base.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Menu Page Table Base.
 *
 * @since 141111 First documented version.
 */
abstract class MenuPageTableBase extends \WP_List_Table
{
    /*
     * Protected properties.
     */

    /**
     * @var Plugin Plugin reference.
     *
     * @since 141111 First documented version.
     */
    protected $plugin;

    /**
     * @var string Singular item name.
     *
     * @since 141111 First documented version.
     */
    protected $singular_name;

    /**
     * @var string Singular item label.
     *
     * @since 141111 First documented version.
     */
    protected $singular_label;

    /**
     * @var string Plural item name.
     *
     * @since 141111 First documented version.
     */
    protected $plural_name;

    /**
     * @var string Plural item label.
     *
     * @since 141111 First documented version.
     */
    protected $plural_label;

    /**
     * @var string Regex for sub IDs.
     *
     * @since 141111 First documented version.
     */
    protected $sub_ids_regex;

    /**
     * @var string Regex for sub emails.
     *
     * @since 150527 Bug fix; missing property.
     */
    protected $sub_emails_regex;

    /**
     * @var string Regex for user IDs.
     *
     * @since 141111 First documented version.
     */
    protected $user_ids_regex;

    /**
     * @var string Regex for post IDs.
     *
     * @since 141111 First documented version.
     */
    protected $post_ids_regex;

    /**
     * @var string Regex for comment IDs.
     *
     * @since 141111 First documented version.
     */
    protected $comment_ids_regex;

    /**
     * @var string Regex for `AND`.
     *
     * @since 141111 First documented version.
     */
    protected $and_regex;

    /**
     * @var string Regex for statuses.
     *
     * @since 141111 First documented version.
     */
    protected $statuses_regex;

    /**
     * @var string Regex for events.
     *
     * @since 141111 First documented version.
     */
    protected $events_regex;

    /**
     * @var array Merged result sets.
     *
     * @since 141111 First documented version.
     */
    protected $merged_result_sets;

    /*
     * Class constructor.
     */

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Constructor arguments.
     */
    public function __construct(array $args = [])
    {
        $this->plugin = plugin();

        $this->singular_name = !empty($args['singular_name'])
            ? (string) $args['singular_name'] : 'item';

        $this->singular_label = !empty($args['singular_label'])
            ? (string) $args['singular_label'] : 'item';

        $this->plural_name = !empty($args['plural_name'])
            ? (string) $args['plural_name'] : 'items';

        $this->plural_label = !empty($args['plural_label'])
            ? (string) $args['plural_label'] : 'items';

        $args = [
            'singular' => $this->singular_name, 'plural' => $this->plural_name,
            'screen'   => !empty($args['screen']) ? (string) $args['screen']
                : $this->plugin->menu_page_hooks[GLOBAL_NS.'_'.$this->plural_name],
        ];
        parent::__construct($args); // Parent constructor.

        $this->items = []; // Initialize.

        // Filters; i.e. `:`= filter; `::` = navigable filter.
        $this->sub_ids_regex     = '/\bsub_ids?\:(?P<sub_ids>[^\s]+)/i';
        $this->sub_emails_regex  = '/\bsub_emails?\:(?P<sub_emails>[^\s]+)/i';
        $this->user_ids_regex    = '/\buser_ids?\:(?P<user_ids>[^\s]+)/i';
        $this->post_ids_regex    = '/\bpost_ids?\:(?P<post_ids>[^\s]+)/i';
        $this->comment_ids_regex = '/\bcomment_ids?\:(?P<comment_ids>[^\s]+)/i';
        $this->statuses_regex    = '/\bstatus(?:es)?\:\:(?P<statuses>[^\s]+)/i';
        $this->events_regex      = '/\bevents?\:\:(?P<events>[^\s]+)/i';

        $this->and_regex = '/(?:^|\s+)\+(?:\s+|$)/i';
        // Must NOT conflict with SQL: <http://dev.mysql.com/doc/refman/5.5/en/fulltext-boolean.html>

        $this->merged_result_sets = []; // Initialize.

        $this->maybeProcessBulkAction();
        $this->prepare_items();
        $this->display();
    }

    /*
     * Public column-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all table columns.
     */
    public function get_columns()
    { // @codingStandardsIgnoreEnd
        return static::getTheColumns();
    }

    /**
     * Table columns.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all table columns.
     *
     * @extenders Extenders should normally override this.
     */
    public static function getTheColumns()
    {
        return [];
    }

    /**
     * Hidden table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all hidden table columns.
     */
    public function getHiddenColumns()
    {
        return static::getTheHiddenColumns();
    }

    /**
     * Hidden table columns.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all hidden table columns.
     *
     * @extenders Extenders should normally override this.
     */
    public static function getTheHiddenColumns()
    {
        return [];
    }

    /**
     * Searchable fulltext table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all fulltext searchables.
     */
    public function getFtSearchableColumns()
    {
        return static::getTheFtSearchableColumns();
    }

    /**
     * Searchable fulltext table columns.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all fulltext searchables.
     *
     * @extenders Extenders should normally override this.
     */
    public static function getTheFtSearchableColumns()
    {
        return [];
    }

    /**
     * Searchable table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all searchables.
     */
    public function getSearchableColumns()
    {
        return static::getTheSearchableColumns();
    }

    /**
     * Searchable table columns.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all searchables.
     *
     * @extenders Extenders should normally override this.
     */
    public static function getTheSearchableColumns()
    {
        return [];
    }

    /**
     * Unsortable table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all unsortable table columns.
     */
    public function getUnsortableColumns()
    {
        $unsortable_columns   = static::getTheUnsortableColumns();
        $unsortable_columns[] = 'cb'; // Always unsortable.

        return array_unique($unsortable_columns);
    }

    /**
     * Unsortable table columns.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all unsortable table columns.
     *
     * @extenders Extenders should normally override this.
     */
    public static function getTheUnsortableColumns()
    {
        return [];
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Sortable table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all sortable table columns.
     */
    public function get_sortable_columns()
    { // @codingStandardsIgnoreEnd
        return static::getTheSortableColumns();
    }

    /**
     * Sortable table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all sortable table columns.
     */
    public static function getTheSortableColumns()
    {
        $sortable_columns     = []; // Initialize.
        $unsortable_columns   = static::getTheUnsortableColumns();
        $unsortable_columns[] = 'cb'; // Always unsortable.
        $unsortable_columns   = array_unique($unsortable_columns);

        foreach (array_keys(static::getTheColumns()) as $_column) {
            if (!in_array($_column, $unsortable_columns, true)) {
                $sortable_columns[$_column] = [$_column, false];
            }
        }
        unset($_column); // Housekeeping.

        return $sortable_columns;
    }

    /*
     * Public filter-related methods.
     */

    /**
     * Navigable table filters.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all navigable table filters.
     */
    public function getNavigableFilters()
    {
        return static::getTheNavigableFilters();
    }

    /**
     * Navigable table filters.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all navigable table filters.
     *
     * @extenders Extenders should normally override this.
     */
    public static function getTheNavigableFilters()
    {
        return [];
    }

    /*
     * Protected column-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_cb($item)
    { // @codingStandardsIgnoreEnd
        return '<input type="checkbox" name="'.esc_attr($this->plural_name).'[]" value="'.esc_attr($item->ID).'" />';
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `key`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_key(\stdClass $item, $prefix = '', $key = 'key')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        return '<code>'.esc_html((string) $item->{$key}).'</code>';
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_key_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_key($item, '', 'key_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to `sub_`.
     * @param string    $key    A particular key to return. Defaults to `sub_id`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_sub_id(\stdClass $item, $prefix = 'sub_', $key = 'sub_id')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        $id_only = '<i class="'.esc_attr('si si-'.SLUG_TD.'-one').'"></i>'.
                   ' <span style="font-weight:bold;">ID #'.esc_html($item->{$key}).'</span>';

        if (empty($this->merged_result_sets['subs'][$item->{$key}])) {
            return $id_only; // All we can do.
        }
        $name_email_args = [
            'separator'   => '<br />',
            'anchor_to'   => 'search',
            'name_style'  => 'font-weight:bold;',
            'email_style' => 'font-weight:normal;',
        ];
        $name     = $item->{$prefix.'fname'}.' '.$item->{$prefix.'lname'};
        $sub_info = '<i class="'.esc_attr('si si-'.SLUG_TD.'-one').'"></i>'.
                           ' '.$this->plugin->utils_markup->nameEmail($name, $item->{$prefix.'email'}, $name_email_args);

        $edit_url = $this->plugin->utils_url->editSubShort($item->{$key});

        $row_actions = [
            'edit' => '<a href="'.esc_attr($edit_url).'">'.__('Edit Subscr.', 'comment-mail').'</a>',
        ];
        return $sub_info.$this->row_actions($row_actions);
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_sub_id_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_sub_id($item, 'sub_before_', 'sub_id_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_oby_sub_id(\stdClass $item)
    {
        return $this->column_sub_id($item, 'oby_sub_', 'oby_sub_id');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_oby_sub_id_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_sub_id($item, 'oby_sub_before_', 'oby_sub_id_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to `user_`.
     * @param string    $key    A particular key to return. Defaults to `user_id`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_user_id(\stdClass $item, $prefix = 'user_', $key = 'user_id')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        $id_only = '<i class="fa fa-user"></i>'.// If it's all we can do.
                   ' <span style="font-weight:bold;">ID #'.esc_html($item->{$key}).'</span>';

        if (empty($this->merged_result_sets['users'][$item->{$key}])) {
            return $id_only; // All we can do.
        }
        $name_email_args = [
            'separator'   => '<br />',
            'anchor_to'   => 'search',
            'name_style'  => 'font-weight:normal;',
            'email_style' => 'font-weight:normal;',
        ];
        $user_info = '<i class="fa fa-user"></i>'.// e.g. ♙ "Name" <email>
                           ' '.$this->plugin->utils_markup->nameEmail($item->{$prefix.'display_name'}, $item->{$prefix.'email'}, $name_email_args);

        $edit_url = $this->plugin->utils_url->editUserShort($item->{$key});

        $row_actions = [
            'edit' => '<a href="'.esc_attr($edit_url).'">'.__('Edit User', 'comment-mail').'</a>',
        ];
        return $user_info.$this->row_actions($row_actions);
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_user_id_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_user_id($item, 'user_before_', 'user_id_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to `post_`.
     * @param string    $key    A particular key to return. Defaults to `post_id`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_post_id(\stdClass $item, $prefix = 'post_', $key = 'post_id')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        $id_only = '<i class="fa fa-thumb-tack"></i>'.// If it's all we can do.
                   ' <span style="font-weight:bold;">ID #'.esc_html($item->{$key}).'</span>';

        if (empty($this->merged_result_sets['posts'][$item->{$key}])) {
            return $id_only; // All we can do.
        }
        if (!$item->{$prefix.'type'} || !$item->{$prefix.'title'}) {
            return $id_only; // All we can do.
        }
        if (!($post_type = get_post_type_object($item->{$prefix.'type'}))) {
            return $id_only; // All we can do.
        }
        $post_type_label        = $post_type->labels->singular_name;
        $post_title_clip        = $this->plugin->utils_string->midClip($item->{$prefix.'title'});
        $post_date              = $this->plugin->utils_date->i18n('M j, Y', strtotime($item->{$prefix.'date_gmt'}));
        $post_date_ago          = $this->plugin->utils_date->approxTimeDifference(strtotime($item->{$prefix.'date_gmt'}));
        $post_comments_status   = $this->plugin->utils_i18n->statusLabel($this->plugin->utils_db->postCommentStatusI18n($item->{$prefix.'comment_status'}), 'ucwords');
        $post_edit_comments_url = $this->plugin->utils_url->postEditCommentsShort($item->{$key});
        $post_total_subs        = $this->plugin->utils_sub->queryTotal($item->{$key});
        $post_total_comments    = (int) $item->{$prefix.'comment_count'};

        $post_info = $this->plugin->utils_markup->subsCount($item->{$key}, $post_total_subs).
                     $this->plugin->utils_markup->commentCount($item->{$key}, $post_total_comments).
                     '<i class="fa fa-thumb-tack"></i>'.// Start w/ a thumb tack icon; works w/ any post type.
                     ' '.'<span title="'.esc_attr($post_date).'">'.esc_html($post_title_clip).'</span>';

        $post_view_url    = $this->plugin->utils_url->postShort($item->{$key});
        $post_edit_url    = $this->plugin->utils_url->postEditShort($item->{$key});
        $post_row_actions = [
            'edit' => '<a href="'.esc_attr($post_edit_url).'">'.sprintf(__('Edit %1$s', 'comment-mail'), esc_html($post_type_label)).'</a>',
            'view' => '<a href="'.esc_attr($post_view_url).'">'.sprintf(__('View', 'comment-mail'), esc_html($post_type_label)).'</a>',
        ];
        return $post_info.$this->row_actions($post_row_actions);
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_post_id_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_post_id($item, 'post_before_', 'post_id_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to `comment_`.
     * @param string    $key    A particular key to return. Defaults to `comment_id`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_comment_id(\stdClass $item, $prefix = 'comment_', $key = 'comment_id')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key} && $key === 'comment_id') {
            return __('all comments', 'comment-mail');
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        $id_only = '<i class="fa fa-comment"></i>'.// If it's all we can do.
                   ' <span style="font-weight:bold;">ID #'.esc_html($item->{$key}).'</span>';

        if (empty($this->merged_result_sets['comments'][$item->{$key}])) {
            return $id_only; // All we can do.
        }
        $name_email_args = [
            'anchor_to'   => 'search',
            'name_style'  => 'font-weight:bold;',
            'email_style' => 'font-weight:normal;',
        ];
        $comment_date_time = $this->plugin->utils_date->i18n('M j, Y g:i a', strtotime($item->{$prefix.'date_gmt'}));
        $comment_time_ago  = $this->plugin->utils_date->approxTimeDifference(strtotime($item->{$prefix.'date_gmt'}));
        $comment_status    = $this->plugin->utils_i18n->statusLabel($this->plugin->utils_db->commentStatusI18n($item->{$prefix.'approved'}), 'ucwords');

        $comment_info = '<i class="fa fa-comment"></i>'.// Start w/ a comment bubble icon.
                        ' '.$this->plugin->utils_markup->nameEmail($item->{$prefix.'author'}, $item->{$prefix.'author_email'}, $name_email_args);

        $comment_view_url    = $this->plugin->utils_url->commentShort($item->{$key});
        $comment_edit_url    = $this->plugin->utils_url->commentEditShort($item->{$key});
        $comment_row_actions = [
            'edit' => '<a href="'.esc_attr($comment_edit_url).'">'.__('Edit Comment', 'comment-mail').'</a>',
            'view' => '<a href="'.esc_attr($comment_view_url).'">'.__('View', 'comment-mail').'</a>',
        ];
        return $comment_info.$this->row_actions($comment_row_actions);
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_comment_id_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_comment_id($item, 'comment_before_', 'comment_id_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_comment_parent_id(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_comment_id($item, 'comment_parent_', 'comment_parent_id');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_comment_parent_id_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_comment_id($item, 'comment_parent_before_', 'comment_parent_id_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `email`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_email(\stdClass $item, $prefix = '', $key = 'email')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        $name_email_args = [
            'anchor_to'   => 'search',
            'email_style' => 'font-weight:normal;',
        ];
        return $this->plugin->utils_markup->nameEmail('', $item->{$key}, $name_email_args);
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_email_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_email($item, '', 'email_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `status`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_status(\stdClass $item, $prefix = '', $key = 'status')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        return esc_html($this->plugin->utils_i18n->statusLabel($item->{$key}));
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_status_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_status($item, '', 'status_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `deliver`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_deliver(\stdClass $item, $prefix = '', $key = 'deliver')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return '—'; // Not possible.
        }
        return esc_html($this->plugin->utils_i18n->deliverLabel($item->{$key}));
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_deliver_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_deliver($item, '', 'deliver_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `user_initiated`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_user_initiated(\stdClass $item, $prefix = '', $key = 'user_initiated')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        return esc_html($item->{$key} ? 'yes' : 'no');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_user_initiated_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_user_initiated($item, '', 'user_initiated_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `hold_until_time`.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_hold_until_time(\stdClass $item, $prefix = '', $key = 'hold_until_time')
    { // @codingStandardsIgnoreEnd
        if (!isset($item->{$key})) {
            return '—'; // Not possible.
        }
        if (!$item->{$key}) {
            return __('n/a; awaiting processing', 'comment-mail');
        }
        return esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $item->{$key})).'<br />'.
               '<span style="font-style:italic;">('.esc_html($this->plugin->utils_date->approxTimeDifference(time(), $item->{$key}, '')).')</span>'.
               ' '.__('~ part of a digest', 'comment-mail');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_hold_until_time_before(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        return $this->column_hold_until_time($item, '', 'hold_until_time_before');
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Table column handler.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $item     Item object; i.e. a row from the DB.
     * @param string    $property Column we need to build markup for.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_default($item, $property)
    { // @codingStandardsIgnoreEnd
        if (!($property = trim((string) $property))) {
            return '—'; // Not applicable.
        }
        $value = isset($item->{$property}) ? $item->{$property} : '';

        if (($property === 'time' || substr($property, -5) === '_time') && is_integer($value)) {
            $value = $value <= 0
                ? '—' // Use a default value of `—` in this case.
                : esc_html($this->plugin->utils_date->i18n('M j, Y g:i a', $value)).'<br />'.
                  '<span style="font-style:italic;">('.esc_html($this->plugin->utils_date->approxTimeDifference($value)).')</span>';
        } elseif (($property === 'ID' || substr($property, -3) === '_id') && is_integer($value)) {
            $value = $value <= 0 ? '—' : esc_html((string) $value);
        } else {
            $value = esc_html($this->plugin->utils_string->midClip((string) $value));
        }
        return isset($value[0]) ? $value : '—'; // Allow for `0`.
    }

    /*
     * Protected parameter-related methods.
     */

    /**
     * Get raw search query.
     *
     * @since 141111 First documented version.
     *
     * @return string Raw search query; w/ search tokens.
     */
    protected function getRawSearchQuery()
    {
        $s = !empty($_REQUEST['s'])
            ? trim(stripslashes((string) $_REQUEST['s']))
            : ''; // Not searching.

        if (!isset($s[0])) {
            return '';
        }
        $_GET['s'] = $_REQUEST['s'] = addslashes($s);

        if (isset($_POST['s'])) {
            $_POST['s'] = $_REQUEST['s'];
        }
        return $s; // Raw query.
    }

    /**
     * Clean search query.
     *
     * @since 141111 First documented version.
     *
     * @return string Clean search query; minus search tokens.
     */
    protected function getCleanSearchQuery()
    {
        $s = $this->getRawSearchQuery();

        if ($s && strpos($s, '@') !== false && is_email($s)) {
            $s = ''; // Goes into email addresses.
        }
        $s = $s ? preg_replace($this->sub_ids_regex, '', $s) : '';
        $s = $s ? preg_replace($this->sub_emails_regex, '', $s) : '';
        $s = $s ? preg_replace($this->user_ids_regex, '', $s) : '';
        $s = $s ? preg_replace($this->post_ids_regex, '', $s) : '';
        $s = $s ? preg_replace($this->comment_ids_regex, '', $s) : '';
        $s = $s ? preg_replace($this->statuses_regex, '', $s) : '';
        $s = $s ? preg_replace($this->events_regex, '', $s) : '';
        $s = $s ? preg_replace($this->and_regex, '', $s) : '';
        $s = $s ? trim(preg_replace('/\s+/', ' ', $s)) : '';

        return $s; // Search search query.
    }

    /**
     * A clean `$_POST['search-submit']`?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if a clean `$_POST['search-submit']`.
     */
    protected function isCleanSearchSubmitPost()
    {
        return !empty($_POST['search-submit']) && $this->getCleanSearchQuery();
    }

    /**
     * Get sub IDs in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array Sub IDs in the search query.
     */
    protected function getSubIdsInSearchQuery()
    {
        $sub_ids = []; // Initialize.
        $s       = $this->getRawSearchQuery();

        if ($s && preg_match_all($this->sub_ids_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['sub_ids']), null, PREG_SPLIT_NO_EMPTY) as $_sub_id) {
                if (($_sub_id = (int) $_sub_id) > 0) {
                    $sub_ids[$_sub_id] = $_sub_id;
                }
            }
        } // unset($_m, $_sub_id); // Housekeeping.

        return $sub_ids;
    }

    /**
     * Get sub emails in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array Sub emails in the search query.
     */
    protected function getSubEmailsInSearchQuery()
    {
        $sub_emails = []; // Initialize.
        $s          = $this->getRawSearchQuery();

        if ($s && strpos($s, '@') !== false && is_email($s)) {
            $s              = strtolower($s);
            $sub_emails[$s] = $s; // Email address.
        } elseif ($s && preg_match_all($this->sub_emails_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['sub_emails']), null, PREG_SPLIT_NO_EMPTY) as $_sub_email) {
                if (($_sub_email = trim(strtolower($_sub_email)))) {
                    $sub_emails[$_sub_email] = $_sub_email;
                }
            }
        } // unset($_m, $_sub_email); // Housekeeping.

        return $sub_emails;
    }

    /**
     * Get user IDs in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array User IDs in the search query.
     */
    protected function getUserIdsInSearchQuery()
    {
        $user_ids = []; // Initialize.
        $s        = $this->getRawSearchQuery();

        if ($s && preg_match_all($this->user_ids_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['user_ids']), null, PREG_SPLIT_NO_EMPTY) as $_user_id) {
                if (($_user_id = (int) $_user_id) > 0) {
                    $user_ids[$_user_id] = $_user_id;
                }
            }
        } // unset($_m, $_user_id); // Housekeeping.

        return $user_ids;
    }

    /**
     * Get post IDs in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array Post IDs in the search query.
     */
    protected function getPostIdsInSearchQuery()
    {
        $post_ids = []; // Initialize.
        $s        = $this->getRawSearchQuery();

        if ($s && preg_match_all($this->post_ids_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['post_ids']), null, PREG_SPLIT_NO_EMPTY) as $_post_id) {
                if (($_post_id = (int) $_post_id) > 0) {
                    $post_ids[$_post_id] = $_post_id;
                }
            }
        } // unset($_m, $_post_id); // Housekeeping.

        return $post_ids;
    }

    /**
     * Get comment IDs in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array Comment IDs in the search query.
     */
    protected function getCommentIdsInSearchQuery()
    {
        $comment_ids = []; // Initialize.
        $s           = $this->getRawSearchQuery();

        if ($s && preg_match_all($this->comment_ids_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['comment_ids']), null, PREG_SPLIT_NO_EMPTY) as $_comment_id) {
                if (($_comment_id = (int) $_comment_id) > 0) {
                    $comment_ids[$_comment_id] = $_comment_id;
                }
            }
        } // unset($_m, $_comment_id); // Housekeeping.

        return $comment_ids;
    }

    /**
     * Get statuses in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array Statuses in the search query.
     */
    protected function getStatusesInSearchQuery()
    {
        $statuses = []; // Initialize.
        $s        = $this->getRawSearchQuery();

        if ($s && preg_match_all($this->statuses_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['statuses']), null, PREG_SPLIT_NO_EMPTY) as $_status) {
                if (($_status = trim(strtolower($_status)))) {
                    $statuses[$_status] = $_status;
                }
            }
        } // unset($_m, $_status); // Housekeeping.

        return $statuses;
    }

    /**
     * Get events in the search query.
     *
     * @since 141111 First documented version.
     *
     * @return array Events in the search query.
     */
    protected function getEventsInSearchQuery()
    {
        $events = []; // Initialize.
        $s      = $this->getRawSearchQuery();

        if ($s && preg_match_all($this->events_regex, $s, $_m)) {
            foreach (preg_split('/[|;,]+/', implode(',', $_m['events']), null, PREG_SPLIT_NO_EMPTY) as $_event) {
                if (($_event = trim(strtolower($_event)))) {
                    $events[$_event] = $_event;
                }
            }
        } // unset($_m, $_event); // Housekeeping.

        return $events;
    }

    /**
     * Are we dealing w/ an `AND` search?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if it's an `AND` search.
     */
    protected function isAndSearchQuery()
    {
        $s = $this->getRawSearchQuery();

        return $s && preg_match($this->and_regex, $s);
    }

    /**
     * Get orderby value.
     *
     * @since 141111 First documented version.
     *
     * @return string The orderby value.
     */
    protected function getOrderby()
    {
        $orderby = !empty($_REQUEST['orderby'])
            ? strtolower(trim(stripslashes((string) $_REQUEST['orderby'])))
            : ''; // Not specified explicitly by site owner.

        if (!$orderby || !in_array($orderby, array_keys($this->get_columns()), true)) {
            $orderby = $this->getCleanSearchQuery() && $this->getFtSearchableColumns() ? 'relevance' : '';
        }
        if ($this->isCleanSearchSubmitPost()) { // Force `orderby`.
            $orderby = $this->getFtSearchableColumns() ? 'relevance' : '';
        }
        $orderby = !$orderby ? $this->getDefaultOrderby() : $orderby;

        $_GET['orderby'] = $_REQUEST['orderby'] = addslashes($orderby);
        if (isset($_POST['orderby'])) {
            $_POST['orderby'] = addslashes($orderby);
        }
        return $orderby; // Current orderby.
    }

    /**
     * Get default orderby value.
     *
     * @since     141111 First documented version.
     *
     * @return string The default orderby value.
     *
     * @extenders Extenders should normally override this.
     */
    protected function getDefaultOrderby()
    {
        return ''; // Default orderby.
    }

    /**
     * Get order value.
     *
     * @since 141111 First documented version.
     *
     * @return string The order value.
     */
    protected function getOrder()
    {
        $order = !empty($_REQUEST['order'])
            ? strtolower(trim(stripslashes((string) $_REQUEST['order'])))
            : ''; // Not specified explicitly by site owner.

        if (!$order || !in_array($order, ['asc', 'desc'], true)) {
            $order = $this->getCleanSearchQuery() ? 'desc' : '';
        }
        if ($this->isCleanSearchSubmitPost()) {
            $order = 'desc'; // Force by relevance.
        }
        $order = !$order ? $this->getDefaultOrder() : $order;

        $_GET['order'] = $_REQUEST['order'] = addslashes($order);
        if (isset($_POST['order'])) {
            $_POST['order'] = addslashes($order);
        }
        return $order; // Current order.
    }

    /**
     * Get default order value.
     *
     * @since     141111 First documented version.
     *
     * @return string The default order value.
     *
     * @extenders Extenders should normally override this.
     */
    protected function getDefaultOrder()
    {
        return ''; // Default order.
    }

    /*
     * Public query-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Runs DB query; sets pagination args.
     *
     * @since     141111 First documented version.
     *
     * @extenders Extenders should ALWAYS override this.
     */
    public function prepare_items()
    { // @codingStandardsIgnoreEnd
        /*
         * This is just a simple example showing
         * only the most useful getters/setters/helpers.
         */
        $per_page                    = $this->getPerPage();
        $current_offset              = $this->getCurrentOffset();
        $clean_search_query          = $this->getCleanSearchQuery();
        $sub_ids_in_search_query     = $this->getSubIdsInSearchQuery();
        $sub_emails_in_search_query  = $this->getSubEmailsInSearchQuery();
        $user_ids_in_search_query    = $this->getUserIdsInSearchQuery();
        $post_ids_in_search_query    = $this->getPostIdsInSearchQuery();
        $comment_ids_in_search_query = $this->getCommentIdsInSearchQuery();
        $statuses_in_search_query    = $this->getStatusesInSearchQuery();
        $events_in_search_query      = $this->getEventsInSearchQuery();
        $is_and_search_query         = $this->isAndSearchQuery();
        $orderby                     = $this->getOrderby();
        $order                       = $this->getOrder();

        $this->setItems([]); // `$this->items` = an array of \stdClass objects.
        $this->setTotalItemsAvailable((int) $this->plugin->utils_db->wp->get_var('SELECT FOUND_ROWS()'));

        $this->prepareItemsMergeSubProperties();
        $this->prepareItemsMergeUserProperties();
        $this->prepareItemsMergePostProperties();
        $this->prepareItemsMergeCommentProperties();
    }

    /**
     * Prepares searchable columns.
     *
     * @since 141111 First documented version.
     *
     * @param string $template The template to use for matches.
     *                         Defaults to `= %1$s` for exact matches.
     *
     * @return string A sequence of `OR ` checks for SQL matches.
     */
    protected function prepareSearchableOrCols($template = '')
    {
        if (!$this->getSearchableColumns()) {
            return ''; // Not applicable.
        }
        if (!($clean_search_query = $this->getCleanSearchQuery())) {
            return ''; // Not applicable.
        }
        $search_like_cols            = ''; // Initialize.
        $esc_clean_search_query      = esc_sql($clean_search_query);
        $esc_like_clean_search_query = esc_sql($this->plugin->utils_db->wp->esc_like($clean_search_query));

        if (!($template = trim(str_replace(["'", '"'], '', (string) $template)))) {
            $template = '= %1$s'; // Use the default template.
        }
        foreach ($this->getSearchableColumns() as $_column) {
            $search_like_cols .= ' OR `'.esc_sql($_column).'`'.// Using the template.
                                 ' '.sprintf($template, "'".$esc_clean_search_query."'", "'".$esc_like_clean_search_query."'");
        }
        unset($_column); // Housekeeping.

        return $search_like_cols;
    }

    /*
     * Protected query-related getters/setters.
     */

    /**
     * Gets configured items per page.
     *
     * @since 141111 First documented version.
     *
     * @return int Configured items per page.
     */
    protected function getPerPage()
    {
        $max_limit       = $this->plugin->utils_user->screenOption($this->screen, 'per_page');
        $upper_max_limit = (int) apply_filters(get_class($this).'_upper_max_limit', 1000);

        $max_limit = $max_limit < 1 ? 1 : $max_limit;
        $max_limit = $max_limit > $upper_max_limit ? 100 : $max_limit;

        return $max_limit;
    }

    /**
     * Gets current page number.
     *
     * @since 141111 First documented version.
     *
     * @return int Current page number.
     */
    protected function getCurrentPage()
    {
        return $current_page = $this->get_pagenum();
    }

    /**
     * Gets current SQL offset.
     *
     * @since 141111 First documented version.
     *
     * @return int Current SQL offset value.
     */
    protected function getCurrentOffset()
    {
        return $current_offset = ($this->getCurrentPage() - 1) * $this->getPerPage();
    }

    /**
     * Sets total items and pagination args.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass[] An array of \stdClass objects.
     *
     * @return \stdClass[] Returns the items.
     */
    protected function setItems($items)
    {
        return $this->items = $items;
    }

    /**
     * Sets total items and pagination args.
     *
     * @since 141111 First documented version.
     *
     * @param int $calc_found_rows Total found rows using `SQL_CALC_FOUND_ROWS`.
     *
     * @return int Total items available; i.e. number of found rows.
     */
    protected function setTotalItemsAvailable($calc_found_rows)
    {
        $per_page    = $this->getPerPage();
        $total_items = (int) $calc_found_rows;
        $total_pages = ceil($total_items / $per_page);
        $this->set_pagination_args(compact('per_page', 'total_items', 'total_pages'));

        return $total_items;
    }

    /*
     * Protected query-related helpers.
     */

    /**
     * Assists w/ DB query; i.e. item preparations.
     *
     * @since 141111 First documented version.
     */
    protected function prepareItemsMergeSubProperties()
    {
        $sub_ids = []; // Initialize.

        $alts = [
            'sub_before_'     => 'sub_id_before',
            'oby_sub_'        => 'oby_sub_id',
            'oby_sub_before_' => 'oby_sub_id_before',
        ];
        foreach ($this->items as $_item) {
            if (!empty($_item->sub_id)) {
                $sub_ids[$_item->sub_id] = $_item->sub_id;
            }
        }
        unset($_item); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                if (!empty($_item->{$_key})) {
                    $sub_ids[$_item->{$_key}] = $_item->{$_key};
                }
            }
        }
        unset($_prefix, $_key, $_item); // Housekeeping.

        $sql_columns = [
            'ID',
            'key',

            'user_id',
            'post_id',
            'comment_id',

            'deliver',
            'status',

            'fname',
            'lname',
            'email',

            'insertion_ip',
            'insertion_region',
            'insertion_country',

            'last_ip',
            'last_region',
            'last_country',

            'insertion_time',
            'last_update_time',
        ];
        $sql_item_columns = $sql_columns;
        unset($sql_item_columns[0]); // Exclude `ID`.

        $sql = 'SELECT `'.implode('`,`', array_map('esc_sql', $sql_columns)).'`'.
               ' FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `ID` IN('".implode("','", array_map('esc_sql', $sub_ids))."')";

        if ($sub_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $this->merged_result_sets['subs'] = $results = $this->plugin->utils_db->typifyDeep($results);
        }
        foreach ($this->items as $_item) {
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'sub_') !== 0) {
                    $_item->{'sub_'.$_sql_item_column} = null;
                } else {
                    $_item->{$_sql_item_column} = null;
                }
            }
            if (!isset($_item->sub_id)) {
                continue; // Not possible.
            }
            if (empty($results) || empty($results[$_item->sub_id])) {
                continue; // Not possible.
            }
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'sub_') !== 0) {
                    $_item->{'sub_'.$_sql_item_column} = $results[$_item->sub_id]->{$_sql_item_column};
                } else {
                    $_item->{$_sql_item_column} = $results[$_item->sub_id]->{$_sql_item_column};
                }
            }
        }
        unset($_item, $_sql_item_column); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^sub_/i', '', $_sql_item_column)} = null;
                }
                if (!isset($_item->{$_key})) {
                    continue; // Not possible.
                }
                if (empty($results) || empty($results[$_item->{$_key}])) {
                    continue; // Not possible.
                }
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^sub_/i', '', $_sql_item_column)}
                        = $results[$_item->{$_key}]->{$_sql_item_column};
                }
            }
        }
        unset($_prefix, $_key, $_item, $_sql_item_column); // Housekeeping.

        $this->items = $this->plugin->utils_db->typifyDeep($this->items);
    }

    /**
     * Assists w/ DB query; i.e. item preparations.
     *
     * @since 141111 First documented version.
     */
    protected function prepareItemsMergeUserProperties()
    {
        $user_ids = []; // Initialize.

        $alts = [
            'user_before_' => 'user_id_before',
        ];
        foreach ($this->items as $_item) {
            if (!empty($_item->user_id)) {
                $user_ids[$_item->user_id] = $_item->user_id;
            }
        }
        unset($_item); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                if (!empty($_item->{$_key})) {
                    $user_ids[$_item->{$_key}] = $_item->{$_key};
                }
            }
        }
        unset($_prefix, $_key, $_item); // Housekeeping.

        $sql_columns = [
            'ID',
            'user_login',
            'user_nicename',
            'user_email',
            'user_url',
            'user_registered',
            'user_activation_key',
            'user_status',
            'display_name',
        ];
        $sql_item_columns = $sql_columns;
        unset($sql_item_columns[0]); // Exclude `ID`.

        $sql = 'SELECT `'.implode('`,`', array_map('esc_sql', $sql_columns)).'`'.
               ' FROM `'.esc_sql($this->plugin->utils_db->wp->users).'`'.
               " WHERE `ID` IN('".implode("','", array_map('esc_sql', $user_ids))."')";

        if ($user_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $this->merged_result_sets['users'] = $results = $this->plugin->utils_db->typifyDeep($results);
        }
        foreach ($this->items as $_item) {
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'user_') !== 0) {
                    $_item->{'user_'.$_sql_item_column} = null;
                } else {
                    $_item->{$_sql_item_column} = null;
                }
            }
            if (!isset($_item->user_id)) {
                continue; // Not possible.
            }
            if (empty($results) || empty($results[$_item->user_id])) {
                continue; // Not possible.
            }
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'user_') !== 0) {
                    $_item->{'user_'.$_sql_item_column} = $results[$_item->user_id]->{$_sql_item_column};
                } else {
                    $_item->{$_sql_item_column} = $results[$_item->user_id]->{$_sql_item_column};
                }
            }
        }
        unset($_item, $_sql_item_column); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^user_/i', '', $_sql_item_column)} = null;
                }
                if (!isset($_item->{$_key})) {
                    continue; // Not possible.
                }
                if (empty($results) || empty($results[$_item->{$_key}])) {
                    continue; // Not possible.
                }
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^user_/i', '', $_sql_item_column)}
                        = $results[$_item->{$_key}]->{$_sql_item_column};
                }
            }
        }
        unset($_prefix, $_key, $_item, $_sql_item_column); // Housekeeping.

        $this->items = $this->plugin->utils_db->typifyDeep($this->items);
    }

    /**
     * Assists w/ DB query; i.e. item preparations.
     *
     * @since 141111 First documented version.
     */
    protected function prepareItemsMergePostProperties()
    {
        $post_ids = []; // Initialize.

        $alts = [
            'post_before_' => 'post_id_before',
        ];
        foreach ($this->items as $_item) {
            if (!empty($_item->post_id)) {
                $post_ids[$_item->post_id] = $_item->post_id;
            }
        }
        unset($_item); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                if (!empty($_item->{$_key})) {
                    $post_ids[$_item->{$_key}] = $_item->{$_key};
                }
            }
        }
        unset($_prefix, $_key, $_item); // Housekeeping.

        $sql_columns = [
            'ID',
            'post_title',
            'post_status',
            'comment_status',
            'post_date_gmt',
            'post_type',
            'comment_count',
        ];
        $sql_item_columns = $sql_columns;
        unset($sql_item_columns[0]); // Exclude `ID`.

        $sql = 'SELECT `'.implode('`,`', array_map('esc_sql', $sql_columns)).'`'.
               ' FROM `'.esc_sql($this->plugin->utils_db->wp->posts).'`'.
               " WHERE `ID` IN('".implode("','", array_map('esc_sql', $post_ids))."')";

        if ($post_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $this->merged_result_sets['posts'] = $results = $this->plugin->utils_db->typifyDeep($results);
        }
        foreach ($this->items as $_item) {
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'post_') !== 0) {
                    $_item->{'post_'.$_sql_item_column} = null;
                } else {
                    $_item->{$_sql_item_column} = null;
                }
            }
            if (!isset($_item->post_id)) {
                continue; // Not possible.
            }
            if (empty($results) || empty($results[$_item->post_id])) {
                continue; // Not possible.
            }
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'post_') !== 0) {
                    $_item->{'post_'.$_sql_item_column} = $results[$_item->post_id]->{$_sql_item_column};
                } else {
                    $_item->{$_sql_item_column} = $results[$_item->post_id]->{$_sql_item_column};
                }
            }
        }
        unset($_item, $_sql_item_column); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^post_/i', '', $_sql_item_column)} = null;
                }
                if (!isset($_item->{$_key})) {
                    continue; // Not possible.
                }
                if (empty($results) || empty($results[$_item->{$_key}])) {
                    continue; // Not possible.
                }
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^post_/i', '', $_sql_item_column)}
                        = $results[$_item->{$_key}]->{$_sql_item_column};
                }
            }
        }
        unset($_prefix, $_key, $_item, $_sql_item_column); // Housekeeping.

        $this->items = $this->plugin->utils_db->typifyDeep($this->items);
    }

    /**
     * Assists w/ DB query; i.e. item preparations.
     *
     * @since 141111 First documented version.
     */
    protected function prepareItemsMergeCommentProperties()
    {
        $comment_ids = []; // Initialize.

        $alts = [
            'comment_before_'        => 'comment_id_before',
            'comment_parent_'        => 'comment_parent_id',
            'comment_parent_before_' => 'comment_parent_id_before',
        ];
        foreach ($this->items as $_item) {
            if (!empty($_item->comment_id)) {
                $comment_ids[$_item->comment_id] = $_item->comment_id;
            }
        }
        unset($_item); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                if (!empty($_item->{$_key})) {
                    $comment_ids[$_item->{$_key}] = $_item->{$_key};
                }
            }
        }
        unset($_prefix, $_key, $_item); // Housekeeping.

        $sql_columns = [
            'comment_ID',
            'comment_author',
            'comment_author_email',
            'comment_date_gmt',
            'comment_approved',
            'comment_type',
            'comment_parent',
        ];
        $sql_item_columns = $sql_columns;
        unset($sql_item_columns[0]); // Exclude `comment_ID`.

        $sql = 'SELECT `'.implode('`,`', array_map('esc_sql', $sql_columns)).'`'.
               ' FROM `'.esc_sql($this->plugin->utils_db->wp->comments).'`'.
               " WHERE `comment_ID` IN('".implode("','", array_map('esc_sql', $comment_ids))."')";

        if ($comment_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            $this->merged_result_sets['comments'] = $results = $this->plugin->utils_db->typifyDeep($results);
        }
        foreach ($this->items as $_item) {
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'comment_') !== 0) {
                    $_item->{'comment_'.$_sql_item_column} = null;
                } else {
                    $_item->{$_sql_item_column} = null;
                }
            }
            if (!isset($_item->comment_id)) {
                continue; // Not possible.
            }
            if (empty($results) || empty($results[$_item->comment_id])) {
                continue; // Not possible.
            }
            foreach ($sql_item_columns as $_sql_item_column) {
                if (strpos($_sql_item_column, 'comment_') !== 0) {
                    $_item->{'comment_'.$_sql_item_column} = $results[$_item->comment_id]->{$_sql_item_column};
                } else {
                    $_item->{$_sql_item_column} = $results[$_item->comment_id]->{$_sql_item_column};
                }
            }
        }
        unset($_item, $_sql_item_column); // Housekeeping.

        foreach ($alts as $_prefix => $_key) {
            foreach ($this->items as $_item) {
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^comment_/i', '', $_sql_item_column)} = null;
                }
                if (!isset($_item->{$_key})) {
                    continue; // Not possible.
                }
                if (empty($results) || empty($results[$_item->{$_key}])) {
                    continue; // Not possible.
                }
                foreach ($sql_item_columns as $_sql_item_column) {
                    $_item->{$_prefix.// Prefix each of these.
                             preg_replace('/^comment_/i', '', $_sql_item_column)}
                        = $results[$_item->{$_key}]->{$_sql_item_column};
                }
            }
        }
        unset($_prefix, $_key, $_item, $_sql_item_column); // Housekeeping.

        $this->items = $this->plugin->utils_db->typifyDeep($this->items);
    }

    /*
     * Protected action-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Bulk actions for this table.
     *
     * @since     141111 First documented version.
     *
     * @return array An array of all bulk actions.
     *
     * @extenders Extenders should normally override this.
     */
    protected function get_bulk_actions()
    { // @codingStandardsIgnoreEnd
        return [];
    }

    /**
     * Bulk action handler for this table.
     *
     * @since 141111 First documented version.
     */
    protected function maybeProcessBulkAction()
    {
        if (!($bulk_action = stripslashes((string) $this->current_action()))) {
            return; // Nothing to do; no action requested here.
        }
        if (!$this->plugin->utils_url->hasValidNonce('bulk-'.$this->plural_name)) {
            return; // Unauthenticated; ignore.
        }
        if (empty($_REQUEST[$this->plural_name]) || !is_array($_REQUEST[$this->plural_name])) {
            return; // Nothing to do; i.e. no boxes were checked in this case.
        }
        if (!($ids = array_map('intval', $_REQUEST[$this->plural_name]))) {
            return; // Nothing to do; i.e. we have no IDs.
        }
        if (!current_user_can($this->plugin->manage_cap)) {
            if (!current_user_can($this->plugin->cap)) {
                return; // Unauthenticated; ignore.
            }
        }
        $counter = $this->processBulkAction($bulk_action, $ids);

        if (method_exists($this->plugin->utils_i18n, $this->plural_name)) {
            $this->plugin->enqueueUserNotice(
                sprintf(
                    __('Action complete. %1$s %2$s.', 'comment-mail'),
                    esc_html($this->plugin->utils_i18n->{$this->plural_name}($counter)),
                    esc_html($this->plugin->utils_i18n->actionEd($bulk_action))
                ),
                ['transient' => true, 'for_page' => $this->plugin->utils_env->currentMenuPage()]
            );
        }
        $redirect_to = $this->plugin->utils_url->pageTableNavVarsOnly();

        if (headers_sent()) { // Output started already?
            exit('      <script type="text/javascript">'.
                 "         document.getElementsByTagName('body')[0].style.display = 'none';".
                 "         location.href = '".$this->plugin->utils_string->escJsSq($redirect_to)."';".
                 '      </script>'.
                 '   </body>'.
                 '</html>');
        }
        wp_redirect($redirect_to);
        exit();
    }

    /**
     * Bulk action handler for this table.
     *
     * @since     141111 First documented version.
     *
     * @param string $bulk_action The bulk action to process.
     * @param array  $ids         The bulk action IDs to process.
     *
     * @return int Number of actions processed successfully.
     *
     * @extenders Extenders should normally override this.
     */
    protected function processBulkAction($bulk_action, array $ids)
    {
        return !empty($counter) ? (int) $counter : 0;
    }

    /*
     * Public display-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Display search box.
     *
     * @since 141111 First documented version.
     *
     * @param string $text     The search button `value=""`.
     *                         This will default to a value of `Search`.
     * @param string $input_id The search input `id=""` attribute.
     *                         This parameter is always forced to an id-compatible value.
     *                         This will default to a value of `get_class($this).'::'.__FUNCTION__`.
     *
     * @throws \exception If unable to do `name="search-submit"` replacement.
     */
    public function search_box($text = '', $input_id = '')
    { // @codingStandardsIgnoreEnd
        if (!$this->getFtSearchableColumns()
            && !$this->getSearchableColumns()
            && !$this->getNavigableFilters()
        ) {
            return; // Not applicable.
        }
        $text     = (string) $text;
        $input_id = (string) $input_id;
        $text     = !$text ? __('Search', 'comment-mail') : esc_html($text);
        $input_id = !$input_id ? get_class($this).'::'.__FUNCTION__ : $input_id;
        $input_id = trim(preg_replace('/[^a-z0-9\-]/i', '-', $input_id), '-');

        ob_start(); // Open an output buffer.
        parent::search_box($text, $input_id);
        $search_box = ob_get_clean();

        $regex = '/\b(?:name\s*\=\s*(["\']).*?\\1\s+)?id\s*\=\s*(["\'])search\-submit\\2/i';

        if ($search_box) { // Only if there is a search box; it doesn't always display.
            if (!($search_box = preg_replace($regex, 'name="search-submit" id="search-submit"', $search_box, 1, $replacements)) || !$replacements) {
                throw new \exception(__('Unable to set `name="search-submit"` attribute.', 'comment-mail'));
            }
        }
        echo $search_box; // Display.
    }

    /**
     * Display search query filter descs.
     *
     * @since 141111 First documented version.
     */
    public function searchQueryFilterDescriptions()
    {
        $sub_ids           = $this->getSubIdsInSearchQuery();
        $sub_emails        = $this->getSubEmailsInSearchQuery();
        $user_ids          = $this->getUserIdsInSearchQuery();
        $post_ids          = $this->getPostIdsInSearchQuery();
        $comment_ids       = $this->getCommentIdsInSearchQuery();
        $statuses          = $this->getStatusesInSearchQuery();
        $events            = $this->getEventsInSearchQuery();
        $navigable_filters = $this->getNavigableFilters();
        $raw_search_query  = $this->getRawSearchQuery();

        $query_contains_filters           = $sub_ids || $sub_emails || $user_ids || $post_ids || $comment_ids;
        $query_contains_navigable_filters = !empty($statuses) || !empty($events);
        $navigable_filters_exist          = !empty($navigable_filters);

        if (!$query_contains_filters && !$navigable_filters_exist) {
            return; // Nothing to do here.
        }
        $subs    = $users    = $posts    = $comments    = []; // Array of bject references.
        $sub_lis = $sub_email_lis = $user_lis = $post_lis = $comment_lis = $navigable_filter_lis = $unknown_lis = [];

        foreach ($sub_ids as $_sub_id) {
            if (($_sub = $this->plugin->utils_sub->get($_sub_id))) {
                $subs[] = $_sub;
            }
        }
        unset($_sub_id, $_sub); // Housekeeping.

        foreach ($user_ids as $_user_id) {
            if (($_user = new \WP_User($_user_id)) && $_user->ID) {
                $users[] = $_user;
            }
        }
        unset($_user_id, $_user); // Housekeeping.

        foreach ($post_ids as $_post_id) {
            if (($_post = get_post($_post_id))) {
                $posts[] = $_post;
            }
        }
        unset($_post_id, $_post); // Housekeeping.

        foreach ($comment_ids as $_comment_id) {
            if (($_comment = get_comment($_comment_id))) {
                $comments[] = $_comment;
            }
        }
        unset($_comment_id, $_comment); // Housekeeping.

        foreach ($subs as $_sub) { // `\stdClass` objects.
            /** @var $_sub \stdClass Reference for IDEs. */
            if (isset($sub_lis[$_sub->ID])) {
                continue; // Duplicate.
            }
            $_name_email_args = [
                'anchor_to'   => 'search',
                'name_style'  => 'font-weight:bold;',
                'email_style' => 'font-weight:normal;',
            ];
            $_sub_name      = $_sub->fname.' '.$_sub->lname; // Concatenate.
            $_sub_edit_link = $this->plugin->utils_url->editSubShort($_sub->ID);

            $sub_lis[$_sub->ID] = '<li>'.// [icon] ID "Name" <email> [edit].
                                  '<i class="'.esc_attr('si si-'.SLUG_TD).'"></i>'.
                                  ' '.$this->plugin->utils_markup->nameEmail($_sub_name, $_sub->email, $_name_email_args).
                                  ($_sub_edit_link // Only if they can edit the subscription ID; else this will be empty.
                                      ? ' [<a href="'.esc_attr($_sub_edit_link).'">'.__('edit', 'comment-mail').'</a>]' : '').
                                  '</li>';
        }
        unset($_sub, $_name_email_args, $_sub_name, $_sub_edit_link); // Housekeeping.

        foreach ($sub_emails as $_sub_email) { // Strings only.
            $_sub_email = trim(strtolower($_sub_email));

            if (isset($sub_email_lis[$_sub_email])) {
                continue; // Duplicate.
            }
            $_name_email_args = [
                'anchor_to'   => 'search',
                'email_style' => 'font-weight:bold;',
            ];
            $sub_email_lis[$_sub_email] = '<li>'.// [icon] <email>.
                                          '<i class="fa fa-envelope"></i>'.// e.g. [icon] <email>.
                                          ' <span style="font-weight:bold;" title="'.esc_attr($_sub_email).'">'.__('Email:', 'comment-mail').'</span>'.
                                          ' '.$this->plugin->utils_markup->nameEmail('', $_sub_email, $_name_email_args).
                                          '</li>';
        }
        unset($_sub_email, $_name_email_args); // Housekeeping.

        foreach ($users as $_user) { // `\WP_User` objects.
            /** @var $_user \WP_User Reference for IDEs. */
            if (isset($user_lis[$_user->ID])) {
                continue; // Duplicate.
            }
            $_name_email_args = [
                'anchor_to'   => 'search',
                'name_style'  => 'font-weight:bold;',
                'email_style' => 'font-weight:normal;',
            ];
            $_user_edit_link = get_edit_user_link($_user->ID);

            $user_lis[$_user->ID] = '<li>'.// [icon] ID "Name" <email> [edit].
                                    '<i class="fa fa-user"></i>'.// e.g. [icon] "Name" <email>
                                    ' '.$this->plugin->utils_markup->nameEmail($_user->display_name, $_user->user_email, $_name_email_args).
                                    ($_user_edit_link // Only if they can edit the user ID; else this will be empty.
                                        ? ' [<a href="'.esc_attr($_user_edit_link).'">'.__('edit', 'comment-mail').'</a>]' : '').
                                    '</li>';
        }
        unset($_user, $_name_email_args, $_user_edit_link); // Housekeeping.

        foreach ($posts as $_post) { // `\WP_Post` objects.
            /** @var $_post \WP_Post Reference for IDEs. */
            if (isset($post_lis[$_post->ID])) {
                continue; // Duplicate.
            }
            if (!($_post_type = get_post_type_object($_post->post_type))) {
                continue; // Unable to determine type.
            }
            $_post_permalink  = get_permalink($_post->ID);
            $_post_edit_link  = get_edit_post_link($_post->ID, '');
            $_post_title_clip = $this->plugin->utils_string->midClip($_post->post_title);
            $_post_type_label = $_post_type->labels->singular_name;

            $post_lis[$_post->ID] = '<li>'.// <title> [edit].
                                    '  "<a href="'.esc_attr($_post_permalink).'">'.esc_html($_post_title_clip).'</a>"'.
                                    ($_post_edit_link // Only if they can edit the post ID; else this will be empty.
                                        ? ' [<a href="'.esc_attr($_post_edit_link).'">'.__('edit', 'comment-mail').'</a>]' : '').
                                    '</li>';
        }
        unset($_post, $_post_type, $_post_permalink, $_post_edit_link, $_post_title_clip, $_post_type_label); // Housekeeping.

        foreach ($comments as $_comment) {
            // `\stdClass` objects.

            /** @var $_comment \stdClass Reference for IDEs. */
            /* @var $_post \WP_Post Reference for IDEs. */

            if (isset($comment_lis[$_comment->comment_ID])) {
                continue; // Duplicate.
            }
            if (!($_post = get_post($_comment->comment_post_ID))) {
                continue; // Unable to get underlying post.
            }
            if (!($_post_type = get_post_type_object($_post->post_type))) {
                continue; // Unable to determine type.
            }
            $_name_email_args = [
                'anchor_to'   => 'search',
                'name_style'  => 'font-weight:bold;',
                'email_style' => 'font-weight:normal;',
            ];
            $_post_permalink  = get_permalink($_post->ID);
            $_post_edit_link  = get_edit_post_link($_post->ID, '');
            $_post_title_clip = $this->plugin->utils_string->midClip($_post->post_title);
            $_post_type_label = $_post_type->labels->singular_name;

            $_comment_permalink    = get_comment_link($_comment->comment_ID);
            $_comment_edit_link    = get_edit_comment_link($_comment->comment_ID);
            $_comment_content_clip = $this->plugin->utils_string->clip($_comment->comment_content, 100);

            $comment_lis[$_comment->comment_ID] = '<li>'.// <title> [edit].
                                                  '   "<a href="'.esc_attr($_post_permalink).'">'.esc_html($_post_title_clip).'</a>"'.
                                                  ($_post_edit_link // Only if they can edit the post ID; else this will be empty.
                                                      ? ' [<a href="'.esc_attr($_post_edit_link).'">'.__('edit', 'comment-mail').'</a>]' : '').

                                                  '   <ul>'.// Nest comment under post.
                                                  '      <li>'.// Comment ID: <author> [edit] ... followed by a content clip.
                                                  '         <span style="font-weight:bold;">'.__('Comment', 'comment-mail').'</span>'.
                                                  '         <span style="font-weight:bold;">ID <a href="'.esc_attr($_comment_permalink).'" target="_blank">#'.esc_html($_comment->comment_ID).'</a>:</span>'.
                                                  '         '.$this->plugin->utils_markup->nameEmail($_comment->comment_author, $_comment->comment_author_email, $_name_email_args).
                                                  ($_comment_edit_link // Only if they can edit the comment ID; else this will be empty.
                                                      ? '     [<a href="'.esc_attr($_comment_edit_link).'">'.__('edit', 'comment-mail').'</a>]' : '').
                                                  '         <blockquote>'.esc_html($_comment_content_clip).'</blockquote>'.
                                                  '      </li>'.
                                                  '   </ul>'.
                                                  '</li>';
        }
        unset($_comment, $_post, $_post_type, $_name_email_args, $_post_permalink, $_post_edit_link, $_post_title_clip, $_post_type_label, $_comment_permalink, $_comment_edit_link, $_comment_content_clip); // Housekeeping.

        foreach ($navigable_filters as $_navigable_filter_s => $_navigable_filter_label) {
            if (!$navigable_filter_lis) { // `all` first; i.e. a way to remove all navigable filters.
                $navigable_filter_lis[] = '<li>'.// List item for special navigable filter `all`.
                                          '   <a href="'.esc_attr($this->plugin->utils_url->tableSearchFilter('::')).'"'.
                                          (!$query_contains_navigable_filters ? ' class="pmp-active"' : '').'>'.
                                          '      '.__('all', 'comment-mail').
                                          '   </a>'.
                                          '</li>';
            }
            $navigable_filter_lis[] = '<li>'.// List item for a navigable filter in this table.
                                      '   <a href="'.esc_attr($this->plugin->utils_url->tableSearchFilter($_navigable_filter_s)).'"'.
                                      (stripos($raw_search_query, $_navigable_filter_s) !== false ? ' class="pmp-active"' : '').'>'.
                                      '      <span style="'.esc_attr($_navigable_filter_s === 'status::trashed' ? 'font-style:italic;' : '').'">'.
                                      '         '.esc_html($_navigable_filter_label).'</span>'.
                                      '   </a>'.
                                      '</li>';
        }
        unset($_navigable_filter_s, $_navigable_filter_label); // Housekeeping.

        $filter_lis_exist           = $sub_lis || $sub_email_lis || $user_lis || $post_lis || $comment_lis;
        $navigable_filter_lis_exist = !empty($navigable_filter_lis); // Have any navigable list items?

        if ($query_contains_filters) { // If query contains non-navigable filters.
            if (!$filter_lis_exist) { // Unable to build list items for search filter(s)?
                $unknown_lis[] = '<li>'.sprintf(
                    __('Unknown filter(s). Unable to build list items for: <code>%1$s</code>', 'comment-mail'),
                    esc_html($this->getRawSearchQuery())
                ).'</li>';
            }
            echo '<h3>'.// Display.
                 '   <i class="fa fa-filter"></i>'.// Filter icon.
                 '   '.sprintf(__('<strong>Search Filters Applied</strong> :: only showing %1$s for:', 'comment-mail'), esc_html($this->plural_label)).
                 '</h3>';
            if ($sub_lis) {
                echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $sub_lis).'</ul>';
            }
            if ($sub_email_lis) {
                echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $sub_email_lis).'</ul>';
            }
            if ($user_lis) {
                echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $user_lis).'</ul>';
            }
            if ($post_lis) {
                echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $post_lis).'</ul>';
            }
            if ($comment_lis) {
                echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $comment_lis).'</ul>';
            }
            if ($unknown_lis) {
                echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $unknown_lis).'</ul>';
            }
        }
        if ($navigable_filter_lis_exist && $navigable_filter_lis) {
            echo '<ul class="pmp-navigable-filters pmp-filters pmp-clean-list-items">'.
                 ' <li>'.__('Navigable Filters:', 'comment-mail').'</li>'.
                 ' '.implode('', $navigable_filter_lis).
                 '</ul>';
        }
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Prints column headers.
     *
     * @since 141111 First documented version.
     *
     * @param bool $with_id Add an `id=""` attribute?
     */
    public function print_column_headers($with_id = true)
    { // @codingStandardsIgnoreEnd
        ob_start(); // Open an output buffer.
        parent::print_column_headers($with_id);
        $column_headers = ob_get_clean();

        $regex = '/\b(href\s*\=\s*)(["\'])(.+?)(\\2)/i';

        if (($raw_search_query = $this->getRawSearchQuery())) {
            $column_headers = preg_replace_callback(
                $regex,
                function ($m) use ($raw_search_query) {
                    $m[3] = wp_specialchars_decode($m[3], ENT_QUOTES);
                    $m[3] = add_query_arg('s', urlencode($raw_search_query), $m[3]);
                    return $m[1].$m[2].esc_attr($m[3]).$m[4]; #
                },
                $column_headers
            );
        }
        echo $column_headers; // Display.
    }

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Prints no items message.
     *
     * @since 141111 First documented version.
     */
    public function no_items()
    { // @codingStandardsIgnoreEnd
        echo esc_html(sprintf(__('No %1$s to display.', 'comment-mail'), $this->plural_label));
    }

    /**
     * Display the table.
     *
     * @since 141111 First documented version.
     */
    public function display()
    {
        $this->search_box(); // When applicable.
        $this->searchQueryFilterDescriptions(); // When applicable.
        parent::display(); // Call parent handler now.
    }
}
