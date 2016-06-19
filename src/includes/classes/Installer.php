<?php
/**
 * Install Routines.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Install Routines.
 *
 * @since 141111 First documented version.
 */
class Installer extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->plugin->setup();

        $this->createDbTables();
        $this->maybeEnqueueNotice();
        $this->setInstallTime();

        stcr_transition();
    }

    /**
     * Create DB tables.
     *
     * @since 141111 First documented version.
     *
     * @throws \exception If table creation fails.
     */
    protected function createDbTables()
    {
        foreach (scandir($tables_dir = dirname(__DIR__).'/tables') as $_sql_file) {
            if (substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file)) {
                $_sql_file_table = substr($_sql_file, 0, -4);
                $_sql_file_table = str_replace('-', '_', $_sql_file_table);
                $_sql_file_table = $this->plugin->utils_db->prefix().$_sql_file_table;

                $_sql = file_get_contents($tables_dir.'/'.$_sql_file);
                $_sql = str_replace('%%prefix%%', $this->plugin->utils_db->prefix(), $_sql);
                $_sql = $this->plugin->utils_db->fulltextCompat($_sql);

                if ($this->plugin->utils_db->wp->query($_sql) === false) { // Table creation failure?
                    throw new \exception(sprintf(__('DB table creation failure. Table: `%1$s`. SQL: `%2$s`.', 'comment-mail'), $_sql_file_table, $_sql));
                }
            }
        }
        unset($_sql_file, $_sql_file_table, $_sql); // Housekeeping.
    }

    /**
     * First time install displays notice.
     *
     * @since 141111 First documented version.
     */
    protected function maybeEnqueueNotice()
    {
        if (get_option(GLOBAL_NS.'_install_time')) {
            return; // Not applicable.
        }
        $notice_markup = $this->plugin->utils_fs->inlineIconSvg().
                         ' '.sprintf(
                             __('%1$s&trade; installed successfully! Please <a href="%2$s"><strong>click here to configure</strong></a> basic options.', 'comment-mail'),
                             esc_html(NAME),
                             esc_attr($this->plugin->utils_url->mainMenuPageOnly())
                         );
        $this->plugin->enqueueUserNotice($notice_markup); // A quick reminder to configure options.
    }

    /**
     * Update installation time.
     *
     * @since 141111 First documented version.
     */
    protected function setInstallTime()
    {
        if (!get_option(GLOBAL_NS.'_install_time')) {
            update_option(GLOBAL_NS.'_install_time', time());
        }
    }
}
