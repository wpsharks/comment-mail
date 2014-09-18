<?php
/**
 * Uninstaller
 *
 * @package wp_comment_subs\uninstall
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
namespace wp_comment_subs
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/plugin.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\uninstall'))
	{
		/**
		 * Uninstaller
		 *
		 * @package wp_comment_subs\uninstall
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
				$this->plugin = plugin( /* Without hooks. */);
				$this->plugin->setup( /* Without hooks. */);
				$this->plugin->uninstall();
			}
		}

		new uninstall(); // Run the uninstaller.
	}
}