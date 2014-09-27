<?php
/**
 * Autoloader
 *
 * @package autoloader
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\autoloader'))
	{
		/**
		 * Autoloader
		 *
		 * @package autoloader
		 * @since 14xxxx First documented version.
		 */
		class autoloader // Queue processor.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var boolean Registered already?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $registered = FALSE;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
				$this->register();
			}

			/**
			 * Handles autoloading for the plugin's namespace.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string `namespace\class` to load up.
			 */
			public function autoload($ns_class)
			{
				if(stripos($ns_class, __NAMESPACE__.'\\') !== 0)
					return; // Not part of this plugin.

				$class_path = trim(strstr($ns_class, '\\'), '\\');
				$class_path = str_replace('_', '-', $class_path);
				$class_file = dirname(__FILE__).'/'.$class_path.'.php';

				if(is_file($class_file)) require_once $class_file;
			}

			/**
			 * Registers autoloader.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function register()
			{
				if(static::$registered)
					return; // Already done.

				spl_autoload_register(array($this, 'autoload'));
				static::$registered = TRUE;
			}
		}
	}
}