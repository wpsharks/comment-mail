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
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id Comment ID.
			 *
			 * @param null|boolean   $auto_confirm Auto-confirm?
			 *    If `NULL`, use current plugin option value.
			 */
			public function __construct($sub_id, $auto_confirm = NULL)
			{
				parent::__construct();

				$sub_id             = (integer)$sub_id;
				$this->sub          = $this->plugin->utils_sub->get($sub_id);
				$this->auto_confirm = isset($auto_confirm) ? (boolean)$auto_confirm : NULL;

				if(!isset($this->auto_confirm)) // If not set explicitly, use option value.
					$this->auto_confirm = (boolean)$this->plugin->options['auto_confirm_enable'];

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

				$this->plugin->utils_mail->send($this->sub->email, // To subscriber.
				                                $subject_template->parse($template_vars),
				                                $message_template->parse($template_vars));
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
					$this->plugin->utils_sub->confirm($this->sub->ID);
					return TRUE; // Confirmed automatically.
				}
				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->sub->post_id)."'".

				       ($this->sub->user_id // Has a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->sub->user_id)."'".
					         "       OR `email` = '".esc_sql($this->sub->email)."')"
					       : " AND `email` = '".esc_sql($this->sub->email)."'").

				       " AND `status` = 'subscribed' LIMIT 1";

				if((integer)$this->plugin->utils_db->wp->get_var($sql))
				{
					$this->plugin->utils_sub->confirm($this->sub->ID);
					return TRUE; // Confirmed automatically.
				}
				return FALSE; // Not subscribed already.
			}
		}
	}
}