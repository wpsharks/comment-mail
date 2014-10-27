<?php
/**
 * DB Utilities
 *
 * @since 14xxxx First documented version.
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
		 * @since 14xxxx First documented version.
		 */
		class utils_db extends abs_base
		{
			/**
			 * @var \wpdb WP DB class reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			public $wp;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->wp = $GLOBALS['wpdb'];
			}

			/**
			 * Current DB prefix for this plugin.
			 *
			 * @since 14xxxx First documented version.
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
			 * @since 14xxxx First documented version.
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
			 * @since 14xxxx First documented version.
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

				if(in_array($key, $integer_keys, TRUE))
					return TRUE;

				if(preg_match('/_(?:'.implode('|', $preg_quoted_integer_keys).')$/i', $key))
					return TRUE;

				return FALSE; // Default.
			}

			/**
			 * Should an array/object key contain a float value?
			 *
			 * @since 14xxxx First documented version.
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
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sql Input SQL to check.
			 *
			 * @return string Output `$sql` w/ possible engine modification.
			 *    Only MySQL v5.6+ supports fulltext indexes with the InnoDB engine.
			 *    Otherwise, we use MyISAM for any table that includes a fulltext index.
			 *
			 * @note MySQL v5.6+ supports fulltext indexes w/ InnoDB.
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
				if($mysql_version && version_compare($mysql_version, '5.6', '>='))
					return $sql; // MySQL v5.6+ supports fulltext indexes.

				return preg_replace('/\bENGINE\=InnoDB\b/i', 'ENGINE=MyISAM', $sql);
			}

			/**
			 * Comment status translator.
			 *
			 * @since 14xxxx First documented version.
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
			 * @since 14xxxx First documented version.
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
			 * @since 14xxxx First documented version.
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

				if(!$no_cache && isset($this->cache[__FUNCTION__]))
					return $this->cache[__FUNCTION__];

				$this->cache[__FUNCTION__] = 0; // Initialize.
				$total                     = &$this->cache[__FUNCTION__];

				$sql = "SELECT SQL_CALC_FOUND_ROWS `ID` FROM `".esc_html($this->wp->users)."`".

				       " LIMIT 1"; // One to check.

				if($this->wp->query($sql) === FALSE) // Initial query failure?
					throw new \exception(__('Query failure.', $this->plugin->text_domain));

				return ($total = (integer)$this->wp->get_var("SELECT FOUND_ROWS()"));
			}

			/**
			 * All users quickly.
			 *
			 * @since 14xxxx First documented version.
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

				if(!$no_cache && isset($this->cache[__FUNCTION__][$max][(integer)$fail_on_max]))
					return $this->cache[__FUNCTION__][$max][(integer)$fail_on_max];

				$this->cache[__FUNCTION__][$max][(integer)$fail_on_max]
					    = array(); // Initialize cache entry for reference used below.
				$users = &$this->cache[__FUNCTION__][$max][(integer)$fail_on_max];

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
			 * @since 14xxxx First documented version.
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
					'for_comments_only' => FALSE,
					'no_cache'          => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$for_comments_only = (boolean)$args['for_comments_only'];
				$no_cache          = (boolean)$args['no_cache'];

				if(!$no_cache && isset($this->cache[__FUNCTION__][(integer)$for_comments_only]))
					return $this->cache[__FUNCTION__][(integer)$for_comments_only];

				$this->cache[__FUNCTION__][(integer)$for_comments_only]
					    = 0; // Initialize cache entry for reference used below.
				$total = &$this->cache[__FUNCTION__][(integer)$for_comments_only];

				$post_types    = get_post_types(array('exclude_from_search' => FALSE));
				$post_statuses = get_post_stati(array('exclude_from_search' => FALSE));

				$sql = "SELECT SQL_CALC_FOUND_ROWS `ID` FROM `".esc_html($this->wp->posts)."`".

				       " WHERE `post_type` IN('".implode("','", array_map('esc_sql', $post_types))."')".
				       " AND `post_status` IN('".implode("','", array_map('esc_sql', $post_statuses))."')".

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
			 * @since 14xxxx First documented version.
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
					'max'                   => PHP_INT_MAX,
					'fail_on_max'           => FALSE,
					'for_comments_only'     => FALSE,
					'exclude_post_types'    => array(),
					'exclude_post_statuses' => array(),
					'no_cache'              => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$max                   = (integer)$args['max'];
				$max                   = $max < 1 ? 1 : $max;
				$fail_on_max           = (boolean)$args['fail_on_max'];
				$for_comments_only     = (boolean)$args['for_comments_only'];
				$exclude_post_types    = (array)$args['exclude_post_types'];
				$exclude_post_statuses = (array)$args['exclude_post_statuses'];
				$no_cache              = (boolean)$args['no_cache'];

				$max_key                   = $max;
				$fail_on_max_key           = (integer)$fail_on_max;
				$for_comments_only_key     = (integer)$for_comments_only;
				$exclude_post_types_key    = sha1(serialize($exclude_post_types));
				$exclude_post_statuses_key = sha1(serialize($exclude_post_statuses));

				if(!$no_cache && isset($this->cache[__FUNCTION__][$max_key][$fail_on_max_key][$for_comments_only_key][$exclude_post_types_key][$exclude_post_statuses_key]))
					return $this->cache[__FUNCTION__][$max_key][$fail_on_max_key][$for_comments_only_key][$exclude_post_types_key][$exclude_post_statuses_key];

				$this->cache[__FUNCTION__][$max_key][$fail_on_max_key][$for_comments_only_key][$exclude_post_types_key][$exclude_post_statuses_key]
					    = array(); // Initialize cache entry for reference used below.
				$posts = &$this->cache[__FUNCTION__][$max_key][$fail_on_max_key][$for_comments_only_key][$exclude_post_types_key][$exclude_post_statuses_key];

				if($fail_on_max && $this->total_posts($args) > $max)
					return ($posts = array()); // Fail when there are too many.

				$post_types    = get_post_types(array('exclude_from_search' => FALSE));
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

				           ($for_comments_only // For comments only?
					           ? " AND (`comment_status` IN('1', 'open', 'opened')".
					             "     OR `comment_count` > '0')"
					           : '').

				           " ORDER BY `post_type` ASC, `post_title` ASC".

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
			 * @since 14xxxx First documented version.
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
					'parents_only' => FALSE,
					'no_cache'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$parents_only = (boolean)$args['parents_only'];
				$no_cache     = (boolean)$args['no_cache'];

				if(!$no_cache && isset($this->cache[__FUNCTION__][$post_id][(integer)$parents_only]))
					return $this->cache[__FUNCTION__][$post_id][(integer)$parents_only];

				$this->cache[__FUNCTION__][$post_id][(integer)$parents_only]
					    = 0; // Initialize cache entry for reference used below.
				$total = &$this->cache[__FUNCTION__][$post_id][(integer)$parents_only];

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
			 * @since 14xxxx First documented version.
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
					'max'          => PHP_INT_MAX,
					'fail_on_max'  => FALSE,
					'parents_only' => FALSE,
					'no_cache'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$max          = (integer)$args['max'];
				$max          = $max < 1 ? 1 : $max;
				$fail_on_max  = (boolean)$args['fail_on_max'];
				$parents_only = (boolean)$args['parents_only'];
				$no_cache     = (boolean)$args['no_cache'];

				if(!$no_cache && isset($this->cache[__FUNCTION__][$post_id][$max][(integer)$fail_on_max][(integer)$parents_only]))
					return $this->cache[__FUNCTION__][$post_id][$max][(integer)$fail_on_max][(integer)$parents_only];

				$this->cache[__FUNCTION__][$post_id][$max][(integer)$fail_on_max][(integer)$parents_only]
					       = array(); // Initialize cache entry for reference used below.
				$comments = &$this->cache[__FUNCTION__][$post_id][$max][(integer)$fail_on_max][(integer)$parents_only];

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
		}
	}
}