<?php
/**
 * SMTP Mailer
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class mail_smtp extends abs_base
		{
			/**
			 * @var boolean Debugging enable?
			 *
			 * @since 141111 First documented version.
			 */
			protected $debug;

			/**
			 * @var string Debug output in HTML markup.
			 *
			 * @since 141111 First documented version.
			 */
			protected $debug_output_markup;

			/**
			 * @var string From name.
			 *
			 * @since 141111 First documented version.
			 */
			protected $from_name;

			/**
			 * @var string From email address.
			 *
			 * @since 141111 First documented version.
			 */
			protected $from_email;

			/**
			 * @var string Reply-to email address.
			 *
			 * @since 141111 First documented version.
			 */
			protected $reply_to_email;

			/**
			 * @var array Recipients.
			 *
			 * @since 141111 First documented version.
			 */
			protected $recipients;

			/**
			 * @var string Subject line.
			 *
			 * @since 141111 First documented version.
			 */
			protected $subject;

			/**
			 * @var string Raw message body.
			 *
			 * @since 141111 First documented version.
			 */
			protected $message;

			/**
			 * @var string Message HTML body.
			 *
			 * @since 141111 First documented version.
			 */
			protected $message_html;

			/**
			 * @var string Message text body.
			 *
			 * @since 141111 First documented version.
			 */
			protected $message_text;

			/**
			 * @var array Additional headers.
			 *
			 * @since 141111 First documented version.
			 */
			protected $headers;

			/**
			 * @var array Attachments.
			 *
			 * @since 141111 First documented version.
			 */
			protected $attachments;

			/**
			 * @var \PHPMailer PHPMailer instance.
			 *
			 * @since 141111 First documented version.
			 */
			protected $mailer;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param boolean $debug Enable debugging?
			 *
			 * @throws \exception If !`smtp_enable` or `smtp_host|port` are missing.
			 */
			public function __construct($debug = FALSE)
			{
				parent::__construct();

				$this->debug               = (boolean)$debug;
				$this->debug_output_markup = '';

				$this->from_name      = '';
				$this->from_email     = '';
				$this->reply_to_email = '';

				$this->recipients = array();

				$this->subject      = '';
				$this->message      = '';
				$this->message_html = '';
				$this->message_text = '';

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
			 * @since 141111 First documented version.
			 *
			 * @return string Current debug ouput in HTML markup.
			 */
			public function debug_output_markup()
			{
				return $this->debug_output_markup;
			}

			/**
			 * Mail sending utility; `wp_mail()` compatible.
			 *
			 * @since 141111 First documented version.
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
				$this->prep($to, $subject, $message, $headers, $attachments);

				// Some basic validation. Do we have all required parts? e.g. from, recipients, subject, and body.
				if(!$this->from_email || !$this->recipients || !$this->subject || !$this->message_html || !$this->message_text)
					return FALSE; // Not possible. Missing vital argument value(s).

				try // PHPMailer (catch exceptions).
				{
					if($this->debug)
					{
						ob_start();
						$this->mailer->SMTPDebug   = 2;
						$this->mailer->Debugoutput = 'html';
					}
					$this->mailer->IsSMTP();
					$this->mailer->SingleTo = TRUE;

					$this->mailer->SMTPSecure = $this->plugin->options['smtp_secure'];
					$this->mailer->Host       = $this->plugin->options['smtp_host'];
					$this->mailer->Port       = (integer)$this->plugin->options['smtp_port'];

					$this->mailer->SMTPAuth = (boolean)$this->plugin->options['smtp_username'];
					$this->mailer->Username = $this->plugin->options['smtp_username'];
					$this->mailer->Password = $this->plugin->options['smtp_password'];

					// If forcing a specific from address, override anything else defined previously.
					if($this->plugin->options['smtp_force_from'] && $this->plugin->options['smtp_from_email'])
						$this->mailer->SetFrom($this->plugin->options['smtp_from_email'], $this->plugin->options['smtp_from_name']);
					else $this->mailer->SetFrom($this->from_email, $this->from_name); // What was parsed above already.

					if($this->reply_to_email) // Add reply-to email.
						$this->mailer->addReplyTo($this->reply_to_email);

					foreach($this->recipients as $_recipient)
						$this->mailer->AddAddress($_recipient);
					unset($_recipient); // Housekeeping.

					$this->mailer->CharSet = 'UTF-8';
					$this->mailer->Subject = $subject;

					$this->mailer->MsgHTML($this->message_html);
					$this->mailer->AltBody = // Our text alternative.
						$this->mailer->normalizeBreaks($this->message_text);

					foreach($this->headers as $_header => $_value)
						$this->mailer->AddCustomHeader($_header, $_value);
					unset($_header, $_value); // Housekeeping.

					foreach($this->attachments as $_attachment)
						$this->mailer->AddAttachment($_attachment);
					unset($_attachment); // Housekeeping.

					$response = $this->mailer->Send();

					if($this->debug) // Debugging?
					{
						$this->mailer->smtpClose();
						// So we pickup goodbye errors too.
						$this->debug_output_markup .= ob_get_clean();
					}
					return (boolean)$response;
				}
				catch(\exception $exception)
				{
					if($this->debug) // Debugging?
					{
						$this->debug_output_markup // Add to debug output.
							.= esc_html($exception->getMessage()).'<br />'."\n";

						try // So we pickup goodbye errors too.
						{
							$this->mailer->smtpClose();
						}
						catch(\exception $exception_on_close)
						{
							$this->debug_output_markup // Add to debug output.
								.= esc_html($exception_on_close->getMessage()).'<br />'."\n";
						}
						$this->debug_output_markup .= ob_get_clean();
					}
					if($throw) throw $exception;

					return FALSE; // Failure.
				}
			}

			/**
			 * Preps for send using args to {@link send()}.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|array $to Email address(es).
			 * @param string       $subject Email subject line.
			 * @param string       $message Message contents.
			 * @param string|array $headers Optional. Additional headers.
			 * @param string|array $attachments Optional. Files to attach.
			 */
			protected function prep($to, $subject, $message, $headers = array(), $attachments = array())
			{
				$this->reset(); // Reset state, always.

				// These serve only as defaults. Override w/ headers.
				$this->from_name      = $this->plugin->options['smtp_from_name'];
				$this->from_email     = $this->plugin->options['smtp_from_email'];
				$this->reply_to_email = $this->plugin->options['smtp_reply_to_email'];

				// Any `Cc:` or `Bcc:` headers will supplement this list below.
				$this->recipients = $this->plugin->utils_mail->parse_addresses_deep($to, FALSE, TRUE);

				// Establish subject line and raw input message body.
				$this->subject = (string)$subject; // Force string at all times.
				$this->message = (string)$message; // Force string at all times.

				// Detect raw message body type for analysis below.
				$is_message_html = $this->plugin->utils_string->is_html($this->message);
				$is_message_text = !$is_message_html; // The exact opposite of course.

				if(!$is_message_html) // Create an HTML message part.
					$this->message_html = $this->plugin->utils_string->text_to_html($this->message);
				else $this->message_html = $this->message; // Already HTML markup.

				if(!$is_message_text) // Create a plain text message part.
					$this->message_text = $this->plugin->utils_string->html_to_text($this->message);
				else $this->message_text = $this->message; // Already text format.

				if(!$this->message_text) // Set a default plain text alternative in this case.
					$this->message_text = __('To view this email message, open it in a program that understands HTML!', $this->plugin->text_domain);

				// Some of the above details may be overridden by headers parsed here; e.g. `from_name`, `from_email`, `reply_to_email`, or `recipients`.
				$this->headers = $this->plugin->utils_mail->parse_headers_deep($headers, $this->from_name, $this->from_email, $this->reply_to_email, $this->recipients);
				unset($this->headers['content-type']); // Ignore this at all times. We always send multipart messages w/ UTF-8 encoding.

				// Parse any attachments that may or may not exist in the call to this method.
				$this->attachments = $this->plugin->utils_mail->parse_attachments_deep($attachments);
			}

			/**
			 * Reset state; i.e. class properties.
			 *
			 * @since 141111 First documented version.
			 */
			protected function reset()
			{
				$this->from_name      = '';
				$this->from_email     = '';
				$this->reply_to_email = '';

				$this->recipients = array();

				$this->subject      = '';
				$this->message      = '';
				$this->message_html = '';
				$this->message_text = '';

				$this->headers     = array();
				$this->attachments = array();

				$this->mailer->isSMTP();
				$this->mailer->SMTPDebug   = 0;
				$this->mailer->Debugoutput = 'html';
				$this->mailer->SingleTo    = TRUE;

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