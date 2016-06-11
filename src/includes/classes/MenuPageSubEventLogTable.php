<?php
/**
 * Menu Page Sub. Event Log Table.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Menu Page Sub. Event Log Table.
 *
 * @since 141111 First documented version.
 */
class MenuPageSubEventLogTable extends MenuPageTableBase
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
            'singular_name'  => 'sub_event_log_entry',
            'plural_name'    => 'sub_event_log_entries',
            'singular_label' => __('sub. event log entry', 'comment-mail'),
            'plural_label'   => __('sub. event log entries', 'comment-mail'),
            'screen'         => $plugin->menu_page_hooks[GLOBAL_NS.'_sub_event_log'],
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

            'time'   => __('Time', 'comment-mail'),
            'sub_id' => __('Subscr. ID', 'comment-mail'),

            'event'          => __('Event', 'comment-mail'),
            'oby_sub_id'     => __('Overwritten By', 'comment-mail'),
            'user_initiated' => __('User Initiated', 'comment-mail'),

            'key_before' => __('Subscr. Key Before', 'comment-mail'),
            'key'        => __('Subscr. Key After', 'comment-mail'),

            'user_id_before' => __('WP User ID Before', 'comment-mail'),
            'user_id'        => __('WP User ID After', 'comment-mail'),

            'post_id_before' => __('Post ID Before', 'comment-mail'),
            'post_id'        => __('Post ID After', 'comment-mail'),

            'comment_id_before' => __('Comment ID Before', 'comment-mail'),
            'comment_id'        => __('Comment ID After', 'comment-mail'),

            'status_before' => __('Status Before', 'comment-mail'),
            'status'        => __('Status After', 'comment-mail'),

            'deliver_before' => __('Delivery Before', 'comment-mail'),
            'deliver'        => __('Delivery After', 'comment-mail'),

            'fname_before' => __('First Name Before', 'comment-mail'),
            'fname'        => __('First Name After', 'comment-mail'),

            'lname_before' => __('Last Name Before', 'comment-mail'),
            'lname'        => __('Last Name After', 'comment-mail'),

            'email_before' => __('Email Before', 'comment-mail'),
            'email'        => __('Email After', 'comment-mail'),

            'ip_before' => __('IP Address Before', 'comment-mail'),
            'ip'        => __('IP Address After', 'comment-mail'),

            'region_before' => __('IP Region Before', 'comment-mail'),
            'region'        => __('IP Region After', 'comment-mail'),

            'country_before' => __('IP Country Before', 'comment-mail'),
            'country'        => __('IP Country After', 'comment-mail'),
        ];
        if (!$plugin->options['geo_location_tracking_enable']) {
            foreach ($columns as $_key => $_column) {
                if (in_array($_key, ['region_before', 'region', 'country_before', 'country'], true)) {
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
            'oby_sub_id',
            'user_initiated',

            'key_before',
            'key',

            'user_id_before',
            'user_id',

            'post_id_before',
            'post_id',

            'comment_id_before',
            'comment_id',

            'deliver_before',
            'deliver',

            'fname_before',
            'fname',

            'lname_before',
            'lname',

            'email_before',
            'email',

            'ip_before',
            'ip',

            'region_before',
            'region',

            'country_before',
            'country',
        ];
        if (!$plugin->options['geo_location_tracking_enable']) {
            foreach ($columns as $_key => $_column) {
                if (in_array($_column, ['region_before', 'region', 'country_before', 'country'], true)) {
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
            'key',
            'fname',
            'lname',
            'email',
            'ip',

            'key_before',
            'fname_before',
            'lname_before',
            'email_before',
            'ip_before',
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
            'event::inserted'    => $plugin->utils_i18n->eventLabel('inserted', 'ucwords'),
            'event::updated'     => $plugin->utils_i18n->eventLabel('updated', 'ucwords'),
            'event::overwritten' => $plugin->utils_i18n->eventLabel('overwritten', 'ucwords'),
            'event::purged'      => $plugin->utils_i18n->eventLabel('purged', 'ucwords'),
            'event::cleaned'     => $plugin->utils_i18n->eventLabel('cleaned', 'ucwords'),
            'event::deleted'     => $plugin->utils_i18n->eventLabel('deleted', 'ucwords'),
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
        $id_info = '<i class="fa fa-clock-o"></i>'.// Entry icon w/ ID.
                   ' <span style="font-weight:bold;">#'.esc_html($item->ID).'</span>';

        $delete_url = $this->plugin->utils_url->tableBulkAction($this->plural_name, [$item->ID], 'delete');

        $row_actions = [
            'delete' => '<a href="#"'.// Depends on `menu-pages.js`.
                        ' data-pmp-action="'.esc_attr($delete_url).'"'.// The action URL.
                        ' data-pmp-confirmation="'.esc_attr($this->plugin->utils_i18n->logEntryJsDeletionConfirmationWarning()).'"'.
                        ' title="'.esc_attr(__('Delete Sub. Event Log Entry', 'comment-mail')).'">'.
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

            case 'inserted': // Subscription was inserted in this case.

                return esc_html($event_label).' '.$this->plugin->utils_event->subInsertedQLink($item);

            case 'updated': // Subscription was updated in this case.

                return esc_html($event_label).' '.$this->plugin->utils_event->subUpdatedQLink($item).'<br />'.
                       '<i class="pmp-child-branch"></i> '.$this->plugin->utils_event->subUpdatedSummary($item);

            case 'overwritten': // Overwritten by another?

                if ($item->oby_sub_id && !empty($this->merged_result_sets['subs'][$item->oby_sub_id])) {
                    $edit_url     = $this->plugin->utils_url->editSubShort($item->oby_sub_id);
                    $oby_sub_info = '<i class="'.esc_attr('si si-'.SLUG_TD.'-one').'"></i>'.
                                    ' <span>ID <a href="'.esc_attr($edit_url).'" title="'.esc_attr($item->oby_sub_key).'">#'.esc_html($item->oby_sub_id).'</a></span>';
                } else {
                    $oby_sub_info = '<i class="'.esc_attr('si si-'.SLUG_TD.'-one').'"></i>'.
                                    ' <span>ID #'.esc_html($item->oby_sub_id).'</span>';
                }
                return esc_html($event_label).' '.$this->plugin->utils_event->subOverwrittenQLink($item).'<br />'.
                       '<i class="pmp-child-branch"></i> '.__('by', 'comment-mail').' '.$oby_sub_info;

            case 'purged': // Subscription was purged in this case.

                return esc_html($event_label).' '.$this->plugin->utils_event->subPurgedQLink($item);

            case 'cleaned': // Subscription was cleaned in this case.

                return esc_html($event_label).' '.$this->plugin->utils_event->subCleanedQLink($item);

            case 'deleted': // Subscription was deleted in this case.

                return esc_html($event_label).' '.$this->plugin->utils_event->subDeletedQLink($item);
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
    public function prepare_items() // The heart of this class.
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

               ' FROM `'.esc_sql($this->plugin->utils_db->prefix().'sub_event_log').'`'.

               ' WHERE 1=1'.// Default where clause.

               ($sub_ids_in_search_query || $sub_emails_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
                   ? ' AND ('.$this->plugin->utils_string->trim(// Trim the following...
                       //
                       ($sub_ids_in_search_query // Search both fields here.
                           ? ' '.$and_or." (`sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')".
                             "               OR `oby_sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."'))" : '').
                       //
                       ($sub_emails_in_search_query // Search both fields here.
                           ? ' '.$and_or." (`email` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."')".
                             "               OR `email_before` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."'))" : '').
                       //
                       ($user_ids_in_search_query // Search both fields here.
                           ? ' '.$and_or." (`user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')".
                             "              OR `user_id_before` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."'))" : '').
                       //
                       ($post_ids_in_search_query // Search both fields here.
                           ? ' '.$and_or." (`post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')".
                             "              OR `post_id_before` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."'))" : '').
                       //
                       ($comment_ids_in_search_query // Search both fields here.
                           ? ' '.$and_or." (`comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')".
                             "              OR `comment_id_before` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."'))" : ''),
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
                $counter = $this->plugin->utils_sub_event_log->bulkDelete($ids);
                break; // Break switch handler.
        }
        return !empty($counter) ? (integer) $counter : 0;
    }
}
