<?php
/**
 * Queue Inserter
 *
 * @package queue_inserter
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\queue_inserter'))
	{
		/**
		 * Queue Inserter
		 *
		 * @package queue_inserter
		 * @since 14xxxx First documented version.
		 */
		class queue_inserter // Queue inserter.
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
			 * Class constructor.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($comment_id)
			{
				$this->plugin = plugin();

				$this->comment_id = (integer)$comment_id;
				// @TODO
			}
		}
	}
}