<?php
/**
 * Comment Status
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class comment_status extends abs_base
		{
			/**
			 * @var \stdClass|null Comment.
			 *
			 * @since 141111 First documented version.
			 */
			protected $comment;

			/**
			 * @var string New comment status applied now.
			 *    One of: `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 141111 First documented version.
			 */
			protected $new_comment_status;

			/**
			 * @var string Old comment status from before.
			 *    One of: `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 141111 First documented version.
			 */
			protected $old_comment_status;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer|string $new_comment_status New comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param integer|string $old_comment_status Old comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param \WP_Comment|null $comment Comment object (now).
			 */
			public function __construct($new_comment_status, $old_comment_status, /* \WP_Comment */ $comment = NULL)
			{
				parent::__construct();

				$this->comment            = $comment; // \WP_Comment|null.
				$this->new_comment_status = $this->plugin->utils_db->comment_status__($new_comment_status);
				$this->old_comment_status = $this->plugin->utils_db->comment_status__($old_comment_status);

				$this->maybe_inject_queue();
				$this->maybe_purge_subs();
			}

			/**
			 * Inject/queue emails.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_inject_queue()
			{
				if(!isset($this->comment))
					return; // Not applicable.

				if($this->new_comment_status === 'approve' && $this->old_comment_status === 'hold')
					new queue_injector($this->comment->comment_ID);
			}

			/**
			 * Purges subscriptions.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_purge_subs()
			{
				if(!isset($this->comment))
					return; // Not applicable.

				if($this->new_comment_status === 'delete' && $this->old_comment_status !== 'delete')
					new sub_purger($this->comment->comment_post_ID, $this->comment->comment_ID);
			}
		}
	}
}
