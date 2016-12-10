<?php
/**
 * Menu Page Subs. Table.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Menu Page Subs. Table.
 *
 * @since 141111 First documented version.
 */
class MenuPageSubsTable extends MenuPageTableBase
{
    /*
     * Class constructor.
     */

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        $plugin = plugin(); // Needed below.

        $args = [
            'singular_name'  => 'subscription',
            'plural_name'    => 'subscriptions',
            'singular_label' => __('subscription', 'comment-mail'),
            'plural_label'   => __('subscriptions', 'comment-mail'),
            'screen'         => $plugin->menu_page_hooks[GLOBAL_NS.'_subs'],
        ];
        parent::__construct($args); // Parent constructor.
    }

    /*
     * Public column-related methods.
     */

    /**
     * Table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all table columns.
     */
    public static function getTheColumns()
    {
        $plugin = plugin(); // Plugin class instance.

        $columns = [
            'cb' => '1', // Include checkboxes.

            'email' => __('Subscriber', 'comment-mail'),
            'fname' => __('First Name', 'comment-mail'),
            'lname' => __('Last Name', 'comment-mail'),

            'user_id'    => __('WP User ID', 'comment-mail'),
            'post_id'    => __('Post', 'comment-mail'),
            'comment_id' => __('Comment', 'comment-mail'),

            'deliver' => __('Delivery', 'comment-mail'),
            'status'  => __('Status', 'comment-mail'),

            'insertion_time'   => __('Subscr. Time', 'comment-mail'),
            'last_update_time' => __('Last Update', 'comment-mail'),

            'insertion_ip'      => __('Subscr. IP', 'comment-mail'),
            'insertion_region'  => __('IP Region', 'comment-mail'),
            'insertion_country' => __('IP Country', 'comment-mail'),

            'last_ip'      => __('Last IP', 'comment-mail'),
            'last_region'  => __('Last IP Region', 'comment-mail'),
            'last_country' => __('Last IP Country', 'comment-mail'),

            'key' => __('Key', 'comment-mail'),
            'ID'  => __('ID', 'comment-mail'),
        ];
        if (!$plugin->options['geo_location_tracking_enable']) {
            foreach ($columns as $_key => $_column) {
                if (in_array($_key, ['insertion_region', 'insertion_country', 'last_region', 'last_country'], true)) {
                    unset($columns[$_key]); // Ditch this column by key.
                }
            }
        }
        unset($_key, $_column); // Housekeeping.

        return $columns; // Associative array.
    }

    /**
     * Hidden table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all hidden table columns.
     */
    public static function getTheHiddenColumns()
    {
        $plugin = plugin(); // Plugin class instance.

        $columns = [
            'fname',
            'lname',

            'user_id',

            'deliver',

            'last_update_time',

            'insertion_ip',
            'insertion_region',
            'insertion_country',

            'last_ip',
            'last_region',
            'last_country',

            'key',
            'ID',
        ];
        if (!$plugin->options['geo_location_tracking_enable']) {
            foreach ($columns as $_key => $_column) {
                if (in_array($_column, ['insertion_region', 'insertion_country', 'last_region', 'last_country'], true)) {
                    unset($columns[$_key]); // Ditch this column by key.
                }
            }
        }
        unset($_key, $_column); // Housekeeping.

        return array_values($columns);
    }

    /**
     * Searchable fulltext table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all fulltext searchables.
     */
    public static function getTheFtSearchableColumns()
    {
        return [
            'email',
            'fname',
            'lname',

            'insertion_ip',
            'last_ip',

            'key',
        ];
    }

    /**
     * Searchable table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all searchables.
     */
    public static function getTheSearchableColumns()
    {
        return [
            'ID',
        ];
    }

    /**
     * Unsortable table columns.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all unsortable table columns.
     */
    public static function getTheUnsortableColumns()
    {
        return [];
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
    public static function getTheNavigableFilters()
    {
        $plugin = plugin(); // Needed for translations.

        return [
            'status::unconfirmed' => $plugin->utils_i18n->statusLabel('unconfirmed', 'ucwords'),
            'status::subscribed'  => $plugin->utils_i18n->statusLabel('subscribed', 'ucwords'),
            'status::suspended'   => $plugin->utils_i18n->statusLabel('suspended', 'ucwords'),
            'status::trashed'     => $plugin->utils_i18n->statusLabel('trashed', 'ucwords'),
        ];
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
     * @param \stdClass $item   Item object; i.e. a row from the DB.
     * @param string    $prefix Prefix for data associated w/ the key. Defaults to ``.
     * @param string    $key    A particular key to return. Defaults to `email`
     *
     * @return string HTML markup for this table column.
     */
    protected function column_email(\stdClass $item, $prefix = '', $key = 'email')
    { // @codingStandardsIgnoreEnd
        $name_email_args = [
            'separator'   => '<br />',
            'anchor_to'   => 'search',
            'name_style'  => 'font-weight:bold;',
            'email_style' => 'font-weight:normal;',
        ];
        $name       = $item->fname.' '.$item->lname; // Concatenate.
        $email_info = '<i class="'.esc_attr('si si-'.SLUG_TD.'-one').'"></i>'.
                           ' '.$this->plugin->utils_markup->nameEmail($name, $item->email, $name_email_args);

        $edit_url      = $this->plugin->utils_url->editSubShort($item->ID);
        $reconfirm_url = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'reconfirm');
        $confirm_url   = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'confirm');
        $unconfirm_url = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'unconfirm');
        $suspend_url   = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'suspend');
        $trash_url     = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'trash');
        $delete_url    = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'delete');

        $row_actions = [
            'edit' => '<a href="'.esc_attr($edit_url).'">'.__('Edit Subscr.', 'comment-mail').'</a>',

            'reconfirm' => '<a href="#"'.// Depends on `menu-pages.js`.
                           ' data-pmp-action="'.esc_attr($reconfirm_url).'"'.// The action URL.
                           ' data-pmp-confirmation="'.esc_attr(__('Resend email confirmation link? Are you sure?', 'comment-mail')).'">'.
                           '  '.__('Reconfirm', 'comment-mail').
                           '</a>',

            'confirm'   => '<a href="'.esc_attr($confirm_url).'">'.__('Subscribe', 'comment-mail').'</a>',
            'unconfirm' => '<a href="'.esc_attr($unconfirm_url).'">'.__('Unconfirm', 'comment-mail').'</a>',
            'suspend'   => '<a href="'.esc_attr($suspend_url).'">'.__('Suspend', 'comment-mail').'</a>',
            'trash'     => '<a href="'.esc_attr($trash_url).'" title="'.esc_attr(__('Trash', 'comment-mail')).'"><i class="fa fa-trash-o"></i></a>',

            'delete' => '<a href="#"'.// Depends on `menu-pages.js`.
                        ' data-pmp-action="'.esc_attr($delete_url).'"'.// The action URL.
                        ' data-pmp-confirmation="'.esc_attr(__('Delete permanently? Are you sure?', 'comment-mail')).'"'.
                        ' title="'.esc_attr(__('Delete', 'comment-mail')).'">'.
                        '  <i class="fa fa-times-circle"></i>'.
                        '</a>',
        ];
        if ($item->status === 'unconfirmed') {
            unset($row_actions['unconfirm'], $row_actions['suspend']);
        }
        if ($item->status === 'subscribed') {
            unset($row_actions['reconfirm'], $row_actions['confirm']);
        }
        if ($item->status === 'suspended') {
            unset($row_actions['suspend'], $row_actions['unconfirm']);
        }
        if ($item->status === 'trashed') {
            unset($row_actions['trash']);
        }
        if ($this->plugin->options['auto_confirm_force_enable']) {
            unset($row_actions['reconfirm']); // N/A.
        }
        return $email_info.$this->row_actions($row_actions);
    }

    /*
     * Public query-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Runs DB query; sets pagination args.
     *
     * @since 141111 First documented version.
     */
    public function prepare_items()
    { // @codingStandardsIgnoreEnd
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

        $and_or = $is_and_search_query ? 'AND' : 'OR';

        $sql = 'SELECT SQL_CALC_FOUND_ROWS *'.// w/ calc enabled.

               ($clean_search_query && $orderby === 'relevance' // Fulltext search?
                   ? ', MATCH(`'.implode('`,`', array_map('esc_sql', $this->getFtSearchableColumns())).'`)'.
                     "  AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE) AS `relevance`"
                   : '').// Otherwise, we can simply exclude this.

               ' FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               ' WHERE 1=1'.// Default where clause.

               ($sub_ids_in_search_query || $sub_emails_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
                   ? ' AND ('.$this->plugin->utils_string->trim(// Trim the following...
                       //
                       ($sub_ids_in_search_query ? ' '.$and_or." `ID` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')" : '').
                       ($sub_emails_in_search_query ? ' '.$and_or." `email` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."')" : '').
                       ($user_ids_in_search_query ? ' '.$and_or." `user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')" : '').
                       ($post_ids_in_search_query ? ' '.$and_or." `post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')" : '').
                       ($comment_ids_in_search_query ? ' '.$and_or." `comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')" : ''),
                       //
                       // Remaining arguments to trim function...
                       '',
                       'AND OR'
                   ).')' : '').// Trims `AND OR` leftover after concatenation occurs.

               ($statuses_in_search_query // Specific statuses?
                   ? " AND `status` IN('".implode("','", array_map('esc_sql', $statuses_in_search_query))."')"
                   : " AND `status` != '".esc_sql('trashed')."'").

               ($clean_search_query // A fulltext search?
                   ? ' AND (MATCH(`'.implode('`,`', array_map('esc_sql', $this->getFtSearchableColumns())).'`)'.
                     "     AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE)".
                     '     '.$this->prepareSearchableOrCols().')'
                   : '').// Otherwise, we can simply exclude this.

               ($orderby // Ordering by a specific column, or relevance?
                   ? ' ORDER BY `'.esc_sql($orderby).'`'.($order ? ' '.esc_sql($order) : '')
                   : '').// Otherwise, we can simply exclude this.

               ' LIMIT '.esc_sql($current_offset).','.esc_sql($per_page);

        // @codingStandardsIgnoreStart
        // PHPCS chokes on indentation here for some reason.
        if (($results = $this->plugin->utils_db->wp->get_results($sql))) {
            $this->setItems($results = $this->plugin->utils_db->typifyDeep($results));
            $this->setTotalItemsAvailable((int) $this->plugin->utils_db->wp->get_var('SELECT FOUND_ROWS()'));

            $this->prepareItemsMergeUserProperties(); // Merge additional properties.
            $this->prepareItemsMergePostProperties(); // Merge additional properties.
            $this->prepareItemsMergeCommentProperties(); // Merge additional properties.
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get default orderby value.
     *
     * @since 141111 First documented version.
     *
     * @return string The default orderby value.
     */
    protected function getDefaultOrderby()
    {
        return 'insertion_time'; // Default orderby.
    }

    /**
     * Get default order value.
     *
     * @since 141111 First documented version.
     *
     * @return string The default order value.
     */
    protected function getDefaultOrder()
    {
        return 'desc'; // Default order.
    }

    /*
     * Protected action-related methods.
     */

    // @codingStandardsIgnoreStart
    // camelCase not possible. This is an extender.
    /**
     * Bulk actions for this table.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of all bulk actions.
     */
    protected function get_bulk_actions()
    { // @codingStandardsIgnoreEnd
        return [
            'reconfirm' => __('Reconfirm', 'comment-mail'),
            'confirm'   => __('Confirm', 'comment-mail'),
            'unconfirm' => __('Unconfirm', 'comment-mail'),
            'suspend'   => __('Suspend', 'comment-mail'),
            'trash'     => __('Trash', 'comment-mail'),
            'delete'    => __('Delete', 'comment-mail'),
        ];
    }

    /**
     * Bulk action handler for this table.
     *
     * @since 141111 First documented version.
     *
     * @param string $bulk_action The bulk action to process.
     * @param array  $ids         The bulk action IDs to process.
     *
     * @return int Number of actions processed successfully.
     */
    protected function processBulkAction($bulk_action, array $ids)
    {
        switch ($bulk_action) {// Bulk action handler.

            case 'reconfirm': // Confirm via email?
                $counter = $this->plugin->utils_sub->bulkReconfirm($ids);
                break; // Break switch handler.

            case 'confirm': // Confirm silently?
                $counter = $this->plugin->utils_sub->bulkConfirm($ids);
                break; // Break switch handler.

            case 'unconfirm': // Unconfirm/unsubscribe?
                $counter = $this->plugin->utils_sub->bulkUnconfirm($ids);
                break; // Break switch handler.

            case 'suspend': // Suspend/unsubscribe?
                $counter = $this->plugin->utils_sub->bulkSuspend($ids);
                break; // Break switch handler.

            case 'trash': // Trashing/unsubscribe?
                $counter = $this->plugin->utils_sub->bulkTrash($ids);
                break; // Break switch handler.

            case 'delete': // Deleting/unsubscribe?
                $counter = $this->plugin->utils_sub->bulkDelete($ids);
                break; // Break switch handler.
        }
        return !empty($counter) ? (int) $counter : 0;
    }
}
