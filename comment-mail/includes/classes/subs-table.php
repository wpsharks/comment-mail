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

	if(!class_exists('\\WP_List_Table')) // WP core.
		require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\subs_table'))
	{
		/**
		 * Subscribers Table
		 *
		 * @since 14xxxx First documented version.
		 */
		class subs_table extends \WP_List_Table
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin;

			/**
			 * @var string Singular item name.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $singular;

			/**
			 * @var string Plural item name.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plural;

			/**
			 * @var string Regex for post IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_ids_regex;

			/**
			 * @var string Regex from comment IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_ids_regex;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				$this->singular          = 'subscriber';
				$this->plural            = 'subscribers';
				$this->items             = array(); // Initialize.
				$this->post_ids_regex    = '/(?:^|\W)post_ids\:(?P<post_ids>[0-9]+)/i';
				$this->comment_ids_regex = '/(?:^|\W)comment_ids\:(?P<comment_ids>[0-9]+)/i';

				$args = array(
					'singular' => $this->singular, 'plural' => $this->plural,
					'screen'   => $this->plugin->menu_page_hooks[__NAMESPACE__.'_subscribers'],
				);
				parent::__construct($args); // Parent constructor.

				add_filter('get_user_option_manage'.$this->screen->id.'columnshidden',
				           array($this, 'get_hidden_columns'));

				$this->maybe_process_bulk_action();
				$this->prepare_items();
				$this->display();
			}

			/**
			 * Table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all table columns.
			 */
			public function get_columns()
			{
				return array(
					'cb'               => '1', // Yes, include checkboxes.
					'ID'               => __('ID', $this->plugin->text_domain),
					'key'              => __('Key', $this->plugin->text_domain),
					'user_id'          => __('User ID', $this->plugin->text_domain),
					'post_id'          => __('Post ID', $this->plugin->text_domain),
					'comment_id'       => __('Comment ID', $this->plugin->text_domain),
					'deliver'          => __('Delivery Option', $this->plugin->text_domain),
					'fname'            => __('First Name', $this->plugin->text_domain),
					'lname'            => __('Last Name', $this->plugin->text_domain),
					'email'            => __('Email', $this->plugin->text_domain),
					'insertion_ip'     => __('Original IP', $this->plugin->text_domain),
					'last_ip'          => __('Last Known IP', $this->plugin->text_domain),
					'status'           => __('Status', $this->plugin->text_domain),
					'insertion_time'   => __('Subscription Time', $this->plugin->text_domain),
					'last_update_time' => __('Last Update Time', $this->plugin->text_domain),
				);
			}

			/**
			 * Table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $value Current user-specific value.
			 *
			 * @return array An array of all table columns.
			 */
			public function get_hidden_columns($value)
			{
				return !is_array($value)
					? array(
						'ID',
						'key',
						'user_id',
						# 'post_id',
						# 'comment_id',
						# 'deliver',
						'fname',
						'lname',
						# 'email',
						# 'insertion_ip',
						'last_ip',
						# 'status',
						# 'insertion_time',
						'last_update_time',
					)
					: $value;
			}

			/**
			 * Sortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all sortable table columns.
			 */
			public function get_sortable_columns()
			{
				foreach(array_keys($this->get_columns()) as $_column)
					if($_column !== 'cb') // Checkbox col. not sortable.
						$sortable[$_column] = array($_column, FALSE);
				unset($_column); // Housekeeping.

				return !empty($sortable) ? $sortable : array();
			}

			/**
			 * Fulltext searchable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all fulltext searchables.
			 */
			public function get_ft_searchable_columns()
			{
				return array(
					# 'ID',
					'key',
					# 'user_id',
					# 'post_id',
					# 'comment_id',
					# 'deliver',
					'fname',
					'lname',
					'email',
					'insertion_ip',
					'last_ip',
					# 'status',
					# 'insertion_time',
					# 'last_update_time',
				);
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
			protected function column_cb(\stdClass $item)
			{
				return '<input type="checkbox" name="'.esc_attr($this->plural).'[]" value="'.esc_attr($item->ID).'" />';
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
			protected function column_email(\stdClass $item)
			{
				$name       = trim($item->fname.' '.$item->lname);
				$name_email = esc_html('"'.str_replace('"', '', $name).'" <'.$item->email.'>');

				$action_url = $this->plugin->utils_url->current_page_nonce_only(); // Base URL to build actions from.
				$edit_url   = add_query_arg(urlencode_deep(array('action' => 'edit', $this->singular => $item->ID)), $action_url);
				$delete_url = add_query_arg(urlencode_deep(array('action' => 'delete', $this->singular => $item->ID)), $action_url);
				$actions    = array(
					'edit'   => '<a href="'.esc_attr($edit_url).'">'.__('Edit', $this->plugin->text_domain).'</a>',
					'delete' => '<a href="'.esc_attr($delete_url).'">'.__('Delete', $this->plugin->text_domain).'</a>',
				);
				return $name_email.$this->row_actions($actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 * @param string    $property Column we need to build markup for.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_default(\stdClass $item, $property)
			{
				return isset($item->{$property}) ? esc_html($item->{$property}) : '—';
			}

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
					'confirm_via_email' => __('Resend Email Confirmation', $this->plugin->text_domain),
					'confirm'           => __('Confirm Silently', $this->plugin->text_domain),
					'unconfirm'         => __('Unconfirm', $this->plugin->text_domain),
					'suspend'           => __('Suspend', $this->plugin->text_domain),
					'delete'            => __('Delete', $this->plugin->text_domain),
				);
			}

			/**
			 * Bulk action handler for this table.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_process_bulk_action()
			{
				if(!($bulk_action = stripslashes((string)$this->current_action())))
					return; // Nothing to do; no action requested here.

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				if(empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce']))
					return; // Unauthenticated; ignore.

				if(empty($_REQUEST[$this->plural]) || !is_array($_REQUEST[$this->plural]))
					return; // Nothing to do; i.e. no boxes were checked in this case.

				if(!($ids = array_map('intval', $_REQUEST[$this->plural])))
					return; // Nothing to do; i.e. we have no IDs.

				switch($bulk_action) // Bulk action handler.
				{
					case 'confirm_via_email': // Confirm via email?
						$this->plugin->utils_sub->bulk_confirm_via_email($ids);
						break; // Break switch handler.

					case 'confirm': // Confirm silently?
						$this->plugin->utils_sub->bulk_confirm($ids);
						break; // Break switch handler.

					case 'unconfirm': // Unconfirm/unsubscribe?
						$this->plugin->utils_sub->bulk_unconfirm($ids);
						break; // Break switch handler.

					case 'suspend': // Suspend/unsubscribe?
						$this->plugin->utils_sub->bulk_suspend($ids);
						break; // Break switch handler.

					case 'delete': // Deleting/unsubscribe?
						$this->plugin->utils_sub->bulk_delete($ids);
						break; // Break switch handler.
				}
			}

			/**
			 * Get raw search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Raw search query; w/ search tokens.
			 */
			protected function get_raw_search_query()
			{
				return !empty($_REQUEST['s'])
					? trim(stripslashes((string)$_REQUEST['s']))
					: ''; // Not searching.
			}

			/**
			 * Clean search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Clean search query; minus search tokens.
			 */
			protected function get_clean_search_query()
			{
				$s = $this->get_raw_search_query();

				$s = $s ? preg_replace($this->post_ids_regex, '', $s) : '';
				$s = $s ? preg_replace($this->comment_ids_regex, '', $s) : '';
				$s = $s ? trim(preg_replace('/\s+/', ' ', $s)) : '';

				return $s; // Search search query.
			}

			/**
			 * Get post IDs in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Post IDs in the search query.
			 */
			protected function get_post_ids_in_search_query()
			{
				$s = $this->get_raw_search_query();

				if($s && preg_match_all($this->post_ids_regex, $s, $_m))
					$post_ids = array_map('intval', $_m['post_ids']);
				unset($_m); // Housekeeping.

				return !empty($post_ids) ? $post_ids : array();
			}

			/**
			 * Get comment IDs in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Comment IDs in the search query.
			 */
			protected function get_comment_ids_in_search_query()
			{
				$s = $this->get_raw_search_query();

				if($s && preg_match_all($this->comment_ids_regex, $s, $_m))
					$comment_ids = array_map('intval', $_m['comment_ids']);
				unset($_m); // Housekeeping.

				return !empty($comment_ids) ? $comment_ids : array();
			}

			/**
			 * Get orderby value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The orderby value.
			 */
			protected function get_orderby()
			{
				$orderby = !empty($_REQUEST['orderby'])
					? strtolower(trim(stripslashes((string)$_REQUEST['orderby'])))
					: ''; // Not specified explicitly by site owner.

				$clean_search_query = $this->get_clean_search_query();

				if(!$orderby || !in_array($orderby, array_keys($this->get_columns()), TRUE))
					$orderby = $clean_search_query ? 'relevance' : '';

				if($clean_search_query && !empty($_POST))
					$_POST['orderby'] = $_GET['orderby'] = $_REQUEST['orderby']
						= $orderby = 'relevance'; // Force by relevance.

				return $orderby; // Current orderby.
			}

			/**
			 * Get order value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The order value.
			 */
			protected function get_order()
			{
				$order = !empty($_REQUEST['order'])
					? strtoupper(trim(stripslashes((string)$_REQUEST['order'])))
					: ''; // Not specified explicitly by site owner.

				$clean_search_query = $this->get_clean_search_query();

				if(!$order || !in_array($order, array('ASC', 'DESC'), TRUE))
					$order = $clean_search_query ? 'DESC' : '';

				if($clean_search_query && !empty($_POST))
					$_POST['order'] = $_GET['order'] = $_REQUEST['order']
						= $order = 'DESC'; // Force by relevance.

				return $order; // Current orderby.
			}

			/**
			 * Runs DB query; sets pagination args.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function prepare_items()
			{
				$max_limit       = $this->plugin->utils_user->screen_option($this->screen, 'per_page');
				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);

				$max_limit = $max_limit < 1 ? 1 : $max_limit;
				$max_limit = $max_limit > $upper_max_limit ? 100 : $max_limit;

				$per_page       = $max_limit;
				$current_page   = $this->get_pagenum();
				$current_offset = ($current_page - 1) * $per_page;
				$total_items    = $total_pages = 0; // Initialize.

				$clean_search_query          = $this->get_clean_search_query();
				$post_ids_in_search_query    = $this->get_post_ids_in_search_query();
				$comment_ids_in_search_query = $this->get_comment_ids_in_search_query();
				$orderby                     = $this->get_orderby();
				$order                       = $this->get_order();

				$sql = "SELECT SQL_CALC_FOUND_ROWS *". // w/ calc enabled.

				       ($clean_search_query && $orderby === 'relevance' // Fulltext search?
					       ? ", MATCH(`".implode('`,`', array_map('esc_sql', $this->get_ft_searchable_columns()))."`)".
					         "  AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE) AS `relevance`"
					       : ''). // Otherwise, we can simply exclude this.

				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Default where clause.

				       ($post_ids_in_search_query // Within certain post IDs?
					       ? " AND `post_id` IN('".implode("','", array_map('esc_sql', $post_ids_in_search_query))."')"
					       : ''). // Otherwise, we can simply exclude this.

				       ($comment_ids_in_search_query // Within certain comment IDs?
					       ? " AND `post_id` IN('".implode("','", array_map('esc_sql', $comment_ids_in_search_query))."')"
					       : ''). // Otherwise, we can simply exclude this.

				       ($clean_search_query // A fulltext search?
					       ? " AND MATCH(`".implode('`,`', array_map('esc_sql', $this->get_ft_searchable_columns()))."`)".
					         "     AGAINST('".esc_sql($clean_search_query)."' IN BOOLEAN MODE)"
					       : ''). // Otherwise, we can simply exclude this.

				       ($orderby // Ordering by a specific column, or relevance?
					       ? " ORDER BY `".esc_sql($orderby)."`".($order ? " ".esc_sql($order) : '')
					       : ''). // Otherwise, we can simply exclude this.

				       " LIMIT ".esc_sql($current_offset).",".esc_sql($per_page);

				if(is_array($results = $this->plugin->utils_db->wp->get_results($sql)))
				{
					$this->items = $this->plugin->utils_db->typify_deep($results);
					$total_items = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()");
					$total_pages = ceil($total_items / $per_page); // Based on total items available.
				}
				$this->set_pagination_args(compact('per_page', 'total_items', 'total_pages'));
			}

			/**
			 * Display the table.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function display()
			{
				$this->search_box(__('Search', $this->plugin->text_domain), __CLASS__);

				parent::display(); // Call parent handler now.
			}
		}
	}
}