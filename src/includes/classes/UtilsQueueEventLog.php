<?php
/**
 * Queue Event Log Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Queue Event Log Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsQueueEventLog extends AbsBase
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
     * @param array $log_entry_ids Event log entry IDs.
     *
     * @return array An array of unique IDs only.
     */
    public function uniqueIds(array $log_entry_ids)
    {
        $unique_ids = []; // Initialize.

        foreach ($log_entry_ids as $_log_entry_id) {
            if (is_numeric($_log_entry_id) && (integer) $_log_entry_id > 0) {
                $unique_ids[] = (integer) $_log_entry_id;
            }
        }
        unset($_log_entry_id); // Housekeeping.

        if ($unique_ids) {  // Unique IDs only.
            $unique_ids = array_unique($unique_ids);
        }
        return $unique_ids;
    }

    /**
     * Delete log entry.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $log_entry_id Log entry ID.
     * @param array      $args         Any additional behavioral args.
     *
     * @throws \exception If a deletion failure occurs.
     * @return bool|null TRUE if log entry is deleted successfully.
     *                   Or, FALSE if unable to delete (e.g. already deleted).
     *                   Or, NULL on complete failure (e.g. invalid ID).
     *
     */
    public function delete($log_entry_id, array $args = [])
    {
        if (!($log_entry_id = (integer) $log_entry_id)) {
            return null; // Not possible.
        }
        $sql = 'DELETE FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.
               " WHERE `ID` = '".esc_sql($log_entry_id)."'";

        if (($deleted = $this->plugin->utils_db->wp->query($sql)) === false) {
            throw new \exception(__('Deletion failure.', 'comment-mail'));
        }
        return (boolean) $deleted; // Convert to boolean value.
    }

    /**
     * Bulk delete log entries.
     *
     * @since 141111 First documented version.
     *
     * @param array $log_entry_ids Log entry IDs.
     * @param array $args          Any additional behavioral args.
     *
     * @return int Number of log entries deleted successfully.
     */
    public function bulkDelete(array $log_entry_ids, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($log_entry_ids) as $_log_entry_id) {
            if ($this->delete($_log_entry_id, $args)) {
                ++$counter; // Bump counter.
            }
        }
        unset($_log_entry_id); // Housekeeping.

        return $counter;
    }
}
