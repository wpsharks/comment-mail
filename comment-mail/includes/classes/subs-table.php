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

					case 'delete': // Deleting/unsubscribe?
						$counter = $this->plugin->utils_sub->bulk_delete($ids);
						break; // Break switch handler.
				}
				return !empty($counter) ? (integer)$counter : 0;
			}
		}
	}
}