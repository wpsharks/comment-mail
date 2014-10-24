<?php
/**
 * Queue Utilities
 *
 * @since 14xxxx First documented version.
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
		 * @since 14xxxx First documented version.
		 */
		class utils_queue extends abstract_base
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
			 * @param array $queue__ids Queued notification IDs.
			 *
			 * @return array An array of unique IDs only.
			 */
			public function unique_ids_only(array $queue__ids)
			{
				$queue_ids = array(); // Initialize.

				foreach($queue__ids as $_queue_id)
				{
					if(is_numeric($_queue_id) && (integer)$_queue_id > 0)
						$queue_ids[] = (integer)$_queue_id;
				}
				unset($_queue_id); // Housekeeping.

				if($queue_ids) // Unique IDs only.
					$queue_ids = array_unique($queue_ids);

				return $queue_ids;
			}

			/**
			 * Delete queued notification.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $queue_id Queued notification ID.
			 * @param array          $args Any additional behavioral args.
			 *
			 * @return boolean|null TRUE if queued notification is deleted successfully.
			 *    Or, FALSE if unable to delete (e.g. already deleted).
			 *    Or, NULL on complete failure (e.g. invalid ID or key).
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
			 * @since 14xxxx First documented version.
			 *
			 * @param array $queue_ids Queued notification IDs.
			 * @param array $args Any additional behavioral args.
			 *
			 * @return integer Number of queued notifications deleted successfully.
			 */
			public function bulk_delete(array $queue_ids, array $args = array())
			{
				$counter = 0; // Initialize.

				foreach($this->unique_ids_only($queue_ids) as $_queue_id)
					if($this->delete($_queue_id, $args))
						$counter++; // Bump counter.
				unset($_queue_id); // Housekeeping.

				return $counter;
			}
		}
	}
}