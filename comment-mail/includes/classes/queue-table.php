<?php
/**
 * Queue Table
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\queue_table'))
	{
		/**
		 * Queue Table
		 *
		 * @since 14xxxx First documented version.
		 */
		class queue_table extends abstract_table
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
					'singular_name'  => 'queued_notification',
					'plural_name'    => 'queued_notifications',
					'singular_label' => __('queued notification', $plugin->text_domain),
					'plural_label'   => __('queued notifications', $plugin->text_domain),
					'screen'         => $plugin->menu_page_hooks[__NAMESPACE__.'_queue'],
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
					'cb'                => '1', // Include checkboxes.
					'ID'                => __('ID', $plugin->text_domain),
					'sub_id'            => __('Subscr. ID', $plugin->text_domain),
					'user_id'           => __('Subscr. User ID', $plugin->text_domain),
					'post_id'           => __('Subscr. to Post ID', $plugin->text_domain),
					'comment_parent_id' => __('Subscr. to Comment ID', $plugin->text_domain),
					'comment_id'        => __('Regarding Comment ID', $plugin->text_domain),
					'insertion_time'    => __('Queued Time', $plugin->text_domain),
					'last_update_time'  => __('Last Update', $plugin->text_domain),
					'hold_until_time'   => __('Holding Until', $plugin->text_domain),
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
					'user_id',
					'comment_parent_id',
					'insertion_time',
					'last_update_time',
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
				return array();
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
				return array();
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
				$id_info = '<i class="fa fa-envelope-o"></i>'. // Notification icon w/ ID.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->ID).'</span>';

				$delete_url = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'delete');

				$row_actions = array(
					'delete' => '<a href="#"'.  // Depends on `menu-pages.js`.
					            ' data-pmp-action="'.esc_attr($delete_url).'"'. // The action URL.
					            ' data-pmp-confirmation="'.esc_attr(__('Delete queued notification? Are you sure?', $this->plugin->text_domain)).'"'.
					            ' title="'.esc_attr(__('Delete Queued Notification', $this->plugin->text_domain)).'">'.
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

				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".

				       " WHERE 1=1". // Default where clause.

				       ($sub_ids_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
					       ? " AND (".$this->plugin->utils_string->trim( // Trim the following...

						       ($sub_ids_in_search_query ? " ".$and_or." `sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')" : '').
						       ($user_ids_in_search_query ? " ".$and_or." `user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')" : '').
						       ($post_ids_in_search_query ? " ".$and_or." `post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')" : '').
						       ($comment_ids_in_search_query ? " ".$and_or." (`comment_parent_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')".
						                                       " OR `comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."'))" : '')

						       , '', 'AND OR').")" : ''). // Trims `AND OR` leftover after concatenation occurs.

				       ($clean_search_query // A search query?
					       ? " AND (".$this->prepare_searchable_or_cols().")"
					       : ''). // Otherwise, we can simply exclude this.

				       ($orderby // Ordering by a specific column, or relevance?
					       ? " ORDER BY `".esc_sql($orderby)."`".($order ? " ".esc_sql($order) : '')
					       : ''). // Otherwise, we can simply exclude this.

				       " LIMIT ".esc_sql($current_offset).",".esc_sql($per_page);

				if(($results = $this->plugin->utils_db->wp->get_results($sql)))
				{
					$this->set_items($results = $this->plugin->utils_db->typify_deep($results));
					$this->set_total_items_available((integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()"));

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
					case 'delete': // Deleting queued notifications?
						$counter = $this->plugin->utils_queue->bulk_delete($ids);
						break; // Break switch handler.
				}
				return !empty($counter) ? (integer)$counter : 0;
			}
		}
	}
}