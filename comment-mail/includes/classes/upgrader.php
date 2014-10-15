<?php
/**
 * Upgrade Routines
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\upgrader'))
	{
		/**
		 * Upgrade Routines
		 *
		 * @since 14xxxx First documented version.
		 */
		class upgrader extends abstract_base
		{
			/**
			 * @var string Current version.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $current_version;

			/**
			 * @var string Previous version.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $prev_version;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->current_version = $this->plugin->options['version'];
				$this->prev_version    = $this->plugin->options['version'];

				$this->maybe_upgrade();
			}

			/**
			 * Upgrade routine(s).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_upgrade()
			{
				if(version_compare($this->current_version, $this->plugin->version, '>='))
					return; // Nothing to do; already @ latest version.

				$this->plugin->options['version'] // Update.
					= $this->current_version = $this->plugin->version;
				update_option(__NAMESPACE__.'_options', $this->plugin->options);

				new upgrader_vs($this->prev_version); // Run version-specific upgrader(s).

				$this->plugin->enqueue_notice // Notify site owner about this upgrade process.
				(
					sprintf(__('<strong>%1$s</strong> detected a new version of itself. Recompiling... All done :-)',
					           $this->plugin->text_domain), esc_html($this->plugin->name)),

					array('requires_cap' => 'manage_network_plugins', 'push_to_top' => TRUE)
				);
			}
		}
	}
}