<?php
/**
 * Sub Event Log Table
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_event_log_table'))
	{
		/**
		 * Sub Event Log Table
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_event_log_table extends abstract_table
		{
			/*
			 * Class constructor.
			 */

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$plugin = plugin(); // Needed below.

				$args = array(
					'singular_name'  => 'sub_event_log_entry',
					'plural_name'    => 'sub_event_log_entries',
					'singular_label' => __('sub. event log entry', $plugin->text_domain),
					'plural_label'   => __('sub. event log entries', $plugin->text_domain),
					'screen'         => $plugin->menu_page_hooks[__NAMESPACE__.'_sub_event_log'],
				);
				parent::__construct($args); // Parent constructor.
			}

			/*
			 * Public column-related methods.
			 */

			/**
			 * Table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all table columns.
			 */
			public static function get_columns_()
			{
				$plugin = plugin(); // Plugin class instance.

				return array(
					'cb'             => '1', // Include checkboxes.
					'ID'             => __('ID', $plugin->text_domain),
					'event'          => __('Event', $plugin->text_domain),
					'time'           => __('Time', $plugin->text_domain),
					'sub_id'         => __('Subscr. ID', $plugin->text_domain),
					'oby_sub_id'     => __('Overwritten By', $plugin->text_domain),
					'user_id'        => __('WP User ID', $plugin->text_domain),
					'post_id'        => __('Subscr. to Post ID', $plugin->text_domain),
					'comment_id'     => __('Subscr. to Comment ID', $plugin->text_domain),
					'deliver'        => __('Delivery', $plugin->text_domain),
					'fname'          => __('First Name', $plugin->text_domain),
					'lname'          => __('Last Name', $plugin->text_domain),
					'email'          => __('Email', $plugin->text_domain),
					'ip'             => __('IP Address', $plugin->text_domain),
					'status_before'  => __('Status Prior', $plugin->text_domain),
					'status'         => __('Status', $plugin->text_domain),
					'user_initiated' => __('User Initiated', $plugin->text_domain),
				);
			}

			/**
			 * Hidden table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all hidden table columns.
			 */
			public static function get_hidden_columns_()
			{
				return array(
					'oby_sub_id',
					'user_id',
					'deliver',
					'fname',
					'lname',
					'email',
					'ip',
					'status_before',
					'user_initiated',
				);
			}

			/**
			 * Searchable fulltext table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all fulltext searchables.
			 */
			public static function get_ft_searchable_columns_()
			{
				return array(
					'fname',
					'lname',
					'email',
					'ip',
				);
			}

			/**
			 * Searchable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all searchables.
			 */
			public static function get_searchable_columns_()
			{
				return array(
					'ID',
				);
			}

			/**
			 * Unsortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all unsortable table columns.
			 */
			public static function get_unsortable_columns_()
			{
				return array();
			}

			/*
			 * Public filter-related methods.
			 */

			/**
			 * Navigable table filters.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all navigable table filters.
			 */
			public static function get_navigable_filters_()
			{
				$plugin = plugin(); // Needed for translations.

				return array(
					'event::inserted'    => $plugin->utils_i18n->status_label('inserted'),
					'event::updated'     => $plugin->utils_i18n->status_label('updated'),
					'event::overwritten' => $plugin->utils_i18n->status_label('overwritten'),
					'event::purged'      => $plugin->utils_i18n->status_label('purged'),
					'event::cleaned'     => $plugin->utils_i18n->status_label('cleaned'),
					'event::deleted'     => $plugin->utils_i18n->status_label('deleted'),
				);
			}

			/*
			 * Protected column-related methods.
			 */

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_ID(\stdClass $item)
			{
				$id_info = '<i class="fa fa-history"></i>'. // Entry icon w/ ID.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->ID).'</span>';

				$delete_url = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'delete');

				$row_actions = array(
					'delete' => '<a href="#"'.  // Depends on `menu-pages.js`.
					            ' data-pmp-action="'.esc_attr($delete_url).'"'. // The action URL.
					            ' data-pmp-confirmation="'.esc_attr(__('Delete log entry? Are you sure?', $this->plugin->text_domain)).'"'.
					            ' title="'.esc_attr(__('Delete Sub. Event Log Entry', $this->plugin->text_domain)).'">'.
					            '  <i class="fa fa-times-circle"></i> '.__('Delete', $this->plugin->text_domain).
					            '</a>',
				);
				return $id_info.$this->row_actions($row_actions);
			}

			/*
			 * Public query-related methods.
			 */

			/**
			 * Runs DB query; sets pagination args.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function prepare_items() // The heart of this class.
			{
				$per_page                    = $this->get_per_page();
				$current_offset              = $this->get_current_offset();
				$clean_search_query          = $this->get_clean_search_query();
				$sub_ids_in_search_query     = $this->get_sub_ids_in_search_query();
				$user_ids_in_search_query    = $this->get_user_ids_in_search_query();
				$post_ids_in_search_query    = $this->get_post_ids_in_search_query();
				$comment_ids_in_search_query = $this->get_comment_ids_in_search_query();
				$statuses_in_search_query    = $this->get_statuses_in_search_query();
				$events_in_search_query      = $this->get_events_in_search_query();
				$is_and_search_query         = $this->is_and_search_query();
				$orderby                     = $this->get_orderby();
				$order                       = $this->get_order();

				$and_or = $is_and_search_query ? 'AND' : 'OR';

				$sql = "SELECT SQL_CALC_FOUND_ROWS *". // w/ calc enabled.

				       ($clean_search_query && $orderby === 'relevance' // Fulltext search?
					       ? ", MATCH(`".implode('`,`', array_map('esc_sql', $this->get_ft_searchable_columns()))."`)".
					         "  AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE) AS `relevance`"
					       : ''). // Otherwise, we can simply exclude this.

				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

				       " WHERE 1=1". // Default where clause.

				       ($sub_ids_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
					       ? " AND (".$this->plugin->utils_string->trim( // Trim the following...

						       ($sub_ids_in_search_query ? " ".$and_or." (`sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')".
						                                   " OR `oby_sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."'))" : '').
						       ($user_ids_in_search_query ? " ".$and_or." `user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')" : '').
						       ($post_ids_in_search_query ? " ".$and_or." `post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')" : '').
						       ($comment_ids_in_search_query ? " ".$and_or." `comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')" : '')

						       , '', 'AND OR').")" : ''). // Trims `AND OR` leftover after concatenation occurs.

				       ($statuses_in_search_query // Specific statuses?
					       ? " AND `status` IN('".implode("','", array_map('esc_sql', $statuses_in_search_query))."')" : '').

				       ($events_in_search_query // Specific events?
					       ? " AND `event` IN('".implode("','", array_map('esc_sql', $events_in_search_query))."')" : '').

				       ($clean_search_query // A fulltext search?
					       ? " AND (MATCH(`".implode('`,`', array_map('esc_sql', $this->get_ft_searchable_columns()))."`)".
					         "     AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE)".
					         "     ".$this->prepare_searchable_or_cols().")"
					       : ''). // Otherwise, we can simply exclude this.

				       ($orderby // Ordering by a specific column, or relevance?
					       ? " ORDER BY `".esc_sql($orderby)."`".($order ? " ".esc_sql($order) : '')
					       : ''). // Otherwise, we can simply exclude this.

				       " LIMIT ".esc_sql($current_offset).",".esc_sql($per_page);

				if(($results = $this->plugin->utils_db->wp->get_results($sql)))
				{
					$this->set_items($results = $this->plugin->utils_db->typify_deep($results));
					$this->set_total_items_available((integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()"));

					$this->prepare_items_merge_subscr_type_property(); // Merge property.
					$this->prepare_items_merge_sub_properties(); // Merge additional properties.
					$this->prepare_items_merge_user_properties(); // Merge additional properties.
					$this->prepare_items_merge_post_properties(); // Merge additional properties.
					$this->prepare_items_merge_comment_properties(); // Merge additional properties.
				}
			}

			/*
			 * Protected action-related methods.
			 */

			/**
			 * Bulk actions for this table.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all bulk actions.
			 */
			protected function get_bulk_actions()
			{
				return array(
					'delete' => __('Delete', $this->plugin->text_domain),
				);
			}

			/**
			 * Bulk action handler for this table.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $bulk_action The bulk action to process.
			 * @param array  $ids The bulk action IDs to process.
			 *
			 * @return integer Number of actions processed successfully.
			 */
			protected function process_bulk_action($bulk_action, array $ids)
			{
				switch($bulk_action) // Bulk action handler.
				{
					case 'delete': // Deleting log entries?
						$counter = $this->plugin->utils_sub_event_log->bulk_delete($ids);
						break; // Break switch handler.
				}
				return !empty($counter) ? (integer)$counter : 0;
			}
		}
	}
}