<?php
/**
 * Uninstall Routines.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Uninstall Routines.
 *
 * @since 141111 First documented version.
 */
class Uninstaller extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->plugin->enable_hooks) {
            return; // Not a good idea.
        }
        $this->plugin->setup(); // Setup.

        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return; // Disallow.
        }
        if (empty($GLOBALS[GLOBAL_NS.'_uninstalling'])) {
            return; // Expecting uninstall file.
        }
        if ($this->plugin->options['uninstall_safeguards_enable']) {
            return; // Nothing to do here; safeguarding.
        }
        if (!current_user_can($this->plugin->uninstall_cap)) {
            return; // Extra layer of security.
        }
        if (!current_user_can($this->plugin->cap)) {
            return; // Extra layer of security.
        }
        $this->deleteOptions();
        $this->deleteNotices();
        $this->deleteInstallTime();
        $this->deleteOptionKeys();
        $this->deleteTransientKeys();
        $this->deletePostMetaKeys();
        $this->deleteUserMetaKeys();
        $this->clearCronHooks();
        $this->dropDbTables();

        if (is_multisite() && is_array($child_blogs = wp_get_sites())) {
            foreach ($child_blogs as $_child_blog) {
                switch_to_blog($_child_blog['blog_id']);
                $this->deleteOptions();
                $this->deleteNotices();
                $this->deleteInstallTime();
                $this->deleteOptionKeys();
                $this->deleteTransientKeys();
                $this->deletePostMetaKeys();
                $this->deleteUserMetaKeys();
                $this->clearCronHooks();
                $this->dropDbTables();
                restore_current_blog();
            }
        }
        unset($_child_blog); // Housekeeping.
    }

    /**
     * Delete plugin-related options.
     *
     * @since 141111 First documented version.
     */
    protected function deleteOptions()
    {
        delete_option(GLOBAL_NS.'_options');
    }

    /**
     * Delete plugin-related notices.
     *
     * @since 141111 First documented version.
     */
    protected function deleteNotices()
    {
        delete_option(GLOBAL_NS.'_notices');
    }

    /**
     * Delete install time.
     *
     * @since 141111 First documented version.
     */
    protected function deleteInstallTime()
    {
        delete_option(GLOBAL_NS.'_install_time');
    }

    /**
     * Clear scheduled CRON hooks.
     *
     * @since 141111 First documented version.
     */
    protected function clearCronHooks()
    {
        $this->plugin->resetCronSetup();
    }

    /**
     * Delete option keys.
     *
     * @since 141111 First documented version.
     */
    protected function deleteOptionKeys()
    {
        $like = // e.g. Delete all keys LIKE `%comment\_mail%`.
            '%'.$this->plugin->utils_db->wp->esc_like(GLOBAL_NS).'%';

        $sql = // Removes any other option keys for this plugin.
            'DELETE FROM `'.esc_sql($this->plugin->utils_db->wp->options).'`'.
            " WHERE `option_name` LIKE '".esc_sql($like)."'";

        $this->plugin->utils_db->wp->query($sql);
    }

    /**
     * Delete transient keys.
     *
     * @since 141111 First documented version.
     */
    protected function deleteTransientKeys()
    {
        $like1 = // e.g. Delete all keys LIKE `%\_transient\_cmtmail\_%`.
            '%'.$this->plugin->utils_db->wp->esc_like('_transient_'.TRANSIENT_PREFIX).'%';

        $like2 = // e.g. Delete all keys LIKE `%\_transient\_timeout\_cmtmail\_%`.
            '%'.$this->plugin->utils_db->wp->esc_like('_transient_timeout_'.TRANSIENT_PREFIX).'%';

        $sql = // This will remove our transients/timeouts.
            'DELETE FROM `'.esc_sql($this->plugin->utils_db->wp->options).'`'.
            " WHERE `option_name` LIKE '".esc_sql($like1)."' OR `option_name` LIKE '".esc_sql($like2)."'";

        $this->plugin->utils_db->wp->query($sql);
    }

    /**
     * Delete post meta keys.
     *
     * @since 141111 First documented version.
     */
    protected function deletePostMetaKeys()
    {
        $like = // e.g. Delete all keys LIKE `%comment\_mail%`.
            '%'.$this->plugin->utils_db->wp->esc_like(GLOBAL_NS).'%';

        $sql = // This will remove our StCR import history also.
            'DELETE FROM `'.esc_sql($this->plugin->utils_db->wp->postmeta).'`'.
            " WHERE `meta_key` LIKE '".esc_sql($like)."'";

        $this->plugin->utils_db->wp->query($sql);
    }

    /**
     * Delete user meta keys.
     *
     * @since 141111 First documented version.
     */
    protected function deleteUserMetaKeys()
    {
        if (is_multisite()) { // Prefixed keys on networks.
            $ms_prefix = $this->plugin->utils_db->wp->prefix;

            $like = $this->plugin->utils_db->wp->esc_like($ms_prefix).
                    // e.g. Delete all keys LIKE `wp\_5\_%comment\_mail%`.
                    // Or, on the main site it might be: `wp\_%comment\_mail%`.
                    '%'.$this->plugin->utils_db->wp->esc_like(GLOBAL_NS).'%';

            $sql = // This will delete all screen options too.
                'DELETE FROM `'.esc_sql($this->plugin->utils_db->wp->usermeta).'`'.
                " WHERE `meta_key` LIKE '".esc_sql($like)."'";
        } else { // No special considerations; there is only one blog.
            $like = // e.g. Delete all keys LIKE `%comment\_mail%`.
                '%'.$this->plugin->utils_db->wp->esc_like(GLOBAL_NS).'%';

            $sql = // This will delete all screen options too.
                'DELETE FROM `'.esc_sql($this->plugin->utils_db->wp->usermeta).'`'.
                " WHERE `meta_key` LIKE '".esc_sql($like)."'";
        }
        $this->plugin->utils_db->wp->query($sql);
    }

    /**
     * Uninstall DB tables.
     *
     * @since 141111 First documented version.
     */
    protected function dropDbTables()
    {
        foreach (scandir($tables_dir = dirname(__DIR__).'/tables') as $_sql_file) {
            if (substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file)) {
                $_sql_file_table = substr($_sql_file, 0, -4);
                $_sql_file_table = str_replace('-', '_', $_sql_file_table);
                $_sql_file_table = $this->plugin->utils_db->prefix().$_sql_file_table;

                if (!$this->plugin->utils_db->wp->query('DROP TABLE IF EXISTS `'.esc_sql($_sql_file_table).'`')) {
                    throw new \exception(sprintf(__('DB table deletion failure: `%1$s`.', 'comment-mail'), $_sql_file_table));
                }
            }
        }
        unset($_sql_file, $_sql_file_table); // Housekeeping.
    }
}
