<?php
/**
 * Sub Injector
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_injector'))
	{
		/**
		 * Sub Injector
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_injector extends abs_base
		{
			/**
			 * @var \WP_User|null Subscription.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user;

			/**
			 * @var \stdClass|null Comment.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment;

			/**
			 * @var string Subscription type.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $type;

			/**
			 * @var string Subscription delivery option.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $deliver;

			/**
			 * @var null|boolean Auto-confirm?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $auto_confirm;

			/**
			 * @var boolean Process events?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $process_events;

			/**
			 * @var boolean User initiated?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_initiated;

			/**
			 * @var sub_inserter|null Sub inserter.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_inserter;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \WP_User|null  $user Subscribing user.
			 * @param integer|string $comment_id Comment ID.
			 * @param array          $args Any additional behavioral args.
			 */
			public function __construct(\WP_User $user = NULL, $comment_id = 0, array $args = array())
			{
				parent::__construct();

				$this->user = $user; // \WP_user|null.

				$comment_id = (integer)$comment_id;

				if($comment_id) // Need to have this.
					$this->comment = get_comment($comment_id);

				$defaults_args = array(
					'type'           => 'comment',
					'deliver'        => 'asap',
					'auto_confirm'   => NULL,
					'process_events' => TRUE,
					'user_initiated' => FALSE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				$this->type    = strtolower((string)$args['type']);
				$this->deliver = strtolower((string)$args['deliver']);

				if(isset($args['auto_confirm']))
					$this->auto_confirm = (boolean)$args['auto_confirm'];
				$this->process_events = (boolean)$args['process_events'];
				$this->user_initiated = (boolean)$args['user_initiated'];
				$this->user_initiated = $this->plugin->utils_sub->check_user_initiated_by_admin(
					$this->comment ? $this->comment->comment_author_email : '', $this->user_initiated
				);
				$this->maybe_inject();
			}

			/**
			 * Sub inserter.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return sub_inserter|null Sub inserter.
			 */
			public function sub_inserter()
			{
				return $this->sub_inserter;
			}

			/**
			 * Injects a new subscription.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_inject()
			{
				if(!$this->comment)
					return; // Not possible.

				if(!$this->comment->comment_post_ID)
					return; // Not possible.

				if(!$this->comment->comment_ID)
					return; // Not possible.

				if(!$this->comment->comment_author_email)
					return; // Not possible.

				if($this->comment->comment_type
				   && $this->comment->comment_type !== 'comment'
				) return; // Not applicable.

				$data               = array(
					'post_id'    => $this->comment->comment_post_ID,
					'user_id'    => $this->user ? $this->user->ID : NULL,
					'comment_id' => $this->type === 'comments' ? 0 : $this->comment->comment_ID,
					'deliver'    => $this->deliver, // Delivery option.

					'fname'      => $this->plugin->utils_string->first_name($this->comment->comment_author, $this->comment->comment_author_email),
					'lname'      => $this->plugin->utils_string->last_name($this->comment->comment_author),
					'email'      => $this->comment->comment_author_email,
				);
				$this->sub_inserter = new sub_inserter($data, array(
					'process_confirmation' => TRUE, // Always.
					'auto_confirm'         => $this->auto_confirm,
					'process_events'       => $this->process_events,
					'user_initiated'       => $this->user_initiated,
				));
			}
		}
	}
}