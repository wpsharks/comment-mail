<?php
/**
 * User Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * User Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsUser extends AbsBase
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
     * Screen option value.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Screen $screen  A screen object instance.
     * @param string     $option  The screen option to get.
     * @param int        $user_id A specific user ID. Defaults to `NULL`.
     *                            A `NULL` value indicates the current user.
     *
     * @return mixed The screen option value; only if not empty; and only it has a valid data type.
     *               If empty, or not the same data type as the default value; returns the default value.
     */
    public function screenOption(\WP_Screen $screen, $option, $user_id = null)
    {
        $user_id       = $this->issetOr($user_id, (integer) get_current_user_id(), 'integer');
        $value         = get_user_meta($user_id, $screen->get_option($option, 'option'), true);
        $default_value = $screen->get_option($option, 'default');

        if (!$value || gettype($value) !== gettype($default_value)) {
            $value = $default_value;
        }
        return $value;
    }

    /**
     * Is the current user?
     *
     * @since 141111 First documented version.
     *
     * @param \WP_User|int A        user to check; object or ID.
     * @param bool $allow_0 Allow `0`-based checks also?
     *
     * @return bool `TRUE` if `$user` is the current user.
     */
    public function isCurrent($user, $allow_0 = false)
    {
        if (is_integer($user)) {
            $user_id = (integer) $user;
        } elseif ($user instanceof \WP_User) {
            $user_id = (integer) $user->ID;
        }
        if (!isset($user_id)) {
            return false; // Not possible.
        }
        return ($user_id || ($user_id === 0 && $allow_0))
               && get_current_user_id() === $user_id;
    }

    /**
     * Email exists on this blog?
     *
     * @since 141111 First documented version.
     *
     * @param string $email    The email address to check.
     * @param bool   $no_cache Refresh a previously cached value?
     *
     * @return bool `TRUE` if `$email` exists on current blog.
     */
    public function emailExistsOnBlog($email, $no_cache = false)
    {
        if (!($email = trim(strtolower((string) $email)))) {
            return false; // Not possible.
        }
        $blog_id    = get_current_blog_id();
        $cache_keys = compact('email', 'blog_id');

        if (!is_null($exists = &$this->cacheKey(__FUNCTION__, $cache_keys)) && !$no_cache) {
            return $exists; // Already cached this.
        }
        if (!($user_id = email_exists($email))) {
            return $exists = false; // Not on any blog.
        }
        if (!($user = new \WP_User($user_id)) || !$user->exists()) {
            return $exists = false; // Not on any blog.
        }
        return $exists = !is_multisite() || !empty($user->roles);
    }

    /**
     * Can users register?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if users can register on the current blog.
     */
    public function canRegister()
    {
        if (is_multisite()) { // Check network options for this.
            return in_array(get_site_option('registration'), ['all', 'user'], true);
        }
        return (boolean) get_option('users_can_register');
    }
}
