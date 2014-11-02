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
		class sub_confirmer extends abs_base
		{
			/**
			 * @var \stdClass|null Subscription.
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
			 * @var boolean User initiated?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_initiated;

			/**
			 * @var boolean Auto confirmed?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $auto_confirmed;

			/**
			 * @var boolean Confirming via email?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $confirming_via_email;

			/**
			 * @var boolean Sent an email?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sent_email_successfully;

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

				if(($sub_id = (integer)$sub_id))
					$this->sub = $this->plugin->utils_sub->get($sub_id);

				$defaults_args = array(
					'auto_confirm'   => NULL,
					'process_events' => TRUE,
					'user_initiated' => FALSE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				if(isset($args['auto_confirm']))
					$this->auto_confirm = (boolean)$args['auto_confirm'];
				$this->process_events          = (boolean)$args['process_events'];
				$this->user_initiated          = (boolean)$args['user_initiated'];
				$this->user_initiated          = $this->plugin->utils_sub->check_user_initiated_by_admin(
					$this->sub ? $this->sub->email : '', $this->user_initiated
				);
				$this->auto_confirmed          = FALSE; // Initialize.
				$this->confirming_via_email    = FALSE; // Initialize.
				$this->sent_email_successfully = FALSE; // Initialize.

				$this->maybe_send_confirmation_request();
			}

			/**
			 * Auto-confirmed?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if auto-confirmed.
			 */
			public function auto_confirmed()
			{
				return $this->auto_confirmed;
			}

			/**
			 * Confirming via email?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if confirming via email.
			 */
			public function confirming_via_email()
			{
				return $this->confirming_via_email;
			}

			/**
			 * Sent email successfully?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if sent email successfully.
			 */
			public function sent_email_successfully()
			{
				return $this->sent_email_successfully;
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

				if(!get_post($this->sub->post_id))
					return; // Post no longer exists.

				if($this->sub->comment_id && !get_comment($this->sub->comment_id))
					return; // Comment no longer exists.

				$sub           = $this->sub; // For template.
				$template_vars = get_defined_vars(); // Everything above.

				$subject_template = new template('email/confirmation-request-subject.php');
				$message_template = new template('email/confirmation-request-message.php');

				$subject = trim(preg_replace('/\s+/', ' ', $subject_template->parse($template_vars)));
				$message = $message_template->parse($template_vars); // With confirmation link.

				if(!$subject || !$message) // Missing one of these?
					return; // One or more corrupted/empty template files.

				$this->confirming_via_email    = TRUE; // Flag this scenario.
				$this->sent_email_successfully = $this->plugin->utils_mail->send(
					$this->sub->email, $subject, $message
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
				$can_auto_confirm = // Call upon our utility for this.
					$this->plugin->utils_sub->can_auto_confirm(
						$this->sub->post_id, $this->sub->user_id, $this->sub->email, $this->sub->last_ip,
						$this->user_initiated, $this->auto_confirm
					);
				if($can_auto_confirm) // Possible to auto-confirm?
				{
					$this->plugin->utils_sub->confirm($this->sub->ID, array(
						'process_events' => $this->process_events,
					)); // With behavioral args.

					return ($this->auto_confirmed = TRUE);
				}
				return ($this->auto_confirmed = FALSE);
			}
		}
	}
}