<?php
/**
 * Post Status
 *
 * @package post_status
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\post_status'))
	{
		/**
		 * Post Status
		 *
		 * @package post_status
		 * @since 14xxxx First documented version.
		 */
		class post_status // Post status.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var \WP_Post|null Post object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post; // Set by constructor.

			/**
			 * @var string New post status.
			 *
			 *    One of the following statuses:
			 *    See: <http://codex.wordpress.org/Function_Reference/get_post_status>
			 *
			 *       - `publish`
			 *       - `pending`
			 *       - `draft`
			 *       - `auto-draft`
			 *       - `future`
			 *       - `private`
			 *       - `inherit`
			 *       - `trash`
			 *
			 *    See also: {@link get_available_post_statuses()}
			 *       Custom post types may have their own statuses.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $new_post_status; // Set by constructor.

			/**
			 * @var string Old post status.
			 *
			 *    One of the following statuses:
			 *    See: <http://codex.wordpress.org/Function_Reference/get_post_status>
			 *
			 *       - `new`
			 *       - `publish`
			 *       - `pending`
			 *       - `draft`
			 *       - `auto-draft`
			 *       - `future`
			 *       - `private`
			 *       - `inherit`
			 *       - `trash`
			 *
			 *    See also: {@link get_available_post_statuses()}
			 *       Custom post types may have their own statuses.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $old_post_status; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param string        $new_post_status New post status.
			 *
			 *    One of the following statuses:
			 *    See: <http://codex.wordpress.org/Function_Reference/get_post_status>
			 *
			 *       - `publish`
			 *       - `pending`
			 *       - `draft`
			 *       - `auto-draft`
			 *       - `future`
			 *       - `private`
			 *       - `inherit`
			 *       - `trash`
			 *
			 *    See also: {@link get_available_post_statuses()}
			 *       Custom post types may have their own statuses.
			 *
			 * @param string        $old_post_status Old comment status.
			 *
			 *    One of the following statuses:
			 *    See: <http://codex.wordpress.org/Function_Reference/get_post_status>
			 *
			 *       - `new`
			 *       - `publish`
			 *       - `pending`
			 *       - `draft`
			 *       - `auto-draft`
			 *       - `future`
			 *       - `private`
			 *       - `inherit`
			 *       - `trash`
			 *
			 *    See also: {@link get_available_post_statuses()}
			 *       Custom post types may have their own statuses.
			 *
			 * @param \WP_Post|null $post Post object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($new_post_status, $old_post_status, $post)
			{
				$this->plugin = plugin();

				$this->post            = is_object($post) ? $post : NULL;
				$this->new_post_status = (string)$new_post_status;
				$this->old_post_status = (string)$old_post_status;

				if(!isset($this->post)) return; // Nothing to do.

				if($new_post_status === 'publish' && $old_post_status !== 'publish')
					if($old_post_status !== 'trash') // Ignore restorations.
						new auto_sub_inserter($post->ID);
			}
		}
	}
}