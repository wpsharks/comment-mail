<?php
/**
 * Activation/Installation
 *
 * @package comment_mail\activation
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/plugin.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\activation'))
	{
		/**
		 * Activation/Installation
		 *
		 * @since 14xxxx First documented version.
		 * @package comment_mail\activation
		 */
		class activation
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
				$this->install_db_tables();
			}

			/**
			 * Install DB tables.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function install_db_tables()
			{
				foreach(scandir($tables_dir = dirname(__FILE__).'/tables') as $_sql_file)
					if(substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file))
					{
						$_sql = file_get_contents($tables_dir.'/'.$_sql_file);
						$this->plugin->wpdb()->query($_sql);
					}
				unset($_sql_file, $_sql); // Housekeeping.
			}
		}
	}
}