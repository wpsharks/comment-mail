<?php
/**
 * URL Utilities
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class utils_url extends abs_base
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
			 * Current scheme; lowercase.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current scheme; lowercase.
			 */
			public function current_scheme()
			{
				if(!is_null($scheme = &$this->static_key(__FUNCTION__)))
					return $scheme; // Cached this already.

				return ($scheme = is_ssl() ? 'https' : 'http');
			}

			/**
			 * Current front scheme; lowercase.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current front scheme; lowercase.
			 *
			 * @note This will return `https://` only if we are NOT in the admin area.
			 *    Also, see {@link \home_url()} for some other considerations.
			 */
			public function current_front_scheme()
			{
				if(!is_null($scheme = &$this->static_key(__FUNCTION__)))
					return $scheme; // Cached this already.

				return ($scheme = (string)parse_url(home_url(), PHP_URL_SCHEME));
			}

			/**
			 * Sets URL scheme.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link \set_url_scheme()} in WordPress for further details.
			 *
			 * @return string URL w/ the proper scheme.
			 *
			 * @note Regarding the special `front` scheme:
			 *    {@link home_url()} establishes the standards we use.
			 *
			 *    It is NOT necessary to use `front` in most scenarios,
			 *       but there are some edge cases where it has a purpose.
			 *
			 *    e.g. building a URL that leads {@to()} a plugin file (while {@link is_admin()});
			 *       but where the URL is intended for display on the front-end of the site.
			 *
			 * @uses set_url_scheme()
			 * @uses current_front_scheme()
			 */
			public function set_scheme($url = '', $scheme = NULL)
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				if($scheme === 'front') // Front-side?
					$scheme = $this->current_front_scheme();

				return set_url_scheme($url, $scheme);
			}

			/**
			 * Current host name; lowercase.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param boolean $no_port No port number? Defaults to `FALSE`.
			 *
			 * @note Some hosts include a port number in `$_SERVER['HTTP_HOST']`.
			 *    That SHOULD be left intact for URL generation in almost every scenario.
			 *    However, in a few other edge cases it may be desirable to exclude the port number.
			 *    e.g. if the purpose of obtaining the host is to use it for email generation, or in a slug, etc.
			 *
			 * @return string Current host name; lowercase.
			 */
			public function current_host($no_port = FALSE)
			{
				if(!is_null($host = &$this->static_key(__FUNCTION__, $no_port)))
					return $host; // Cached this already.

				$host = strtolower((string)$_SERVER['HTTP_HOST']);

				if($no_port) // Remove possible port number?
					$host = preg_replace('/\:[0-9]+$/', '', $host);

				return $host; // Current host (cached).
			}

			/**
			 * Current `host[/path]`; w/ multisite compat.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current `host/path`; w/ multisite compat.
			 *
			 * @note We don't cache this, since a blog can get changed at runtime.
			 */
			public function current_host_path()
			{
				if(is_multisite()) // Multisite network?
				{
					global $current_blog; // Current MS blog.

					$host = rtrim($current_blog->domain, '/');
					$path = trim($current_blog->path, '/');

					return strtolower(trim($host.'/'.$path, '/'));
				}
				return strtolower($this->plugin->utils_url->current_host(TRUE));
			}

			/**
			 * Current base/root host name; w/ multisite compat.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current base/root host name; w/ multisite compat.
			 *
			 * @note We don't cache this, since a blog can get changed at runtime.
			 */
			public function current_host_base()
			{
				if(is_multisite()) // Multisite network?
				{
					global $current_blog; // Current MS blog.

					$host = strtolower(rtrim($current_blog->domain, '/'));
					if(defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL)
						return $host; // Intentional sub-domain.
				}
				else $host = $this->current_host(); // Standard WP installs.

				if(substr_count($host, '.') > 1) // Reduce to base/root host name.
				{
					$_parts = explode('.', $host); // e.g. `www.example.com` becomes `example.com`.
					$host   = $_parts[count($_parts) - 2].'.'.$_parts[count($_parts) - 1];
					unset($_parts); // Housekeeping.
				}
				return strtolower($host); // Base/root host name.
			}

			/**
			 * Current URI; with a leading `/`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current URI; with a leading `/`.
			 */
			public function current_uri()
			{
				if(!is_null($uri = &$this->static_key(__FUNCTION__)))
					return $uri; // Cached this already.

				return ($uri = '/'.ltrim((string)$_SERVER['REQUEST_URI'], '/'));
			}

			/**
			 * Current URI/path; with a leading `/`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current URI/path; with a leading `/`.
			 */
			public function current_path()
			{
				if(!is_null($path = &$this->static_key(__FUNCTION__)))
					return $path; // Cached this already.

				return ($path = '/'.ltrim((string)parse_url($this->current_uri(), PHP_URL_PATH), '/'));
			}

			/**
			 * Current URL; i.e. scheme.host.URI put together.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Current URL; i.e. scheme.host.URI put together.
			 */
			public function current($scheme = NULL)
			{
				if(!is_null($url = &$this->static_key(__FUNCTION__, $scheme)))
					return $url; // Cached this already.

				$url = '//'.$this->current_host().$this->current_uri();

				return ($url = $this->set_scheme($url, $scheme));
			}

			/**
			 * URL without a query string.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL without a query string.
			 */
			public function no_query($url = '', $scheme = NULL)
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$url = strpos($url, '?') !== FALSE ? (string)strstr($url, '?', TRUE) : $url;

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * URL with `_wpnonce`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL with `_wpnonce`.
			 */
			public function nonce($nonce_action = __NAMESPACE__, $url = '', $scheme = 'admin')
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$args = array('_wpnonce' => wp_create_nonce($nonce_action));
				$url  = add_query_arg(urlencode_deep($args), $url);

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * URL with only a `page` var (if applicable).
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $page A specific page value (optional).
			 *    If empty, we use `page` from the URL; else current `page`.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL with only a `page` var (if applicable).
			 */
			public function page_only($page = '', $url = '', $scheme = 'admin')
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
				$url  = add_query_arg(urlencode_deep($args), $url);

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * URL with only a `page` var (if applicable) and `_wpnonce`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $page A specific page value (optional).
			 *    If empty, we use `page` from the URL; else current `page`.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL with only a `page` var (if applicable) and `_wpnonce`.
			 */
			public function page_nonce_only($page = '', $nonce_action = __NAMESPACE__, $url = '', $scheme = 'admin')
			{
				$url = $this->page_only($page, $url);

				return $this->nonce($nonce_action, $url, $scheme);
			}

			/**
			 * Main menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Main menu page URL.
			 */
			public function main_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__, $url, $scheme);
			}

			/**
			 * Main menu page URL; w/ `_wpnonce`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Main menu page URL; w/ `_wpnonce`.
			 */
			public function main_menu_page_nonce_only($nonce_action = __NAMESPACE__, $scheme = 'admin')
			{
				$url = $this->main_menu_page_only();

				return $this->nonce($nonce_action, $url, $scheme);
			}

			/**
			 * Subscriptions menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Subscriptions menu page URL.
			 */
			public function subs_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_subs', $url, $scheme);
			}

			/**
			 * Sub. event log menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Sub. event log menu page URL.
			 */
			public function sub_event_log_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_sub_event_log', $url, $scheme);
			}

			/**
			 * Queue menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Queue menu page URL.
			 */
			public function queue_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_queue', $url, $scheme);
			}

			/**
			 * Queue event log menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Queue event log menu page URL.
			 */
			public function queue_event_log_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_queue_event_log', $url, $scheme);
			}

			/**
			 * Statistics menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Statistics menu page URL.
			 */
			public function stats_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_stats', $url, $scheme);
			}

			/**
			 * Import/export menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Import/export menu page URL.
			 */
			public function import_export_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_import_export', $url, $scheme);
			}

			/**
			 * Email templates menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Email templates menu page URL.
			 */
			public function email_templates_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_email_templates', $url, $scheme);
			}

			/**
			 * Site templates menu page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Site templates menu page URL.
			 */
			public function site_templates_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/admin.php');

				return $this->page_only(__NAMESPACE__.'_site_templates', $url, $scheme);
			}

			/**
			 * Options updated URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Options updated URL.
			 */
			public function options_updated($scheme = 'admin')
			{
				return $this->page_only('', '', $scheme);
			}

			/**
			 * Restore default options URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Restore default options URL.
			 */
			public function restore_default_options($scheme = 'admin')
			{
				$url  = $this->main_menu_page_nonce_only(__NAMESPACE__, $scheme);
				$args = array(__NAMESPACE__ => array('restore_default_options' => '1'));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Options restored URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Options restored URL.
			 */
			public function default_options_restored($scheme = 'admin')
			{
				return $this->main_menu_page_only($scheme);
			}

			/**
			 * Pro preview URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the main menu page.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Pro preview URL.
			 */
			public function pro_preview($url = '', $scheme = 'admin')
			{
				if(!($url = trim((string)$url)))
					$url = $this->main_menu_page_only();

				$args = array(__NAMESPACE__.'_pro_preview' => '1');
				$url  = add_query_arg(urlencode_deep($args), $url);

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * Adds search filter(s) to the `s` key for tables.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|array $filters A string or an array of filters.
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
			 * @param string|null  $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ search filters added to the `s` key.
			 */
			public function table_search_filter($filters, $url = '', $scheme = 'admin')
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
				$filter_regex = '/\b(?P<type>\w+)(?P<group>\:+)(?P<values>[^\s]+)?/i';

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

				$s   = trim(preg_replace('/\s+/', ' ', $s));
				$url = add_query_arg('s', $s ? urlencode($s) : FALSE, $url);
				// Note: `FALSE` tells `add_query_arg()` to remove `s`.

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * Bulk action URL generator for tables.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $plural Plural table name/key.
			 * @param array       $ids An array of IDs to act upon.
			 * @param string      $action The bulk action to perform.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL leading to the bulk action necessary.
			 */
			public function table_bulk_action($plural, array $ids, $action, $url = '', $scheme = 'admin')
			{
				$plural = trim((string)$plural);
				$action = trim((string)$action);

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$args = array($plural => $ids, 'action' => $action);
				$url  = add_query_arg(urlencode_deep($args), $url);

				return $this->nonce('bulk-'.$plural, $url, $scheme);
			}

			/**
			 * URL w/ page & table nav vars only — from a given URL and/or `$_REQUEST` vars.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array       $also_keep Any additional names/keys to keep.
			 *    Built-in names/keys to keep already includes the following:
			 *       `page`, `orderby`, `order`, and `s` for searches.
			 *
			 *    If `_wponce` is passed in this array, we not only keep that variable,
			 *    but we also generate a new `_wpnonce` key too. In short, `_wpnonce` is
			 *    forced into the URL w/ a fresh value when keeping `_wp_nonce`.
			 *    ~ See also: {@link page_nonce_table_nav_vars_only()}.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    Defaults to current URL. Existing vars will be taken from this URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    ~ See also: {@link page_nonce_table_nav_vars_only()}.
			 *
			 * @return string URL w/ page & table nav vars only — from given URL and/or `$_REQUEST` vars.
			 *
			 * @note Vars found in the given URL are given priority over any found in the current `$_REQUEST` vars.
			 *    i.e. If the given (and/or default) URL contains a particular table nav var, it's given precedence.
			 *    Otherwise, if the URL does not have a particular table nav var, we look at `$_REQUEST` vars.
			 */
			public function page_table_nav_vars_only(array $also_keep = array(), $url = '', $scheme = 'admin', $nonce_action = __NAMESPACE__)
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$query = (string)parse_url($url, PHP_URL_QUERY);
				wp_parse_str($query, $query_vars);
				$url = $this->no_query($url);

				$_r = $this->plugin->utils_string->trim_strip_deep($_REQUEST);

				$also_keep = array_map('strval', $also_keep);
				$keepers   = array('page', 'orderby', 'order', 's');
				$keepers   = array_unique(array_merge($keepers, $also_keep));

				foreach($keepers as $_keeper) // Add keepers back onto the clean URL.
				{
					if(isset($query_vars[$_keeper])) // In query vars?
						$url = add_query_arg(urlencode($_keeper), urlencode($query_vars[$_keeper]), $url);

					else if(isset($_r[$_keeper])) // In the current request array?
						$url = add_query_arg(urlencode($_keeper), urlencode($_r[$_keeper]), $url);
				}
				unset($_keeper); // Housekeeping.

				if(in_array('_wpnonce', $also_keep, TRUE)) // Generate a fresh value.
					$url = add_query_arg('_wpnonce', urlencode(wp_create_nonce($nonce_action)), $url);

				return $this->set_scheme($url, $scheme); // With page & table nav vars only.
			}

			/**
			 * URL w/ page, nonce & table nav vars only — from a given URL and/or `$_REQUEST` vars.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array       $also_keep See {@link page_table_nav_vars_only()}.
			 * @param string      $url See {@link page_table_nav_vars_only()}.
			 * @param string      $nonce_action See {@link page_table_nav_vars_only()}.
			 * @param string|null $scheme See {@link page_table_nav_vars_only()}.
			 *
			 * @return string See {@link page_table_nav_vars_only()}.
			 */
			public function page_nonce_table_nav_vars_only(array $also_keep = array(), $url = '', $scheme = 'admin', $nonce_action = __NAMESPACE__)
			{
				return $this->page_table_nav_vars_only(array_merge($also_keep, array('_wpnonce')), $url, $scheme, $nonce_action);
			}

			/**
			 * Notice dimissal URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $notice_key The notice key to dismiss.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Notice dimissal URL.
			 */
			public function dismiss_notice($notice_key, $scheme = 'admin')
			{
				$notice_key = trim((string)$notice_key);

				$url  = $this->nonce(__NAMESPACE__, '', $scheme);
				$args = array(__NAMESPACE__ => array('dismiss_notice' => compact('notice_key')));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Notice dimissed URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Notice dimissed URL.
			 */
			public function notice_dismissed($scheme = 'admin')
			{
				$url = $this->current($scheme);

				return remove_query_arg(__NAMESPACE__, $url);
			}

			/**
			 * Product page URL; normally at WebSharks™.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Product page URL; normally at WebSharks™.
			 */
			public function product_page($scheme = NULL)
			{
				if(!empty($this->plugin->product_url))
					$url = $this->plugin->product_url; // Provided by plugin class?
				else $url = 'http://www.websharks-inc.com/product/'.urlencode($this->plugin->slug).'/';

				return isset($scheme) ? $this->set_scheme($url, $scheme) : $url;
			}

			/**
			 * Subscribe page URL; normally at WebSharks™.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Subscribe page URL; normally at WebSharks™.
			 */
			public function subscribe_page($scheme = NULL)
			{
				if(!empty($this->plugin->subscribe_url))
					$url = $this->plugin->subscribe_url; // Provided by plugin class?
				else $url = 'http://www.websharks-inc.com/r/'.urlencode($this->plugin->slug).'-subscribe/';

				return isset($scheme) ? $this->set_scheme($url, $scheme) : $url;
			}

			/**
			 * URL to a plugin file.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $file Optional file path; relative to plugin directory.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL to plugin directory; or to the specified `$file` if applicable.
			 */
			public function to($file = '', $scheme = NULL)
			{
				if(is_null($plugin_dir_url = &$this->static_key(__FUNCTION__, 'plugin_dir_url')))
					$plugin_dir_url = rtrim(plugin_dir_url($this->plugin->file), '/');

				return $this->set_scheme($plugin_dir_url.(string)$file, $scheme);
			}

			/**
			 * Checks for a valid `_wpnonce` value.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 *
			 * @param integer     $post_id A WP post ID.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Post shortlink.
			 */
			public function post_short($post_id, $scheme = NULL)
			{
				$post_id = (integer)$post_id;

				$url  = home_url('/', $scheme);
				$args = array('p' => $post_id);

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates a post edit shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $post_id A WP post ID.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Post edit shortlink.
			 */
			public function post_edit_short($post_id, $scheme = 'admin')
			{
				$post_id = (integer)$post_id;

				$url  = admin_url('/post.php', $scheme);
				$args = array('post' => $post_id, 'action' => 'edit');

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates an edit comments shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $post_id A WP post ID.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Post edit comments shortlink.
			 */
			public function post_edit_comments_short($post_id, $scheme = 'admin')
			{
				$post_id = (integer)$post_id;

				$url  = admin_url('/edit-comments.php', $scheme);
				$args = array('p' => $post_id);

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates an edit subscriptions shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $post_id A WP post ID.
			 * @param string      $s Any additional search words/filters.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Post edit subscriptions shortlink.
			 */
			public function post_edit_subs_short($post_id, $s = '', $scheme = 'admin')
			{
				$post_id = (integer)$post_id;
				$s       = trim((string)$s);

				$url  = $this->subs_menu_page_only($scheme);
				$args = array('s' => 'post_id:'.$post_id.($s ? ' '.$s : ''));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Search subscriptions shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $s Search words/filters.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Search subscriptions shortlink.
			 */
			public function search_subs_short($s = '', $scheme = 'admin')
			{
				$s = trim((string)$s); // Search words/filters.

				$url  = $this->subs_menu_page_only($scheme);
				$args = array('s' => $s); // Query args.

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates a new subscription shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string New subscription shortlink.
			 */
			public function new_sub_short($scheme = 'admin')
			{
				$url  = $this->subs_menu_page_only();
				$url  = $this->page_table_nav_vars_only(array(), $url, $scheme);
				$args = array('action' => 'new');

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates an edit subscription shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $sub_id Subscription ID.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Edit subscription shortlink.
			 */
			public function edit_sub_short($sub_id, $scheme = 'admin')
			{
				$sub_id = (integer)$sub_id;

				$url  = $this->subs_menu_page_only();
				$url  = $this->page_table_nav_vars_only(array(), $url, $scheme);
				$args = array('action' => 'edit', 'subscription' => $sub_id);

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates an edit user shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $user_id A WP User ID.
			 *
			 * @param string|null Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Edit user shortlink.
			 */
			public function edit_user_short($user_id, $scheme = 'admin')
			{
				$user_id = (integer)$user_id;

				$url  = admin_url('/user-edit.php', $scheme);
				$args = array('user_id' => $user_id);

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates a comment shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $comment_id A WP comment ID.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Comment shortlink.
			 */
			public function comment_short($comment_id, $scheme = NULL)
			{
				$comment_id = (integer)$comment_id;

				$url  = home_url('/', $scheme);
				$args = array('c' => $comment_id);

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Creates a comment edit shortlink w/o a DB query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer     $comment_id A WP comment ID.
			 *
			 * @param string|null Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Comment edit shortlink.
			 */
			public function comment_edit_short($comment_id, $scheme = 'admin')
			{
				$comment_id = (integer)$comment_id;

				$url  = admin_url('/comment.php', $scheme);
				$args = array('action' => 'editcomment', 'c' => $comment_id);

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Confirmation URL for a specific sub. key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $sub_key Unique subscription key.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sub_confirm_url($sub_key, $scheme = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$sub_key = !isset($sub_key[0]) ? '0' : $sub_key;

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('confirm' => $sub_key));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Unsubscribe URL for a specific sub. key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $sub_key Unique subscription key.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sub_unsubscribe_url($sub_key, $scheme = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$sub_key = !isset($sub_key[0]) ? '0' : $sub_key;

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('unsubscribe' => $sub_key));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Unsubscribe ALL URL for a specific sub. email.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $sub_email Subscriber's email address.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sub_unsubscribe_all_url($sub_email, $scheme = NULL)
			{
				$sub_email = trim((string)$sub_email);
				$sub_email = $this->plugin->utils_enc->encrypt($sub_email);

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('unsubscribe_all' => $sub_email));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Manage URL for a specific sub. key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $sub_key Unique subscription key.
			 *    If empty, the subscription management system will use
			 *    the current user's email address; if available/possible.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sub_manage_url($sub_key = '', $scheme = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$sub_key = !isset($sub_key[0]) ? '0' : $sub_key;

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('manage' => $sub_key));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Manage URL for a specific sub. key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string        $sub_key Unique subscription key.
			 *    If empty, the subscription management system will use
			 *    the current user's email address; if available/possible.
			 *
			 * @param string|null   $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @param boolean|array $include_nav_vars Defaults to a `NULL` value.
			 *    Use a non-empty array to add new nav vars; `TRUE` to simply include existing nav vars.
			 *    See also {@link sub_manage_summary_nav_vars()} for additional details.
			 *
			 * @param string        $return_type Type of return value; i.e. `(string)$url` or `(array)$args`?
			 *    Set this to a value of `array` to indicate that you want `(array)$args`.
			 *    ~ Defaults to a value of `string`, indicating `(string)$url`.
			 *
			 * @return string URL w/ all args + nav vars. Or, `(array)$args`; i.e. array of all args + nav vars.
			 *    In short, return value is dependent upon the `$return_type` parameter.
			 */
			public function sub_manage_summary_url($sub_key = '', $scheme = NULL, $include_nav_vars = NULL, $return_type = 'string')
			{
				$sub_key = trim((string)$sub_key);
				$sub_key = !isset($sub_key[0]) ? '0' : $sub_key;

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('manage' => array('summary' => $sub_key)));

				if($include_nav_vars && ($nav_vars = $this->sub_manage_summary_nav_vars($include_nav_vars)))
					$args[__NAMESPACE__]['manage']['summary_nav'] = $nav_vars;

				return $return_type === 'array' ? compact('url', 'args') : add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Summary nav vars in a given URL and/or current `$_REQUEST` vars.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param boolean|array $include_nav_vars Defaults to a `TRUE` value.
			 *    Use a non-empty array to add new nav vars; `TRUE` to simply include existing nav vars.
			 *    ~ Any other value results in no nav vars; i.e. this function returns an empty array.
			 *
			 *    • Regarding `(array)$include_nav_vars`; i.e. adding new nav vars:
			 *
			 *       Any new nav vars are ADDED to those which may already exist in the URL and/or `$_REQUEST` vars.
			 *       If you want to override any that may already exist in these sources, define key `0` in your array.
			 *       i.e. `if(array_key_exists(0, $include_nav_vars))`; yours will override any that exist already.
			 *       ~ Noting that the `0` key is excluded automatically after interpretation for this purpose.
			 *
			 * @param string        $url The input URL to work from (optional).
			 *    Defaults to current URL. Existing nav vars will be taken from this URL.
			 *
			 * @return array An array of any summary nav vars; when/if applicable.
			 *
			 * @note Nav vars found in the given URL are given priority over any found in the current `$_REQUEST` vars.
			 *    i.e. If the given (and/or default) URL contains a particular summary nav var, it's given precedence.
			 *    Otherwise, if the URL does not have a particular summary nav var, we look at `$_REQUEST` vars.
			 */
			public function sub_manage_summary_nav_vars($include_nav_vars = TRUE, $url = '')
			{
				if($include_nav_vars !== TRUE // Exclude nav vars?
				   && (!is_array($include_nav_vars) || empty($include_nav_vars))
				) return array(); // Must be `TRUE`, or a non-empty array.

				if(!is_array($nav_vars = $include_nav_vars))
					$nav_vars = array(); // Force array.

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$existing_nav_vars = array(); // Initialize.
				$new_nav_vars_only = FALSE; // Default behavior.

				if(array_key_exists(0, $nav_vars)) // Only use new nav vars?
				{
					$new_nav_vars_only = TRUE; // Flag as `TRUE`; only use new nav vars.
					unset($nav_vars[0]); // Unset automatically after interpretation.
				}
				if(!$new_nav_vars_only) // Only if we NEED existing nav vars.
				{
					$query = (string)parse_url($url, PHP_URL_QUERY);
					wp_parse_str($query, $query_vars); // By reference.

					if(!empty($query_vars[__NAMESPACE__]['manage']['summary_nav']))
						$query_nav_vars = (array)$query_vars[__NAMESPACE__]['manage']['summary_nav'];

					if($_REQUEST && !empty($_REQUEST[__NAMESPACE__]['manage']['summary_nav']))
						$_r_nav_vars = $this->plugin->utils_string->trim_strip_deep((array)$_REQUEST[__NAMESPACE__]['manage']['summary_nav']);

					foreach(array_keys(sub_manage_summary::$default_nav_vars) as $_nav_var_key)
					{
						if(isset($query_nav_vars[$_nav_var_key]))
							$existing_nav_vars[$_nav_var_key] = (string)$query_nav_vars[$_nav_var_key];

						else if(isset($_r_nav_vars[$_nav_var_key]))
							$existing_nav_vars[$_nav_var_key] = (string)$_r_nav_vars[$_nav_var_key];
					}
					unset($_nav_var_key); // Housekeeping.
				}
				return $new_nav_vars_only ? $nav_vars : array_merge($existing_nav_vars, $nav_vars);
			}

			/**
			 * Manage URL for adding a new subscription.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null   $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @param boolean|array $include_nav_vars Defaults to a `NULL` value.
			 *    Use a non-empty array to add new nav vars; `TRUE` to simply include existing nav vars.
			 *    See also {@link sub_manage_summary_nav_vars()} for additional details.
			 *
			 * @param array         $prefill Any prefill variables; e.g. `post_id`, `comment_id`.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sub_manage_sub_new_url($scheme = NULL, $include_nav_vars = NULL, array $prefill = array())
			{
				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('manage' => array('sub_new' => '0')));

				if($include_nav_vars && ($nav_vars = $this->sub_manage_summary_nav_vars($include_nav_vars)))
					$args[__NAMESPACE__]['manage']['summary_nav'] = $nav_vars;

				foreach($prefill as $_key => $_value)
					$args[__NAMESPACE__]['manage']['sub_form'][$_key] = $_value;
				unset($_key, $_value); // Housekeeping.

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Manage URL to edit a specific sub. key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string        $sub_key Unique subscription key.
			 *
			 * @param string|null   $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @param boolean|array $include_nav_vars Defaults to a `NULL` value.
			 *    Use a non-empty array to add new nav vars; `TRUE` to simply include existing nav vars.
			 *    See also {@link sub_manage_summary_nav_vars()} for additional details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sub_manage_sub_edit_url($sub_key = '', $scheme = NULL, $include_nav_vars = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$sub_key = !isset($sub_key[0]) ? '0' : $sub_key;

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('manage' => array('sub_edit' => $sub_key)));

				if($include_nav_vars && ($nav_vars = $this->sub_manage_summary_nav_vars($include_nav_vars)))
					$args[__NAMESPACE__]['manage']['summary_nav'] = $nav_vars;

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Manage URL to delete a specific sub. key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string        $sub_key Unique subscription key.
			 *
			 * @param string|null   $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @param boolean|array $include_nav_vars Defaults to a `NULL` value.
			 *    Use a non-empty array to add new nav vars; `TRUE` to simply include existing nav vars.
			 *    See also {@link sub_manage_summary_nav_vars()} for additional details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 *
			 * @note It's IMPORTANT to set `summary=0` here, since the key in this URL
			 *    will ultimately be deleted; i.e. it will not be valid once the action is complete.
			 */
			public function sub_manage_sub_delete_url($sub_key = '', $scheme = NULL, $include_nav_vars = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$sub_key = !isset($sub_key[0]) ? '0' : $sub_key;

				$url  = home_url('/', $scheme);
				$args = array(__NAMESPACE__ => array('manage' => array('sub_delete' => $sub_key, 'summary' => '0')));

				if($include_nav_vars && ($nav_vars = $this->sub_manage_summary_nav_vars($include_nav_vars)))
					$args[__NAMESPACE__]['manage']['summary_nav'] = $nav_vars;

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Webhook URL for replies via email; through Mandrill.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function rve_mandrill_webhook_url($scheme = NULL)
			{
				$url  = home_url('/', $scheme);
				$key  = rve_mandrill::key(); // Webhook key.
				$args = array(__NAMESPACE__ => array('rve_mandrill' => $key));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * URL for an SSO action handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $service SSO service integration slug.
			 *
			 * @param string      $action A particular action; defaults to `authorize`.
			 *    To request authorization, set this to `authorize`.
			 *    To receive a callback, set this to `callback`.
			 *
			 * @param null|string $redirect_to The underlying URL that a user is trying to access.
			 *    If empty (and not === `NULL`), this defaults to the current URL; or the current `redirect_to`.
			 *    If `NULL`, the `redirect_to` arg is excluded completely.
			 *
			 * @note If `$action` is `callback`, the `redirect_to` is forced to a `NULL` value.
			 *    Callbacks should remain consistent; i.e. not be changed from one redirection URL to another.
			 *    Before an oAuth authorization redirection occurs, a `redirect_to` should be stored in a session;
			 *    i.e. NOT passed through the oAuth callback URL. This is why it is forced to a `NULL` value here.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function sso_action_url($service, $action = '', $redirect_to = '', $scheme = NULL)
			{
				$service = trim((string)$service);

				if(!($action = trim((string)$action)))
					$action = 'authorize';

				if($action === 'callback') $redirect_to = NULL;

				if(isset($redirect_to) && !($redirect_to = trim((string)$redirect_to)))
				{
					if(!empty($_REQUEST['redirect_to']))
						$redirect_to = trim(stripslashes((string)$_REQUEST['redirect_to']));
					else $redirect_to = $this->current();

					if(strpos($redirect_to, 'wp-login.php') !== FALSE)
						$redirect_to = home_url('/');
				}
				$url = home_url('/', $scheme);
				if(!isset($redirect_to)) unset($redirect_to); // Prevent `compact()` inclusion.
				$args = array(__NAMESPACE__ => array('sso' => compact('service', 'action', 'redirect_to')));

				return add_query_arg(urlencode_deep($args), $url);
			}
		}
	}
}