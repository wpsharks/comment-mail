<?php
/**
 * Upgrade Routines.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Upgrade Routines.
 *
 * @since 141111 First documented version.
 */
class Upgrader extends AbsBase
{
    /**
     * @type string Previous version.
     *
     * @since 141111 First documented version.
     */
    protected $prev_version;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->prev_version = $this->plugin->options['version'];

        $this->maybeUpgrade();
    }

    /**
     * Upgrade routine(s).
     *
     * @since 141111 First documented version.
     */
    protected function maybeUpgrade()
    {
        if (version_compare($this->prev_version, VERSION, '>=')) {
            return; // Nothing to do; already @ latest version.
        }
        $this->plugin->options['version'] = VERSION;
        update_option(GLOBAL_NS.'_options', $this->plugin->options);

        new UpgraderVs($this->prev_version); // Run version-specific upgrader(s).

        $this->plugin->enqueueNotice(// Notify site owner about this upgrade process.
            sprintf(__('<strong>%1$s&trade;</strong> was automatically recompiled upon detecting an upgrade to v%2$s. Your existing configuration remains :-)', 'comment-mail'), esc_html(NAME), esc_html(VERSION)),
            ['requires_cap' => $this->plugin->auto_recompile_cap, 'push_to_top' => true]
        );
    }
}
