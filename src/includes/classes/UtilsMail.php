<?php
/**
 * Mail Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Mail Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsMail extends AbsBase
{
    /**
     * @type array Role-based blacklist patterns.
     *
     * @since 141111 First documented version.
     */
    public static $role_based_blacklist_patterns = [
        'abuse@*',
        'admin@*',
        'billing@*',
        'compliance@*',
        'devnull@*',
        'dns@*',
        'ftp@*',
        'help@*',
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
        'sales@*',
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
    ];

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * `TRUE` if we can send mail via SMTP.
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if we can send mail via SMTP.
     */
    public function isSmtpEnabled()
    {
        return $this->plugin->options['smtp_enable'] // Enabled & configured?
               && $this->plugin->options['smtp_host'] && $this->plugin->options['smtp_port'];
    }

    /**
     * Does a particular header already exist?
     *
     * @since 141111 First documented version.
     *
     * @param string $header  Header we are looking for.
     *                        e.g. `Reply-To`, without any `:` suffix or anything else.
     * @param array  $headers An array of existing headers to search through.
     *                        This array is expected to contain string elements with full headers.
     *                        e.g. `Reply-To: [value]` would be a single string header.
     *
     * @return string[]|int[]|bool Array keys where the header exists.
     *                             This will return an array with all keys where the header currently exists.
     *                             ~ An empty array if it does NOT exist currently.
     */
    public function headerExists($header, array $headers)
    {
        $existing_keys = []; // Initialize.

        if (!($header = $this->plugin->utils_string->trim((string) $header, '', ':'))) {
            return $existing_keys; // Not possible to look for nothing.
        }
        foreach ($headers as $_key => $_header) {
            if (stripos($_header, $header.':') === 0) {
                $existing_keys[] = $_key;
            }
        }
        unset($_key, $_header); // Housekeeping.

        return $existing_keys; // All existing keys.
    }

    /**
     * Mail sending utility; `wp_mail()` compatible.
     *
     * @since 141111 First documented version.
     *
     * @note  This method always (ALWAYS) sends email in HTML format;
     *    w/ a plain text alternative — generated automatically.
     *
     * @param string|array $to          Email address(es).
     * @param string       $subject     Email subject line.
     * @param string       $message     Message contents.
     * @param string|array $headers     Optional. Additional headers.
     * @param string|array $attachments Optional. Files to attach.
     * @param bool         $throw       Defaults to a `FALSE` value.
     *                                  If `TRUE`, an exception might be thrown here.
     *
     * @throws \exception If `$throw` is `TRUE` and an SMTP failure occurs.
     *
     * @return bool `TRUE` if the email was sent successfully.
     */
    public function send($to, $subject, $message, $headers = [], $attachments = [], $throw = false)
    {
        if ($this->isSmtpEnabled()) { // Can use SMTP; i.e. enabled?
            if (is_null($mail_smtp = &$this->cacheKey(__FUNCTION__, 'mail_smtp'))) {
                $mail_smtp = new MailSmtp(); // Single instance.
            }
            return $mail_smtp->send($to, $subject, $message, $headers, $attachments, $throw);
        }
        if (!is_array($headers)) { // Force array.
            $headers = explode("\r\n", (string) $headers);
        }
        if (($_content_type_keys = $this->headerExists('Content-Type', $headers))) {
            foreach ($_content_type_keys as $_content_type_key) {
                unset($headers[$_content_type_key]);
            }
        }
        unset($_content_type_keys, $_content_type_key); // Housekeeping.

        $headers[] = 'Content-Type: text/html; charset=UTF-8'; // Force this, always.

        if ($this->plugin->options['from_email'] && !$this->headerExists('From', $headers)) {
            $headers[] = 'From: "'.$this->plugin->utils_string->escDq($this->plugin->options['from_name']).'"'.
                         ' <'.$this->plugin->options['from_email'].'>';
        }
        if ($this->plugin->options['reply_to_email'] && !$this->headerExists('Reply-To', $headers)) {
            $headers[] = 'Reply-To: '.$this->plugin->options['reply_to_email'];
        }
        return wp_mail($to, $subject, $message, $headers, $attachments);
    }

    /**
     * A mail testing utility.
     *
     * @since 141111 First documented version.
     *
     * @note  This method always (ALWAYS) sends email in HTML format;
     *    w/ a plain text alternative — generated automatically.
     *
     * @param string|array $to          Email address(es).
     * @param string       $subject     Email subject line.
     * @param string       $message     Message contents.
     * @param string|array $headers     Optional. Additional headers.
     * @param string|array $attachments Optional. Files to attach.
     *
     * @return \stdClass With the following properties:
     *
     *    • `to` = addresses the test was sent to; as an array.
     *    • `via` = the transport layer used in the test; as a string.
     *    • `sent` = `TRUE` if the email was sent successfully; as a boolean.
     *    • `debug_output_markup` = HTML markup w/ any debugging output; as a string.
     *    • `results_markup` = Markup with all of the above in test response format; as a string.
     */
    public function test($to, $subject, $message, $headers = [], $attachments = [])
    {
        if ($this->isSmtpEnabled()) { // Can use SMTP; i.e. enabled?
            return $this->smtpTest($to, $subject, $message, $headers, $attachments);
        }
        $to = array_map('strval', (array) $to); // Force array.

        $via  = 'wp_mail'; // Via `wp_mail` in this case.
        $sent = false; // Initialize as `FALSE`.

        global $phpmailer; // WP global var.
        if (!($phpmailer instanceof \PHPMailer)) {
            require_once ABSPATH.WPINC.'/class-phpmailer.php';
            require_once ABSPATH.WPINC.'/class-smtp.php';
            $phpmailer = new \PHPMailer(true);
        }
        ob_start();
        $phpmailer->SMTPDebug   = 2;
        $phpmailer->Debugoutput = 'html';

        // Note: `wp_mail()` might not actually use \PHPMailer.
        // If that's the case, then debug output below will likely be empty.
        // It's also possible that \PHPMailer is not using SMTP. That's OK too.

        if (!is_array($headers)) { // Force array.
            $headers = explode("\r\n", (string) $headers);
        }
        if (($_content_type_keys = $this->headerExists('Content-Type', $headers))) {
            foreach ($_content_type_keys as $_content_type_key) {
                unset($headers[$_content_type_key]);
            }
        }
        unset($_content_type_keys, $_content_type_key); // Housekeeping.

        $headers[] = 'Content-Type: text/html; charset=UTF-8'; // Force this, always.

        if ($this->plugin->options['from_email'] && !$this->headerExists('From', $headers)) {
            $headers[] = 'From: "'.$this->plugin->utils_string->escDq($this->plugin->options['from_name']).'"'.
                         ' <'.$this->plugin->options['from_email'].'>';
        }
        if ($this->plugin->options['reply_to_email'] && !$this->headerExists('Reply-To', $headers)) {
            $headers[] = 'Reply-To: '.$this->plugin->options['reply_to_email'];
        }
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        if ($phpmailer instanceof \PHPMailer && $phpmailer->Mailer === 'smtp') {
            $phpmailer->smtpClose();
        }
        unset($phpmailer); // Unset so WordPress will recreate if it needs it again in this process.

        $debug_output_markup = $this->plugin->utils_string->trimHtml(ob_get_clean());
        $results_markup      = $this->testResultsMarkup($to, $via, $sent, $debug_output_markup);

        return (object) compact('to', 'via', 'sent', 'debug_output_markup', 'results_markup');
    }

    /**
     * SMTP mail testing utility.
     *
     * @since 141111 First documented version.
     *
     * @note  This method always (ALWAYS) sends email in HTML format;
     *    w/ a plain text alternative — generated automatically.
     *
     * @param string|array $to          Email address(es).
     * @param string       $subject     Email subject line.
     * @param string       $message     Message contents.
     * @param string|array $headers     Optional. Additional headers.
     * @param string|array $attachments Optional. Files to attach.
     *
     * @return \stdClass With the following properties:
     *
     *    • `to` = addresses the test was sent to; as an array.
     *    • `via` = the transport layer used in the test; as a string.
     *    • `sent` = `TRUE` if the email was sent successfully; as a boolean.
     *    • `debug_output_markup` = HTML markup w/ any debugging output; as a string.
     *    • `results_markup` = Markup with all of the above in test response format; as a string.
     */
    public function smtpTest($to, $subject, $message, $headers = [], $attachments = [])
    {
        $to = array_map('strval', (array) $to); // Force array.

        $via  = 'smtp'; // Via SMTP in this case.
        $sent = false; // Initialize as `FALSE`.

        if ($this->isSmtpEnabled()) { // Can use SMTP; i.e. enabled?
            $mail_smtp           = new MailSmtp(true); // Single instance w/ debugging.
            $sent                = $mail_smtp->send($to, $subject, $message, $headers, $attachments);
            $debug_output_markup = $this->plugin->utils_string->trimHtml($mail_smtp->debugOutputMarkup());
        } else {
            $debug_output_markup = __('Complete failure; configuration incomplete.', 'comment-mail');
        }
        $results_markup = $this->testResultsMarkup($to, $via, $sent, $debug_output_markup);

        return (object) compact('to', 'via', 'sent', 'debug_output_markup', 'results_markup');
    }

    /**
     * Test results formatter.
     *
     * @since 141111 First documented version.
     *
     * @note  This method always (ALWAYS) sends email in HTML format;
     *    w/ a plain text alternative — generated automatically.
     *
     * @param array  $to                  Addresses test was sent to.
     * @param string $via                 Transport layer used for the test.
     * @param bool   $sent                Was the test sent succesfully?
     * @param string $debug_output_markup Any debug out; in HTML markup.
     *
     * @return string Full HTML markup with test results; for back-end display.
     */
    public function testResultsMarkup(array $to, $via, $sent, $debug_output_markup)
    {
        $to = array_map('strval', $to);

        $via                 = (string) $via;
        $sent                = (boolean) $sent;
        $debug_output_markup = (string) $debug_output_markup;
        $debug_output_markup = $this->plugin->utils_string->trimHtml($debug_output_markup);

        if ($via === 'wp_mail') { // Convert this to HTML markup.
            $via_markup = $this->plugin->utils_markup->xAnchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail()');
        } elseif ($via === 'smtp') { // Convert this to HTML markup.
            $via_markup = $this->plugin->utils_markup->xAnchor('http://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol', 'SMTP');
        } else {
            $via_markup = esc_html($via); // Convert this to HTML markup.
        }
        if ($sent && !$debug_output_markup) { // There might not be any output in some cases; e.g. if SMTP is not in use.
            $debug_output_markup = '<em>'.esc_html(__('— please check your email to be sure you received the message —', 'comment-mail')).'</em>';
        } elseif (!$sent && !$debug_output_markup) { // There might not be any output in some cases; e.g., if SMTP is not in use.
            $debug_output_markup = '<em>'.esc_html(__('— please seek assistance from your hosting company —', 'comment-mail')).'</em>';
        }
        $results_markup = '<h4 style="margin:0 0 1em 0;">'.
                          '   '.sprintf(
                              __('%1$s&trade; sent a test email via %2$s to:', 'comment-mail'),
                              esc_html(NAME),
                              $via_markup
                          ).'<br />'.
                          '   &lt;<code>'.esc_html(implode('; ', $to)).'</code>&gt;'.
                          '</h4>';

        $results_markup .= '<h4 style="margin:0 0 1em 0;">'.
                           '   '.__('Email sent successfully?', 'comment-mail').'<br />'.
                           '<code>'.esc_html($sent ? __('seems so; please check your email to be sure', 'comment-mail') : __('no', 'comment-mail')).'</code>'.
                           '</h4>';

        $results_markup .= '<hr />'.
                           '<div style="margin:0 0 1em 0;">'.
                           '   '.$debug_output_markup.
                           '</div>';

        return $results_markup; // Full HTML markup for back-end display.
    }

    /**
     * Parses addresses deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $value       Any input value w/ recipients.
     * @param bool  $strict      Optional. Defaults to `FALSE` (faster). Parses all strings w/ `@` signs.
     *                           If `TRUE`, we will validate each address; and we ONLY return 100% valid email addresses.
     * @param bool  $emails_only Optional. Defaults to a `FALSE` value.
     *                           If `TRUE`, this returns an array of email addresses only.
     *
     * @return \stdClass[]|string[] Unique/associative array of all addresses.
     *                              Each object in the array contains 3 properties: `fname`, `lname`, `email`.
     *                              If `$emails_only` is `TRUE`, each element is simply an email address.
     *
     * @note  Array keys contain the email address for each address.
     *    This is true even when `$emails_only` are requested here.
     */
    public function parseAddressesDeep($value, $strict = false, $emails_only = false)
    {
        $addresses = []; // Initialize.

        if (is_array($value) || is_object($value)) {
            foreach ($value as $_key => $_value) { // Collect all addresses.
                $addresses = array_merge($addresses, $this->parseAddressesDeep($_value, $strict, false));
            }
            unset($_key, $_value); // A little housekeeping.

            goto finale; // Where `$emails_only` is dealt w/ separately.
        }
        $value                       = trim((string) $value);
        $delimiter                   = (strpos($value, ';') !== false) ? ';' : ',';
        $regex_delimitation_splitter = '/'.preg_quote($delimiter, '/').'+/';

        $possible_addresses = preg_split($regex_delimitation_splitter, $value, null, PREG_SPLIT_NO_EMPTY);
        $possible_addresses = $this->plugin->utils_string->trimDeep($possible_addresses);

        foreach ($possible_addresses as $_address) { // Iterate all possible addresses.
            if (strpos($_address, '@') === false) {
                continue; // NOT an email address.
            }
            if (strpos($_address, '<') !== false && preg_match('/(?:"(?P<name>[^"]+?)"\s*)?\<(?P<email>.+?)\>/', $_address, $_m)) {
                if ($_m['email'] && strpos($_m['email'], '@', 1) !== false && (!$strict || is_email($_m['email']))) {
                    $_email             = strtolower($_m['email']);
                    $_name              = !empty($_m['name']) ? $_m['name'] : '';
                    $_fname             = $this->plugin->utils_string->firstName($_name, $_email);
                    $_lname             = $this->plugin->utils_string->lastName($_name);
                    $addresses[$_email] = (object) ['fname' => $_fname, 'lname' => $_lname, 'email' => $_email];
                    continue; // Inside brackets; all done here.
                }
            }
            if ($_address && strpos($_address, '@', 1) !== false && (!$strict || is_email($_address))) {
                $_email             = strtolower($_address);
                $_fname             = $this->plugin->utils_string->firstName('', $_email);
                $_lname             = ''; // Not possible in this case.
                $addresses[$_email] = (object) ['fname' => $_fname, 'lname' => $_lname, 'email' => $_email];
            }
        }
        unset($_address, $_m, $_email, $_name, $_fname, $_lname); // Housekeeping.

        finale: // Target point; grand finale w/ return handlers.

        if ($emails_only) { // Return emails only?
            $address_emails = [];

            foreach ($addresses as $_email_key => $_address) {
                $address_emails[$_email_key] = $_address->email;
            }
            unset($_email_key, $_address); // Housekeeping.

            return $address_emails ? array_unique($address_emails) : [];
        }
        return $addresses ? $this->plugin->utils_array->uniqueDeep($addresses) : [];
    }

    /**
     * Parses headers deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed  $value          Input value w/ headers.
     * @param string $from_name      From name; by reference.
     * @param string $from_email     From address; by reference.
     * @param string $reply_to_email Reply-to address; by reference.
     * @param array  $recipients     Recipients; by reference.
     * @param bool   $strict         Optional. Defaults to `FALSE` (faster).
     *                               This is related to the parsing of `$recipients`. See {@link parse_recipients_deep()}.
     *
     * @return array Unique/associative array of all parsed headers.
     */
    public function parseHeadersDeep($value, &$from_name, &$from_email, &$reply_to_email, array &$recipients, $strict = false)
    {
        $headers = []; // Initialize.

        if (is_array($value) || is_object($value)) {
            foreach ($value as $_key => $_value) {
                if (is_string($_key) && is_string($_value)) { // Associative array?
                    $headers = array_merge($headers, $this->parseHeadersDeep($_key.': '.$_value, $from_name, $from_email, $reply_to_email, $recipients));
                } else {
                    $headers = array_merge($headers, $this->parseHeadersDeep($_value, $from_name, $from_email, $reply_to_email, $recipients));
                }
            }
            unset($_key, $_value); // A little housekeeping.

            goto finale; // Return handlers.
        }
        $value = trim((string) $value); // Force string value.

        foreach (explode("\r\n", $value) as $_rn_delimited_header) {
            if (strpos($_rn_delimited_header, ':') === false) {
                continue; // Invalid header.
            }
            list($_header, $_value) = explode(':', $_rn_delimited_header, 2);
            if (!($_header = trim($_header)) || !strlen($_value = trim($_value))) {
                continue; // No header; no empty value.
            }
            switch (strtolower($_header)) { // Deal w/ special headers.
                case 'content-type': // A `Content-Type` header?

                    // This is unsupported in our SMTP class.
                    // All emails are sent with a `UTF-8` charset.
                    // All emails are sent as HTML w/ a plain text fallback.

                    break; // Break switch handler.

                case 'from': // Custom `From:` header?

                    if (($_from_addresses = $this->parseAddressesDeep($_value))) {
                        $_from      = array_pop($_from_addresses); // Just one.
                        $from_name  = trim($_from->fname.' '.$_from->lname);
                        $from_email = $_from->email; // By reference.
                    }
                    unset($_from_addresses, $_from); // Housekeeping.

                    break; // Break switch handler.

                case 'reply-to': // Custom `Reply-To` header?

                    if (($_reply_to_emails = $this->parseAddressesDeep($_value, false, true))) {
                        $_reply_to_email = array_pop($_reply_to_emails);  // Just one.
                        $reply_to_email  = $_reply_to_email; // By reference.
                    }
                    unset($_reply_to_emails, $_reply_to_email); // Housekeeping.

                    break; // Break switch handler.

                case 'cc':  // A `CC:` header; i.e. carbon copies?
                case 'bcc': // A `BCC:` header; i.e. blind carbon copies?

                    if (($_cc_bcc_emails = $this->parseAddressesDeep($_value, $strict, true))) {
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

        return $headers ? array_unique($headers) : [];
    }

    /**
     * Parses attachments deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $value Any input value w/ attachments.
     *
     * @return array Unique/associative array of all attachments.
     */
    public function parseAttachmentsDeep($value)
    {
        $attachments = []; // Initialize.

        if (is_array($value) || is_object($value)) {
            foreach ($value as $_key => $_value) {
                $attachments = array_merge($attachments, $this->parseAttachmentsDeep($_value));
            }
            unset($_key, $_value); // Housekeeping.

            goto finale; // Return handlers.
        }
        if (($value = trim((string) $value)) && is_file($value)) {
            $attachments[$value] = $value; // Only one here.
        }
        finale: // Target point; grand finale w/ return handlers.

        return $attachments ? array_unique($attachments) : [];
    }

    /**
     * Formats the name of a header.
     *
     * @since 150619 Improving custom headers.
     *
     * @param string $header Input header name; lowercase.
     *
     * @return string The `Output-Header-Name`.
     */
    public function ucwordsHeader($header)
    {
        if (!($header = trim((string) $header))) {
            return $header; // Nothing.
        }
        $header = strtolower($header);

        if (strpos($header, '-') === false) {
            return ucfirst($header);
        }
        $words_in_header         = explode('-', $header);
        $ucfirst_words_in_header = array_map('ucfirst', $words_in_header);

        return implode('-', $ucfirst_words_in_header);
    }
}
