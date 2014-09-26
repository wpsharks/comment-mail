<?php
/**
 * Uninstaller
 *
 * @package uninstall
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	$GLOBALS[__NAMESPACE__.'_uninstalling']    = TRUE;
	$GLOBALS[__NAMESPACE__.'_autoload_plugin'] = FALSE;

	require_once dirname(__FILE__).'/plugin.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\uninstall'))
	{
		/**
		 * Uninstaller
		 *
		 * @package uninstall
		 * @since 14xxxx First documented version.
		 */
		class uninstall // Uninstall handler.
		{
			/**
			 * @since 14xxxx First documented version.
			 *
			 * @var plugin Primary plugin class instance.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Uninstall constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$GLOBALS[__NAMESPACE__] // Without hooks.
					= $this->plugin = new plugin(FALSE);

				$this->plugin->uninstall();
			}
		}
	}
	new uninstall(); // Run the uninstaller.
}