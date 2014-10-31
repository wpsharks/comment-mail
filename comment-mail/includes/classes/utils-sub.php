<?php
/**
 * Subscription Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_sub'))
	{
		/**
		 * Subscription Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_sub extends abs_base
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
			 * Subscription key to ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_key Input key to convert to an ID.
			 *
			 * @return integer The subscription ID matching the input `$sub_key`.
			 *    If the `$sub_key` is not found, this returns `0`.
			 */
			public function key_to_id($sub_key)
			{
				if(!($sub_key = trim((string)$sub_key)))
					return 0; // Not possible.

				if(!($sub = $this->get($sub_key)))
					return 0; // Not found.

				return $sub->ID;
			}

			/**
			 * Subscription key to email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_key Input key to convert to an email address.
			 *
			 * @return string The subscription email address matching the input `$sub_key`.
			 *    If the `$sub_key` is not found, this returns an empty string.
			 */
			public function key_to_email($sub_key)
			{
				if(!($sub_key = trim((string)$sub_key)))
					return ''; // Not possible.

				if(!($sub = $this->get($sub_key)))
					return ''; // Not found.

				return $sub->email;
			}

			/**
			 * Unique IDs only, from IDs/keys.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys An array of IDs/keys.
			 *
			 * @return array An array of unique IDs only.
			 */
			public function unique_ids_only(array $sub_ids_or_keys)
			{
				$unique_ids = $sub_keys = array();

				foreach($sub_ids_or_keys as $_sub_id_or_key)
				{
					if(is_numeric($_sub_id_or_key) && (integer)$_sub_id_or_key > 0)
						$unique_ids[] = (integer)$_sub_id_or_key;

					else if(is_string($_sub_id_or_key) && $_sub_id_or_key)
						$sub_keys[] = $_sub_id_or_key; // String key.
				}
				unset($_sub_id_or_key); // Housekeeping.

				foreach($sub_keys as $_sub_key)
					if(($_sub_id = $this->key_to_id($_sub_key)) > 0)
						$unique_ids[] = $_sub_id;
				unset($_sub_key, $_sub_id); // Housekeeping.

				if($unique_ids) // Unique IDs only.
					$unique_ids = array_unique($unique_ids);

				return $unique_ids;
			}

			/**
			 * Nullify the object cache for IDs/keys.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys An array of IDs/keys.
			 */
			public function nullify_cache(array $sub_ids_or_keys = array())
			{
				foreach($sub_ids_or_keys as $_sub_id_or_key)
					unset($this->cache['get'][$_sub_id_or_key]);
				unset($_sub_id_or_key); // Housekeeping.

				unset($this->cache['query_total'], $this->cache['last_x']);
			}

			/**
			 * Get subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 *
			 * @param boolean        $no_cache Defaults to a FALSE value.
			 *    TRUE if you want to avoid a potentially cached value.
			 *
			 * @return \stdClass|null Subscription object, if possible.
			 */
			public function get($sub_id_or_key, $no_cache = FALSE)
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(is_null($cache = &$this->cache_key(__FUNCTION__)))
					$cache = array(); // Initialize array.

				if(!$no_cache && $cache && array_key_exists($sub_id_or_key, $cache))
					return $cache[$sub_id_or_key]; // From built-in object cache.

				if($cache && count($cache) > 2000) // Too large?
				{
					$this->plugin->utils_array->shuffle_assoc($cache);
					$cache = array_slice($cache, 0, 2000, TRUE);
				}
				if(is_string($sub_id_or_key) && !is_numeric($sub_id_or_key))
				{
					$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

					       " WHERE `key` = '".esc_sql($sub_id_or_key)."' LIMIT 1";
				}
				else // Treat the value as an ID; i.e. the default behavior.
				{
					$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

					       " WHERE `ID` = '".esc_sql((integer)$sub_id_or_key)."' LIMIT 1";
				}
				if(($row = $this->plugin->utils_db->wp->get_row($sql)))
					return ($cache[$row->ID] = $cache[$row->key] = $row = $this->plugin->utils_db->typify_deep($row));

				return ($cache[$sub_id_or_key] = NULL);
			}

			/**
			 * Reconfirm subscription via email.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if subscription is reconfirmed successfully.
			 *    Or, FALSE if unable to reconfirm (e.g. already confirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 */
			public function reconfirm($sub_id_or_key, array $args = array())
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'deleted')
					return NULL; // Not possible.

				if($sub->status === 'subscribed')
					return FALSE; // Confirmed already.

				if(!isset($args['auto_confirm']))
					$args['auto_confirm'] = FALSE;

				if(!isset($args['process_confirmation']))
					$args['process_confirmation'] = TRUE;

				$updater = new sub_updater(array('ID' => $sub->ID, 'status' => 'unconfirmed'), $args);

				return $updater->did_update();
			}

			/**
			 * Bulk reconfirm subscriptions via email.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscription IDs/keys.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of suscribers reconfirmed successfully.
			 */
			public function bulk_reconfirm(array $sub_ids_or_keys, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($sub_ids_or_keys) as $_sub_id)
					if($this->reconfirm($_sub_id, $args))
						$counter++; // Update counter.
				unset($_sub_id); // Housekeeping.

				return $counter;
			}

			/**
			 * Confirm subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if subscription is confirmed successfully.
			 *    Or, FALSE if unable to confirm (e.g. already confirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 */
			public function confirm($sub_id_or_key, array $args = array())
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'deleted')
					return NULL; // Not possible.

				if($sub->status === 'subscribed')
					return FALSE; // Confirmed already.

				$updater = new sub_updater(array('ID' => $sub->ID, 'status' => 'subscribed'), $args);

				return $updater->did_update();
			}

			/**
			 * Bulk confirm subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscription IDs/keys.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of suscribers confirmed successfully.
			 */
			public function bulk_confirm(array $sub_ids_or_keys, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($sub_ids_or_keys) as $_sub_id)
					if($this->confirm($_sub_id, $args))
						$counter++; // Update counter.
				unset($_sub_id); // Housekeeping.

				return $counter;
			}

			/**
			 * Unconfirm subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if subscription is unconfirmed successfully.
			 *    Or, FALSE if unable to unconfirm (e.g. already unconfirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 */
			public function unconfirm($sub_id_or_key, array $args = array())
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'deleted')
					return NULL; // Not possible.

				if($sub->status === 'unconfirmed')
					return FALSE; // Unconfirmed already.

				$updater = new sub_updater(array('ID' => $sub->ID, 'status' => 'unconfirmed'), $args);

				return $updater->did_update();
			}

			/**
			 * Bulk unconfirm subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscription IDs/keys.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of suscribers unconfirmed successfully.
			 */
			public function bulk_unconfirm(array $sub_ids_or_keys, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($sub_ids_or_keys) as $_sub_id)
					if($this->unconfirm($_sub_id, $args))
						$counter++; // Update counter.
				unset($_sub_id); // Housekeeping.

				return $counter;
			}

			/**
			 * Suspend subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if subscription is suspended successfully.
			 *    Or, FALSE if unable to suspend (e.g. already suspended).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 */
			public function suspend($sub_id_or_key, array $args = array())
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'deleted')
					return NULL; // Not possible.

				if($sub->status === 'suspended')
					return FALSE; // Suspended already.

				$updater = new sub_updater(array('ID' => $sub->ID, 'status' => 'suspended'), $args);

				return $updater->did_update();
			}

			/**
			 * Bulk suspend subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscription IDs/keys.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of suscribers suspended successfully.
			 */
			public function bulk_suspend(array $sub_ids_or_keys, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($sub_ids_or_keys) as $_sub_id)
					if($this->suspend($_sub_id, $args))
						$counter++; // Update counter.
				unset($_sub_id); // Housekeeping.

				return $counter;
			}

			/**
			 * Trash subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if subscription is trashed successfully.
			 *    Or, FALSE if unable to trash (e.g. already trashed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 */
			public function trash($sub_id_or_key, array $args = array())
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'deleted')
					return NULL; // Not possible.

				if($sub->status === 'trashed')
					return FALSE; // Trashed already.

				$updater = new sub_updater(array('ID' => $sub->ID, 'status' => 'trashed'), $args);

				return $updater->did_update();
			}

			/**
			 * Bulk trash subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscription IDs/keys.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of suscribers trashed successfully.
			 */
			public function bulk_trash(array $sub_ids_or_keys, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($sub_ids_or_keys) as $_sub_id)
					if($this->trash($_sub_id, $args))
						$counter++; // Update counter.
				unset($_sub_id); // Housekeeping.

				return $counter;
			}

			/**
			 * Delete subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscription ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if subscription is deleted successfully.
			 *    Or, FALSE if unable to delete (e.g. already deleted).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 */
			public function delete($sub_id_or_key, array $args = array())
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return FALSE; // Deleted already.

				if($sub->status === 'deleted')
					return FALSE; // Deleted already.

				$deleter = new sub_deleter($sub->ID, $args);

				return $deleter->did_delete();
			}

			/**
			 * Bulk delete subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscription IDs/keys.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of suscribers deleted successfully.
			 */
			public function bulk_delete(array $sub_ids_or_keys, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($sub_ids_or_keys) as $_sub_id)
					if($this->delete($_sub_id, $args))
						$counter++; // Bump counter.
				unset($_sub_id); // Housekeeping.

				return $counter;
			}

			/**
			 * Query total subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|null $post_id Defaults to a `NULL` value.
			 *    i.e. defaults to any post ID. Pass this to limit the query.
			 *
			 * @param array        $args Any additional behavioral args.
			 *
			 * @return integer Total subscriptions for the given query.
			 *
			 * @throws \exception If a query failure occurs.
			 */
			public function query_total($post_id = NULL, array $args = array())
			{
				if(isset($post_id)) // Force integer?
					$post_id = (integer)$post_id;

				$default_args = array(
					'status'              => '',
					'comment_id'          => NULL,
					'auto_discount_trash' => TRUE,
					'group_by_email'      => FALSE,
					'no_cache'            => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$status              = trim((string)$args['status']);
				$comment_id          = $this->isset_or($args['comment_id'], NULL, 'integer');
				$auto_discount_trash = (boolean)$args['auto_discount_trash'];
				$group_by_email      = (boolean)$args['group_by_email'];
				$no_cache            = (boolean)$args['no_cache'];

				$cache_keys = compact('post_id', 'status', 'comment_id', 'auto_discount_trash', 'group_by_email');
				if(!is_null($total = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $total; // Already cached this.

				$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`".
				       " FROM `".esc_html($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Initialize where clause.

				       ($status // A specific status?
					       ? " AND `status` = '".esc_sql((string)$status)."'"
					       : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

				       (isset($post_id) ? " AND `post_id` = '".esc_sql((integer)$post_id)."'" : '').
				       (isset($comment_id) ? " AND `comment_id` = '".esc_sql((integer)$comment_id)."'" : '').

				       ($group_by_email ? " GROUP BY `email`" : '').

				       " LIMIT 1"; // Just one to check.

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Query failure.', $this->plugin->text_domain));

				return ($total = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()"));
			}

			/**
			 * Last X subscriptions w/ a given status.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer      $x The total number to return.
			 *
			 * @param integer|null $post_id Defaults to a `NULL` value.
			 *    i.e. defaults to any post ID. Pass this to limit the query.
			 *
			 * @param array        $args Any additional behavioral args.
			 *
			 * @return \stdClass[] Last X subscriptions w/ a given status.
			 */
			public function last_x($x = 0, $post_id = NULL, array $args = array())
			{
				if(($x = (integer)$x) <= 0)
					$x = 10; // Default value.

				if(isset($post_id)) // Force integer?
					$post_id = (integer)$post_id;

				$default_args = array(
					'offset'              => 0,
					'status'              => '',
					'sub_email'           => '',
					'comment_id'          => NULL,
					'auto_discount_trash' => TRUE,
					'group_by_email'      => FALSE,
					'no_cache'            => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$offset              = abs((integer)$args['offset']);
				$status              = trim((string)$args['status']);
				$sub_email           = trim((string)$args['sub_email']);
				$comment_id          = $this->isset_or($args['comment_id'], NULL, 'integer');
				$auto_discount_trash = (boolean)$args['auto_discount_trash'];
				$group_by_email      = (boolean)$args['group_by_email'];
				$no_cache            = (boolean)$args['no_cache'];

				$cache_keys = compact('x', 'post_id', 'offset', 'status', 'sub_email', 'comment_id', 'auto_discount_trash', 'group_by_email');
				if(!is_null($last_x = &$this->cache_key(__FUNCTION__, $cache_keys)) && !$no_cache)
					return $last_x; // Already cached this.

				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Initialize where clause.

				       ($status // A specific status in this case?
					       ? " AND `status` = '".esc_sql((string)$status)."'"
					       : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

				       (isset($post_id) ? " AND `post_id` = '".esc_sql($post_id)."'" : '').
				       ($sub_email ? " AND `email` = '".esc_sql($sub_email)."'" : '').
				       (isset($comment_id) ? " AND `comment_id` = '".esc_sql($comment_id)."'" : '').

				       ($group_by_email ? " GROUP BY `email`" : '').

				       " ORDER BY `insertion_time` DESC".

				       " LIMIT ".esc_sql($offset).", ".esc_sql($x);

				if(($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					return ($last_x = $results = $this->plugin->utils_db->typify_deep($results));

				return ($last_x = array()); // Default value.
			}

			/**
			 * Check existing email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_email Email address to check.
			 *
			 * @return boolean TRUE if email exists already.
			 */
			public function email_exists($sub_email)
			{
				if(!($sub_email = trim((string)$sub_email)))
					return FALSE; // Not possible.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `email` = '".esc_sql($sub_email)."'".

				       " LIMIT 1"; // One to check.

				return (boolean)$this->plugin->utils_db->wp->get_var($sql);
			}

			/**
			 * Last IP associated w/ email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_email Email address to check.
			 *
			 * @return string Last IP associated w/ email address; else empty string.
			 */
			public function email_last_ip($sub_email)
			{
				if(!($sub_email = trim((string)$sub_email)))
					return ''; // Not possible.

				$sql = "SELECT `last_ip` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `email` = '".esc_sql($sub_email)."'".
				       " AND `last_ip` != ''". // Has an IP.

				       " ORDER BY `last_update_time` DESC".

				       " LIMIT 1"; // One to check.

				return trim((string)$this->plugin->utils_db->wp->get_var($sql));
			}

			/**
			 * Is an email address blacklisted?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_email Email address to check.
			 *
			 * @return boolean `TRUE` if the email is blacklisted.
			 */
			public function email_is_blacklisted($sub_email)
			{
				if(!($sub_email = trim((string)$sub_email)))
					return FALSE; // Not possible.

				if(!($blacklist = trim($this->plugin->options['email_blacklist_patterns'])))
					return FALSE; // There is no blacklist.

				if(is_null($blacklist_patterns = &$this->cache_key(__FUNCTION__, 'blacklist_patterns')))
					$blacklist_patterns = '(?:'.implode('|', array_map(function ($pattern)
						{
							return preg_replace('/\\\\\*/', '.*?', preg_quote($pattern, '/')); #

						}, preg_split('/['."\r\n".']+/', $blacklist, NULL, PREG_SPLIT_NO_EMPTY))).')';

				return (boolean)preg_match('/^'.$blacklist_patterns.'$/i', $sub_email);
			}

			/**
			 * Set current sub's email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sub_email Subscriber's current email address.
			 *
			 * @warning It's VERY IMPORTANT that we only call upon this function to set the email address
			 *    during a subscriber action; i.e. in real-time. This cookie is used as a trusted source by {@link current_email()}.
			 *    In short, do NOT set the current email address unless an action is being performed against a key.
			 *
			 * @throws \exception If attempting to set the current email when it's not a sub. action being processed in real time.
			 *    Note that it's still possible to set the email address to an empty string; from anywhere at any time.
			 */
			public function set_current_email($sub_email)
			{
				$sub_email = trim((string)$sub_email); // Force clean string.

				if($sub_email) // Check security if we are attempting to set a non-empty cookie value.
					if(is_admin() || (!isset($_REQUEST[__NAMESPACE__]['confirm']) && !isset($_REQUEST[__NAMESPACE__]['unsubscribe']) && !isset($_REQUEST[__NAMESPACE__]['manage'])))
						throw new \exception(__('Trying to set current email w/o a sub. action.', $this->plugin->text_domain));

				$this->plugin->utils_enc->set_cookie(__NAMESPACE__.'_sub_email', $sub_email);
			}

			/**
			 * Current sub's email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param boolean $search_untrusted_sources Also search untrusted sources?
			 *    This defaults to a `FALSE` value. By default, we only return a "confirmed" email address.
			 *    i.e. an email address from a source that can be trusted to absolutely identify the current user.
			 *
			 * @return string Current subscriber's email address.
			 */
			public function current_email($search_untrusted_sources = FALSE)
			{
				if(($user = wp_get_current_user()) && $user->ID && $user->user_email)
					return (string)$user->user_email; // Force string.

				if(($sub_email = $this->plugin->utils_enc->get_cookie(__NAMESPACE__.'_sub_email')))
					return (string)$sub_email; // Force string.

				if($search_untrusted_sources) // Try current commenter?
					if(($commenter = wp_get_current_commenter()) && !empty($commenter['comment_author_email']))
						return (string)$commenter['comment_author_email']; // Force string.

				return ''; // Not possible.
			}
		}
	}
}