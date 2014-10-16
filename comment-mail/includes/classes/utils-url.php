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
		class utils_url extends abstract_base
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
				return is_ssl() ? 'https' : 'http';
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
				return strtolower((string)$_SERVER['HTTP_HOST']);
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
				return '/'.ltrim((string)$_SERVER['REQUEST_URI'], '/');
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
				return '/'.ltrim((string)parse_url($this->current_uri(), PHP_URL_PATH), '/');
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
				return $this->current_scheme().'://'.$this->current_host().$this->current_uri();
			}

			/**
			 * Current URL; without a query string.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; without a query string.
			 */
			public function current_no_query()
			{
				$current = $this->current(); // Current URL w/ possible query string.

				return strpos($current, '?') !== FALSE ? (string)strstr($current, '?', TRUE) : $current;
			}

			/**
			 * Current URL; with `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @return string Current URL; with `_wpnonce`.
			 */
			public function current_nonce($nonce_action = __NAMESPACE__)
			{
				$args = array('_wpnonce' => wp_create_nonce($nonce_action));

				return add_query_arg(urlencode_deep($args), $this->current());
			}

			/**
			 * Current URL; with only a `page` var (if applicable).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current URL; with only a `page` var (if applicable).
			 */
			public function current_page_only()
			{
				$page = !empty($_REQUEST['page'])
					? stripslashes((string)$_REQUEST['page']) : '';
				$args = $page ? array('page' => $page) : array(); // If applicable.

				return add_query_arg(urlencode_deep($args), $this->current_no_query());
			}

			/**
			 * Current URL; with only a `page` var (if applicable) and `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @return string Current URL; with only a `page` var (if applicable) and `_wpnonce`.
			 */
			public function current_page_nonce_only($nonce_action = __NAMESPACE__)
			{
				$args = array('_wpnonce' => wp_create_nonce($nonce_action));

				return add_query_arg(urlencode_deep($args), $this->current_page_only());
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
				$args = array('page' => __NAMESPACE__);

				return add_query_arg(urlencode_deep($args), admin_url('/edit-comments.php'));
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
				$args = array('_wpnonce' => wp_create_nonce($nonce_action));

				return add_query_arg(urlencode_deep($args), $this->main_menu_page_only());
			}

			/**
			 * Subscribers menu page URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Subscribers menu page URL.
			 */
			public function subscribers_menu_page_only()
			{
				$args = array('page' => __NAMESPACE__.'_subscribers');

				return add_query_arg(urlencode_deep($args), admin_url('/edit-comments.php'));
			}

			/**
			 * Restore default options URL; for main menu page w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @return string Restore default options URL; for main menu page w/ `_wpnonce`.
			 */
			public function restore_default_options($nonce_action = __NAMESPACE__)
			{
				$args = array(__NAMESPACE__ => array('restore_default_options' => '1'));

				return add_query_arg(urlencode_deep($args), $this->main_menu_page_nonce_only($nonce_action));
			}

			/**
			 * Add options restored flag to a given URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to flag (optional).
			 *    If empty, defaults to the current menu page.
			 *
			 * @return string The input `$url` with an options restored flag.
			 */
			public function options_restored($url = '')
			{
				$args = array(__NAMESPACE__.'_options_restored' => '1');

				return add_query_arg(urlencode_deep($args), $url ? (string)$url : $this->current_page_only());
			}

			/**
			 * Add options updated flag to a given URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to flag (optional).
			 *    If empty, defaults to the current menu page.
			 *
			 * @return string The input `$url` with an options updated flag.
			 */
			public function options_updated($url = '')
			{
				$args = array(__NAMESPACE__.'_options_updated' => '1');

				return add_query_arg(urlencode_deep($args), $url ? (string)$url : $this->current_page_only());
			}

			/**
			 * Add pro preview action to a given URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to preview (optional).
			 *    If empty, defaults to the current menu page.
			 *
			 * @return string The input `$url` with a pro preview action.
			 */
			public function pro_preview($url = '')
			{
				$args = array(__NAMESPACE__.'_pro_preview' => '1');

				return add_query_arg(urlencode_deep($args), $url ? (string)$url : $this->current_page_only());
			}

			/**
			 * Adds search filter(s) to a given URL.
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
			 * @param string       $url The input URL to search (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL w/ search filters added to the `s` key.
			 */
			public function search_filter($filters, $url = '')
			{
				if(is_array($filters)) // Force string.
					$filters = implode(' ', $filters);
				$filters = trim((string)$filters);

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$query = (string)parse_url($url, PHP_URL_QUERY);
				wp_parse_str($query, $query_vars); // Parse query.

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
			 * Bulk action URL generator.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $plural Plural name/key.
			 * @param array  $ids An array of IDs to act upon.
			 * @param string $action The bulk action to perform.
			 *
			 * @param string $url The input URL to act on (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @return string URL leading to the bulk action necessary.
			 */
			public function bulk_action($plural, array $ids, $action, $url = '')
			{
				$plural = (string)$plural; // Force string.
				$action = (string)$action; // Force string.

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$args = array($plural => $ids, 'action' => $action, '_wpnonce' => wp_create_nonce('bulk-'.$plural));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Notice dimissal URL, for current URL w/ `_wpnonce`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key The notice key to dismiss.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @return string Notice dimissal URL, for current URL w/ `_wpnonce`.
			 */
			public function dismiss_notice($key, $nonce_action = __NAMESPACE__)
			{
				$args = array(__NAMESPACE__ => array('dismiss_notice' => array('key' => (string)$key)));

				return add_query_arg(urlencode_deep($args), $this->current_nonce($nonce_action));
			}

			/**
			 * Removes notice dismissal flag from a given URL.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $url The input URL to unflag (optional).
			 *    If empty, defaults to the current menu page.
			 *
			 * @return string The input `$url` with a notice dismissal flag removed.
			 */
			public function notice_dismissed($url = '')
			{
				return remove_query_arg(__NAMESPACE__, $url ? (string)$url : $this->current());
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

				return set_url_scheme($this->static[__FUNCTION__]['plugin_dir_url'].(string)$file, $scheme);
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
			 * @return boolean TRUE if it has a valid `_wpnonce` `$action` value.
			 */
			public function has_valid_nonce($nonce_action = __NAMESPACE__, $url = '')
			{
				if(($url = trim((string)$url)))
					wp_parse_str((string)@parse_url($url, PHP_URL_QUERY), $_r);
				else $_r = stripslashes_deep($_REQUEST); // Current `$_REQUEST`.

				if(empty($_r['_wpnonce']) || !wp_verify_nonce($_r['_wpnonce'], $nonce_action))
					return FALSE; // Unauthenticated; failure.

				return TRUE; // Valid `_wpnonce` value.
			}

			/**
			 * Creates a post shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|object $post_id A WP post ID; or a post object.
			 *
			 * @return string Post shortlink.
			 */
			public function post_short($post_id)
			{
				if(is_object($post_id) && !empty($post_id->ID))
					$post_id = $post_id->ID;

				$post_id = (integer)$post_id; // Force integer.
				$args    = array('p' => $post_id); // Post ID.

				return add_query_arg(urlencode_deep($args), home_url('/'));
			}

			/**
			 * Creates a post edit shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|object $post_id A WP post ID; or a post object.
			 *
			 * @return string Post edit shortlink.
			 */
			public function post_edit_short($post_id)
			{
				if(is_object($post_id) && !empty($post_id->ID))
					$post_id = $post_id->ID;

				$post_id = (integer)$post_id; // Force integer.
				$args    = array('post' => $post_id, 'action' => 'edit');

				return add_query_arg(urlencode_deep($args), admin_url('/post.php'));
			}

			/**
			 * Creates an edit comments shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|object $post_id A WP post ID; or a post object.
			 *
			 * @return string Post edit comments shortlink.
			 */
			public function post_edit_comments_short($post_id)
			{
				if(is_object($post_id) && !empty($post_id->ID))
					$post_id = $post_id->ID;

				$post_id = (integer)$post_id; // Force integer.
				$args    = array('p' => $post_id); // Post ID.

				return add_query_arg(urlencode_deep($args), admin_url('/edit-comments.php'));
			}

			/**
			 * Creates an edit subscribers shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|object $post_id A WP post ID; or a post object.
			 * @param string         $s Any additional search words/filters.
			 *
			 * @return string Post edit subscribers shortlink.
			 */
			public function post_edit_subscribers_short($post_id, $s = '')
			{
				if(is_object($post_id) && !empty($post_id->ID))
					$post_id = $post_id->ID;

				$post_id = (integer)$post_id; // Force integer.
				$s       = trim((string)$s); // Force trimmed string.
				$args    = array('s' => 'post_id:'.$post_id.($s ? ' '.$s : ''));

				return add_query_arg(urlencode_deep($args), $this->subscribers_menu_page_only());
			}

			/**
			 * Creates an edit subscriber shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $sub_id Subscriber ID.
			 *
			 * @return string Edit subscriber shortlink.
			 */
			public function edit_subscriber_short($sub_id)
			{
				$sub_id = (integer)$sub_id; // Force integer.
				$args   = array('subscriber' => $sub_id); // @TODO

				return add_query_arg(urlencode_deep($args), $this->subscribers_menu_page_only());
			}

			/**
			 * Creates a comment shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|object $comment_id A WP comment ID; or a post object.
			 *
			 * @return string Comment shortlink.
			 */
			public function comment_short($comment_id)
			{
				if(is_object($comment_id) && !empty($comment_id->commennt_ID))
					$comment_id = $comment_id->commennt_ID;

				$comment_id = (integer)$comment_id; // Force integer.
				$args       = array('c' => $comment_id); // Comment ID.

				return add_query_arg(urlencode_deep($args), home_url('/'));
			}

			/**
			 * Creates a comment edit shortlink w/o a DB query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|object $comment_id A WP comment ID; or a post object.
			 *
			 * @return string Comment edit shortlink.
			 */
			public function comment_edit_short($comment_id)
			{
				if(is_object($comment_id) && !empty($comment_id->commennt_ID))
					$comment_id = $comment_id->commennt_ID;

				$comment_id = (integer)$comment_id; // Force integer.
				$args       = array('action' => 'editcomment', 'c' => $comment_id);

				return add_query_arg(urlencode_deep($args), admin_url('/comment.php'));
			}
		}
	}
}