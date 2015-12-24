<?php
/**
 * Menu Page Sub. Event Log Table
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_sub_event_log_table'))
	{
		/**
		 * Menu Page Sub. Event Log Table
		 *
		 * @since 141111 First documented version.
		 */
		class menu_page_sub_event_log_table extends menu_page_table_base
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

				$args = array(
					'singular_name'  => 'sub_event_log_entry',
					'plural_name'    => 'sub_event_log_entries',
					'singular_label' => __('sub. event log entry', 'comment-mail'),
					'plural_label'   => __('sub. event log entries', 'comment-mail'),
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
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all table columns.
			 */
			public static function get_columns_()
			{
				$plugin = plugin(); // Plugin class instance.

				$columns = array(
					'cb'                => '1', // Include checkboxes.
					'ID'                => __('Entry', 'comment-mail'),

					'time'              => __('Time', 'comment-mail'),
					'sub_id'            => __('Subscr. ID', 'comment-mail'),

					'event'             => __('Event', 'comment-mail'),
					'oby_sub_id'        => __('Overwritten By', 'comment-mail'),
					'user_initiated'    => __('User Initiated', 'comment-mail'),

					'key_before'        => __('Subscr. Key Before', 'comment-mail'),
					'key'               => __('Subscr. Key After', 'comment-mail'),

					'user_id_before'    => __('WP User ID Before', 'comment-mail'),
					'user_id'           => __('WP User ID After', 'comment-mail'),

					'post_id_before'    => __('Post ID Before', 'comment-mail'),
					'post_id'           => __('Post ID After', 'comment-mail'),

					'comment_id_before' => __('Comment ID Before', 'comment-mail'),
					'comment_id'        => __('Comment ID After', 'comment-mail'),

					'status_before'     => __('Status Before', 'comment-mail'),
					'status'            => __('Status After', 'comment-mail'),

					'deliver_before'    => __('Delivery Before', 'comment-mail'),
					'deliver'           => __('Delivery After', 'comment-mail'),

					'fname_before'      => __('First Name Before', 'comment-mail'),
					'fname'             => __('First Name After', 'comment-mail'),

					'lname_before'      => __('Last Name Before', 'comment-mail'),
					'lname'             => __('Last Name After', 'comment-mail'),

					'email_before'      => __('Email Before', 'comment-mail'),
					'email'             => __('Email After', 'comment-mail'),

					'ip_before'         => __('IP Address Before', 'comment-mail'),
					'ip'                => __('IP Address After', 'comment-mail'),
				);
				return $columns; // Associative array.
			}

			/**
			 * Hidden table columns.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all hidden table columns.
			 */
			public static function get_hidden_columns_()
			{
				$plugin = plugin(); // Plugin class instance.

				$columns = array(
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
				);
				return array_values($columns);
			}

			/**
			 * Searchable fulltext table columns.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all fulltext searchables.
			 */
			public static function get_ft_searchable_columns_()
			{
				return array(
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
				);
			}

			/**
			 * Searchable table columns.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all navigable table filters.
			 */
			public static function get_navigable_filters_()
			{
				$plugin = plugin(); // Needed for translations.

				return array(
					'event::inserted'    => $plugin->utils_i18n->event_label('inserted', 'ucwords'),
					'event::updated'     => $plugin->utils_i18n->event_label('updated', 'ucwords'),
					'event::overwritten' => $plugin->utils_i18n->event_label('overwritten', 'ucwords'),
					'event::purged'      => $plugin->utils_i18n->event_label('purged', 'ucwords'),
					'event::cleaned'     => $plugin->utils_i18n->event_label('cleaned', 'ucwords'),
					'event::deleted'     => $plugin->utils_i18n->event_label('deleted', 'ucwords'),

				);
			}

			/*
			 * Protected column-related methods.
			 */

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
			{
				$id_info = '<i class="fa fa-clock-o"></i>'. // Entry icon w/ ID.
				           ' <span style="font-weight:bold;">#'.esc_html($item->ID).'</span>';

				$delete_url = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'delete');

				$row_actions = array(
					'delete' => '<a href="#"'.  // Depends on `menu-pages.js`.
					            ' data-pmp-action="'.esc_attr($delete_url).'"'. // The action URL.
					            ' data-pmp-confirmation="'.esc_attr($this->plugin->utils_i18n->log_entry_js_deletion_confirmation_warning()).'"'.
					            ' title="'.esc_attr(__('Delete Sub. Event Log Entry', $this->plugin->text_domain)).'">'.
					            '  <i class="fa fa-times-circle"></i> '.__('Delete', $this->plugin->text_domain).
					            '</a>',
				);
				return $id_info.$this->row_actions($row_actions);
			}

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
			{
				$event_label = $this->plugin->utils_i18n->event_label($item->event);

				switch($item->event) // Based on the type of event that took place.
				{
					case 'inserted': // Subscription was inserted in this case.

						return esc_html($event_label).' '.$this->plugin->utils_event->sub_inserted_q_link($item);

					case 'updated': // Subscription was updated in this case.

						return esc_html($event_label).' '.$this->plugin->utils_event->sub_updated_q_link($item).'<br />'.
						       '<i class="pmp-child-branch"></i> '.$this->plugin->utils_event->sub_updated_summary($item);

					case 'overwritten': // Overwritten by another?

						if($item->oby_sub_id && !empty($this->merged_result_sets['subs'][$item->oby_sub_id]))
						{
							$edit_url     = $this->plugin->utils_url->edit_sub_short($item->oby_sub_id);
							$oby_sub_info = '<i class="'.esc_attr('si si-'.$this->plugin->slug.'-one').'"></i>'.
							                ' <span>ID <a href="'.esc_attr($edit_url).'" title="'.esc_attr($item->oby_sub_key).'">#'.esc_html($item->oby_sub_id).'</a></span>';
						}
						else $oby_sub_info = '<i class="'.esc_attr('si si-'.$this->plugin->slug.'-one').'"></i>'.
						                     ' <span>ID #'.esc_html($item->oby_sub_id).'</span>';

						return esc_html($event_label).' '.$this->plugin->utils_event->sub_overwritten_q_link($item).'<br />'.
						       '<i class="pmp-child-branch"></i> '.__('by', $this->plugin->text_domain).' '.$oby_sub_info;

					case 'purged': // Subscription was purged in this case.

						return esc_html($event_label).' '.$this->plugin->utils_event->sub_purged_q_link($item);

					case 'cleaned': // Subscription was cleaned in this case.

						return esc_html($event_label).' '.$this->plugin->utils_event->sub_cleaned_q_link($item);

					case 'deleted': // Subscription was deleted in this case.

						return esc_html($event_label).' '.$this->plugin->utils_event->sub_deleted_q_link($item);
				}
				return esc_html($event_label); // Default case handler.
			}

			/*
			 * Public query-related methods.
			 */

			/**
			 * Runs DB query; sets pagination args.
			 *
			 * @since 141111 First documented version.
			 */
			public function prepare_items() // The heart of this class.
			{
				$per_page                    = $this->get_per_page();
				$current_offset              = $this->get_current_offset();
				$clean_search_query          = $this->get_clean_search_query();
				$sub_ids_in_search_query     = $this->get_sub_ids_in_search_query();
				$sub_emails_in_search_query  = $this->get_sub_emails_in_search_query();
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

				       ($sub_ids_in_search_query || $sub_emails_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
					       ? " AND (".$this->plugin->utils_string->trim( // Trim the following...

						       ($sub_ids_in_search_query // Search both fields here.
							       ? " ".$and_or." (`sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')".
							         "               OR `oby_sub_id` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."'))" : '').

						       ($sub_emails_in_search_query // Search both fields here.
							       ? " ".$and_or." (`email` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."')".
							         "               OR `email_before` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."'))" : '').

						       ($user_ids_in_search_query // Search both fields here.
							       ? " ".$and_or." (`user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')".
							         "              OR `user_id_before` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."'))" : '').

						       ($post_ids_in_search_query // Search both fields here.
							       ? " ".$and_or." (`post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')".
							         "              OR `post_id_before` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."'))" : '').

						       ($comment_ids_in_search_query // Search both fields here.
							       ? " ".$and_or." (`comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')".
							         "              OR `comment_id_before` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."'))" : '')

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

					$this->prepare_items_merge_sub_properties(); // Merge additional properties.
					$this->prepare_items_merge_user_properties(); // Merge additional properties.
					$this->prepare_items_merge_post_properties(); // Merge additional properties.
					$this->prepare_items_merge_comment_properties(); // Merge additional properties.
				}
			}

			/**
			 * Get default orderby value.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string The default orderby value.
			 */
			protected function get_default_orderby()
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
			protected function get_default_order()
			{
				return 'desc'; // Default order.
			}

			/*
			 * Protected action-related methods.
			 */

			/**
			 * Bulk actions for this table.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
