<?php
/**
 * Menu Page Queue Event Log Table.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Menu Page Queue Event Log Table.
 *
 * @since 141111 First documented version.
 */
class MenuPageQueueEventLogTable extends MenuPageTableBase
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
            'singular_name'  => 'queue_event_log_entry',
            'plural_name'    => 'queue_event_log_entries',
            'singular_label' => __('queue event log entry', 'comment-mail'),
            'plural_label'   => __('queue event log entries', 'comment-mail'),
            'screen'         => $plugin->menu_page_hooks[GLOBAL_NS.'_queue_event_log'],
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
            'ID' => __('Entry', 'comment-mail'),

            'time'  => __('Time', 'comment-mail'),
            'event' => __('Event', 'comment-mail'),

            'queue_id'     => __('Queue ID', 'comment-mail'),
            'dby_queue_id' => __('Digested by Queue ID', 'comment-mail'),

            'sub_id' => __('Subscr. ID', 'comment-mail'),

            'user_id'           => __('WP User ID', 'comment-mail'),
            'post_id'           => __('Subscr. to Post ID', 'comment-mail'),
            'comment_parent_id' => __('Subscr. to Comment ID', 'comment-mail'),
            'comment_id'        => __('Regarding Comment ID', 'comment-mail'),

            'fname' => __('Subscr. First Name', 'comment-mail'),
            'lname' => __('Subscr. Last Name', 'comment-mail'),
            'email' => __('Subscr. Email', 'comment-mail'),

            'ip'      => __('Subscr. IP', 'comment-mail'),
            'region'  => __('Subscr. IP Region', 'comment-mail'),
            'country' => __('Subscr. IP Country', 'comment-mail'),

            'status' => __('Subscr. Status', 'comment-mail'),
        ];
        if (!$plugin->options['geo_location_tracking_enable']) {
            foreach ($columns as $_key => $_column) {
                if (in_array($_key, ['region', 'country'], true)) {
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
            'queue_id',
            'dby_queue_id',

            'user_id',

            'comment_parent_id',
            'comment_id',

            'fname',
            'lname',
            'email',

            'ip',
            'region',
            'country',

            'status',
        ];
        if (!$plugin->options['geo_location_tracking_enable']) {
            foreach ($columns as $_key => $_column) {
                if (in_array($_column, ['region', 'country'], true)) {
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
            'fname',
            'lname',
            'email',

            'ip',
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
            'event::invalidated' => $plugin->utils_i18n->eventLabel('invalidated', 'ucwords'),
            'event::notified'    => $plugin->utils_i18n->eventLabel('notified', 'ucwords'),
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
     * @param \stdClass $item Item object; i.e. a row from the DB.
     *
     * @return string HTML markup for this table column.
     */
    protected function column_ID(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        $id_info = '<i class="fa fa-paper-plane"></i>'.// Entry icon w/ ID.
                   ' <span style="font-weight:bold;">#'.esc_html($item->ID).'</span>';

        $delete_url = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'delete');

        $row_actions = [
            'delete' => '<a href="#"'.// Depends on `menu-pages.js`.
                        ' data-pmp-action="'.esc_attr($delete_url).'"'.// The action URL.
                        ' data-pmp-confirmation="'.esc_attr($this->plugin->utils_i18n->logEntryJsDeletionConfirmationWarning()).'"'.
                        ' title="'.esc_attr(__('Delete Queue Event Log Entry', 'comment-mail')).'">'.
                        '  <i class="fa fa-times-circle"></i> '.__('Delete', 'comment-mail').
                        '</a>',
        ];
        return $id_info.$this->row_actions($row_actions);
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
    protected function column_event(\stdClass $item)
    { // @codingStandardsIgnoreEnd
        $event_label = $this->plugin->utils_i18n->eventLabel($item->event);

        switch ($item->event) {// Based on the type of event that took place.

            case 'notified': // Queue entry was notified in this case.

                $name_email_args = [
                    'anchor_to'   => 'search',
                    'email_style' => 'font-weight:normal;',
                ];
                return esc_html($event_label).' '.$this->plugin->utils_event->queueNotifiedQLink($item).'<br />'.
                       $this->plugin->utils_markup->nameEmail('', $item->email, $name_email_args);

            case 'invalidated': // Queue entry was invalidated in this case.

                return esc_html($event_label).' '.$this->plugin->utils_event->queueInvalidatedQLink($item).'<br />'.
                       '<code style="font-size:90%;">'.esc_html($item->note_code).'</code>';
        }
        return esc_html($event_label); // Default case handler.
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

               ' FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.

               ' WHERE 1=1'.// Default where clause.

               ($sub_ids_in_search_query || $sub_emails_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
                   ? ' AND ('.$this->plugin->utils_string->trim(// Trim the following...
                       //
                       ($sub_ids_in_search_query ? ' '.$and_or." `sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')" : '').
                       ($sub_emails_in_search_query ? ' '.$and_or." `email` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."')" : '').
                       ($user_ids_in_search_query ? ' '.$and_or." `user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')" : '').
                       ($post_ids_in_search_query ? ' '.$and_or." `post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')" : '').
                       //
                       ($comment_ids_in_search_query // Search both fields here.
                           ? ' '.$and_or." (`comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')".
                             "              OR `comment_parent_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."'))" : ''),
                       //
                       // Remaining arguments to trim function...
                       '',
                       'AND OR'
                   ).')' : '').// Trims `AND OR` leftover after concatenation occurs.

               ($statuses_in_search_query // Specific statuses?
                   ? " AND `status` IN('".implode("','", array_map('esc_sql', $statuses_in_search_query))."')" : '').

               ($events_in_search_query // Specific events?
                   ? " AND `event` IN('".implode("','", array_map('esc_sql', $events_in_search_query))."')" : '').

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
            $this->setTotalItemsAvailable((integer) $this->plugin->utils_db->wp->get_var('SELECT FOUND_ROWS()'));

            $this->prepareItemsMergeSubProperties(); // Merge additional properties.
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
        return 'time'; // Default orderby.
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
            'delete' => __('Delete', 'comment-mail'),
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

            case 'delete': // Deleting log entries?
                $counter = $this->plugin->utils_queue_event_log->bulkDelete($ids);
                break; // Break switch handler.
        }
        return !empty($counter) ? (integer) $counter : 0;
    }
}
