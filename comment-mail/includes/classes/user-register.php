<?php
/**
 * User Register
 *
 * @package user_register
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\user_register'))
	{
		/**
		 * User Register
		 *
		 * @package user_register
		 * @since 14xxxx First documented version.
		 */
		class user_register // User registration handler.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var integer User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_id; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $user_id User ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($user_id)
			{
				$this->plugin = plugin();

				$this->user_id = (integer)$user_id;

				if(!$this->user_id) return; // Nothing to do.

				// @TODO
			}
		}
	}
}