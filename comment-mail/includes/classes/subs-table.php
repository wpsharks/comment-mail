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
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin   = plugin();
				$this->singular = 'subscriber';
				$this->plural   = 'subscribers';

				$args = array(
					'singular' => $this->singular,
					'plural'   => $this->plural,
				);
				parent::__construct($args);

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
					'cb'    => TRUE,
					'email' => __('Email', $this->plugin->text_domain),
					'key'   => __('Key', $this->plugin->text_domain),
					'ID'    => __('ID', $this->plugin->text_domain)
				);
			}

			/**
			 * Sortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all sortable table columns.
			 */
			protected function get_sortable_columns()
			{
				return array(
					'email' => array('email', FALSE),
					'key'   => array('key', FALSE),
					'ID'    => array('ID', FALSE)
				);
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
			 */
			protected function maybe_process_bulk_action()
			{
				if(!($bulk_action = stripslashes((string)$this->current_action())))
					return; // Nothing to do; no action requested here.

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				if(empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce']))
					return; // Unauthenticated; ignore.

				switch($bulk_action) // Based on requested action.
				{
					case 'confirm': // Confirming?

						break; // Break switch handler.

					case 'unconfirm': // Unconfirming?

						break; // Break switch handler.

					case 'suspend': // Suspending?

						break; // Break switch handler.

					case 'delete': // Deleting?

						break; // Break switch handler.
				}
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
				return '<input type="checkbox" name="'.esc_attr($this->singular).'[]" value="'.esc_attr($item->ID).'" />';
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
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_key(\stdClass $item)
			{
				return esc_html($item->key);
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
			protected function column_ID(\stdClass $item)
			{
				return esc_html($item->ID);
			}

			/** ************************************************************************
			 * REQUIRED! This is where you prepare your data for display. This method will
			 * usually be used to query the database, sort and filter the data, and generally
			 * get it ready to be displayed. At a minimum, we should set $this->items and
			 * $this->set_pagination_args(), although the following properties and methods
			 * are frequently interacted with here...
			 *
			 * @global WPDB $wpdb
			 * @uses $this->_column_headers
			 * @uses $this->items
			 * @uses $this->get_columns()
			 * @uses $this->get_sortable_columns()
			 * @uses $this->get_pagenum()
			 * @uses $this->set_pagination_args()
			 **************************************************************************/
			public function prepare_items()
			{
				$per_page = 5;

				/**
				 * REQUIRED. Now we need to define our column headers. This includes a complete
				 * array of columns to be displayed (slugs & titles), a list of columns
				 * to keep hidden, and a list of columns that are sortable. Each of these
				 * can be defined in another method (as we've done here) before being
				 * used to build the value for our _column_headers property.
				 */
				$columns  = $this->get_columns();
				$hidden   = array();
				$sortable = $this->get_sortable_columns();

				/**
				 * REQUIRED. Finally, we build an array to be used by the class for column
				 * headers. The $this->_column_headers property takes an array which contains
				 * 3 other arrays. One for all columns, one for hidden columns, and one
				 * for sortable columns.
				 */
				$this->_column_headers = array($columns, $hidden, $sortable);

				/***********************************************************************
				 * ---------------------------------------------------------------------
				 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
				 *
				 * In a real-world situation, this is where you would place your query.
				 *
				 * For information on making queries in WordPress, see this Codex entry:
				 * http://codex.wordpress.org/Class_Reference/wpdb
				 *
				 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
				 * ---------------------------------------------------------------------
				 **********************************************************************/

				/**
				 * REQUIRED for pagination. Let's figure out what page the user is currently
				 * looking at. We'll need this later, so you should always include it in
				 * your own package classes.
				 */
				$current_page = $this->get_pagenum();

				/**
				 * REQUIRED for pagination. Let's check how many items are in our data array.
				 * In real-world use, this would be the total number of items in your database,
				 * without filtering. We'll need this later, so you should always include it
				 * in your own package classes.
				 */
				$total_items = count($data);

				/**
				 * The WP_List_Table class does not handle pagination for us, so we need
				 * to ensure that the data is trimmed to only the current page. We can use
				 * array_slice() to
				 */
				$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

				/**
				 * REQUIRED. Now we can add our *sorted* data to the items property, where
				 * it can be used by the rest of the class.
				 */
				$this->items = $data;

				/**
				 * REQUIRED. We also have to register our pagination options & calculations.
				 */
				$this->set_pagination_args(array(
					                           'total_items' => $total_items,                  //WE have to calculate the total number of items
					                           'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
					                           'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
				                           ));
			}
		}
	}
}