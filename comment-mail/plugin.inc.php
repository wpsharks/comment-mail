<?php
/**
 * Plugin Class
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail {

	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/includes/classes/abs-base.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\plugin'))
	{
		/**
		 * Plugin Class
		 *
		 * @property-read utils_array           $utils_array
		 * @property-read utils_date            $utils_date
		 * @property-read utils_db              $utils_db
		 * @property-read utils_enc             $utils_enc
		 * @property-read utils_env             $utils_env
		 * @property-read utils_event           $utils_event
		 * @property-read utils_fs              $utils_fs
		 * @property-read utils_i18n            $utils_i18n
		 * @property-read utils_ip              $utils_ip
		 * @property-read utils_log             $utils_log
		 * @property-read utils_mail            $utils_mail
		 * @property-read utils_map             $utils_map
		 * @property-read utils_markup          $utils_markup
		 * @property-read utils_math            $utils_math
		 * @property-read utils_php             $utils_php
		 * @property-read utils_queue           $utils_queue
		 * @property-read utils_queue_event_log $utils_queue_event_log
		 * @property-read utils_string          $utils_string
		 * @property-read utils_sub             $utils_sub
		 * @property-read utils_sub_event_log   $utils_sub_event_log
		 * @property-read utils_url             $utils_url
		 * @property-read utils_user            $utils_user
		 *
		 * @since 141111 First documented version.
		 */
		class plugin extends abs_base
		{
			/*
			 * Public Properties
			 */

			/**
			 * Identifies pro version.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var boolean `TRUE` for pro version.
			 */
			public $is_pro = FALSE;

			/**
			 * Plugin name.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Plugin name.
			 */
			public $name = 'Comment Mail';

			/**
			 * Plugin name (abbreviated).
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Plugin name (abbreviated).
			 */
			public $short_name = 'CM';

			/**
			 * Transient prefix.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string 8-character transient prefix.
			 */
			public $transient_prefix = 'cmtmail_';

			/**
			 * Query var prefix.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Query var prefix.
			 */
			public $qv_prefix = 'cm_';

			/**
			 * Site name.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Site name.
			 */
			public $site_name = 'Comment-Mail.com';

			/**
			 * Plugin product page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Plugin product page URL.
			 */
			public $product_url = 'http://comment-mail.com';

			/**
			 * Used by the plugin's uninstall handler.
			 *
			 * @since 141111 Adding uninstall handler.
			 *
			 * @var boolean Defined by constructor.
			 */
			public $enable_hooks;

			/**
			 * Text domain for translations; based on `__NAMESPACE__`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Defined by class constructor; for translations.
			 */
			public $text_domain;

			/**
			 * Plugin slug; based on `__NAMESPACE__`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Defined by constructor.
			 */
			public $slug;

			/**
			 * Stub `__FILE__` location.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Defined by class constructor.
			 */
			public $file;

			/**
			 * Version string in YYMMDD[+build] format.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Current version of the software.
			 */
			public $version = '150709';

			/*
			 * Public Properties (Defined @ Setup)
			 */

			/**
			 * An array of all default option values.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Default options array.
			 */
			public $default_options;

			/**
			 * An array of pro-only option keys.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Default options array.
			 */
			public $pro_only_option_keys;

			/**
			 * Configured option values.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Options configured by site owner.
			 */
			public $options;

			/**
			 * General capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to administer.
			 *    i.e. to use any aspect of the plugin, including the configuration
			 *    of any/all plugin options and/or advanced settings.
			 */
			public $cap; // Most important cap.

			/**
			 * Management capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to manage.
			 *    i.e. to use/manage the plugin from the back-end,
			 *    but NOT to allow for any config. changes.
			 */
			public $manage_cap;

			/**
			 * Auto-recompile capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to auto-recompile.
			 *    i.e. to see notices regarding automatic recompilations
			 *    following an upgrade the plugin files/version.
			 */
			public $auto_recompile_cap;

			/**
			 * Uninstall capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to uninstall.
			 *    i.e. the ability to deactivate and even delete the plugin.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 */
			public function setup()
			{
				/*
				 * Setup already?
				 */
				if(!is_null($setup = &$this->cache_key(__FUNCTION__)))
					return; // Already setup. Once only!
				$setup = TRUE; // Once only please.

				/*
				 * Fire pre-setup hooks.
				 */
				if($this->enable_hooks) // Hooks enabled?
					do_action('before_'.__METHOD__, get_defined_vars());

				/*
				 * Load the plugin's text domain for translations.
				 */
				load_plugin_textdomain($this->text_domain); // Translations.

				/*
				 * Setup class properties related to authentication/capabilities.
				 */
				$this->cap                = apply_filters(__METHOD__.'_cap', 'activate_plugins');
				$this->manage_cap         = apply_filters(__METHOD__.'_manage_cap', 'moderate_comments');
				$this->auto_recompile_cap = apply_filters(__METHOD__.'_auto_recompile_cap', 'activate_plugins');
				$this->uninstall_cap      = apply_filters(__METHOD__.'_uninstall_cap', 'delete_plugins');

				/*
				 * Setup pro-only option keys.
				 */
				$this->pro_only_option_keys = array(
		            # Related to automatic pro updates.

		            'pro_update_check',
		            'last_pro_update_check',

		            'pro_update_username',
		            'pro_update_password',

					# Related to the stats pinger.

					'last_pro_stats_log',

					# Related to SSO.

					'sso_enable',

					'comment_form_sso_template_enable',
					'comment_form_sso_scripts_enable',

					'login_form_sso_template_enable',
					'login_form_sso_scripts_enable',

					'sso_twitter_key',
					'sso_twitter_secret',

					'sso_facebook_key',
					'sso_facebook_secret',

					'sso_google_key',
					'sso_google_secret',

					'sso_linkedin_key',
					'sso_linkedin_secret',

					# Related to SMPT configuration.

					'smtp_enable',

					'smtp_host',
					'smtp_port',
					'smtp_secure',

					'smtp_username',
					'smtp_password',

					'smtp_from_name',
					'smtp_from_email',
					'smtp_reply_to_email',
					'smtp_force_from',

					# Related to replies via email.

					'replies_via_email_enable',
					'replies_via_email_handler',

					'rve_mandrill_reply_to_email',
					'rve_mandrill_max_spam_score',
					'rve_mandrill_spf_check_enable',
					'rve_mandrill_dkim_check_enable',

					# Related to list server integrations.

					'list_server_enable',
					'list_server',

					'list_server_mailchimp_api_key',
					'list_server_mailchimp_list_id',

					# Related to blacklisting.

					'email_blacklist_patterns',

					#Related to performance tuning.

					'queue_processor_max_time',
					'queue_processor_delay',
					'queue_processor_max_limit',
					'queue_processor_realtime_max_limit',

					'sub_cleaner_max_time',
					'unconfirmed_expiration_time',
					'trashed_expiration_time',

					'log_cleaner_max_time',
					'sub_event_log_expiration_time',
					'queue_event_log_expiration_time',

					# Related to IP tracking.

					'prioritize_remote_addr',
					'geo_location_tracking_enable',

					# Related to comment notifications.

					'comment_notification_parent_content_clip_max_chars',
					'comment_notification_content_clip_max_chars',

					# Related to subscription summary.

					'sub_manage_summary_max_limit',

					# Related to select options.

					'post_select_options_enable',
					'post_select_options_media_enable',
					'comment_select_options_enable',
					'user_select_options_enable',
					'max_select_options',

					#Related to menu pages; i.e. logo display.

					'menu_pages_logo_icon_enable',

					# Template-related config. options.

					'template_type',
					'template_syntax_theme' => 'monokai',

					# Advanced HTML, PHP-based templates for the site.

					'template__type_a__site__header___php',
					'template__type_a__site__header_styles___php',
					'template__type_a__site__header_scripts___php',
					'template__type_a__site__header_tag___php',

					'template__type_a__site__footer_tag___php',
					'template__type_a__site__footer___php',

					'template__type_a__site__comment_form__sso_ops___php',
					'template__type_a__site__comment_form__sso_op_scripts___php',

					'template__type_a__site__login_form__sso_ops___php',
					'template__type_a__site__login_form__sso_op_scripts___php',

					'template__type_a__site__sso_actions__complete___php',

					'template__type_a__site__comment_form__sub_ops___php',
					'template__type_a__site__comment_form__sub_op_scripts___php',

					'template__type_a__site__sub_actions__confirmed___php',
					'template__type_a__site__sub_actions__unsubscribed___php',
					'template__type_a__site__sub_actions__unsubscribed_all___php',
					'template__type_a__site__sub_actions__manage_summary___php',
					'template__type_a__site__sub_actions__manage_sub_form___php',
					'template__type_a__site__sub_actions__manage_sub_form_comment_id_row_via_ajax___php',

					# Advanced HTML, PHP-based templates for emails.

					'template__type_a__email__header___php',
					'template__type_a__email__header_styles___php',
					'template__type_a__email__header_scripts___php',
					'template__type_a__email__header_tag___php',

					'template__type_a__email__footer_tag___php',
					'template__type_a__email__footer___php',

					'template__type_a__email__sub_confirmation__subject___php',
					'template__type_a__email__sub_confirmation__message___php',

					'template__type_a__email__comment_notification__subject___php',
					'template__type_a__email__comment_notification__message___php',
				);

				/*
				 * Setup the array of all plugin options.
				 */
				$this->default_options = array(
					# Core/systematic option keys.

					'version'                                                                              => $this->version,
					'crons_setup'                                                                          => '0', // `0` or timestamp.
					'stcr_transition_complete'                                                             => '0', // `0|1` transitioned?

					# Related to data safeguards.

					'uninstall_safeguards_enable'                                                          => '1', // `0|1`; safeguards on?

					# Related to user authentication.

					'manage_cap'                                                                           => $this->manage_cap, // Capability.

		            # Related to automatic pro updates.

		            'pro_update_check'                                                                     => '1', // `0|1`; enable?
		            'last_pro_update_check'                                                                => '0', // Timestamp.

		            'pro_update_username'                                                                  => '', // Username.
		            'pro_update_password'                                                                  => '', // Password or license key.

					# Related to the stats pinger.

					'last_pro_stats_log'                                                                   => '0', // Timestamp.

					/* Low-level switches to enable/disable certain functionalities.
					 *
					 * With the `enable=0` option, here is an overview of what happens:
					 *
					 * • Subscription options no longer appear on comment forms; i.e. no new subscriptions.
					 *    In addition, the ability to add a new subscription through any/all front-end forms
					 *    is disabled too. All back-end functionality remains available however.
					 *
					 * • The queue processor will stop processing, until such time as the plugin is renabled.
					 *    i.e. No more email notifications. Queue injections continue, but no queue processing.
					 *    If it is desirable that any queued notifications NOT be processed at all upon re-enabling,
					 *    a site owner can choose to delete queued notifications in the dashboard before doing so.
					 *
					 * • Even w/ `enable=0`, all other functionality remains while the plugin is enabled in WP.
					 *
					 * The `new_subs_enable` and `queue_processing_enable` options allow for more control over
					 * which of these two functionalities should be enabled/disabled. In some cases it might
					 * be nice to disable queue processing temporarily; allowing everything else to remain as-is.
					 *
					 * Or, a site owner can allow other functionality to remain available, but stop
					 * accepting new subscriptions if they so desire; i.e. by setting `new_subs_enable=0`.
					 *
					 * --------------------------------------------------------------------------------------
					 * The `comment_form_sub_template_enable` option can be turned off if the site owner would like to
					 * implement their own HTML markup for comment subscription options; instead of the built-in template.
					 *
					 * The `comment_form_sub_scripts_enable` option can be turned off if the site owner has decided not to use
					 * the default HTML markup for comment subscription options; i.e. they might not need JavaScript in this case.
					 *    Note that `comment_form_sub_template_enable` must also be disabled for this option to actually work;
					 *    i.e. the default comment form template relies on this; so IT must be off to turn this off.
					 */
					'enable'                                                                               => '0', // `0|1`; enable?
					'new_subs_enable'                                                                      => '1', // `0|1`; enable?
					'queue_processing_enable'                                                              => '1', // `0|1`; enable?
                    'enabled_post_types'                                                                   => 'post', // Comma-delimited post types.

					'comment_form_sub_template_enable'                                                     => '1', // `0|1`; enable?
					'comment_form_sub_scripts_enable'                                                      => '1', // `0|1`; enable?

					'comment_form_default_sub_type_option'                                                 => 'comment', // ``, `comment` or `comments`.
					'comment_form_default_sub_deliver_option'                                              => 'asap', // `asap`, `hourly`, `daily`, `weekly`.

					# Related to SSO and service integrations.

					'sso_enable'                                                                           => '0', // `0|1`; enable?

					'comment_form_sso_template_enable'                                                     => '1', // `0|1`; enable?
					'comment_form_sso_scripts_enable'                                                      => '1', // `0|1`; enable?

					'login_form_sso_template_enable'                                                       => '1', // `0|1`; enable?
					'login_form_sso_scripts_enable'                                                        => '1', // `0|1`; enable?

					'sso_twitter_key'                                                                      => '',
					'sso_twitter_secret'                                                                   => '',
					// See: <https://apps.twitter.com/app/new>

					'sso_facebook_key'                                                                     => '',
					'sso_facebook_secret'                                                                  => '',
					// See: <https://developers.facebook.com/quickstarts/?platform=web>

					'sso_google_key'                                                                       => '',
					'sso_google_secret'                                                                    => '',
					// See: <https://developers.google.com/accounts/docs/OpenIDConnect#getcredentials>

					'sso_linkedin_key'                                                                     => '',
					'sso_linkedin_secret'                                                                  => '',
					// See: <https://www.linkedin.com/secure/developer?newapp=>

					/* Related to CAN-SPAM compliance. */

					'can_spam_postmaster'                                                                  => get_bloginfo('admin_email'),
					'can_spam_mailing_address'                                                             => get_bloginfo('name').'<br />'."\n".
					                                                                                          '123 Somewhere Street<br />'."\n".
					                                                                                          'Attn: Comment Subscriptions<br />'."\n".
					                                                                                          'Somewhere, USA 99999 ~ Ph: 555-555-5555', // CAN-SPAM contact info.
					'can_spam_privacy_policy_url'                                                          => '', // CAN-SPAM privacy policy.

					# Related to auto-subscribe functionality.

					'auto_subscribe_enable'                                                                => '1', // `0|1`; auto-subscribe enable?
					'auto_subscribe_deliver'                                                               => 'asap', // `asap`, `hourly`, `daily`, `weekly`.
                    'auto_subscribe_post_types'                                                            => 'post', // Comma-delimited post types.
					'auto_subscribe_post_author_enable'                                                    => '1', // `0|1`; auto-subscribe post authors?
					'auto_subscribe_recipients'                                                            => '', // Others `;|,` delimited emails.

					/* Auto-confirm functionality and security issues related to this.

					 * Note that turning `auto_confirm_force_enable` on, has the negative side-effect of making it
					 * much more difficult for users to view a summary of their existing subscriptions;
					 * i.e. they won't get a `sub_email` cookie right away via email confirmation.
					 *
					 * The only way they can view a summary of their subscriptions is:
					 *    1. If they're a logged-in user, and the site owner says that `all_wp_users_confirm_email`.
					 *    2. Or, if they click a link to manage their subscription after having received a notification.
					 *       It is at this point that an auto-confirmed subscriber will finally get their cookie.
					 *
					 * For this reason (and for security), it is suggested that `auto_confirm_force_enable=0`,
					 * unless there happens to be a very good reason for doing so. Can't really think of one;
					 * but this option remains nonetheless — just in case it becomes handy for some.
					 *
					 * The second option here: `auto_confirm_if_already_subscribed_u0ip_enable`, is a bit different.
					 * This option does not explicitly enable auto-confirm functionality, it simply states that we will
					 * allow auto-confirmations to occur even whenever there is no reliable user ID to help verify.
					 *
					 * In this case, we can try to match the IP address and auto-confirm that way.
					 * However, since IP addresses can be spoofed, it remains disabled by default as a security measure.
					 * A site owner must turn this on themselves. Note: this option is not necessary (or recommended)
					 * if you require folks to login before leaving a comment. A user ID can be used in this case.
					 *
					 * The final option here is related to our ability to trust the `wp_users` table, or not!
					 * Some sites run plugins that allow users to register and gain immediate access w/o confirmation
					 * being necessary. We assume (by default) that this is the case on every site. A site owner must tell us
					 * explicitly that they force every user to confirm via email before being allowed to log into the site.
					 * Otherwise, we will not trust the email addresses associated with registered users.
					 */
					'auto_confirm_force_enable'                                                            => '0', // `0|1`; auto-confirm enable?
					'auto_confirm_if_already_subscribed_u0ip_enable'                                       => '0', // `0|1`; auto-confirm enable?
					'all_wp_users_confirm_email'                                                           => '0', // WP users confirm their email?

					# Related to email headers.

					'from_name'                                                                            => get_bloginfo('name'), // From: name.
					'from_email'                                                                           => get_bloginfo('admin_email'), // From: <email>.
					'reply_to_email'                                                                       => get_bloginfo('admin_email'), // Reply-To: <email>.

					# Related to SMPT configuration.

					'smtp_enable'                                                                          => '0', // `0|1`; enable?

					'smtp_host'                                                                            => '', // SMTP host name.
					'smtp_port'                                                                            => '465', // SMTP port number.
					'smtp_secure'                                                                          => 'ssl', // ``, `ssl` or `tls`.

					'smtp_username'                                                                        => '', // SMTP username.
					'smtp_password'                                                                        => '', // SMTP password.

					'smtp_from_name'                                                                       => get_bloginfo('name'), // From: name.
					'smtp_from_email'                                                                      => get_bloginfo('admin_email'), // From: <email>.
					'smtp_reply_to_email'                                                                  => get_bloginfo('admin_email'), // Reply-To: <email>.
					'smtp_force_from'                                                                      => '1', // `0|1`; force? Not configurable at this time.

					# Related to replies via email.

					'replies_via_email_enable'                                                             => '0', // `0|1`; enable?
					'replies_via_email_handler'                                                            => '', // `mandrill`.
					// Mandrill is currently the only choice. In the future we may add other options to this list.

					'rve_mandrill_reply_to_email'                                                          => '', // `Reply-To:` address.
					'rve_mandrill_max_spam_score'                                                          => '5.0', // Max allowable spam score.
					'rve_mandrill_spf_check_enable'                                                        => '1', // `0|1|2|3|4`; where `0` = disable.
					'rve_mandrill_dkim_check_enable'                                                       => '1', // `0|1|2`; where `0` = disable.

					# Related to blacklisting.

					'email_blacklist_patterns'                                                             => implode("\n", utils_mail::$role_based_blacklist_patterns),

					# Related to performance tuning.

					'queue_processor_max_time'                                                             => '30', // In seconds.
					'queue_processor_delay'                                                                => '250', // In milliseconds.
					'queue_processor_max_limit'                                                            => '100', // Total queue entries.
					'queue_processor_realtime_max_limit'                                                   => '5', // Total queue entries.

					'sub_cleaner_max_time'                                                                 => '30', // In seconds.
					'unconfirmed_expiration_time'                                                          => '60 days', // `strtotime()` compatible.
					'trashed_expiration_time'                                                              => '60 days', // `strtotime()` compatible.

					'log_cleaner_max_time'                                                                 => '30', // In seconds.
					'sub_event_log_expiration_time'                                                        => '', // `strtotime()` compatible.
					'queue_event_log_expiration_time'                                                      => '', // `strtotime()` compatible.

					# Related to IP tracking.

					'prioritize_remote_addr'                                                               => '0', // `0|1`; enable?
					'geo_location_tracking_enable'                                                         => '0', // `0|1`; enable?

					# Related to meta boxes.

					'excluded_meta_box_post_types'                                                         => 'link,comment,revision,attachment,nav_menu_item,snippet,redirect',

					# Related to comment notifications.

					'comment_notification_parent_content_clip_max_chars'                                   => '100', // Max chars to include in notifications.
					'comment_notification_content_clip_max_chars'                                          => '200', // Max chars to include in notifications.

					# Related to subscription summary.

					'sub_manage_summary_max_limit'                                                         => '25', // Subscriptions per page.

					# Related to select options.

					'post_select_options_enable'                                                           => '1', // `0|1`; enable?
					'post_select_options_media_enable'                                                     => '0', // `0|1`; enable?
					'comment_select_options_enable'                                                        => '1', // `0|1`; enable?
					'user_select_options_enable'                                                           => '1', // `0|1`; enable?
					'max_select_options'                                                                   => '2000', // Max options.

					# Related to menu pages; i.e. logo display.

					'menu_pages_logo_icon_enable'                                                          => '1', // `0|1`; display?

					/* Related to branding; i.e. powered by Comment Mail™ notes.
					~ IMPORTANT: please see <https://wordpress.org/plugins/about/guidelines/>
					#10. The plugin must NOT embed external links on the public site (like a "powered by" link) without
					explicitly asking the user's permission. Any such options in the plugin must default to NOT show the link. */

					'email_footer_powered_by_enable'                                                       => '0', // `0|1`; enable?
					'site_footer_powered_by_enable'                                                        => '0', // `0|1`; enable?

					# Template-related config. options.

					'template_type'                                                                        => 's', // `a|s`.

					# Simple snippet-based templates for the site.

					'template__type_s__site__snippet__header_tag___php'                                    => '', // HTML code.
					'template__type_s__site__snippet__footer_tag___php'                                    => '', // HTML code.

					'template__type_s__site__login_form__snippet__sso_ops___php'                           => '', // HTML code.

					'template__type_s__site__comment_form__snippet__sso_ops___php'                         => '', // HTML code.
					'template__type_s__site__comment_form__snippet__sub_ops___php'                         => '', // HTML code.

					'template__type_s__site__sub_actions__snippet__confirmed___php'                        => '', // HTML code.
					'template__type_s__site__sub_actions__snippet__unsubscribed___php'                     => '', // HTML code.
					'template__type_s__site__sub_actions__snippet__unsubscribed_all___php'                 => '', // HTML code.

					# Advanced HTML, PHP-based templates for the site.

					'template__type_a__site__header___php'                                                 => '', // HTML/PHP code.
					'template__type_a__site__header_styles___php'                                          => '', // HTML/PHP code.
					'template__type_a__site__header_scripts___php'                                         => '', // HTML/PHP code.
					'template__type_a__site__header_tag___php'                                             => '', // HTML/PHP code.

					'template__type_a__site__footer_tag___php'                                             => '', // HTML/PHP code.
					'template__type_a__site__footer___php'                                                 => '', // HTML/PHP code.

					'template__type_a__site__comment_form__sso_ops___php'                                  => '', // HTML/PHP code.
					'template__type_a__site__comment_form__sso_op_scripts___php'                           => '', // HTML/PHP code.

					'template__type_a__site__login_form__sso_ops___php'                                    => '', // HTML/PHP code.
					'template__type_a__site__login_form__sso_op_scripts___php'                             => '', // HTML/PHP code.

					'template__type_a__site__sso_actions__complete___php'                                  => '', // HTML/PHP code.

					'template__type_a__site__comment_form__sub_ops___php'                                  => '', // HTML/PHP code.
					'template__type_a__site__comment_form__sub_op_scripts___php'                           => '', // HTML/PHP code.

					'template__type_a__site__sub_actions__confirmed___php'                                 => '', // HTML/PHP code.
					'template__type_a__site__sub_actions__unsubscribed___php'                              => '', // HTML/PHP code.
					'template__type_a__site__sub_actions__unsubscribed_all___php'                          => '', // HTML/PHP code.
					'template__type_a__site__sub_actions__manage_summary___php'                            => '', // HTML/PHP code.
					'template__type_a__site__sub_actions__manage_sub_form___php'                           => '', // HTML/PHP code.
					'template__type_a__site__sub_actions__manage_sub_form_comment_id_row_via_ajax___php'   => '', // HTML/PHP code.

					# Simple snippet-based templates for emails.

					'template__type_s__email__snippet__header_tag___php'                                   => '', // HTML code.
					'template__type_s__email__snippet__footer_tag___php'                                   => '', // HTML code.

					'template__type_s__email__sub_confirmation__snippet__subject___php'                    => '', // HTML code.
					'template__type_s__email__sub_confirmation__snippet__message___php'                    => '', // HTML code.

					'template__type_s__email__comment_notification__snippet__subject___php'                => '', // HTML code.
					'template__type_s__email__comment_notification__snippet__message_heading___php'        => '', // HTML code.
					'template__type_s__email__comment_notification__snippet__message_in_response_to___php' => '', // HTML code.
					'template__type_s__email__comment_notification__snippet__message_reply_from___php'     => '', // HTML code.
					'template__type_s__email__comment_notification__snippet__message_comment_from___php'   => '', // HTML code.

					# Advanced HTML, PHP-based templates for emails.

					'template__type_a__email__header___php'                                                => '', // HTML/PHP code.
					'template__type_a__email__header_styles___php'                                         => '', // HTML/PHP code.
					'template__type_a__email__header_scripts___php'                                        => '', // HTML/PHP code.
					'template__type_a__email__header_tag___php'                                            => '', // HTML/PHP code.

					'template__type_a__email__footer_tag___php'                                            => '', // HTML/PHP code.
					'template__type_a__email__footer___php'                                                => '', // HTML/PHP code.

					'template__type_a__email__sub_confirmation__subject___php'                             => '', // HTML/PHP code.
					'template__type_a__email__sub_confirmation__message___php'                             => '', // HTML/PHP code.

					'template__type_a__email__comment_notification__subject___php'                         => '', // HTML/PHP code.
					'template__type_a__email__comment_notification__message___php'                         => '', // HTML/PHP code.

				); // Default options are merged with those defined by the site owner.
				$this->default_options = apply_filters(__METHOD__.'_default_options', $this->default_options); // Allow filters.
				$this->options         = is_array($this->options = get_option(__NAMESPACE__.'_options')) ? $this->options : array();

				$this->options = array_merge($this->default_options, $this->options); // Merge into default options.
				$this->options = array_intersect_key($this->options, $this->default_options); // Valid keys only.
				$this->options = apply_filters(__METHOD__.'_options', $this->options); // Allow filters.
				$this->options = array_map('strval', $this->options); // Force string values.

				if($this->options['manage_cap']) // This can be altered by plugin config. options.
					$this->manage_cap = apply_filters(__METHOD__.'_manage_cap', $this->options['manage_cap']);

				if(!$this->options['auto_confirm_force_enable'])
					$this->options['all_wp_users_confirm_email'] = '0';

				require_once dirname(__FILE__).'/includes/api.php';
				require_once dirname(__FILE__).'/includes/stcr.php';

				foreach($this->pro_only_option_keys as $_key)
					$this->options[$_key] = $this->default_options[$_key];
				unset($_key); // Housekeeping.

				/*
				 * With or without hooks?
				 */
				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; setup without hooks.

				/*
				 * Setup all secondary plugin hooks.
				 */
				add_action('init', array($this, 'actions'), -10);
				add_action('init', array($this, 'stcr_check'), 100);
				add_action('init', array($this, 'jetpack_check'), 100);

				add_action('admin_init', array($this, 'check_version'), 10);
				add_action('all_admin_notices', array($this, 'all_admin_notices'), 10);

				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'), 10);
				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 10);

				add_action('admin_menu', array($this, 'add_menu_pages'), 10);
				add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
				add_filter('plugin_action_links_'.plugin_basename($this->file), array($this, 'add_settings_link'), 10, 1);

				add_action('init', array($this, 'comment_shortlink_redirect'), -11);

				add_action('wp_print_scripts', array($this, 'enqueue_front_scripts'), 10);

				add_action('login_form', array($this, 'login_form'), 5, 0); // Ideal choice.
				add_action('login_footer', array($this, 'login_form'), 5, 0); // Secondary fallback.

				add_action('transition_post_status', array($this, 'post_status'), 10, 3);
				add_action('before_delete_post', array($this, 'post_delete'), 10, 1);

				add_action('comment_form_must_log_in_after', array($this, 'comment_form_must_log_in_after'), 5, 0);
				add_action('comment_form_top', array($this, 'comment_form_must_log_in_after'), 5, 0); // Secondary fallback.

				add_filter('comment_form_field_comment', array($this, 'comment_form_filter_append'), 5, 1);
				add_action('comment_form', array($this, 'comment_form'), 5, 0); // Secondary fallback.

				add_action('comment_post', array($this, 'comment_post'), 10, 2);
				add_action('transition_comment_status', array($this, 'comment_status'), 10, 3);

				add_filter('pre_option_comment_registration', array($this, 'pre_option_comment_registration'), 1000, 1);
				add_filter('pre_comment_approved', array($this, 'pre_comment_approved'), 1000, 2);

				add_action('user_register', array($this, 'user_register'), 10, 1);
				add_action('delete_user', array($this, 'user_delete'), 10, 1);
				add_action('wpmu_delete_user', array($this, 'user_delete'), 10, 1);
				add_action('remove_user_from_blog', array($this, 'user_delete'), 10, 2);

				add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10);

				/*
				 * Setup CRON-related hooks.
				 */
				add_filter('cron_schedules', array($this, 'extend_cron_schedules'), 10, 1);

				if((integer)$this->options['crons_setup'] < 1382523750)
				{
					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_queue_processor');
					wp_schedule_event(time() + 60, 'every5m', '_cron_'.__NAMESPACE__.'_queue_processor');

					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_sub_cleaner');
					wp_schedule_event(time() + 60, 'hourly', '_cron_'.__NAMESPACE__.'_sub_cleaner');

					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_log_cleaner');
					wp_schedule_event(time() + 60, 'hourly', '_cron_'.__NAMESPACE__.'_log_cleaner');

					$this->options['crons_setup'] = (string)time();
					update_option(__NAMESPACE__.'_options', $this->options);
				}
				add_action('_cron_'.__NAMESPACE__.'_queue_processor', array($this, 'queue_processor'), 10);
				add_action('_cron_'.__NAMESPACE__.'_sub_cleaner', array($this, 'sub_cleaner'), 10);
				add_action('_cron_'.__NAMESPACE__.'_log_cleaner', array($this, 'log_cleaner'), 10);

				/*
				 * Fire setup completion hooks.
				 */
				do_action('after_'.__METHOD__, get_defined_vars());
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
			 * First installation time.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return integer UNIX timestamp.
			 */
			public function install_time()
			{
				return (integer)get_option(__NAMESPACE__.'_install_time');
			}

			/**
			 * Plugin activation hook.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `init` action.
			 */
			public function actions()
			{
				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do here.

				new actions(); // Handle action(s).
			}

			/*
			 * Conflict-relatd methods.
			 */

			/**
			 * Check for StCR conflict(s).
			 *
			 * @since 150626 Improving StCR compat.
			 */
			public function stcr_check()
			{
				if(!$this->options['enable'])
					return; // Not applicable.

				if(!class_exists('wp_subscribe_reloaded'))
					return; // Nothing to do here.

				if(!is_admin() || !empty($_REQUEST['action']))
					return; // Stay quiet in this case.

				$conflict = sprintf(__('<p style="font-size:120%%; font-weight:400; margin:0;"><strong>%1$s&trade;</strong> + <strong>Subscribe to Comments Reloaded</strong> = Possible Conflict!</p>', $this->text_domain), esc_html($this->name));
				$conflict .= '<p style="margin:0;">'.sprintf(__('<strong>WARNING (ACTION REQUIRED):</strong> Running %1$s&trade; while Subscribe to Comments Reloaded is <em>also</em> an active WordPress plugin <strong>can cause problems</strong>; i.e., these two plugins do the same thing—%1$s being the newer of the two. We recommend keeping %1$s; please deactivate the Subscribe to Comments Reloaded plugin to get rid of this message.', $this->text_domain), esc_html($this->name)).'</p>';
				$this->enqueue_error($conflict);
			}

			/**
			 * Check for Jetpack conflict(s).
			 *
			 * @since 150626 Improving Jetpack compat.
			 */
			public function jetpack_check()
			{
				if(!$this->options['enable'])
					return; // Not applicable.

				if(!class_exists('Jetpack_Subscriptions'))
					return; // Nothing to do here.

				if(/* !get_option('stb_enabled') && */ !get_option('stc_enabled', 1))
					return; // Nothing to do here.

				if(!is_admin() || !empty($_REQUEST['action']))
					return; // Stay quiet in this case.

				$conflict = sprintf(__('<p style="font-size:120%%; font-weight:400; margin:0;"><strong>%1$s&trade;</strong> + <strong>Jetpack Subscriptions module</strong> (with Follow Comments enabled) = Possible Conflict!</p>', $this->text_domain), esc_html($this->name));
				$conflict .= '<p style="margin:0;">'.sprintf(__('<strong>WARNING (ACTION REQUIRED):</strong> Running %1$s&trade; while the Jetpack Subscriptions module (with Follow Comments enabled) is <em>also</em> active in WordPress <strong>can cause problems</strong>; i.e., these two handle the same thing—%1$s being the newer of the two. We recommend keeping %1$s; please deactivate the Follow Comments functionality in the Jetpack Subscriptions module to get rid of this message (see <strong>Dashboard → Settings → Discussion → Jetpack Subscriptions Settings</strong>).', $this->text_domain), esc_html($this->name)).'</p>';
				$this->enqueue_error($conflict);
			}

			/*
			 * Option-Related Methods
			 */

			/**
			 * Saves new plugin options.
			 *
			 * @since 150227 Improving GitHub API Recursion.
			 *
			 * @param array $options An array of new plugin options.
			 */
			public function options_quick_save(array $options)
			{
				$this->options = array_merge($this->default_options, $this->options, $options);

				foreach($this->pro_only_option_keys as $_key)
					$this->options[$_key] = $this->default_options[$_key];
				unset($_key); // Housekeeping.

				$this->options = array_intersect_key($this->options, $this->default_options);
				$this->options = array_map('strval', $this->options); // Force strings.

				update_option(__NAMESPACE__.'_options', $this->options); // DB update.
			}

			/**
			 * Saves new plugin options.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $options An array of new plugin options.
			 */
			public function options_save(array $options)
			{
				$this->options = array_merge($this->default_options, $this->options, $options);

				foreach($this->pro_only_option_keys as $_key)
					$this->options[$_key] = $this->default_options[$_key];
				unset($_key); // Housekeeping.

				$this->options = array_intersect_key($this->options, $this->default_options);
				$this->options = array_map('strval', $this->options); // Force strings.

				foreach($this->options as $_key => &$_value) if(strpos($_key, 'template__') === 0)
				{
					$_key_data = template::option_key_data($_key);
					if($_key_data->type === 'a') continue; // Not included with lite version.
					$_default_template     = new template($_key_data->file, $_key_data->type, TRUE);
					$_default_template_nws = preg_replace('/\s+/', '', $_default_template->file_contents());
					$_option_template_nws  = preg_replace('/\s+/', '', $_value);

					if($_option_template_nws === $_default_template_nws)
						$_value = ''; // Empty; it's a default value.
				}
				unset($_key, $_key_data, $_value, // Housekeeping.
					$_default_template, $_option_template_nws, $_default_template_nws);

				update_option(__NAMESPACE__.'_options', $this->options); // DB update.
			}

			/*
			 * Admin Meta-Box-Related Methods
			 */

			/**
			 * Adds plugin meta boxes.
			 *
			 * @since 141111 First documented version.
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

                $post_type = strtolower((string)$post_type);

                $enabled_post_types = strtolower($this->options['enabled_post_types']);
                $enabled_post_types = preg_split('/[\s;,]+/', $enabled_post_types, NULL, PREG_SPLIT_NO_EMPTY);

                if($enabled_post_types && !in_array($post_type, $enabled_post_types, TRUE))
                    return; // Ignore; not enabled for this post type.

                $excluded_post_types = strtolower($this->options['excluded_meta_box_post_types']);
                $excluded_post_types = preg_split('/[\s;,]+/', $excluded_post_types, NULL, PREG_SPLIT_NO_EMPTY);

                if(in_array($post_type, $excluded_post_types, TRUE))
                    return; // Ignore; this post type excluded.

				// Meta boxes use an SVG graphic.
				$icon = $this->utils_fs->inline_icon_svg();

				if(!$this->utils_env->is_menu_page('post-new.php'))
					add_meta_box(__NAMESPACE__.'_small', $icon.' '.$this->name.'&trade;', array($this, 'post_small_meta_box'), $post_type, 'side', 'high');

				// @TODO disabling this for now.
				//add_meta_box(__NAMESPACE__.'_large', $icon.' '.$this->name.'&trade; '.__('Subscriptions', $this->text_domain),
				//             array($this, 'post_large_meta_box'), $post_type, 'normal', 'high');
			}

			/**
			 * Builds small meta box for this plugin.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` action.
			 */
			public function enqueue_admin_styles()
			{
				if($this->utils_env->is_menu_page('post.php')
				   || $this->utils_env->is_menu_page('post-new.php')
				) $this->_enqueue_post_admin_styles();

				if(!$this->utils_env->is_menu_page(__NAMESPACE__.'*'))
					return; // Nothing to do; not applicable.

				$deps = array('codemirror', 'jquery-datetimepicker', 'chosen', 'font-awesome', 'sharkicons'); // Dependencies.

				wp_enqueue_style('codemirror', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/codemirror.min.css'), array(), NULL, 'all');
				wp_enqueue_style('codemirror-fullscreen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/display/fullscreen.min.css'), array('codemirror'), NULL, 'all');
				wp_enqueue_style('codemirror-'.$this->options['template_syntax_theme'].'-theme', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/theme/'.urlencode($this->options['template_syntax_theme']).'.min.css'), array('codemirror'), NULL, 'all');

				wp_enqueue_style('jquery-datetimepicker', $this->utils_url->to('/submodules/datetimepicker/jquery.datetimepicker.css'), array(), NULL, 'all');
				wp_enqueue_style('chosen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css'), array(), NULL, 'all');

				wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), array(), NULL, 'all');
				wp_enqueue_style('sharkicons', $this->utils_url->to('/submodules/sharkicons/src/styles.min.css'), array(), NULL, 'all');

				wp_enqueue_style(__NAMESPACE__, $this->utils_url->to('/client-s/css/menu-pages.min.css'), $deps, $this->version, 'all');
			}

			/**
			 * Adds CSS for administrative menu pages.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` action indirectly.
			 */
			public function _enqueue_post_admin_styles()
			{
				if(!$this->utils_env->is_menu_page('post.php')
				   && !$this->utils_env->is_menu_page('post-new.php')
				) return; // Not applicable.

				$deps = array('font-awesome', 'sharkicons'); // Dependencies.

				wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), array(), NULL, 'all');
				wp_enqueue_style('sharkicons', $this->utils_url->to('/submodules/sharkicons/src/styles.min.css'), array(), NULL, 'all');

				wp_enqueue_style(__NAMESPACE__, $this->utils_url->to('/client-s/css/menu-pages.min.css'), $deps, $this->version, 'all');
			}

			/**
			 * Adds JS for administrative menu pages.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` action.
			 */
			public function enqueue_admin_scripts()
			{
				if(!$this->utils_env->is_menu_page(__NAMESPACE__.'*'))
					return; // Nothing to do; NOT a plugin menu page.

				$deps = array('jquery', 'postbox', 'codemirror', 'google-jsapi-modules', 'chartjs', 'jquery-datetimepicker', 'chosen'); // Dependencies.

				wp_enqueue_script('codemirror', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/codemirror.min.js'), array(), NULL, TRUE);
				wp_enqueue_script('codemirror-fullscreen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/display/fullscreen.min.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-matchbrackets', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/edit/matchbrackets.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-htmlmixed', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/htmlmixed/htmlmixed.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-xml', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/xml/xml.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-javascript', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/javascript/javascript.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-css', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/css/css.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-clike', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/clike/clike.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-php', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/php/php.js'), array('codemirror'), NULL, TRUE);

				$google_jsapi_modules = "{'modules':[{'name':'visualization','version':'1','packages':['geochart']}]}";
				wp_enqueue_script('google-jsapi-modules', set_url_scheme('//www.google.com/jsapi?autoload='.urlencode($google_jsapi_modules)), array(), NULL, TRUE);

				wp_enqueue_script('chartjs', set_url_scheme('//cdn.jsdelivr.net/chart.js/1.0.1-beta.4/Chart.min.js'), array(), NULL, TRUE);
				wp_enqueue_script('jquery-datetimepicker', $this->utils_url->to('/submodules/datetimepicker/jquery.datetimepicker.js'), array('jquery'), NULL, TRUE);
				wp_enqueue_script('chosen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js'), array('jquery'), NULL, TRUE);

				wp_enqueue_script(__NAMESPACE__, $this->utils_url->to('/client-s/js/menu-pages.min.js'), $deps, $this->version, TRUE);

				wp_localize_script(__NAMESPACE__, __NAMESPACE__.'_vars', array(
					'pluginUrl'    => rtrim($this->utils_url->to('/'), '/'),
					'ajaxEndpoint' => rtrim($this->utils_url->page_nonce_only(), '/'),
					'templateSyntaxTheme' => $this->options['template_syntax_theme'],
				));
				wp_localize_script(__NAMESPACE__, __NAMESPACE__.'_i18n', array(
					'bulkReconfirmConfirmation' => __('Resend email confirmation link? Are you sure?', $this->text_domain),
					'bulkDeleteConfirmation'    => $this->utils_env->is_menu_page('*_event_log')
						? $this->utils_i18n->log_entry_js_deletion_confirmation_warning()
						: __('Delete permanently? Are you sure?', $this->text_domain),
					'dateTimePickerI18n'        => array('en' => array(
						'months'    => array(
							__('January', $this->text_domain),
							__('February', $this->text_domain),
							__('March', $this->text_domain),
							__('April', $this->text_domain),
							__('May', $this->text_domain),
							__('June', $this->text_domain),
							__('July', $this->text_domain),
							__('August', $this->text_domain),
							__('September', $this->text_domain),
							__('October', $this->text_domain),
							__('November', $this->text_domain),
							__('December', $this->text_domain),
						),
						'dayOfWeek' => array(
							__('Sun', $this->text_domain),
							__('Mon', $this->text_domain),
							__('Tue', $this->text_domain),
							__('Wed', $this->text_domain),
							__('Thu', $this->text_domain),
							__('Fri', $this->text_domain),
							__('Sat', $this->text_domain),
						),
					)),
				));
			}

			/**
			 * Creates admin menu pages.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_menu` action.
			 */
			public function add_menu_pages()
			{
				if(!current_user_can($this->manage_cap))
					if(!current_user_can($this->cap))
						return; // Do not add meta boxes.

				// Menu page icon uses an SVG graphic.
				$icon = $this->utils_fs->inline_icon_svg();
				$icon = $this->utils_markup->color_svg_menu_icon($icon);

				$divider = // Dividing line used by various menu items below.
					'<span style="display:block; padding:0; margin:0 0 12px 0; height:1px; line-height:1px; background:#CCCCCC; opacity:0.1;"></span>';

				$child_branch_indent = // Each child branch uses the following UTF-8 char `꜖`; <http://unicode-table.com/en/A716/>.
					'<span style="display:inline-block; margin-left:.5em; position:relative; top:-.2em; left:-.2em; font-weight:normal; opacity:0.2;">&#42774;</span> ';

				$current_menu_page = $this->utils_env->current_menu_page(); // Current menu page slug.

				// Menu page titles use UTF-8 char: `⥱`; <http://unicode-table.com/en/2971/>.

				/* ----------------------------------------- */

				$_menu_title                          = $this->name;
				$_page_title                          = $this->name.'&trade;';
				$_menu_position                       = apply_filters(__METHOD__.'_position', '25.00001');
				$this->menu_page_hooks[__NAMESPACE__] = add_menu_page($_page_title, $_menu_title, $this->cap, __NAMESPACE__, array($this, 'menu_page_options'), 'data:image/svg+xml;base64,'.base64_encode($icon), $_menu_position);
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__], array($this, 'menu_page_options_screen'));

				unset($_menu_title, $_page_title, $_menu_position); // Housekeeping.

				/* ----------------------------------------- */

				$_menu_title = __('Config. Options', $this->text_domain);
				$_page_title = $this->name.'&trade; &#10609; '.__('Config. Options', $this->text_domain);
				add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->cap, __NAMESPACE__, array($this, 'menu_page_options'));

				$_menu_title                                           = // Visible on-demand only.
					'<small><em>'.$child_branch_indent.__('Import/Export', $this->text_domain).'</em></small>';
				$_page_title                                           = $this->name.'&trade; &#10609; '.__('Import/Export', $this->text_domain);
				$_menu_parent                                          = $current_menu_page === __NAMESPACE__.'_import_export' ? __NAMESPACE__ : NULL;
				$this->menu_page_hooks[__NAMESPACE__.'_import_export'] = add_submenu_page($_menu_parent, $_page_title, $_menu_title, $this->cap, __NAMESPACE__.'_import_export', array($this, 'menu_page_import_export'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_import_export'], array($this, 'menu_page_import_export_screen'));

				$_menu_title                                             = // Visible on-demand only.
					'<small><em>'.$child_branch_indent.__('Email Templates', $this->text_domain).'</em></small>';
				$_page_title                                             = $this->name.'&trade; &#10609; '.__('Email Templates', $this->text_domain);
				//$_menu_parent                                            = $current_menu_page === __NAMESPACE__.'_email_templates' ? __NAMESPACE__ : NULL;
				$this->menu_page_hooks[__NAMESPACE__.'_email_templates'] = add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->cap, __NAMESPACE__.'_email_templates', array($this, 'menu_page_email_templates'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_email_templates'], array($this, 'menu_page_email_templates_screen'));

				$_menu_title                                            = // Visible on-demand only.
					'<small><em>'.$child_branch_indent.__('Site Templates', $this->text_domain).'</em></small>';
				$_page_title                                            = $this->name.'&trade; &#10609; '.__('Site Templates', $this->text_domain);
				//$_menu_parent                                           = $current_menu_page === __NAMESPACE__.'_site_templates' ? __NAMESPACE__ : NULL;
				$this->menu_page_hooks[__NAMESPACE__.'_site_templates'] = add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->cap, __NAMESPACE__.'_site_templates', array($this, 'menu_page_site_templates'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_site_templates'], array($this, 'menu_page_site_templates_screen'));

				unset($_menu_title, $_page_title, $_menu_parent); // Housekeeping.

				/* ----------------------------------------- */

				$_menu_title                                  = $divider.__('Subscriptions', $this->text_domain);
				$_page_title                                  = $this->name.'&trade; &#10609; '.__('Subscriptions', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_subs'] = add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->manage_cap, __NAMESPACE__.'_subs', array($this, 'menu_page_subs'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_subs'], array($this, 'menu_page_subs_screen'));

				$_menu_title                                           = $child_branch_indent.__('Event Log', $this->text_domain);
				$_page_title                                           = $this->name.'&trade; &#10609; '.__('Sub. Event Log', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_sub_event_log'] = add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->manage_cap, __NAMESPACE__.'_sub_event_log', array($this, 'menu_page_sub_event_log'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_sub_event_log'], array($this, 'menu_page_sub_event_log_screen'));

				unset($_menu_title, $_page_title); // Housekeeping.

				/* ----------------------------------------- */

				$_menu_title                                   = $divider.__('Mail Queue', $this->text_domain);
				$_page_title                                   = $this->name.'&trade; &#10609; '.__('Mail Queue', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_queue'] = add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->manage_cap, __NAMESPACE__.'_queue', array($this, 'menu_page_queue'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_queue'], array($this, 'menu_page_queue_screen'));

				$_menu_title                                             = $child_branch_indent.__('Event Log', $this->text_domain);
				$_page_title                                             = $this->name.'&trade; &#10609; '.__('Queue Event Log', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__.'_queue_event_log'] = add_submenu_page(__NAMESPACE__, $_page_title, $_menu_title, $this->manage_cap, __NAMESPACE__.'_queue_event_log', array($this, 'menu_page_queue_event_log'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_queue_event_log'], array($this, 'menu_page_queue_event_log_screen'));

				unset($_menu_title, $_page_title); // Housekeeping.
			}

			/**
			 * Set plugin-related screen options.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
					'default' => '20', // Default items per page.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
					'default' => '20', // Default items per page.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
					'default' => '20', // Default items per page.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
					'default' => '20', // Default items per page.
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
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_queue_event_log()
			{
				new menu_page('queue_event_log');
			}

			/**
			 * Menu page screen; for import/export.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_import_export']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_import_export_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_import_export'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_import_export']
				) return; // Not applicable.

				return; // No screen for this page right now.
			}

			/**
			 * Menu page for import/export.
			 *
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_import_export()
			{
				new menu_page('import_export');
			}

			/**
			 * Menu page screen; for email templates.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_email_templates']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_email_templates_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_email_templates'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_email_templates']
				) return; // Not applicable.

				return; // No screen for this page right now.
			}

			/**
			 * Menu page for email templates.
			 *
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_email_templates()
			{
				new menu_page('email_templates');
			}

			/**
			 * Menu page screen; for site templates.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_site_templates']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_site_templates_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_site_templates'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_site_templates']
				) return; // Not applicable.

				return; // No screen for this page right now.
			}

			/**
			 * Menu page for site templates.
			 *
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_site_templates()
			{
				new menu_page('site_templates');
			}

			/**
			 * Adds link(s) to plugin row on the WP plugins page.
			 *
			 * @since 141111 First documented version.
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
				if(!$this->is_pro) $links[] = '<a href="'.esc_attr($this->utils_url->pro_preview()).'">'.__('Preview Pro Features', $this->text_domain).'</a>';
				if(!$this->is_pro) $links[] = '<a href="'.esc_attr($this->utils_url->product_page()).'" target="_blank">'.__('Upgrade', $this->text_domain).'</a>';

				return apply_filters(__METHOD__, $links, get_defined_vars());
			}

			/*
			 * Admin Notice/Error Related Methods
			 */

			/**
			 * Enqueue an administrative notice.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $markup HTML markup containing the notice itself.
			 * @param array  $args An array of additional args; i.e. presentation/style.
			 */
			public function enqueue_notice($markup, array $args = array())
			{
				if(!($markup = trim((string)$markup)))
					return; // Nothing to do here.

				$default_args   = array(
					'markup'        => '',
					'requires_cap'  => '',
					'for_user_id'   => 0,
					'for_page'      => '',
					'persistent'    => FALSE,
					'persistent_id' => '',
					'transient'     => FALSE,
					'push_to_top'   => FALSE,
					'type'          => 'notice',
				);
				$args['markup'] = (string)$markup; // + markup.
				$args           = array_merge($default_args, $args);
				$args           = array_intersect_key($args, $default_args);

				$args['requires_cap'] = trim((string)$args['requires_cap']);
				$args['requires_cap'] = $args['requires_cap'] // Force valid format.
					? strtolower(preg_replace('/\W/', '_', $args['requires_cap'])) : '';

				$args['for_user_id'] = (integer)$args['for_user_id'];
				$args['for_page']    = trim((string)$args['for_page']);

				$args['persistent']     = (boolean)$args['persistent'];
				$args['persistent_id']  = (string)$args['persistent_id'];
				$args['transient']      = (boolean)$args['transient'];
				$args['push_to_top']    = (boolean)$args['push_to_top'];

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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
						'markup'        => '',
						'requires_cap'  => '',
						'for_user_id'   => 0,
						'for_page'      => '',
						'persistent'    => FALSE,
						'persistent_id' => '',
						'transient'     => FALSE,
						'push_to_top'   => FALSE,
						'type'          => 'notice',
					);
					$_args        = array_merge($default_args, $_args);
					$_args        = array_intersect_key($_args, $default_args);

					$_args['markup'] = trim((string)$_args['markup']);

					$_args['requires_cap'] = trim((string)$_args['requires_cap']);
					$_args['requires_cap'] = $_args['requires_cap'] // Force valid format.
						? strtolower(preg_replace('/\W/', '_', $_args['requires_cap'])) : '';

					$_args['for_user_id'] = (integer)$_args['for_user_id'];
					$_args['for_page']    = trim((string)$_args['for_page']);

					$_args['persistent']     = (boolean)$_args['persistent'];
					$_args['persistent_id']  = (string)$_args['persistent_id'];
					$_args['transient']      = (boolean)$_args['transient'];
					$_args['push_to_top']    = (boolean)$_args['push_to_top'];

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
							$_dismiss_style = 'float: right;'.
							                  'margin: 0 0 0 15px;'.
							                  'display: inline-block;'.
							                  'text-decoration: none;'.
							                  'font-weight: bold;';
							$_dismiss_url   = $this->utils_url->dismiss_notice($_key);
							$_dismiss       = '<a href="'.esc_attr($_dismiss_url).'"'.
							                  '  style="'.esc_attr($_dismiss_style).'">'.
							                  '  '.__('dismiss &times;', $this->text_domain).
							                  '</a>';
						}
						else $_dismiss = ''; // Default value; n/a.

						$_classes = $this->slug.'-menu-page-area'; // Always.
						$_classes .= ' '.($_args['type'] === 'error' ? 'error' : 'updated');

						$_full_markup = // Put together the full markup; including other pieces.
							'<div class="'.esc_attr($_classes).'">'.
							'  '.$this->utils_string->p_wrap($_args['markup'], $_dismiss).
							'</div>';
						echo apply_filters(__METHOD__.'_notice', $_full_markup, get_defined_vars());
					}
					if(!$_args['persistent']) unset($notices[$_key]); // Once only; i.e. don't show again.
				}
				unset($_key, $_args, $_dismiss_style, $_dismiss_url, $_dismiss, $_classes, $_full_markup); // Housekeeping.

				if($original_notices !== $notices) update_option(__NAMESPACE__.'_notices', $notices);
			}

			/*
			 * Front-Side Scripts
			 */

			/**
			 * Enqueues front-side scripts.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `wp_print_scripts` hook.
			 */
			public function enqueue_front_scripts()
			{
				new front_scripts();
			}

			/*
			 * Login-Related Methods
			 */

			/**
			 * Login form integration.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `login_form` hook.
			 * @attaches-to `login_footer` as a secondary fallback.
			 */
			public function login_form()
			{
				if(!is_null($fired = &$this->static_key(__FUNCTION__)))
					return; // We only handle this for a single hook.
				// The first hook to fire this will win automatically.

				$fired = TRUE; // Flag as `TRUE` now.

				new login_form_after();
			}

			/*
			 * Post-Related Methods
			 */

			/**
			 * Post status handler.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * Comment form login integration.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `comment_form_must_log_in_after` hook.
			 * @attaches-to `comment_form_top` as a secondary fallback.
			 */
			public function comment_form_must_log_in_after()
			{
				if(!is_null($fired = &$this->static_key(__FUNCTION__)))
					return; // We only handle this for a single hook.
				// The first hook to fire this will win automatically.

				$fired = TRUE; // Flag as `TRUE` now.

				new comment_form_login();
			}

			/**
			 * Comment form integration; via filter.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `comment_form_field_comment` filter.
			 *
			 * @param mixed $value Value passed in by a filter.
			 *
			 * @return mixed The `$value`; possibly filtered here.
			 */
			public function comment_form_filter_append($value)
			{
				if(!is_null($fired = &$this->static_key('comment_form')))
					return $value; // We only handle this for a single hook.
				// The first hook to fire this will win automatically.

				if(is_string($value))
				{
					$fired = TRUE; // Flag as `TRUE` now.

					ob_start(); // Output buffer.
					new comment_form_after();
					$value .= ob_get_clean();
				}
				return $value;
			}

			/**
			 * Comment form integration.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `comment_form` action.
			 */
			public function comment_form()
			{
				if(!is_null($fired = &$this->static_key(__FUNCTION__)))
					return; // We only handle this for a single hook.
				// The first hook to fire this will win automatically.

				$fired = TRUE; // Flag as `TRUE` now.

				new comment_form_after();
			}

			/**
			 * Comment post handler.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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

			/**
			 * Filters `comment_registration` option in WordPress.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `pre_option_comment_registration` filter.
			 *
			 * @param integer|string|boolean $registration_required `FALSE` if not yet defined by another filter.
			 *
			 * @return integer|string|boolean Filtered `$comment_registration` value.
			 */
			public function pre_option_comment_registration($registration_required)
			{
				return $registration_required; // Pass through.
			}

			/**
			 * Filters `pre_comment_approved` value in WordPress.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `pre_comment_approved` filter.
			 *
			 * @param integer|string $comment_status New comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param array          $comment_data An array of all comment data associated w/ a new comment being created.
			 *
			 * @return integer|string Filtered `$comment_status` value.
			 */
			public function pre_comment_approved($comment_status, array $comment_data)
			{
				return $comment_status; // Pass through.
			}

			/*
			 * User-Related Methods
			 */

			/**
			 * User registration handler.
			 *
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
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
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `_cron_'.__NAMESPACE__.'_sub_cleaner` action.
			 */
			public function sub_cleaner()
			{
				new sub_cleaner();
			}

			/**
			 * Log cleaner.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `_cron_'.__NAMESPACE__.'_log_cleaner` action.
			 */
			public function log_cleaner()
			{
				new log_cleaner();
			}
		}

		/*
		 * Namespaced Functions
		 */

		/**
		 * Used internally by other classes as an easy way to reference
		 *    the core {@link plugin} class instance.
		 *
		 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
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
