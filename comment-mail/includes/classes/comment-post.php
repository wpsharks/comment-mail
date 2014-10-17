<?php
/**
 * Comment Post
 *
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
		 * @since 14xxxx First documented version.
		 */
		class comment_post extends abstract_base
		{
			/**
			 * @var integer Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_id;

			/**
			 * @var string Current/initial comment status.
			 *    One of: `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_status;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @param integer|string $comment_status Initial comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 */
			public function __construct($comment_id, $comment_status)
			{
				parent::__construct();

				$this->comment_id     = (integer)$comment_id;
				$this->comment_status = $this->plugin->utils_db->comment_status__($comment_status);

				$this->maybe_inject_sub();
				$this->maybe_inject_queue();
				$this->maybe_set_sub_current_email();
			}

			/**
			 * Inject subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_inject_sub()
			{
				if(!$this->comment_id)
					return; // Not applicable.

				if(empty($_POST[__NAMESPACE__.'_sub_type']))
					return; // Not applicable.

				if(empty($_POST[__NAMESPACE__.'_sub_deliver']))
					return; // Not applicable.

				$type = (string)$_POST[__NAMESPACE__.'_sub_type'];
				if(!($type = $this->plugin->utils_string->trim_strip_deep($type)))
					return; // Not applicable.

				$deliver = (string)$_POST[__NAMESPACE__.'_sub_deliver'];
				if(!($deliver = $this->plugin->utils_string->trim_strip_deep($deliver)))
					return; // Not applicable.

				new sub_injector(wp_get_current_user(), $this->comment_id, compact('type', 'deliver'));
			}

			/**
			 * Inject/queue emails.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_inject_queue()
			{
				if(!$this->comment_id)
					return; // Not applicable.

				if($this->comment_status !== 'approve')
					return; // Not applicable.

				new queue_injector($this->comment_id);

				$this->maybe_immediately_process_queue();
			}

			/**
			 * Immediately process queued emails.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_immediately_process_queue()
			{
				if(($immediate_max_time = (integer)$this->plugin->options['queue_processor_immediate_max_time']) <= 0)
					return; // Immediate queue processing is not enabled right now.

				if(($immediate_max_limit = (integer)$this->plugin->options['queue_processor_immediate_max_limit']) <= 0)
					return; // Immediate queue processing is not enabled right now.

				new queue_processor(FALSE, $immediate_max_time, 0, $immediate_max_limit); // No delay.
			}

			/**
			 * Set current sub. email address.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_set_sub_current_email()
			{
				if(empty($_REQUEST['email']))
					return; // Nothing to do.

				if(headers_sent()) return; // Not possible.

				if(!($email = trim(stripslashes((string)$_REQUEST['email']))))
					return; // Nothing to do.

				$this->plugin->utils_sub->set_current_email($email);
			}
		}
	}
}