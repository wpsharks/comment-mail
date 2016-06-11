<?php
/**
 * Subscription Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Subscription Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsSub extends AbsBase
{
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
     * Sanitizes a subscription key.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_key Input subscription key.
     *
     * @return string Sanitized subscription key; else an empty string.
     *
     * @note  Numeric keys represent a security issue, since one of our utility functions
     *    may be able to accept either a key or an ID. Thus, all user-facing action handlers MUST always
     *    sanitize keys they're working with; in order to be sure keys are NOT numeric.
     *
     * @see   UtilsEnc::uunnciKey20Max()
     */
    public function sanitizeKey($sub_key)
    {
        $sub_key = trim((string) $sub_key);

        if (!$this->hasUunnciKey20MaxFormat($sub_key)) {
            $sub_key = $sub_key === '0' ? '' : 'k'.$sub_key;
        }
        return $sub_key;
    }

    /**
     * Checks a subscription key.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $sub_key Input value to check on.
     *
     * @return bool `TRUE` if the input value is in key format.
     *
     * @see   UtilsEnc::uunnciKey20Max()
     */
    public function hasUunnciKey20MaxFormat($sub_key)
    {
        if (is_string($sub_key) && !is_numeric($sub_key)) {
            if (isset($sub_key[0]) && strcasecmp($sub_key[0], 'k') === 0) {
                if (strlen($sub_key) <= 20) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Subscription key to ID.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_key Input key to convert to an ID.
     *
     * @return int The subscription ID matching the input `$sub_key`.
     *             If the `$sub_key` is not found, this returns `0`.
     */
    public function keyToId($sub_key)
    {
        if (!($sub_key = trim((string) $sub_key))) {
            return 0; // Not possible.
        }
        if (!($sub = $this->get($sub_key))) {
            return 0; // Not found.
        }
        return $sub->ID;
    }

    /**
     * Subscription key to email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_key Input key to convert to an email address.
     *
     * @return string The subscription email address matching the input `$sub_key`.
     *                If the `$sub_key` is not found, this returns an empty string.
     */
    public function keyToEmail($sub_key)
    {
        if (!($sub_key = trim((string) $sub_key))) {
            return ''; // Not possible.
        }
        if (!($sub = $this->get($sub_key))) {
            return ''; // Not found.
        }
        return strtolower($sub->email);
    }

    /**
     * Unique IDs only, from IDs/keys.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys An array of IDs/keys.
     *
     * @return array An array of unique IDs only.
     */
    public function uniqueIds(array $sub_ids_or_keys)
    {
        $unique_ids = $sub_keys = [];

        foreach ($sub_ids_or_keys as $_sub_id_or_key) {
            if (is_string($_sub_id_or_key) && $this->hasUunnciKey20MaxFormat($_sub_id_or_key)) {
                $sub_keys[] = $_sub_id_or_key; // String key.
            } elseif (is_integer($_sub_id_or_key) && $_sub_id_or_key > 0) {
                $unique_ids[] = (integer) $_sub_id_or_key;
            }
        }
        unset($_sub_id_or_key); // Housekeeping.

        foreach ($sub_keys as $_sub_key) {
            if (($_sub_id = $this->keyToId($_sub_key)) > 0) {
                $unique_ids[] = $_sub_id;
            }
        }
        unset($_sub_key, $_sub_id); // Housekeeping.

        if ($unique_ids) { // Unique IDs only.
            $unique_ids = array_unique($unique_ids);
        }
        return $unique_ids;
    }

    /**
     * Get subscription.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param bool       $no_cache      Defaults to a FALSE value.
     *                                  TRUE if you want to avoid a potentially cached value.
     *
     * @return \stdClass|null Subscription object, if possible.
     */
    public function get($sub_id_or_key, $no_cache = false)
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (is_null($cache = &$this->cacheKey(__FUNCTION__))) {
            $cache = []; // Initialize array.
        }
        if (!$no_cache && $cache && array_key_exists($sub_id_or_key, $cache)) {
            return $cache[$sub_id_or_key]; // From built-in object cache.
        }
        if ($cache && count($cache) > 2000) { // Too large?
            $this->plugin->utils_array->shuffleAssoc($cache);
            $cache = array_slice($cache, 0, 2000, true);
        }
        if (is_string($sub_id_or_key) && $this->hasUunnciKey20MaxFormat($sub_id_or_key)) {
            $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
                   " WHERE `key` = '".esc_sql($sub_id_or_key)."' LIMIT 1";
        } elseif (is_integer($sub_id_or_key) && $sub_id_or_key > 0) { // It's a subscription ID.
            $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
                   " WHERE `ID` = '".esc_sql((integer) $sub_id_or_key)."' LIMIT 1";
        }
        if (!empty($sql) && ($row = $this->plugin->utils_db->wp->get_row($sql))) {
            return $cache[(integer) $row->ID] = $cache[(string) $row->key] = $row = $this->plugin->utils_db->typifyDeep($row);
        }
        return $cache[$sub_id_or_key] = null;
    }

    /**
     * Reconfirm subscription via email.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param array      $args          Any additional behavioral args.
     *
     * @return bool|null TRUE if subscription is reconfirmed successfully.
     *                   Or, FALSE if unable to reconfirm (e.g. already confirmed).
     *                   Or, NULL on complete failure (e.g. invalid ID or key).
     */
    public function reconfirm($sub_id_or_key, array $args = [])
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (!($sub = $this->get($sub_id_or_key))) {
            return null; // Not possible.
        }
        if ($sub->status === 'deleted') {
            return null; // Not possible.
        }
        if ($sub->status === 'subscribed') {
            return false; // Confirmed already.
        }
        if (!isset($args['auto_confirm'])) {
            $args['auto_confirm'] = false;
        }
        if (!isset($args['process_confirmation'])) {
            $args['process_confirmation'] = true;
        }
        $updater = new SubUpdater(['ID' => $sub->ID, 'status' => 'unconfirmed'], $args);

        return $updater->didUpdate();
    }

    /**
     * Bulk reconfirm subscriptions via email.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys Subscription IDs/keys.
     * @param array $args            Any additional behavioral args.
     *
     * @return int Number of subscriptions reconfirmed successfully.
     */
    public function bulkReconfirm(array $sub_ids_or_keys, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($sub_ids_or_keys) as $_sub_id) {
            if ($this->reconfirm($_sub_id, $args)) {
                ++$counter; // Update counter.
            }
        }
        unset($_sub_id); // Housekeeping.

        return $counter;
    }

    /**
     * Confirm subscription.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param array      $args          Any additional behavioral args.
     *
     * @return bool|null TRUE if subscription is confirmed successfully.
     *                   Or, FALSE if unable to confirm (e.g. already confirmed).
     *                   Or, NULL on complete failure (e.g. invalid ID or key).
     */
    public function confirm($sub_id_or_key, array $args = [])
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (!($sub = $this->get($sub_id_or_key))) {
            return null; // Not possible.
        }
        if ($sub->status === 'deleted') {
            return null; // Not possible.
        }
        if ($sub->status === 'subscribed') {
            return false; // Confirmed already.
        }
        $updater = new SubUpdater(['ID' => $sub->ID, 'status' => 'subscribed'], $args);

        return $updater->didUpdate();
    }

    /**
     * Bulk confirm subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys Subscription IDs/keys.
     * @param array $args            Any additional behavioral args.
     *
     * @return int Number of subscriptions confirmed successfully.
     */
    public function bulkConfirm(array $sub_ids_or_keys, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($sub_ids_or_keys) as $_sub_id) {
            if ($this->confirm($_sub_id, $args)) {
                ++$counter; // Update counter.
            }
        }
        unset($_sub_id); // Housekeeping.

        return $counter;
    }

    /**
     * Unconfirm subscription.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param array      $args          Any additional behavioral args.
     *
     * @return bool|null TRUE if subscription is unconfirmed successfully.
     *                   Or, FALSE if unable to unconfirm (e.g. already unconfirmed).
     *                   Or, NULL on complete failure (e.g. invalid ID or key).
     */
    public function unconfirm($sub_id_or_key, array $args = [])
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (!($sub = $this->get($sub_id_or_key))) {
            return null; // Not possible.
        }
        if ($sub->status === 'deleted') {
            return null; // Not possible.
        }
        if ($sub->status === 'unconfirmed') {
            return false; // Unconfirmed already.
        }
        $updater = new SubUpdater(['ID' => $sub->ID, 'status' => 'unconfirmed'], $args);

        return $updater->didUpdate();
    }

    /**
     * Bulk unconfirm subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys Subscription IDs/keys.
     * @param array $args            Any additional behavioral args.
     *
     * @return int Number of subscriptions unconfirmed successfully.
     */
    public function bulkUnconfirm(array $sub_ids_or_keys, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($sub_ids_or_keys) as $_sub_id) {
            if ($this->unconfirm($_sub_id, $args)) {
                ++$counter; // Update counter.
            }
        }
        unset($_sub_id); // Housekeeping.

        return $counter;
    }

    /**
     * Suspend subscription.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param array      $args          Any additional behavioral args.
     *
     * @return bool|null TRUE if subscription is suspended successfully.
     *                   Or, FALSE if unable to suspend (e.g. already suspended).
     *                   Or, NULL on complete failure (e.g. invalid ID or key).
     */
    public function suspend($sub_id_or_key, array $args = [])
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (!($sub = $this->get($sub_id_or_key))) {
            return null; // Not possible.
        }
        if ($sub->status === 'deleted') {
            return null; // Not possible.
        }
        if ($sub->status === 'suspended') {
            return false; // Suspended already.
        }
        $updater = new SubUpdater(['ID' => $sub->ID, 'status' => 'suspended'], $args);

        return $updater->didUpdate();
    }

    /**
     * Bulk suspend subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys Subscription IDs/keys.
     * @param array $args            Any additional behavioral args.
     *
     * @return int Number of subscriptions suspended successfully.
     */
    public function bulkSuspend(array $sub_ids_or_keys, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($sub_ids_or_keys) as $_sub_id) {
            if ($this->suspend($_sub_id, $args)) {
                ++$counter; // Update counter.
            }
        }
        unset($_sub_id); // Housekeeping.

        return $counter;
    }

    /**
     * Trash subscription.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param array      $args          Any additional behavioral args.
     *
     * @return bool|null TRUE if subscription is trashed successfully.
     *                   Or, FALSE if unable to trash (e.g. already trashed).
     *                   Or, NULL on complete failure (e.g. invalid ID or key).
     */
    public function trash($sub_id_or_key, array $args = [])
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (!($sub = $this->get($sub_id_or_key))) {
            return null; // Not possible.
        }
        if ($sub->status === 'deleted') {
            return null; // Not possible.
        }
        if ($sub->status === 'trashed') {
            return false; // Trashed already.
        }
        $updater = new SubUpdater(['ID' => $sub->ID, 'status' => 'trashed'], $args);

        return $updater->didUpdate();
    }

    /**
     * Bulk trash subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys Subscription IDs/keys.
     * @param array $args            Any additional behavioral args.
     *
     * @return int Number of subscriptions trashed successfully.
     */
    public function bulkTrash(array $sub_ids_or_keys, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($sub_ids_or_keys) as $_sub_id) {
            if ($this->trash($_sub_id, $args)) {
                ++$counter; // Update counter.
            }
        }
        unset($_sub_id); // Housekeeping.

        return $counter;
    }

    /**
     * Delete subscription.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $sub_id_or_key Subscription ID.
     * @param array      $args          Any additional behavioral args.
     *
     * @return bool|null TRUE if subscription is deleted successfully.
     *                   Or, FALSE if unable to delete (e.g. already deleted).
     *                   Or, NULL on complete failure (e.g. invalid ID or key).
     */
    public function delete($sub_id_or_key, array $args = [])
    {
        if (!$sub_id_or_key) {
            return null; // Not possible.
        }
        if (!($sub = $this->get($sub_id_or_key))) {
            return false; // Deleted already.
        }
        if ($sub->status === 'deleted') {
            return false; // Deleted already.
        }
        $deleter = new SubDeleter($sub->ID, $args);

        return $deleter->didDelete();
    }

    /**
     * Bulk delete subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys Subscription IDs/keys.
     * @param array $args            Any additional behavioral args.
     *
     * @return int Number of subscriptions deleted successfully.
     */
    public function bulkDelete(array $sub_ids_or_keys, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($sub_ids_or_keys) as $_sub_id) {
            if ($this->delete($_sub_id, $args)) {
                ++$counter; // Bump counter.
            }
        }
        unset($_sub_id); // Housekeeping.

        return $counter;
    }

    /**
     * Delete email/user all; for unsubscribe all functionality.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email An input email address.
     * @param array  $args      Any additional behavioral args.
     *
     * @return int Number of subscriptions deleted successfully.
     */
    public function deleteEmailUserAll($sub_email, array $args = [])
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return null; // Not possible.
        }
        $user_ids = $this->emailUserIds($sub_email);

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
                   " WHERE `email` = '".esc_sql($sub_email)."'".
                   ($user_ids // Only if we DO have user IDs to search for here.
                       ? " OR `user_id` IN('".implode("','", array_map('esc_sql', $user_ids))."')"
                       : '');
        $sub_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql));

        return $this->bulkDelete($sub_ids, $args);
    }

    /**
     * Query total subscriptions.
     *
     * @since 141111 First documented version.
     *
     * @param int|null $post_id Defaults to a `NULL` value.
     *                          i.e. defaults to any post ID. Pass this to limit the query.
     * @param array    $args    Any additional behavioral args.
     *
     * @throws \exception If a query failure occurs.
     *
     * @return int Total subscriptions for the given query.
     */
    public function queryTotal($post_id = null, array $args = [])
    {
        if (isset($post_id)) { // Force integer?
            $post_id = (integer) $post_id;
        }
        $default_args = [
            'status'     => '',
            'sub_email'  => '',
            'user_id'    => null,
            'comment_id' => null,

            'auto_discount_trash'   => true,
            'sub_email_or_user_ids' => false,
            'group_by_email'        => false,
            'no_cache'              => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $status     = trim((string) $args['status']);
        $sub_email  = trim(strtolower((string) $args['sub_email']));
        $user_id    = $this->issetOr($args['user_id'], null, 'integer');
        $comment_id = $this->issetOr($args['comment_id'], null, 'integer');

        $auto_discount_trash   = (boolean) $args['auto_discount_trash'];
        $sub_email_or_user_ids = (boolean) $args['sub_email_or_user_ids'];
        $group_by_email        = (boolean) $args['group_by_email'];
        $no_cache              = (boolean) $args['no_cache'];

        $cache_keys = compact(
            'post_id',
            'status',
            'sub_email',
            'user_id',
            'comment_id',
            'auto_discount_trash',
            'sub_email_or_user_ids',
            'group_by_email'
        );
        if (!is_null($total = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $total; // Already cached this.
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `email`'.
               ' FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               ' WHERE 1=1'.// Initialize where clause.

               ($status // A specific status?
                   ? " AND `status` = '".esc_sql((string) $status)."'"
                   : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

               ($sub_email // Match a specific email address?
                   ? ($sub_email_or_user_ids // Email or user IDs?
                       ? " AND (`email` = '".esc_sql($sub_email)."'".
                         (isset($user_id) ? " OR `user_id` = '".esc_sql($user_id)."'" : '').
                         (($_sub_email_user_ids = $this->emailUserIds($sub_email, $no_cache))
                             ? " OR `user_id` IN('".implode("','", array_map('esc_sql', $_sub_email_user_ids))."')"
                             : '').')' // ↑ Only if we DO have user IDs to look for.
                       : " AND `email` = '".esc_sql($sub_email)."'")
                   : '').// End `sub_email` check.

               (isset($user_id) && (!$sub_email || !$sub_email_or_user_ids)
                   ? " AND `user_id` = '".esc_sql($user_id)."'" : '').

               (isset($post_id) ? " AND `post_id` = '".esc_sql($post_id)."'" : '').
               (isset($comment_id) ? " AND `comment_id` = '".esc_sql($comment_id)."'" : '').

               ($group_by_email ? ' GROUP BY `email`' : '').

               ' LIMIT 1'; // Just one to check.

        if ($this->plugin->utils_db->wp->query($sql) === false) {
            throw new \exception(sprintf(__('Query failure on SQL: `%1$s`', 'comment-mail'), $sql));
        }
        return $total = (integer) $this->plugin->utils_db->wp->get_var('SELECT FOUND_ROWS()');
    }

    /**
     * Last X subscriptions w/ a given status.
     *
     * @since 141111 First documented version.
     *
     * @param int      $x       The total number to return.
     * @param int|null $post_id Defaults to a `NULL` value.
     *                          i.e. defaults to any post ID. Pass this to limit the query.
     * @param array    $args    Any additional behavioral args.
     *
     * @return \stdClass[] Last X subscriptions w/ a given status.
     */
    public function lastX($x = 0, $post_id = null, array $args = [])
    {
        if (($x = (integer) $x) <= 0) {
            $x = 10; // Default value.
        }
        if (isset($post_id)) { // Force integer?
            $post_id = (integer) $post_id;
        }
        $default_args = [
            'offset' => 0,

            'status'     => '',
            'sub_email'  => '',
            'user_id'    => null,
            'comment_id' => null,

            'auto_discount_trash'   => true,
            'sub_email_or_user_ids' => false,
            'group_by_email'        => false,
            'no_cache'              => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $offset     = abs((integer) $args['offset']);
        $status     = trim((string) $args['status']);
        $sub_email  = trim(strtolower((string) $args['sub_email']));
        $user_id    = $this->issetOr($args['user_id'], null, 'integer');
        $comment_id = $this->issetOr($args['comment_id'], null, 'integer');

        $auto_discount_trash   = (boolean) $args['auto_discount_trash'];
        $sub_email_or_user_ids = (boolean) $args['sub_email_or_user_ids'];
        $group_by_email        = (boolean) $args['group_by_email'];
        $no_cache              = (boolean) $args['no_cache'];

        $cache_keys = compact(
            'x',
            'post_id',
            'offset',
            'status',
            'sub_email',
            'user_id',
            'comment_id',
            'auto_discount_trash',
            'sub_email_or_user_ids',
            'group_by_email'
        );
        if (!is_null($last_x = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $last_x; // Already cached this.
        }
        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               ' WHERE 1=1'.// Initialize where clause.

               ($status // A specific status in this case?
                   ? " AND `status` = '".esc_sql((string) $status)."'"
                   : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

               ($sub_email // Match a specific email address?
                   ? ($sub_email_or_user_ids // Email or user IDs?
                       ? " AND (`email` = '".esc_sql($sub_email)."'".
                         (isset($user_id) ? " OR `user_id` = '".esc_sql($user_id)."'" : '').
                         (($_sub_email_user_ids = $this->emailUserIds($sub_email, $no_cache))
                             ? " OR `user_id` IN('".implode("','", array_map('esc_sql', $_sub_email_user_ids))."')"
                             : '').')' // ↑ Only if we DO have user IDs to look for.
                       : " AND `email` = '".esc_sql($sub_email)."'")
                   : '').// End `sub_email` check.

               (isset($user_id) && (!$sub_email || !$sub_email_or_user_ids)
                   ? " AND `user_id` = '".esc_sql($user_id)."'" : '').

               (isset($post_id) ? " AND `post_id` = '".esc_sql($post_id)."'" : '').
               (isset($comment_id) ? " AND `comment_id` = '".esc_sql($comment_id)."'" : '').

               ($group_by_email ? ' GROUP BY `email`' : '').

               ' ORDER BY `insertion_time` DESC'.

               ' LIMIT '.esc_sql($offset).','.esc_sql($x);

        if (($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K))) {
            return $last_x = $results = $this->plugin->utils_db->typifyDeep($results);
        }
        return $last_x = []; // Default value.
    }

    /**
     * User initiated, but by an admin on behalf of another?
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email      Email address to check.
     * @param bool   $user_initiated Current value for this flag.
     *
     * @return bool `TRUE` if user initiated, but by an admin on behalf of another?
     */
    public function checkUserInitiatedByAdmin($sub_email, $user_initiated = false)
    {
        $sub_email = trim(strtolower((string) $sub_email));
        // Even if the email is empty; we still run the check below.

        if ($user_initiated) { // We only need this check if it IS user-initiated obviously.
            if (current_user_can($this->plugin->manage_cap) || current_user_can($this->plugin->cap) || current_user_can('edit_posts')) {
                if (strcasecmp($sub_email, wp_get_current_user()->user_email) !== 0) { // Email not a match?
                    $user_initiated = false; // Possible update on behalf of another.
                }
            }
        }
        return $user_initiated;
    }

    /**
     * Latest key associated w/ a particular email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return string Latest key associated w/ the email; else an empty string.
     */
    public function emailLatestKey($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return []; // Not possible.
        }
        if (!is_null($sub_key = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $sub_key; // Already cached this.
        }
        $sql = 'SELECT `key` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `email` = '".esc_sql($sub_email)."' AND `key` != ''".
               ' ORDER BY `last_update_time` DESC LIMIT 1';

        return $sub_key = (string) $this->plugin->utils_db->wp->get_var($sql);
    }

    /**
     * All keys associated w/ a particular email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return array An array of unique subscription keys.
     */
    public function emailKeys($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return []; // Not possible.
        }
        if (!is_null($sub_keys = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $sub_keys; // Already cached this.
        }
        $sql = 'SELECT DISTINCT `key` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `email` = '".esc_sql($sub_email)."' AND `key` != ''";

        return $sub_keys = $this->plugin->utils_db->wp->get_col($sql);
    }

    /**
     * All user IDs associated w/ a particular email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return array An array of unique user IDs.
     *
     * @see   SubManageSummary::prepareSubs()
     */
    public function emailUserIds($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return []; // Not possible.
        }
        if (!is_null($user_ids = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $user_ids; // Already cached this.
        }
        $sql1 = 'SELECT DISTINCT `user_id` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
                " WHERE `email` = '".esc_sql($sub_email)."' AND `user_id` > '0'";

        $sql2 = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->wp->users).'`'.
                " WHERE `user_email` = '".esc_sql($sub_email)."' AND `ID` > '0'";

        $user_ids = $this->plugin->utils_db->wp->get_col($sql1);
        $user_ids = array_merge($user_ids, $this->plugin->utils_db->wp->get_col($sql2));

        return $user_ids = array_unique(array_map('intval', $user_ids));
    }

    /**
     * All user ID-based emails associated w/ a particular email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return array An array of unique user ID-based emails (including `$sub_email`).
     *               Note that all of these emails will be in lowercase format.
     *
     * @see   SubManageSummary::prepareSubs()
     * @note  See `assets/sma-diagram.png` for further details on this.
     */
    public function emailUserIdEmails($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return []; // Not possible.
        }
        if (!is_null($user_id_emails = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $user_id_emails; // Already cached this.
        }
        if (!($user_ids = $this->emailUserIds($sub_email, $no_cache))) {
            return $user_id_emails = [$sub_email];
        }
        $sql = 'SELECT DISTINCT `email` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `user_id` IN('".implode("','", array_map('esc_sql', $user_ids))."')";

        $user_id_emails = $this->plugin->utils_db->wp->get_col($sql);
        $user_id_emails = array_merge([$sub_email], $user_id_emails);

        return $user_id_emails = array_unique(array_map('strtolower', $user_id_emails));
    }

    /**
     * Last IP associated w/ email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return string Last IP associated w/ email address; else empty string.
     */
    public function emailLastIp($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return ''; // Not possible.
        }
        if (!is_null($last_ip = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $last_ip; // Already cached this.
        }
        $sql = 'SELECT `last_ip` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `email` = '".esc_sql($sub_email)."' AND `last_ip` != ''".

               ' ORDER BY `last_update_time` DESC'.

               ' LIMIT 1'; // One to check.

        return $last_ip = trim((string) $this->plugin->utils_db->wp->get_var($sql));
    }

    /**
     * Last region associated w/ email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return string Last region associated w/ email address; else empty string.
     */
    public function emailLastRegion($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return ''; // Not possible.
        }
        if (!is_null($last_region = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $last_region; // Already cached this.
        }
        $sql = 'SELECT `last_region` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `email` = '".esc_sql($sub_email)."' AND `last_region` != ''".

               ' ORDER BY `last_update_time` DESC'.

               ' LIMIT 1'; // One to check.

        return $last_region = trim((string) $this->plugin->utils_db->wp->get_var($sql));
    }

    /**
     * Last country associated w/ email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return string Last country associated w/ email address; else empty string.
     */
    public function emailLastCountry($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return ''; // Not possible.
        }
        if (!is_null($last_country = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $last_country; // Already cached this.
        }
        $sql = 'SELECT `last_country` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `email` = '".esc_sql($sub_email)."' AND `last_country` != ''".

               ' ORDER BY `last_update_time` DESC'.

               ' LIMIT 1'; // One to check.

        return $last_country = trim((string) $this->plugin->utils_db->wp->get_var($sql));
    }

    /**
     * Is an email address blacklisted?
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_email Email address to check.
     * @param bool   $no_cache  Disallow a previously cached value?
     *
     * @return bool `TRUE` if the email is blacklisted.
     */
    public function emailIsBlacklisted($sub_email, $no_cache = false)
    {
        if (!($sub_email = trim(strtolower((string) $sub_email)))) {
            return false; // Not possible.
        }
        if (!is_null($is = &$this->cacheKey(__FUNCTION__, $sub_email)) && !$no_cache) {
            return $is; // Already cached this.
        }
        if (!($blacklist = trim($this->plugin->options['email_blacklist_patterns']))) {
            return false; // There is no blacklist.
        }
        if (is_null($blacklist_patterns = &$this->cacheKey(__FUNCTION__, 'blacklist_patterns'))) {
            $blacklist_patterns = '(?:'.implode(
                '|',
                array_map(
                    function ($pattern) {
                        return preg_replace(['/\\\\\*/', '/\\\\\^/'], ['.*?', '[^@]*?'], preg_quote($pattern, '/'));
                    },
                    preg_split('/['."\r\n".']+/', $blacklist, null, PREG_SPLIT_NO_EMPTY)
                )
            ).')';
        }
        return $is = (boolean) preg_match('/^'.$blacklist_patterns.'$/i', $sub_email);
    }

    /**
     * Can a subscription be auto-confirmed?
     *
     * @since 141111 First documented version.
     *
     * @param array $args An array w/ the following:
     *
     *    • integer `post_id` A WP post ID.
     *
     *    • integer `sub_user_id` Subscriber's WP user ID.
     *    • string `sub_email` Subscriber's email address.
     *    • string `sub_last_ip` Subscriber's last IP address.
     *
     *    • boolean `user_initiated` Request is user-initiated?
     *    • boolean|null `auto_confirm` Flag to force specific behavior.
     *
     * @return bool|null `TRUE` if the subscription can be auto-confirmed.
     *                   Or, `FALSE` if the subscription CANNOT be auto-confirmed (explicitly).
     *
     *    Otherwise, we will simply allow an already-`NULL` value to pass through as-is.
     *    This preserves our ability to recognize that it was left to the default behavior,
     *    and that we could not determine definitively if it should be auto-confirmed or not.
     *
     * @note  Regarding the default auto-confirm-if-already-subscribed behavior:
     *
     *    We MUST check both the user ID and also the email address here.
     *    Otherwise, the following scenario would be allowed to occur.
     *
     *    `1@example.com` subscribes w/ sub ID `1`; as user ID `1`, and confirms their email address.
     *    `2@example.com` subscribes w/ sub ID `2`; as user ID `2`, and confirms their email address.
     *    `1@example.com` subscribes w/ sub ID `3`; as user ID `2` ~ using a forged email address!
     *
     *    If we didn't check both the user ID and also the email address here;
     *    then sub ID `3` would be auto-confirmed; associated w/ user ID `2`.
     *
     *    Since user ID `2` will be unable to receive email for sub ID `3`, they wouldn't
     *    get a key for this particular subscription, so there's not a security issue there.
     *
     *    However, this WOULD create two problems that we should avoid if at all possible.
     *
     *       1. The subscription would have been auto-confirmed, even though the owner of the email
     *          address did not confirm the subscription themselves; i.e. it was a forged email address.
     *
     *       2. It creates an invalid association between the email address and user ID.
     *          This could still occur anyway, but the subscr. should NOT be auto-confirmed when it does.
     *          i.e. it should be left as `unconfirmed` so it will be cleaned from the DB eventually.
     *
     *    Hmm, but what if both users were to have the same ID (e.g. `0` when not logged-in)?
     *    In that case, anyone who is NOT logged-in might submit a comment as someone else (w/ a subscr. request).
     *    If the email address they entered (i.e. forged) was already confirmed by the real owner some time before,
     *    it would be auto-confirmed by a fake; even though the real owner did not actually request the subscription.
     *    That could occur quite often on a site that doesn't require a user to be logged-in when commenting.
     *
     *    It's important to note however, that since this type of auto-confirmation occurs on a post-specific basis anyway,
     *    the aforementioned problem doesn't pose a serious security threat. However, we should prevent it by default,
     *    by disabling auto-confirmations whenever there's a NOT a reliable non-empty user ID that we can use;
     *    and/or when `all_wp_users_confirm_email` has not been turned on by the site owner.
     *
     *    Instead, we can let a site owner turn on `auto_confirm_if_already_subscribed_u0ip_enable`
     *    after being warned about the potential for abuse that such a thing could expose. In this case,
     *    instead of checking only the user ID, we can also check the user's IP address to be sure it matches up.
     *    Still, IP addresses can be spoofed too, so this should NOT be enabled w/o a strong warning.
     *
     *    All of that said, if `$sub_user_id` > `0` (and `all_wp_users_confirm_email=1`), we can safely continue
     *    w/o the additional check against the current plugin options; since the user ID can be matched up properly in that case.
     *
     *    Note: If SSO is enabled, `all_wp_users_confirm_email` is ignored; since SSO allows for unconfirmed email addresses.
     */
    public function canAutoConfirm(array $args)
    {
        $default_args = [
            'post_id' => 0,

            'sub_user_id' => 0,
            'sub_email'   => '',
            'sub_last_ip' => '',

            'user_initiated' => false,
            'auto_confirm'   => null,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $post_id     = (integer) $args['post_id'];
        $sub_user_id = (integer) $args['sub_user_id'];
        $sub_email   = trim(strtolower((string) $args['sub_email']));
        $sub_last_ip = trim((string) $args['sub_last_ip']);

        $user_initiated = (boolean) $args['user_initiated'];
        $user_initiated = $this->checkUserInitiatedByAdmin($sub_email, $user_initiated);

        $auto_confirm = $args['auto_confirm']; // Initialize only.

        if (!$post_id || !$sub_email) {
            return false; // Not possible.
        }
        if (!in_array($auto_confirm, [null, true, false], true)) {
            $auto_confirm = null; // Force one of these values.
        }
        if (!isset($auto_confirm)) { // If not set explicitly, check option value.
            if ((boolean) $this->plugin->options['auto_confirm_force_enable']) {
                $auto_confirm = true; // Site owner says `TRUE` explicitly.
            }
        }
        // ↑ Note that we're preserving `FALSE` (explicitly) here.
        // The default behavior is to check for an already-confirmed subscription.

        if ($auto_confirm === true) { // `TRUE` (explicitly)?
            return $auto_confirm; // Report back to the caller.
        }
        // Now, unless `$auto_confirm` was passed as `FALSE` (explicitly),
        // we continue with the additional/default behavior down below.

        if ($auto_confirm === false) { // `FALSE` (explicitly)?
            return $auto_confirm; // Report back to the caller.
        }
        // Else use default `NULL` behavior; i.e. check if they've already confirmed another.

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `post_id` = '".esc_sql($post_id)."'".// On a post-specific basis (always).

               " AND `user_id` = '".esc_sql($sub_user_id)."'".// Must match user ID.
               " AND `email` = '".esc_sql($sub_email)."'".// Must match email address.

               ($sub_user_id <= 0 || !$this->plugin->options['all_wp_users_confirm_email'] || $this->plugin->options['sso_enable']
                   ? " AND (`insertion_ip` = '".esc_sql($sub_last_ip)."' OR `last_ip` = '".esc_sql($sub_last_ip)."')".
                     " AND '".esc_sql($sub_last_ip)."' != ''" // The IP that we're checking cannot be empty.
                   : '').// Exclude otherwise; we have a good user ID we can check in this case.

               " AND `status` = 'subscribed' LIMIT 1"; // One to check.

        if (($sub_user_id > 0 && $this->plugin->options['all_wp_users_confirm_email'] && !$this->plugin->options['sso_enable'])
            || ($sub_last_ip && $this->plugin->options['auto_confirm_if_already_subscribed_u0ip_enable'])
        ) {
            if ((boolean) $this->plugin->utils_db->wp->get_var($sql)) {
                $auto_confirm = true; // Confirmed once already on this post ID.
            }
        }
        return $auto_confirm; // Report back to the caller; possibly still `NULL` here.
    }

    /**
     * Set current sub's email address.
     *
     * @since   141111 First documented version.
     *
     * @param string $sub_key   Subscription key; MUST match the email address.
     * @param string $sub_email Subscriber's current email address.
     *
     * @warning It's VERY IMPORTANT that we only call upon this function to set the email address
     *    during a user-initiated sub. action; i.e. in real-time. This cookie is used as a trusted source by {@link current_email()}.
     *    In short, do NOT set the current email address cookie unless an action is being performed against a key.
     *
     * @throws \exception If `$sub_key` does NOT match any existing keys for the `$sub_email`.
     * @throws \exception If attempting to set the current email when it's not a sub. action being processed in real time.
     *                    Note that it's still possible to set the email address to an empty string; from anywhere at any time.
     */
    public function setCurrentEmail($sub_key, $sub_email)
    {
        $sub_key   = trim((string) $sub_key);
        $sub_email = trim(strtolower((string) $sub_email));

        if (isset($sub_email[0])) { // Double-check security issues here.
            if (!$sub_key || !in_array($sub_key, $this->emailKeys($sub_email), true)) {
                throw new \exception(__('Key-to-email mismatch; possible security issue.', 'comment-mail'));
            }
            if (is_admin() || (!isset($_REQUEST[GLOBAL_NS]['confirm']) && !isset($_REQUEST[GLOBAL_NS]['unsubscribe']) && !isset($_REQUEST[GLOBAL_NS]['manage']))) {
                throw new \exception(__('Trying to set current email w/o a user-initiated sub. action.', 'comment-mail'));
            }
        }
        // Cookie is ONLY set for subscribers that received a secret `key` in one way or another.
        // A subscriber only receives a secret key if we can confirm they own the email associated w/ it.
        // ~ Note also that this cookie is encrypted via `MCRYPT_RIJNDAEL_256` w/ a unique salt.
        $this->plugin->utils_enc->setCookie(GLOBAL_NS.'_sub_email', $sub_email);
    }

    /**
     * Current sub's email address.
     *
     * @since 141111 First documented version.
     *
     * @return string Current subscriber's email address.
     */
    public function currentEmail()
    {
        if ($this->plugin->options['all_wp_users_confirm_email'] || current_user_can('edit_posts')) {
            if (($user = wp_get_current_user()) && $user->ID && $user->user_email) {
                return trim(strtolower((string) $user->user_email));
            }
        }
        // Cookie is ONLY set for subscribers that received a secret `key` in one way or another.
        // A subscriber only receives a secret key if we can confirm they own the email associated w/ it.
        // ~ Note also that this cookie is encrypted via `MCRYPT_RIJNDAEL_256` w/ a unique salt.
        if (($sub_email = $this->plugin->utils_enc->getCookie(GLOBAL_NS.'_sub_email'))) {
            return trim(strtolower((string) $sub_email));
        }
        return ''; // Not possible.
    }

    /**
     * Latest info for current email.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Specs/behavioral args.
     *
     * @return \stdClass Latest info for current email.
     *                   If values cannot be filled, we return a set of default values.
     */
    public function currentEmailLatestInfo(array $args = [])
    {
        $sub_email = $this->currentEmail();

        $default_args = [
            'post_id'               => null,
            'comment_form_defaults' => false,
            'no_cache'              => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $post_id               = $this->issetOr($args['post_id'], null, 'integer');
        $comment_form_defaults = (boolean) $args['comment_form_defaults'];
        $no_cache              = (boolean) $args['no_cache'];

        $default_sub_type    = 'comment';
        $default_sub_deliver = 'asap';

        if ($comment_form_defaults) { // Sub type CAN be empty.
            $default_sub_type = $this->plugin->options['comment_form_default_sub_type_option'];
        }
        if ($comment_form_defaults) { // Deliver option is never empty.
            $default_sub_deliver = $this->plugin->options['comment_form_default_sub_deliver_option'];
        }
        $default_info = (object) [
            'ID'  => 0,
            'key' => '',

            'user_id'    => 0,
            'post_id'    => 0,
            'comment_id' => 0,

            'type'    => $default_sub_type,
            'deliver' => $default_sub_deliver,

            'fname' => '',
            'lname' => '',
            'email' => $sub_email,

            'insertion_ip'      => '',
            'insertion_region'  => '',
            'insertion_country' => '',

            'last_ip'      => '',
            'last_region'  => '',
            'last_country' => '',

            'status' => '',

            'insertion_time'   => 0,
            'last_update_time' => 0,
        ];
        if (!$sub_email || (isset($post_id) && $post_id <= 0)) {
            return $default_info; // Not possible.
        }
        $cache_keys = compact('sub_email', 'post_id', 'comment_form_defaults');

        if (!is_null($info = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $info; // Already cached this.
        }
        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `email` = '".esc_sql($sub_email)."'".

               ($post_id // For a specific post ID?
                   ? " AND `post_id` = '".esc_sql($post_id)."'" : '').

               " AND `status` = 'subscribed'".// Subscribed only.

               ' ORDER BY `comment_id` ASC, `last_update_time` DESC'.

               ' LIMIT 1'; // Only need last one; give precedence to `comment_id=0`.

        if (($results = $this->plugin->utils_db->wp->get_results($sql))) {
            $results    = $this->plugin->utils_db->typifyDeep($results);
            $info       = $results[0]; // Expecting one result only here.
            $info->type = $info->comment_id <= 0 ? 'comments' : 'comment';

            return $info; // \stdClass object properties.
        }
        return $info = $default_info;
    }

    /**
     * Nullify the object cache.
     *
     * @since 141111 First documented version.
     *
     * @param array $sub_ids_or_keys An array of IDs/keys.
     */
    public function nullifyCache(array $sub_ids_or_keys = [])
    {
        $preserve = []; // Initialize.

        if ($sub_ids_or_keys) {
            $preserve[] = 'get';
        }
        $this->unsetCacheKeys($preserve);

        foreach ($sub_ids_or_keys as $_sub_id_or_key) {
            unset($this->cache['get'][$_sub_id_or_key]);
        }
        unset($_sub_id_or_key); // Housekeeping.
    }
}
