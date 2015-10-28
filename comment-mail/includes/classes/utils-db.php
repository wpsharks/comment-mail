<?php
/**
 * DB Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_db'))
	{
		/**
		 * DB Utilities
		 *
		 * @since 141111 First documented version.
		 */
		class utils_db extends abs_base
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
				return $this->wp->prefix.__NAMESPACE__.'_';
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
			public function typify_deep($value)
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => &$_value)
					{
						if(is_array($_value) || is_object($_value))
							$_value = $this->typify_deep($_value);

						else if($this->is_integer_key($_key))
							$_value = (integer)$_value;

						else if($this->is_float_key($_key))
							$_value = (float)$_value;

						else $_value = (string)$_value;
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
			 * @return boolean TRUE if the key should contain an integer value.
			 */
			public function is_integer_key($key)
			{
				if(!$key || !is_string($key))
					return FALSE;

				$key = strtolower($key);

				$integer_keys             = array(
					'id',
					'parent',
					'time',
					'count',
					'counter',
					'user_initiated',
				);
				$preg_quoted_integer_keys = array_map(function ($key)
				{
					return preg_quote($key, '/'); #

				}, $integer_keys);

				if(preg_match('/(?:^|_)(?:'.implode('|', $preg_quoted_integer_keys).')(?:_before)?$/i', $key))
					return TRUE; // e.g. `id`, `x_id`, `x_x_id`, `x_id_before`, `time_before`, `x_time_before`.

				return FALSE; // Default.
			}

			/**
			 * Should an array/object key contain a float value?
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $key The input key to check.
			 *
			 * @return boolean TRUE if the key should contain a float value.
			 */
			public function is_float_key($key)
			{
				return FALSE; // Default; no float keys at this time.
			}

			/**
			 * Check DB engine compat. w/ fulltext indexes.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $sql Input SQL to check.
			 *
			 * @return string Output `$sql` w/ possible engine modification.
			 *    Only MySQL v5.6.4+ supports fulltext indexes with the InnoDB engine.
			 *    Otherwise, we use MyISAM for any table that includes a fulltext index.
			 *
			 * @note MySQL v5.6.4+ supports fulltext indexes w/ InnoDB.
			 *    See: <http://bit.ly/ZVeF42>
			 */
			public function fulltext_compat($sql)
			{
				if(!($sql = trim((string)$sql)))
					return $sql; // Empty.

				if(!preg_match('/^CREATE\s+TABLE\s+/i', $sql))
					return $sql; // Not applicable.

				if(!preg_match('/\bFULLTEXT\s+KEY\b/i', $sql))
					return $sql; // No fulltext index.

				if(!preg_match('/\bENGINE\=InnoDB\b/i', $sql))
					return $sql; // Not using InnoDB anyway.

				$mysql_version = $this->wp->db_version();
				if($mysql_version && version_compare($mysql_version, '5.6.4', '>='))
					return $sql; // MySQL v5.6.4+ supports fulltext indexes.

				return preg_replace('/\bENGINE\=InnoDB\b/i', 'ENGINE=MyISAM', $sql);
			}

			/**
			 * Comment status translator.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer|string $status
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @return string `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @throws \exception If an unexpected status is encountered.
			 */
			public function comment_status__($status)
			{
				switch(trim(strtolower((string)$status)))
				{
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
						throw new \exception(sprintf(__('Unexpected comment status: `%1$s`.', $this->plugin->text_domain), $status));
				}
			}

			/**
			 * Post comment status translator.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer|string $status
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `closed`, `close`).
			 *       - `1` (aka: `opened`, `open`).
			 *
			 * @return string `open`, `closed`.
			 *
			 * @throws \exception If an unexpected status is encountered.
			 */
			public function post_comment_status__($status)
			{
				switch(trim(strtolower((string)$status)))
				{
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
						throw new \exception(sprintf(__('Unexpected post comment status: `%1$s`.', $this->plugin->text_domain), $status));
				}
			}

			/**
			 * Counts total users.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Behavioral args (optional).
			 *
			 * @return integer Total users available.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function total_users(array $args = array())
			{
				$default_args = array(
					'no_cache' => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$no_cache = (boolean)$args['no_cache'];

				$cache_keys = array(); // No cacheable keys at this time.

				if(!is_null($total = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $total; // Already cached this.

				$sql = "SELECT SQL_CALC_FOUND_ROWS `ID` FROM `".esc_html($this->wp->users)."`".

				       " LIMIT 1"; // One to check.

				if($this->wp->query($sql) === FALSE) // Initial query failure?
					throw new \exception(__('Query failure.', $this->plugin->text_domain));

				return ($total = (integer)$this->wp->get_var("SELECT FOUND_ROWS()"));
			}

			/**
			 * All users quickly.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Behavioral args (optional).
			 *
			 * @return \stdClass[] An array of all users.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function all_users(array $args = array())
			{
				$default_args = array(
					'max'         => PHP_INT_MAX,
					'fail_on_max' => FALSE,
					'no_cache'    => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$max         = (integer)$args['max'];
				$max         = $max < 1 ? 1 : $max;
				$fail_on_max = (boolean)$args['fail_on_max'];
				$no_cache    = (boolean)$args['no_cache'];

				$cache_keys = compact('max', 'fail_on_max');

				if(!is_null($users = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $users; // Already cached this.

				if($fail_on_max && $this->total_users($args) > $max)
					return ($users = array()); // Fail when there are too many.

				$columns = array(
					'ID',
					'user_login',
					'user_nicename',
					'user_email',
					'user_url',
					'user_registered',
					'user_activation_key',
					'user_status',
					'display_name',
				);
				$sql     = "SELECT `".implode("`,`", array_map('esc_sql', $columns))."`".
				           " FROM `".esc_html($this->wp->users)."`".

				           ($max !== PHP_INT_MAX ? " LIMIT ".esc_sql($max) : '');

				if(($results = $this->wp->get_results($sql, OBJECT_K)))
					return ($users = $results = $this->typify_deep($results));

				return ($users = array()); // Default return value.
			}

			/**
			 * Counts total posts.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Behavioral args (optional).
			 *
			 * @return integer Total posts available.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function total_posts(array $args = array())
			{
				$default_args = array(
					'for_comments_only'          => FALSE,
                    'include_post_types'         => array(),
					'exclude_post_types'         => array(),
					'exclude_post_statuses'      => array(),
					'exclude_password_protected' => FALSE,
					'no_cache'                   => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$for_comments_only          = (boolean)$args['for_comments_only'];
                $include_post_types         = (array)$args['include_post_types'];
				$exclude_post_types         = (array)$args['exclude_post_types'];
				$exclude_post_statuses      = (array)$args['exclude_post_statuses'];
				$exclude_password_protected = (boolean)$args['exclude_password_protected'];
				$no_cache                   = (boolean)$args['no_cache'];

				$cache_keys = compact('for_comments_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected');

				if(!is_null($total = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $total; // Already cached this.

                $post_types    = $include_post_types ? $include_post_types : get_post_types(array('exclude_from_search' => FALSE));
				$post_statuses = get_post_stati(array('exclude_from_search' => FALSE));

				$sql = "SELECT SQL_CALC_FOUND_ROWS `ID` FROM `".esc_html($this->wp->posts)."`".

				       " WHERE `post_type` IN('".implode("','", array_map('esc_sql', $post_types))."')".
				       ($exclude_post_types ? " AND `post_type` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_types))."')" : '').

				       " AND `post_status` IN('".implode("','", array_map('esc_sql', $post_statuses))."')".
				       ($exclude_post_statuses ? " AND `post_status` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_statuses))."')" : '').

				       ($exclude_password_protected ? " AND `post_password` = ''" : ''). // Exlude password protected posts?

				       ($for_comments_only // For comments only?
					       ? " AND (`comment_status` IN('1', 'open', 'opened')".
					         "     OR `comment_count` > '0')"
					       : '').

				       " LIMIT 1"; // One to check.

				if($this->wp->query($sql) === FALSE) // Initial query failure?
					throw new \exception(__('Query failure.', $this->plugin->text_domain));

				return ($total = (integer)$this->wp->get_var("SELECT FOUND_ROWS()"));
			}

			/**
			 * All posts quickly.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Behavioral args (optional).
			 *
			 * @return \stdClass[] An array of all posts.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function all_posts(array $args = array())
			{
				$default_args = array(
					'max'                        => PHP_INT_MAX,
					'fail_on_max'                => FALSE,
					'for_comments_only'          => FALSE,
                    'include_post_types'         => array(),
					'exclude_post_types'         => array(),
					'exclude_post_statuses'      => array(),
					'exclude_password_protected' => FALSE,
					'no_cache'                   => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$max                        = (integer)$args['max'];
				$max                        = $max < 1 ? 1 : $max;
				$fail_on_max                = (boolean)$args['fail_on_max'];
				$for_comments_only          = (boolean)$args['for_comments_only'];
                $include_post_types         = (array)$args['include_post_types'];
				$exclude_post_types         = (array)$args['exclude_post_types'];
				$exclude_post_statuses      = (array)$args['exclude_post_statuses'];
				$exclude_password_protected = (boolean)$args['exclude_password_protected'];
				$no_cache                   = (boolean)$args['no_cache'];

				$cache_keys = compact('max', 'fail_on_max', 'for_comments_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected');

				if(!is_null($posts = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $posts; // Already cached this.

				if($fail_on_max && $this->total_posts($args) > $max)
					return ($posts = array()); // Fail when there are too many.

                $post_types    = $include_post_types ? $include_post_types : get_post_types(array('exclude_from_search' => FALSE));
				$post_statuses = get_post_stati(array('exclude_from_search' => FALSE));

				$columns = array(
					'ID',
					'post_author',
					'post_date_gmt',
					'post_title',
					'post_status',
					'comment_status',
					'post_name',
					'post_parent',
					'post_type',
					'comment_count',
				);
				$sql     = "SELECT `".implode("`,`", array_map('esc_sql', $columns))."`".
				           " FROM `".esc_html($this->wp->posts)."`".

				           " WHERE `post_type` IN('".implode("','", array_map('esc_sql', $post_types))."')".
				           ($exclude_post_types ? " AND `post_type` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_types))."')" : '').

				           " AND `post_status` IN('".implode("','", array_map('esc_sql', $post_statuses))."')".
				           ($exclude_post_statuses ? " AND `post_status` NOT IN('".implode("','", array_map('esc_sql', $exclude_post_statuses))."')" : '').

				           ($exclude_password_protected ? " AND `post_password` = ''" : ''). // Exlude password protected posts?

				           ($for_comments_only // For comments only?
					           ? " AND (`comment_status` IN('1', 'open', 'opened')".
					             "     OR `comment_count` > '0')"
					           : '').

				           " ORDER BY `post_type` ASC, `post_date_gmt` DESC".

				           ($max !== PHP_INT_MAX ? " LIMIT ".esc_sql($max) : '');

				if(($results = $this->wp->get_results($sql, OBJECT_K)))
				{
					$post_results = $page_results // Initialize.
						= $media_results = $other_results = array();

					foreach($results as $_key => $_result)
					{
						if($_result->post_type === 'post')
							$post_results[$_key] = $_result;

						else if($_result->post_type === 'page')
							$page_results[$_key] = $_result;

						else if($_result->post_type === 'attachment')
							$media_results[$_key] = $_result;

						else $other_results[$_key] = $_result;
					}
					unset($_key, $_result); // Housekeeping.

					$results // Change precedence of certain post types.
						= $post_results + $page_results  // Highest priority.
						  + $other_results  // Everything else.
						  + $media_results; // Lowest priority.

					return ($posts = $results = $this->typify_deep($results));
				}
				return ($posts = array()); // Default return value.
			}

			/**
			 * Counts total comments.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id A post ID.
			 * @param array   $args Behavioral args (optional).
			 *
			 * @return integer Total comments available.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function total_comments($post_id, array $args = array())
			{
				if(!($post_id = (integer)$post_id))
					return 0; // Not possible.

				$default_args = array(
					'parents_only'               => FALSE,
                    'include_post_types'         => array(),
					'exclude_post_types'         => array(),
					'exclude_post_statuses'      => array(),
					'exclude_password_protected' => FALSE,
					'no_cache'                   => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$parents_only               = (boolean)$args['parents_only'];
                $include_post_types         = (array)$args['include_post_types'];
				$exclude_post_types         = (array)$args['exclude_post_types'];
				$exclude_post_statuses      = (array)$args['exclude_post_statuses'];
				$exclude_password_protected = (boolean)$args['exclude_password_protected'];
				$no_cache                   = (boolean)$args['no_cache'];

				$cache_keys = compact('post_id', 'parents_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected');

				if(!is_null($total = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $total; // Already cached this.

				if(!($post = get_post($post_id))) return ($total = 0);

                if($include_post_types && !in_array($post->post_type, $include_post_types, TRUE))
                    return ($total = 0); // Post type not included; automatic zero.

				if($exclude_post_types && in_array($post->post_type, $exclude_post_types, TRUE))
					return ($total = 0); // Post type is excluded; automatic zero.

				if($exclude_post_statuses && in_array($post->post_status, $exclude_post_statuses, TRUE))
					return ($total = 0); // Post status is excluded; automatic zero.

				if($exclude_password_protected && $post->post_password) // Has password?
					return ($total = 0); // Passwords excluded; automatic zero.

				$sql = "SELECT SQL_CALC_FOUND_ROWS `comment_ID` FROM `".esc_html($this->wp->comments)."`".

				       " WHERE `comment_post_ID` = '".esc_sql($post_id)."'".
				       " AND (`comment_type` = '' OR `comment_type` = 'comment')".

				       ($parents_only // Parents only?
					       ? " AND `comment_parent` <= '0'" : '').

				       " LIMIT 1"; // One to check.

				if($this->wp->query($sql) === FALSE) // Initial query failure?
					throw new \exception(__('Query failure.', $this->plugin->text_domain));

				return ($total = (integer)$this->wp->get_var("SELECT FOUND_ROWS()"));
			}

			/**
			 * All comments quickly.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id A post ID.
			 * @param array   $args Behavioral args (optional).
			 *
			 * @return \stdClass[] An array of all comments.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function all_comments($post_id, array $args = array())
			{
				if(!($post_id = (integer)$post_id))
					return array(); // Not possible.

				$default_args = array(
					'max'                        => PHP_INT_MAX,
					'fail_on_max'                => FALSE,
					'parents_only'               => FALSE,
                    'include_post_types'         => array(),
					'exclude_post_types'         => array(),
					'exclude_post_statuses'      => array(),
					'exclude_password_protected' => FALSE,
					'no_cache'                   => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$max                        = (integer)$args['max'];
				$max                        = $max < 1 ? 1 : $max;
				$fail_on_max                = (boolean)$args['fail_on_max'];
				$parents_only               = (boolean)$args['parents_only'];
                $include_post_types         = (array)$args['include_post_types'];
				$exclude_post_types         = (array)$args['exclude_post_types'];
				$exclude_post_statuses      = (array)$args['exclude_post_statuses'];
				$exclude_password_protected = (boolean)$args['exclude_password_protected'];
				$no_cache                   = (boolean)$args['no_cache'];

				$cache_keys = compact('post_id', 'max', 'fail_on_max', 'parents_only', 'exclude_post_types', 'exclude_post_statuses', 'exclude_password_protected');

				if(!is_null($comments = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $comments; // Already cached this.

				if(!($post = get_post($post_id))) return ($comments = array());

                if($include_post_types && !in_array($post->post_type, $include_post_types, TRUE))
                    return ($comments = array()); // Post type not included; automatic empty.

				if($exclude_post_types && in_array($post->post_type, $exclude_post_types, TRUE))
					return ($comments = array()); // Post type is excluded; automatic empty.

				if($exclude_post_statuses && in_array($post->post_status, $exclude_post_statuses, TRUE))
					return ($comments = array()); // Post status is excluded; automatic empty.

				if($exclude_password_protected && $post->post_password) // Has a password?
					return ($comments = array()); // Passwords excluded; automatic empty.

				if($fail_on_max && $this->total_comments($post_id, $args) > $max)
					return ($comments = array()); // Fail when there are too many.

				$columns = array(
					'comment_ID',
					'comment_post_ID',
					'comment_author',
					'comment_author_email',
					'comment_date_gmt',
					'comment_approved',
					'comment_type',
					'comment_parent',
					'comment_content',
				);
				$sql     = "SELECT `".implode("`,`", array_map('esc_sql', $columns))."`".
				           " FROM `".esc_html($this->wp->comments)."`".

				           " WHERE `comment_post_ID` = '".esc_sql($post_id)."'".
				           " AND (`comment_type` = '' OR `comment_type` = 'comment')".

				           ($parents_only // Parents only?
					           ? " AND `comment_parent` <= '0'" : '').

				           " ORDER BY `comment_date_gmt` ASC".

				           ($max !== PHP_INT_MAX ? " LIMIT ".esc_sql($max) : '');

				if(($results = $this->wp->get_results($sql, OBJECT_K)))
					return ($comments = $results = $this->typify_deep($results));

				return ($comments = array()); // Default return value.
			}

			/**
			 * Pagination links start page.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $current_page The current page number.
			 * @param integer $total_pages The total pages available.
			 * @param integer $max_links Max pagination links to display.
			 *
			 * @return integer The page number to begin pagination links from.
			 *
			 * @note This method has been tested; even against invalid figures.
			 *    It handles every scenario gracefully; even if invalid figures are given.
			 */
			public function pagination_links_start_page($current_page, $total_pages, $max_links)
			{
				$current_page = (integer)$current_page;
				$total_pages  = (integer)$total_pages;
				$max_links    = (integer)$max_links;

				$min_start_page = 1; // Obviously.
				$max_start_page = max($total_pages - ($max_links - 1), $min_start_page);
				$start_page     = max(min($current_page - floor($max_links / 2), $max_start_page), $min_start_page);

				return (integer)$start_page;
			}
		}
	}
}
