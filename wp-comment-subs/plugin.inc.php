<?php
/**
 * WP Comment Subs
 *
 * @package wp_comment_subs\plugin
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
namespace wp_comment_subs
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\plugin'))
	{
		/**
		 * WP Comment Subs
		 *
		 * @package wp_comment_subs\plugin
		 * @since 14xxxx First documented version.
		 */
		class plugin
		{
			/**
			 * Stub `__FILE__` location.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Current `__FILE__` from the stub.
			 */
			public $file = '';

			/**
			 * Identifies pro version.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var boolean `TRUE` for pro version.
			 */
			public $is_pro = FALSE;

			/**
			 * Version string in YYMMDD[+build] format.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Current version of the software.
			 */
			public $version = '14xxxx';

			/**
			 * Text domain for translations; based on `__NAMESPACE__`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Defined by class constructor; for translations.
			 */
			public $text_domain = '';

			/**
			 * An instance-based cache for class members.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array An instance-based cache for class members.
			 */
			public $cache = array();

			/**
			 * A global static cache for class members.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array Global static cache for class members.
			 */
			public static $static = array();

			/**
			 * An array of all default option values.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array Default options array; set by constructor.
			 */
			public $default_options = array();

			/**
			 * Configured option values.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array Options configured by site owner; set by constructor.
			 */
			public $options = array();

			/**
			 * General capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string WordPress capability required to administer.
			 */
			public $cap = '';

			/**
			 * Uninstall capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string WordPress capability required to completely uninstall/delete.
			 */
			public $uninstall_cap = '';

			/**
			 * Used by the plugin's uninstall handler.
			 *
			 * @since 14xxxx Adding uninstall handler.
			 *
			 * @var boolean If FALSE, run without any hooks.
			 */
			public $enable_hooks = TRUE;

			/**
			 * Plugin constructor.
			 *
			 * @param boolean $enable_hooks Defaults to a TRUE value.
			 *    If FALSE, setup runs but without adding any hooks.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($enable_hooks = TRUE)
			{
				$this->enable_hooks = (boolean)$enable_hooks;
				$this->file         = preg_replace('/\.inc\.php$/', '.php', __FILE__);

				if(!$this->enable_hooks) return; // All done in this case.

				add_action('after_setup_theme', array($this, 'setup'));
				register_activation_hook($this->file, array($this, 'activate'));
				register_deactivation_hook($this->file, array($this, 'deactivate'));
			}

			/**
			 * Setup the plugin.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function setup()
			{
				if($this->enable_hooks) // Hooks enabled?
					do_action('before__'.__METHOD__, get_defined_vars());

				load_plugin_textdomain($this->text_domain);

				$this->default_options = array(
					'version'               => $this->version,

					'crons_setup'           => '0', // `0` or timestamp.

					'enable'                => '0', // `0|1`.

					'uninstall_on_deletion' => '0' // `0|1`.
				); // Default options are merged with those defined by the site owner.
				$options               = (is_array($options = get_option(__NAMESPACE__.'_options'))) ? $options : array();
				$this->default_options = apply_filters(__METHOD__.'__default_options', $this->default_options, get_defined_vars());
				$this->options         = array_merge($this->default_options, $options); // This considers old options also.
				$this->options         = apply_filters(__METHOD__.'__options', $this->options, get_defined_vars());

				$this->cap           = apply_filters(__METHOD__.'__cap', 'activate_plugins');
				$this->uninstall_cap = apply_filters(__METHOD__.'__uninstall_cap', 'delete_plugins');

				if(!$this->enable_hooks) return; // Stop here; setup without hooks.

				add_action('wp_loaded', array($this, 'actions'));

				add_action('admin_init', array($this, 'check_version'));

				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

				add_action('all_admin_notices', array($this, 'all_admin_notices'));
				add_action('all_admin_notices', array($this, 'all_admin_errors'));

				add_action('admin_menu', array($this, 'add_menu_pages'));

				add_filter('plugin_action_links_'.plugin_basename($this->file), array($this, 'add_settings_link'));

				add_filter('cron_schedules', array($this, 'extend_cron_schedules'));

				if((integer)$this->options['crons_setup'] < 1382523750)
				{
					//wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_xxx');
					//wp_schedule_event(time() + 60, 'daily', '_cron_'.__NAMESPACE__.'_xxx');

					$this->options['crons_setup'] = (string)time();
					update_option(__NAMESPACE__.'_options', $this->options); // Blog-specific.
				}
				//add_action('_cron_'.__NAMESPACE__.'_cleanup', array($this, 'xxx'));

				do_action('after__'.__METHOD__, get_defined_vars());
				do_action(__METHOD__.'_complete', get_defined_vars());
			}

			/**
			 * WordPress database instance.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return \wpdb Reference for IDEs.
			 */
			public function wpdb() // Shortcut for other routines.
			{
				return $GLOBALS['wpdb'];
			}

			/**
			 * Plugin activation hook.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to {@link \register_activation_hook()}
			 */
			public function activate()
			{
				$this->setup(); // Setup routines.

				if(!$this->options['enable'])
					return; // Nothing to do.
			}

			/**
			 * Check current plugin version that installed in WP.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `admin_init` hook.
			 */
			public function check_version()
			{
				$current_version = $prev_version = $this->options['version'];
				if(version_compare($current_version, $this->version, '>='))
					return; // Nothing to do; we've already upgraded them.

				$current_version = $this->options['version'] = $this->version;
				update_option(__NAMESPACE__.'_options', $this->options); // Updates version.

				require_once dirname(__FILE__).'/includes/version-specific-upgrade.php';
				new version_specific_upgrade($prev_version);

				$this->enqueue_notice(__('<strong>WP Comment Subs:</strong> detected a new version of itself. Recompiling w/ latest version... all done :-)', $this->text_domain), '', TRUE);
			}

			/**
			 * Plugin deactivation hook.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to {@link \register_deactivation_hook()}
			 */
			public function deactivate()
			{
			}

			/**
			 * Plugin uninstall hook.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to {@link \register_uninstall_hook()} ~ via {@link uninstall()}
			 */
			public function uninstall()
			{
				if(!current_user_can($this->uninstall_cap))
					return; // Extra layer of security.

				if(!class_exists('\\'.__NAMESPACE__.'\\uninstall'))
					return; // Expecting the uninstall class.

				if(!defined('WP_UNINSTALL_PLUGIN'))
					return; // Disallow.

				if(!$this->options['uninstall_on_deletion'])
					return; // Nothing to do here.

				delete_option(__NAMESPACE__.'_options');
				delete_option(__NAMESPACE__.'_notices');
				delete_option(__NAMESPACE__.'_errors');
				//
				// wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_xxx');
			}

			/**
			 * Current request is for a pro version preview?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if the current request is for a pro preview.
			 */
			public function is_pro_preview()
			{
				static $is;
				if(isset($is)) return $is;

				if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
					return ($is = TRUE);

				return ($is = FALSE);
			}

			/**
			 * URL to a plugin file.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $file Optional file path; relative to plugin directory.
			 * @param string $scheme Optional URL scheme; defaults to the current scheme.
			 *
			 * @return string URL to plugin directory; or to the specified `$file` if applicable.
			 */
			public function url($file = '', $scheme = '')
			{
				if(!isset(static::$static[__FUNCTION__]['plugin_dir']))
					static::$static[__FUNCTION__]['plugin_dir'] = rtrim(plugin_dir_url($this->file), '/');
				$plugin_dir =& static::$static[__FUNCTION__]['plugin_dir'];

				$url = $plugin_dir.(string)$file;

				if($scheme) // A specific URL scheme?
					$url = set_url_scheme($url, (string)$scheme);

				return apply_filters(__METHOD__, $url, get_defined_vars());
			}

			/**
			 * Plugin action handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `wp_loaded` hook.
			 */
			public function actions()
			{
				if(!empty($_REQUEST[__NAMESPACE__]))
					require_once dirname(__FILE__).'/includes/actions.php';
			}

			/**
			 * Adds CSS for administrative menu pages.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` hook.
			 */
			public function enqueue_admin_styles()
			{
				if(empty($_GET['page']) || strpos($_GET['page'], __NAMESPACE__) !== 0)
					return; // Nothing to do; NOT a plugin page in the administrative area.

				$deps = array(); // Plugin dependencies.

				wp_enqueue_style(__NAMESPACE__, $this->url('/client-s/css/menu-pages.min.css'), $deps, $this->version, 'all');
			}

			/**
			 * Adds JS for administrative menu pages.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` hook.
			 */
			public function enqueue_admin_scripts()
			{
				if(empty($_GET['page']) || strpos($_GET['page'], __NAMESPACE__) !== 0)
					return; // Nothing to do; NOT a plugin page in the administrative area.

				$deps = array('jquery'); // Plugin dependencies.

				wp_enqueue_script(__NAMESPACE__, $this->url('/client-s/js/menu-pages.min.js'), $deps, $this->version, TRUE);
			}

			/**
			 * Creates admin menu pages.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `admin_menu` hook.
			 */
			public function add_menu_pages()
			{
				add_menu_page(__('WP Comment Subs', $this->text_domain), __('WP Comment Subs', $this->text_domain),
				              $this->cap, __NAMESPACE__, array($this, 'menu_page_options'),
				              $this->url('/client-s/images/menu-icon.png'));
			}

			/**
			 * Adds link(s) to plugin row on the WP plugins page.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `plugin_action_links_'.plugin_basename($this->file)` filter.
			 *
			 * @param array $links An array of the existing links provided by WordPress.
			 *
			 * @return array Revised array of links.
			 */
			public function add_settings_link($links)
			{
				$links[] = '<a href="options-general.php?page='.urlencode(__NAMESPACE__).'">'.__('Settings', $this->text_domain).'</a>';
				$links[] = '<br/><a href="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, __NAMESPACE__.'_pro_preview' => '1')), self_admin_url('/admin.php'))).'">'.__('Preview Pro Features', $this->text_domain).'</a>';
				$links[] = '<a href="'.esc_attr('http://www.websharks-inc.com/product/'.str_replace('_', '-', __NAMESPACE__).'/').'" target="_blank">'.__('Upgrade', $this->text_domain).'</a>';

				return apply_filters(__METHOD__, $links, get_defined_vars());
			}

			/**
			 * Loads the admin menu page options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_options()
			{
				require_once dirname(__FILE__).'/includes/menu-pages.php';
				$menu_pages = new menu_pages();
				$menu_pages->options();
			}

			/**
			 * Render admin notices; across all admin dashboard views.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `all_admin_notices` hook.
			 */
			public function all_admin_notices()
			{
				if(($notices = (is_array($notices = get_option(__NAMESPACE__.'_notices'))) ? $notices : array()))
				{
					$notices = $updated_notices = array_unique($notices); // De-dupe.

					foreach(array_keys($updated_notices) as $_key) if(strpos($_key, 'persistent-') !== 0)
						unset($updated_notices[$_key]); // Leave persistent notices; ditch others.
					unset($_key); // Housekeeping after updating notices.

					update_option(__NAMESPACE__.'_notices', $updated_notices);
				}
				if(current_user_can($this->cap)) foreach($notices as $_key => $_notice)
				{
					$_dismiss = ''; // Initialize empty string; e.g. reset value on each pass.
					if(strpos($_key, 'persistent-') === 0) // A dismissal link is needed in this case?
					{
						$_dismiss_css = 'display:inline-block; float:right; margin:0 0 0 15px; text-decoration:none; font-weight:bold;';
						$_dismiss     = add_query_arg(urlencode_deep(array(__NAMESPACE__ => array('dismiss_notice' => array('key' => $_key)), '_wpnonce' => wp_create_nonce())));
						$_dismiss     = '<a style="'.esc_attr($_dismiss_css).'" href="'.esc_attr($_dismiss).'">'.__('dismiss &times;', $this->text_domain).'</a>';
					}
					echo apply_filters(__METHOD__.'__notice', '<div class="updated"><p>'.$_notice.$_dismiss.'</p></div>', get_defined_vars());
				}
				unset($_key, $_notice, $_dismiss_css, $_dismiss); // Housekeeping.
			}

			/**
			 * Enqueue an administrative notice.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $notice HTML markup containing the notice itself.
			 *
			 * @param string  $persistent_key Optional. A unique key which identifies a particular type of persistent notice.
			 *    This defaults to an empty string. If this is passed, the notice is persistent; i.e. it continues to be displayed until dismissed by the site owner.
			 *
			 * @param boolean $push_to_top Optional. Defaults to a `FALSE` value.
			 *    If `TRUE`, the notice is pushed to the top of the stack; i.e. displayed above any others.
			 */
			public function enqueue_notice($notice, $persistent_key = '', $push_to_top = FALSE)
			{
				$notice         = (string)$notice;
				$persistent_key = (string)$persistent_key;

				$notices = get_option(__NAMESPACE__.'_notices');
				if(!is_array($notices)) $notices = array();

				if($persistent_key) // A persistent notice?
				{
					if(strpos($persistent_key, 'persistent-') !== 0)
						$persistent_key = 'persistent-'.$persistent_key;

					if($push_to_top) // Push this notice to the top?
						$notices = array($persistent_key => $notice) + $notices;
					else $notices[$persistent_key] = $notice;
				}
				else if($push_to_top) // Push to the top?
					array_unshift($notices, $notice);

				else $notices[] = $notice; // Default behavior.

				update_option(__NAMESPACE__.'_notices', $notices);
			}

			/**
			 * Render admin errors; across all admin dashboard views.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `all_admin_notices` hook.
			 */
			public function all_admin_errors()
			{
				if(($errors = (is_array($errors = get_option(__NAMESPACE__.'_errors'))) ? $errors : array()))
				{
					$errors = $updated_errors = array_unique($errors); // De-dupe.

					foreach(array_keys($updated_errors) as $_key) if(strpos($_key, 'persistent-') !== 0)
						unset($updated_errors[$_key]); // Leave persistent errors; ditch others.
					unset($_key); // Housekeeping after updating notices.

					update_option(__NAMESPACE__.'_errors', $updated_errors);
				}
				if(current_user_can($this->cap)) foreach($errors as $_key => $_error)
				{
					$_dismiss = ''; // Initialize empty string; e.g. reset value on each pass.
					if(strpos($_key, 'persistent-') === 0) // A dismissal link is needed in this case?
					{
						$_dismiss_css = 'display:inline-block; float:right; margin:0 0 0 15px; text-decoration:none; font-weight:bold;';
						$_dismiss     = add_query_arg(urlencode_deep(array(__NAMESPACE__ => array('dismiss_error' => array('key' => $_key)), '_wpnonce' => wp_create_nonce())));
						$_dismiss     = '<a style="'.esc_attr($_dismiss_css).'" href="'.esc_attr($_dismiss).'">'.__('dismiss &times;', $this->text_domain).'</a>';
					}
					echo apply_filters(__METHOD__.'__error', '<div class="error"><p>'.$_error.$_dismiss.'</p></div>', get_defined_vars());
				}
				unset($_key, $_error, $_dismiss_css, $_dismiss); // Housekeeping.
			}

			/**
			 * Enqueue an administrative error.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $error HTML markup containing the error itself.
			 *
			 * @param string  $persistent_key Optional. A unique key which identifies a particular type of persistent error.
			 *    This defaults to an empty string. If this is passed, the error is persistent; i.e. it continues to be displayed until dismissed by the site owner.
			 *
			 * @param boolean $push_to_top Optional. Defaults to a `FALSE` value.
			 *    If `TRUE`, the error is pushed to the top of the stack; i.e. displayed above any others.
			 */
			public function enqueue_error($error, $persistent_key = '', $push_to_top = FALSE)
			{
				$error          = (string)$error;
				$persistent_key = (string)$persistent_key;

				$errors = get_option(__NAMESPACE__.'_errors');
				if(!is_array($errors)) $errors = array();

				if($persistent_key) // A persistent notice?
				{
					if(strpos($persistent_key, 'persistent-') !== 0)
						$persistent_key = 'persistent-'.$persistent_key;

					if($push_to_top) // Push this notice to the top?
						$errors = array($persistent_key => $error) + $errors;
					else $errors[$persistent_key] = $error;
				}
				else if($push_to_top) // Push to the top?
					array_unshift($errors, $error);

				else $errors[] = $error; // Default behavior.

				update_option(__NAMESPACE__.'_errors', $errors);
			}

			/**
			 * Extends WP-Cron schedules.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `cron_schedules` filter.
			 *
			 * @param array $schedules An array of the current schedules.
			 *
			 * @return array Revised array of WP-Cron schedules.
			 */
			public function extend_cron_schedules($schedules)
			{
				$schedules['every15m'] = array('interval' => 900, 'display' => __('Every 15 Minutes', $this->text_domain));

				return apply_filters(__METHOD__, $schedules, get_defined_vars());
			}
		}

		/**
		 * Used internally by other classes as an easy way to reference
		 *    the core {@link plugin} class instance.
		 *
		 * @since 14xxxx First documented version.
		 *
		 * @return plugin Class instance.
		 */
		function plugin() // Easy reference.
		{
			return $GLOBALS[__NAMESPACE__];
		}

		/**
		 * A global reference to the plugin.
		 *
		 * @since 14xxxx First documented version.
		 *
		 * @var plugin Main plugin class.
		 */
		$GLOBALS[__NAMESPACE__] = new plugin(!class_exists('\\'.__NAMESPACE__.'\\uninstall'));
	}
	else if(!class_exists('\\'.__NAMESPACE__.'\\uninstall')) add_action('all_admin_notices', function ()
	{
		echo '<div class="error"><p>'. // Running multiple versions of this plugin at same time.
		     __('Please disable the LITE version of WP Comment Subs before you activate the PRO version.',
		        str_replace('_', '-', __NAMESPACE__)).'</p></div>';
	});
}