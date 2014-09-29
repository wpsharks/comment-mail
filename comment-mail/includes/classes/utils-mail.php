<?php
/**
 * Mail Utilities
 *
 * @package utils_mail
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
		 * @package utils_mail
		 * @since 14xxxx First documented version.
		 */
		class utils_mail // Mail utilities.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var array Instance cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $cache = array();

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
			}

			/**
			 * Mail sending utility; `wp_mail()` compatible.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This method always (ALWAYS) sends email in HTML format;
			 *    w/ a plain text alternative — generated automatically.
			 *
			 * @param string|array $to Array or comma-separated list of emails.
			 * @param string       $subject Email subject line.
			 * @param string       $message Message contents.
			 * @param string|array $headers Optional. Additional headers.
			 * @param string|array $attachments Optional. Files to attach.
			 *
			 * @return boolean TRUE if the email was sent successfully.
			 */
			public function send($to, $subject, $message, $headers = array(), $attachments = array())
			{
				if($this->plugin->options['smtp_enable'] // SMTP mailer enabled?
				   && $this->plugin->options['smtp_host'] && $this->plugin->options['smtp_port']
				) // If the SMTP mailer is enabled & configured; i.e. ready for use.
				{
					if(!isset($this->cache[__FUNCTION__]['mail_smtp']))
						$mail_smtp = $this->cache[__FUNCTION__]['mail_smtp'] = new mail_smtp();
					else $mail_smtp = $this->cache[__FUNCTION__]['mail_smtp'];

					/** @var $mail_smtp mail_smtp Reference for IDEs. */
					return $mail_smtp->send($to, $subject, $message, $headers, $attachments);
				}
				if(is_array($headers)) // Append `Content-Type`.
					$headers[] = 'Content-Type: text/html; charset=UTF-8';
				else $headers = trim((string)$headers."\r\n".'Content-Type: text/html; charset=UTF-8');

				return wp_mail($to, $subject, $message, $headers, $attachments);
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
			 * @return \stdClass[] Unique array of all parsed recipients (lowercase emails).
			 *    Each object in the array contains 3 properties: `fname`, `lname`, `email`.
			 */
			public function parse_recipients_deep($value, $strict = FALSE)
			{
				$recipients = array(); // Initialize.

				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => $_value) // Collect all recipients.
						$recipients = array_merge($recipients, $this->parse_recipients_deep($_value, $strict));
					unset($_key, $_value); // A little housekeeping.

					return $recipients ? $this->plugin->utils_array->unique_deep($recipients) : array();
				}
				$value                       = trim((string)$value);
				$delimiter                   = (strpos($value, ';') !== FALSE) ? ';' : ',';
				$regex_delimitation_splitter = '/'.preg_quote($delimiter, '/').'+/';

				$possible_recipients = preg_split($regex_delimitation_splitter, $value, NULL, PREG_SPLIT_NO_EMPTY);
				$possible_recipients = $this->plugin->utils_string->trim_deep($possible_recipients);

				foreach($possible_recipients as $_recipient) // Iterate all possible recipients.
				{
					if(strpos($_recipient, '@') === FALSE) continue; // NOT an email address.

					if(strpos($_recipient, '<') !== FALSE && preg_match('/(?:"(?P<recipient_name>[^"]+?)"\s*)?\<(?P<recipient_email>.+?)\>/', $_recipient, $_m))
						if(strpos($_m['recipient_email'], '@', 1) !== FALSE && (!$strict || is_email($_m['recipient_email'])))
						{
							$_email = strtolower($_m['recipient_email']);

							$_name = !empty($_m['recipient_name']) ? $_m['recipient_name'] : '';
							$_name = $_name ? $this->plugin->utils_string->clean_name($_name) : '';

							$_fname = $_name; // Default value; full name.
							$_lname = ''; // Default value; empty string for now.

							if($_name && strpos($_name, ' ', 1) !== FALSE) // Last name?
								list($_fname, $_lname) = explode($_name, ' ', 2);

							if(!$_fname) $_fname = (string)strstr($_email, '@', TRUE);

							$recipients[] = (object)array('fname' => $_fname, 'lname' => $_lname, 'email' => $_email);

							continue; // Inside brackets; all done here.
						}
					if(strpos($_recipient, '@', 1) !== FALSE && (!$strict || is_email($_recipient)))
					{
						$_email = strtolower($_recipient);

						$_fname = (string)strstr($_email, '@', TRUE);
						$_lname = ''; // Not possible in this case.

						$recipients[] = (object)array('fname' => $_fname, 'lname' => $_lname, 'email' => $_email);
					}
				}
				unset($_recipient, $_m, $_email, $_name, $_fname, $_lname); // Housekeeping.

				return $recipients ? $this->plugin->utils_array->unique_deep($recipients) : array();
			}
		}
	}
}