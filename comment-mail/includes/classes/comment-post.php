<?php
/**
 * Comment Post
 *
 * @package comment_post
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_post'))
	{
		/**
		 * Comment Post
		 *
		 * @package comment_post
		 * @since 14xxxx First documented version.
		 */
		class comment_post // Comment post.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var integer Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_id = 0;

			/**
			 * @var integer|string Comment approval status.
			 *    One of the following: `0`, `1`, or `spam`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $approval_status = 0;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $comment_id Comment ID.
			 * @param integer|string $approval_status `0`, `1`, or `spam`.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($comment_id, $approval_status)
			{
				$this->plugin = plugin();

				$this->comment_id = (integer)$comment_id;

				if($approval_status !== 'spam')
					$approval_status = (integer)$approval_status;
				$this->approval_status = $approval_status;
				// @TODO
			}
		}
	}
}