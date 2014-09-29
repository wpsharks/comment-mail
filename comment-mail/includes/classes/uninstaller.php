<?php
/**
 * Uninstall Routines
 *
 * @package uninstaller
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\uninstaller'))
	{
		/**
		 * Uninstall Routines
		 *
		 * @package uninstaller
		 * @since 14xxxx First documented version.
		 */
		class uninstaller
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				if($this->plugin->enable_hooks)
					return; // Not a good idea.

				$this->plugin->setup(); // Setup.

				if(!defined('WP_UNINSTALL_PLUGIN'))
					return; // Disallow.

				if(empty($GLOBALS[__NAMESPACE__.'_uninstalling']))
					return; // Expecting uninstall file.

				if(!$this->plugin->options['uninstall_on_deletion'])
					return; // Nothing to do here.

				if(!current_user_can($this->plugin->uninstall_cap))
					return; // Extra layer of security.

				$this->delete_options();
				$this->clear_cron_hooks();
				$this->drop_db_tables();
			}

			/**
			 * Delete plugin-related options.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function delete_options()
			{
				delete_option(__NAMESPACE__.'_options');
				delete_option(__NAMESPACE__.'_notices');
				delete_option(__NAMESPACE__.'_errors');
			}

			/**
			 * Clear scheduled CRON hooks.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function clear_cron_hooks()
			{
				wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_process_queue');
			}

			/**
			 * Uninstall DB tables.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function drop_db_tables()
			{
				foreach(scandir($tables_dir = dirname(__FILE__).'/tables') as $_sql_file)
					if(substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file))
					{
						$_sql_file_table = substr($_sql_file, 0, -4);
						$_sql_file_table = str_replace('-', '_', $_sql_file_table);
						$_sql_file_table = $this->plugin->utils_db->prefix().$_sql_file_table;

						if(!$this->plugin->utils_db->wp->query('DROP TABLE IF EXISTS `'.esc_sql($_sql_file_table).'`'))
							throw new \exception(sprintf(__('DB table deletion failure: `%1$s`.', $this->plugin->text_domain), $_sql_file_table));
					}
				unset($_sql_file, $_sql_file_table); // Housekeeping.
			}
		}
	}
}