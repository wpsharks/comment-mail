<?php
/**
 * Install Routines
 *
 * @package installer
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\installer'))
	{
		/**
		 * Install Routines
		 *
		 * @package installer
		 * @since 14xxxx First documented version.
		 */
		class installer
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

				$this->plugin->setup(); // Setup.

				$this->create_db_tables();
			}

			/**
			 * Create DB tables.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function create_db_tables()
			{
				foreach(scandir($tables_dir = dirname(__FILE__).'/tables') as $_sql_file)
					if(substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file))
					{
						$_sql_file_table = substr($_sql_file, 0, -4);
						$_sql_file_table = str_replace('-', '_', $_sql_file_table);
						$_sql_file_table = $this->plugin->db_prefix().$_sql_file_table;

						$_sql = file_get_contents($tables_dir.'/'.$_sql_file);
						$_sql = str_replace('%%prefix%%', $this->plugin->db_prefix(), $_sql);

						$this->plugin->wpdb->query($_sql); // Create.
					}
				unset($_sql_file, $_sql_file_table, $_sql); // Housekeeping.
			}
		}
	}
}