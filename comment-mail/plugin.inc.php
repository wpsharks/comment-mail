<?php
/**
 * Plugin Class
 *
 * @package plugin
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\plugin'))
	{
		/**
		 * Plugin Class
		 *
		 * @package plugin
		 * @since 14xxxx First documented version.
		 */
		class plugin // The heart of this plugin.
		{
			/********************************************************************************************************/

			/*
			 * Public Properties Defined by Constructor
			 */

			/**
			 * Identifies pro version.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var boolean `TRUE` for pro version.
			 */
			public $is_pro = FALSE;

			/**
			 * Plugin name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Plugin name.
			 */
			public $name = 'Comment Mail™';

			/**
			 * Plugin name (abbreviated).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Plugin name (abbreviated).
			 */
			public $name_abbr = 'CM™';

			/**
			 * Used by the plugin's uninstall handler.
			 *
			 * @since 14xxxx Adding uninstall handler.
			 *
			 * @var boolean Defined by constructor.
			 */
			public $enable_hooks;

			/**
			 * Text domain for translations; based on `__NAMESPACE__`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Defined by class constructor; for translations.
			 */
			public $text_domain;

			/**
			 * Plugin slug; based on `__NAMESPACE__`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Defined by constructor.
			 */
			public $slug;

			/**
			 * Stub `__FILE__` location.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Defined by class constructor.
			 */
			public $file;

			/**
			 * Version string in YYMMDD[+build] format.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Current version of the software.
			 */
			public $version = '14xxxx';

			/********************************************************************************************************/

			/*
			 * Public Properties Defined @ Setup
			 */

			/**
			 * An array of all default option values.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array Default options array.
			 */
			public $default_options;

			/**
			 * Configured option values.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array Options configured by site owner.
			 */
			public $options;

			/**
			 * WordPress database reference.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var \wpdb Database object reference.
			 */
			public $wpdb;

			/**
			 * General capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Capability required to administer.
			 */
			public $cap;

			/**
			 * Uninstall capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Capability required to uninstall.
			 */
			public $uninstall_cap;

			/********************************************************************************************************/

			/*
			 * Protected Cache-Related Properties
			 */

			/**
			 * An instance-based cache for class members.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array An instance-based cache for class members.
			 */
			protected $cache = array();

			/**
			 * A global static cache for class members.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var array Global static cache for class members.
			 */
			protected static $static = array();

			/********************************************************************************************************/

			/*
			 * Plugin Constructor
			 */

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
				$this->text_domain  = $this->slug = str_replace('_', '-', __NAMESPACE__);
				$this->file         = preg_replace('/\.inc\.php$/', '.php', __FILE__);

				/* -------------------------------------------------------------- */

				require_once dirname(__FILE__).'/includes/classes/autoloader.php';
				new autoloader(); // Register the plugin's autoloader.

				/* -------------------------------------------------------------- */

				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; setup without hooks.

				/* -------------------------------------------------------------- */

				add_action('after_setup_theme', array($this, 'setup'));
				register_activation_hook($this->file, array($this, 'activate'));
				register_deactivation_hook($this->file, array($this, 'deactivate'));
			}

			/********************************************************************************************************/

			/*
			 * Setup Routine(s)
			 */

			/**
			 * Setup the plugin.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function setup()
			{
				if(isset($this->cache[__FUNCTION__]))
					return; // Already setup. Once only!
				$this->cache[__FUNCTION__] = -1;

				if($this->enable_hooks) // Hooks enabled?
					do_action('before__'.__METHOD__, get_defined_vars());

				/* -------------------------------------------------------------- */

				load_plugin_textdomain($this->text_domain); // For translations.

				$this->default_options = array( // Option defaults.
				                                'version'               => $this->version,
				                                'enable'                => '0', // `0|1`.
				                                'crons_setup'           => '0', // `0` or timestamp.
				                                'uninstall_on_deletion' => '0', // `0|1`.

				                                'smtp_enable'           => '0', // `0|1`.
				                                'smtp_host'             => '', // Host name.
				                                'smtp_port'             => '', // Port number.
				                                'smtp_secure'           => '', // ``, `ssl` or `tls`.

				                                'smtp_username'         => '', // Username.
				                                'smtp_password'         => '', // Password.

				                                'smtp_from_name'        => '', // From name.
				                                'smtp_from_addr'        => '', // From address.
				                                'smtp_force_from'       => '1', // `0|1`.

				); // Default options are merged with those defined by the site owner.
				$this->default_options = apply_filters(__METHOD__.'__default_options', $this->default_options, get_defined_vars());

				$options       = (is_array($options = get_option(__NAMESPACE__.'_options'))) ? $options : array();
				$this->options = array_merge($this->default_options, $options); // Merge into default options.
				$this->options = apply_filters(__METHOD__.'__options', $this->options, get_defined_vars());

				$this->wpdb          = $GLOBALS['wpdb']; // DB reference.
				$this->cap           = apply_filters(__METHOD__.'__cap', 'activate_plugins');
				$this->uninstall_cap = apply_filters(__METHOD__.'__uninstall_cap', 'delete_plugins');

				/* -------------------------------------------------------------- */

				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; setup without hooks.

				/* -------------------------------------------------------------- */

				add_action('wp_loaded', array($this, 'actions'));
				add_action('admin_init', array($this, 'check_version'));

				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

				add_action('all_admin_notices', array($this, 'all_admin_notices'));
				add_action('all_admin_notices', array($this, 'all_admin_errors'));

				add_action('admin_menu', array($this, 'add_menu_pages'));
				add_filter('plugin_action_links_'.plugin_basename($this->file), array($this, 'add_settings_link'));

				add_filter('cron_schedules', array($this, 'extend_cron_schedules'));

				add_action('comment_form', array($this, 'comment_form'), 5, 1);
				add_action('comment_post', array($this, 'comment_post'), 10, 2);
				add_action('transition_comment_status', array($this, 'comment_status'), 10, 3);

				add_action('before_delete_post', array($this, 'delete_post'), 10, 1);

				add_action('user_register', array($this, 'user_register'), 10, 1);
				add_action('delete_user', array($this, 'delete_user'), 10, 1);
				add_action('wpmu_delete_user', array($this, 'delete_user'), 10, 1);
				add_action('remove_user_from_blog', array($this, 'delete_user'), 10, 2);

				/* -------------------------------------------------------------- */

				if((integer)$this->options['crons_setup'] < 1382523750)
				{
					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_process_queue');
					wp_schedule_event(time() + 60, 'every5m', '_cron_'.__NAMESPACE__.'_process_queue');

					$this->options['crons_setup'] = (string)time();
					update_option(__NAMESPACE__.'_options', $this->options);
				}
				add_action('_cron_'.__NAMESPACE__.'_process_queue', array($this, 'process_queue'));

				/* -------------------------------------------------------------- */

				do_action('after__'.__METHOD__, get_defined_vars());
				do_action(__METHOD__.'_complete', get_defined_vars());
			}

			/********************************************************************************************************/

			/*
			 * Install-Related Methods
			 */

			/**
			 * Plugin activation hook.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to {@link \register_activation_hook()}
			 */
			public function activate()
			{
				new installer(); // Installation handler.
			}

			/**
			 * Check current plugin version.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `admin_init` action.
			 */
			public function check_version()
			{
				if(version_compare($this->options['version'], $this->version, '>='))
					return; // Nothing to do; already @ latest version.

				new upgrader(); // Upgrade handler.
			}

			/********************************************************************************************************/

			/*
			 * Uninstall-Related Methods
			 */

			/**
			 * Plugin deactivation hook.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to {@link \register_deactivation_hook()}
			 */
			public function deactivate()
			{
				// Does nothing at this time.
			}

			/**
			 * Plugin uninstall handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @called-by {@link uninstall}
			 */
			public function uninstall()
			{
				new uninstaller(); // Uninstall handler.
			}

			/********************************************************************************************************/

			/*
			 * CRON-Related Methods
			 */

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
				$schedules['every5m']  = array('interval' => 300, 'display' => __('Every 5 Minutes', $this->text_domain));
				$schedules['every15m'] = array('interval' => 900, 'display' => __('Every 15 Minutes', $this->text_domain));

				return apply_filters(__METHOD__, $schedules, get_defined_vars());
			}

			/********************************************************************************************************/

			/*
			 * Action-Related Methods
			 */

			/**
			 * Plugin action handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `wp_loaded` action.
			 */
			public function actions()
			{
				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do here.

				new actions(); // Handle action(s).
			}

			/********************************************************************************************************/

			/*
			 * Admin UI-Related Methods
			 */

			/**
			 * Adds CSS for administrative menu pages.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` action.
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
			 * @attaches-to `admin_enqueue_scripts` action.
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
			 * @attaches-to `admin_menu` action.
			 */
			public function add_menu_pages()
			{
				add_comments_page($this->name, $this->name, $this->cap, __NAMESPACE__, array($this, 'menu_page_options'));
				add_comments_page($this->name, $this->name, $this->cap, __NAMESPACE__.'_subscribers', array($this, 'menu_page_subscribers'));
				add_comments_page($this->name, $this->name, $this->cap, __NAMESPACE__.'_queue', array($this, 'menu_page_queue'));
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
				$links[] = '<a href="options-general.php?page='.urlencode(__NAMESPACE__).'">'.__('Settings', $this->text_domain).'</a><br/>';
				$links[] = '<a href="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, __NAMESPACE__.'_pro_preview' => '1')), self_admin_url('/admin.php'))).'">'.__('Preview Pro Features', $this->text_domain).'</a>';
				$links[] = '<a href="'.esc_attr('http://www.websharks-inc.com/product/'.str_replace('_', '-', __NAMESPACE__).'/').'" target="_blank">'.__('Upgrade', $this->text_domain).'</a>';

				return apply_filters(__METHOD__, $links, get_defined_vars());
			}

			/**
			 * Menu page for options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_options()
			{
				$menu_pages = new menu_pages();
				$menu_pages->options();
			}

			/**
			 * Menu page for subscribers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_subscribers()
			{
				$menu_pages = new menu_pages();
				$menu_pages->subscribers();
			}

			/**
			 * Menu page for mail queue.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_queue()
			{
				$menu_pages = new menu_pages();
				$menu_pages->queue();
			}

			/********************************************************************************************************/

			/*
			 * Admin Notice/Error Methods
			 */

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
			 * Render admin notices; across all admin dashboard views.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `all_admin_notices` action.
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
			 * Render admin errors; across all admin dashboard views.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `all_admin_notices` action.
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

			/********************************************************************************************************/

			/*
			 * Post-Related Methods
			 */

			/**
			 * Post deletion handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `before_delete_post` action.
			 *
			 * @param integer|string $post_id Post ID.
			 */
			public function delete_post($post_id)
			{
				new delete_post($post_id);
			}

			/********************************************************************************************************/

			/*
			 * Comment-Related Methods
			 */

			/**
			 * Comment form handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `comment_form` action.
			 *
			 * @param integer|string $post_id Post ID.
			 */
			public function comment_form($post_id)
			{
				new comment_form($post_id);
			}

			/**
			 * Comment post handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `comment_post` action.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @param integer|string $comment_status Initial comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 */
			public function comment_post($comment_id, $comment_status)
			{
				new comment_post($comment_id, $comment_status);
			}

			/**
			 * Comment status handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `transition_comment_status` action.
			 *
			 * @param integer|string $new_comment_status New comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 *
			 * @param integer|string $old_comment_status Old comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 *
			 * @param object|null    $comment Comment object (now).
			 */
			public function comment_status($new_comment_status, $old_comment_status, $comment)
			{
				new comment_status($new_comment_status, $old_comment_status, $comment);
			}

			/**
			 * Comment status translator.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $status
			 *
			 *    One of the following:
			 *       - `0` (aka: `hold`, `unapproved`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `spam`, `delete`.
			 *
			 * @return string `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @throws \exception If an unexpected status is encountered.
			 */
			public function comment_status__($status)
			{
				switch(strtolower((string)$status))
				{
					case '1':
					case 'approve':
					case 'approved':
						return 'approve';

					case '0':
					case 'hold':
					case 'unapproved':
						return 'hold';

					case 'trash':
						return 'trash';

					case 'spam':
						return 'spam';

					case 'delete':
						return 'delete';

					default: // Throw exception on anything else.
						throw new \exception(sprintf(__('Unexpected comment status: `%1$s`.'), $status));
				}
			}

			/********************************************************************************************************/

			/*
			 * User-Related Methods
			 */

			/**
			 * User registration handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `user_register` action.
			 *
			 * @param integer|string $user_id User ID.
			 */
			public function user_register($user_id)
			{
				new user_register($user_id);
			}

			/**
			 * User deletion handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `delete_user` action.
			 * @attaches-to `wpmu_delete_user` action.
			 * @attaches-to `remove_user_from_blog` action.
			 *
			 * @param integer|string $user_id User ID.
			 * @param integer|string $blog_id Blog ID. Defaults to `0` (current blog).
			 */
			public function delete_user($user_id, $blog_id = 0)
			{
				new delete_user($user_id, $blog_id);
			}

			/********************************************************************************************************/

			/*
			 * Queue-Related Methods
			 */

			/**
			 * Queue processor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `_cron_'.__NAMESPACE__.'_process_queue` action.
			 */
			public function process_queue()
			{
				new queue_processor(); // Process queue.
			}

			/*
			 * Mail Utilities
			 */

			/**
			 * Mail sending utility; `wp_mail()` compatible.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This method always (ALWAYS) sends email in HTML format;
			 *    w/ a plain text alternative — generated automatically.
			 *
			 * @param string|array $to Array or comma-separated list of emails.
			 * @param string       $subject Email subject line.
			 * @param string       $message Message contents.
			 * @param string|array $headers Optional. Additional headers.
			 * @param string|array $attachments Optional. Files to attach.
			 *
			 * @return boolean TRUE if the email was sent successfully.
			 */
			public function mail($to, $subject, $message, $headers = array(), $attachments = array())
			{
				if($this->options['smtp_enable'] && $this->options['smtp_host'] && $this->options['smtp_port'])
				{
					if(!isset($this->cache[__FUNCTION__]['smtp']))
						$smtp = $this->cache[__FUNCTION__]['smtp'] = new smtp();
					else $smtp = $this->cache[__FUNCTION__]['smtp'];

					/** @var $smtp smtp Reference for IDEs. */
					return $smtp->mail($to, $subject, $message, $headers, $attachments);
				}
				if(is_array($headers)) // Append `Content-Type`.
					$headers[] = 'Content-Type: text/html; charset=UTF-8';
				else $headers = trim((string)$headers."\r\n".'Content-Type: text/html; charset=UTF-8');

				return wp_mail($to, $subject, $message, $headers, $attachments);
			}

			/********************************************************************************************************/

			/*
			 * DB Utilities
			 */

			/**
			 * Current DB table prefix for this plugin.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current DB table prefix.
			 */
			public function db_prefix()
			{
				return $this->wpdb->prefix.__NAMESPACE__.'_';
			}

			/********************************************************************************************************/

			/*
			 * Conditional Utilities
			 */

			/**
			 * Current request is for a pro version preview?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if the current request is for a pro preview.
			 */
			public function is_pro_preview()
			{
				if(isset(static::$static[__FUNCTION__]))
					return static::$static[__FUNCTION__];

				if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
					return (static::$static[__FUNCTION__] = TRUE);

				return (static::$static[__FUNCTION__] = FALSE);
			}

			/********************************************************************************************************/

			/*
			 * URL Utilities
			 */

			/**
			 * URL to a plugin file.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string      $file Optional file path; relative to plugin directory.
			 * @param string|null $scheme Optional URL scheme. Defaults to the current scheme.
			 *
			 * @return string URL to plugin directory; or to the specified `$file` if applicable.
			 */
			public function url($file = '', $scheme = NULL)
			{
				if(!isset(static::$static[__FUNCTION__]['plugin_dir']))
					static::$static[__FUNCTION__]['plugin_dir'] = rtrim(plugin_dir_url($this->file), '/');

				$url = static::$static[__FUNCTION__]['plugin_dir'].(string)$file;
				$url = set_url_scheme($url, $scheme);

				return apply_filters(__METHOD__, $url, get_defined_vars());
			}

			/********************************************************************************************************/

			/*
			 * String Utilities
			 */

			/**
			 * Strips slashes in strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $value Any value can be converted into a stripped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @return string|array|object Stripped string, array, object.
			 */
			public function strip_deep($value)
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as &$_value)
						$_value = $this->strip_deep($_value);
					return $value;
				}
				return stripslashes((string)$value);
			}

			/**
			 * Trims strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed  $value Any value can be converted into a trimmed string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param string $chars Specific chars to trim.
			 *    Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
			 *
			 * @param string $extra_chars Additional chars to trim.
			 *
			 * @return string|array|object Trimmed string, array, object.
			 */
			public function trim_deep($value, $chars = '', $extra_chars = '')
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as &$_value)
						$_value = $this->trim_deep($_value, $chars, $extra_chars);
					return $value;
				}
				$chars = isset($chars[0]) ? $chars : " \r\n\t\0\x0B";
				$chars = $chars.$extra_chars; // Concatenate.

				return trim((string)$value, $chars);
			}

			/**
			 * Trims and strips slashes in strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed  $value Any value can be converted into a trimmed/stripped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param string $chars Specific chars to trim.
			 *    Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
			 *
			 * @param string $extra_chars Additional chars to trim.
			 *
			 * @return string|array|object Trimmed/stripped string, array, object.
			 */
			public function trim_strip_deep($value, $chars = '', $extra_chars = '')
			{
				return $this->trim_deep($this->strip_deep($value), $chars, $extra_chars);
			}
		}

		/***********************************************************************************************************/

		/*
		 * Namespaced Functions
		 */

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

		/***********************************************************************************************************/

		/*
		 * Automatic Plugin Loader
		 */

		/**
		 * A global reference to the plugin.
		 *
		 * @since 14xxxx First documented version.
		 *
		 * @var plugin Main plugin class.
		 */
		if(!isset($GLOBALS[__NAMESPACE__.'_autoload_plugin']) || $GLOBALS[__NAMESPACE__.'_autoload_plugin'])
			$GLOBALS[__NAMESPACE__] = new plugin(); // Load plugin automatically.
	}
	/**************************************************************************************************************/

	/*
	 * Catch a scenario where the plugin class already exists.
	 *    Assume both lite/pro are running in this case.
	 */

	else if(empty($GLOBALS[__NAMESPACE__.'_uninstalling'])) add_action('all_admin_notices', function ()
	{
		echo '<div class="error">'. // Notify the site owner.
		     '   <p>'.
		     '      '.sprintf(__('Please disable the lite version of <code>%1$s</code> before activating the pro version.',
		                         str_replace('_', '-', __NAMESPACE__)), esc_html(str_replace('_', '-', __NAMESPACE__))).
		     '   </p>'.
		     '</div>';
	});
}