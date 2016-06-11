<?php
/**
 * User Register.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * User Register.
 *
 * @since 141111 First documented version.
 */
class UserRegister extends AbsBase
{
    /**
     * @type \WP_User|null
     *
     * @since 141111 First documented version.
     */
    protected $user;

    /**
     * Class constructor.
     *
     * @param int|string $user_id User ID.
     *
     * @since 141111 First documented version.
     */
    public function __construct($user_id)
    {
        parent::__construct();

        if (($user_id = (integer) $user_id)) {
            $this->user = new \WP_User($user_id);
        }
        $this->maybeUpdateSubs();
    }

    /**
     * Update subscriptions; set user ID.
     *
     * @since 141111 First documented version.
     *
     * @throws \exception If a deletion failure occurs.
     */
    protected function maybeUpdateSubs()
    {
        if (!$this->user) {
            return; // Not possible.
        }
        if (!$this->user->ID) {
            return; // Not possible.
        }
        if (!$this->user->user_email) {
            return; // Not possible.
        }
        # Update the subs table; i.e. associate w/ this user where applicable.

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `email` = '".esc_sql($this->user->user_email)."'".
               " AND `user_id` = '0'"; // Not yet associated w/ a user ID.

        if (($sub_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            foreach ($sub_ids as $_sub_id) { // Update the `user_id` on each of these.
                new SubUpdater(['ID' => $_sub_id, 'user_id' => $this->user->ID]);
            }
        }
        unset($_sub_id); // Housekeeping.

        # Update event logs too; i.e. associate w/ this user where applicable.

        $sql = 'UPDATE `'.esc_sql($this->plugin->utils_db->prefix().'sub_event_log').'`'.
               " SET `user_id` = '".esc_sql($this->user->ID)."'".// Update.

               " WHERE `email` = '".esc_sql($this->user->user_email)."'".
               " AND `user_id` = '0'"; // Not yet associated w/ a user ID.

        if ($this->plugin->utils_db->wp->query($sql) === false) {
            throw new \exception(__('Update failure.', 'comment-mail'));
        }
        $sql = 'UPDATE `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.
               " SET `user_id` = '".esc_sql($this->user->ID)."'".// Update.

               " WHERE `email` = '".esc_sql($this->user->user_email)."'".
               " AND `user_id` = '0'"; // Not yet associated w/ a user ID.

        if ($this->plugin->utils_db->wp->query($sql) === false) {
            throw new \exception(__('Update failure.', 'comment-mail'));
        }
    }
}
