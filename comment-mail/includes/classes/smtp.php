<?php
/**
 * SMTP (powered by PHPMailer)
 *
 * @package smtp
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\smtp'))
	{
		/**
		 * SMTP (powered by PHPMailer)
		 *
		 * @package smtp
		 * @since 14xxxx First documented version.
		 */
		class smtp // SMTP mail handler.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var string From name.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $from_name = '';

			/**
			 * @var string From email address.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $from_email = '';

			/**
			 * @var array Recipients.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $recipients = array();

			/**
			 * @var string Subject line.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $subject = '';

			/**
			 * @var string Message content body.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $message = '';

			/**
			 * @var array Additional headers.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $headers = array();

			/**
			 * @var array Attachments.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $attachments = array();

			/**
			 * @var \PHPMailer PHPMailer instance.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $mailer = NULL;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				if(!class_exists('\\PHPMailer'))
					require_once ABSPATH.WPINC.'/class-phpmailer.php';

				if(!class_exists('\\SMTP'))
					require_once ABSPATH.WPINC.'/class-smtp.php';

				$this->mailer = new \PHPMailer(TRUE); // With exceptions.

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
			 * @param string|array $to Array or comma-separated list of emails.
			 * @param string       $subject Email subject line.
			 * @param string       $message Message contents.
			 * @param string|array $headers Optional. Additional headers.
			 * @param string|array $attachments Optional. Files to attach.
			 *
			 * @param boolean      $throw Defaults to a FALSE value.
			 *    If TRUE, an exception might be thrown here.
			 *
			 * @return boolean TRUE if the email was sent successfully.
			 *
			 * @throws \exception If `$throw` is TRUE and an exception is thrown.
			 */
			public function mail($to, $subject, $message, $headers = array(), $attachments = array(), $throw = FALSE)
			{
				$this->reset(); // Reset state; i.e. class properties.

				$this->from_name  = $this->plugin->options['smtp_from_name'];
				$this->from_email = $this->plugin->options['smtp_from_email'];

				$this->recipients = $this->parse_recipients_deep($to);

				$this->subject = (string)$subject; // Force string value.
				$this->message = (string)$message; // Force string value.

				$this->headers     = $this->parse_headers_deep($headers);
				$this->attachments = $this->parse_attachments_deep($attachments);

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

					if(!$this->is_html($this->message))
						$this->mailer->MsgHTML($this->to_html($this->message));
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
			 * Is a string in HTML format?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string Any input string to test here.
			 *
			 * @return boolean TRUE if string is HTML.
			 */
			protected function is_html($string)
			{
				return strpos($string, '<') !== FALSE && preg_match('/\<[^<>]+\>/', $string);
			}

			/**
			 * Convert plain text to HTML markup.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string Input string to convert.
			 *
			 * @return string Plain text converted to HTML markup.
			 */
			protected function to_html($string)
			{
				return nl2br(make_clickable(esc_html($this->message)));
			}

			/**
			 * Parses recipients (deeply).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $value Any input value.
			 *
			 * @param boolean $strict Optional. Defaults to FALSE (faster). Parses all strings w/ `@` signs.
			 *    If TRUE, we will validate each address; and we ONLY return 100% valid email addresses.
			 *
			 * @return array Unique array of all parsed recipients (lowercase).
			 */
			protected function parse_recipients_deep($value, $strict = FALSE)
			{
				$recipients = array(); // Initialize.

				foreach($this->plugin->utils_mail->parse_recipients_deep($value, $strict) as $_recipient)
					$recipients[] = $_recipient->email; // Email address only.
				unset($_recipient); // Housekeeping.

				return $recipients ? array_unique($recipients) : array();
			}

			/**
			 * Parses headers (deeply).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $value Any input value.
			 *
			 * @param boolean $___recursion Internal use only (indicates function recursion).
			 *
			 * @return array Unique array of all parsed headers.
			 */
			protected function parse_headers_deep($value, $___recursion = FALSE)
			{
				$headers = array(); // Initialize.

				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => $_value)
					{
						if(is_string($_key) && is_string($_value)) // Associative array of headers?
							$headers = array_merge($headers, $this->parse_headers_deep($_key.': '.$_value, TRUE));
						else $headers = array_merge($headers, $this->parse_headers_deep($_value, TRUE));
					}
					unset($_key, $_value); // A little housekeeping.

					return $headers ? array_unique($headers) : array();
				}
				$value = trim((string)$value); // Force string value.

				foreach(explode("\r\n", $value) as $_rn_delimited_header)
				{
					if(strpos($_rn_delimited_header, ':') === FALSE)
						continue; // Invalid header.

					list($_header, $_value) = explode(':', $_rn_delimited_header, 2);
					if(!($_header = trim($_header)) || !strlen($_value = trim($_value)))
						continue; // No header; no empty value.

					switch(strtolower($_header)) // Deal w/ special headers.
					{
						case 'content-type': // A `Content-Type` header?

							// This is unsupported in our SMTP class.
							// All emails are sent with a `UTF-8` charset.
							// All emails are sent as HTML w/ a plain text fallback.

							break; // Break switch handler.

						case 'from': // Custom `From:` header?

							if(strpos($_value, '<') !== FALSE) // Possible name?
							{
								$_from_name = substr($_value, 0, strpos($_value, '<') - 1);
								$_from_name = str_replace('"', '', $_from_name);
								$_from_name = trim($_from_name);

								$_from_email = substr($_value, strpos($_value, '<') + 1);
								$_from_email = str_replace('>', '', $_from_email);
								$_from_email = trim($_from_email);

								if($_from_email && strpos($_from_email, '@', 1) !== FALSE && is_email($_value))
								{
									$this->from_name  = $_from_name;
									$this->from_email = $_from_email;
								}
							}
							else if($_value && strpos($_value, '@', 1) !== FALSE && is_email($_value))
							{
								$this->from_name  = ''; // No name.
								$this->from_email = $_value;
							}
							break; // Break switch handler.

						case 'cc':  // A `CC:` header; i.e. carbon copies?
						case 'bcc': // A `BCC:` header; i.e. blind carbon copies?

							// Our SMTP mailer sends all emails singularly.
							// Thus, all recipients are pooled together.

							if(($_cc_bcc_emails = $this->parse_recipients_deep($_value)))
							{
								$this->recipients = array_merge($this->recipients, $_cc_bcc_emails);
								$this->recipients = array_unique($this->recipients);
							}
							break; // Break switch handler.

						default: // Everything else becomes a header.

							$headers[$_header] = $_value;

							break; // Break switch handler.
					}
					unset($_from_name, $_from_email, $_cc_bcc_emails); // Housekeeping.
				}
				unset($_rn_delimited_header, $_header, $_value); // More housekeeping.

				return $headers ? array_unique($headers) : array();
			}

			/**
			 * Parses attachments (deeply).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $value Any input value.
			 *
			 * @param boolean $___recursion Internal use only (indicates function recursion).
			 *
			 * @return array Unique array of all parsed attachments.
			 */
			protected function parse_attachments_deep($value, $___recursion = FALSE)
			{
				$attachments = array(); // Initialize.

				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => $_value)
						$attachments = array_merge($attachments, $this->parse_attachments_deep($_value, TRUE));
					unset($_key, $_value); // A little housekeeping.

					return $attachments ? array_unique($attachments) : array();
				}
				$value = trim((string)$value); // Force string value.

				if($value && is_file($value)) $attachments[] = $value;

				return $attachments ? array_unique($attachments) : array();
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