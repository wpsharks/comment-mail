<?php
/**
 * User Columns.
 *
 * @since     151224 Adding custom user columns.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * User Columns.
 *
 * @since 151224 Adding custom user columns.
 */
class UserColumns extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 151224 Adding custom user columns.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Filter (and add) user columns.
     *
     * @since 151224 Adding custom user columns.
     *
     * @param array $columns Existing columns.
     *
     * @return array Filtered columns.
     */
    public function filter(array $columns)
    {
        if (!$this->plugin->options['enable']) {
            return $columns; // Not applicable.
        }
        if ($this->plugin->options['sso_enable']) {
            $columns[GLOBAL_NS.'_sso_services'] = __('SSO Service', 'comment-mail');
        }
        return $columns;
    }

    /**
     * Maybe fill custom user columns.
     *
     * @since 151224 Adding custom user columns.
     *
     * @param mixed      $value   Existing column value.
     * @param string     $column  Column name.
     * @param int|string $user_id User ID.
     *
     * @return mixed Filtered value.
     */
    public function maybeFill($value, $column, $user_id)
    {
        if ($column === GLOBAL_NS.'_sso_services') {
            $user_sso_services = get_user_option(GLOBAL_NS.'_sso_services', $user_id);
            $user_sso_services = is_array($user_sso_services) ? $user_sso_services : [];
            $value             = $user_sso_services ? implode(', ', $user_sso_services) : '—';
        }
        return $value;
    }
}
