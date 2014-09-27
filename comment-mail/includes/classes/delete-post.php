<?php
/**
 * Delete Post
 *
 * @package delete_post
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\delete_post'))
	{
		/**
		 * Delete Post
		 *
		 * @package delete_post
		 * @since 14xxxx First documented version.
		 */
		class delete_post // Post deletion handler.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var integer Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_id; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $post_id Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($post_id)
			{
				$this->plugin = plugin();

				$this->post_id = (integer)$post_id;

				if(!$this->post_id) return; // Nothing to do.

				// @TODO
			}
		}
	}
}