<?php
/**
 * Plugin Class
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/includes/classes/abs-base.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\plugin'))
	{
		/**
		 * Plugin Class
		 *
		 * @property utils_array           $utils_array
		 * @property utils_date            $utils_date
		 * @property utils_db              $utils_db
		 * @property utils_enc             $utils_enc
		 * @property utils_env             $utils_env
		 * @property utils_event           $utils_event
		 * @property utils_fs              $utils_fs
		 * @property utils_i18n            $utils_i18n
		 * @property utils_mail            $utils_mail
		 * @property utils_markup          $utils_markup
		 * @property utils_php             $utils_php
		 * @property utils_queue           $utils_queue
		 * @property utils_queue_event_log $utils_queue_event_log
		 * @property utils_string          $utils_string
		 * @property utils_sub             $utils_sub
		 * @property utils_sub_event_log   $utils_sub_event_log
		 * @property utils_url             $utils_url
		 * @property utils_user            $utils_user
		 *
		 * @since 14xxxx First documented version.
		 */
		class plugin extends abs_base
		{
			/*
			 * Public Properties
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
			public $name = 'Comment Mail';

			/**
			 * Plugin name (abbreviated).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Plugin name (abbreviated).
			 */
			public $short_name = 'CM';

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

			/*
			 * Public Properties (Defined @ Setup)
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
			 * General capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Capability required to administer.
			 */
			public $cap;

			/**
			 * Management capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Capability required to manage.
			 */
			public $manage_cap;

			/**
			 * Uninstall capability requirement.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @var string Capability required to uninstall.
			 */
			public $uninstall_cap;

			/*
			 * Public Properties (Defined by Various Hooks)
			 */

			public $menu_page_hooks = array();

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
				/*
				 * Parent constructor.
				 */
				$GLOBALS[__NAMESPACE__] = $this; // Global ref.
				parent::__construct(); // Run parent constructor.

				/*
				 * Initialize properties.
				 */
				$this->enable_hooks = (boolean)$enable_hooks;
				$this->text_domain  = $this->slug = str_replace('_', '-', __NAMESPACE__);
				$this->file         = preg_replace('/\.inc\.php$/', '.php', __FILE__);

				/*
				 * Initialize autoloader.
				 */
				require_once dirname(__FILE__).'/includes/classes/autoloader.php';
				new autoloader(); // Register the plugin's autoloader.

				/*
				 * With or without hooks?
				 */
				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; construct without hooks.

				/*
				 * Setup primary plugin hooks.
				 */
				add_action('after_setup_theme', array($this, 'setup'));
				register_activation_hook($this->file, array($this, 'activate'));
				register_deactivation_hook($this->file, array($this, 'deactivate'));
			}

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
				/*
				 * Setup already?
				 */
				if(isset($this->cache[__FUNCTION__]))
					return; // Already setup. Once only!
				$this->cache[__FUNCTION__] = -1;

				/*
				 * Fire pre-setup hooks.
				 */
				if($this->enable_hooks) // Hooks enabled?
					do_action('before__'.__METHOD__, get_defined_vars());

				/*
				 * Load the plugin's text domain for translations.
				 */
				load_plugin_textdomain($this->text_domain); // For translations.

				/*
				 * Setup additional class properties.
				 */
				$this->cap = apply_filters(__METHOD__.'_cap', 'activate_plugins');

				$this->default_options = array(
					'version'                                     => $this->version,
					'enable'                                      => '0', // `0|1`; enable?
					'crons_setup'                                 => '0', // `0` or timestamp.
					'uninstall_on_deletion'                       => '0', // `0|1`; run uninstaller?

					'manage_cap'                                  => $this->cap, // Capability.
					'uninstall_cap'                               => 'delete_plugins', // Capability.

					'auto_confirm_enable'                         => '0', // `0|1`; auto-confirm enable?

					'auto_subscribe_enable'                       => '1', // `0|1`; auto-subscribe enable?
					'auto_subscribe_deliver'                      => 'asap', // `asap`, `hourly`, `daily`, `weekly`.
					'auto_subscribe_post_types'                   => 'post,page', // Comma-delimited post types.
					'auto_subscribe_post_author'                  => '1', // `0|1`; auto-subscribe post authors?
					'auto_subscribe_recipients'                   => '', // Others `;|,` delimited emails.

					'reply_to_email'                              => '', // Reply-To header.

					'smtp_enable'                                 => '0', // `0|1`; enable?
					'smtp_host'                                   => '', // SMTP host name.
					'smtp_port'                                   => '', // SMTP port number.
					'smtp_secure'                                 => '', // ``, `ssl` or `tls`.

					'smtp_username'                               => '', // SMTP username.
					'smtp_password'                               => '', // SMTP password.

					'smtp_from_name'                              => '', // SMTP from name.
					'smtp_from_email'                             => '', // SMTP from email.
					'smtp_force_from'                             => '1', // `0|1`; force?

					'template_site_site_header'                   => '', // HTML/PHP code.
					'template_site_site_footer'                   => '', // HTML/PHP code.

					'template_site_comment_form_sub_ops'          => '', // HTML/PHP code.

					'template_site_sub_actions_confirmed'         => '', // HTML/PHP code.
					'template_site_sub_actions_unsubscribed'      => '', // HTML/PHP code.
					'template_site_sub_actions_manage_summary'    => '', // HTML/PHP code.

					'template_email_email_header'                 => '', // HTML/PHP code.
					'template_email_email_footer'                 => '', // HTML/PHP code.

					'template_email_confirmation_request_subject' => '', // HTML/PHP code.
					'template_email_confirmation_request_message' => '', // HTML/PHP code.

					'template_email_comment_notification_subject' => '', // HTML/PHP code.
					'template_email_comment_notification_message' => '', // HTML/PHP code.

					'queue_processor_max_time'                    => '30', // In seconds.
					'queue_processor_delay'                       => '250', // In milliseconds.
					'queue_processor_max_limit'                   => '100', // Total queue entries.

					'queue_processor_immediate_max_time'          => '10', // In seconds.
					'queue_processor_immediate_max_limit'         => '5', // Total queue entries.

					'sub_cleaner_max_time'                        => '60', // In seconds.

					'unconfirmed_expiration_time'                 => '60 days', // `strtotime()` compatible.
					// Or, this can be left empty to disable automatic expirations altogether.

					'trashed_expiration_time'                     => '60 days', // `strtotime()` compatible.
					// Or, this can be left empty to disable automatic deletions altogether.

					'excluded_meta_box_post_types'                => 'link,comment,revision,attachment,nav_menu_item,snippet,redirect',

				); // Default options are merged with those defined by the site owner.
				$this->default_options = apply_filters(__METHOD__.'__default_options', $this->default_options); // Allow filters.
				$this->options         = is_array($this->options = get_option(__NAMESPACE__.'_options')) ? $this->options : array();
				$this->options         = array_merge($this->default_options, $this->options); // Merge into default options.
				$this->options         = array_intersect_key($this->options, $this->default_options); // Valid keys only.
				$this->options         = apply_filters(__METHOD__.'__options', $this->options); // Allow filters.

				$this->manage_cap    = $this->options['manage_cap'] ? (string)$this->options['manage_cap'] : $this->cap;
				$this->uninstall_cap = $this->options['uninstall_cap'] ? (string)$this->options['uninstall_cap'] : 'delete_plugins';

				/*
				 * With or without hooks?
				 */
				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; setup without hooks.

				/*
				 * Setup all secondary plugin hooks.
				 */
				add_action('wp_loaded', array($this, 'actions'));

				add_action('admin_init', array($this, 'check_version'));
				add_action('all_admin_notices', array($this, 'all_admin_notices'));

				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

				add_action('admin_menu', array($this, 'add_menu_pages'));
				add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
				add_filter('plugin_action_links_'.plugin_basename($this->file), array($this, 'add_settings_link'));

				add_action('init', array($this, 'comment_shortlink_redirect'), -(PHP_INT_MAX - 10));

				add_action('transition_post_status', array($this, 'post_status'), 10, 3);
				add_action('before_delete_post', array($this, 'post_delete'), 10, 1);

				add_action('comment_form', array($this, 'comment_form'), 5, 1);
				add_action('comment_post', array($this, 'comment_post'), 10, 2);
				add_action('transition_comment_status', array($this, 'comment_status'), 10, 3);

				add_action('user_register', array($this, 'user_register'), 10, 1);
				add_action('delete_user', array($this, 'user_delete'), 10, 1);
				add_action('wpmu_delete_user', array($this, 'user_delete'), 10, 1);
				add_action('remove_user_from_blog', array($this, 'user_delete'), 10, 2);

				add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

				/*
				 * Setup CRON-related hooks.
				 */
				add_filter('cron_schedules', array($this, 'extend_cron_schedules'));

				if((integer)$this->options['crons_setup'] < 1382523750)
				{
					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_queue_processor');
					wp_schedule_event(time() + 60, 'every5m', '_cron_'.__NAMESPACE__.'_queue_processor');

					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_sub_cleaner');
					wp_schedule_event(time() + 60, 'hourly', '_cron_'.__NAMESPACE__.'_sub_cleaner');

					$this->options['crons_setup'] = (string)time();
					update_option(__NAMESPACE__.'_options', $this->options);
				}
				add_action('_cron_'.__NAMESPACE__.'_queue_processor', array($this, 'queue_processor'));
				add_action('_cron_'.__NAMESPACE__.'_sub_cleaner', array($this, 'sub_cleaner'));

				/*
				 * Fire setup completion hooks.
				 */
				do_action('after__'.__METHOD__, get_defined_vars());
				do_action(__METHOD__.'_complete', get_defined_vars());
			}

			/*
			 * Magic Methods
			 */

			/**
			 * Magic/overload property getter.
			 *
			 * @param string $property Property to get.
			 *
			 * @return mixed The value of `$this->___overload->{$property}`.
			 *
			 * @throws \exception If the `$___overload` property is undefined.
			 *
			 * @see http://php.net/manual/en/language.oop5.overloading.php
			 */
			public function __get($property)
			{
				$property          = (string)$property;
				$ns_class_property = '\\'.__NAMESPACE__.'\\'.$property;

				if(stripos($property, 'utils_') === 0 && class_exists($ns_class_property))
					if(!isset($this->___overload->{$property})) // Not defined yet?
						$this->___overload->{$property} = new $ns_class_property;

				return parent::__get($property);
			}

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

			/*
			 * Admin Meta-Box-Related Methods
			 */

			/**
			 * Adds plugin meta boxes.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `add_meta_boxes` action.
			 *
			 * @param string $post_type The current post type.
			 */
			public function add_meta_boxes($post_type)
			{
				if(!current_user_can($this->manage_cap))
					if(!current_user_can($this->cap))
						return; // Do not add meta boxes.

				$post_type           = strtolower((string)$post_type);
				$excluded_post_types = $this->options['excluded_meta_box_post_types'];
				$excluded_post_types = preg_split('/[\s;,]+/', $excluded_post_types, NULL, PREG_SPLIT_NO_EMPTY);

				if(in_array($post_type, $excluded_post_types, TRUE))
					return; // Ignore; this post type excluded.

				add_meta_box(__NAMESPACE__.'_small', $this->name.'™', array($this, 'post_small_meta_box'), $post_type, 'side', 'high');
				add_meta_box(__NAMESPACE__.'_large', $this->name.'™ '.__('Subscriptions', $this->text_domain),
				             array($this, 'post_large_meta_box'), $post_type, 'normal', 'high');
			}

			/**
			 * Builds small meta box for this plugin.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \WP_Post $post A WP post object reference.
			 *
			 * @see add_meta_boxes()
			 */
			public function post_small_meta_box(\WP_Post $post)
			{
				new post_small_meta_box($post);
			}

			/**
			 * Builds large meta box for this plugin.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \WP_Post $post A WP post object reference.
			 *
			 * @see add_meta_boxes()
			 */
			public function post_large_meta_box(\WP_Post $post)
			{
				new post_large_meta_box($post);
			}

			/*
			 * Admin Menu-Page-Related Methods
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
				if(!$this->utils_env->is_menu_page(__NAMESPACE__.'*')
				   && !$this->utils_env->is_menu_page('post.php')
				) return; // Nothing to do; not applicable.

				$deps = array(); // Plugin dependencies.

				wp_enqueue_style(__NAMESPACE__, $this->utils_url->to('/client-s/css/menu-pages.min.css'), $deps, $this->version, 'all');
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
				if(!$this->utils_env->is_menu_page(__NAMESPACE__.'*'))
					return; // Nothing to do; NOT a plugin menu page.

				$deps = array('jquery'); // Plugin dependencies.

				wp_enqueue_script(__NAMESPACE__, $this->utils_url->to('/client-s/js/menu-pages.min.js'), $deps, $this->version, TRUE);
				wp_localize_script(__NAMESPACE__, __NAMESPACE__.'_vars', array(
					'plugin_url'    => rtrim($this->utils_url->to('/'), '/'),
					'ajax_endpoint' => rtrim($this->utils_url->current_page_nonce_only(), '/')
				));
				wp_localize_script(__NAMESPACE__, __NAMESPACE__.'_i18n', array(
					'bulk_reconfirm_confirmation' => __('Resend email confirmation link? Are you sure?', $this->text_domain),
					'bulk_delete_confirmation'    => __('Delete permanently? Are you sure?', $this->text_domain),
				));
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
				if(!current_user_can($this->manage_cap))
					if(!current_user_can($this->cap))
						return; // Do not add meta boxes.

				$this->menu_page_hooks[__NAMESPACE__] = add_comments_page($this->name.'™', $this->name.'™', $this->cap, __NAMESPACE__, array($this, 'menu_page_options'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__], array($this, 'menu_page_options_screen'));

				$menu_title                                   = '⥱ '.__('Subscriptions', $this->text_domain);
				$page_title                                   = $this->name.'™ ⥱ '.__('Subscriptions', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_subs'] = add_comments_page($page_title, $menu_title, $this->manage_cap, __NAMESPACE__.'_subs', array($this, 'menu_page_subs'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_subs'], array($this, 'menu_page_subs_screen'));

				$menu_title                                            = '⥱ '.__('Sub. Event Log', $this->text_domain);
				$page_title                                            = $this->name.'™ ⥱ '.__('Sub. Event Log', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_sub_event_log'] = add_comments_page($page_title, $menu_title, $this->manage_cap, __NAMESPACE__.'_sub_event_log', array($this, 'menu_page_sub_event_log'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_sub_event_log'], array($this, 'menu_page_sub_event_log_screen'));

				$menu_title                                    = '⥱ '.__('Mail Queue', $this->text_domain);
				$page_title                                    = $this->name.'™ ⥱ '.__('Mail Queue', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_queue'] = add_comments_page($page_title, $menu_title, $this->manage_cap, __NAMESPACE__.'_queue', array($this, 'menu_page_queue'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_queue'], array($this, 'menu_page_queue_screen'));

				$menu_title                                              = '⥱ '.__('Queue Event Log', $this->text_domain);
				$page_title                                              = $this->name.'™ ⥱ '.__('Queue Event Log', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_queue_event_log'] = add_comments_page($page_title, $menu_title, $this->manage_cap, __NAMESPACE__.'_queue_event_log', array($this, 'menu_page_queue_event_log'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_queue_event_log'], array($this, 'menu_page_queue_event_log_screen'));
			}

			/**
			 * Set plugin-related screen options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `set-screen-option` filter.
			 *
			 * @param mixed|boolean $what_wp_says `FALSE` if not saving (default).
			 *    If we set this to any value besides `FALSE`, the option will be saved by WP.
			 *
			 * @param string        $option The option being checked; i.e. should we save this option?
			 *
			 * @param mixed         $value The current value for this option.
			 *
			 * @return mixed|boolean Returns `$value` for plugin-related options.
			 *    Other we simply return `$what_wp_says`.
			 */
			public function set_screen_option($what_wp_says, $option, $value)
			{
				if(strpos($option, __NAMESPACE__.'_') === 0)
					return $value; // Yes, save this.

				return $what_wp_says;
			}

			/**
			 * Menu page screen; for options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__]` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_options_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__]
				) return; // Not applicable.

				return; // No screen for this page right now.
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
				new menu_page('options');
			}

			/**
			 * Menu page screen; for subs.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_subs']` action.
			 *
			 * @see add_menu_pages()
			 * @see subs_table::get_hidden_columns()
			 */
			public function menu_page_subs_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_subs'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_subs']
				) return; // Not applicable.

				add_screen_option('per_page', array(
					'default' => '50', // Default items per page.
					'label'   => __('Per Page', $this->text_domain),
					'option'  => __NAMESPACE__.'_subs_per_page',
				));
				add_filter('manage_'.$screen->id.'_columns', function ()
				{
					return menu_page_subs_table::get_columns_();
				});
				add_filter('get_user_option_manage'.$screen->id.'columnshidden', function ($value)
				{
					return is_array($value) ? $value : menu_page_subs_table::get_hidden_columns_();
				});
			}

			/**
			 * Menu page for subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_subs()
			{
				new menu_page('subs');
			}

			/**
			 * Menu page screen; for subs.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_subs']` action.
			 *
			 * @see add_menu_pages()
			 * @see subs_table::get_hidden_columns()
			 */
			public function menu_page_sub_event_log_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_sub_event_log'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_sub_event_log']
				) return; // Not applicable.

				add_screen_option('per_page', array(
					'default' => '50', // Default items per page.
					'label'   => __('Per Page', $this->text_domain),
					'option'  => __NAMESPACE__.'_sub_event_log_entries_per_page',
				));
				add_filter('manage_'.$screen->id.'_columns', function ()
				{
					return menu_page_sub_event_log_table::get_columns_();
				});
				add_filter('get_user_option_manage'.$screen->id.'columnshidden', function ($value)
				{
					return is_array($value) ? $value : menu_page_sub_event_log_table::get_hidden_columns_();
				});
			}

			/**
			 * Menu page for sub. event log.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_sub_event_log()
			{
				new menu_page('sub_event_log');
			}

			/**
			 * Menu page screen; for queue.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_queue']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_queue_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_queue'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_queue']
				) return; // Not applicable.

				add_screen_option('per_page', array(
					'default' => '50', // Default items per page.
					'label'   => __('Per Page', $this->text_domain),
					'option'  => __NAMESPACE__.'_queued_notifications_per_page',
				));
				add_filter('manage_'.$screen->id.'_columns', function ()
				{
					return menu_page_queue_table::get_columns_();
				});
				add_filter('get_user_option_manage'.$screen->id.'columnshidden', function ($value)
				{
					return is_array($value) ? $value : menu_page_queue_table::get_hidden_columns_();
				});
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
				new menu_page('queue');
			}

			/**
			 * Menu page screen; for queue event log.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_queue_event_log']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_queue_event_log_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_queue_event_log'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_queue_event_log']
				) return; // Not applicable.

				add_screen_option('per_page', array(
					'default' => '50', // Default items per page.
					'label'   => __('Per Page', $this->text_domain),
					'option'  => __NAMESPACE__.'_queue_event_log_entries_per_page',
				));
				add_filter('manage_'.$screen->id.'_columns', function ()
				{
					return menu_page_queue_event_log_table::get_columns_();
				});
				add_filter('get_user_option_manage'.$screen->id.'columnshidden', function ($value)
				{
					return is_array($value) ? $value : menu_page_queue_event_log_table::get_hidden_columns_();
				});
			}

			/**
			 * Menu page for mail queue event log.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_queue_event_log()
			{
				new menu_page('queue_event_log');
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
			public function add_settings_link(array $links)
			{
				$links[] = '<a href="'.esc_attr($this->utils_url->main_menu_page_only()).'">'.__('Settings', $this->text_domain).'</a><br/>';
				$links[] = '<a href="'.esc_attr($this->utils_url->pro_preview($this->utils_url->main_menu_page_only())).'">'.__('Preview Pro Features', $this->text_domain).'</a>';
				$links[] = '<a href="'.esc_attr($this->utils_url->product_page()).'" target="_blank">'.__('Upgrade', $this->text_domain).'</a>';

				return apply_filters(__METHOD__, $links, get_defined_vars());
			}

			/*
			 * Admin Notice/Error Related Methods
			 */

			/**
			 * Enqueue an administrative notice.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $markup HTML markup containing the notice itself.
			 * @param array  $args An array of additional args; i.e. presentation/style.
			 */
			public function enqueue_notice($markup, array $args = array())
			{
				if(!($markup = trim((string)$markup)))
					return; // Nothing to do here.

				$default_args   = array(
					'markup'       => '',
					'requires_cap' => '',
					'for_user_id'  => 0,
					'for_page'     => '',
					'persistent'   => FALSE,
					'transient'    => FALSE,
					'push_to_top'  => FALSE,
					'type'         => 'notice',
				);
				$args['markup'] = (string)$markup; // + markup.
				$args           = array_merge($default_args, $args);
				$args           = array_intersect_key($args, $default_args);

				$args['requires_cap'] = trim((string)$args['requires_cap']);
				$args['requires_cap'] = $args['requires_cap'] // Force valid format.
					? strtolower(preg_replace('/\W/', '_', $args['requires_cap'])) : '';

				$args['for_user_id'] = (integer)$args['for_user_id'];
				$args['for_page']    = trim((string)$args['for_page']);

				$args['persistent']  = (boolean)$args['persistent'];
				$args['transient']   = (boolean)$args['transient'];
				$args['push_to_top'] = (boolean)$args['push_to_top'];

				if(!in_array($args['type'], array('notice', 'error'), TRUE))
					$args['type'] = 'notice'; // Use default type.

				ksort($args); // Sort args (by key) for key generation.
				$key = $this->utils_enc->hmac_sha256_sign(serialize($args));

				if(!is_array($notices = get_option(__NAMESPACE__.'_notices')))
					$notices = array(); // Force an array of notices.

				if($args['push_to_top']) // Push this notice to the top?
					$this->utils_array->unshift_assoc($notices, $key, $args);
				else $notices[$key] = $args; // Default behavior.

				update_option(__NAMESPACE__.'_notices', $notices);
			}

			/**
			 * Enqueue an administrative notice; for a particular user.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $markup HTML markup. See {@link enqueue_notice()}.
			 * @param array  $args Additional args. See {@link enqueue_notice()}.
			 */
			public function enqueue_user_notice($markup, array $args = array())
			{
				if(!isset($args['for_user_id']))
					$args['for_user_id'] = get_current_user_id();

				$this->enqueue_notice($markup, $args);
			}

			/**
			 * Enqueue an administrative error.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $markup HTML markup. See {@link enqueue_notice()}.
			 * @param array  $args Additional args. See {@link enqueue_notice()}.
			 */
			public function enqueue_error($markup, array $args = array())
			{
				$this->enqueue_notice($markup, array_merge($args, array('type' => 'error')));
			}

			/**
			 * Enqueue an administrative error; for a particular user.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $markup HTML markup. See {@link enqueue_error()}.
			 * @param array  $args Additional args. See {@link enqueue_notice()}.
			 */
			public function enqueue_user_error($markup, array $args = array())
			{
				if(!isset($args['for_user_id']))
					$args['for_user_id'] = get_current_user_id();

				$this->enqueue_error($markup, $args);
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
				if(!is_array($notices = get_option(__NAMESPACE__.'_notices')))
					update_option(__NAMESPACE__.'_notices', ($notices = array()));

				if(!$notices) return; // Nothing more to do in this case.

				$user_can_view_notices // All notices require one of the following caps.
					= current_user_can($this->manage_cap) || current_user_can($this->cap);

				$original_notices = $notices; // Copy.

				foreach($notices as $_key => $_args)
				{
					$default_args = array(
						'markup'       => '',
						'requires_cap' => '',
						'for_user_id'  => 0,
						'for_page'     => '',
						'persistent'   => FALSE,
						'transient'    => FALSE,
						'push_to_top'  => FALSE,
						'type'         => 'notice',
					);
					$_args        = array_merge($default_args, $_args);
					$_args        = array_intersect_key($_args, $default_args);

					$_args['markup'] = trim((string)$_args['markup']);

					$_args['requires_cap'] = trim((string)$_args['requires_cap']);
					$_args['requires_cap'] = $_args['requires_cap'] // Force valid format.
						? strtolower(preg_replace('/\W/', '_', $_args['requires_cap'])) : '';

					$_args['for_user_id'] = (integer)$_args['for_user_id'];
					$_args['for_page']    = trim((string)$_args['for_page']);

					$_args['persistent']  = (boolean)$_args['persistent'];
					$_args['transient']   = (boolean)$_args['transient'];
					$_args['push_to_top'] = (boolean)$_args['push_to_top'];

					if(!in_array($_args['type'], array('notice', 'error'), TRUE))
						$_args['type'] = 'notice'; // Use default type.

					if($_args['transient']) // Transient; i.e. single pass only?
						unset($notices[$_key]); // Remove always in this case.

					if(!$user_can_view_notices) // Primary capability check.
						continue;  // Don't display to this user under any circumstance.

					if($_args['requires_cap'] && !current_user_can($_args['requires_cap']))
						continue; // Don't display to this user; lacks required cap.

					if($_args['for_user_id'] && get_current_user_id() !== $_args['for_user_id'])
						continue; // Don't display to this particular user ID.

					if($_args['for_page'] && !$this->utils_env->is_menu_page($_args['for_page']))
						continue; // Don't display on this page; i.e. pattern match failure.

					if($_args['markup']) // Only display non-empty notices.
					{
						if($_args['persistent']) // Need [dismiss] link?
						{
							$_dismiss_style  = 'float: right;'.
							                   'margin: 0 0 0 15px;'.
							                   'display: inline-block;'.
							                   'text-decoration: none;'.
							                   'font-weight: bold;';
							$_dismiss_url    = $this->utils_url->dismiss_notice($_key);
							$_dismiss_anchor = '<a href="'.esc_attr($_dismiss_url).'"'.
							                   '  style="'.esc_attr($_dismiss_style).'">'.
							                   '  '.__('dismiss &times;', $this->text_domain).
							                   '</a>';
						}
						else $_dismiss_anchor = ''; // Define a default value.

						$_classes = $this->slug.'-menu-page-area'; // Always.
						$_classes .= ' pmp-'.($_args['type'] === 'error' ? 'error' : 'notice');
						$_classes .= ' '.($_args['type'] === 'error' ? 'error' : 'updated');

						$_full_markup = // Put together the full markup; including other pieces.
							'<div class="'.esc_attr($_classes).'">'.
							'  <p>'.$_args['markup'].$_dismiss_anchor.'</p>'.
							'</div>';
						echo apply_filters(__METHOD__.'_notice', $_full_markup, get_defined_vars());
					}
					if(!$_args['persistent']) unset($notices[$_key]); // Once only; i.e. don't show again.
				}
				unset($_key, $_args, $_dismiss_style, $_dismiss_url, $_dismiss_anchor, $_classes, $_full_markup); // Housekeeping.

				if($original_notices !== $notices) update_option(__NAMESPACE__.'_notices', $notices);
			}

			/*
			 * Post-Related Methods
			 */

			/**
			 * Post status handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `transition_post_status` action.
			 *
			 * @param string        $new_post_status New post status.
			 *
			 *    One of the following statuses:
			 *    See: <http://codex.wordpress.org/Function_Reference/get_post_status>
			 *
			 *       - `publish`
			 *       - `pending`
			 *       - `draft`
			 *       - `auto-draft`
			 *       - `future`
			 *       - `private`
			 *       - `inherit`
			 *       - `trash`
			 *
			 *    See also: {@link get_available_post_statuses()}
			 *       Custom post types may have their own statuses.
			 *
			 * @param string        $old_post_status Old post status.
			 *
			 *    One of the following statuses:
			 *    See: <http://codex.wordpress.org/Function_Reference/get_post_status>
			 *
			 *       - `new`
			 *       - `publish`
			 *       - `pending`
			 *       - `draft`
			 *       - `auto-draft`
			 *       - `future`
			 *       - `private`
			 *       - `inherit`
			 *       - `trash`
			 *
			 *    See also: {@link get_available_post_statuses()}
			 *       Custom post types may have their own statuses.
			 *
			 * @param \WP_Post|null $post Post object instance.
			 */
			public function post_status($new_post_status, $old_post_status, \WP_Post $post = NULL)
			{
				new post_status($new_post_status, $old_post_status, $post);
			}

			/**
			 * Post deletion handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `before_delete_post` action.
			 *
			 * @param integer|string $post_id Post ID.
			 */
			public function post_delete($post_id)
			{
				new post_delete($post_id);
			}

			/*
			 * Comment-Related Methods
			 */

			/**
			 * Comment shortlink redirections.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `init` action.
			 */
			public function comment_shortlink_redirect()
			{
				if(empty($_REQUEST['c']) || is_admin())
					return; // Nothing to do.

				new comment_shortlink_redirect();
			}

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
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
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
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param integer|string $old_comment_status Old comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param \stdClass|null $comment Comment object (now).
			 */
			public function comment_status($new_comment_status, $old_comment_status, \stdClass $comment = NULL)
			{
				new comment_status($new_comment_status, $old_comment_status, $comment);
			}

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
			public function user_delete($user_id, $blog_id = 0)
			{
				new user_delete($user_id, $blog_id);
			}

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
			public function extend_cron_schedules(array $schedules)
			{
				$schedules['every5m']  = array('interval' => 300, 'display' => __('Every 5 Minutes', $this->text_domain));
				$schedules['every15m'] = array('interval' => 900, 'display' => __('Every 15 Minutes', $this->text_domain));

				return apply_filters(__METHOD__, $schedules, get_defined_vars());
			}

			/**
			 * Queue processor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `_cron_'.__NAMESPACE__.'_queue_processor` action.
			 */
			public function queue_processor()
			{
				new queue_processor();
			}

			/**
			 * Sub cleaner.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @attaches-to `_cron_'.__NAMESPACE__.'_sub_cleaner` action.
			 */
			public function sub_cleaner()
			{
				new sub_cleaner();
			}
		}

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