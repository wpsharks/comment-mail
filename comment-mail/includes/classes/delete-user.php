<?php
/**
 * Delete User
 *
 * @package delete_user
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\delete_user'))
	{
		/**
		 * Delete User
		 *
		 * @package delete_user
		 * @since 14xxxx First documented version.
		 */
		class delete_user // User deletion handler.
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
			 * @var integer Blog ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $blog_id; // Set by constructor.

			/**
			 * @var boolean Switched blog?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $switched_blog = FALSE;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $user_id User ID.
			 * @param integer|string $blog_id Blog ID. Defaults to `0` (current blog).
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($user_id, $blog_id = 0)
			{
				$this->plugin = plugin();

				$this->user_id = (integer)$user_id;
				$this->blog_id = (integer)$blog_id;

				if(!$this->user_id) return; // Nothing to do.

				if($this->blog_id && $this->blog_id !== $GLOBALS['blog_id'])
				{
					switch_to_blog($this->blog_id);
					$this->switched_blog = TRUE;
				}
				// @TODO

				if($this->blog_id && $this->switched_blog)
				{
					restore_current_blog();
					$this->switched_blog = FALSE;
				}
			}
		}
	}
}