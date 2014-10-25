<?php
/**
 * SMTP Mailer
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\mail_smtp'))
	{
		/**
		 * SMTP Mailer
		 *
		 * @since 14xxxx First documented version.
		 */
		class mail_smtp extends abs_base
		{
			/**
			 * @var string From name.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $from_name;

			/**
			 * @var string From email address.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $from_email;

			/**
			 * @var array Recipients.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $recipients;

			/**
			 * @var string Subject line.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $subject;

			/**
			 * @var string Message content body.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $message;

			/**
			 * @var array Additional headers.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $headers;

			/**
			 * @var array Attachments.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $attachments;

			/**
			 * @var \PHPMailer PHPMailer instance.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $mailer;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->from_name  = '';
				$this->from_email = '';

				$this->recipients = array();

				$this->subject = '';
				$this->message = '';

				$this->headers     = array();
				$this->attachments = array();

				if(!class_exists('\\PHPMailer'))
					require_once ABSPATH.WPINC.'/class-phpmailer.php';

				if(!class_exists('\\SMTP'))
					require_once ABSPATH.WPINC.'/class-smtp.php';

				$this->mailer = new \PHPMailer(TRUE);

				if(!$this->plugin->options['smtp_enable'])
					throw new \exception(__('SMTP not enabled.', $this->plugin->text_domain));

				if(!$this->plugin->options['smtp_host'] || !$this->plugin->options['smtp_port'])
					throw new \exception(__('SMTP host/port missing.', $this->plugin->text_domain));
			}

			/**
			 * Mail sending utility; `wp_mail()` compatible.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string|array $to Email address(es).
			 * @param string       $subject Email subject line.
			 * @param string       $message Message contents.
			 * @param string|array $headers Optional. Additional headers.
			 * @param string|array $attachments Optional. Files to attach.
			 *
			 * @param boolean      $throw Defaults to a `FALSE` value.
			 *    If `TRUE`, an exception might be thrown here.
			 *
			 * @return boolean `TRUE` if the email was sent successfully.
			 *
			 * @throws \exception If `$throw` is `TRUE` and a failure occurs.
			 */
			public function send($to, $subject, $message, $headers = array(), $attachments = array(), $throw = FALSE)
			{
				$this->reset(); // Reset state; i.e. class properties.

				$this->from_name  = $this->plugin->options['smtp_from_name'];
				$this->from_email = $this->plugin->options['smtp_from_email'];

				$this->recipients = $this->plugin->utils_mail->parse_recipients_deep($to, FALSE, TRUE);

				$this->subject = (string)$subject; // Force string at all times.
				$this->message = (string)$message; // Force string at all times.

				$this->headers     = $this->plugin->utils_mail->parse_headers_deep($headers, $this->from_name, $this->from_email, $this->recipients);
				$this->attachments = $this->plugin->utils_mail->parse_attachments_deep($attachments);

				if(!$this->from_email || !$this->recipients || !$this->subject || !$this->message)
					return FALSE; // Not possible. Missing vital argument value(s).

				try // PHPMailer (catch exceptions).
				{
					$this->mailer->IsSMTP();
					$this->mailer->SingleTo = TRUE;

					$this->mailer->SMTPSecure = $this->plugin->options['smtp_secure'];
					$this->mailer->Host       = $this->plugin->options['smtp_host'];
					$this->mailer->Port       = (integer)$this->plugin->options['smtp_port'];

					$this->mailer->SMTPAuth = (boolean)$this->plugin->options['smtp_username'];
					$this->mailer->Username = $this->plugin->options['smtp_username'];
					$this->mailer->Password = $this->plugin->options['smtp_password'];

					$this->mailer->SetFrom($this->from_email, $this->from_name);
					if($this->plugin->options['smtp_force_from'] && $this->plugin->options['smtp_from_email'])
						$this->mailer->SetFrom($this->plugin->options['smtp_from_email'], $this->plugin->options['smtp_from_name']);

					foreach($this->recipients as $_recipient)
						$this->mailer->AddAddress($_recipient);
					unset($_recipient);

					$this->mailer->CharSet = 'UTF-8';
					$this->mailer->Subject = $subject;

					if(!$this->plugin->utils_string->is_html($this->message))
						$this->mailer->MsgHTML($this->plugin->utils_string->to_html($this->message));
					else $this->mailer->MsgHTML($this->message);

					foreach($this->headers as $_header => $_value)
						$this->mailer->AddCustomHeader($_header, $_value);
					unset($_header, $_value); // Housekeeping.

					foreach($this->attachments as $_attachment)
						$this->mailer->AddAttachment($_attachment);
					unset($_attachment); // Housekeeping.

					return $this->mailer->Send();
				}
				catch(\exception $exception)
				{
					if($throw) throw $exception;

					return FALSE; // Failure.
				}
			}

			/**
			 * Reset state; i.e. class properties.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function reset()
			{
				$this->from_name  = '';
				$this->from_email = '';

				$this->recipients = array();

				$this->subject = '';
				$this->message = '';

				$this->headers     = array();
				$this->attachments = array();

				$this->mailer->isSMTP();
				$this->mailer->SingleTo = TRUE;

				$this->mailer->SMTPSecure = '';
				$this->mailer->Host       = '';
				$this->mailer->Port       = 25;

				$this->mailer->SMTPAuth = FALSE;
				$this->mailer->Username = '';
				$this->mailer->Password = '';

				$this->mailer->From       = '';
				$this->mailer->FromName   = '';
				$this->mailer->Sender     = '';
				$this->mailer->ReturnPath = '';

				$this->mailer->ClearReplyTos();
				$this->mailer->ClearAllRecipients();
				$this->mailer->ClearCustomHeaders();
				$this->mailer->ClearAttachments();

				$this->mailer->CharSet = 'UTF-8';
				$this->mailer->Subject = '';
				$this->mailer->Body    = '';
				$this->mailer->AltBody = '';
			}
		}
	}
}