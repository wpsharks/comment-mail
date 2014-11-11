<?php
/**
 * Menu Page Subs. Table
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_subs_table'))
	{
		/**
		 * Menu Page Subs. Table
		 *
		 * @since 141111 First documented version.
		 */
		class menu_page_subs_table extends menu_page_table_base
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
					'singular_name'  => 'subscription',
					'plural_name'    => 'subscriptions',
					'singular_label' => __('subscription', $plugin->text_domain),
					'plural_label'   => __('subscriptions', $plugin->text_domain),
					'screen'         => $plugin->menu_page_hooks[__NAMESPACE__.'_subs'],
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

				return array(
					'cb'               => '1', // Include checkboxes.
					'email'            => __('Subscriber Email', $plugin->text_domain),
					'fname'            => __('First Name', $plugin->text_domain),
					'lname'            => __('Last Name', $plugin->text_domain),
					'user_id'          => __('WP User ID', $plugin->text_domain),
					'post_id'          => __('Subscr. to Post ID', $plugin->text_domain),
					'comment_id'       => __('Subscr. to Comment ID', $plugin->text_domain),
					'insertion_time'   => __('Subscr. Time', $plugin->text_domain),
					'insertion_ip'     => __('Subscr. IP', $plugin->text_domain),
					'sub_type'         => __('Subscr. Type', $plugin->text_domain),
					'deliver'          => __('Delivery', $plugin->text_domain),
					'last_ip'          => __('Last IP', $plugin->text_domain),
					'status'           => __('Status', $plugin->text_domain),
					'last_update_time' => __('Last Update', $plugin->text_domain),
					'key'              => __('Key', $plugin->text_domain),
					'ID'               => __('ID', $plugin->text_domain),
				);
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
				return array(
					'fname',
					'lname',
					'user_id',
					'comment_id',
					'insertion_ip',
					'last_ip',
					'last_update_time',
					'key',
					'ID',
				);
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
					'email',
					'fname',
					'lname',
					'insertion_ip',
					'last_ip',
					'key',
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
				return array(
					'sub_type',
				);
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
					'status::unconfirmed' => $plugin->utils_i18n->status_label('unconfirmed'),
					'status::subscribed'  => $plugin->utils_i18n->status_label('subscribed'),
					'status::suspended'   => $plugin->utils_i18n->status_label('suspended'),
					'status::trashed'     => $plugin->utils_i18n->status_label('trashed'),
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
			protected function column_email(\stdClass $item)
			{
				$name_email_args = array(
					'separator'   => '<br />',
					'anchor_to'   => 'search',
					'email_style' => 'font-weight:bold;',
				);
				$name            = $item->fname.' '.$item->lname; // Concatenate.
				$email_info      = '<i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i>'.
				                   ' <span style="font-weight:bold;" title="'.esc_attr($item->key).'">ID #'.esc_html($item->ID).'</span>'.
				                   ' '.$this->plugin->utils_markup->name_email($name, $item->email, $name_email_args);

				$edit_url      = $this->plugin->utils_url->edit_sub_short($item->ID);
				$reconfirm_url = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'reconfirm');
				$confirm_url   = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'confirm');
				$unconfirm_url = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'unconfirm');
				$suspend_url   = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'suspend');
				$trash_url     = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'trash');
				$delete_url    = $this->plugin->utils_url->table_bulk_action($this->plural_name, array($item->ID), 'delete');

				$row_actions = array(
					'edit'      => '<a href="'.esc_attr($edit_url).'">'.__('Edit Subscr.', $this->plugin->text_domain).'</a>',

					'reconfirm' => '<a href="#"'.  // Depends on `menu-pages.js`.
					               ' data-pmp-action="'.esc_attr($reconfirm_url).'"'. // The action URL.
					               ' data-pmp-confirmation="'.esc_attr(__('Resend email confirmation link? Are you sure?', $this->plugin->text_domain)).'">'.
					               '  '.__('Reconfirm', $this->plugin->text_domain).
					               '</a>',

					'confirm'   => '<a href="'.esc_attr($confirm_url).'">'.__('Confirm', $this->plugin->text_domain).'</a>',
					'unconfirm' => '<a href="'.esc_attr($unconfirm_url).'">'.__('Unconfirm', $this->plugin->text_domain).'</a>',
					'suspend'   => '<a href="'.esc_attr($suspend_url).'">'.__('Suspend', $this->plugin->text_domain).'</a>',
					'trash'     => '<a href="'.esc_attr($trash_url).'" title="'.esc_attr(__('Trash', $this->plugin->text_domain)).'"><i class="fa fa-trash-o"></i></a>',

					'delete'    => '<a href="#"'.  // Depends on `menu-pages.js`.
					               ' data-pmp-action="'.esc_attr($delete_url).'"'. // The action URL.
					               ' data-pmp-confirmation="'.esc_attr(__('Delete permanently? Are you sure?', $this->plugin->text_domain)).'"'.
					               ' title="'.esc_attr(__('Delete', $this->plugin->text_domain)).'">'.
					               '  <i class="fa fa-times-circle"></i>'.
					               '</a>',
				);
				if($item->status === 'unconfirmed') unset($row_actions['unconfirm'], $row_actions['suspend']);
				if($item->status === 'subscribed') unset($row_actions['reconfirm'], $row_actions['confirm']);
				if($item->status === 'suspended') unset($row_actions['suspend'], $row_actions['unconfirm']);
				if($item->status === 'trashed') unset($row_actions['trash']);

				if($this->plugin->options['auto_confirm_force_enable'])
					unset($row_actions['reconfirm']); // N/A.

				return $email_info.$this->row_actions($row_actions);
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

				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Default where clause.

				       ($sub_ids_in_search_query || $sub_emails_in_search_query || $user_ids_in_search_query || $post_ids_in_search_query || $comment_ids_in_search_query
					       ? " AND (".$this->plugin->utils_string->trim( // Trim the following...

						       ($sub_ids_in_search_query ? " ".$and_or." `ID` IN('".implode("','", array_map('esc_sql', $sub_ids_in_search_query))."')" : '').
						       ($sub_emails_in_search_query ? " ".$and_or." `email` IN('".implode("','", array_map('esc_sql', $sub_emails_in_search_query))."')" : '').
						       ($user_ids_in_search_query ? " ".$and_or." `user_id` IN('".implode("','", array_map('esc_sql', $user_ids_in_search_query))."')" : '').
						       ($post_ids_in_search_query ? " ".$and_or." `post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')" : '').
						       ($comment_ids_in_search_query ? " ".$and_or." `comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')" : '')

						       , '', 'AND OR').")" : ''). // Trims `AND OR` leftover after concatenation occurs.

				       ($statuses_in_search_query // Specific statuses?
					       ? " AND `status` IN('".implode("','", array_map('esc_sql', $statuses_in_search_query))."')"
					       : " AND `status` != '".esc_sql('trashed')."'").

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

					$this->prepare_items_merge_sub_type_property(); // Merge property.
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
				return 'email'; // Default orderby.
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
				return 'asc'; // Default order.
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
					'reconfirm' => __('Reconfirm', $this->plugin->text_domain),
					'confirm'   => __('Confirm', $this->plugin->text_domain),
					'unconfirm' => __('Unconfirm', $this->plugin->text_domain),
					'suspend'   => __('Suspend', $this->plugin->text_domain),
					'trash'     => __('Trash', $this->plugin->text_domain),
					'delete'    => __('Delete', $this->plugin->text_domain),
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
					case 'reconfirm': // Confirm via email?
						$counter = $this->plugin->utils_sub->bulk_reconfirm($ids);
						break; // Break switch handler.

					case 'confirm': // Confirm silently?
						$counter = $this->plugin->utils_sub->bulk_confirm($ids);
						break; // Break switch handler.

					case 'unconfirm': // Unconfirm/unsubscribe?
						$counter = $this->plugin->utils_sub->bulk_unconfirm($ids);
						break; // Break switch handler.

					case 'suspend': // Suspend/unsubscribe?
						$counter = $this->plugin->utils_sub->bulk_suspend($ids);
						break; // Break switch handler.

					case 'trash': // Trashing/unsubscribe?
						$counter = $this->plugin->utils_sub->bulk_trash($ids);
						break; // Break switch handler.

					case 'delete': // Deleting/unsubscribe?
						$counter = $this->plugin->utils_sub->bulk_delete($ids);
						break; // Break switch handler.
				}
				return !empty($counter) ? (integer)$counter : 0;
			}
		}
	}
}