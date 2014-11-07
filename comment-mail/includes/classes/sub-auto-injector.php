<?php
/**
 * Auto Sub Injector
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_auto_injector'))
	{
		/**
		 * Auto Sub Injector
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_auto_injector extends abs_base
		{
			/**
			 * @var \stdClass|null Post object.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post;

			/**
			 * @var \WP_User|null Post author.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_author;

			/**
			 * @var array Auto-subscribable post types.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_types;

			/**
			 * @var boolean Process events?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $process_events;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $post_id Post ID.
			 * @param array          $args Any additional behavioral args.
			 */
			public function __construct($post_id, array $args = array())
			{
				parent::__construct();

				$post_id = (integer)$post_id;

				if($post_id) // Need to have this.
					$this->post = get_post($post_id);

				$defaults_args = array(
					'process_events' => TRUE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				if($this->post && $this->post->post_author)
					if($this->plugin->options['auto_subscribe_post_author_enable'])
						$this->post_author = new \WP_User($this->post->post_author);

				$this->post_types = strtolower($this->plugin->options['auto_subscribe_post_types']);
				$this->post_types = preg_split('/[;,\s]+/', $this->post_types, NULL, PREG_SPLIT_NO_EMPTY);

				$this->process_events = (boolean)$args['process_events'];

				$this->maybe_auto_inject();
			}

			/**
			 * Injects subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_auto_inject()
			{
				if(!$this->post)
					return; // Not possible.

				if(!$this->post->ID)
					return; // Not possible.

				if(!$this->plugin->options['auto_subscribe_enable'])
					return; // Not applicable.

				if(!in_array($this->post->post_type, $this->post_types, TRUE))
					return; // Not applicable.

				if(in_array($this->post->post_type, array('revision', 'nav_menu_item'), TRUE))
					return; // Not applicable.

				$this->maybe_inject_post_author();
				$this->maybe_inject_recipients();
			}

			/**
			 * Injects post author.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_inject_post_author()
			{
				if(!$this->post_author)
					return; // Not possible.

				if(!$this->post_author->ID)
					return; // Not possible.

				if(!$this->post_author->user_email)
					return; // Not possible.

				if(!$this->plugin->options['auto_subscribe_post_author_enable'])
					return; // Not applicable.

				$data = array(
					'post_id'    => $this->post->ID,
					'user_id'    => $this->post_author->ID,
					'comment_id' => 0, // Subscribe to all comments.
					'deliver'    => $this->plugin->options['auto_subscribe_deliver'],

					'fname'      => $this->plugin->utils_string->first_name('', $this->post_author),
					'lname'      => $this->plugin->utils_string->last_name('', $this->post_author),
					'email'      => $this->post_author->user_email,

					'status'     => 'subscribed',
				);
				new sub_inserter($data, array(
					'process_events' => $this->process_events,
				));
			}

			/**
			 * Injects recipients.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_inject_recipients()
			{
				if(!$this->plugin->options['auto_subscribe_recipients'])
					return; // Not applicable.

				$recipients = $this->plugin->options['auto_subscribe_recipients'];
				$recipients = $this->plugin->utils_mail->parse_recipients_deep($recipients);

				foreach($recipients as $_recipient)
				{
					if(!$_recipient->email)
						continue; // Not applicable.

					$_data = array(
						'post_id'    => $this->post->ID,
						'comment_id' => 0, // Subscribe to all comments.
						'deliver'    => $this->plugin->options['auto_subscribe_deliver'],

						'fname'      => $_recipient->fname,
						'lname'      => $_recipient->lname,
						'email'      => $_recipient->email,

						'status'     => 'subscribed',
					);
					new sub_inserter($_data, array(
						'process_events' => $this->process_events,
					));
				}
				unset($_recipient, $_data); // Housekeeping.
			}
		}
	}
}