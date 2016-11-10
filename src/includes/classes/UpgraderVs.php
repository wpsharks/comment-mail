<?php
/**
 * Upgrader (Version-Specific).
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Upgrader (Version-Specific).
 *
 * @since 141111 First documented version.
 */
class UpgraderVs extends AbsBase
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
     *
     * @param string $prev_version Version they are upgrading from.
     */
    public function __construct($prev_version)
    {
        parent::__construct();

        $this->prev_version = (string) $prev_version;

        $this->runHandlers(); // Run upgrade(s).
    }

    /**
     * Runs upgrade handlers in the proper order.
     *
     * @since 141111 First documented version.
     */
    protected function runHandlers()
    {
        $this->fromLtV141115();
        $this->fromLteV160213();
    }

    /**
     * Upgrading from a version prior to our rewrite.
     *
     * @since 141111 First documented version.
     */
    protected function fromLtV141115()
    {
        if (version_compare($this->prev_version, '141115', '<')) {
            $sql1 = 'ALTER TABLE `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

                    " ADD `insertion_region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region code at time of insertion.',".
                    " ADD `insertion_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country code at time of insertion.',".

                    " ADD `last_region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known geographic region code.',".
                    " ADD `last_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known geographic country code.'";

            $sql2 = 'ALTER TABLE `'.esc_sql($this->plugin->utils_db->prefix().'sub_event_log').'`'.

                    " ADD `region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; at the time of the event.',".
                    " ADD `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; at the time of the event.',".

                    " ADD `region_before` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; before the event, if applicable.',".
                    " ADD `country_before` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; before the event, if applicable.'";

            $sql3 = 'ALTER TABLE `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.

                    " ADD `region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; at the time of the event.',".
                    " ADD `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; at the time of the event.'";

            if ($this->plugin->utils_db->wp->query($sql1) === false
                || $this->plugin->utils_db->wp->query($sql2) === false
                || $this->plugin->utils_db->wp->query($sql3) === false
            ) {
                throw new \exception(__('Query failure.', 'comment-mail'));
            }
        }
    }

    /**
     * Upgrading from a Pro version prior to changes that broke back. compat. w/ Advanced Templates
     *
     * @since 160618 First documented version.
     */
    protected function fromLteV160213()
    {
        if (version_compare($this->prev_version, '160213', '<=') && IS_PRO) {

            $_marker = '<?php /* --------------------------- Legacy Template Backup ---------------------------
 * Comment Mail v160618 included changes that were not backwards compatible with your
 * customized Advanced Template. To prevent problems with the upgrade to v160618, we reset
 * the Advanced Templates to their (new) default and backed up your customized template, a
 * copy of which is included below. You can reference your original template below to reapply
 * those changes to the new default template above. When you are ready to discard this backup,
 * simply delete this comment, and everything below it, and save the template. If you leave
 * this comment here and save the options, your backup below will be also saved.
 *
 * Note: Everything below this comment is not parsed by Comment Mail; it is only here for
 * your reference so that you can re-apply your modifications to the new template above.
 */ ?>';

            foreach ($this->plugin->options as $_key => &$_value) {
                if (strpos($_key, 'template__type_a__') === 0) {
                    $_key_data                  = Template::optionKeyData($_key);
                    $_default_template          = new Template($_key_data->file, $_key_data->type, true);
                    $_option_template_contents  = $_value; // A copy of the option value (potentially incompatible, modified template).
                    $_default_template_contents = $_default_template->fileContents(); // New (safe) default template
                    $_option_template_nws       = preg_replace('/\s+/', '', $_option_template_contents); // Strip whitespace for comparison
                    $_default_template_nws      = preg_replace('/\s+/', '', $_default_template_contents); // Strip whitespace for comparison

                    if (!$_option_template_nws || $_option_template_nws === $_default_template_nws) {
                        continue; // Skip this one, because it's empty, or it's no different from the default template.
                    }

                    $_options_reset[] = $_key; // Save this so that we can let the site owner know which keys were reset.

                    // Add note and append the modified (incompatible) template to the bottom of the new default template
                    $_value = $_default_template_contents."\n\n".$_marker."\n\n".$_option_template_contents;
                }
            }
            unset($_marker, $_key, $_key_data, $_value); // Housekeeping.
            unset($_default_template, $_default_template_nws, $_new_and_old_template);

            if (isset($_options_reset) && is_array($_options_reset) && count($_options_reset) >= 1) {
                $this->plugin->optionsSave($this->plugin->options);

                $_options_to_menu_map = [
                    # PHP-based templates for the site.

                    'template__type_a__site__header___php' => 'Comment Mail → Config Options → Site Templates → Site Header',
                    'template__type_a__site__header_styles___php' => 'Comment Mail → Config Options → Site Templates → Site Header Styles',
                    'template__type_a__site__header_scripts___php' => 'Comment Mail → Config Options → Site Templates → Site Header Scripts',
                    'template__type_a__site__header_tag___php' => 'Comment Mail → Config Options → Site Templates → Site Header Tag',

                    'template__type_a__site__footer_tag___php' => 'Comment Mail → Config Options → Site Templates → Site Footer Tag',
                    'template__type_a__site__footer___php' => 'Comment Mail → Config Options → Site Templates → Site Footer',

                    'template__type_a__site__comment_form__sso_ops___php' => 'Comment Mail → Config Options → Site Templates → Comment Form SSO Options',
                    'template__type_a__site__comment_form__sso_op_scripts___php' => 'Comment Mail → Config Options → Site Templates → Comment Form Scripts for SSO Options',

                    'template__type_a__site__login_form__sso_ops___php' => 'Comment Mail → Config Options → Site Templates → Login Form SSO Options',
                    'template__type_a__site__login_form__sso_op_scripts___php' => 'Comment Mail → Config Options → Site Templates → Login Form Scripts for SSO Options',

                    'template__type_a__site__sso_actions__complete___php' => 'Comment Mail → Config Options → Site Templates → Single Sign-on Registration Complete',

                    'template__type_a__site__comment_form__sub_ops___php' => 'Comment Mail → Config Options → Site Templates → Comment Form Subscr. Options',
                    'template__type_a__site__comment_form__sub_op_scripts___php' => 'Comment Mail → Config Options → Site Templates → Comment Form Scripts for Subscr. Options',

                    'template__type_a__site__sub_actions__confirmed___php' => 'Comment Mail → Config Options → Site Templates → Subscr. Confirmed',
                    'template__type_a__site__sub_actions__unsubscribed___php' => 'Comment Mail → Config Options → Site Templates → Unsubscribed',
                    'template__type_a__site__sub_actions__unsubscribed_all___php' => 'Comment Mail → Config Options → Site Templates → Unsubscribed All',
                    'template__type_a__site__sub_actions__manage_summary___php' => 'Comment Mail → Config Options → Site Templates → Summary',
                    'template__type_a__site__sub_actions__manage_sub_form___php' => 'Comment Mail → Config Options → Site Templates → Add/Edit Form',
                    'template__type_a__site__sub_actions__manage_sub_form_comment_id_row_via_ajax___php' => 'Comment Mail → Config Options → Site Templates → Comment ID Row via AJAX',

                    # PHP-based templates for emails.

                    'template__type_a__email__header___php' => 'Comment Mail → Config Options → Email Templates → Email Header',
                    'template__type_a__email__header_styles___php' => 'Comment Mail → Config Options → Email Templates → Email Header Styles',
                    'template__type_a__email__header_scripts___php' => 'Comment Mail → Config Options → Email Templates → Email Header Scripts',
                    'template__type_a__email__header_tag___php' => 'Comment Mail → Config Options → Email Templates → Email Header Tag',

                    'template__type_a__email__footer_tag___php' => 'Comment Mail → Config Options → Email Templates → Email Footer Tag',
                    'template__type_a__email__footer___php' => 'Comment Mail → Config Options → Email Templates → Email Footer',

                    'template__type_a__email__sub_confirmation__subject___php' => 'Comment Mail → Config Options → Email Templates → Subscr. Confirmation Subject',
                    'template__type_a__email__sub_confirmation__message___php' => 'Comment Mail → Config Options → Email Templates → Subscr. Confirmation Message Body',

                    'template__type_a__email__comment_notification__subject___php' => 'Comment Mail → Config Options → Email Templates → Comment Notification Subject',
                    'template__type_a__email__comment_notification__message___php' => 'Comment Mail → Config Options → Email Templates → Comment Notification Message Body'
                ];

                $_options_reset_html = ''; // Initialize

                foreach ($_options_reset as $_key => $_option) { // Build list of menu paths to templates that have been reset
                    $_options_reset_html .= '<li>'.$_options_to_menu_map[$_option].'</li>'."\n";
                }

                // Let the site owner know we've reset their customized Advanced Templates
                $_notice = sprintf(__('<p><strong>%1$s has detected that your customized Advanced Templates are incompatible with this version.</strong></p>', 'comment-mail'), esc_html(NAME));
                $_notice .= '<p><strong>To retain your template customizations, please read the following message carefully.</strong></p>';
                $_notice .= sprintf(__('<p>%1$s v%2$s was released with a rewritten and improved codebase. This came with the unfortunate side effect of breaking backwards compatibility with any Advanced Templates that had been customized in a previous version.</p>', 'comment-mail'), esc_html(NAME), $this->plugin->options['version']);
                $_notice .= '<p>All of your customized Advanced Templates have been reset to their new default and your customizations have been backed up. You will find the backup of your old customized template appended to the bottom of the new template, separated with a  <code>Legacy Template Backup</code> PHP comment. The following templates have been reset:</p>';
                $_notice .= '<ul style="margin:0 0 1.3em 3em; list-style:disc;">'.
                           $_options_reset_html .
                           '</ul>';
                $_notice .= '<p><strong>Please review the above templates and re-apply your customizations.</strong></p>';
                $_notice .= '<p><em>Note: Once this message has been dismissed, the above list will will no longer be accessible.</em></p>';
                $this->plugin->enqueueNotice($_notice, ['persistent' => true, 'persistent_id' => 'vs-upgrade-advanced-templates-reset', 'type' => 'warning']);
            }
            unset($_key, $_option, $_notice);
            unset($_options_reset, $_options_to_menu_map, $_options_reset_html);
        }
    }
}
