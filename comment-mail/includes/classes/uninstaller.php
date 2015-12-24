<?php
/**
 * Uninstall Routines
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\uninstaller'))
	{
		/**
		 * Uninstall Routines
		 *
		 * @since 141111 First documented version.
		 */
		class uninstaller extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				if($this->plugin->enable_hooks)
					return; // Not a good idea.

				$this->plugin->setup(); // Setup.

				if(!defined('WP_UNINSTALL_PLUGIN'))
					return; // Disallow.

				if(empty($GLOBALS[__NAMESPACE__.'_uninstalling']))
					return; // Expecting uninstall file.

				if($this->plugin->options['uninstall_safeguards_enable'])
					return; // Nothing to do here; safeguarding.

				if(!current_user_can($this->plugin->uninstall_cap))
					return; // Extra layer of security.

				if(!current_user_can($this->plugin->cap))
					return; // Extra layer of security.

				$this->delete_options();
				$this->delete_notices();
				$this->delete_install_time();
				$this->delete_option_keys();
				$this->delete_transient_keys();
				$this->delete_post_meta_keys();
				$this->delete_user_meta_keys();
				$this->clear_cron_hooks();
				$this->drop_db_tables();

				if(is_multisite() && is_array($child_blogs = wp_get_sites()))
					foreach($child_blogs as $_child_blog)
					{
						switch_to_blog($_child_blog['blog_id']);
						$this->delete_options();
						$this->delete_notices();
						$this->delete_install_time();
						$this->delete_option_keys();
						$this->delete_transient_keys();
						$this->delete_post_meta_keys();
						$this->delete_user_meta_keys();
						$this->clear_cron_hooks();
						$this->drop_db_tables();
						restore_current_blog();
					}
					unset($_child_blog); // Housekeeping.
			}

			/**
			 * Delete plugin-related options.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_options()
			{
				delete_option(__NAMESPACE__.'_options');
			}

			/**
			 * Delete plugin-related notices.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_notices()
			{
				delete_option(__NAMESPACE__.'_notices');
			}

			/**
			 * Delete install time.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_install_time()
			{
				delete_option(__NAMESPACE__.'_install_time');
			}

			/**
			 * Clear scheduled CRON hooks.
			 *
			 * @since 141111 First documented version.
			 */
			protected function clear_cron_hooks()
			{
				wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_queue_processor');
				wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_sub_cleaner');
				wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_log_cleaner');
			}

			/**
			 * Delete option keys.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_option_keys()
			{
				$like = // e.g. Delete all keys LIKE `%comment\_mail%`.
					'%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

				$sql = // Removes any other option keys for this plugin.
					"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->options)."`".
					" WHERE `option_name` LIKE '".esc_sql($like)."'";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Delete transient keys.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_transient_keys()
			{
				$like1 = // e.g. Delete all keys LIKE `%\_transient\_cmtmail\_%`.
					'%'.$this->plugin->utils_db->wp->esc_like('_transient_'.$this->plugin->transient_prefix).'%';

				$like2 = // e.g. Delete all keys LIKE `%\_transient\_timeout\_cmtmail\_%`.
					'%'.$this->plugin->utils_db->wp->esc_like('_transient_timeout_'.$this->plugin->transient_prefix).'%';

				$sql = // This will remove our transients/timeouts.
					"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->options)."`".
					" WHERE `option_name` LIKE '".esc_sql($like1)."' OR `option_name` LIKE '".esc_sql($like2)."'";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Delete post meta keys.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_post_meta_keys()
			{
				$like = // e.g. Delete all keys LIKE `%comment\_mail%`.
					'%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

				$sql = // This will remove our StCR import history also.
					"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->postmeta)."`".
					" WHERE `meta_key` LIKE '".esc_sql($like)."'";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Delete user meta keys.
			 *
			 * @since 141111 First documented version.
			 */
			protected function delete_user_meta_keys()
			{
				if(is_multisite()) // Prefixed keys on networks.
				{
					$ms_prefix = $this->plugin->utils_db->wp->prefix;

					$like = $this->plugin->utils_db->wp->esc_like($ms_prefix).
					        // e.g. Delete all keys LIKE `wp\_5\_%comment\_mail%`.
					        // Or, on the main site it might be: `wp\_%comment\_mail%`.
					        '%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

					$sql = // This will delete all screen options too.
						"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->usermeta)."`".
						" WHERE `meta_key` LIKE '".esc_sql($like)."'";
				}
				else // No special considerations; there is only one blog.
				{
					$like = // e.g. Delete all keys LIKE `%comment\_mail%`.
						'%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

					$sql = // This will delete all screen options too.
						"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->usermeta)."`".
						" WHERE `meta_key` LIKE '".esc_sql($like)."'";
				}
				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Uninstall DB tables.
			 *
			 * @since 141111 First documented version.
			 */
			protected function drop_db_tables()
			{
				foreach(scandir($tables_dir = dirname(dirname(__FILE__)).'/tables') as $_sql_file)
					if(substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file))
					{
						$_sql_file_table = substr($_sql_file, 0, -4);
						$_sql_file_table = str_replace('-', '_', $_sql_file_table);
						$_sql_file_table = $this->plugin->utils_db->prefix().$_sql_file_table;

						if(!$this->plugin->utils_db->wp->query('DROP TABLE IF EXISTS `'.esc_sql($_sql_file_table).'`'))
							throw new \exception(sprintf(__('DB table deletion failure: `%1$s`.', $this->plugin->text_domain), $_sql_file_table));
					}
				unset($_sql_file, $_sql_file_table); // Housekeeping.
			}
		}
	}
}
