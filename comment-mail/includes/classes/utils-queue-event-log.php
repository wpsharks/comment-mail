<?php
/**
 * Queue Event Log Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_queue_event_log'))
	{
		/**
		 * Queue Event Log Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_queue_event_log extends abs_base
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
			 * Unique IDs only.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $log_entry_ids Event log entry IDs.
			 *
			 * @return array An array of unique IDs only.
			 */
			public function unique_ids(array $log_entry_ids)
			{
				$unique_ids = array(); // Initialize.

				foreach($log_entry_ids as $_log_entry_id)
				{
					if(is_numeric($_log_entry_id) && (integer)$_log_entry_id > 0)
						$unique_ids[] = (integer)$_log_entry_id;
				}
				unset($_log_entry_id); // Housekeeping.

				if($unique_ids) // Unique IDs only.
					$unique_ids = array_unique($unique_ids);

				return $unique_ids;
			}

			/**
			 * Delete log entry.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $log_entry_id Log entry ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if log entry is deleted successfully.
			 *    Or, FALSE if unable to delete (e.g. already deleted).
			 *    Or, NULL on complete failure (e.g. invalid ID).
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			public function delete($log_entry_id, array $args = array())
			{
				if(!($log_entry_id = (integer)$log_entry_id))
					return NULL; // Not possible.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".
				       " WHERE `ID` = '".esc_sql($log_entry_id)."'";

				if(($deleted = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));

				return (boolean)$deleted; // Convert to boolean value.
			}

			/**
			 * Bulk delete log entries.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $log_entry_ids Log entry IDs.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of log entries deleted successfully.
			 */
			public function bulk_delete(array $log_entry_ids, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids($log_entry_ids) as $_log_entry_id)
					if($this->delete($_log_entry_id, $args))
						$counter++; // Bump counter.
				unset($_log_entry_id); // Housekeeping.

				return $counter;
			}
		}
	}
}