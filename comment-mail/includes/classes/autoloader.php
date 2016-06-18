<?php
/**
 * Autoloader
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/abs-base.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\autoloader'))
	{
		/**
		 * Autoloader
		 *
		 * @since 141111 First documented version.
		 */
		class autoloader extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->register();
			}

			/**
			 * Handles autoloading for the plugin's namespace.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string `namespace\class` to load up.
			 */
			public function autoload($ns_class)
			{
				if(stripos($ns_class, __NAMESPACE__.'\\') !== 0)
					return; // Not part of this plugin.

				$class_path = trim(stristr($ns_class, '\\'), '\\');
				$class_path = strtolower(str_replace('_', '-', $class_path));
				$class_file = dirname(__FILE__).'/'.$class_path.'.php';

				if(is_file($class_file)) require_once $class_file;
			}

			/**
			 * Registers autoloader.
			 *
			 * @since 141111 First documented version.
			 */
			protected function register()
			{
				if(($registered = &$this->static_key(__FUNCTION__)))
					return; // Already registered autoloader.

				spl_autoload_register(array($this, 'autoload'));

				$registered = TRUE; // Flag as complete.
			}
		}
	}
}
