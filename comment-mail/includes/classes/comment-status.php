<?php
/**
 * Comment Status
 *
 * @package comment_status
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_status'))
	{
		/**
		 * Comment Status
		 *
		 * @package comment_status
		 * @since 14xxxx First documented version.
		 */
		class comment_status // Comment status.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var object|null Comment object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment; // Set by constructor.

			/**
			 * @var string New comment status; `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $new_comment_status; // Set by constructor.

			/**
			 * @var string Old comment status; `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $old_comment_status; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $new_comment_status New comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 *
			 * @param integer|string $old_comment_status Old comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 *
			 * @param object|null    $comment Comment object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($new_comment_status, $old_comment_status, $comment)
			{
				$this->plugin = plugin();

				$this->comment            = is_object($comment) ? $comment : NULL;
				$this->new_comment_status = $this->plugin->comment_status__($new_comment_status);
				$this->old_comment_status = $this->plugin->comment_status__($old_comment_status);

				if(!isset($this->comment)) return; // Nothing to do.
				// @TODO
			}
		}
	}
}