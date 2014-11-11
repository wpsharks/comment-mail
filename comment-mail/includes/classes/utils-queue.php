<?php
/**
 * Queue Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_queue'))
	{
		/**
		 * Queue Utilities
		 *
		 * @since 141111 First documented version.
		 */
		class utils_queue extends abs_base
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
			 * Unique IDs only.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $queue_ids Queued notification IDs.
			 *
			 * @return array An array of unique IDs only.
			 */
			public function unique_ids(array $queue_ids)
			{
				$unique_ids = array(); // Initialize.

				foreach($queue_ids as $_queue_id)
				{
					if(is_numeric($_queue_id) && (integer)$_queue_id > 0)
						$unique_ids[] = (integer)$_queue_id;
				}
				unset($_queue_id); // Housekeeping.

				if($unique_ids) // Unique IDs only.
					$unique_ids = array_unique($unique_ids);

				return $unique_ids;
			}

			/**
			 * Delete queued notification.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer|string $queue_id Queued notification ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if queued notification is deleted successfully.
			 *    Or, FALSE if unable to delete (e.g. already deleted).
			 *    Or, NULL on complete failure (e.g. invalid ID).
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			public function delete($queue_id, array $args = array())
			{
				if(!($queue_id = (integer)$queue_id))
					return NULL; // Not possible.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'queue')."`".
				       " WHERE `ID` = '".esc_sql($queue_id)."'";

				if(($deleted = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));

				return (boolean)$deleted; // Convert to boolean value.
			}

			/**
			 * Bulk delete queued notifications.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $queue_ids Queued notification IDs.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of queued notifications deleted successfully.
			 */
			public function bulk_delete(array $queue_ids, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids($queue_ids) as $_queue_id)
					if($this->delete($_queue_id, $args))
						$counter++; // Bump counter.
				unset($_queue_id); // Housekeeping.

				return $counter;
			}
		}
	}
}