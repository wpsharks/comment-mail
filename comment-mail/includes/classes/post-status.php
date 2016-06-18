<?php
/**
 * Post Status Change Handler
 *
 * @since 141111 First documented version.
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
		 * Post Status Change Handler
		 *
		 * @since 141111 First documented version.
		 */
		class post_status extends abs_base
		{
			/**
			 * @var \WP_Post|null Post object (now).
			 *
			 * @since 141111 First documented version.
			 */
			protected $post;

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
			 * @since 141111 First documented version.
			 */
			protected $new_post_status;

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
			 * @since 141111 First documented version.
			 */
			protected $old_post_status;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
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
			 */
			public function __construct($new_post_status, $old_post_status, \WP_Post $post = NULL)
			{
				parent::__construct();

				$this->post            = $post; // \WP_Post|null.
				$this->new_post_status = (string)$new_post_status;
				$this->old_post_status = (string)$old_post_status;

				$this->maybe_sub_auto_inject();
			}

			/**
			 * Auto inject post author and/or recipients.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_sub_auto_inject()
			{
				if(!$this->post)
					return; // Not possible.

				if(!$this->plugin->options['auto_subscribe_enable'])
					return; // Not applicable.

				if($this->new_post_status === 'publish' && $this->old_post_status !== 'publish')
					if($this->old_post_status !== 'trash') // Ignore restorations.
						new sub_auto_injector($this->post->ID);
			}
		}
	}
}