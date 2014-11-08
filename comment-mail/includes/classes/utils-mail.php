<?php
/**
 * Mail Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_mail'))
	{
		/**
		 * Mail Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_mail extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Mail sending utility; `wp_mail()` compatible.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This method always (ALWAYS) sends email in HTML format;
			 *    w/ a plain text alternative — generated automatically.
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
			 * @throws \exception If `$throw` is `TRUE` and an SMTP failure occurs.
			 */
			public function send($to, $subject, $message, $headers = array(), $attachments = array(), $throw = FALSE)
			{
				if($this->plugin->options['smtp_enable'] // SMTP mailer enabled?
				   && $this->plugin->options['smtp_host'] && $this->plugin->options['smtp_port']
				) // If the SMTP mailer is enabled & configured; i.e. ready for use.
				{
					if(is_null($mail_smtp = &$this->cache_key(__FUNCTION__, 'mail_smtp')))
						/** @var $mail_smtp mail_smtp Reference for IDEs. */
						$mail_smtp = new mail_smtp(); // Single instance.

					return $mail_smtp->send($to, $subject, $message, $headers, $attachments, $throw);
				}
				if(is_array($headers)) // Append `Content-Type`.
					$headers[] = 'Content-Type: text/html; charset=UTF-8';
				else $headers = trim((string)$headers."\r\n".'Content-Type: text/html; charset=UTF-8');

				return wp_mail($to, $subject, $message, $headers, $attachments);
			}

			/**
			 * SMTP mail testing utility.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This method always (ALWAYS) sends email in HTML format;
			 *    w/ a plain text alternative — generated automatically.
			 *
			 * @param string|array $to Email address(es).
			 * @param string       $subject Email subject line.
			 * @param string       $message Message contents.
			 * @param string|array $headers Optional. Additional headers.
			 * @param string|array $attachments Optional. Files to attach.
			 *
			 * @return \stdClass With the following properties:
			 *
			 *    • `debug_output` = a string w/ any debugging output.
			 *    • `sent` = `yes` if the email was sent successfully; else `no`.
			 *    • `results` = Markup with all of the above in test response format.
			 */
			public function smtp_test($to, $subject, $message, $headers = array(), $attachments = array())
			{
				$sent         = 'no'; // Initialize.
				$debug_output = ''; // Initialize.

				if($this->plugin->options['smtp_enable'] // SMTP mailer enabled?
				   && $this->plugin->options['smtp_host'] && $this->plugin->options['smtp_port']
				) // If the SMTP mailer is enabled & configured; i.e. ready for use.
				{
					$mail_smtp    = new mail_smtp(TRUE); // Single instance w/ debugging.
					$sent         = $mail_smtp->send($to, $subject, $message, $headers, $attachments);
					$sent         = $sent ? __('yes', $this->plugin->text_domain) : __('no', $this->plugin->text_domain);
					$debug_output = $mail_smtp->debug_output();
				}
				$results = '<h4 style="margin:0;">'.sprintf(__('Test email sent by %1$s&trade; to: &lt;<code>%2$s</code>&gt;', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html($to)).'</h4>';
				$results .= '<h4 style="margin:0 0 1em 0;">'.sprintf(__('Email sent successfully? <code>%1$s</code>', $this->plugin->text_domain), esc_html($sent)).'</h4>';

				$results .= '<h4 style="margin:0;">'.__('Debug Output:', $this->plugin->text_domain).'</h4><hr />'.
				            '<div style="margin:0;">'.$debug_output.'</div>'; // Already HTML markup.

				return (object)compact('sent', 'debug_output', 'results');
			}

			/**
			 * Parses recipients deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $value Any input value w/ recipients.
			 *
			 * @param boolean $strict Optional. Defaults to `FALSE` (faster). Parses all strings w/ `@` signs.
			 *    If `TRUE`, we will validate each address; and we ONLY return 100% valid email addresses.
			 *
			 * @param boolean $emails_only Optional. Defaults to a `FALSE` value.
			 *    If `TRUE`, this returns an array of email addresses only.
			 *
			 * @return \stdClass[]|string[] Unique/associative array of all recipients.
			 *    Each object in the array contains 3 properties: `fname`, `lname`, `email`.
			 *    If `$emails_only` is `TRUE`, each element is simply an email address.
			 *
			 * @note Array keys contain the email address for each recipient.
			 *    This is true even when `$emails_only` are requested here.
			 */
			public function parse_recipients_deep($value, $strict = FALSE, $emails_only = FALSE)
			{
				$recipients = array(); // Initialize.

				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => $_value) // Collect all recipients.
						$recipients = array_merge($recipients, $this->parse_recipients_deep($_value, $strict, $emails_only));
					unset($_key, $_value); // A little housekeeping.

					goto finale; // Return handlers.
				}
				$value                       = trim((string)$value);
				$delimiter                   = (strpos($value, ';') !== FALSE) ? ';' : ',';
				$regex_delimitation_splitter = '/'.preg_quote($delimiter, '/').'+/';

				$possible_recipients = preg_split($regex_delimitation_splitter, $value, NULL, PREG_SPLIT_NO_EMPTY);
				$possible_recipients = $this->plugin->utils_string->trim_deep($possible_recipients);

				foreach($possible_recipients as $_recipient) // Iterate all possible recipients.
				{
					if(strpos($_recipient, '@') === FALSE) continue; // NOT an email address.

					if(strpos($_recipient, '<') !== FALSE && preg_match('/(?:"(?P<name>[^"]+?)"\s*)?\<(?P<email>.+?)\>/', $_recipient, $_m))
						if($_m['email'] && strpos($_m['email'], '@', 1) !== FALSE && (!$strict || is_email($_m['email'])))
						{
							$_email = strtolower($_m['email']);
							$_name  = !empty($_m['name']) ? $_m['name'] : '';
							$_fname = $this->plugin->utils_string->first_name($_name, $_email);
							$_lname = $this->plugin->utils_string->last_name($_name);

							$recipients[$_email] = (object)array('fname' => $_fname, 'lname' => $_lname, 'email' => $_email);

							continue; // Inside brackets; all done here.
						}
					if($_recipient && strpos($_recipient, '@', 1) !== FALSE && (!$strict || is_email($_recipient)))
					{
						$_email = strtolower($_recipient);
						$_fname = $this->plugin->utils_string->first_name('', $_email);
						$_lname = ''; // Not possible in this case.

						$recipients[$_email] = (object)array('fname' => $_fname, 'lname' => $_lname, 'email' => $_email);
					}
				}
				unset($_recipient, $_m, $_email, $_name, $_fname, $_lname); // Housekeeping.

				finale: // Target point; grand finale w/ return handlers.

				if($emails_only) // Return emails only?
				{
					$recipient_emails = array();

					foreach($recipients as $_email_key => $_recipient)
						$recipient_emails[$_email_key] = $_recipient->email;
					unset($_email_key, $_recipient); // Housekeeping.

					return $recipient_emails ? array_unique($recipient_emails) : array();
				}
				return $recipients ? $this->plugin->utils_array->unique_deep($recipients) : array();
			}

			/**
			 * Parses headers deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $value Input value w/ headers.
			 * @param string  $from_name From name; by reference.
			 * @param string  $from_email From address; by reference.
			 * @param array   $recipients Recipients; by reference.
			 *
			 * @param boolean $strict Optional. Defaults to `FALSE` (faster).
			 *    This is related to the parsing of `$recipients`. See {@link parse_recipients_deep()}.
			 *
			 * @return array Unique/associative array of all parsed headers.
			 */
			public function parse_headers_deep($value, &$from_name, &$from_email, array &$recipients, $strict = FALSE)
			{
				$headers = array(); // Initialize.

				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => $_value)
					{
						if(is_string($_key) && is_string($_value)) // Associative array?
							$headers = array_merge($headers, $this->parse_headers_deep($_key.': '.$_value, $from_name, $from_email, $recipients));
						else $headers = array_merge($headers, $this->parse_headers_deep($_value, $from_name, $from_email, $recipients));
					}
					unset($_key, $_value); // A little housekeeping.

					goto finale; // Return handlers.
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

							if(strpos($_value, '<') !== FALSE) // e.g. "Name" <email>.
							{
								$_from_name = substr($_value, 0, strpos($_value, '<') - 1);
								$_from_name = str_replace('"', '', $_from_name);
								$_from_name = trim($_from_name);

								$_from_email = substr($_value, strpos($_value, '<') + 1);
								$_from_email = str_replace('>', '', $_from_email);
								$_from_email = trim($_from_email);

								if($_from_email && strpos($_from_email, '@', 1) !== FALSE && is_email($_value))
								{
									$from_name  = $_from_name; // Use name in `From:` header.
									$from_email = $_from_email; // Use email in `From:` header.
								}
							}
							else if($_value && strpos($_value, '@', 1) !== FALSE && is_email($_value))
							{
								$from_name  = ''; // No name in `From:` header.
								$from_email = $_value; // Use email in `From:` header.
							}
							unset($_from_name, $_from_email); // Housekeeping.

							break; // Break switch handler.

						case 'cc':  // A `CC:` header; i.e. carbon copies?
						case 'bcc': // A `BCC:` header; i.e. blind carbon copies?

							if(($_cc_bcc_emails = $this->parse_recipients_deep($_value, $strict, TRUE)))
							{
								$recipients = array_merge($recipients, $_cc_bcc_emails);
								$recipients = array_unique($recipients); // Unique only.
							}
							unset($_cc_bcc_emails); // Housekeeping.

							break; // Break switch handler.

						default: // Everything else becomes a header.

							$headers[strtolower($_header)] = $_value;

							break; // Break switch handler.
					}
				} // This ends the `foreach()` loop over each of the headers.
				unset($_rn_delimited_header, $_header, $_value); // Housekeeping.

				finale: // Target point; grand finale w/ return handlers.

				return $headers ? array_unique($headers) : array();
			}

			/**
			 * Parses attachments deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $value Any input value w/ attachments.
			 *
			 * @return array Unique/associative array of all attachments.
			 */
			public function parse_attachments_deep($value)
			{
				$attachments = array(); // Initialize.

				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => $_value)
						$attachments = array_merge($attachments, $this->parse_attachments_deep($_value));
					unset($_key, $_value); // Housekeeping.

					goto finale; // Return handlers.
				}
				if(($value = trim((string)$value)) && is_file($value))
					$attachments[$value] = $value; // Only one here.

				finale: // Target point; grand finale w/ return handlers.

				return $attachments ? array_unique($attachments) : array();
			}

			/**
			 * @var array Role-based blacklist patterns.
			 *
			 * @since 14xxxx First documented version.
			 */
			public static $role_based_blacklist_patterns = array(
				'abuse@*',
				'admin@*',
				'billing@*',
				'compliance@*',
				'devnull@*',
				'dns@*',
				'ftp@*',
				'hostmaster@*',
				'inoc@*',
				'ispfeedback@*',
				'ispsupport@*',
				'list-request@*',
				'list@*',
				'maildaemon@*',
				'noc@*',
				'no-reply@*',
				'noreply@*',
				'null@*',
				'phish@*',
				'phishing@*',
				'postmaster@*',
				'privacy@*',
				'registrar@*',
				'root@*',
				'security@*',
				'spam@*',
				'support@*',
				'sysadmin@*',
				'tech@*',
				'undisclosed-recipients@*',
				'unsubscribe@*',
				'usenet@*',
				'uucp@*',
				'webmaster@*',
				'www@*',
			);
		}
	}
}