<?php
/**
 * Installation
 *
 * @since 14xxxx First documented version.
 * @package comment_mail\installation
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/plugin.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\installation'))
	{
		/**
		 * Installation
		 *
		 * @since 14xxxx First documented version.
		 * @package comment_mail\installation
		 */
		class installation
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
			}
		}
	}
}