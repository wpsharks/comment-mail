<?php
/**
 * URL Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_url'))
	{
		/**
		 * URL Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_url extends abs_base
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
			 * Current scheme; lowercase.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current scheme; lowercase.
			 */
			public function current_scheme()
			{
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$this->static[__FUNCTION__] = NULL; // Initialize.
				$scheme                     = &$this->static[__FUNCTION__];

				return ($scheme = is_ssl() ? 'https' : 'http');
			}

			/**
			 * Current host name; lowercase.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current host name; lowercase.
			 */
			public function current_host()
			{
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$this->static[__FUNCTION__] = NULL; // Initialize.
				$host                       = &$this->static[__FUNCTION__];

				return ($host = strtolower((string)$_SERVER['HTTP_HOST']));
			}

			/**
			 * Current URI; with a leading `/`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URI; with a leading `/`.
			 */
			public function current_uri()
			{
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$this->static[__FUNCTION__] = NULL; // Initialize.
				$uri                        = &$this->static[__FUNCTION__];

				return ($uri = '/'.ltrim((string)$_SERVER['REQUEST_URI'], '/'));
			}

			/**
			 * Current URI/path; with a leading `/`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URI/path; with a leading `/`.
			 */
			public function current_path()
			{
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$this->static[__FUNCTION__] = NULL; // Initialize.
				$path                       = &$this->static[__FUNCTION__];

				return ($path = '/'.ltrim((string)parse_url($this->current_uri(), PHP_URL_PATH), '/'));
			}

			/**
			 * Current URL; i.e. scheme.host.URI put together.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; i.e. scheme.host.URI put together.
			 */
			public function current()
			{
				if(isset($this->static[__FUNCTION__]))
					return $this->static[__FUNCTION__];

				$this->static[__FUNCTION__] = NULL; // Initialize.
				$url                        = &$this->static[__FUNCTION__];

				return ($url = $this->current_scheme().'://'.$this->current_host().$this->current_uri());
			}

			/**
			 * URL without a query string.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL without a query string.
			 */
			public function no_query($url = '')
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				return strpos($url, '?') !== FALSE ? (string)strstr($url, '?', TRUE) : $url;
			}

			/**
			 * URL with `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL with `_wpnonce`.
			 */
			public function nonce($nonce_action = __NAMESPACE__, $url = '')
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$args = array('_wpnonce' => wp_create_nonce($nonce_action));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * URL with only a `page` var (if applicable).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $page A specific page value (optional).
			 *    If empty, we use `page` from the URL; else current `page`.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL with only a `page` var (if applicable).
			 */
			public function page_only($page = '', $url = '')
			{
				$page = trim((string)$page);

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$query = (string)parse_url($url, PHP_URL_QUERY);
				wp_parse_str($query, $query_vars);
				$url = $this->no_query($url);

				if(!$page && !empty($query_vars['page']))
					$page = trim((string)$query_vars['page']);

				if(!$page && !empty($_REQUEST['page']))
					$page = trim(stripslashes((string)$_REQUEST['page']));

				$args = $page ? array('page' => $page) : array();

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * URL with only a `page` var (if applicable) and `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $page A specific page value (optional).
			 *    If empty, we use `page` from the URL; else current `page`.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL with only a `page` var (if applicable) and `_wpnonce`.
			 */
			public function page_nonce_only($page = '', $nonce_action = __NAMESPACE__, $url = '')
			{
				return $this->nonce($nonce_action, $this->page_only($page, $url));
			}

			/**
			 * Main menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Main menu page URL.
			 */
			public function main_menu_page_only()
			{
				return $this->page_only(__NAMESPACE__, admin_url('/edit-comments.php'));
			}

			/**
			 * Main menu page URL; w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @return string Main menu page URL; w/ `_wpnonce`.
			 */
			public function main_menu_page_nonce_only($nonce_action = __NAMESPACE__)
			{
				return $this->nonce($nonce_action, $this->main_menu_page_only());
			}

			/**
			 * Subscriptions menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Subscriptions menu page URL.
			 */
			public function subs_menu_page_only()
			{
				return $this->page_only(__NAMESPACE__.'_subs', admin_url('/edit-comments.php'));
			}

			/**
			 * Sub. event log menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Sub. event log menu page URL.
			 */
			public function sub_event_log_menu_page_only()
			{
				return $this->page_only(__NAMESPACE__.'_sub_event_log', admin_url('/edit-comments.php'));
			}

			/**
			 * Queue menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Queue menu page URL.
			 */
			public function queue_menu_page_only()
			{
				return $this->page_only(__NAMESPACE__.'_queue', admin_url('/edit-comments.php'));
			}

			/**
			 * Queue event log menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Queue event log menu page URL.
			 */
			public function queue_event_log_menu_page_only()
			{
				return $this->page_only(__NAMESPACE__.'_queue_event_log', admin_url('/edit-comments.php'));
			}

			/**
			 * Restore default options URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Restore default options URL.
			 */
			public function restore_default_options()
			{
				$args = array(__NAMESPACE__ => array('restore_default_options' => '1'));

				return add_query_arg(urlencode_deep($args), $this->main_menu_page_nonce_only());
			}

			/**
			 * Options restored URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Options restored URL.
			 */
			public function options_restored()
			{
				$args = array(__NAMESPACE__.'_options_restored' => '1');

				return add_query_arg(urlencode_deep($args), $this->main_menu_page_only());
			}

			/**
			 * Options updated URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current menu page.
			 *
			 * @return string Options updated URL.
			 */
			public function options_updated($url = '')
			{
				if(!($url = trim((string)$url)))
					$url = $this->page_only();

				$args = array(__NAMESPACE__.'_options_updated' => '1');

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Pro preview URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the main menu page.
			 *
			 * @return string Pro preview URL.
			 */
			public function pro_preview($url = '')
			{
				if(!($url = trim((string)$url)))
					$url = $this->main_menu_page_only();

				$args = array(__NAMESPACE__.'_pro_preview' => '1');

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Adds search filter(s) to the `s` key for tables.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string|array A string or an array of filters.
			 *    e.g. `array('post_ids:1,2,3', 'comment_ids:4,5,6')`.
			 *    e.g. `post_ids:1,2,3 comment_ids:4,5,6`.
			 *
			 *    You can pass `:` or `::` to remove existing filters in that specific <group>;
			 *       i.e. without adding new filters; it just removes all filters in <group>.
			 *
			 *    You can pass `type:` or `type::` to remove existing filters of that specific <type><group>;
			 *       i.e. without adding new filters; it just removes all filters of <type><group>.
			 *
			 * @param string       $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL w/ search filters added to the `s` key.
			 */
			public function table_search_filter($filters, $url = '')
			{
				if(is_array($filters)) // Force string.
					$filters = implode(' ', $filters);
				$filters = trim((string)$filters);

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$query = (string)parse_url($url, PHP_URL_QUERY);
				wp_parse_str($query, $query_vars);

				$s            = !empty($query_vars['s']) ? (string)$query_vars['s'] : '';
				$filters      = preg_split('/\s+/', $filters, NULL, PREG_SPLIT_NO_EMPTY);
				$filter_regex = '/\b(?P<type>\w+)(?P<group>\:+)(?P<values>[\w+|;,]+)?/i';

				foreach($filters as $_filter) // Remove filters in <group> or of <type><group>.
				{
					if(preg_match('/^\:+$/', $_filter)) // Specifies a <group> to remove only?
						$s = preg_replace(str_replace('<group>\:+', // Remove filters in this <group>.
						                              '<group>\:{'.strlen($_filter).'}', $filter_regex), '', $s);

					else if(preg_match($filter_regex, $_filter, $_filter_m)) // Remove <type><group>?
						$s = preg_replace(str_replace('<type>\w+', // Remove filters of this <type><group>.
						                              '<type>'.preg_quote(rtrim($_filter_m['type'], 's'), '/').'s*',
						                              str_replace('<group>\:+', // We convert the <group> first; nested inside.
						                                          '<group>\:{'.strlen($_filter_m['group']).'}', $filter_regex)), '', $s);
				}
				foreach($filters as $_filter) // Add each of the new filters.
				{
					if(preg_match($filter_regex, $_filter, $_filter_m) && isset($_filter_m['values'][0]))
						$s .= ' '.$_filter; // Only if valid; and only if it has values.
				}
				unset($_filter, $_filter_m); // Just housekeeping.

				$s = trim(preg_replace('/\s+/', ' ', $s));
				// Note: `FALSE` tells `add_query_arg()` to remove `s`.
				return add_query_arg('s', $s ? urlencode($s) : FALSE, $url);
			}

			/**
			 * Bulk action URL generator for tables.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $plural Plural table name/key.
			 * @param array  $ids An array of IDs to act upon.
			 * @param string $action The bulk action to perform.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL leading to the bulk action necessary.
			 */
			public function table_bulk_action($plural, array $ids, $action, $url = '')
			{
				$plural = trim((string)$plural);
				$action = trim((string)$action);

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$args = array($plural => $ids, 'action' => $action);

				return $this->nonce('bulk-'.$plural, add_query_arg(urlencode_deep($args), $url));
			}

			/**
			 * URL w/ page & table nav vars only.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array  $also_keep Any additional names/keys to keep.
			 *
			 *    Built-in names/keys to keep already includes the following:
			 *       `page`, `orderby`, `order`, and `s` for searches.
			 *
			 *    If `_wponce` is passed in this array, we not only keep that variable,
			 *    but we also generate a new `_wpnonce` key too. In short, `_wpnonce` is
			 *    forced into the URL w/ a fresh value when keeping `_wp_nonce`.
			 *    ~ See also: {@link page_nonce_table_nav_vars_only()}.
			 *
			 * @param string $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    ~ See also: {@link page_nonce_table_nav_vars_only()}.
			 *
			 * @return string URL w/ page & table nav vars only.
			 */
			public function page_table_nav_vars_only(array $also_keep = array(), $url = '', $nonce_action = __NAMESPACE__)
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$_r    = $this->plugin->utils_string->trim_strip_deep($_REQUEST);
				$query = (string)parse_url($url, PHP_URL_QUERY);
				wp_parse_str($query, $query_vars);
				$url = $this->no_query($url);

				$also_keep = array_map('strval', $also_keep);
				$keepers   = array('page', 'orderby', 'order', 's');
				$keepers   = array_unique(array_merge($keepers, $also_keep));

				foreach($keepers as $_keeper) // Add keepers back onto the clean URL.
				{
					if(!empty($query_vars[$_keeper])) // In query vars?
						$url = add_query_arg(urlencode($_keeper), urlencode($query_vars[$_keeper]), $url);

					else if(!empty($_r[$_keeper])) // In the current request array?
						$url = add_query_arg(urlencode($_keeper), urlencode($_r[$_keeper]), $url);
				}
				unset($_keeper); // Housekeeping.

				if(in_array('_wpnonce', $also_keep, TRUE)) // Generate a fresh value.
					$url = add_query_arg('_wpnonce', urlencode(wp_create_nonce($nonce_action)), $url);

				return $url; // With page & table nav vars only.
			}

			/**
			 * URL w/ page, nonce & table nav vars only.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array  $also_keep See {@link page_table_nav_vars_only()}.
			 * @param string $url See {@link page_table_nav_vars_only()}.
			 * @param string $nonce_action See {@link page_table_nav_vars_only()}.
			 *
			 * @return string See {@link page_table_nav_vars_only()}.
			 */
			public function page_nonce_table_nav_vars_only(array $also_keep = array(), $url = '', $nonce_action = __NAMESPACE__)
			{
				return $this->page_table_nav_vars_only(array_merge($also_keep, array('_wpnonce')), $url, $nonce_action);
			}

			/**
			 * Notice dimissal URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key The notice key to dismiss.
			 *
			 * @return string Notice dimissal URL.
			 */
			public function dismiss_notice($key)
			{
				$key  = trim((string)$key); // Key to dismiss.
				$args = array(__NAMESPACE__ => array('dismiss_notice' => compact('key')));

				return add_query_arg(urlencode_deep($args), $this->nonce());
			}

			/**
			 * Notice dimissed URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Notice dimissed URL.
			 */
			public function notice_dismissed()
			{
				return remove_query_arg(__NAMESPACE__, $this->current());
			}

			/**
			 * Product page URL; normally at WebSharks™.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Product page URL; normally at WebSharks™.
			 */
			public function product_page()
			{
				return 'http://www.websharks-inc.com/product/'.urlencode($this->plugin->slug).'/';
			}

			/**
			 * Subscribe page URL; normally at WebSharks™.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Subscribe page URL; normally at WebSharks™.
			 */
			public function subscribe_page()
			{
				return 'http://www.websharks-inc.com/r/'.urlencode($this->plugin->slug).'-subscribe/';
			}

			/**
			 * URL to a plugin file.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $file Optional file path; relative to plugin directory.
			 * @param string|null $scheme Optional URL scheme. Defaults to the current scheme.
			 *
			 * @return string URL to plugin directory; or to the specified `$file` if applicable.
			 */
			public function to($file = '', $scheme = NULL)
			{
				if(!isset($this->static[__FUNCTION__]['plugin_dir_url']))
					$this->static[__FUNCTION__]['plugin_dir_url'] = rtrim(plugin_dir_url($this->plugin->file), '/');
				$plugin_dir_url = &$this->static[__FUNCTION__]['plugin_dir_url']; // Reference.

				return set_url_scheme($plugin_dir_url.(string)$file, $scheme);
			}

			/**
			 * Checks for a valid `_wpnonce` value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string $url A specific URL to check?
			 *    Defaults to the current URL; i.e. current `$_REQUEST`.
			 *
			 * @return boolean TRUE if it has a valid `_wpnonce`.
			 */
			public function has_valid_nonce($nonce_action = __NAMESPACE__, $url = '')
			{
				if(($url = trim((string)$url)))
					wp_parse_str((string)@parse_url($url, PHP_URL_QUERY), $_r);
				else $_r = stripslashes_deep($_REQUEST);

				if(!empty($_r['_wpnonce']) && wp_verify_nonce($_r['_wpnonce'], $nonce_action))
					return TRUE; // Valid `_wpnonce` value.

				return FALSE; // Unauthenticated; failure.
			}

			/**
			 * Creates a post shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id A WP post ID.
			 *
			 * @return string Post shortlink.
			 */
			public function post_short($post_id)
			{
				$post_id = (integer)$post_id;
				$args    = array('p' => $post_id);

				return add_query_arg(urlencode_deep($args), home_url('/'));
			}

			/**
			 * Creates a post edit shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id A WP post ID.
			 *
			 * @return string Post edit shortlink.
			 */
			public function post_edit_short($post_id)
			{
				$post_id = (integer)$post_id;
				$args    = array('post' => $post_id, 'action' => 'edit');

				return add_query_arg(urlencode_deep($args), admin_url('/post.php'));
			}

			/**
			 * Creates an edit comments shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id A WP post ID.
			 *
			 * @return string Post edit comments shortlink.
			 */
			public function post_edit_comments_short($post_id)
			{
				$post_id = (integer)$post_id;
				$args    = array('p' => $post_id);

				return add_query_arg(urlencode_deep($args), admin_url('/edit-comments.php'));
			}

			/**
			 * Creates an edit subscriptions shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id A WP post ID.
			 * @param string  $s Any additional search words/filters.
			 *
			 * @return string Post edit subscriptions shortlink.
			 */
			public function post_edit_subs_short($post_id, $s = '')
			{
				$post_id = (integer)$post_id;
				$s       = trim((string)$s);
				$args    = array('s' => 'post_id:'.$post_id.($s ? ' '.$s : ''));

				return add_query_arg(urlencode_deep($args), $this->subs_menu_page_only());
			}

			/**
			 * Creates a new subscription shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string New subscription shortlink.
			 */
			public function new_sub_short()
			{
				$args = array('action' => 'new');
				$url  = $this->page_table_nav_vars_only(array(), $this->subs_menu_page_only());

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates an edit subscription shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $sub_id Subscription ID.
			 *
			 * @return string Edit subscription shortlink.
			 */
			public function edit_sub_short($sub_id)
			{
				$sub_id = (integer)$sub_id;
				$args   = array('action' => 'edit', 'subscription' => $sub_id);
				$url    = $this->page_table_nav_vars_only(array(), $this->subs_menu_page_only());

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates an edit user shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $user_id A WP User ID.
			 *
			 * @return string Edit user shortlink.
			 */
			public function edit_user_short($user_id)
			{
				$user_id = (integer)$user_id;
				$args    = array('user_id' => $user_id);

				return add_query_arg(urlencode_deep($args), admin_url('/user-edit.php'));
			}

			/**
			 * Creates a comment shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $comment_id A WP comment ID.
			 *
			 * @return string Comment shortlink.
			 */
			public function comment_short($comment_id)
			{
				$comment_id = (integer)$comment_id;
				$args       = array('c' => $comment_id);

				return add_query_arg(urlencode_deep($args), home_url('/'));
			}

			/**
			 * Creates a comment edit shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $comment_id A WP comment ID.
			 *
			 * @return string Comment edit shortlink.
			 */
			public function comment_edit_short($comment_id)
			{
				$comment_id = (integer)$comment_id;
				$args       = array('action' => 'editcomment', 'c' => $comment_id);

				return add_query_arg(urlencode_deep($args), admin_url('/comment.php'));
			}
		}
	}
}