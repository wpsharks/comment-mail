<?php
/**
 * Pluginclass Plugin.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Plugin Class.
 *
 * @property-read UtilsArray         $utils_array
 * @property-read UtilsDate          $utils_date
 * @property-read UtilsDb            $utils_db
 * @property-read UtilsEnc           $utils_enc
 * @property-read UtilsEnv           $utils_env
 * @property-read UtilsEvent         $utils_event
 * @property-read UtilsFs            $utils_fs
 * @property-read UtilsI18n          $utils_i18n
 * @property-read UtilsIp            $utils_ip
 * @property-read UtilsListServer    $utils_list_server
 * @property-read UtilsLog           $utils_log
 * @property-read UtilsMail          $utils_mail
 * @property-read UtilsMap           $utils_map
 * @property-read UtilsMarkup        $utils_markup
 * @property-read UtilsMath          $utils_math
 * @property-read UtilsPhp           $utils_php
 * @property-read UtilsQueue         $utils_queue
 * @property-read UtilsQueueEventLog $utils_queue_event_log
 * @property-read UtilsRve           $utils_rve
 * @property-read UtilsSso           $utils_sso
 * @property-read UtilsString        $utils_string
 * @property-read UtilsSub           $utils_sub
 * @property-read UtilsSubEventLog   $utils_sub_event_log
 * @property-read UtilsUrl           $utils_url
 * @property-read UtilsUser          $utils_user
 *
 * @since 141111 First documented version.
 */
class Plugin extends AbsBase
{
    /*
     * Public Properties
     */

    /**
     * Used by the plugin's uninstall handler.
     *
     * @since 141111 Adding uninstall handler.
     *
     * @var bool|null Defined by constructor.
     */
    public $enable_hooks = null;

    /*
     * Public Properties (Defined @ Setup)
     */

    /**
     * An array of pro-only option keys.
     *
     * @since 141111 First documented version.
     *
     * @var array Default options array.
     */
    public $pro_only_option_keys;

    /**
     * An array of all default option values.
     *
     * @since 141111 First documented version.
     *
     * @var array Default options array.
     */
    public $default_options;

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
     *             i.e. to use any aspect of the plugin, including the configuration
     *             of any/all plugin options and/or advanced settings.
     */
    public $cap; // Most important cap.

    /**
     * Management capability requirement.
     *
     * @since 141111 First documented version.
     *
     * @var string Capability required to manage.
     *             i.e. to use/manage the plugin from the back-end,
     *             but NOT to allow for any config. changes.
     */
    public $manage_cap;

    /**
     * Auto-recompile capability requirement.
     *
     * @since 141111 First documented version.
     *
     * @var string Capability required to auto-recompile.
     *             i.e. to see notices regarding automatic recompilations
     *             following an upgrade the plugin files/version.
     */
    public $auto_recompile_cap;

    /**
     * Upgrade capability requirement.
     *
     * @since 141111 First documented version.
     *
     * @var string Capability required to upgrade.
     *             i.e. the ability to run any sort of plugin upgrader.
     */
    public $update_cap;

    /**
     * Uninstall capability requirement.
     *
     * @since 141111 First documented version.
     *
     * @var string Capability required to uninstall.
     *             i.e. the ability to deactivate and even delete the plugin.
     */
    public $uninstall_cap;

    /*
     * Public Properties (Defined by Various Hooks)
     */

    public $menu_page_hooks = [];

    /*
     * Plugin Constructor
     */

    /**
     * Plugin constructor.
     *
     * @param bool $enable_hooks Defaults to a TRUE value.
     *                           If FALSE, setup runs but without adding any hooks.
     *
     * @since 141111 First documented version.
     */
    public function __construct($enable_hooks = true)
    {
        /*
         * Global reference.
         */
        $GLOBALS[GLOBAL_NS] = $this;

        /*
         * Parent constructor.
         */
        parent::__construct();

        /*
         * Initialize properties.
         */
        $this->enable_hooks = (bool) $enable_hooks;

        /*
         * With or without hooks?
         */
        if (!$this->enable_hooks) { // Without hooks?
            return; // Stop here; construct without hooks.
        }
        /*
         * Setup primary plugin hooks.
         */
        add_action('after_setup_theme', [$this, 'setup']);
        register_activation_hook(PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(PLUGIN_FILE, [$this, 'deactivate']);
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
        if (!is_null($setup = &$this->cacheKey(__FUNCTION__))) {
            return; // Already setup. Once only!
        }
        $setup = true; // Once only please.

        /*
         * Fire pre-setup hooks.
         */
        if ($this->enable_hooks) { // Hooks enabled?
            do_action('before_'.__METHOD__, get_defined_vars());
        }
        /*
         * Load the plugin's text domain for translations.
         */
        load_plugin_textdomain(SLUG_TD); // Translations.

        /*
         * Load additional class dependencies.
         */
        if (is_admin() && !class_exists('WP_List_Table')) {
            require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
        }
        /*
         * Setup class properties related to authentication/capabilities.
         */
        $this->cap                = apply_filters(__METHOD__.'_cap', 'activate_plugins');
        $this->manage_cap         = apply_filters(__METHOD__.'_manage_cap', 'moderate_comments');
        $this->auto_recompile_cap = apply_filters(__METHOD__.'_auto_recompile_cap', 'activate_plugins');
        $this->update_cap         = apply_filters(__METHOD__.'_update_cap', 'update_plugins');
        $this->uninstall_cap      = apply_filters(__METHOD__.'_uninstall_cap', 'delete_plugins');

        /*
         * Setup pro-only option keys.
         */
        $this->pro_only_option_keys = [

            # Updates.

            'pro_update_check',
            'last_pro_update_check',

            'pro_update_username',
            'pro_update_password',

            # Stats pinger.

            'last_pro_stats_log',

            # SSO.

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

            # SMTP configuration.

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

            # Replies via email.

            'replies_via_email_enable',
            'replies_via_email_handler',

            'rve_sparkpost_api_key',
            'rve_sparkpost_reply_to_email',
            'rve_sparkpost_webhook_setup_hash',
            'rve_sparkpost_webhook_id',

            'rve_mandrill_reply_to_email',
            'rve_mandrill_max_spam_score',
            'rve_mandrill_spf_check_enable',
            'rve_mandrill_dkim_check_enable',

            # List server integrations.

            'list_server_enable',
            'list_server',

            'list_server_checkbox_label',
            'list_server_checkbox_default_state',

            'list_server_mailchimp_list_id',
            'list_server_mailchimp_list_id',

            # Blacklisting.

            'email_blacklist_patterns',

            # Performance tuning.

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

            # IP tracking.

            'prioritize_remote_addr',
            'geo_location_tracking_enable',

            # Comment notifications.

            'comment_notification_clipping_enable',
            'comment_notification_parent_content_clip_max_chars',
            'comment_notification_content_clip_max_chars',

            # Subscription summary.

            'sub_manage_summary_max_limit',

            # Select options.

            'post_select_options_enable',
            'post_select_options_media_enable',
            'comment_select_options_enable',
            'user_select_options_enable',
            'enhance_select_options_enable',
            'max_select_options',

            # Menu pages; i.e. logo display.

            'menu_pages_logo_icon_enable',

            # Template-related config. options.

            'template_type',
            'template_syntax_theme',

            # PHP-based templates for the site.

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

            # PHP-based templates for emails.

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
        ];
        /*
         * Setup the array of all plugin options.
         */
        $this->default_options = [
            # Core/systematic option keys.

            'version'                  => VERSION,
            'stcr_transition_complete' => '0', // `0|1` transitioned?

            'crons_setup'                      => '0', // `0` or timestamp.
            'crons_setup_on_namespace'         => '', // The namespace on which they were set up.
            'crons_setup_on_wp_with_schedules' => '', // A sha1 hash of `wp_get_schedules()`

            # Related to data safeguards.

            'uninstall_safeguards_enable' => '1', // `0|1`; safeguards on?

            # Related to user authentication.

            'manage_cap' => $this->manage_cap, // Capability.

            # Related to automatic pro updates.

            'pro_update_check'      => '1', // `0|1`; enable?
            'last_pro_update_check' => '0', // Timestamp.

            'pro_update_username' => '', // Username.
            'pro_update_password' => '', // Password or license key.

            # Related to the stats pinger.

            'last_pro_stats_log' => '0', // Timestamp.

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
            'enable'                  => '0', // `0|1`; enable?
            'new_subs_enable'         => '1', // `0|1`; enable?
            'queue_processing_enable' => '1', // `0|1`; enable?
            'enabled_post_types'      => 'post', // Comma-delimited post types.

            'comment_form_sub_template_enable' => '1', // `0|1`; enable?
            'comment_form_sub_scripts_enable'  => '1', // `0|1`; enable?

            'comment_form_default_sub_type_option'    => 'comment', // ``, `comment` or `comments`.
            'comment_form_default_sub_deliver_option' => 'asap', // `asap`, `hourly`, `daily`, `weekly`.

            # Related to SSO and service integrations.

            'sso_enable' => '0', // `0|1`; enable?

            'comment_form_sso_template_enable' => '1', // `0|1`; enable?
            'comment_form_sso_scripts_enable'  => '1', // `0|1`; enable?

            'login_form_sso_template_enable' => '1', // `0|1`; enable?
            'login_form_sso_scripts_enable'  => '1', // `0|1`; enable?

            'sso_twitter_key'    => '',
            'sso_twitter_secret' => '',
            // See: <https://apps.twitter.com/app/new>

            'sso_facebook_key'    => '',
            'sso_facebook_secret' => '',
            // See: <https://developers.facebook.com/quickstarts/?platform=web>

            'sso_google_key'    => '',
            'sso_google_secret' => '',
            // See: <https://developers.google.com/accounts/docs/OpenIDConnect#getcredentials>

            'sso_linkedin_key'    => '',
            'sso_linkedin_secret' => '',
            // See: <https://www.linkedin.com/secure/developer?newapp=>

            # Related to CAN-SPAM compliance.

            'can_spam_postmaster'      => get_bloginfo('admin_email'),
            'can_spam_mailing_address' => get_bloginfo('name').'<br />'."\n".
                                             '123 Somewhere Street<br />'."\n".
                                             'Attn: Comment Subscriptions<br />'."\n".
                                             'Somewhere, USA 99999 ~ Ph: 555-555-5555', // CAN-SPAM contact info.
            'can_spam_privacy_policy_url' => '', // CAN-SPAM privacy policy.

            # Related to auto-subscribe functionality.

            'auto_subscribe_enable'             => '1', // `0|1`; auto-subscribe enable?
            'auto_subscribe_deliver'            => 'asap', // `asap`, `hourly`, `daily`, `weekly`.
            'auto_subscribe_post_types'         => 'post', // Comma-delimited post types.
            'auto_subscribe_post_author_enable' => '1', // `0|1`; auto-subscribe post authors?
            'auto_subscribe_recipients'         => '', // Others `;|,` delimited emails.
            'auto_subscribe_roles'              => '', // Comma-delimited list of WP Roles.

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
            'auto_confirm_force_enable'                      => '0', // `0|1`; auto-confirm enable?
            'auto_confirm_if_already_subscribed_u0ip_enable' => '0', // `0|1`; auto-confirm enable?
            'all_wp_users_confirm_email'                     => '0', // WP users confirm their email?

            # Related to email headers.

            'from_name'      => get_bloginfo('name'), // From: name.
            'from_email'     => get_bloginfo('admin_email'), // From: <email>.
            'reply_to_email' => get_bloginfo('admin_email'), // Reply-To: <email>.

            # Related to SMPT configuration.

            'smtp_enable' => '0', // `0|1`; enable?

            'smtp_host'   => '', // SMTP host name.
            'smtp_port'   => '465', // SMTP port number.
            'smtp_secure' => 'ssl', // ``, `ssl` or `tls`.

            'smtp_username' => '', // SMTP username.
            'smtp_password' => '', // SMTP password.

            'smtp_from_name'      => get_bloginfo('name'), // From: name.
            'smtp_from_email'     => get_bloginfo('admin_email'), // From: <email>.
            'smtp_reply_to_email' => get_bloginfo('admin_email'), // Reply-To: <email>.
            'smtp_force_from'     => '1', // `0|1`; force? Not configurable at this time.

            # Related to replies via email.

            'replies_via_email_enable'  => '0', // `0|1`; enable?
            'replies_via_email_handler' => '', // `sparkpost` or `mandrill`.
            // Mandrill is currently the only choice. In the future we may add other options to this list.

            'rve_sparkpost_api_key'            => '', // SparkPost API key.
            'rve_sparkpost_reply_to_email'     => '', // `Reply-To:` address.
            'rve_sparkpost_webhook_setup_hash' => '', // Setup hash.
            'rve_sparkpost_webhook_id'         => '', // Webhook ID.

            'rve_mandrill_reply_to_email'    => '', // `Reply-To:` address.
            'rve_mandrill_max_spam_score'    => '5.0', // Max allowable spam score.
            'rve_mandrill_spf_check_enable'  => '1', // `0|1|2|3|4`; where `0` = disable.
            'rve_mandrill_dkim_check_enable' => '1', // `0|1|2`; where `0` = disable.

            # Related to list server integrations.

            'list_server_enable' => '0', // `0|1`; enable?
            'list_server'        => 'mailchimp', // List server identifier.

            'list_server_checkbox_default_state' => 'checked', // `checked` or empty.
            'list_server_checkbox_label'         => __('Yes, I want to receive blog updates also.', 'comment-mail'),

            'list_server_mailchimp_api_key' => '', // MailChimp API key.
            'list_server_mailchimp_list_id' => '', // MailChimp list ID.

            # Related to blacklisting.

            'email_blacklist_patterns' => implode("\n", UtilsMail::$role_based_blacklist_patterns),

            # Related to performance tuning.

            'queue_processor_max_time'           => '30', // In seconds.
            'queue_processor_delay'              => '250', // In milliseconds.
            'queue_processor_max_limit'          => '100', // Total queue entries.
            'queue_processor_realtime_max_limit' => '5', // Total queue entries.

            'sub_cleaner_max_time'        => '30', // In seconds.
            'unconfirmed_expiration_time' => '60 days', // `strtotime()` compatible.
            'trashed_expiration_time'     => '60 days', // `strtotime()` compatible.

            'log_cleaner_max_time'            => '30', // In seconds.
            'sub_event_log_expiration_time'   => '', // `strtotime()` compatible.
            'queue_event_log_expiration_time' => '', // `strtotime()` compatible.

            # Related to IP tracking.

            'prioritize_remote_addr'       => '0', // `0|1`; enable?
            'geo_location_tracking_enable' => '0', // `0|1`; enable?

            # Related to meta boxes.

            'excluded_meta_box_post_types' => 'link,comment,revision,attachment,nav_menu_item,snippet,redirect',

            # Related to comment notifications.

            'comment_notification_clipping_enable'               => '1', // `0|1`; enable?
            'comment_notification_parent_content_clip_max_chars' => '100', // Max chars to include in notifications.
            'comment_notification_content_clip_max_chars'        => '200', // Max chars to include in notifications.

            # Related to subscription summary.

            'sub_manage_summary_max_limit' => '25', // Subscriptions per page.

            # Related to select options.

            'post_select_options_enable'       => '1', // `0|1`; enable?
            'post_select_options_media_enable' => '0', // `0|1`; enable?
            'comment_select_options_enable'    => '1', // `0|1`; enable?
            'user_select_options_enable'       => '1', // `0|1`; enable?
            'enhance_select_options_enable'    => '1', // `0|1`; enable?
            'max_select_options'               => '2000', // Max options.

            # Related to menu pages; i.e. logo display.

            'menu_pages_logo_icon_enable' => IS_PRO ? '0' : '1', // `0|1`; display?

            /* Related to branding; i.e. powered by Comment Mail™ notes.
            ~ IMPORTANT: please see <https://wordpress.org/plugins/about/guidelines/>
            #10. The plugin must NOT embed external links on the public site (like a "powered by" link) without
            explicitly asking the user's permission. Any such options in the plugin must default to NOT show the link. */

            'email_footer_powered_by_enable' => '0', // `0|1`; enable?
            'site_footer_powered_by_enable'  => '0', // `0|1`; enable?

            # Template-related config. options.

            'template_type'         => 's', // `a|s`.
            'template_syntax_theme' => 'monokai',

            # Simple snippet-based templates for the site.

            'template__type_s__site__snippet__header_tag___php' => '', // HTML code.
            'template__type_s__site__snippet__footer_tag___php' => '', // HTML code.

            'template__type_s__site__login_form__snippet__sso_ops___php' => '', // HTML code.

            'template__type_s__site__comment_form__snippet__sso_ops___php' => '', // HTML code.
            'template__type_s__site__comment_form__snippet__sub_ops___php' => '', // HTML code.

            'template__type_s__site__sub_actions__snippet__confirmed___php'        => '', // HTML code.
            'template__type_s__site__sub_actions__snippet__unsubscribed___php'     => '', // HTML code.
            'template__type_s__site__sub_actions__snippet__unsubscribed_all___php' => '', // HTML code.

            # Advanced HTML, PHP-based templates for the site.

            'template__type_a__site__header___php'         => '', // HTML/PHP code.
            'template__type_a__site__header_styles___php'  => '', // HTML/PHP code.
            'template__type_a__site__header_scripts___php' => '', // HTML/PHP code.
            'template__type_a__site__header_tag___php'     => '', // HTML/PHP code.

            'template__type_a__site__footer_tag___php' => '', // HTML/PHP code.
            'template__type_a__site__footer___php'     => '', // HTML/PHP code.

            'template__type_a__site__comment_form__sso_ops___php'        => '', // HTML/PHP code.
            'template__type_a__site__comment_form__sso_op_scripts___php' => '', // HTML/PHP code.

            'template__type_a__site__login_form__sso_ops___php'        => '', // HTML/PHP code.
            'template__type_a__site__login_form__sso_op_scripts___php' => '', // HTML/PHP code.

            'template__type_a__site__sso_actions__complete___php' => '', // HTML/PHP code.

            'template__type_a__site__comment_form__sub_ops___php'        => '', // HTML/PHP code.
            'template__type_a__site__comment_form__sub_op_scripts___php' => '', // HTML/PHP code.

            'template__type_a__site__sub_actions__confirmed___php'                               => '', // HTML/PHP code.
            'template__type_a__site__sub_actions__unsubscribed___php'                            => '', // HTML/PHP code.
            'template__type_a__site__sub_actions__unsubscribed_all___php'                        => '', // HTML/PHP code.
            'template__type_a__site__sub_actions__manage_summary___php'                          => '', // HTML/PHP code.
            'template__type_a__site__sub_actions__manage_sub_form___php'                         => '', // HTML/PHP code.
            'template__type_a__site__sub_actions__manage_sub_form_comment_id_row_via_ajax___php' => '', // HTML/PHP code.

            # Simple snippet-based templates for emails.

            'template__type_s__email__snippet__header_tag___php' => '', // HTML code.
            'template__type_s__email__snippet__footer_tag___php' => '', // HTML code.

            'template__type_s__email__sub_confirmation__snippet__subject___php' => '', // HTML code.
            'template__type_s__email__sub_confirmation__snippet__message___php' => '', // HTML code.

            'template__type_s__email__comment_notification__snippet__subject___php'                => '', // HTML code.
            'template__type_s__email__comment_notification__snippet__message_heading___php'        => '', // HTML code.
            'template__type_s__email__comment_notification__snippet__message_in_response_to___php' => '', // HTML code.
            'template__type_s__email__comment_notification__snippet__message_reply_from___php'     => '', // HTML code.
            'template__type_s__email__comment_notification__snippet__message_comment_from___php'   => '', // HTML code.

            # Advanced HTML, PHP-based templates for emails.

            'template__type_a__email__header___php'         => '', // HTML/PHP code.
            'template__type_a__email__header_styles___php'  => '', // HTML/PHP code.
            'template__type_a__email__header_scripts___php' => '', // HTML/PHP code.
            'template__type_a__email__header_tag___php'     => '', // HTML/PHP code.

            'template__type_a__email__footer_tag___php' => '', // HTML/PHP code.
            'template__type_a__email__footer___php'     => '', // HTML/PHP code.

            'template__type_a__email__sub_confirmation__subject___php' => '', // HTML/PHP code.
            'template__type_a__email__sub_confirmation__message___php' => '', // HTML/PHP code.

            'template__type_a__email__comment_notification__subject___php' => '', // HTML/PHP code.
            'template__type_a__email__comment_notification__message___php' => '', // HTML/PHP code.

        ]; // Default options are merged with those defined by the site owner.
        $this->default_options = apply_filters(__METHOD__.'_default_options', $this->default_options); // Allow filters.
        $this->options         = is_array($this->options = get_option(GLOBAL_NS.'_options')) ? $this->options : [];

        $this->options = array_merge($this->default_options, $this->options); // Merge into default options.
        $this->options = array_intersect_key($this->options, $this->default_options); // Valid keys only.
        $this->options = apply_filters(__METHOD__.'_options', $this->options); // Allow filters.
        $this->options = array_map('strval', $this->options); // Force string values.

        if ($this->options['manage_cap']) { // This can be altered by plugin config. options.
            $this->manage_cap = apply_filters(__METHOD__.'_manage_cap', $this->options['manage_cap']);
        }
        if (!$this->options['auto_confirm_force_enable']) {
            $this->options['all_wp_users_confirm_email'] = '0';
        }
        if (!$this->options['replies_via_email_handler']) {
            $this->options['replies_via_email_enable'] = '0';
        }
        foreach (!IS_PRO ? $this->pro_only_option_keys : [] as $_key) {
            $this->options[$_key] = $this->default_options[$_key];
        } // Force default pro-only option keys in lite version.
        unset($_key); // Housekeeping.

        /*
         * With or without hooks?
         */
        if (!$this->enable_hooks) { // Without hooks?
            return; // Stop here; setup without hooks.
        }
        /*
         * Setup all secondary plugin hooks.
         */
        add_action('init', [$this, 'actions'], -10);
        add_action('init', [$this, 'stcrCheck'], 100);
        add_action('init', [$this, 'jetpackCheck'], 100);

        add_action('admin_init', [$this, 'checkVersion'], 10);
        add_action('admin_init', [$this, 'checkPhpVersion'], 10);

        

        

        add_action('all_admin_notices', [$this, 'allAdminNotices'], 10);

        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminStyles'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts'], 10);

        add_action('admin_menu', [$this, 'addMenuPages'], 10);
        add_filter('set-screen-option', [$this, 'setScreenOption'], 10, 3);
        add_filter('plugin_action_links_'.plugin_basename(PLUGIN_FILE), [$this, 'addSettingsLink'], 10, 1);

        add_filter('manage_users_columns', [$this, 'manageUsersColumns'], 10, 1);
        add_filter('manage_users_custom_column', [$this, 'manageUsersCustomColumn'], 10, 3);

        add_action('init', [$this, 'commentShortlinkRedirect'], -11);

        add_action('wp_print_scripts', [$this, 'enqueueFrontScripts'], 10);

        add_action('login_form', [$this, 'loginForm'], 5, 0); // Ideal choice.
        add_action('login_footer', [$this, 'loginForm'], 5, 0); // Secondary fallback.

        add_action('transition_post_status', [$this, 'postStatus'], 10, 3);
        add_action('before_delete_post', [$this, 'postDelete'], 10, 1);

        add_action('comment_form_must_log_in_after', [$this, 'commentFormMustLogInAfter'], 5, 0);
        add_action('comment_form_top', [$this, 'commentFormMustLogInAfter'], 5, 0); // Secondary fallback.

        //add_filter('comment_form_field_comment', array($this, 'commentFormFilterAppend'), 5, 1);
        add_filter('comment_form_submit_field', [$this, 'commentFormFilterPrepend'], 5, 1);
        add_action('comment_form', [$this, 'commentForm'], 5, 0); // Secondary fallback.

        add_action('comment_post', [$this, 'commentPost'], 10, 2);
        add_action('transition_comment_status', [$this, 'commentStatus'], 10, 3);

        add_filter('pre_option_comment_registration', [$this, 'preOptionCommentRegistration'], 1000, 1);
        add_filter('pre_comment_approved', [$this, 'preCommentApproved'], 1000, 2);

        add_action('user_register', [$this, 'userRegister'], 10, 1);
        add_action('delete_user', [$this, 'userDelete'], 10, 1);
        add_action('wpmu_delete_user', [$this, 'userDelete'], 10, 1);
        add_action('remove_user_from_blog', [$this, 'userDelete'], 10, 2);

        add_action('add_meta_boxes', [$this, 'addMetaBoxes'], 10);

        /*
         * Setup CRON-related hooks.
         */
        add_filter('cron_schedules', [$this, 'extendCronSchedules'], 10, 1);
        add_action('init', [$this, 'checkCronSetup'], PHP_INT_MAX);

        add_action('_cron_'.GLOBAL_NS.'_queue_processor', [$this, 'queueProcessor'], 10);
        add_action('_cron_'.GLOBAL_NS.'_sub_cleaner', [$this, 'subCleaner'], 10);
        add_action('_cron_'.GLOBAL_NS.'_log_cleaner', [$this, 'logCleaner'], 10);

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
     * @throws \exception If the `$___overload` property is undefined.
     *
     * @return mixed The value of `$this->___overload->{$property}`.
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php
     */
    public function __get($property)
    {
        $property = (string) $property;

        if (strpos($property, 'utils_') === 0) {
            $class_property = ucfirst(
                preg_replace_callback(
                    '/_(.)/',
                    function ($m) {
                        return strtoupper($m[1]);
                    },
                    $property
                )
            );
            $ns_class_property = '\\'.__NAMESPACE__.'\\'.$class_property;

            if (class_exists($ns_class_property)) {
                if (!isset($this->___overload->{$property})) {
                    $this->___overload->{$property} = new $ns_class_property();
                }
            }
        }
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
     * @return int UNIX timestamp.
     */
    public function installTime()
    {
        return (int) get_option(GLOBAL_NS.'_install_time');
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
        new Installer();
    }

    /**
     * Check current plugin version.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `admin_init` action.
     */
    public function checkVersion()
    {
        if (version_compare($this->options['version'], VERSION, '>=')) {
            return; // Nothing to do; already @ latest version.
        }
        new Upgrader(); // Upgrade handler.
    }

    /**
     * Check current PHP version.
     *
     * @since 161118 PHP version check.
     *
     * @attaches-to `admin_init` action.
     */
    public function checkPhpVersion()
    {
        $is_php7              = version_compare(PHP_VERSION, '7', '>=');
        $is_php7_incompatible = $is_php7 && version_compare(PHP_VERSION, '7.0.9', '<');
        $php_clean_version    = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;

        if ($is_php7 && $is_php7_incompatible) {
            $markup = sprintf(
                __('<strong>PHP v7 Warning:</strong> The %1$s&trade; plugin is compatible with PHP v7.0, but you\'re running PHP v%2$s which has a bug that causes problems in Comment Mail. Please upgrade to PHP v7.0.9 or higher.', 'comment-mail'),
                esc_html(NAME),
                esc_html($php_clean_version)
            );
            $this->enqueueWarning($markup, [
                'persistent'    => true,
                'dismissable'   => false,
                'persistent_id' => 'php-7-compat',
                'requires_cap'  => 'administrator',
            ]);
        } elseif ($is_php7 && !$is_php7_incompatible) {
            $notices = get_option(GLOBAL_NS.'_notices');
            $notices = is_array($notices) ? $notices : [];

            foreach ($notices as $_key => $_notice) {
                if (!empty($_notice['persistent_id']) && $_notice['persistent_id'] === 'php-7-compat') {
                    unset($notices[$_key]); // i.e., Get rid of `php-7-compat` warning.
                    update_option(GLOBAL_NS.'_notices', $notices);
                    break; // Dismiss persistent key.
                }
            } // unset($_key, $_notice);
        }
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
        new Uninstaller();
    }

    /*
     * Ping-Related Methods
     */

    /**
     * Maybe ping stats logger.
     *
     * @since       150708 Adding stats pinger.
     *
     * @attaches-to `admin_init` action.
     */
    public function statsPinger()
    {
        new StatsPinger();
    }

    /*
     * Action-Related Methods
     */

    /**
     * Plugin action handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `init` action.
     */
    public function actions()
    {
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Nothing to do here.
        }
        new Actions(); // Handle action(s).
    }

    /*
     * Conflict-relatd methods.
     */

    /**
     * Check for StCR conflict(s).
     *
     * @since 150626 Improving StCR compat.
     */
    public function stcrCheck()
    {
        if (!$this->options['enable']) {
            return; // Not applicable.
        }
        if (!class_exists('wp_subscribe_reloaded')) {
            return; // Nothing to do here.
        }
        if (!is_admin() || !empty($_REQUEST['action'])) {
            return; // Stay quiet in this case.
        }
        $conflict = sprintf(__('<p style="font-size:120%%; font-weight:400; margin:0;"><strong>%1$s&trade;</strong> + <strong>Subscribe to Comments Reloaded</strong> = Possible Conflict!</p>', 'comment-mail'), esc_html(NAME));
        $conflict .= '<p style="margin:0;">'.sprintf(__('<strong>WARNING (ACTION REQUIRED):</strong> Running %1$s&trade; while Subscribe to Comments Reloaded is <em>also</em> an active WordPress plugin <strong>can cause problems</strong>; i.e., these two plugins do the same thing—%1$s being the newer of the two. We recommend keeping %1$s; please <a href="%2$s">deactivate the Subscribe to Comments Reloaded plugin</a> to get rid of this message.', 'comment-mail'), esc_html(NAME), esc_html(admin_url('plugins.php'))).'</p>';
        $this->enqueueError($conflict);
    }

    /**
     * Check for Jetpack conflict(s).
     *
     * @since 150626 Improving Jetpack compat.
     */
    public function jetpackCheck()
    {
        if (!$this->options['enable']) {
            return; // Not applicable.
        }
        if (!class_exists('Jetpack_Subscriptions')) {
            return; // Nothing to do here.
        }
        if (!get_option('stc_enabled', 1)) {
            return; // Nothing to do here.
        }
        if (!is_admin() || !empty($_REQUEST['action'])) {
            return; // Stay quiet in this case.
        }
        $conflict = sprintf(__('<p style="font-size:120%%; font-weight:400; margin:0;"><strong>%1$s&trade;</strong> + <strong>Jetpack Subscriptions module</strong> (with Follow Comments enabled) = Possible Conflict!</p>', 'comment-mail'), esc_html(NAME));
        $conflict .= '<p style="margin:0;">'.sprintf(__('<strong>WARNING (ACTION REQUIRED):</strong> Running %1$s&trade; while the Jetpack Subscriptions module (with Follow Comments enabled) is <em>also</em> active in WordPress <strong>can cause problems</strong>; i.e., these two handle the same thing—%1$s being the newer of the two. We recommend keeping %1$s; please deactivate the Follow Comments functionality in the Jetpack Subscriptions module to get rid of this message (see <strong>Dashboard → Settings → Discussion → Jetpack Subscriptions Settings</strong>).', 'comment-mail'), esc_html(NAME)).'</p>';
        $this->enqueueError($conflict);
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
    public function optionsQuickSave(array $options)
    {
        $this->options = array_merge($this->default_options, $this->options, $options);

        foreach (!IS_PRO ? $this->pro_only_option_keys : [] as $_key) {
            $this->options[$_key] = $this->default_options[$_key];
        } // Force default pro-only option keys in lite version.
        unset($_key); // Housekeeping.

        $this->options = array_intersect_key($this->options, $this->default_options);
        $this->options = array_map('strval', $this->options); // Force strings.

        update_option(GLOBAL_NS.'_options', $this->options); // DB update.
    }

    /**
     * Saves new plugin options.
     *
     * @since 141111 First documented version.
     *
     * @param array $options An array of new plugin options.
     */
    public function optionsSave(array $options)
    {
        $this->options = array_merge($this->default_options, $this->options, $options);

        foreach (!IS_PRO ? $this->pro_only_option_keys : [] as $_key) {
            $this->options[$_key] = $this->default_options[$_key];
        } // Force default pro-only option keys in lite version.
        unset($_key); // Housekeeping.

        $this->options = array_intersect_key($this->options, $this->default_options);
        $this->options = array_map('strval', $this->options); // Force strings.

        foreach ($this->options as $_key => &$_value) {
            if (strpos($_key, 'template__') === 0) {
                $_key_data = Template::optionKeyData($_key);
                if (!IS_PRO && $_key_data->type === 'a') {
                    continue; // Not possible in lite version.
                }
                $_default_template     = new Template($_key_data->file, $_key_data->type, true);
                $_default_template_nws = preg_replace('/\s+/', '', $_default_template->fileContents());
                $_option_template_nws  = preg_replace('/\s+/', '', $_value);

                if ($_option_template_nws === $_default_template_nws) {
                    $_value = ''; // Empty; it's a default value.
                }
            }
        }
        unset($_key, $_key_data, $_value); // Housekeeping.
        unset($_default_template, $_option_template_nws, $_default_template_nws);

        

        update_option(GLOBAL_NS.'_options', $this->options); // DB update.
    }

    /*
     * Admin Meta-Box-Related Methods
     */

    /**
     * Adds plugin meta boxes.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `add_meta_boxes` action.
     *
     * @param string $post_type The current post type.
     */
    public function addMetaBoxes($post_type)
    {
        if (!current_user_can($this->manage_cap)) {
            if (!current_user_can($this->cap)) {
                return; // Do not add meta boxes.
            }
        }
        $post_type = strtolower((string) $post_type);

        $enabled_post_types = strtolower($this->options['enabled_post_types']);
        $enabled_post_types = preg_split('/[\s;,]+/', $enabled_post_types, null, PREG_SPLIT_NO_EMPTY);

        if ($enabled_post_types && !in_array($post_type, $enabled_post_types, true)) {
            return; // Ignore; not enabled for this post type.
        }
        $excluded_post_types = strtolower($this->options['excluded_meta_box_post_types']);
        $excluded_post_types = preg_split('/[\s;,]+/', $excluded_post_types, null, PREG_SPLIT_NO_EMPTY);

        if (in_array($post_type, $excluded_post_types, true)) {
            return; // Ignore; this post type excluded.
        }
        // Meta boxes use an SVG graphic.
        $icon = $this->utils_fs->inlineIconSvg();

        if (!$this->utils_env->isMenuPage('post-new.php')) {
            add_meta_box(GLOBAL_NS.'_small', $icon.' '.NAME.'&trade;', [$this, 'postSmallMetaBox'], $post_type, 'normal', 'default');
        }
        // @TODO disabling this for now.
        // add_meta_box(GLOBAL_NS.'_large', $icon.' '.NAME.'&trade; '.__('Subscriptions', 'comment-mail'),
        //             array($this, 'postLargeMetaBox'), $post_type, 'normal', 'high');
    }

    /**
     * Builds small meta box for this plugin.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Post $post A WP post object reference.
     *
     * @see   addMetaBoxes()
     */
    public function postSmallMetaBox(\WP_Post $post)
    {
        new PostSmallMetaBox($post);
    }

    /**
     * Builds large meta box for this plugin.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Post $post A WP post object reference.
     *
     * @see   addMetaBoxes()
     */
    public function postLargeMetaBox(\WP_Post $post)
    {
        new PostLargeMetaBox($post);
    }

    /*
     * Admin Menu-Page-Related Methods
     */

    /**
     * Adds CSS for administrative menu pages.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `admin_enqueue_scripts` action.
     */
    public function enqueueAdminStyles()
    {
        if ($this->utils_env->isMenuPage('post.php')
            || $this->utils_env->isMenuPage('post-new.php')
        ) {
            $this->enqueuePostAdminStyles();
        }
        if (!$this->utils_env->isMenuPage(GLOBAL_NS.'*')) {
            return; // Nothing to do; not applicable.
        }
        $deps = ['codemirror', 'jquery-datetimepicker', 'chosen', 'font-awesome', 'sharkicons']; // Dependencies.

        wp_enqueue_style('codemirror', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/codemirror.min.css'), [], null, 'all');
        wp_enqueue_style('codemirror-fullscreen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/display/fullscreen.min.css'), ['codemirror'], null, 'all');
        wp_enqueue_style('codemirror-'.$this->options['template_syntax_theme'].'-theme', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/theme/'.urlencode($this->options['template_syntax_theme']).'.min.css'), ['codemirror'], null, 'all');

        wp_enqueue_style('jquery-datetimepicker', $this->utils_url->to('/src/vendor/package/datetimepicker/jquery.datetimepicker.css'), [], null, 'all');
        wp_enqueue_style('chosen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css'), [], null, 'all');

        wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), [], null, 'all');
        wp_enqueue_style('sharkicons', $this->utils_url->to('/src/vendor/websharks/sharkicons/src/short-classes.min.css'), [], null, 'all');

        wp_enqueue_style(GLOBAL_NS, $this->utils_url->to('/src/client-s/css/menu-pages.min.css'), $deps, VERSION, 'all');
    }

    /**
     * Adds CSS for administrative menu pages.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `admin_enqueue_scripts` action indirectly.
     */
    public function enqueuePostAdminStyles()
    {
        if (!$this->utils_env->isMenuPage('post.php')
            && !$this->utils_env->isMenuPage('post-new.php')
        ) {
            return; // Not applicable.
        }
        $deps = ['font-awesome', 'sharkicons']; // Dependencies.

        wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), [], null, 'all');
        wp_enqueue_style('sharkicons', $this->utils_url->to('/src/vendor/websharks/sharkicons/src/short-classes.min.css'), [], null, 'all');

        wp_enqueue_style(GLOBAL_NS, $this->utils_url->to('/src/client-s/css/menu-pages.min.css'), $deps, VERSION, 'all');
    }

    /**
     * Adds JS for administrative menu pages.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `admin_enqueue_scripts` action.
     */
    public function enqueueAdminScripts()
    {
        if (!$this->utils_env->isMenuPage(GLOBAL_NS.'*')) {
            return; // Nothing to do; NOT a plugin menu page.
        }
        $deps = ['jquery', 'postbox', 'codemirror', 'google-jsapi-modules', 'chartjs', 'jquery-datetimepicker', 'chosen']; // Dependencies.

        wp_enqueue_script('codemirror', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/codemirror.min.js'), [], null, true);
        wp_enqueue_script('codemirror-fullscreen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/display/fullscreen.min.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-matchbrackets', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/edit/matchbrackets.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-htmlmixed', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/htmlmixed/htmlmixed.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-xml', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/xml/xml.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-javascript', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/javascript/javascript.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-css', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/css/css.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-clike', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/clike/clike.js'), ['codemirror'], null, true);
        wp_enqueue_script('codemirror-php', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/php/php.js'), ['codemirror'], null, true);

        $google_jsapi_modules = "{'modules':[{'name':'visualization','version':'1','packages':['geochart']}]}";
        wp_enqueue_script('google-jsapi-modules', set_url_scheme('//www.google.com/jsapi?autoload='.urlencode($google_jsapi_modules)), [], null, true);

        wp_enqueue_script('chartjs', set_url_scheme('//cdn.jsdelivr.net/chart.js/1.0.1-beta.4/Chart.min.js'), [], null, true);
        wp_enqueue_script('jquery-datetimepicker', $this->utils_url->to('/src/vendor/package/datetimepicker/jquery.datetimepicker.js'), ['jquery'], null, true);
        wp_enqueue_script('chosen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js'), ['jquery'], null, true);

        wp_enqueue_script(GLOBAL_NS, $this->utils_url->to('/src/client-s/js/menu-pages.min.js'), $deps, VERSION, true);

        wp_localize_script(
            GLOBAL_NS,
            GLOBAL_NS.'_vars',
            [
                'pluginUrl'           => rtrim($this->utils_url->to('/'), '/'),
                'ajaxEndpoint'        => rtrim($this->utils_url->pageNonceOnly(), '/'),
                'templateSyntaxTheme' => $this->options['template_syntax_theme'],
            ]
        );
        wp_localize_script(
            GLOBAL_NS,
            GLOBAL_NS.'_i18n',
            [
                'bulkReconfirmConfirmation' => __('Resend email confirmation link? Are you sure?', 'comment-mail'),
                'bulkDeleteConfirmation'    => $this->utils_env->isMenuPage('*_event_log')
                    ? $this->utils_i18n->logEntryJsDeletionConfirmationWarning()
                    : __('Delete permanently? Are you sure?', 'comment-mail'),
                'dateTimePickerI18n' => [
                    'en' => [
                        'months' => [
                            __('January', 'comment-mail'),
                            __('February', 'comment-mail'),
                            __('March', 'comment-mail'),
                            __('April', 'comment-mail'),
                            __('May', 'comment-mail'),
                            __('June', 'comment-mail'),
                            __('July', 'comment-mail'),
                            __('August', 'comment-mail'),
                            __('September', 'comment-mail'),
                            __('October', 'comment-mail'),
                            __('November', 'comment-mail'),
                            __('December', 'comment-mail'),
                        ],
                        'dayOfWeek' => [
                            __('Sun', 'comment-mail'),
                            __('Mon', 'comment-mail'),
                            __('Tue', 'comment-mail'),
                            __('Wed', 'comment-mail'),
                            __('Thu', 'comment-mail'),
                            __('Fri', 'comment-mail'),
                            __('Sat', 'comment-mail'),
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Creates admin menu pages.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `admin_menu` action.
     */
    public function addMenuPages()
    {
        if (!current_user_can($this->manage_cap)) {
            if (!current_user_can($this->cap)) {
                return; // Do not add meta boxes.
            }
        }
        // Menu page icon uses an SVG graphic.
        $icon = $this->utils_fs->inlineIconSvg();
        $icon = $this->utils_markup->colorSvgMenuIcon($icon);

        $divider = // Dividing line used by various menu items below.
            '<span style="display:block; padding:0; margin:0 0 12px 0; height:1px; line-height:1px; background:#CCCCCC; opacity:0.1;"></span>';

        $child_branch_indent = // Each child branch uses the following UTF-8 char `꜖`; <http://unicode-table.com/en/A716/>.
            '<span style="display:inline-block; margin-left:.5em; position:relative; top:-.2em; left:-.2em; font-weight:normal; opacity:0.2;">&#42774;</span> ';

        $current_menu_page = $this->utils_env->currentMenuPage(); // Current menu page slug.

        // Menu page titles use UTF-8 char: `⥱`; <http://unicode-table.com/en/2971/>.

        /* ----------------------------------------- */

        $_menu_title                      = NAME.(IS_PRO ? ' <sup style="font-size:60%; line-height:1;">Pro</sup>' : '');
        $_page_title                      = NAME.'&trade;';
        $_menu_position                   = apply_filters(__METHOD__.'_position', '25.00001');
        $this->menu_page_hooks[GLOBAL_NS] = add_menu_page($_page_title, $_menu_title, $this->cap, GLOBAL_NS, [$this, 'menuPageOptions'], 'data:image/svg+xml;base64,'.base64_encode($icon), $_menu_position);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS], [$this, 'menuPageOptionsScreen']);

        unset($_menu_title, $_page_title, $_menu_position); // Housekeeping.

        /* ----------------------------------------- */

        $_menu_title = __('Config. Options', 'comment-mail');
        $_page_title = NAME.'&trade; &#8594; '.__('Config. Options', 'comment-mail');
        add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->cap, GLOBAL_NS, [$this, 'menuPageOptions']);

        if (IS_PRO || ImportStcr::dataExists()) {
            $_menu_title = // Visible on-demand only.
                '<small><em>'.$child_branch_indent.__('Import/Export', 'comment-mail').'</em></small>';
            $_page_title = NAME.'&trade; &#8594; '.__('Import/Export', 'comment-mail');
            //$_menu_parent                                          = $current_menu_page === GLOBAL_NS.'_import_export' ? GLOBAL_NS : NULL;
            $this->menu_page_hooks[GLOBAL_NS.'_import_export'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->cap, GLOBAL_NS.'_import_export', [$this, 'menuPageImportExport']);
            add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_import_export'], [$this, 'menuPageImportExportScreen']);
        }

        $_menu_title = // Visible on-demand only.
            '<small><em>'.$child_branch_indent.__('Email Templates', 'comment-mail').'</em></small>';
        $_page_title = NAME.'&trade; &#8594; '.__('Email Templates', 'comment-mail');
        //$_menu_parent                                            = $current_menu_page === GLOBAL_NS.'_email_templates' ? GLOBAL_NS : NULL;
        $this->menu_page_hooks[GLOBAL_NS.'_email_templates'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->cap, GLOBAL_NS.'_email_templates', [$this, 'menuPageEmailTemplates']);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_email_templates'], [$this, 'menuPageEmailTemplatesScreen']);

        $_menu_title = // Visible on-demand only.
            '<small><em>'.$child_branch_indent.__('Site Templates', 'comment-mail').'</em></small>';
        $_page_title = NAME.'&trade; &#8594; '.__('Site Templates', 'comment-mail');
        //$_menu_parent                                           = $current_menu_page === GLOBAL_NS.'_site_templates' ? GLOBAL_NS : NULL;
        $this->menu_page_hooks[GLOBAL_NS.'_site_templates'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->cap, GLOBAL_NS.'_site_templates', [$this, 'menuPageSiteTemplates']);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_site_templates'], [$this, 'menuPageSiteTemplatesScreen']);

        unset($_menu_title, $_page_title, $_menu_parent); // Housekeeping.

        /* ----------------------------------------- */

        $_menu_title                              = $divider.__('Subscriptions', 'comment-mail');
        $_page_title                              = NAME.'&trade; &#8594; '.__('Subscriptions', 'comment-mail');
        $this->menu_page_hooks[GLOBAL_NS.'_subs'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->manage_cap, GLOBAL_NS.'_subs', [$this, 'menuPageSubs']);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_subs'], [$this, 'menuPageSubsScreen']);

        $_menu_title                                       = $child_branch_indent.__('Event Log', 'comment-mail');
        $_page_title                                       = NAME.'&trade; &#8594; '.__('Sub. Event Log', 'comment-mail');
        $this->menu_page_hooks[GLOBAL_NS.'_sub_event_log'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->manage_cap, GLOBAL_NS.'_sub_event_log', [$this, 'menuPageSubEventLog']);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_sub_event_log'], [$this, 'menuPageSubEventLogScreen']);

        unset($_menu_title, $_page_title); // Housekeeping.

        /* ----------------------------------------- */

        $_menu_title                               = $divider.__('Mail Queue', 'comment-mail');
        $_page_title                               = NAME.'&trade; &#8594; '.__('Mail Queue', 'comment-mail');
        $this->menu_page_hooks[GLOBAL_NS.'_queue'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->manage_cap, GLOBAL_NS.'_queue', [$this, 'menuPageQueue']);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_queue'], [$this, 'menuPageQueueScreen']);

        $_menu_title                                         = $child_branch_indent.__('Event Log', 'comment-mail');
        $_page_title                                         = NAME.'&trade; &#8594; '.__('Queue Event Log', 'comment-mail');
        $this->menu_page_hooks[GLOBAL_NS.'_queue_event_log'] = add_submenu_page(GLOBAL_NS, $_page_title, $_menu_title, $this->manage_cap, GLOBAL_NS.'_queue_event_log', [$this, 'menuPageQueueEventLog']);
        add_action('load-'.$this->menu_page_hooks[GLOBAL_NS.'_queue_event_log'], [$this, 'menuPageQueueEventLogScreen']);

        unset($_menu_title, $_page_title); // Housekeeping.

        /* ----------------------------------------- */

        

        /* ----------------------------------------- */

        
    }

    /**
     * Set plugin-related screen options.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `set-screen-option` filter.
     *
     * @param mixed|bool $what_wp_says `FALSE` if not saving (default).
     *                                 If we set this to any value besides `FALSE`, the option will be saved by WP.
     * @param string     $option       The option being checked; i.e. should we save this option?
     * @param mixed      $value        The current value for this option.
     *
     * @return mixed|bool Returns `$value` for plugin-related options.
     *                    Other we simply return `$what_wp_says`.
     */
    public function setScreenOption($what_wp_says, $option, $value)
    {
        if (strpos($option, GLOBAL_NS.'_') === 0) {
            return $value; // Yes, save this.
        }
        return $what_wp_says;
    }

    /**
     * Menu page screen; for options.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS]` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageOptionsScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS]
        ) {
            return; // Not applicable.
        }
        return; // No screen for this page right now.
    }

    /**
     * Menu page for options.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageOptions()
    {
        new MenuPage('options');
    }

    /**
     * Menu page screen; for subs.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_subs']` action.
     *
     * @see         addMenuPages()
     * @see         MenuPageSubsTable::getTheHiddenColumns()
     */
    public function menuPageSubsScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_subs'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_subs']
        ) {
            return; // Not applicable.
        }
        add_screen_option(
            'per_page',
            [
                'default' => '20', // Default items per page.
                'label'   => __('Per Page', 'comment-mail'),
                'option'  => GLOBAL_NS.'_subs_per_page',
            ]
        );
        add_filter(
            'manage_'.$screen->id.'_columns',
            function () {
                return MenuPageSubsTable::getTheColumns();
            }
        );
        add_filter(
            'get_user_option_manage'.$screen->id.'columnshidden',
            function ($value) {
                return is_array($value) ? $value : MenuPageSubsTable::getTheHiddenColumns();
            }
        );
    }

    /**
     * Menu page for subscriptions.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageSubs()
    {
        new MenuPage('subs');
    }

    /**
     * Menu page screen; for subs.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_subs']` action.
     *
     * @see         addMenuPages()
     * @see         MenuPageSubEventLogTable::getTheHiddenColumns()
     */
    public function menuPageSubEventLogScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_sub_event_log'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_sub_event_log']
        ) {
            return; // Not applicable.
        }
        add_screen_option(
            'per_page',
            [
                'default' => '20', // Default items per page.
                'label'   => __('Per Page', 'comment-mail'),
                'option'  => GLOBAL_NS.'_sub_event_log_entries_per_page',
            ]
        );
        add_filter(
            'manage_'.$screen->id.'_columns',
            function () {
                return MenuPageSubEventLogTable::getTheColumns();
            }
        );
        add_filter(
            'get_user_option_manage'.$screen->id.'columnshidden',
            function ($value) {
                return is_array($value) ? $value : MenuPageSubEventLogTable::getTheHiddenColumns();
            }
        );
    }

    /**
     * Menu page for sub. event log.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageSubEventLog()
    {
        new MenuPage('sub_event_log');
    }

    /**
     * Menu page screen; for queue.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_queue']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageQueueScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_queue'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_queue']
        ) {
            return; // Not applicable.
        }
        add_screen_option(
            'per_page',
            [
                'default' => '20', // Default items per page.
                'label'   => __('Per Page', 'comment-mail'),
                'option'  => GLOBAL_NS.'_queued_notifications_per_page',
            ]
        );
        add_filter(
            'manage_'.$screen->id.'_columns',
            function () {
                return MenuPageQueueTable::getTheColumns();
            }
        );
        add_filter(
            'get_user_option_manage'.$screen->id.'columnshidden',
            function ($value) {
                return is_array($value) ? $value : MenuPageQueueTable::getTheHiddenColumns();
            }
        );
    }

    /**
     * Menu page for mail queue.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageQueue()
    {
        new MenuPage('queue');
    }

    /**
     * Menu page screen; for queue event log.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_queue_event_log']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageQueueEventLogScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_queue_event_log'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_queue_event_log']
        ) {
            return; // Not applicable.
        }
        add_screen_option(
            'per_page',
            [
                'default' => '20', // Default items per page.
                'label'   => __('Per Page', 'comment-mail'),
                'option'  => GLOBAL_NS.'_queue_event_log_entries_per_page',
            ]
        );
        add_filter(
            'manage_'.$screen->id.'_columns',
            function () {
                return MenuPageQueueEventLogTable::getTheColumns();
            }
        );
        add_filter(
            'get_user_option_manage'.$screen->id.'columnshidden',
            function ($value) {
                return is_array($value) ? $value : MenuPageQueueEventLogTable::getTheHiddenColumns();
            }
        );
    }

    /**
     * Menu page for mail queue event log.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageQueueEventLog()
    {
        new MenuPage('queue_event_log');
    }

    /**
     * Menu page screen; for stats.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_stats']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageStatsScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_stats'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_stats']
        ) {
            return; // Not applicable.
        }
        return; // No screen for this page right now.
    }

    /**
     * Menu page for stats.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageStats()
    {
        new MenuPage('stats');
    }

    /**
     * Menu page screen; for pro updater.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_pro_updater']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageProUpdaterScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_pro_updater'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_pro_updater']
        ) {
            return; // Not applicable.
        }
        return; // No screen for this page right now.
    }

    /**
     * Menu page for pro updater.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageProUpdater()
    {
        new MenuPage('pro_updater');
    }

    /**
     * Menu page screen; for import/export.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_import_export']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageImportExportScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_import_export'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_import_export']
        ) {
            return; // Not applicable.
        }
        return; // No screen for this page right now.
    }

    /**
     * Menu page for import/export.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageImportExport()
    {
        new MenuPage('import_export');
    }

    /**
     * Menu page screen; for email templates.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_email_templates']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageEmailTemplatesScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_email_templates'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_email_templates']
        ) {
            return; // Not applicable.
        }
        return; // No screen for this page right now.
    }

    /**
     * Menu page for email templates.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageEmailTemplates()
    {
        new MenuPage('email_templates');
    }

    /**
     * Menu page screen; for site templates.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `'load-'.$this->menu_page_hooks[GLOBAL_NS.'_site_templates']` action.
     *
     * @see         addMenuPages()
     */
    public function menuPageSiteTemplatesScreen()
    {
        $screen = get_current_screen();
        if (!($screen instanceof \WP_Screen)) {
            return; // Not possible.
        }
        if (empty($this->menu_page_hooks[GLOBAL_NS.'_site_templates'])
            || $screen->id !== $this->menu_page_hooks[GLOBAL_NS.'_site_templates']
        ) {
            return; // Not applicable.
        }
        return; // No screen for this page right now.
    }

    /**
     * Menu page for site templates.
     *
     * @since 141111 First documented version.
     * @see   addMenuPages()
     */
    public function menuPageSiteTemplates()
    {
        new MenuPage('site_templates');
    }

    /**
     * Adds link(s) to plugin row on the WP plugins page.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `plugin_action_links_'.plugin_basename(PLUGIN_FILE)` filter.
     *
     * @param array $links An array of the existing links provided by WordPress.
     *
     * @return array Revised array of links.
     */
    public function addSettingsLink(array $links)
    {
        $links[] = '<a href="'.esc_attr($this->utils_url->mainMenuPageOnly()).'">'.__('Settings', 'comment-mail').'</a><br/>';
        if (!IS_PRO) {
            $links[] = '<a href="'.esc_attr($this->utils_url->proPreview()).'">'.__('Preview Pro Features', 'comment-mail').'</a>';
        }
        if (!IS_PRO) {
            $links[] = '<a href="'.esc_attr($this->utils_url->productPage()).'" target="_blank">'.__('Upgrade', 'comment-mail').'</a>';
        }
        return apply_filters(__METHOD__, $links, get_defined_vars());
    }

    /**
     * Adds columns to the list of users.
     *
     * @since       151224 Enhancing users list.
     *
     * @attaches-to `manage_users_columns` filter.
     *
     * @param array $columns Existing columns passed in by filter.
     *
     * @return array Filtered columns.
     */
    public function manageUsersColumns(array $columns)
    {
        $user_columns = &$this->staticKey(__FUNCTION__);
        $user_columns = new UserColumns();

        return $user_columns->filter($columns);
    }

    /**
     * Fills columns in the list of users.
     *
     * @since       151224 Enhancing users list.
     *
     * @attaches-to `manage_users_custom_column` filter.
     *
     * @param mixed      $value   Existing column value passed in by filter.
     * @param string     $column  Column name; passed in by filter.
     * @param int|string $user_id User ID; passed in by filter.
     *
     * @return mixed Filtered column value.
     */
    public function manageUsersCustomColumn($value, $column, $user_id)
    {
        if (!($user_columns = &$this->staticKey('manageUsersColumns'))) {
            return $value; // Not possible to fill; no class instance.
        }
        return $user_columns->maybeFill($value, $column, $user_id);
    }

    /*
     * Pro Update-Related Methods
     */

     

    

    

    /*
     * Admin Notice/Error Related Methods
     */

    /**
     * Enqueue an administrative notice.
     *
     * @since 141111 First documented version.
     *
     * @param string $markup HTML markup containing the notice itself.
     * @param array  $args   An array of additional args; i.e. presentation/style.
     */
    public function enqueueNotice($markup, array $args = [])
    {
        if (!($markup = trim((string) $markup))) {
            return; // Nothing to do here.
        }
        $default_args = [
            'markup'        => '',
            'requires_cap'  => '',
            'for_user_id'   => 0,
            'for_page'      => '',
            'persistent'    => false,
            'persistent_id' => '',
            'dismissable'   => true,
            'transient'     => false,
            'push_to_top'   => false,
            'type'          => 'notice',
        ];
        $args['markup'] = (string) $markup; // + markup.
        $args           = array_merge($default_args, $args);
        $args           = array_intersect_key($args, $default_args);

        $args['requires_cap'] = trim((string) $args['requires_cap']);
        $args['requires_cap'] = $args['requires_cap'] // Force valid format.
            ? strtolower(preg_replace('/\W/', '_', $args['requires_cap'])) : '';

        $args['for_user_id'] = (int) $args['for_user_id'];
        $args['for_page']    = trim((string) $args['for_page']);

        $args['persistent']    = (bool) $args['persistent'];
        $args['persistent_id'] = (string) $args['persistent_id'];
        $args['dismissable']   = (bool) $args['dismissable'];
        $args['transient']     = (bool) $args['transient'];
        $args['push_to_top']   = (bool) $args['push_to_top'];

        if (!in_array($args['type'], ['notice', 'error', 'warning'], true)) {
            $args['type'] = 'notice'; // Use default type.
        }
        ksort($args); // Sort args (by key) for key generation.
        $key = $this->utils_enc->hmacSha256Sign(serialize($args));

        if (!is_array($notices = get_option(GLOBAL_NS.'_notices'))) {
            $notices = []; // Force an array of notices.
        }
        if ($args['push_to_top']) { // Push this notice to the top?
            $this->utils_array->unshiftAssoc($notices, $key, $args);
        } else {
            $notices[$key] = $args; // Default behavior.
        }
        update_option(GLOBAL_NS.'_notices', $notices);
    }

    /**
     * Enqueue an administrative notice; for a particular user.
     *
     * @since 141111 First documented version.
     *
     * @param string $markup HTML markup. See {@link enqueue_notice()}.
     * @param array  $args   Additional args. See {@link enqueue_notice()}.
     */
    public function enqueueUserNotice($markup, array $args = [])
    {
        if (!isset($args['for_user_id'])) {
            $args['for_user_id'] = get_current_user_id();
        }
        $this->enqueueNotice($markup, $args);
    }

    /**
     * Enqueue an administrative error.
     *
     * @since 141111 First documented version.
     *
     * @param string $markup HTML markup. See {@link enqueue_notice()}.
     * @param array  $args   Additional args. See {@link enqueue_notice()}.
     */
    public function enqueueError($markup, array $args = [])
    {
        $this->enqueueNotice($markup, array_merge($args, ['type' => 'error']));
    }

    /**
     * Enqueue an administrative warning.
     *
     * @since 151224 Improving notices.
     *
     * @param string $markup HTML markup. See {@link enqueue_notice()}.
     * @param array  $args   Additional args. See {@link enqueue_notice()}.
     */
    public function enqueueWarning($markup, array $args = [])
    {
        $this->enqueueNotice($markup, array_merge($args, ['type' => 'warning']));
    }

    /**
     * Enqueue an administrative error; for a particular user.
     *
     * @since 141111 First documented version.
     *
     * @param string $markup HTML markup. See {@link enqueue_error()}.
     * @param array  $args   Additional args. See {@link enqueue_notice()}.
     */
    public function enqueueUserError($markup, array $args = [])
    {
        if (!isset($args['for_user_id'])) {
            $args['for_user_id'] = get_current_user_id();
        }
        $this->enqueueError($markup, $args);
    }

    /**
     * Render admin notices; across all admin dashboard views.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `all_admin_notices` action.
     */
    public function allAdminNotices()
    {
        if (!$this->options['enable']) {
            $this->enqueueWarning(sprintf(__('<strong>%1$s is disabled. Please visit the <a href="%2$s">settings</a> and enable the plugin</strong>.', 'comment-mail'), esc_html(NAME), esc_attr($this->utils_url->mainMenuPageOnly())));
        }
        if (!is_array($notices = get_option(GLOBAL_NS.'_notices'))) {
            update_option(GLOBAL_NS.'_notices', ($notices = []));
        }
        if (!$notices) {
            return; // Nothing more to do in this case.
        }
        $user_can_view_notices = current_user_can($this->manage_cap) || current_user_can($this->cap);

        $original_notices = $notices; // Copy.

        foreach ($notices as $_key => $_args) {
            $default_args = [
                'markup'        => '',
                'requires_cap'  => '',
                'for_user_id'   => 0,
                'for_page'      => '',
                'persistent'    => false,
                'persistent_id' => '',
                'dismissable'   => true,
                'transient'     => false,
                'push_to_top'   => false,
                'type'          => 'notice',
            ];
            $_args = array_merge($default_args, $_args);
            $_args = array_intersect_key($_args, $default_args);

            $_args['markup'] = trim((string) $_args['markup']);

            $_args['requires_cap'] = trim((string) $_args['requires_cap']);
            $_args['requires_cap'] = $_args['requires_cap'] // Force valid format.
                ? strtolower(preg_replace('/\W/', '_', $_args['requires_cap'])) : '';

            $_args['for_user_id'] = (int) $_args['for_user_id'];
            $_args['for_page']    = trim((string) $_args['for_page']);

            $_args['persistent']    = (bool) $_args['persistent'];
            $_args['persistent_id'] = (string) $_args['persistent_id'];
            $_args['dismissable']   = (bool) $_args['dismissable'];
            $_args['transient']     = (bool) $_args['transient'];
            $_args['push_to_top']   = (bool) $_args['push_to_top'];

            if (!in_array($_args['type'], ['notice', 'error', 'warning'], true)) {
                $_args['type'] = 'notice'; // Use default type.
            }
            if ($_args['transient']) { // Transient; i.e. single pass only?
                unset($notices[$_key]); // Remove always in this case.
            }
            if (!$user_can_view_notices) { // Primary capability check.
                continue; // Don't display to this user under any circumstance.
            }
            if ($_args['requires_cap'] && !current_user_can($_args['requires_cap'])) {
                continue; // Don't display to this user; lacks required cap.
            }
            if ($_args['for_user_id'] && get_current_user_id() !== $_args['for_user_id']) {
                continue; // Don't display to this particular user ID.
            }
            if ($_args['for_page'] && !$this->utils_env->isMenuPage($_args['for_page'])) {
                continue; // Don't display on this page; i.e. pattern match failure.
            }
            if ($_args['markup']) { // Only display non-empty notices.
                if ($_args['persistent'] && $_args['dismissable']) {
                    $_dismiss_style = 'clear: both;'.
                                      'padding-right: 38px;'.
                                      'position: relative;';
                    $_dismiss_url = $this->utils_url->dismissNotice($_key);
                    $_dismiss     = '<a href="'.esc_attr($_dismiss_url).'">'.
                                      '  <button type="button" class="notice-dismiss">'.
                                      '     <span class="screen-reader-text">Dismiss this notice.</span>'.
                                      '  </button>'.
                                      '</a>';
                } else {
                    $_dismiss       = ''; // Default value; n/a.
                    $_dismiss_style = '';
                }
                $_classes = SLUG_TD.'-menu-page-area'; // Always.

                switch ($_args['type']) {
                    case 'error':
                        $_classes .= ' error'; // Red error
                        break;

                    case 'warning': // This is called 'warning' because the term 'notice' was already used throughout the codebase
                        $_classes .= ' notice notice-warning'; // Yellow warning notice
                        break;

                    case 'updated':
                    default: // Default behavior.
                        $_classes .= ' updated'; // Green informational notice
                }
                $_full_markup = // Put together the full markup; including other pieces.
                    '<div class="notice '.esc_attr($_classes).'" style="'.esc_attr($_dismiss_style).'">'.// clear:both needed to fix StCR options page clash; see http://bit.ly/1V83vQl
                    '  '.$this->utils_string->pWrap($_args['markup'], $_dismiss).
                    '</div>';

                echo apply_filters(__METHOD__.'_notice', $_full_markup, get_defined_vars());
            }
            if (!$_args['persistent']) {
                unset($notices[$_key]); // Once only; i.e. don't show again.
            }
        }
        unset($_key, $_args, $_dismiss_style, $_dismiss_url, $_dismiss, $_classes, $_full_markup); // Housekeeping.

        if ($original_notices !== $notices) {
            update_option(GLOBAL_NS.'_notices', $notices);
        }
    }

    /*
     * Front-Side Scripts
     */

    /**
     * Enqueues front-side scripts.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `wp_print_scripts` hook.
     */
    public function enqueueFrontScripts()
    {
        new FrontScripts();
    }

    /*
     * Login-Related Methods
     */

    /**
     * Login form integration.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `login_form` hook.
     * @attaches-to `login_footer` as a secondary fallback.
     */
    public function loginForm()
    {
        if (!is_null($fired = &$this->staticKey(__FUNCTION__))) {
            return; // We only handle this for a single hook.
        }
        // The first hook to fire this will win automatically.

        $fired = true; // Flag as `TRUE` now.

        new LoginFormAfter();
    }

    /*
     * Post-Related Methods
     */

    /**
     * Post status handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `transition_post_status` action.
     *
     * @param string $new_post_status New post status.
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
     * @param string $old_post_status Old post status.
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
     * @param \WP_Post|null $post Post object instance.
     */
    public function postStatus($new_post_status, $old_post_status, \WP_Post $post = null)
    {
        new PostStatus($new_post_status, $old_post_status, $post);
    }

    /**
     * Post deletion handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `before_delete_post` action.
     *
     * @param int|string $post_id Post ID.
     */
    public function postDelete($post_id)
    {
        new PostDelete($post_id);
    }

    /*
     * Comment-Related Methods
     */

    /**
     * Comment shortlink redirections.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `init` action.
     */
    public function commentShortlinkRedirect()
    {
        if (empty($_REQUEST['c']) || is_admin()) {
            return; // Nothing to do.
        }
        new CommentShortlinkRedirect();
    }

    /**
     * Comment form login integration.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `comment_form_must_log_in_after` hook.
     * @attaches-to `comment_form_top` as a secondary fallback.
     */
    public function commentFormMustLogInAfter()
    {
        if (!is_null($fired = &$this->staticKey(__FUNCTION__))) {
            return; // We only handle this for a single hook.
        }
        // The first hook to fire this will win automatically.

        $fired = true; // Flag as `TRUE` now.

        new CommentFormLogin();
    }

    /**
     * Comment form integration; via filter.
     *
     * @since       151224 Improving comment form compat.
     *
     * @attaches-to `comment_form_submit_field` filter.
     *
     * @param mixed $value Value passed in by a filter.
     *
     * @return mixed The `$value`; possibly filtered here.
     */
    public function commentFormFilterPrepend($value)
    {
        if (!is_null($fired = &$this->staticKey('commentForm'))) {
            return $value; // We only handle this for a single hook.
        }
        // The first hook to fire this will win automatically.
        if (is_string($value)) {
            $fired = true; // Flag as `TRUE` now.

            ob_start(); // Output buffer.
            new CommentFormAfter();
            $value = ob_get_clean().$value;
        }
        return $value;
    }

    /**
     * Comment form integration; via filter.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `comment_form_field_comment` filter.
     *
     * @param mixed $value Value passed in by a filter.
     *
     * @return mixed The `$value`; possibly filtered here.
     */
    public function commentFormFilterAppend($value)
    {
        if (!is_null($fired = &$this->staticKey('commentForm'))) {
            return $value; // We only handle this for a single hook.
        }
        // The first hook to fire this will win automatically.

        if (is_string($value)) {
            $fired = true; // Flag as `TRUE` now.

            ob_start(); // Output buffer.
            new CommentFormAfter();
            $value .= ob_get_clean();
        }
        return $value;
    }

    /**
     * Comment form integration.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `comment_form` action.
     */
    public function commentForm()
    {
        if (!is_null($fired = &$this->staticKey(__FUNCTION__))) {
            return; // We only handle this for a single hook.
        }
        // The first hook to fire this will win automatically.

        $fired = true; // Flag as `TRUE` now.

        new CommentFormAfter();
    }

    /**
     * Comment post handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `comment_post` action.
     *
     * @param int|string $comment_id     Comment ID.
     * @param int|string $comment_status Initial comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     */
    public function commentPost($comment_id, $comment_status)
    {
        new CommentPost($comment_id, $comment_status);
    }

    /**
     * Comment status handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `transition_comment_status` action.
     *
     * @param int|string $new_comment_status New comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     * @param int|string $old_comment_status Old comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     * @param \WP_Comment|null $comment Comment object (now).
     */
    public function commentStatus($new_comment_status, $old_comment_status, \WP_Comment $comment = null)
    {
        new CommentStatus($new_comment_status, $old_comment_status, $comment);
    }

    /**
     * Filters `comment_registration` option in WordPress.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `pre_option_comment_registration` filter.
     *
     * @param int|string|bool $registration_required `FALSE` if not yet defined by another filter.
     *
     * @return int|string|bool Filtered `$comment_registration` value.
     */
    public function preOptionCommentRegistration($registration_required)
    {
        if ($this->options['replies_via_email_enable']) {
            $registration_required = $this->utils_rve->preOptionCommentRegistration($registration_required);
        }
        return $registration_required; // Pass through.
    }

    /**
     * Filters `pre_comment_approved` value in WordPress.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `pre_comment_approved` filter.
     *
     * @param int|string $comment_status New comment status.
     *
     *    One of the following:
     *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
     *       - `1` (aka: `approve`, `approved`),
     *       - or `trash`, `post-trashed`, `spam`, `delete`.
     * @param array $comment_data An array of all comment data associated w/ a new comment being created.
     *
     * @return int|string Filtered `$comment_status` value.
     */
    public function preCommentApproved($comment_status, array $comment_data)
    {
        if ($this->options['replies_via_email_enable']) {
            $comment_status = $this->utils_rve->preCommentApproved($comment_status, $comment_data);
        }
        return $comment_status; // Pass through.
    }

    /*
     * User-Related Methods
     */

    /**
     * User registration handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `user_register` action.
     *
     * @param int|string $user_id User ID.
     */
    public function userRegister($user_id)
    {
        new UserRegister($user_id);
    }

    /**
     * User deletion handler.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `delete_user` action.
     * @attaches-to `wpmu_delete_user` action.
     * @attaches-to `remove_user_from_blog` action.
     *
     * @param int|string $user_id User ID.
     * @param int|string $blog_id Blog ID. Defaults to `0` (current blog).
     */
    public function userDelete($user_id, $blog_id = 0)
    {
        new UserDelete($user_id, $blog_id);
    }

    /*
     * CRON-Related Methods
     */

    /**
     * Extends WP-Cron schedules.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `cron_schedules` filter.
     *
     * @param array $schedules An array of the current schedules.
     *
     * @return array Revised array of WP-Cron schedules.
     */
    public function extendCronSchedules(array $schedules)
    {
        $schedules['every5m']  = ['interval' => 300, 'display' => __('Every 5 Minutes', 'comment-mail')];
        $schedules['every15m'] = ['interval' => 900, 'display' => __('Every 15 Minutes', 'comment-mail')];

        return apply_filters(__METHOD__, $schedules, get_defined_vars());
    }

    /**
     * Checks Cron setup, validates schedules, and reschedules events if necessary.
     *
     * @attaches-to `init` hook.
     *
     * @since 160618 Improving WP Cron setup and validation of schedules
     */
    public function checkCronSetup()
    {
        if ((int) $this->options['crons_setup'] < 1465568335
            || $this->options['crons_setup_on_namespace'] !== __NAMESPACE__
            || $this->options['crons_setup_on_wp_with_schedules'] !== sha1(serialize(wp_get_schedules()))
            || !wp_next_scheduled('_cron_'.GLOBAL_NS.'_queue_processor')
            || !wp_next_scheduled('_cron_'.GLOBAL_NS.'_sub_cleaner')
            || !wp_next_scheduled('_cron_'.GLOBAL_NS.'_log_cleaner')
        ) {
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_queue_processor');
            wp_schedule_event(time() + 60, 'every5m', '_cron_'.GLOBAL_NS.'_queue_processor');

            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_sub_cleaner');
            wp_schedule_event(time() + 60, 'hourly', '_cron_'.GLOBAL_NS.'_sub_cleaner');

            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_log_cleaner');
            wp_schedule_event(time() + 60, 'hourly', '_cron_'.GLOBAL_NS.'_log_cleaner');

            $this->options['crons_setup']                      = time();
            $this->options['crons_setup_on_namespace']         = __NAMESPACE__;
            $this->options['crons_setup_on_wp_with_schedules'] = sha1(serialize(wp_get_schedules()));
            update_option(GLOBAL_NS.'_options', $this->options);
        }
    }

    /**
     * Resets `crons_setup` and clears WP-Cron schedules.
     *
     * @since 160618 Fixing bug with Queue Processor cron disappearing in some scenarios
     *
     * @note This MUST happen upon uninstall and deactivation due to buggy WP_Cron behavior. Events with a custom schedule will disappear when plugin is not active (see http://bit.ly/1lGdr78).
     */
    public function resetCronSetup()
    {
        if (is_multisite()) { // Main site CRON jobs.
            switch_to_blog(get_current_site()->blog_id);
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_queue_processor');
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_sub_cleaner');
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_log_cleaner');
            restore_current_blog(); // Restore current blog.
        } else { // Standard WP installation.
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_queue_processor');
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_sub_cleaner');
            wp_clear_scheduled_hook('_cron_'.GLOBAL_NS.'_log_cleaner');
        }

        if (!empty($GLOBALS[GLOBAL_NS.'_uninstalling'])) {
            return; // Uninstalling, nothing more to do here
        }

        $this->options['crons_setup']                      = $this->default_options['crons_setup'];
        $this->options['crons_setup_on_namespace']         = $this->default_options['crons_setup_on_namespace'];
        $this->options['crons_setup_on_wp_with_schedules'] = $this->default_options['crons_setup_on_wp_with_schedules'];
        update_option(GLOBAL_NS.'_options', $this->options);
    }

    /**
     * Queue processor.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `_cron_'.GLOBAL_NS.'_queue_processor` action.
     */
    public function queueProcessor()
    {
        new QueueProcessor();
    }

    /**
     * Sub cleaner.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `_cron_'.GLOBAL_NS.'_sub_cleaner` action.
     */
    public function subCleaner()
    {
        new SubCleaner();
    }

    /**
     * Log cleaner.
     *
     * @since       141111 First documented version.
     *
     * @attaches-to `_cron_'.GLOBAL_NS.'_log_cleaner` action.
     */
    public function logCleaner()
    {
        new LogCleaner();
    }
}
