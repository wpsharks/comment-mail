<?php
/**
 * Queue Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Queue Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsQueue extends AbsBase
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
    public function uniqueIds(array $queue_ids)
    {
        $unique_ids = []; // Initialize.

        foreach ($queue_ids as $_queue_id) {
            if (is_numeric($_queue_id) && (integer) $_queue_id > 0) {
                $unique_ids[] = (integer) $_queue_id;
            }
        }
        unset($_queue_id); // Housekeeping.

        if ($unique_ids) { // Unique IDs only.
            $unique_ids = array_unique($unique_ids);
        }
        return $unique_ids;
    }

    /**
     * Delete queued notification.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $queue_id Queued notification ID.
     * @param array      $args     Any additional behavioral args.
     *
     * @throws \exception If a deletion failure occurs.
     * @return bool|null TRUE if queued notification is deleted successfully.
     *                   Or, FALSE if unable to delete (e.g. already deleted).
     *                   Or, NULL on complete failure (e.g. invalid ID).
     *
     */
    public function delete($queue_id, array $args = [])
    {
        if (!($queue_id = (integer) $queue_id)) {
            return null; // Not possible.
        }
        $sql = 'DELETE FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue').'`'.
               " WHERE `ID` = '".esc_sql($queue_id)."'";

        if (($deleted = $this->plugin->utils_db->wp->query($sql)) === false) {
            throw new \exception(__('Deletion failure.', 'comment-mail'));
        }
        return (boolean) $deleted; // Convert to boolean value.
    }

    /**
     * Bulk delete queued notifications.
     *
     * @since 141111 First documented version.
     *
     * @param array $queue_ids Queued notification IDs.
     * @param array $args      Any additional behavioral args.
     *
     * @return int Number of queued notifications deleted successfully.
     */
    public function bulkDelete(array $queue_ids, array $args = [])
    {
        $counter = 0; // Initialize.

        foreach ($this->uniqueIds($queue_ids) as $_queue_id) {
            if ($this->delete($_queue_id, $args)) {
                ++$counter; // Bump counter.
            }
        }
        unset($_queue_id); // Housekeeping.

        return $counter;
    }
}
