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

				if(($comment_id = (integer)$comment_id) > 0)
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

				$this->maybe_inject();
			}

			/**
			 * Inserted successfully?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean Did we insert?
			 */
			public function did_insert()
			{
				if(!$this->sub_inserter)
					return FALSE;

				return $this->sub_inserter->did_insert();
			}

			/**
			 * Insertion ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return integer Insertion ID; if applicable.
			 */
			public function insert_id()
			{
				if(!$this->sub_inserter)
					return 0;

				return $this->sub_inserter->insert_id();
			}

			/**
			 * Do we have errors?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if has errors.
			 */
			public function has_errors()
			{
				if(!$this->sub_inserter)
					return FALSE;

				return $this->sub_inserter->has_errors();
			}

			/**
			 * Array of any errors.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of any/all errors.
			 */
			public function errors()
			{
				if(!$this->sub_inserter)
					return array();

				return $this->sub_inserter->errors();
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

				if($this->comment->comment_type)
					if($this->comment->comment_type !== 'comment')
						return; // Not applicable.

				$data               = array(
					'post_id'    => $this->comment->comment_post_ID,
					'user_id'    => $this->user ? $this->user->ID : NULL,
					'comment_id' => $this->type === 'comments' ? 0 : $this->comment->comment_ID,
					'deliver'    => $this->deliver, // Delivery option.

					'fname'      => $this->first_name(),
					'lname'      => $this->last_name(),
					'email'      => $this->comment->comment_author_email,
				);
				$this->sub_inserter = new sub_inserter($data, array(
					'process_confirmation' => TRUE, // Always.
					'auto_confirm'         => $this->auto_confirm,
					'process_events'       => $this->process_events,
					'user_initiated'       => $this->user_initiated,
				));
			}

			/**
			 * Commenters first name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters first name; else `name` in email address.
			 */
			protected function first_name()
			{
				$fname = $name =  // Start with a clean full name.
					$this->plugin->utils_string->clean_name($this->comment->comment_author);

				if(strpos($name, ' ', 1) !== FALSE)
					list($fname,) = explode(' ', $name, 2);

				$fname = trim($fname); // Cleanup first name.

				return $fname; // First name.
			}

			/**
			 * Commenters last name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters last name; else empty string.
			 */
			protected function last_name()
			{
				$lname = ''; // Empty string; initialize.
				$name  // Last part of full name might be useable.
				       = $this->plugin->utils_string->clean_name($this->comment->comment_author);

				if(strpos($name, ' ', 1) !== FALSE)
					list(, $lname) = explode(' ', $name, 2);

				$lname = trim($lname); // Cleanup last name.

				return $lname; // Last name.
			}
		}
	}
}