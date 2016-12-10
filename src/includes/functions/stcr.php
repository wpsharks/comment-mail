<?php
/**
 * StCR Back Compat.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

function stcr_transition()
{
    $plugin = plugin();

    # Have we already done this?

    if ($plugin->options['stcr_transition_complete']) {
        return; // Not applicable.
    }
    # Do we have StCR options that we can import?

    if (!get_option('subscribe_reloaded_version')) {
        return; // Not applicable.
    }
    # Is the StCR plugin actually installed; i.e., dir exists?

    if (!is_dir(WP_PLUGIN_DIR.'/subscribe-to-comments-reloaded')) {
        return; // Not applicable.
    }
    # StCR to consider during a transition.

    $subscribe_reloaded_show_subscription_box         = get_option('subscribe_reloaded_show_subscription_box');
    $subscribe_reloaded_checked_by_default            = get_option('subscribe_reloaded_checked_by_default');
    $subscribe_reloaded_enable_advanced_subscriptions = get_option('subscribe_reloaded_enable_advanced_subscriptions');
    $subscribe_reloaded_default_subscription_type     = get_option('subscribe_reloaded_default_subscription_type');
    $subscribe_reloaded_from_name                     = get_option('subscribe_reloaded_from_name');
    $subscribe_reloaded_from_email                    = get_option('subscribe_reloaded_from_email');
    $subscribe_reloaded_enable_double_check           = get_option('subscribe_reloaded_enable_double_check');
    $subscribe_reloaded_notify_authors                = get_option('subscribe_reloaded_notify_authors');
    $subscribe_reloaded_admin_bcc                     = get_option('subscribe_reloaded_admin_bcc');
    $subscribe_reloaded_enable_admin_messages         = get_option('subscribe_reloaded_enable_admin_messages');

    # Transition StCR site owners to Comment Mail.

    if (!filter_var($subscribe_reloaded_show_subscription_box, FILTER_VALIDATE_BOOLEAN)) {
        $plugin->options['comment_form_sub_template_enable'] = '0';
    }
    if (filter_var($subscribe_reloaded_enable_advanced_subscriptions, FILTER_VALIDATE_BOOLEAN)) {
        if ($subscribe_reloaded_default_subscription_type === '0') {
            $plugin->options['comment_form_default_sub_type_option'] = '';
        }
        if ($subscribe_reloaded_default_subscription_type === '1') {
            $plugin->options['comment_form_default_sub_type_option'] = 'comments';
        }
        if ($subscribe_reloaded_default_subscription_type === '2') {
            $plugin->options['comment_form_default_sub_type_option'] = 'comment';
        }
    }
    if (!filter_var($subscribe_reloaded_checked_by_default, FILTER_VALIDATE_BOOLEAN)) {
        $plugin->options['comment_form_default_sub_type_option'] = '';
    }
    if ($subscribe_reloaded_from_name && is_string($subscribe_reloaded_from_name)) {
        $plugin->options['from_name'] = trim($subscribe_reloaded_from_name);
    }
    if ($subscribe_reloaded_from_email && is_string($subscribe_reloaded_from_email)) {
        $plugin->options['from_email'] = trim($subscribe_reloaded_from_email);
    }
    if (!filter_var($subscribe_reloaded_enable_double_check, FILTER_VALIDATE_BOOLEAN)) {
        $plugin->options['auto_confirm_force_enable'] = '1';
    }
    if (!filter_var($subscribe_reloaded_notify_authors, FILTER_VALIDATE_BOOLEAN)) {
        $plugin->options['auto_subscribe_post_author_enable'] = '0';
    }
    if (filter_var($subscribe_reloaded_admin_bcc, FILTER_VALIDATE_BOOLEAN)) {
        $plugin->options['auto_subscribe_recipients'] = get_bloginfo('admin_email');
    }
    if (filter_var($subscribe_reloaded_enable_admin_messages, FILTER_VALIDATE_BOOLEAN)) {
        $plugin->options['auto_subscribe_recipients'] = get_bloginfo('admin_email');
    }
    # Save option changes, if any.

    $plugin->options['stcr_transition_complete'] = '1';
    $plugin->optionsSave($plugin->options); // Update options.

    # Notice to existing StCR users now upgrading to Comment Mail.

    $notice = sprintf(__('<h3 style="font-weight:400; margin:0 0 1em 0;">Upgrading from <strong>Subscribe to Comments Reloaded</strong> (StCR) to <strong>%1$s&trade;</strong> %2$s — Welcome! :-)</h3>', 'comment-mail'), esc_html(NAME), $plugin->utils_fs->inlineIconSvg());
    $notice .= '<ul style="margin:0 0 1.3em 3em; list-style:disc;">'.
                    '<li>'.sprintf(__('%1$s automatically imported many of your StCR options (to learn what was imported, see <a href="http://comment-mail.com/r/kb-article-stcr-options-transitioned-by-comment-mail/" target="_blank">this article</a>). It\'s still a good idea to review your %1$s configuration though.', 'comment-mail'), esc_html(NAME)).'</li>'.
                    (!$plugin->options['comment_form_sub_template_enable'] ? '<li>'.sprintf(__('<strong>NOTE:</strong> The built-in %1$s template system has been disabled due to your imported StCR configuration. To enable the built-in comment form template system, please see: <strong>%1$s → Config. Options → Comment Form</strong>.', 'comment-mail'), esc_html(NAME)).'</li>' : '').
                    (ImportStcr::dataExists() ? '<li>'.sprintf(__('<strong>IMPORTANT TIP:</strong> %1$s can import your existing StCR subscribers automatically too! <strong><a href="%2$s">Click here to review the StCR → %1$s import process</a></strong>.', 'comment-mail'), esc_html(NAME), esc_attr($plugin->utils_url->importExportMenuPageOnly())).'</li>' : '').
               '</ul>';
    $plugin->enqueueNotice($notice, ['persistent' => true, 'persistent_id' => 'upgrading-from-stcr']);
}
