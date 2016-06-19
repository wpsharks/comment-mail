<?php
/**
 * Log Cleaner.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Log Cleaner.
 *
 * @since 141111 First documented version.
 */
class LogCleaner extends AbsBase
{
    /**
     * @type int Start time.
     *
     * @since 141111 First documented version.
     */
    protected $start_time;

    /**
     * @type int Max execution time.
     *
     * @since 141111 First documented version.
     */
    protected $max_time;

    /**
     * @type int Total cleaned entries.
     *
     * @since 141111 First documented version.
     */
    protected $cleaned;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int|null $max_time Max time (in seconds).
     *
     *    This cannot be less than `10` seconds.
     *    This cannot be greater than `3600` seconds.
     */
    public function __construct($max_time = null)
    {
        parent::__construct();

        $this->start_time = time();

        if (isset($max_time)) {
            $this->max_time = (integer) $max_time;
        } else {
            $this->max_time = (integer) $this->plugin->options['log_cleaner_max_time'];
        }
        if ($this->max_time < 10) {
            $this->max_time = 10;
        }
        if ($this->max_time > 3600) {
            $this->max_time = 3600;
        }
        $this->cleaned = 0; // Initialize.

        $this->prepCronJob();
        $this->maybeCleanSubEventLogEntries();
        $this->maybeCleanQueueEventLogEntries();
    }

    /**
     * Total log entries cleaned.
     *
     * @since 141111 First documented version.
     */
    public function cleaned()
    {
        return $this->cleaned;
    }

    /**
     * Prep CRON job.
     *
     * @since 141111 First documented version.
     */
    protected function prepCronJob()
    {
        ignore_user_abort(true);

        @set_time_limit($this->max_time); // Max time only (first).
        // Doing this first in case the time below exceeds an upper limit.
        // i.e. hosts may prevent this from being set higher than `$max_time`.

        // The following may not work, but we can try :-)
        @set_time_limit(min(3600, $this->max_time + 30)); // If possible.
    }

    /**
     * Cleanup sub. event log entries.
     *
     * @since 141111 First documented version.
     */
    protected function maybeCleanSubEventLogEntries()
    {
        if ($this->isOutOfTime()) {
            return; // Not enough time.
        }
        if (!$this->plugin->options['sub_event_log_expiration_time']) {
            return; // Not applicable; functionality disabled.
        }
        if (!($exp_time = strtotime('-'.$this->plugin->options['sub_event_log_expiration_time']))) {
            return; // Invalid time. Not compatible with `strtotime()`.
        }
        $sql = 'DELETE FROM `'.esc_sql($this->plugin->utils_db->prefix().'sub_event_log').'`'.
               " WHERE `time` < '".esc_sql($exp_time)."'";

        if ($this->plugin->utils_db->wp->query($sql) === false) {
            throw new \exception(__('Deletion failure.', 'comment-mail'));
        }
    }

    /**
     * Cleanup queue event log entries.
     *
     * @since 141111 First documented version.
     */
    protected function maybeCleanQueueEventLogEntries()
    {
        if ($this->isOutOfTime()) {
            return; // Not enough time.
        }
        if (!$this->plugin->options['queue_event_log_expiration_time']) {
            return; // Not applicable; functionality disabled.
        }
        if (!($exp_time = strtotime('-'.$this->plugin->options['queue_event_log_expiration_time']))) {
            return; // Invalid time. Not compatible with `strtotime()`.
        }
        $sql = 'DELETE FROM `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.
               " WHERE `time` < '".esc_sql($exp_time)."'";

        if ($this->plugin->utils_db->wp->query($sql) === false) {
            throw new \exception(__('Deletion failure.', 'comment-mail'));
        }
    }

    /**
     * Out of time yet?
     *
     * @since 141111 First documented version.
     *
     * @return bool TRUE if out of time.
     */
    protected function isOutOfTime()
    {
        if ((time() - $this->start_time) >= ($this->max_time - 5)) {
            return true; // Out of time.
        }
        return false; // Let's keep cleaning!
    }
}
