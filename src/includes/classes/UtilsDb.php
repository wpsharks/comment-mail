<?php
/**
 * DB Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * DB Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsDb extends AbsBase
{
    /**
     * @var \wpdb WP DB class reference.
     *
     * @since 141111 First documented version.
     */
    public $wp;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->wp = $GLOBALS['wpdb'];
    }

    /**
     * Current DB prefix for this plugin.
     *
     * @since 141111 First documented version.
     *
     * @return string Current DB table prefix.
     */
    public function prefix()
    {
        return $this->wp->prefix.GLOBAL_NS.'_';
    }

    /**
     * Typify result properties deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $value Any value can be typified deeply.
     *
     * @return mixed Typified value.
     */
    public function typifyDeep($value)
    {
        if (is_array($value) || is_object($value)) {
            foreach ($value as $_key => &$_value) {
                if (is_array($_value) || is_object($_value)) {
                    $_value = $this->typifyDeep($_value);
                } elseif ($this->isIntegerKey($_key)) {
                    $_value = (int) $_value;
                } elseif ($this->isFloatKey($_key)) {
                    $_value = (float) $_value;
                } else {
                    $_value = (string) $_value;
                }
            }
            unset($_key, $_value); // Housekeeping.
        }
        return $value; // Typified deeply.
    }

    /**
     * Should an array/object key contain an integer value?
     *
     * @since 141111 First documented version.
     *
     * @param mixed $key The input key to check.
     *
     * @return bool TRUE if the key should contain an integer value.
     */
    public function isIntegerKey($key)
    {
        if (!$key || !is_string($key)) {
            return false;
        }
        $key = strtolower($key);

        $integer_keys = [
            'id',
            'parent',
            'time',
            'count',
            'counter',
            'user_initiated',
        ];
        $preg_quoted_integer_keys = array_map(
            function ($key) {
                return preg_quote($key, '/'); #
            },
            $integer_keys
        );
        if (preg_match('/(?:^|_)(?:'.implode('|', $preg_quoted_integer_keys).')(?:_before)?$/i', $key)) {
            return true; // e.g. `id`, `x_id`, `x_x_id`, `x_id_before`, `time_before`, `x_time_before`.
        }
        return false; // Default.
    }

    /**
     * Should an array/object key contain a float value?
     *
     * @since 141111 First documented version.
     *
     * @param mixed $key The input key to check.
     *
     * @return bool TRUE if the key should contain a float value.
     */
    public function isFloatKey($key)
    {
        return false; // Default; no float keys at this time.
    }

    /**
     * Check DB engine compat. w/ fulltext indexes.
     *
     * @since 141111 First documented version.
     *
     * @param string $sql Input SQL to check.
     *
     * @return string Output `$sql` w/ possible engine modification.
     *                Only MySQL v5.6.4+ supports fulltext indexes with the InnoDB engine.
     *                Otherwise, we use MyISAM for any table that includes a fulltext index.
     *
     * @note  MySQL v5.6.4+ supports fulltext indexes w/ InnoDB.
     *    See: <http://bit.ly/ZVeF42>
     */
    public function fulltextCompat($sql)
    {
        if (!($sql = trim((string) $sql))) {
            return $sql; // Empty.
        }
        if (!preg_match('/^CREATE\s+TABLE\s+/i', $sql)) {
            return $sql; // Not applicable.
        }
        if (!preg_match('/\bFULLTEXT\s+KEY\b/i', $sql)) {
            return $sql; // No fulltext index.
        }
        if (!preg_match('/\bENGINE\=InnoDB\b/i', $sql)) {
            return $sql; // Not using InnoDB anyway.
        }
        $mysql_version = $this->wp->db_version();
        if ($mysql_version && version_compare($mysql_version, '5.6.4', '>=')) {
            return $sql; // MySQL v5.6.4+ supports fulltext indexes.
        }
        return preg_replace('/\bENGINE\=InnoDB\b/i', 'ENGINE=MyISAM', $sql);
    }

    /**
     * Comment status translator.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $status
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     *
     * @throws \exception If an unexpected status is encountered.
     *
     * @return string `approve`, `hold`, `trash`, `spam`, `delete`.
     */
    public function commentStatusI18n($status)
    {
        switch (trim(strtolower((string) $status))) {
            case '1':
            case 'approve':
            case 'approved':
                return 'approve';

            case '0':
            case '':
            case 'hold':
            case 'unapprove':
            case 'unapproved':
            case 'moderated':
                return 'hold';

            case 'trash':
            case 'post-trashed':
                return 'trash';

            case 'spam':
                return 'spam';

            case 'delete':
                return 'delete';

            default: // Throw exception on anything else.
                throw new \exception(sprintf(__('Unexpected comment status: `%1$s`.', 'comment-mail'), $status));
        }
    }

    /**
     * Post comment status translator.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $status
     *
     *    One of the following:
     *       - `0` (aka: ``, `closed`, `close`).
     *       - `1` (aka: `opened`, `open`).
     *
     * @throws \exception If an unexpected status is encountered.
     *
     * @return string `open`, `closed`.
     */
    public function postCommentStatusI18n($status)
    {
        switch (trim(strtolower((string) $status))) {
            case '1':
            case 'open':
            case 'opened':
                return 'open';

            case '0':
            case '':
            case 'close':
            case 'closed':
                return 'closed';

            default: // Throw exception on anything else.
                throw new \exception(sprintf(__('Unexpected post comment status: `%1$s`.', 'comment-mail'), $status));
        }
    }

    /**
     * Counts total users.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Behavioral args (optional).
     *
     * @throws \exception If a query failure occurs.
     *
     * @return int Total users available.
     */
    public function totalUsers(array $args = [])
    {
        $default_args = [
            'no_cache' => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $no_cache = (bool) $args['no_cache'];

        $cache_keys = []; // No cacheable keys at this time.

        if (!is_null($total = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $total; // Already cached this.
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `ID` FROM `'.esc_sql($this->wp->users).'`'.

               ' LIMIT 1'; // One to check.

        if ($this->wp->query($sql) === false) { // Initial query failure?
            throw new \exception(__('Query failure.', 'comment-mail'));
        }
        return $total = (int) $this->wp->get_var('SELECT FOUND_ROWS()');
    }

    /**
     * All users quickly.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Behavioral args (optional).
     *
     * @throws \exception If a query failure occurs.
     *
     * @return \WP_User[] An array of all users.
     */
    public function allUsers(array $args = [])
    {
        $default_args = [
            'max'         => PHP_INT_MAX,
            'fail_on_max' => false,
            'no_cache'    => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $max         = (int) $args['max'];
        $max         = $max < 1 ? 1 : $max;
        $fail_on_max = (bool) $args['fail_on_max'];
        $no_cache    = (bool) $args['no_cache'];

        $cache_keys = compact('max', 'fail_on_max');

        if (!is_null($users = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $users; // Already cached this.
        }
        if ($fail_on_max && $this->totalUsers($args) > $max) {
            return $users = []; // Fail when there are too many.
        }
        $sql = 'SELECT *'.// Everything please.
                   ' FROM `'.esc_sql($this->wp->users).'`'.

                   ($max !== PHP_INT_MAX ? ' LIMIT '.esc_sql($max) : '');

        if (($results = $this->wp->get_results($sql, OBJECT_K))) {
            $users = $results; // Set as users.

            foreach ($users as &$_user) {
                $_user = new \WP_User($_user);
            }
            unset($_user); // Housekeeping.
        }
        return $users = []; // Default return value.
    }

    /**
     * Counts total posts.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Behavioral args (optional).
     *
     * @throws \exception If a query failure occurs.
     *
     * @return int Total posts available.
     */
    public function totalPosts(array $args = [])
    {
        $default_args = [
            'for_comments_only'          => false,
            'include_post_types'         => [],
            'exclude_post_types'         => [],
            'exclude_post_statuses'      => [],
            'exclude_password_protected' => false,
            'no_cache'                   => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $for_comments_only          = (bool) $args['for_comments_only'];
        $include_post_types         = (array) $args['include_post_types'];
        $exclude_post_types         = (array) $args['exclude_post_types'];
        $exclude_post_statuses      = (array) $args['exclude_post_statuses'];
        $exclude_password_protected = (bool) $args['exclude_password_protected'];
        $no_cache                   = (bool) $args['no_cache'];

        $cache_keys = compact('for_comments_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected');

        if (!is_null($total = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $total; // Already cached this.
        }
        $post_types    = $include_post_types ? $include_post_types : get_post_types(['exclude_from_search' => false]);
        $post_statuses = get_post_stati(['exclude_from_search' => false]);

        $sql = 'SELECT SQL_CALC_FOUND_ROWS `ID` FROM `'.esc_sql($this->wp->posts).'`'.

               " WHERE `post_type` IN('".implode("','", array_map('esc_sql', $post_types))."')".
               ($exclude_post_types ? " AND `post_type` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_types))."')" : '').

               " AND `post_status` IN('".implode("','", array_map('esc_sql', $post_statuses))."')".
               ($exclude_post_statuses ? " AND `post_status` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_statuses))."')" : '').

               ($exclude_password_protected ? " AND `post_password` = ''" : '').// Exlude password protected posts?

               ($for_comments_only // For comments only?
                   ? " AND (`comment_status` IN('1', 'open', 'opened')".
                     "     OR `comment_count` > '0')"
                   : '').

               ' LIMIT 1'; // One to check.

        if ($this->wp->query($sql) === false) { // Initial query failure?
            throw new \exception(__('Query failure.', 'comment-mail'));
        }
        return $total = (int) $this->wp->get_var('SELECT FOUND_ROWS()');
    }

    /**
     * All posts quickly.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Behavioral args (optional).
     *
     * @throws \exception If a query failure occurs.
     *
     * @return \WP_Post[] An array of all posts.
     */
    public function allPosts(array $args = [])
    {
        $default_args = [
            'max'                        => PHP_INT_MAX,
            'fail_on_max'                => false,
            'include_post_types'         => [],
            'for_comments_only'          => false,
            'exclude_post_types'         => [],
            'exclude_post_statuses'      => [],
            'exclude_password_protected' => false,
            'no_cache'                   => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $max                        = (int) $args['max'];
        $max                        = $max < 1 ? 1 : $max;
        $fail_on_max                = (bool) $args['fail_on_max'];
        $for_comments_only          = (bool) $args['for_comments_only'];
        $include_post_types         = (array) $args['include_post_types'];
        $exclude_post_types         = (array) $args['exclude_post_types'];
        $exclude_post_statuses      = (array) $args['exclude_post_statuses'];
        $exclude_password_protected = (bool) $args['exclude_password_protected'];
        $no_cache                   = (bool) $args['no_cache'];

        $cache_keys = compact('max', 'fail_on_max', 'for_comments_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected');

        if (!is_null($posts = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $posts; // Already cached this.
        }
        if ($fail_on_max && $this->totalPosts($args) > $max) {
            return $posts = []; // Fail when there are too many.
        }
        $post_types    = $include_post_types ? $include_post_types : get_post_types(['exclude_from_search' => false]);
        $post_statuses = get_post_stati(['exclude_from_search' => false]);

        $sql = 'SELECT *'.// Everything please.
                   ' FROM `'.esc_sql($this->wp->posts).'`'.

                   " WHERE `post_type` IN('".implode("','", array_map('esc_sql', $post_types))."')".
                   ($exclude_post_types ? " AND `post_type` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_types))."')" : '').

                   " AND `post_status` IN('".implode("','", array_map('esc_sql', $post_statuses))."')".
                   ($exclude_post_statuses ? " AND `post_status` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_statuses))."')" : '').

                   ($exclude_password_protected ? " AND `post_password` = ''" : '').// Exlude password protected posts?

                   ($for_comments_only // For comments only?
                       ? " AND (`comment_status` IN('1', 'open', 'opened')".
                         "     OR `comment_count` > '0')"
                       : '').

                   ' ORDER BY `post_type` ASC, `post_date_gmt` DESC'.

                   ($max !== PHP_INT_MAX ? ' LIMIT '.esc_sql($max) : '');

        if (($results = $this->wp->get_results($sql, OBJECT_K))) {
            $post_results = $page_results = $media_results = $other_results = [];

            foreach ($results as $_key => $_result) {
                if ($_result->post_type === 'post') {
                    $post_results[$_key] = $_result;
                } elseif ($_result->post_type === 'page') {
                    $page_results[$_key] = $_result;
                } elseif ($_result->post_type === 'attachment') {
                    $media_results[$_key] = $_result;
                } else {
                    $other_results[$_key] = $_result;
                }
            }
            unset($_key, $_result); // Housekeeping.

            $results = $post_results + $page_results + $other_results + $media_results;
            $posts   = $results; // Use as posts in this order of priority.

            foreach ($posts as &$_post) {
                $_post = new \WP_Post($_post);
            }
            unset($_post); // Housekeeping.

            return $posts;
        }
        return $posts = []; // Default return value.
    }

    /**
     * Counts total comments.
     *
     * @since 141111 First documented version.
     *
     * @param int   $post_id A post ID.
     * @param array $args    Behavioral args (optional).
     *
     * @throws \exception If a query failure occurs.
     *
     * @return int Total comments available.
     */
    public function totalComments($post_id, array $args = [])
    {
        if (!($post_id = (int) $post_id)) {
            return 0; // Not possible.
        }
        $default_args = [
            'parents_only'                => false,
            'include_post_types'          => [],
            'exclude_post_types'          => [],
            'exclude_post_statuses'       => [],
            'exclude_password_protected'  => false,
            'exclude_unapproved_comments' => false,
            'no_cache'                    => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $parents_only                = (bool) $args['parents_only'];
        $include_post_types          = (array) $args['include_post_types'];
        $exclude_post_types          = (array) $args['exclude_post_types'];
        $exclude_post_statuses       = (array) $args['exclude_post_statuses'];
        $exclude_password_protected  = (bool) $args['exclude_password_protected'];
        $exclude_unapproved_comments = (bool) $args['exclude_unapproved_comments'];
        $no_cache                    = (bool) $args['no_cache'];

        $cache_keys = compact('post_id', 'parents_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected', 'exclude_unapproved_comments');

        if (!is_null($total = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $total; // Already cached this.
        }
        if (!($post = get_post($post_id))) {
            return $total = 0;
        }
        if ($include_post_types && !in_array($post->post_type, $include_post_types, true)) {
            return $total = 0; // Post type not included; automatic zero.
        }
        if ($exclude_post_types && in_array($post->post_type, $exclude_post_types, true)) {
            return $total = 0; // Post type is excluded; automatic zero.
        }
        if ($exclude_post_statuses && in_array($post->post_status, $exclude_post_statuses, true)) {
            return $total = 0; // Post status is excluded; automatic zero.
        }
        if ($exclude_password_protected && $post->post_password) { // Has password?
            return $total = 0; // Passwords excluded; automatic zero.
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `comment_ID` FROM `'.esc_sql($this->wp->comments).'`'.

               " WHERE `comment_post_ID` = '".esc_sql($post_id)."'".
               " AND (`comment_type` = '' OR `comment_type` = 'comment')".

               ($parents_only // Parents only?
                   ? " AND `comment_parent` <= '0'" : '').

               ($exclude_unapproved_comments
                   ? " AND `comment_approved` IN('1', 'approve', 'approved')" : '').

               ' LIMIT 1'; // One to check.

        if ($this->wp->query($sql) === false) { // Initial query failure?
            throw new \exception(__('Query failure.', 'comment-mail'));
        }
        return $total = (int) $this->wp->get_var('SELECT FOUND_ROWS()');
    }

    /**
     * All comments quickly.
     *
     * @since 141111 First documented version.
     *
     * @param int   $post_id A post ID.
     * @param array $args    Behavioral args (optional).
     *
     * @throws \exception If a query failure occurs.
     *
     * @return \WP_Comment[] An array of all comments.
     */
    public function allComments($post_id, array $args = [])
    {
        if (!($post_id = (int) $post_id)) {
            return []; // Not possible.
        }
        $default_args = [
            'max'                         => PHP_INT_MAX,
            'fail_on_max'                 => false,
            'parents_only'                => false,
            'include_post_types'          => [],
            'exclude_post_types'          => [],
            'exclude_post_statuses'       => [],
            'exclude_password_protected'  => false,
            'exclude_unapproved_comments' => false,
            'no_cache'                    => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $max                         = (int) $args['max'];
        $max                         = $max < 1 ? 1 : $max;
        $fail_on_max                 = (bool) $args['fail_on_max'];
        $parents_only                = (bool) $args['parents_only'];
        $include_post_types          = (array) $args['include_post_types'];
        $exclude_post_types          = (array) $args['exclude_post_types'];
        $exclude_post_statuses       = (array) $args['exclude_post_statuses'];
        $exclude_password_protected  = (bool) $args['exclude_password_protected'];
        $exclude_unapproved_comments = (bool) $args['exclude_unapproved_comments'];
        $no_cache                    = (bool) $args['no_cache'];

        $cache_keys = compact('post_id', 'max', 'fail_on_max', 'parents_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected', 'exclude_unapproved_comments');

        if (!is_null($comments = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $comments; // Already cached this.
        }
        if (!($post = get_post($post_id))) {
            return $comments = [];
        }
        if ($include_post_types && !in_array($post->post_type, $include_post_types, true)) {
            return $comments = []; // Post type not included; automatic empty
        }
        if ($exclude_post_types && in_array($post->post_type, $exclude_post_types, true)) {
            return $comments = []; // Post type is excluded; automatic empty.
        }
        if ($exclude_post_statuses && in_array($post->post_status, $exclude_post_statuses, true)) {
            return $comments = []; // Post status is excluded; automatic empty.
        }
        if ($exclude_password_protected && $post->post_password) { // Has a password?
            return $comments = []; // Passwords excluded; automatic empty.
        }
        if ($fail_on_max && $this->totalComments($post_id, $args) > $max) {
            return $comments = []; // Fail when there are too many.
        }
        $sql = 'SELECT *'.// Everything please.
                   ' FROM `'.esc_sql($this->wp->comments).'`'.

                   " WHERE `comment_post_ID` = '".esc_sql($post_id)."'".
                   " AND (`comment_type` = '' OR `comment_type` = 'comment')".

                   ($parents_only // Parents only?
                       ? " AND `comment_parent` <= '0'" : '').

                   ($exclude_unapproved_comments
                       ? " AND `comment_approved` IN('1', 'approve', 'approved')" : '').

                   ' ORDER BY `comment_date_gmt` ASC'.

                   ($max !== PHP_INT_MAX ? ' LIMIT '.esc_sql($max) : '');

        if (($results = $this->wp->get_results($sql, OBJECT_K))) {
            $comments = $results; // Set as comments.

            foreach ($comments as &$_comment) {
                $_comment = new \WP_Comment($_comment);
            }
            unset($_comment); // Housekeeping.

            return $comments;
        }
        return $comments = []; // Default return value.
    }

    /**
     * Pagination links start page.
     *
     * @since 141111 First documented version.
     *
     * @param int $current_page The current page number.
     * @param int $total_pages  The total pages available.
     * @param int $max_links    Max pagination links to display.
     *
     * @return int The page number to begin pagination links from.
     *
     * @note  This method has been tested; even against invalid figures.
     *    It handles every scenario gracefully; even if invalid figures are given.
     */
    public function paginationLinksStartPage($current_page, $total_pages, $max_links)
    {
        $current_page = (int) $current_page;
        $total_pages  = (int) $total_pages;
        $max_links    = (int) $max_links;

        $min_start_page = 1; // Obviously.
        $max_start_page = max($total_pages - ($max_links - 1), $min_start_page);
        $start_page     = max(min($current_page - floor($max_links / 2), $max_start_page), $min_start_page);

        return (int) $start_page;
    }
}
