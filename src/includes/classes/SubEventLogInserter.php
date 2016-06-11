<?php
/**
 * Sub. Event Log Inserter.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub. Event Log Inserter.
 *
 * @since 141111 First documented version.
 */
class SubEventLogInserter extends AbsBase
{
    /**
     * @type array Log entry data.
     *
     * @since 141111 First documented version.
     */
    protected $entry;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $entry  Log entry data; w/ sub. now.
     * @param array $before Log entry data; w/ sub. before.
     *                      Not applicable w/ insertions.
     *
     * @throws \exception If `$entry` is missing required keys.
     */
    public function __construct(array $entry, array $before = [])
    {
        parent::__construct();

        $defaults = [
            'sub_id' => 0,
            'key'    => '',

            'oby_sub_id' => 0,

            'user_id'    => 0,
            'post_id'    => 0,
            'comment_id' => 0,
            'deliver'    => '',

            'fname' => '',
            'lname' => '',
            'email' => '',

            'ip'      => '',
            'region'  => '',
            'country' => '',

            'status' => '',

            'event'          => '',
            'user_initiated' => 0,

            'time' => time(),

            /* ----------------- */

            'key_before' => '',

            'user_id_before'    => 0,
            'post_id_before'    => 0,
            'comment_id_before' => 0,
            'deliver_before'    => '',

            'fname_before' => '',
            'lname_before' => '',
            'email_before' => '',

            'ip_before'      => '',
            'region_before'  => '',
            'country_before' => '',

            'status_before' => '',
        ];
        # Sub ID auto-fill from subscription data.

        if (empty($entry['sub_id']) && !empty($entry['ID'])) {
            $entry['sub_id'] = $entry['ID'];
        }
        # IP, region, country; auto-fill from subscription data.

        foreach (['ip', 'region', 'country'] as $_key) {
            if (empty($entry[$_key])) { // Coalesce; giving precedence to the `last_` value.
                $entry[$_key] = $this->notEmptyCoalesce($entry['last_'.$_key], $entry['insertion_'.$_key]);
            }
            if (empty($before[$_key])) { // Coalesce; giving precedence to the `last_` value.
                $before[$_key] = $this->notEmptyCoalesce($before['last_'.$_key], $before['insertion_'.$_key]);
            }
        }
        unset($_key); // Just a little housekeeping.

        # Auto-suffix subscription data from `_before`.

        foreach ($before as $_key => $_value) {
            $before[$_key.'_before'] = $_value;
            unset($before[$_key]); // Unset.
        }
        unset($_key, $_value); // Housekeeping.

        $this->entry = array_merge($defaults, $entry, $before);
        $this->entry = array_intersect_key($this->entry, $defaults);
        $this->entry = $this->plugin->utils_db->typifyDeep($this->entry);

        $this->maybeInsert(); // Record event; if applicable.
    }

    /**
     * Record event; if applicable.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInsert()
    {
        if (!$this->entry['sub_id']) {
            return; // Not applicable.
        }
        if (!$this->entry['post_id']) {
            return; // Not applicable.
        }
        if (!$this->entry['deliver']) {
            return; // Not applicable.
        }
        if (!$this->entry['email']) {
            return; // Not applicable.
        }
        if (!$this->entry['status']) {
            return; // Not applicable.
        }
        if (!$this->entry['event']) {
            return; // Not applicable.
        }
        if (!$this->entry['time']) {
            return; // Not applicable.
        }
        if (!$this->plugin->utils_db->wp->insert($this->plugin->utils_db->prefix().'sub_event_log', $this->entry)) {
            throw new \exception(__('Insertion failure.', 'comment-mail'));
        }
    }
}
