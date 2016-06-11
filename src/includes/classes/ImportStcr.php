<?php
/**
 * StCR Importer.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * StCR Importer.
 *
 * @since 141111 First documented version.
 */
class ImportStcr extends AbsBase
{
    /**
     * @type int Max number of post IDs.
     *
     * @since 141111 First documented version.
     */
    protected $max_post_ids_limit;

    /**
     * @type array Unimported post IDs.
     *
     * @since 141111 First documented version.
     */
    protected $unimported_post_ids;

    /**
     * @type array Imported post IDs.
     *
     * @since 141111 First documented version.
     */
    protected $imported_post_ids;

    /**
     * @type int Total imported post IDs.
     *
     * @since 141111 First documented version.
     */
    protected $total_imported_post_ids;

    /**
     * @type int Total imported subs.
     *
     * @since 141111 First documented version.
     */
    protected $total_imported_subs;

    /**
     * @type int Total created subs.
     *
     * @since 151224 Improving StCR import count results
     */
    protected $total_created_subs;

    /**
     * @type int Total skipped subscriptions during import.
     *
     * @since 151224 Improving StCR import count results
     */
    protected $total_skipped_subs;

    /**
     * @type bool Has more posts to import?
     *
     * @since 141111 First documented version.
     */
    protected $has_more_posts_to_import;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Arguments to the constructor.
     *                            These should NOT be trusted; they come from a `$_REQUEST` action.
     */
    public function __construct(array $request_args = [])
    {
        parent::__construct();

        $default_request_args = [
            'max_post_ids_limit' => 15,
        ];
        $request_args = array_merge($default_request_args, $request_args);
        $request_args = array_intersect_key($request_args, $default_request_args);

        $this->max_post_ids_limit = (integer) $request_args['max_post_ids_limit'];

        if ($this->max_post_ids_limit < 1) {
            $this->max_post_ids_limit = 1; // At least one.
        }
        $upper_max_post_ids_limit = (integer) apply_filters(__CLASS__.'_upper_max_post_ids_limit', 1000);
        if ($this->max_post_ids_limit > $upper_max_post_ids_limit) {
            $this->max_post_ids_limit = $upper_max_post_ids_limit;
        }
        $this->has_more_posts_to_import = false; // Initialize.
        $this->unimported_post_ids      = $this->unimportedPostIds($this->max_post_ids_limit + 1);

        if (count($this->unimported_post_ids) > $this->max_post_ids_limit) {
            $this->has_more_posts_to_import = true; // Yes, there are more to import later.
            $this->unimported_post_ids      = array_slice($this->unimported_post_ids, 0, $this->max_post_ids_limit);
        }
        $this->imported_post_ids       = []; // Initialize.
        $this->total_imported_post_ids = $this->total_imported_subs = $this->total_created_subs = $this->total_skipped_subs = 0;

        $this->maybeImport(); // Handle importation.
    }

    /**
     * Import processor.
     *
     * @since 141111 First documented version.
     */
    protected function maybeImport()
    {
        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        foreach (($notices = is_array($notices = get_option(GLOBAL_NS.'_notices')) ? $notices : []) as $_key => $_notice) {
            if (!empty($_notice['persistent_id']) && $_notice['persistent_id'] === 'upgrading-from-stcr') {
                unset($notices[$_key]); // Remove this one! :-)
            }
        }
        unset($_key, $_notice); // Housekeeping.

        update_option(GLOBAL_NS.'_notices', $notices); // Update notices.

        foreach ($this->unimported_post_ids as $_post_id) {
            ++$this->total_imported_post_ids;
            $this->imported_post_ids[] = $_post_id;

            $this->markPostImported($_post_id);
            $this->maybeImportPost($_post_id);
        }
        unset($_post_id); // Housekeeping.

        $this->outputStatus();
    }

    /**
     * Mark as imported post ID.
     *
     * @since 141111 First documented version.
     *
     * @param int $post_id Post ID.
     */
    protected function markPostImported($post_id)
    {
        if (!($post_id = (integer) $post_id)) {
            return; // Nothing to do.
        }
        update_post_meta($post_id, GLOBAL_NS.'_imported_stcr_subs', '1');
    }

    /**
     * Post import processor.
     *
     * @since 141111 First documented version.
     *
     * @param int $post_id Post ID.
     */
    protected function maybeImportPost($post_id)
    {
        if (!($post_id = (integer) $post_id)) {
            return; // Not possible.
        }
        if (!($stcr_subs = $this->stcrSubsForPost($post_id))) {
            $this->logFailure('Failed to insert subscriptions for Post; no StCR subscribers found', ['post_id' => $post_id]);
            return; // No StCR subscribers.
        }
        foreach ($stcr_subs as $_email => $_sub) {
            $this->maybeImportSub($post_id, $_sub);
        }
        unset($_email, $_sub); // Housekeeping.
    }

    /**
     * Sub. import processor.
     *
     * @since 141111 First documented version.
     *
     * @param int       $post_id Post ID.
     * @param \stdClass $sub     Subscriber obj. data.
     */
    protected function maybeImportSub($post_id, \stdClass $sub)
    {
        if (!($post_id = (integer) $post_id)) {
            return; // Not possible.
        }
        if (empty($sub->email) || empty($sub->time) || empty($sub->status)) {
            $this->logFailure('Not importing subscription; data missing', $sub);
            return; // Not possible; data missing.
        }
        if ($sub->status !== 'Y' && $sub->status !== 'R') {
            $this->logFailure('Not importing subscription; not an active subscriber', $sub);
            return; // Not an active subscriber.
        }
        if ($sub->status === 'Y') {
            // All comments?

            $sub_insert_data = [
                'post_id' => $post_id,

                'status'  => 'subscribed',
                'deliver' => 'asap',

                'fname' => $sub->fname,
                'email' => $sub->email,

                'insertion_time' => $sub->time,
            ];
            $sub_inserter = new SubInserter($sub_insert_data);

            if ($sub_inserter->didInsert()) {
                ++$this->total_imported_subs;
                ++$this->total_created_subs;
            } else {
                $this->logFailure('Failed to insert an All Comments (Y) subscription', array_merge($sub_insert_data, $sub_inserter->errors()));
                ++$this->total_skipped_subs;
            }
        } else { // Otherwise, specific comment(s) only; i.e. "Replies Only".
            $_sub_comment_ids = $this->subCommentIds($post_id, $sub->email);

            if (!empty($_sub_comment_ids)) {
                /*
                 * This is where the behavior of Comment Mail and StCR diverge when it comes to how they store subscriptions.
                 * StCR only stores one (1) `R` subscription per email per Post ID, while Comment Mail creates a Replies Only subscription
                 * for each comment the user has posted on a given Post ID. That means the Total StCR Subscriptions imported will
                 * likely be much lower than the total subscriptions created by Comment Mail. See also: http://bit.ly/1QtwEWO
                 *
                 * Note how we count imported subs outside of this foreach loop, but we count created subs inside the foreach loop.
                 */
                ++$this->total_imported_subs;

                foreach ($_sub_comment_ids as $_comment_id) {
                    $_sub_insert_data = [
                        'post_id'    => $post_id,
                        'comment_id' => $_comment_id,

                        'status'  => 'subscribed',
                        'deliver' => 'asap',

                        'fname' => $sub->fname,
                        'email' => $sub->email,

                        'insertion_time' => $sub->time,
                    ];
                    $_sub_inserter = new SubInserter($_sub_insert_data);
                    if ($_sub_inserter->didInsert()) {
                        ++$this->total_created_subs;
                    } else {
                        $this->logFailure('Failed to import Replies Only (R) subscription', array_merge($_sub_insert_data, $_sub_inserter->errors()));
                        ++$this->total_skipped_subs;
                        --$this->total_imported_subs; // Imported subs are counted outside this foreach loop, so we need to decrease here when we have a failure.
                    }
                }
            } else { // No comments associated with $sub->email were found for $post_id
                $this->logFailure('Failed to import Replies Only (R) subscription', ['note' => 'Associated comment has been deleted, trashed, or marked as spam', 'post_id' => $post_id, 'email' => $sub->email]);
                ++$this->total_skipped_subs;
            }
            unset($_comment_id, $_sub_insert_data, $_sub_inserter, $_sub_comment_ids); // Housekeeping.
        }
    }

    /**
     * Collect all StCR subscribers for a given post ID.
     *
     * @since 141111 First documented version.
     *
     * @param int $post_id Subscribers for which post ID.
     *
     * @return \stdClass[] Array of objects; i.e. StCR subscribers for the post ID.
     *
     *    Each object in the array will contain the following properties.
     *
     *    - `(string)fname` The subscriber's first name (based on email address).
     *
     *    - `(string)email` The subscriber's email address (lowercase).
     *          Note: each key in the array is also indexed by this email address.
     *
     *    - `(integer)time` The date the subscription was created; converted to a UTC timestamp.
     *
     *    - `(string)status` The status of the subscription. One of: `Y|R`.
     *          A `Y` indicates they want notifications for all comments.
     *          An `R` indicates they want notifications for replies only.
     */
    protected function stcrSubsForPost($post_id)
    {
        if (!($post_id = (integer) $post_id)) {
            return []; // Not possible.
        }
        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->wp->postmeta).'`'.

               " WHERE `post_id` = '".esc_sql($post_id)."'".
               " AND `meta_key` LIKE '%\\_stcr@\\_%'";

        if (!($results = $this->plugin->utils_db->wp->get_results($sql))) {
            $this->logFailure('No subscriptions for this Post ID', ['post_id' => $post_id]);
            return []; // Nothing to do; no results.
        }
        $subs = []; // Initialize array of subscribers.

        foreach ($results as $_result) { // Iterate results.

            // Original format: `_stcr@_user@example.com`.
            $_email = preg_replace('/^.*?_stcr@_/i', '', $_result->meta_key);
            $_email = trim(strtolower($_email));

            if (!$_email || strpos($_email, '@', 1) === false || !is_email($_email)) {
                $this->logFailure('Invalid Email Address', ['email' => $_email, $this->plugin->utils_db->wp->postmeta.'.meta_id' => $_result->meta_id, 'post_id' => $post_id]);
                ++$this->total_skipped_subs;
                continue; // Invalid email address.
            }
            // Original format: `2013-03-11 01:31:01|R`.
            if (!$_result->meta_value || strpos($_result->meta_value, '|', 1) === false) {
                $this->logFailure('Invalid meta data', ['email' => $_email, 'meta_value' => $_result->meta_value, $this->plugin->utils_db->wp->postmeta.'.meta_id' => $_result->meta_id, 'post_id' => $post_id]);
                ++$this->total_skipped_subs;
                continue; // Invalid meta data.
            }
            list($_local_datetime, $_status) = explode('|', $_result->meta_value);

            if (!($_time = strtotime($_local_datetime))) {
                $this->logFailure('Date not strtotime() compatible', ['email' => $_email, 'date' => $_local_datetime, $this->plugin->utils_db->wp->postmeta.'.meta_id' => $_result->meta_id, 'post_id' => $post_id]);
                ++$this->total_skipped_subs;
                continue; // Not `strtotime()` compatible.
            }
            if (($_time = $_time + (get_option('gmt_offset') * 3600)) < 1) {
                $this->logFailure('Unable to convert date to UTC timestamp', ['email' => $_email, 'date' => $_time, $this->plugin->utils_db->wp->postmeta.'.meta_id' => $_result->meta_id, 'post_id' => $post_id]);
                ++$this->total_skipped_subs;
                continue; // Unable to convert date to UTC timestamp.
            }
            // Possible statuses: `Y|R|YC|RC|C|-C`.
            // A `Y` indicates they want notifications for all comments.
            // An `R` indicates they want notifications for replies only.
            // A `C` indicates "suspended" or "unconfirmed".
            if ($_status !== 'Y' && $_status !== 'R') {
                // Active?
                $this->logFailure('Ignoring this subscription; not an active status (Y or R)', ['email' => $_email, 'status' => $_status, $this->plugin->utils_db->wp->postmeta.'.meta_id' => $_result->meta_id, 'post_id' => $post_id]);
                ++$this->total_skipped_subs;
                continue; // Not an active subscriber.
            }
            if (isset($subs[$_email])) { // Only when we've already found a subscription for this email in a previous iteration; this if-block MUST come before the next section
                if ($subs[$_email]->status === 'Y' && $_status === 'R') { // We're going to overwrite a `Y` subscription with an `R` subscription in the next section
                    $this->logFailure('Skipping this subscription', ['note' => 'A Replies Only (R) subscription already exists for this Post ID; see http://bit.ly/1RqXCyD', 'email' => $_email, 'status' => 'Y', 'post_id' => $post_id]);
                    ++$this->total_skipped_subs;
                } elseif ($subs[$_email]->status === 'R' && $_status === 'R') { // We're going to skip an `R` subscription in the next section because we already have one
                    $this->logFailure('Skipping this subscription', ['note' => 'A Replies Only (R) subscription already exists for this Post ID; see http://bit.ly/1RqXCyD', 'email' => $_email, 'status' => 'R', 'post_id' => $post_id]);
                    ++$this->total_skipped_subs;
                }
            }
            // Note: This section might overwrite a previously found `Y` subscription, or skip an existing `Y` or `R` subscription
            if (!isset($subs[$_email]) || ($_status === 'R' && $subs[$_email]->status === 'Y')) {
                // Give precedence to any subscription that chose to receive "Replies Only".
                // See: <https://github.com/websharks/comment-mail/issues/7#issuecomment-57252200>
                $subs[$_email] = (object) [
                    'fname' => $this->plugin->utils_string->firstName('', $_email),
                    'email' => $_email, 'time' => $_time, 'status' => $_status,
                ];
            }
        }
        unset($_result, $_email, $_local_datetime, $_status); // Housekeeping.

        return $subs; // Subscribers, for this post ID.
    }

    /**
     * Subscriber's comment IDs.
     *
     * @since 141111 First documented version.
     *
     * @param int    $post_id Post ID to check.
     * @param string $email   Email address (i.e. subscriber).
     *
     * @return array Subscriber's comment IDs.
     */
    protected function subCommentIds($post_id, $email)
    {
        $comment_ids = []; // Initialize.

        if (!($post_id = (integer) $post_id) || !($email = (string) $email)) {
            $this->logFailure('Can\'t get subscriber\'s comment IDs', ['post_id' => $post_id, 'email' => $email]);
            return $comment_ids; // Not possible; data missing.
        }
        $sql = 'SELECT `comment_ID` FROM  `'.esc_sql($this->plugin->utils_db->wp->comments).'`'.

               " WHERE  `comment_post_ID` = '".esc_sql($post_id)."'".
               " AND  `comment_author_email` = '".esc_sql($email)."'".
               " AND `comment_approved`  NOT IN ('trash', 'post-trashed', 'spam', 'delete')".

               ' ORDER BY `comment_date` ASC'; // Oldest to newest.

        $comment_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql));

        return $comment_ids; // All of their comment IDs.
    }

    /**
     * Up to `$max_limit` unimported post IDs.
     *
     * @since 141111 First documented version.
     *
     * @param int $max_limit Max IDs to return.
     *
     * @return array Up to `$max_limit` unimported post IDs.
     */
    protected function unimportedPostIds($max_limit = 0)
    {
        if (($max_limit = (integer) $max_limit) < 1) {
            $max_limit = $this->max_post_ids_limit + 1;
        }
        $post_ids_with_stcr_meta = // Those with StCR metadata.
            'SELECT DISTINCT `post_id` FROM `'.esc_sql($this->plugin->utils_db->wp->postmeta).'`'.
            " WHERE `meta_key` LIKE '%\\_stcr@\\_%'";

        $post_ids_imported_already = // Those already imported by this class.
            'SELECT DISTINCT `post_id` FROM `'.esc_sql($this->plugin->utils_db->wp->postmeta).'`'.
            " WHERE `meta_key` = '".esc_sql(GLOBAL_NS.'_imported_stcr_subs')."'";

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->wp->posts).'`'.

               " WHERE `post_status` = 'publish'".// Published posts only.
               " AND `post_type` NOT IN('revision', 'nav_menu_item', 'redirect', 'snippet')".

               ' AND `ID` IN ('.$post_ids_with_stcr_meta.')'.
               ' AND `ID` NOT IN ('.$post_ids_imported_already.')'.

               ' LIMIT '.$max_limit; // Limit results.

        $post_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql));

        return $post_ids; // Up to `$max_limit` unimported post IDs.
    }

    /**
     * Output status; for public API use.
     *
     * @since 141111 First documented version.
     */
    protected function outputStatus()
    {
        $this->plugin->utils_env->prepForOutput();

        status_header(200); // OK status.
        nocache_headers(); // No browser cache.
        header('Content-Type: text/html; charset=UTF-8');

        $child_status_var = // Child identifier.
            str_replace('\\', '_', __CLASS__).'_child_status';

        $child_status_request_args = [
            $child_status_var => 1, // Child process identifier.
            GLOBAL_NS         => ['import' => ['type' => 'stcr']],
        ];
        $child_status_url = $this->plugin->utils_url->nonce();
        $child_status_url = add_query_arg(urlencode_deep($child_status_request_args), $child_status_url);

        if (!empty($_REQUEST[$child_status_var])) {
            exit($this->childOutputStatus());
        }
        exit($this->parentOutputStatus($child_status_url));
    }

    /**
     * Parent output status.
     *
     * @since 141111 First documented version.
     *
     * @param string $child_status_url Child status URL.
     *
     * @return string HTML markup for the status.
     */
    protected function parentOutputStatus($child_status_url)
    {
        $status = '<!DOCTYPE html>'."\n";
        $status .= '<html>'."\n";

        $status .= '   <head>'."\n";

        $status .= '      <meta charset="UTF-8" />'."\n";
        $status .= '      <title>'.esc_html(__('StCR Importer', 'comment-mail')).'</title>'."\n";

        $status .= '      <style type="text/css">'."\n";
        $status .= '         body { background: #CCCCCC; color: #000000; }'."\n";
        $status .= '         body { font-size: 18px; line-height: 1em; font-family: Georgia, serif; }'."\n";
        $status .= '         body { padding: .5em; text-align: center; }'."\n";
        $status .= '      </style>'."\n";

        $status .= '      <script type="text/javascript"'.// jQuery dependency.
                   '         src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js">'.
                   '      </script>'."\n";

        $status .= '      <script type="text/javascript">'."\n";
        $status .= '         function updateCounters(childTotalPostIds, childTotalSubs, childTotalSkippedSubs, childTotalCreatedSubs)'."\n".
                   '            {'."\n".
                   '               var $totalImportedPostIds = $("#total-imported-post-ids");'."\n".
                   '               var $totalImportedSubs = $("#total-imported-subs");'."\n".
                   '               var $totalSkippedSubs = $("#total-skipped-subs");'."\n".
                   '               var $totalCreatedSubs = $("#total-created-subs");'."\n".

                   '               $totalImportedPostIds.html(Number($totalImportedPostIds.text()) + Number(childTotalPostIds));'."\n".
                   '               $totalImportedSubs.html(Number($totalImportedSubs.text()) + Number(childTotalSubs));'."\n".
                   '               $totalSkippedSubs.html(Number($totalSkippedSubs.text()) + Number(childTotalSkippedSubs));'."\n".
                   '               $totalCreatedSubs.html(Number($totalCreatedSubs.text()) + Number(childTotalCreatedSubs));'."\n".
                   '            }'."\n";
        $status .= '         function importComplete(additionalSkippedSubs)'."\n".
                   '            {'."\n".
                   '               var $totalSkippedSubs = $("#total-skipped-subs");'."\n".
                   '               $totalSkippedSubs.html(Number($totalSkippedSubs.text()) + Number(additionalSkippedSubs));'."\n".
                   '               $("#importing").remove();'."\n".// Removing importing div/animation.
                   '               $("body").append("<div>'.sprintf(__('<strong>Import complete!<strong> (<a href=\'%1$s\' target=\'_parent\'>view list of all subscriptions</a>)', 'comment-mail'), esc_attr($this->plugin->utils_url->subsMenuPageOnly())).'</div>");'."\n".
                   '            }'."\n";
        $status .= '      </script>'."\n";

        $status .= '   </head>'."\n"; // End `<head>`.

        $status .= '   <body>'."\n"; // Main output status.

        if ($this->has_more_posts_to_import) { // Import will continue w/ child processes?
            $status .= '   <div id="importing">'.
                       '      <strong>'.__('Importing StCR Subscribers', 'comment-mail').'</strong>'.
                       '       &nbsp;&nbsp; <img src="'.esc_html($this->plugin->utils_url->to('/src/client-s/images/tiny-progress-bar.gif')).'"'.
                       '                        style="width:16px; height:11px; border:0; vertical-align:middle;" />'.
                       '   </div>'."\n";
        }
        $status .= '      <code id="total-imported-post-ids">'.esc_html($this->total_imported_post_ids).'</code> '.__('post IDs', 'comment-mail').';'.
                   '      <code id="total-imported-subs">'.esc_html($this->total_imported_subs).'</code> '.__('subscriptions', 'comment-mail').
                   '      (<code id="total-skipped-subs">'.esc_html($this->total_skipped_subs).'</code> '.__('skipped', 'comment-mail').';'.
                   '      <code id="total-created-subs">'.esc_html($this->total_created_subs).'</code> '.__('created', 'comment-mail').').'."\n";

        if ($this->has_more_posts_to_import) { // Import will contiue w/ child processes?
            $status .= '   <iframe src="'.esc_attr((string) $child_status_url).'" style="width:1px; height:1px; border:0; visibility:hidden;"></iframe>';
        } else {
            $status .= ' <div><strong>'.__('Import complete!', 'comment-mail').'</strong></div>';
        }
        $status .= '   </body>'."\n";

        $status .= '</html>';

        return $status; // HTML markup.
    }

    /**
     * Child output status.
     *
     * @since 141111 First documented version.
     *
     * @return string HTML markup for the status.
     */
    protected function childOutputStatus()
    {
        $status = '<!DOCTYPE html>'."\n";
        $status .= '<html>'."\n";

        $status .= '   <head>'."\n";

        $status .= '      <title>...</title>'."\n";
        $status .= '      <meta charset="UTF-8" />'."\n";

        $status .= '      <script type="text/javascript">'."\n";
        $status .= '         parent.updateCounters('.$this->total_imported_post_ids.', '.$this->total_imported_subs.', '.$this->total_skipped_subs.', '.$this->total_created_subs.');'."\n";
        $status .= '      </script>'."\n";

        if ($this->has_more_posts_to_import) {
            $status .= '   <meta http-equiv="refresh" content="1" />';
        } else { // Import complete; signal the parent output status window.
            $status .= '   <script type="text/javascript">'."\n";
            $status .= '      parent.importComplete('.$this->totalSubsWithInvalidPostIds().');'."\n";
            $status .= '   </script>'."\n";
        }
        $status .= '   </head>'."\n"; // End `<head>`.

        $status .= '   <body>'."\n"; // Child output status.
        $status .= '   </body>'."\n";

        $status .= '</html>';

        return $status; // HTML markup.
    }

    /**
     * StCR data exists?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if StCR data exists.
     */
    public static function dataExists()
    {
        $plugin = plugin(); // Need this below.

        $sql = 'SELECT `meta_id` FROM `'.esc_sql($plugin->utils_db->wp->postmeta).'`'.
               " WHERE `meta_key` LIKE '%\\_stcr@\\_%' LIMIT 1";

        return (boolean) $plugin->utils_db->wp->get_var($sql);
    }

    /**
     * Ever done an StCR import?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if ever done an StCR import.
     */
    public static function everImported()
    {
        $plugin = plugin(); // Need this below.

        $like = // e.g. LIKE `%comment\_mail\_imported\_stcr\_subs%`.
            '%'.$plugin->utils_db->wp->esc_like(GLOBAL_NS.'_imported_stcr_subs').'%';

        $sql = 'SELECT `meta_id` FROM `'.esc_sql($plugin->utils_db->wp->postmeta).'`'.
               " WHERE `meta_key` LIKE '".esc_sql($like)."' LIMIT 1";

        return (boolean) $plugin->utils_db->wp->get_var($sql);
    }

    /**
     * Delete post meta keys.
     *
     * @since 141111 First documented version.
     */
    public static function deletePostMetaKeys()
    {
        $plugin = plugin(); // Need this below.

        $like = // e.g. Delete all keys LIKE `%comment\_mail%`.
            '%'.$plugin->utils_db->wp->esc_like(GLOBAL_NS.'_imported_stcr_subs').'%';

        $sql = // This will remove our StCR import history also.
            'DELETE FROM `'.esc_sql($plugin->utils_db->wp->postmeta).'`'.
            " WHERE `meta_key` LIKE '".esc_sql($like)."'";

        $plugin->utils_db->wp->query($sql);
    }

    /**
     * Count StCR subscriptions that belong to Post IDs that no longer exist or are no longer published.
     *
     * @since 151224 Improving StCR import count results
     *
     * @return int Number of subscriptions the importer will skip due to non-existent Post IDs
     *
     * @note  This routine is used to update the total number of skipped subscriptions, as the import routine only processes subscriptions for posts that exist.
     */
    protected function totalSubsWithInvalidPostIds()
    {
        $valid_post_ids = // All valid Post IDs
            'SELECT DISTINCT `ID` FROM `'.esc_sql($this->plugin->utils_db->wp->posts).'`';

        $sql = 'SELECT COUNT(*) as `count` FROM `'.esc_sql($this->plugin->utils_db->wp->postmeta).'`'.
               " WHERE `meta_key` LIKE '%\\_stcr@\\_%'".
               ' AND `post_id` NOT IN ('.$valid_post_ids.')'; // StCR subs that belong to invalid Post IDs

        return (int) $this->plugin->utils_db->wp->get_var($sql);
    }

    /**
     * Log StCR import failures.
     *
     * @since 151224 Improving StCR import debugging.
     *
     * @param string $msg     Description of import failure
     * @param array  $details Array of key => value pairs with additional information to be logged
     *
     * @throws \Exception If log file exists already; but is NOT writable.
     */
    protected function logFailure($msg, $details = [])
    {
        $log_file = dirname(dirname(plugin_dir_path(__FILE__))).'/stcr-import-failures.log';

        if (is_file($log_file) && !is_writable($log_file)) {
            throw new \Exception(sprintf(__('StCR import log file is NOT writable: `%1$s`. Please set permissions to `644` (or higher). `666` might be needed in some cases.', 'comment-mail'), $log_file));
        }
        $log_entry = $msg."\n";
        foreach ($details as $key => $val) {
            $log_entry .= $key.': '.$val."\n";
        }
        $log_entry .= "\n";

        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}
