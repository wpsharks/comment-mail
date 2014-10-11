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

	if(!class_exists('\\WP_List_Table')) // WP core.
		require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\queue_table'))
	{
		/**
		 * Queue Table
		 *
		 * @since 14xxxx First documented version.
		 */
		class queue_table extends \WP_List_Table
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
				$this->singular = 'queued notification';
				$this->plural   = 'queued notifications';

				$args = array(
					'singular' => $this->singular, 'plural' => $this->plural,
					'screen'   => $this->plugin->menu_page_hooks[__NAMESPACE__.'_queue'],
				);
				parent::__construct($args);

				$this->maybe_process_bulk_action();
				$this->prepare_items();
				$this->display();
			}
		}
	}
}