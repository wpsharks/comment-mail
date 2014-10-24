<?php
/**
 * Sub Confirmer
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_confirmer'))
	{
		/**
		 * Sub Confirmer
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_confirmer extends abstract_base
		{
			/**
			 * @var \stdClass|null Subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub;

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
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id Comment ID.
			 *
			 * @param array          $args Any additional behavioral args.
			 */
			public function __construct($sub_id, array $args = array())
			{
				parent::__construct();

				$sub_id = (integer)$sub_id;

				$defaults_args = array(
					'auto_confirm'   => NULL,
					'process_events' => TRUE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				if(isset($args['auto_confirm']))
					$this->auto_confirm = (boolean)$args['auto_confirm'];
				$this->sub            = $this->plugin->utils_sub->get($sub_id);
				$this->process_events = (boolean)$args['process_events'];

				if(!isset($this->auto_confirm)) // If not set explicitly, use option value.
					if((boolean)$this->plugin->options['auto_confirm_enable'])
						$this->auto_confirm = TRUE; // Yes.

				$this->maybe_send_confirmation_request();
			}

			/**
			 * Send confirmation request.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_send_confirmation_request()
			{
				if(!$this->sub)
					return; // Not possible.

				if(!$this->sub->email)
					return; // Not possible.

				if($this->sub->status === 'subscribed')
					return; // Nothing to do.

				if($this->maybe_auto_confirm())
					return; // Nothing more to do.

				$template_vars    = array('sub' => $this->sub);
				$subject_template = new template('email/confirmation-request-subject.php');
				$message_template = new template('email/confirmation-request-message.php');

				$this->plugin->utils_mail->send(
					$this->sub->email, // To subscriber.
					$subject_template->parse($template_vars),
					$message_template->parse($template_vars)
				);
			}

			/**
			 * Auto-confirm, if possible.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if auto-confirmed in some way.
			 */
			protected function maybe_auto_confirm()
			{
				if($this->auto_confirm === FALSE)
					return FALSE; // Nope.

				if($this->auto_confirm) // Auto-confirm?
				{
					$this->plugin->utils_sub->confirm($this->sub->ID, array(
						'process_events' => $this->process_events
					)); // With behavioral args.

					return TRUE; // Confirmed automatically.
				}
				// Else use default `NULL` behavior; i.e. check if they've already confirmed another.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->sub->post_id)."'".

				       ($this->sub->user_id // Has a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->sub->user_id)."'".
					         "       OR `email` = '".esc_sql($this->sub->email)."')"
					       : " AND `email` = '".esc_sql($this->sub->email)."'").

				       " AND `status` = 'subscribed'".

				       " LIMIT 1"; // One to check.

				if((integer)$this->plugin->utils_db->wp->get_var($sql))
				{
					$this->plugin->utils_sub->confirm($this->sub->ID, array(
						'process_events' => $this->process_events
					)); // With behavioral args.

					return TRUE; // Confirmed automatically.
				}
				return FALSE; // Not subscribed already.
			}
		}
	}
}