<?php
/**
 * Subscribers Table
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\subs_table'))
	{
		/**
		 * Subscribers Table
		 *
		 * @since 14xxxx First documented version.
		 */
		class subs_table extends abstract_table
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
					'singular_name'  => 'subscriber',
					'plural_name'    => 'subscribers',
					'singular_label' => __('subscriber', $plugin->text_domain),
					'plural_label'   => __('subscribers', $plugin->text_domain),
					'screen'         => $plugin->menu_page_hooks[__NAMESPACE__.'_subscribers'],
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
					'cb'               => '1', // Yes, include checkboxes.
					'email'            => __('Subscriber Email', $plugin->text_domain),
					'fname'            => __('First Name', $plugin->text_domain),
					'lname'            => __('Last Name', $plugin->text_domain),
					'user_id'          => __('User ID', $plugin->text_domain),
					'post_id'          => __('Subscr. to Post ID', $plugin->text_domain),
					'comment_id'       => __('Subscr. to Comment ID', $plugin->text_domain),
					'insertion_time'   => __('Subscr. Time', $plugin->text_domain),
					'insertion_ip'     => __('Subscr. IP', $plugin->text_domain),
					'subscr_type'      => __('Subscr. Type', $plugin->text_domain),
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
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all hidden table columns.
			 */
			public static function get_hidden_columns_()
			{
				return array(
					'fname',
					'lname',
					'user_id',
					'insertion_ip',
					'last_ip',
					'last_update_time',
					'key',
					'ID',
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
					'email',
					'fname',
					'lname',
					'insertion_ip',
					'last_ip',
					'key',
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
				return array(
					'subscr_type',
				);
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
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_email(\stdClass $item)
			{
				$name       = $item->fname.' '.$item->lname; // Concatenate.
				$email_info = '<i class="fa fa-user"></i>'. // e.g. ♙ ID "Name" <email>; w/ key in hover title.
				              ' <span style="font-weight:bold;" title="'.esc_attr($item->key).'">ID #'.esc_html($item->ID).'</span>'.
				              ' '.$this->plugin->utils_markup->name_email($name, $item->email, '<br />', FALSE, TRUE, '', 'font-weight:bold;');

				$edit_url      = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'edit'); // @TODO
				$reconfirm_url = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'reconfirm');
				$confirm_url   = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'confirm');
				$unconfirm_url = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'unconfirm');
				$suspend_url   = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'suspend');
				$trash_url     = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'trash');
				$delete_url    = $this->plugin->utils_url->bulk_action($this->plural_name, array($item->ID), 'delete');

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

				return $email_info.$this->row_actions($row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_post_id(\stdClass $item)
			{
				if(!$item->post_id || !$item->post_type)
					return '—'; // Not applicable.

				if(!($post_type = get_post_type_object($item->post_type)))
					return '—'; // Not applicable.

				$post_type_label        = $post_type->labels->singular_name;
				$post_title_clip        = $this->plugin->utils_string->mid_clip($item->post_title);
				$post_date              = $this->plugin->utils_date->i18n('M j, Y', strtotime($item->post_date_gmt));
				$post_date_ago          = $this->plugin->utils_date->approx_time_difference(strtotime($item->post_date_gmt));
				$post_comments_status   = $this->plugin->utils_i18n->status_label($this->plugin->utils_db->post_comment_status__($item->post_comment_status));
				$post_edit_comments_url = $this->plugin->utils_url->post_edit_comments_short($item->post_id);
				$post_total_subscribers = $this->plugin->utils_sub->query_total('', $item->post_id);
				$post_total_comments    = (integer)$item->post_comment_count; // Total comments.

				$post_info = $this->plugin->utils_markup->subscriber_count($item->post_id, $post_total_subscribers, 'float:right; margin-left:5px;').
				             $this->plugin->utils_markup->comment_count($item->post_id, $post_total_comments, 'float:right; margin-left:5px;').
				             '<span style="font-weight:bold;">'.esc_html($post_type_label).' ID #'.esc_html($item->post_id).'</span>'.
				             ' <span style="font-style:italic;">('.__('comments', $this->plugin->text_domain).' '.esc_html($post_comments_status).')</span><br />'.
				             '<span title="'.esc_attr($post_date).'">“'.esc_html($post_title_clip).'”</span>';

				$post_view_url    = $this->plugin->utils_url->post_short($item->post_id);
				$post_edit_url    = $this->plugin->utils_url->post_edit_short($item->post_id);
				$post_row_actions = array(
					'edit' => '<a href="'.esc_attr($post_edit_url).'">'.sprintf(__('Edit %1$s', $this->plugin->text_domain), esc_html($post_type_label)).'</a>',
					'view' => '<a href="'.esc_attr($post_view_url).'">'.sprintf(__('View', $this->plugin->text_domain), esc_html($post_type_label)).'</a>',
				);
				return $post_info.$this->row_actions($post_row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_comment_id(\stdClass $item)
			{
				if(!$item->post_id || !$item->comment_id)
					return '— all —'; // All of them.

				$comment_date_time = $this->plugin->utils_date->i18n('M j, Y, g:i a', strtotime($item->comment_date_gmt));
				$comment_time_ago  = $this->plugin->utils_date->approx_time_difference(strtotime($item->comment_date_gmt));
				$comment_status    = $this->plugin->utils_i18n->status_label($this->plugin->utils_db->comment_status__($item->comment_approved));

				$comment_info = '<span style="font-weight:bold;">'.esc_html(__('Comment', $this->plugin->text_domain)).' ID #'.esc_html($item->comment_id).'</span>'.
				                ' <span style="font-style:italic;">('.esc_html($comment_status).')</span><br />'.
				                '<span style="font-style:italic;">'.__('by:', $this->plugin->text_domain).'</span>'.
				                ' '.$this->plugin->utils_markup->name_email($item->comment_author, $item->comment_author_email);

				$comment_view_url    = $this->plugin->utils_url->comment_short($item->comment_id);
				$comment_edit_url    = $this->plugin->utils_url->comment_edit_short($item->comment_id);
				$comment_row_actions = array(
					'edit' => '<a href="'.esc_attr($comment_edit_url).'">'.__('Edit Comment', $this->plugin->text_domain).'</a>',
					'view' => '<a href="'.esc_attr($comment_view_url).'">'.__('View', $this->plugin->text_domain).'</a>',
				);
				return $comment_info.$this->row_actions($comment_row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_subscr_type(\stdClass $item)
			{
				return esc_html($this->plugin->utils_i18n->subscr_type_label($item->subscr_type));
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
				$post_ids_in_search_query    = $this->get_post_ids_in_search_query();
				$comment_ids_in_search_query = $this->get_comment_ids_in_search_query();
				$statuses_in_search_query    = $this->get_statuses_in_search_query();
				$orderby                     = $this->get_orderby();
				$order                       = $this->get_order();

				$subs_table     = $this->plugin->utils_db->prefix().'subs';
				$posts_table    = $this->plugin->utils_db->wp->posts;
				$comments_table = $this->plugin->utils_db->wp->comments;

				$sql = "SELECT SQL_CALC_FOUND_ROWS *". // w/ calc enabled.

				       ($clean_search_query && $orderby === 'relevance' // Fulltext search?
					       ? ", MATCH(`".implode('`,`', array_map('esc_sql', $this->get_searchable_columns()))."`)".
					         "  AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE) AS `relevance`"
					       : ''). // Otherwise, we can simply exclude this.

				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Default where clause.

				       ($post_ids_in_search_query // Within certain post IDs?
					       ? " AND (`post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')".
					         ($comment_ids_in_search_query ? // If we have comment IDs too, let's do an `OR` search.
						         " OR `comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')"
						         : '').")" // Always close the bracket.

					       : ($comment_ids_in_search_query // Within certain comment IDs?
						       ? " AND `comment_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')"
						       : '')). // Otherwise, we can simply exclude this.

				       ($statuses_in_search_query // Specific statuses?
					       ? " AND `status` IN('".implode("','", array_map('esc_sql', $statuses_in_search_query))."')"
					       : " AND `status` != '".esc_sql('trashed')."'").

				       ($clean_search_query // A fulltext search?
					       ? " AND MATCH(`".implode('`,`', array_map('esc_sql', $this->get_searchable_columns()))."`)".
					         "     AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE)"
					       : ''). // Otherwise, we can simply exclude this.

				       ($orderby // Ordering by a specific column, or relevance?
					       ? " ORDER BY `".esc_sql($orderby)."`".($order ? " ".esc_sql($order) : '')
					       : ''). // Otherwise, we can simply exclude this.

				       " LIMIT ".esc_sql($current_offset).",".esc_sql($per_page);

				if(is_array($results = $this->plugin->utils_db->wp->get_results($sql)))
				{
					$this->set_items($results = $this->plugin->utils_db->typify_deep($results));
					$this->set_total_items_available((integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()"));

					$this->prepare_items_merge_subscr_properties(); // Merge additional properties.
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