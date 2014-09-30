<?php
/**
 * Sub Confirmer
 *
 * @package sub_confirmer
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
		 * @package sub_confirmer
		 * @since 14xxxx First documented version.
		 */
		class sub_confirmer // Sub confirmer.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var \stdClass|null Subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id Comment ID.
			 */
			public function __construct($sub_id)
			{
				$this->plugin = plugin();

				$sub_id    = (integer)$sub_id;
				$this->sub = $this->plugin->utils_sub->get($sub_id);

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
				if($this->plugin->options['auto_confirm_enable'])
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

				       " AND `status` = 'subscribed'".

				       " ORDER BY `insertion_time` DESC LIMIT 1";

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