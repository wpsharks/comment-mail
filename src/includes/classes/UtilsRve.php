<?php
/**
 * RVE Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * RVE Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsRve extends AbsBase
{
    /**
     * Key for this webhook.
     *
     * @since 141111 First documented version.
     */
    public static function key()
    {
        $plugin = plugin();
        $class  = get_called_class();
        return $plugin->utils_enc->hmacSha256Sign($class);
    }

    /**
     * Suffixes a `Reply-To:` w/ IRT info; for replies via email.
     *
     * @since 141111 First documented version.
     *
     * @param string   $reply_to   Base `Reply-To:` email address.
     *                             e.g. `rve@mandrill.mysite.com` becomes `rve+779-84-kgjdgxr4ldqpdrgjdgxr@mandrill.mysite.com`.
     *                             An `Reply-To:` suffix always begins with a `+` sign to preserve the original mailbox name.
     * @param int      $post_id    A WP post ID.
     * @param null|int $comment_id A WP comment ID (optional).
     *                             To exclude the comment ID, use a `NULL` value; `0` has meaning.
     * @param string   $sub_key    Subscription key (optional).
     *
     * @return string Suffixed `Reply-To:` w/ IRT info; for replies via email.
     */
    public function irtSuffix($reply_to, $post_id, $comment_id = null, $sub_key = '')
    {
        if (!($reply_to = trim((string) $reply_to))) {
            return $reply_to; // Empty.
        }
        if (strpos($reply_to, '@', 1) === false) {
            return $reply_to; // Not possible.
        }
        if (!($post_id = abs((integer) $post_id))) {
            return $reply_to; // Not possible.
        }
        if (isset($comment_id)) { // Only if set; `0` has meaning.
            $comment_id = abs((integer) $comment_id);
        }
        $sub_key = trim((string) $sub_key);

        list($mailbox, $mailbox_host) = explode('@', $reply_to, 2);
        $mailbox_irt_suffix           = '+'.$post_id.(isset($comment_id) ? '-'.$comment_id : '').($sub_key ? '-'.$sub_key : '');

        return $reply_to = $mailbox.$mailbox_irt_suffix.'@'.$mailbox_host;
    }

    /**
     * Generates a reply via email IRT marker; for digests.
     *
     * @since 141111 First documented version.
     *
     * @param int      $post_id    A WP post ID.
     * @param null|int $comment_id A WP comment ID (optional).
     *                             To exclude the comment ID, use a `NULL` value; `0` has meaning.
     * @param string   $sub_key    Subscription key (optional).
     *
     * @return string Reply via email IRT marker; for digests.
     */
    public function irtMarker($post_id, $comment_id = null, $sub_key = '')
    {
        if (!($post_id = abs((integer) $post_id))) {
            return ''; // Not possible.
        }
        if (isset($comment_id)) { // Only if set; `0` has meaning.
            $comment_id = abs((integer) $comment_id);
        }
        $sub_key = trim((string) $sub_key);

        return '~rve#'.$post_id.(isset($comment_id) ? '-'.$comment_id : '').($sub_key ? '-'.$sub_key : '');
    }

    /**
     * Manual reply via email end marker; for email notifications.
     *
     * @since 141111 First documented version.
     *
     * @return string Reply via email end marker; for email notifications.
     *
     * @note  The use of `!END` is compatible with WordPress.com.
     *    This is only used manually. In fact, this method only exists so that we can easily
     *    call upon it to show a site owner the fragment needed if they choose to use it; or if they
     *    want to share it with their audience. We may also include this in any reply via email instructions.
     */
    public function manualEndDivider()
    {
        return '!END'; // Only used manually.
    }

    /**
     * Generates a reply via email end divider; for email notifications.
     *
     * @since 141111 First documented version.
     *
     * @return string Reply via email end divider; for email notifications.
     *
     * @note  The use of `!END` manually will override this automatic built-in marker.
     *    See also: {@link rve_manual_end_divider()} for further details.
     */
    public function endDivider()
    {
        return '<p style="float:left; display:none; overflow:hidden; margin:0; padding:0; font-size:0px; line-height:0px; max-height:0px; mso-hide:all;">'.
               '!~~~end-rve--- '.__('reply above this line please', 'comment-mail').' ---end-rve~~~!'.
               '</p>';
    }

    /**
     * Regex fragment for various reply via email markers/dividers/etc.
     *
     * @since 141111 First documented version.
     *
     * @param string $pattern_name The type of match we're looking for.
     *                             One of: `irt_suffix`, `irt_marker`, `manual_end_divider`, `end_divider`, `wrote_by_line`.
     *
     * @throws \exception If an invalid `$for` is passed to this routine.
     *
     * @return string Regex fragment for various reply via email markers/dividers/etc.
     */
    public function regexFragFor($pattern_name)
    {
        $pattern_name = trim(strtolower((string) $pattern_name));

        if (in_array($pattern_name, ['irt_suffix', 'irt_marker'], true)) {
            return ($pattern_name === 'irt_suffix' ? '\+' : '~rve#').

                   '(?P<post_id>[1-9][0-9]*)'.// Required post ID; always.

                   '(?Ji:'.// Both of these additional values are optional completely.
                   // We allow for multiple named sub-patterns under various conditions using the `J` modifier.
                   // CaSe-insensitive matching is enabled with the `i` modifier; for a possible sub. key.

                   // First check if we have a comment ID, and a sub key too.
                   // Note: comment ID can be `0`; indicating no specific comment ID.

                   '\-(?P<comment_id>0|[1-9][0-9]*)\-(?P<sub_key>k[a-zA-Z0-9]+)'.

                   '|'.// Or, a comment ID only.

                   '\-(?P<comment_id>0|[1-9][0-9]*)'.

                   '|'.// Or, a sub. key only.

                   '\-(?P<sub_key>k[a-z0-9]+)'.

                   ')?'.($pattern_name === 'irt_suffix' ? '@' : '\b');
        }
        if ($pattern_name === 'manual_end_divider') { // Manual end divider.
            return '(?i:\!END\b)'; // Followed by a word boundary.
        }
        if ($pattern_name === 'end_divider') { // Auto-generated end divider line.
            return '(?is:\!~{3}end\-rve\-{3}.*?\-{3}end\-rve~{3}\!)';
        }
        if ($pattern_name === 'wrote_by_line') { // Auto-generated `wrote:` line.
            return '(?i:.*?\s(?:wrote|writes|said|says)\:)'; // Variations.
        }
        throw new \exception(__('Invalid `$pattern_name`.', 'comment-mail'));
    }

    /**
     * Parses incoming details to determine what an email is in reply to.
     *
     * @since 141111 First documented version.
     *
     * @param string $reply_to_email `Reply-To:` email address.
     *                               i.e. Email address the incoming reply was sent to.
     * @param string $subject        Subject line.
     * @param string $rich_text_body Rich text body.
     *
     * @return \stdClass|null An object with the following properties.
     *
     *    • integer|null `post_id` In reply to post ID.
     *    • integer|null `comment_id` In reply to comment ID.
     *    • string|null `sub_key` A reply from a particular subscriber.
     */
    public function inReplyTo($reply_to_email, $subject, $rich_text_body)
    {
        $reply_to_email  = trim((string) $reply_to_email);
        $subject         = trim((string) $subject);
        $rich_text_body  = trim((string) $rich_text_body);
        $plain_text_body = $this->plugin->utils_string->htmlToText($rich_text_body);

        $regex_irt_suffix_frag = $this->regexFragFor('irt_suffix');
        $regex_irt_marker_frag = $this->regexFragFor('irt_marker');
        /*
         * These are filled with a certain priority given to each match location.
         *    In order of precedence, the following locations are scanned:
         *
         *    1. Plain text body w/ a single leading IRT marker.
         *    2. Reply-To address w/ a single IRT suffix.
         *    3. Subject line w/ a single IRT marker.
         *    4. Plain text body w/ a single IRT marker.
         *
         * We check each of these until we have all possible components.
         *
         * We pull whatever we can from each location. If anything is not filled by that particular location,
         * we move on to the next location and continue scanning. Data filled by a location will not be
         * overridden by data from others we scan later. Once it is set by a location, that's it!
         *
         * For instance, if a Reply-To address contains a post ID and sub key, but is missing a comment ID,
         * we will continue scanning to look for a comment ID in the subject, and then in the body too.
         * However, if other locations contain a post ID or a sub key, we ignore that data completely;
         * since that information was already obtained from locations w/ a higher precedence.
         *
         * In some cases the search may continue until we reach the last possible location, and we still get nothing.
         * It is even possible for this to return all `NULL` values. Care should be taken to validate what is found here.
         */
        $post_id = $comment_id = $sub_key = null; // Initialize.

        if (!isset($post_id, $comment_id, $sub_key)) {
            if ($plain_text_body && preg_match_all('/^'.$regex_irt_marker_frag.'/', $plain_text_body, $m, PREG_SET_ORDER) === 1) {
                //var_dump($m); // Found an IRT marker at the beginning of the text body.
                if (!isset($post_id) && isset($m[0]['post_id'][0])) {
                    $post_id = (integer) $m[0]['post_id'];
                }
                if (!isset($comment_id) && isset($m[0]['comment_id'][0])) {
                    $comment_id = (integer) $m[0]['comment_id'];
                }
                if (!isset($sub_key) && isset($m[0]['sub_key'][0])) {
                    $sub_key = $m[0]['sub_key'];
                }
            }
        }
        if (!isset($post_id, $comment_id, $sub_key)) {
            if ($reply_to_email && preg_match_all('/'.$regex_irt_suffix_frag.'/', $reply_to_email, $m, PREG_SET_ORDER) === 1) {
                // var_dump($m); // Found a single IRT suffix in the email address.
                if (!isset($post_id) && isset($m[0]['post_id'][0])) {
                    $post_id = (integer) $m[0]['post_id'];
                }
                if (!isset($comment_id) && isset($m[0]['comment_id'][0])) {
                    $comment_id = (integer) $m[0]['comment_id'];
                }
                if (!isset($sub_key) && isset($m[0]['sub_key'][0])) {
                    $sub_key = $m[0]['sub_key'];
                }
            }
        }
        if (!isset($post_id, $comment_id, $sub_key)) {
            if ($subject && preg_match_all('/'.$regex_irt_marker_frag.'/', $subject, $m, PREG_SET_ORDER) === 1) {
                //var_dump($m); // Found a single IRT marker in the subject line.
                if (!isset($post_id) && isset($m[0]['post_id'][0])) {
                    $post_id = (integer) $m[0]['post_id'];
                }
                if (!isset($comment_id) && isset($m[0]['comment_id'][0])) {
                    $comment_id = (integer) $m[0]['comment_id'];
                }
                if (!isset($sub_key) && isset($m[0]['sub_key'][0])) {
                    $sub_key = $m[0]['sub_key'];
                }
            }
        }
        if (!isset($post_id, $comment_id, $sub_key)) {
            if ($plain_text_body && preg_match_all('/'.$regex_irt_marker_frag.'/', $plain_text_body, $m, PREG_SET_ORDER) === 1) {
                //var_dump($m); // Found a single IRT marker in the text body.
                if (!isset($post_id) && isset($m[0]['post_id'][0])) {
                    $post_id = (integer) $m[0]['post_id'];
                }
                if (!isset($comment_id) && isset($m[0]['comment_id'][0])) {
                    $comment_id = (integer) $m[0]['comment_id'];
                }
                if (!isset($sub_key) && isset($m[0]['sub_key'][0])) {
                    $sub_key = $m[0]['sub_key'];
                }
            }
        }
        return (object) compact('post_id', 'comment_id', 'sub_key'); // Possibly all NULL values.
    }

    /**
     * Strips reply via email IRT markers from rich text body.
     *
     * @since 141111 First documented version.
     *
     * @param string $rich_text_body Rich text body.
     *
     * @return string Rich text body w/ IRT markers stripped away.
     */
    public function stripIrtMarkers($rich_text_body)
    {
        if (!($rich_text_body = trim((string) $rich_text_body))) {
            return $rich_text_body; // Empty.
        }
        $regex_irt_marker_frag = $this->regexFragFor('irt_marker');

        $regex_irt_markers = // IRT markers in rich text body.

            '/'.// Open regex; markers can appear anywhere.

            '(?:\s*\<[^\/<>]+\>\s*)*'.// Any HTML open tags wrapping the marker.

            '\s*'.$regex_irt_marker_frag.'\s*'.// Including any surrounding whitespace.

            '(?:\s*\<\/[^<>]+\>\s*)*'.// Any closing tags wrapping the marker.

            '/';
        return preg_replace($regex_irt_markers, '', $rich_text_body);
    }

    /**
     * Strips `wrote:` by line.
     *
     * @since 150619 Improving RVE handler.
     *
     * @param string $rich_text_body Rich text body.
     *
     * @return string Rich text body w/ `wrote:` stripped away.
     */
    public function stripWroteByLine($rich_text_body)
    {
        if (!($rich_text_body = trim((string) $rich_text_body))) {
            return $rich_text_body; // Empty.
        }
        $regex_wrote_by_line_frag = $this->regexFragFor('wrote_by_line');

        $regex_wrote_by_line = // Last line w/ `wrote:` in rich text body.

            '/'.// Open regex; let's find a trailing `wrote:` by line.

            '(?:\s*\<[^\/<>]+\>\s*)*'.// Any HTML open tags wrapping it up.

            '\s*'.$regex_wrote_by_line_frag.'\s*'.// Any surrounding whitespace.

            '(?:\s*\<\/[^<>]+\>\s*)*'.// Any closing tags wrapping it up.

            '$/'; // End of the string (very important in this case).

        return preg_replace($regex_wrote_by_line, '', $rich_text_body);
    }

    /**
     * Sanitizes reply via email message body.
     *
     * @since 141111 First documented version.
     *
     * @param string $rich_text_body Rich text body.
     *
     * @return \stdClass An object with two properties, as follows:
     *
     *    • boolean `force_moderation` We should force moderation on this reply.
     *       This will be `TRUE` if we were unable to find a valid end divider.
     *
     *    • string `sanitized_rich_text_body` The sanitized rich text body.
     *       If we were unable to find a valid end divider, this may still needing cleaning.
     *       In such a case, `force_moderation` will be `TRUE` of course.
     */
    public function sanitizeRichTextBody($rich_text_body)
    {
        if (!($rich_text_body = trim((string) $rich_text_body))) {
            return $rich_text_body; // Empty.
        }
        $regex_manual_end_divider_frag = $this->regexFragFor('manual_end_divider');
        $regex_end_divider_frag        = $this->regexFragFor('end_divider');

        // Note: the use of `!END` is compatible with WordPress.com.

        $regex_manual_end_divider = // Manual end divider.

            '/'.// Open regex; this divider can appear anywhere.

            '(?:\s*\<[^\/<>]+\>\s*)*'.// Any HTML open tags wrapping the divider.

            '\s*'.$regex_manual_end_divider_frag.'\s*'.// Including any surrounding whitespace.

            '(?:\s*\<\/[^<>]+\>\s*)*'.// Any closing tags wrapping the divider.

            '/i'; // End of divider pattern.

        $regex_end_divider = // Auto-generated end divider.

            '/'.// Open regex; this divider can appear anywhere.

            '(?:\s*\<[^\/<>]+\>\s*)*'.// Any HTML open tags wrapping the divider.

            '\s*'.$regex_end_divider_frag.'\s*'.// Including any surrounding whitespace.

            '(?:\s*\<\/[^<>]+\>\s*)*'.// Any closing tags wrapping the divider.

            '/is'; // End of divider pattern.

        $rich_text_body = $this->stripIrtMarkers($rich_text_body);

        if (preg_match($regex_manual_end_divider, $rich_text_body)) {
            $force_moderation               = false; // Found end divider, no need to moderate.
            list($sanitized_rich_text_body) = preg_split($regex_manual_end_divider, $rich_text_body, 2);
            $sanitized_rich_text_body       = $this->stripWroteByLine($sanitized_rich_text_body);
            $sanitized_rich_text_body       = $this->plugin->utils_string->trimHtml($sanitized_rich_text_body);
        } elseif (preg_match($regex_end_divider, $rich_text_body)) {
            $force_moderation               = false; // Found end divider, no need to moderate.
            list($sanitized_rich_text_body) = preg_split($regex_end_divider, $rich_text_body, 2);
            $sanitized_rich_text_body       = $this->stripWroteByLine($sanitized_rich_text_body);
            $sanitized_rich_text_body       = $this->plugin->utils_string->trimHtml($sanitized_rich_text_body);
        } else { // If unable to find a valid end divider; force moderation on this reply.
            $force_moderation         = true; // Force moderation in this case.
            $sanitized_rich_text_body = $rich_text_body; // Initialize sanitized form.
            $sanitized_rich_text_body = $this->plugin->utils_string->trimHtml($sanitized_rich_text_body);
        }
        return (object) compact('force_moderation', 'sanitized_rich_text_body');
    }

    /**
     * Filters `pre_comment_approved` value in WordPress.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Input arguments from an RVE handler.
     */
    public function maybePostComment(array $args)
    {
        $default_args = [
            'reply_to_email' => '',

            'from_name'  => '',
            'from_email' => '',

            'subject' => '',

            'rich_text_body' => '',

            'force_status' => '',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $reply_to_email = trim((string) $args['reply_to_email']);

        $from_name  = trim((string) $args['from_name']);
        $from_email = trim((string) $args['from_email']);

        $subject = trim((string) $args['subject']);

        $rich_text_body = trim((string) $args['rich_text_body']);

        $force_status = trim((string) $args['force_status']);

        if ($force_status === '0') {
            $force_status = 0;
        }
        if (!in_array($force_status, [0, 'spam'], true)) {
            $force_status = null; // `0` or `spam` only.
        }
        # Populate as many variables as we possibly can.

        $sub         = $comment         = null; // Initialize these; needed below.
        $in_reply_to = $this->inReplyTo($reply_to_email, $subject, $rich_text_body);

        $post_id    = (integer) $in_reply_to->post_id; // In reply to post ID.
        $comment_id = (integer) $in_reply_to->comment_id; // Comment ID.
        $sub_key    = (string) $in_reply_to->sub_key; // By sub key.

        if ($comment_id) { // In reply to a specific comment ID?
            $comment = get_comment($comment_id); // Try to acquire.
        }
        if (!$post_id && $comment) { // Use comment post ID?
            $post_id = (integer) $comment->comment_post_ID;
        }
        if ($sub_key) { // If sub key is known, get subscription.
            $sub = $this->plugin->utils_sub->get($sub_key);
        }
        if (!$from_name && $sub && $sub->fname) { // Can autofill?
            $from_name = $sub->fname; // Use first name already on file.
        }
        if (!$from_email && $sub && $sub->email) { // Can autofill?
            $from_email = $sub->email; // Email address already on file.
        }
        $from_name = $this->plugin->utils_string->cleanName($from_name);
        $from_ip   = $sub ? $sub->last_ip : ''; // Use last known IP if possible.

        # Basic validation before attempting to post the comment below.

        if (!$post_id) {
            return; // Unable to determine post ID.
        }
        if ($comment_id && !$comment) {
            return; // Invalid comment ID.
        }
        if ($post_id && $comment && $post_id !== (integer) $comment->comment_post_ID) {
            return; // Post ID to comment ID mismatch in this case.
        }
        if ($sub_key && !$sub) {
            return; // Invalid subscription key given.
        }
        if (!$from_email) {
            return; // Do not post w/o an email address.
        }
        $sanitizer                = $this->sanitizeRichTextBody($rich_text_body);
        $sanitized_rich_text_body = $sanitizer->sanitized_rich_text_body;

        if ($sanitizer->force_moderation && $force_status !== 'spam') {
            $force_status = 0; // Force `0` status.
        }
        if (!$rich_text_body || !$sanitized_rich_text_body) {
            return; // Do not post an empty reply.
        }
        # Attempt to post; remaining validation performed by WP core and our filters.

        $response = wp_remote_post(
            site_url('/wp-comments-post.php'),
            [
                'user-agent' => NAME.'/'.VERSION,
                'headers'    => [
                    'REMOTE_ADDR'          => $from_ip,
                    'HTTP_X_FORWARDED_FOR' => $from_ip,
                ],
                'redirection' => 0, // Don't follow redirects.

                'body' => [
                    'comment_post_ID' => $post_id,
                    'comment_parent'  => $comment_id,

                    'author'  => $from_name,
                    'email'   => $from_email,
                    'comment' => $sanitized_rich_text_body,

                    'akismet_comment_nonce' => wp_create_nonce('akismet_comment_nonce_'.$post_id),

                    GLOBAL_NS.'_rve_key'          => static::key(), // Key identifier.
                    GLOBAL_NS.'_rve_sub_key'      => $sub_key, // Subscription key identifier.
                    GLOBAL_NS.'_rve_force_status' => $force_status, // Force a status?
                ],
            ]
        );
        if (is_wp_error($response)) { // Log for possible debugging later.
            $this->plugin->utils_log->maybeDebug($response);
        }
    }

    /**
     * Filters `comment_registration` option in WordPress.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `pre_option_comment_registration` filter; indirectly.
     *
     * @param int|string|bool $requires_logged_in_user `FALSE` if not yet defined by another filter.
     *
     * @return int|string|bool Filtered `$comment_registration` value.
     */
    public function preOptionCommentRegistration($requires_logged_in_user)
    {
        if (empty($_REQUEST[GLOBAL_NS.'_rve'])) {
            return $requires_logged_in_user;
        }
        if (!($current_uri = $this->plugin->utils_url->currentUri())) {
            return $requires_logged_in_user;
        }
        if (!preg_match('/\/wp\-comments\-post\.php(?:[?#]|$)/', $current_uri)) {
            return $requires_logged_in_user;
        }
        /*
         * This allows comments to be posted through RVE handlers w/o requiring a logged-in user.
         *    If, and only if, the RVE is submitted with a valid RVE key.
         *
         * In addition, we have a `pre_comment_approved` filter that will auto-disapprove any comment
         *    that is posted through an RVE handler which does not include valid RVE/sub keys.
         *    Please see {@link pre_comment_approved()} for further details on this.
         */
        $valid_rve_key = static::key(); // Class identifier.
        $rve_key       = trim(stripslashes((string) $_REQUEST[GLOBAL_NS.'_rve_key']));

        if (!$requires_logged_in_user) { // Not required at all anyway?
            return $requires_logged_in_user;
        }
        if ($rve_key === $valid_rve_key) { // Registration not required in this case.
            return $requires_logged_in_user = 0;
        }
        return $requires_logged_in_user; // Do not filter; invalid key.
    }

    /**
     * Filters `pre_comment_approved` value in WordPress.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `pre_comment_approved` filter.
     *
     * @param int|string $comment_status New comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     * @param array $comment_data An array of all comment data associated w/ a new comment being created.
     *
     * @return int|string Filtered `$comment_status` value.
     */
    public function preCommentApproved($comment_status, array $comment_data)
    {
        if (empty($_REQUEST[GLOBAL_NS.'_rve'])) {
            return $comment_status;
        }
        if (!($current_uri = $this->plugin->utils_url->currentUri())) {
            return $comment_status;
        }
        if (!preg_match('/\/wp\-comments\-post\.php(?:[?#]|$)/', $current_uri)) {
            return $comment_status;
        }
        /*
         * 1. If an RVE is submitted w/ a valid RVE key, and the RVE handler forces a particular status,
         *    we apply that forced status here. A forced status can be `0` or `spam` only.
         *
         * 2. If an RVE is submitted with valid RVE/sub keys, and the sub key belongs to the post author,
         *    or to someone else with the capability to manage comment subscriptions; we auto-approve the reply in this case.
         *    A sub key tells us the email address is confirmed/owned by the replyer, so we auto-approve authors/admins securely.
         *
         * 3. This will allow an already-approved reply through, iff it was posted with valid RVE/sub keys.
         *    Note that we do NOT explicitly approve the reply, we only allow an already-approved comment
         *    to get through; iff it includes a valid subscription key along with the submission.
         *
         * 4. If an RVE is submitted without valid RVE/sub keys, we force the status to `0`.
         *    In short, any RVE that we are unable to verify, will be forced into moderation here.
         */
        $valid_rve_key       = static::key(); // Class identifier.
        $rve_key             = trim(stripslashes((string) $_REQUEST[GLOBAL_NS.'_rve_key']));
        $sub_key             = trim(stripslashes($this->issetOr($_REQUEST[GLOBAL_NS.'_rve_sub_key'], '', 'string')));
        $force_status        = trim(stripslashes($this->issetOr($_REQUEST[GLOBAL_NS.'_rve_force_status'], '', 'string')));
        $current_hard_status = $this->plugin->utils_db->commentStatusI18n($comment_status);

        if ($force_status === '0') {
            $force_status = 0;
        }
        if (!in_array($force_status, [0, 'spam'], true)) {
            $force_status = null; // `0` or `spam` only.
        }
        /*
         * What to do? Several checks here for various circumstances.
         */
        if ($rve_key === $valid_rve_key && isset($force_status)) { // RVE handler forcing a status?
            return $comment_status = $force_status; // Force a `0` or `spam` status in this case.
        }
        if ($rve_key === $valid_rve_key && $sub_key && ($sub = $this->plugin->utils_sub->get($sub_key))) {
            if ($current_hard_status !== 'approve') { // If unapproved, check sub key for authors/admins.
                if (!empty($comment_data['comment_post_ID']) && ($post = get_post($comment_data['comment_post_ID']))) {
                    if (($user = \WP_User::get_data_by('email', $sub->email)) && ($user = new \WP_User($user->ID))) {
                        if ($user->ID === (integer) $post->post_author || $user->has_cap($this->plugin->manage_cap) || $user->has_cap($this->plugin->cap)) {
                            return $comment_status = 1; // Auto-approve comment from author/admin.
                        }
                    }
                }
            }
            return $comment_status; // Allow whatever WP says; posted w/ valid RVE/sub keys.
        }
        if ($current_hard_status !== 'approve') { // It's not approved anyway?
            return $comment_status; // Stick w/ whatever WP says in this case.
        }
        return $comment_status = 0; // Disapprove by default; invalid RVE/sub keys.
    }
}
