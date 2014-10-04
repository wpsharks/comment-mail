<?php
/**
 * Subscriber Utilities
 *
 * @package utils_sub
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
		 * Subscriber Utilities
		 *
		 * @package utils_sub
		 * @since 14xxxx First documented version.
		 */
		class utils_sub // Subscriber utilities.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var array Instance cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $cache = array();

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
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

				return ($cache[$sub_id_or_key] = NULL); // Default.
			}

			/**
			 * Confirm subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $log_confirmed_event Log `confirmed` event?
			 *
			 * @param string         $last_ip Most recent IP address, when possible.
			 *
			 * @return boolean|null TRUE if subscriber is confirmed successfully.
			 *    Or, FALSE if unable to confirm (e.g. already confirmed).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
			 *
			 * @throws \exception If an update failure occurs.
			 */
			public function confirm($sub_id_or_key, $log_confirmed_event = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return NULL; // Not possible.

				$last_ip = (string)$last_ip; // Force string.

				if($log_confirmed_event) // Log `confirmed` event?
					new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'confirmed')));

				$sql = "UPDATE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " SET `status` = '".esc_sql('subscribed')."'".
				       ($last_ip ? ", `last_ip` = '".esc_sql($last_ip)."'" : '').
				       ", `last_update_time` = '".esc_sql(time())."'".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($confirmed = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));

				if(($confirmed = (boolean)$confirmed)) // Convert to boolean.
				{
					$sub->status = 'subscribed'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();
				}
				return $confirmed; // TRUE if confirmed successfully.
			}

			/**
			 * Delete subscriber.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id_or_key Subscriber ID.
			 *
			 * @param boolean        $log_unsubscribed_event Log `unsubscribed` event?
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
			public function delete($sub_id_or_key, $log_unsubscribed_event = FALSE, $last_ip = '')
			{
				if(!$sub_id_or_key)
					return NULL; // Not possible.

				if(!($sub = $this->get($sub_id_or_key)))
					return FALSE; // Deleted already.

				$last_ip = (string)$last_ip; // Force string.

				if($log_unsubscribed_event) // Log `unsubscribed` event?
					new sub_event_log_inserter(array_merge((array)$sub, array('event' => 'unsubscribed')));

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `ID` = '".esc_sql($sub->ID)."'";

				if(($deleted = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));

				if(($deleted = (boolean)$deleted)) // Convert to boolean.
				{
					$this->cache['get'][$sub->ID] // Nullify cache.
						= $this->cache['get'][$sub->key] = NULL;

					$sub->status = 'deleted'; // Obj. properties.
					if($last_ip) $sub->last_ip = $last_ip;
					$sub->last_update_time = time();
				}
				return $deleted; // TRUE if deleted successfully.
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
		}
	}
}