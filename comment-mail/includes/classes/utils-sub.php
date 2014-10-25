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
			 * @param string $key Input key to convert to an ID.
			 *
			 * @return integer The subscription ID matching the input `$key`.
			 *    If the `$key` is not found, this returns `0`.
			 */
			public function key_to_id($key)
			{
				if(!($key = trim((string)$key)))
					return 0; // Not possible.

				if(!($sub = $this->get($key)))
					return 0; // Not found.

				return $sub->ID;
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

				if(!isset($this->cache[__FUNCTION__]))
					$this->cache[__FUNCTION__] = array();
				$cache = &$this->cache[__FUNCTION__]; // Reference.

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

				$post_id_key             = $this->isset_or($post_id, -1, 'integer');
				$status_key              = strtolower((string)$status); // Lowercase.
				$comment_id_key          = $this->isset_or($comment_id, -1, 'integer');
				$auto_discount_trash_key = (integer)$auto_discount_trash;
				$group_by_email_key      = (integer)$group_by_email;

				if(!$no_cache && isset($this->cache[__FUNCTION__][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key]))
					return $this->cache[__FUNCTION__][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key];

				$this->cache[__FUNCTION__][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key]
					    = 0; // Initialize this cache entry for the routines below; i.e. a shorter reference will be necesseary.
				$total = &$this->cache[__FUNCTION__][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key];

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
					'status'              => '',
					'comment_id'          => NULL,
					'auto_discount_trash' => TRUE,
					'group_by_email'      => TRUE,
					'no_cache'            => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$status              = trim((string)$args['status']);
				$comment_id          = $this->isset_or($args['comment_id'], NULL, 'integer');
				$auto_discount_trash = (boolean)$args['auto_discount_trash'];
				$group_by_email      = (boolean)$args['group_by_email'];
				$no_cache            = (boolean)$args['no_cache'];

				$post_id_key             = $this->isset_or($post_id, -1, 'integer');
				$status_key              = strtolower((string)$status); // Force lowercase.
				$comment_id_key          = $this->isset_or($comment_id, -1, 'integer');
				$auto_discount_trash_key = (integer)$auto_discount_trash;
				$group_by_email_key      = (integer)$group_by_email;

				if(!$no_cache && isset($this->cache[__FUNCTION__][$x][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key]))
					return $this->cache[__FUNCTION__][$x][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key];

				$this->cache[__FUNCTION__][$x][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key]
					     = array(); // Initialize this cache entry for the routines below; i.e. a shorter reference will be necesseary.
				$last_x = &$this->cache[__FUNCTION__][$x][$post_id_key][$status_key][$comment_id_key][$auto_discount_trash_key][$group_by_email_key];

				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Initialize where clause.

				       ($status // A specific status in this case?
					       ? " AND `status` = '".esc_sql((string)$status)."'"
					       : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

				       (isset($post_id) ? " AND `post_id` = '".esc_sql((integer)$post_id)."'" : '').
				       (isset($comment_id) ? " AND `comment_id` = '".esc_sql((integer)$comment_id)."'" : '').

				       ($group_by_email ? " GROUP BY `email`" : '').

				       " ORDER BY `insertion_time` DESC".

				       " LIMIT ".esc_sql($x); // X rows only please.

				if(($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					return ($last_x = $results = $this->plugin->utils_db->typify_deep($results));

				return ($last_x = array()); // Default value.
			}

			/**
			 * Email address decrypted automagically.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $email Subscriber email address.
			 *
			 * @return string Subscriber email address; else an emtpy string.
			 */
			public function decrypt_email($email)
			{
				if(!($email = trim((string)$email))) // Force string.
					return ''; // Not possible in this case.

				if(!is_email($email) && is_email($decrypted_email = $this->plugin->utils_enc->decrypt($email)))
					$email = $decrypted_email; // Decrypted automatically.

				return $email; // Decrypted; i.e. plain text.
			}

			/**
			 * Check existing email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $email Email address to check.
			 *
			 * @return boolean TRUE if email exists already.
			 */
			public function email_exists($email)
			{
				if(!($email = $this->decrypt_email((string)$email)))
					return FALSE; // Not possible.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `email` = '".esc_sql($email)."'".

				       " LIMIT 1"; // One to check.

				return (boolean)$this->plugin->utils_db->wp->get_var($sql);
			}

			/**
			 * Current sub's email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current subscriber's email address.
			 */
			public function current_email()
			{
				if(($user = wp_get_current_user()) && $user->exists() && $user->user_email)
					return (string)$user->user_email; // Force string.

				if(($email = $this->plugin->utils_enc->get_cookie(__NAMESPACE__.'_sub_email')))
					return (string)$email; // Force string.

				if(($commenter = wp_get_current_commenter()) && !empty($commenter['comment_author_email']))
					return (string)$commenter['comment_author_email']; // Force string.

				return ''; // Not possible.
			}

			/**
			 * Set current sub's email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $email Subscriber's current email address.
			 */
			public function set_current_email($email)
			{
				$email = $this->decrypt_email((string)$email);

				$this->plugin->utils_enc->set_cookie(__NAMESPACE__.'_sub_email', $email);
			}

			/**
			 * Confirmation URL for a specific sub. key.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $key Unique subscription key.
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function confirm_url($key, $scheme = NULL)
			{
				$key  = trim((string)$key); // Force string.
				$args = array(__NAMESPACE__ => array('confirm' => $key));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}

			/**
			 * Unsubscribe URL for a specific sub. key.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $key Unique subscription key.
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function unsubscribe_url($key, $scheme = NULL)
			{
				$key  = trim((string)$key); // Force string.
				$args = array(__NAMESPACE__ => array('unsubscribe' => $key));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}

			/**
			 * Manage URL for a specific email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param null|string $email Subscriber's email address.
			 *    This is optional. If `NULL` we use `current_email()`.
			 *
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function manage_url($email = NULL, $scheme = NULL)
			{
				if(!isset($email))
					$email = $this->current_email();
				$email = $this->decrypt_email((string)$email);

				$encrypted_email = $this->plugin->utils_enc->encrypt($email);
				$args            = array(__NAMESPACE__ => array('manage' => $encrypted_email));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}

			/**
			 * Manage URL for a specific email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param null|string $email Subscriber's email address.
			 *    This is optional. If `NULL` we use `current_email()`.
			 *
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function manage_summary_url($email = NULL, $scheme = NULL)
			{
				if(!isset($email))
					$email = $this->current_email();
				$email = $this->decrypt_email((string)$email);

				$encrypted_email = $this->plugin->utils_enc->encrypt($email);
				$args            = array(__NAMESPACE__ => array('manage' => array('summary' => $encrypted_email)));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}
		}
	}
}