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
			protected $comment_id; // Set by constructor.

			/**
			 * @var string Status; `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $status; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @param integer|string $status Initial status.
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($comment_id, $status)
			{
				$this->plugin = plugin();

				$this->comment_id = (integer)$comment_id;
				$this->status     = $this->plugin->comment_status__($status);

				if(!$this->comment_id) return; // Nothing to do.
				// @TODO
			}
		}
	}
}