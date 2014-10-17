<?php
/**
 * Subscriber Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 *
 * @TODO check status before in each of these methods; before firing an event.
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_sub'))
	{
		/**
		 * Subscriber Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_sub extends abstract_base
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
			 * Get subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $no_cache Defaults to a FALSE value.
			 *    TRUE if you want to avoid a potentially cached value.
			 *
			 * @return \stdClass|null Subscriber object, if possible.
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

				if($cache && count($cache) > 2000) // Get too large?
					// Never allow more than `2000` cached entries into memory.
					$cache = array_slice($this->plugin->utils_array->shuffle($cache), 0, 2000, TRUE);

				if(is_string($sub_id_or_key) && !is_numeric($sub_id_or_key)) // A key?
				{
					$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
					       " WHERE `key` = '".esc_sql($sub_id_or_key)."' LIMIT 1";
				}
				else $sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				            " WHERE `ID` = '".esc_sql((integer)$sub_id_or_key)."' LIMIT 1";

				if(($row = $this->plugin->utils_db->wp->get_row($sql)))
					return ($cache[$row->ID] = $cache[$row->key] = $row = $this->plugin->utils_db->typify_deep($row));

				return ($cache[$sub_id_or_key] = NULL);
			}

			/**
			 * Reconfirm subscriber via email.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $process_events Process events?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is reconfirmed successfully.
			 *    Or, FALSE if unable to reconfirm (e.g. already confirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @throws \exception If an update failure occurs.
			 */
			public function reconfirm($sub_id_or_key, $process_events = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'subscribed')
					return FALSE; // Already confirmed.

				$last_ip = (string)$last_ip; // Force string.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('unconfirmed')."'".
				       ($last_ip ? ", `last_ip` = '".esc_sql($last_ip)."'" : '').
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($confirmed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$confirmed = (boolean)$confirmed; // Convert to boolean.

				if($confirmed) // Confirmed successfully?
				{
					$sub->status = 'unconfirmed'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();

					$this->nullify_cache(); // Nullify cache.

					if($process_events) // Log a `subscribed` event here?
						new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'subscribed')));
					new sub_confirmer($sub->ID, compact('process_events'));
				}
				return $confirmed;
			}

			/**
			 * Bulk reconfirm subscribers via email.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscriber IDs/keys.
			 *
			 * @return integer Number of suscribers reconfirmed successfully.
			 */
			public function bulk_reconfirm(array $sub_ids_or_keys)
			{
				$counter = 0; // Initialize.

				if(!$sub_ids_or_keys)
					return $counter; // Not possible.

				@set_time_limit(300); // Give this time.
				@set_time_limit(900); // Give this time.

				foreach($sub_ids_or_keys as $_sub_id_or_key)
					if($this->reconfirm($_sub_id_or_key))
						$counter++; // Updated counter.
				unset($_sub_id_or_key); // Housekeeping.

				$this->nullify_cache($sub_ids_or_keys);

				return $counter;
			}

			/**
			 * Confirm subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $process_events Process events?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is confirmed successfully.
			 *    Or, FALSE if unable to confirm (e.g. already confirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @throws \exception If an update failure occurs.
			 */
			public function confirm($sub_id_or_key, $process_events = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'subscribed')
					return FALSE; // Already confirmed.

				$last_ip = (string)$last_ip; // Force string.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('subscribed')."'".
				       ($last_ip ? ", `last_ip` = '".esc_sql($last_ip)."'" : '').
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($confirmed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$confirmed = (boolean)$confirmed; // Convert to boolean.

				if($confirmed) // Confirmed successfully?
				{
					$sub->status = 'subscribed'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();

					$this->nullify_cache(); // Nullify cache.

					if($process_events) // Log a `confirmed` event here?
						new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'confirmed')));
				}
				return $confirmed;
			}

			/**
			 * Bulk confirm subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscriber IDs/keys.
			 *
			 * @return integer Number of suscribers confirmed successfully.
			 *
			 * @throws \exception If a DB update failure occurs.
			 */
			public function bulk_confirm(array $sub_ids_or_keys)
			{
				$counter = 0; // Initialize.

				if(!$sub_ids_or_keys)
					return $counter; // Not possible.

				$separate // Separate IDs from keys.
					= $this->separate_ids_keys($sub_ids_or_keys);

				if(!$separate['sub_ids'] && !$separate['sub_keys'])
					return $counter; // Not possible.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('subscribed')."'".
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE". // Begin MySQL where clause.

				       ($separate['sub_ids'] ? // Have subscriber IDs?
					       " `ID` IN ('".implode("','", array_map('esc_sql', $separate['sub_ids']))."')"
					       : ''). // Otherwise, we can simply exlude this.

				       ($separate['sub_keys'] ? // Have subscriber keys?
					       ($separate['sub_ids'] ? " OR" : ''). // Need the `OR` here?
					       " `key` IN ('".implode("','", array_map('esc_sql', $separate['sub_keys']))."')"
					       : ''); // Otherwise, we can simply exlude this.

				if(($confirmed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$counter += (integer)$confirmed; // Bump counter.

				$this->nullify_cache($sub_ids_or_keys);

				return $counter;
			}

			/**
			 * Unconfirm subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $process_events Log `unsubscribed` event?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is unconfirmed successfully.
			 *    Or, FALSE if unable to unconfirm (e.g. already unconfirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @throws \exception If an update failure occurs.
			 */
			public function unconfirm($sub_id_or_key, $process_events = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'unconfirmed')
					return FALSE; // Already unconfirmed.

				$last_ip = (string)$last_ip; // Force string.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('unconfirmed')."'".
				       ($last_ip ? ", `last_ip` = '".esc_sql($last_ip)."'" : '').
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($unconfirmed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$unconfirmed = (boolean)$unconfirmed; // Convert to boolean.

				if($unconfirmed) // Unconfirmed successfully?
				{
					$sub->status = 'unconfirmed'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();

					$this->nullify_cache(); // Nullify cache.

					if($process_events) // Log an `unsubscribed` event here?
						new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'unsubscribed')));
				}
				return $unconfirmed;
			}

			/**
			 * Bulk unconfirm subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscriber IDs/keys.
			 *
			 * @return integer Number of suscribers unconfirmed successfully.
			 *
			 * @throws \exception If a DB update failure occurs.
			 */
			public function bulk_unconfirm(array $sub_ids_or_keys)
			{
				$counter = 0; // Initialize.

				if(!$sub_ids_or_keys)
					return $counter; // Not possible.

				$separate // Separate IDs from keys.
					= $this->separate_ids_keys($sub_ids_or_keys);

				if(!$separate['sub_ids'] && !$separate['sub_keys'])
					return $counter; // Not possible.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('unconfirmed')."'".
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE". // Begin MySQL where clause.

				       ($separate['sub_ids'] ? // Have subscriber IDs?
					       " `ID` IN ('".implode("','", array_map('esc_sql', $separate['sub_ids']))."')"
					       : ''). // Otherwise, we can simply exlude this.

				       ($separate['sub_keys'] ? // Have subscriber keys?
					       ($separate['sub_ids'] ? " OR" : ''). // Need the `OR` here?
					       " `key` IN ('".implode("','", array_map('esc_sql', $separate['sub_keys']))."')"
					       : ''); // Otherwise, we can simply exlude this.

				if(($unconfirmed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$counter += (integer)$unconfirmed; // Bump counter.

				$this->nullify_cache($sub_ids_or_keys);

				return $counter;
			}

			/**
			 * Suspend subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $process_events Process events?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is suspended successfully.
			 *    Or, FALSE if unable to suspend (e.g. already suspended).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @throws \exception If an update failure occurs.
			 */
			public function suspend($sub_id_or_key, $process_events = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'suspended')
					return FALSE; // Already suspended.

				$last_ip = (string)$last_ip; // Force string.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('suspended')."'".
				       ($last_ip ? ", `last_ip` = '".esc_sql($last_ip)."'" : '').
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($suspended = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$suspended = (boolean)$suspended; // Convert to boolean.

				if($suspended) // Suspended successfully?
				{
					$sub->status = 'suspended'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();

					$this->nullify_cache(); // Nullify cache.

					if($process_events) // Log a `suspended` event here?
						new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'suspended')));
				}
				return $suspended;
			}

			/**
			 * Bulk suspend subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscriber IDs/keys.
			 *
			 * @return integer Number of suscribers suspended successfully.
			 *
			 * @throws \exception If a DB update failure occurs.
			 */
			public function bulk_suspend(array $sub_ids_or_keys)
			{
				$counter = 0; // Initialize.

				if(!$sub_ids_or_keys)
					return $counter; // Not possible.

				$separate // Separate IDs from keys.
					= $this->separate_ids_keys($sub_ids_or_keys);

				if(!$separate['sub_ids'] && !$separate['sub_keys'])
					return $counter; // Not possible.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('suspended')."'".
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE". // Begin MySQL where clause.

				       ($separate['sub_ids'] ? // Have subscriber IDs?
					       " `ID` IN ('".implode("','", array_map('esc_sql', $separate['sub_ids']))."')"
					       : ''). // Otherwise, we can simply exlude this.

				       ($separate['sub_keys'] ? // Have subscriber keys?
					       ($separate['sub_ids'] ? " OR" : ''). // Need the `OR` here?
					       " `key` IN ('".implode("','", array_map('esc_sql', $separate['sub_keys']))."')"
					       : ''); // Otherwise, we can simply exlude this.

				if(($suspended = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$counter += (integer)$suspended; // Bump counter.

				$this->nullify_cache($sub_ids_or_keys);

				return $counter;
			}

			/**
			 * Trash subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $process_events Process events?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is trashed successfully.
			 *    Or, FALSE if unable to trash (e.g. already trashed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @throws \exception If an update failure occurs.
			 */
			public function trash($sub_id_or_key, $process_events = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				if($sub->status === 'trashed')
					return FALSE; // Already trashed.

				$last_ip = (string)$last_ip; // Force string.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('trashed')."'".
				       ($last_ip ? ", `last_ip` = '".esc_sql($last_ip)."'" : '').
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($trashed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$trashed = (boolean)$trashed; // Convert to boolean.

				if($trashed) // Trashed successfully?
				{
					$sub->status = 'trashed'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();

					$this->nullify_cache(); // Nullify cache.

					if($process_events) // Log an `unsubscribed` event here?
						new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'unsubscribed')));
				}
				return $trashed;
			}

			/**
			 * Bulk trash subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscriber IDs/keys.
			 *
			 * @return integer Number of suscribers trashed successfully.
			 *
			 * @throws \exception If a DB update failure occurs.
			 */
			public function bulk_trash(array $sub_ids_or_keys)
			{
				$counter = 0; // Initialize.

				if(!$sub_ids_or_keys)
					return $counter; // Not possible.

				$separate // Separate IDs from keys.
					= $this->separate_ids_keys($sub_ids_or_keys);

				if(!$separate['sub_ids'] && !$separate['sub_keys'])
					return $counter; // Not possible.

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('trashed')."'".
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE". // Begin MySQL where clause.

				       ($separate['sub_ids'] ? // Have subscriber IDs?
					       " `ID` IN ('".implode("','", array_map('esc_sql', $separate['sub_ids']))."')"
					       : ''). // Otherwise, we can simply exlude this.

				       ($separate['sub_keys'] ? // Have subscriber keys?
					       ($separate['sub_ids'] ? " OR" : ''). // Need the `OR` here?
					       " `key` IN ('".implode("','", array_map('esc_sql', $separate['sub_keys']))."')"
					       : ''); // Otherwise, we can simply exlude this.

				if(($trashed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
				$counter += (integer)$trashed; // Bump counter.

				$this->nullify_cache($sub_ids_or_keys);

				return $counter;
			}

			/**
			 * Delete subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $process_events Process events?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is deleted successfully.
			 *    Or, FALSE if unable to delete (e.g. already deleted).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @note There is one additional status that a subscriber can have (in code only, not in the DB).
			 *    See below. It's possible for a subscriber to have a `deleted` status.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			public function delete($sub_id_or_key, $process_events = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return FALSE; // Deleted already.

				$last_ip = (string)$last_ip; // Force string.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($deleted = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
				$deleted = (boolean)$deleted; // Convert to boolean.

				if($deleted) // Deleted successfully?
				{
					$sub->status = 'deleted'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();

					$this->nullify_cache(array($sub->ID, $sub->key));

					if($process_events) // Log an `unsubscribed` event here?
						new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'unsubscribed')));
				}
				return $deleted;
			}

			/**
			 * Bulk delete subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys Subscriber IDs/keys.
			 *
			 * @return integer Number of suscribers deleted successfully.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			public function bulk_delete(array $sub_ids_or_keys)
			{
				$counter = 0; // Initialize.

				if(!$sub_ids_or_keys)
					return $counter; // Not possible.

				$separate // Separate IDs from keys.
					= $this->separate_ids_keys($sub_ids_or_keys);

				if(!$separate['sub_ids'] && !$separate['sub_keys'])
					return $counter; // Not possible.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE". // Begin MySQL where clause.

				       ($separate['sub_ids'] ? // Have subscriber IDs?
					       " `ID` IN ('".implode("','", array_map('esc_sql', $separate['sub_ids']))."')"
					       : ''). // Otherwise, we can simply exlude this.

				       ($separate['sub_keys'] ? // Have subscriber keys?
					       ($separate['sub_ids'] ? " OR" : ''). // Need the `OR` here?
					       " `key` IN ('".implode("','", array_map('esc_sql', $separate['sub_keys']))."')"
					       : ''); // Otherwise, we can simply exlude this.

				if(($deleted = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
				$counter += (integer)$deleted; // Bump counter.

				$this->nullify_cache($sub_ids_or_keys);

				return $counter;
			}

			/**
			 * Query total subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|null $post_id Defaults to a `NULL` value.
			 *    i.e. defaults to any post ID. Pass this to limit the query.
			 *
			 * @param integer|null $comment_id Defaults to a `NULL` value.
			 *    i.e. defaults to any comment ID. Pass this to limit the query.
			 *
			 * @param string|null  $status Defaults to an empty string.
			 *    i.e. defaults to any status. Pass this to limit the query.
			 *
			 * @param boolean      $auto_discount_trash Defaults to a `TRUE` value.
			 *    This applies to the case where `$status` is empty.
			 *    i.e. do not count subscribers in the trash.
			 *
			 * @return integer Total subscribers for the given query.
			 */
			public function query_total($post_id = NULL, $comment_id = NULL, $status = '', $auto_discount_trash = TRUE)
			{
				$post_id_key             = isset($post_id) ? (integer)$post_id : -1;
				$comment_id_key          = isset($comment_id) ? (integer)$comment_id : -1;
				$status_key              = $status = (string)$status; // Force string.
				$auto_discount_trash_key = $auto_discount_trash ? 1 : 0;

				if(isset($this->cache[__FUNCTION__][$post_id_key][$comment_id_key][$status_key][$auto_discount_trash_key]))
					return $this->cache[__FUNCTION__][$post_id_key][$comment_id_key][$status_key][$auto_discount_trash_key];
				$total = &$this->cache[__FUNCTION__][$post_id_key][$comment_id_key][$status_key][$auto_discount_trash_key];

				$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`".
				       " FROM `".esc_html($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Initialize where clause.

				       ($status // A specific status?
					       ? " AND `status` = '".esc_sql((string)$status)."'"
					       : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

				       (isset($post_id) ? " AND `post_id` = '".esc_sql((integer)$post_id)."'" : '').
				       (isset($comment_id) ? " AND `comment_id` = '".esc_sql((integer)$comment_id)."'" : '').

				       " LIMIT 1"; // Just one to check.

				if($this->plugin->utils_db->wp->query($sql))
					return ($total = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()"));

				return ($total = 0); // Default value.
			}

			/**
			 * Last X subscribers w/ a given status.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer      $x The total number to return.
			 *
			 * @param integer|null $post_id Defaults to a `NULL` value.
			 *    i.e. defaults to any post ID. Pass this to limit the query.
			 *
			 * @param integer|null $comment_id Defaults to a `NULL` value.
			 *    i.e. defaults to any comment ID. Pass this to limit the query.
			 *
			 * @param string|null  $status Defaults to an empty string.
			 *    i.e. defaults to any status. Pass this to limit the query.
			 *
			 * @param boolean      $auto_discount_trash Defaults to a `TRUE` value.
			 *    This applies to the case where `$status` is empty.
			 *    i.e. do not count subscribers in the trash.
			 *
			 * @return \stdClass[] Last X subscribers w/ a given status.
			 */
			public function last_x($x = 0, $post_id = NULL, $comment_id = NULL, $status = '', $auto_discount_trash = TRUE)
			{
				if(($x = (integer)$x) <= 0) $x = 10; // Default value.
				$post_id_key             = isset($post_id) ? (integer)$post_id : -1;
				$comment_id_key          = isset($comment_id) ? (integer)$comment_id : -1;
				$status_key              = $status = (string)$status; // Force string.
				$auto_discount_trash_key = $auto_discount_trash ? 1 : 0;

				if(isset($this->cache[__FUNCTION__][$x][$post_id_key][$comment_id_key][$status_key][$auto_discount_trash_key]))
					return $this->cache[__FUNCTION__][$x][$post_id_key][$comment_id_key][$status_key][$auto_discount_trash_key];
				$results = &$this->cache[__FUNCTION__][$x][$post_id_key][$comment_id_key][$status_key][$auto_discount_trash_key];

				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE 1=1". // Initialize where clause.

				       ($status // A specific status?
					       ? " AND `status` = '".esc_sql((string)$status)."'"
					       : ($auto_discount_trash ? " AND `status` != '".esc_sql('trashed')."'" : '')).

				       (isset($post_id) ? " AND `post_id` = '".esc_sql((integer)$post_id)."'" : '').
				       (isset($comment_id) ? " AND `comment_id` = '".esc_sql((integer)$comment_id)."'" : '').

				       " GROUP BY `email` ORDER BY `insertion_time` DESC".
				       " LIMIT ".esc_sql($x); // X rows only please.

				if(($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					return ($results = $this->plugin->utils_db->typify_deep($results));

				return ($results = array()); // Default value.
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
				unset($this->cache['query_total'], $this->cache['last_x']);

				if(!$sub_ids_or_keys) return; // Nothing more to do.

				$separate // Separate IDs from keys.
					= $this->separate_ids_keys($sub_ids_or_keys);

				foreach($separate['sub_ids'] as $_sub_id)
					$this->cache['get'][$_sub_id] = NULL;
				unset($_sub_id); // Housekeeping.

				foreach($separate['sub_keys'] as $_sub_key)
					$this->cache['get'][$_sub_key] = NULL;
				unset($_sub_key); // Housekeeping.

				// This prevents odd cache conflicts at runtime.
			}

			/**
			 * Separates IDs from keys.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $sub_ids_or_keys An array of IDs/keys.
			 *
			 * @return array An array with two elements.
			 *    - `sub_ids`, an array of all of the sub IDs.
			 *    - `sub_keys`, an array of all of the sub keys.
			 */
			public function separate_ids_keys(array $sub_ids_or_keys)
			{
				$sub_ids = $sub_keys = array(); // Initialize.

				foreach($sub_ids_or_keys as $_sub_id_or_key)
				{
					if(is_numeric($_sub_id_or_key) && (integer)$_sub_id_or_key > 0)
						$sub_ids[] = (integer)$_sub_id_or_key;

					else if(is_string($_sub_id_or_key) && $_sub_id_or_key)
						$sub_keys[] = $_sub_id_or_key;
				}
				unset($_sub_id_or_key); // Housekeeping.

				return compact('sub_ids', 'sub_keys');
			}

			/**
			 * Subscriber key to ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key Input key to convert to an ID.
			 *
			 * @return integer The subscriber ID matching the input `$key`.
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
				if(!($email = (string)$email))
					return FALSE; // Not possible.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `email` = '".esc_sql($email)."'".

				       " LIMIT 1"; // Only need one row to check this.

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
					return (string)$user->user_email;

				if(($sub_email = $this->plugin->utils_enc->get_cookie(__NAMESPACE__.'_sub_email')))
					return (string)$sub_email;

				if(($commenter = wp_get_current_commenter()) && !empty($commenter['comment_author_email']))
					return (string)$commenter['comment_author_email'];

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
				$email = trim((string)$email);

				$this->plugin->utils_enc->set_cookie(__NAMESPACE__.'_sub_email', $email);
			}

			/**
			 * Confirmation URL for a specific sub. key.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $sub_key Unique subscription key.
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function confirm_url($sub_key, $scheme = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$args    = array(__NAMESPACE__ => array('confirm' => $sub_key));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}

			/**
			 * Unsubscribe URL for a specific sub. key.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $sub_key Unique subscription key.
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function unsubscribe_url($sub_key, $scheme = NULL)
			{
				$sub_key = trim((string)$sub_key);
				$args    = array(__NAMESPACE__ => array('unsubscribe' => $sub_key));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}

			/**
			 * Manage URL for a specific email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param null|string $sub_email Subscribers email address.
			 *    This is optional. If `NULL` we use `current_email()`.
			 *
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function manage_url($sub_email = NULL, $scheme = NULL)
			{
				if(!isset($sub_email))
					$sub_email = $this->current_email();
				$sub_email = trim((string)$sub_email);

				$encrypted_sub_email = $this->plugin->utils_enc->encrypt($sub_email);
				$args                = array(__NAMESPACE__ => array('manage' => $encrypted_sub_email));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}

			/**
			 * Manage URL for a specific email address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param null|string $sub_email Subscribers email address.
			 *    This is optional. If `NULL` we use `current_email()`.
			 *
			 * @param string|null $scheme Optiona. Defaults to a `NULL` value.
			 *    See `home_url()` in WordPress for further details on this.
			 *
			 * @return string URL w/ the given `$scheme`.
			 */
			public function manage_summary_url($sub_email = NULL, $scheme = NULL)
			{
				if(!isset($sub_email))
					$sub_email = $this->current_email();
				$sub_email = trim((string)$sub_email);

				$encrypted_sub_email = $this->plugin->utils_enc->encrypt($sub_email);
				$args                = array(__NAMESPACE__ => array('manage' => array('summary' => $encrypted_sub_email)));

				return add_query_arg(urlencode_deep($args), home_url('/', $scheme));
			}
		}
	}
}