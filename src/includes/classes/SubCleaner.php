<?php
/**
 * Sub Cleaner.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Cleaner.
 *
 * @since 141111 First documented version.
 */
class SubCleaner extends AbsBase
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
     * @type int Total cleaned subs.
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

        if (isset($max_time)) { // Argument is set?
            $this->max_time = (integer) $max_time;
        } else { // This takes precedence.
            $this->max_time = (integer) $this->plugin->options['sub_cleaner_max_time'];
        }
        if ($this->max_time < 10) {
            $this->max_time = 10;
        }
        if ($this->max_time > 3600) {
            $this->max_time = 3600;
        }
        $this->cleaned = 0; // Initialize.

        $this->prepCronJob();
        $this->cleanNonexistentUsers();
        $this->maybeCleanUnconfirmedExpirations();
        $this->maybeCleanTrashedExpirations();
    }

    /**
     * Total subs cleaned.
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
     * Cleanup nonexistent users.
     *
     * @since 141111 First documented version.
     *
     * @note  This does NOT cover multisite `capabilities`.
     *    That's intentional. There is too much room for error in that case.
     *    We have `wpmu_delete_user` and `remove_user_from_blog` hooks for this anyway.
     *    We also have a `delete_user` hook too, for normal WP installs.
     *
     *    This routine is just here to help keep things extra tidy on normal WP installs.
     */
    protected function cleanNonexistentUsers()
    {
        if ($this->isOutOfTime()) {
            return; // Not enough time.
        }
        $user_ids = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->wp->users).'`';
        $user_ids = 'SELECT `ID` FROM ('.$user_ids.') AS `ID`'; // See: <http://jas.xyz/1I52mVE>

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.
               " WHERE `user_id` != '0' AND `user_id` NOT IN(".$user_ids.')';

        if (($ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->cleaned += $this->plugin->utils_sub->bulkDelete($ids, ['cleaning' => true]);
        }
    }

    /**
     * Cleanup unconfirmed subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybeCleanUnconfirmedExpirations()
    {
        if ($this->isOutOfTime()) {
            return; // Not enough time.
        }
        if (!$this->plugin->options['unconfirmed_expiration_time']) {
            return; // Not applicable; functionality disabled.
        }
        if (!($exp_time = strtotime('-'.$this->plugin->options['unconfirmed_expiration_time']))) {
            return; // Invalid time. Not compatible with `strtotime()`.
        }
        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `status` = 'unconfirmed'".
               " AND `last_update_time` < '".esc_sql($exp_time)."'";

        if (($ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->cleaned += $this->plugin->utils_sub->bulkDelete($ids, ['cleaning' => true]);
        }
    }

    /**
     * Cleanup trashed subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybeCleanTrashedExpirations()
    {
        if ($this->isOutOfTime()) {
            return; // Not enough time.
        }
        if (!$this->plugin->options['trashed_expiration_time']) {
            return; // Not applicable; functionality disabled.
        }
        if (!($exp_time = strtotime('-'.$this->plugin->options['trashed_expiration_time']))) {
            return; // Invalid time. Not compatible with `strtotime()`.
        }
        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `status` = 'trashed'".
               " AND `last_update_time` < '".esc_sql($exp_time)."'";

        if (($ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->cleaned += $this->plugin->utils_sub->bulkDelete($ids, ['cleaning' => true]);
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
