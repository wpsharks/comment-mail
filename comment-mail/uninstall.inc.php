<?php
/**
 * Uninstaller
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class uninstall // Stand-alone class.
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				$GLOBALS[__NAMESPACE__] = new plugin(FALSE);
				$GLOBALS[__NAMESPACE__]->uninstall();
			}
		}
	}
	new uninstall(); // Run the uninstaller.
}