<?php
/**
 * Upgrade Routines
 *
 * @package upgrader
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/plugin.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\upgrader'))
	{
		/**
		 * Upgrade Routines
		 *
		 * @package upgrader
		 * @since 14xxxx First documented version.
		 */
		class upgrader
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				if(version_compare($this->plugin->options['version'], $this->plugin->version, '>='))
					return; // Nothing to do; already @ latest version.

				$prev_version                     = $this->plugin->options['version'];
				$this->plugin->options['version'] = $this->plugin->version;
				update_option(__NAMESPACE__.'_options', $this->plugin->options);

				require_once dirname(__FILE__).'/version-specific-upgrade.php';
				new version_specific_upgrade($prev_version); // Run upgrader(s).

				$notice = __('<strong>%1$s</strong> detected a new version of itself. Recompiling... All done :-)', $this->plugin->text_domain);
				$this->plugin->enqueue_notice(sprintf($notice, esc_html($this->plugin->name)), '', TRUE); // Push this to the top.
			}
		}
	}
}