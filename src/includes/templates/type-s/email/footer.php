<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin   $plugin Plugin class.
 * @var Template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string   $email_footer_tag Parsed <footer> tag template file.
 *    This is a partial footer template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @var Template $parent_template Parent template class reference.
 *
 * @note This file is automatically included as a child of other templates.
 *    Therefore, this template will ALSO receive any variable(s) passed to the parent template file,
 *    where the parent automatically calls upon this template. In short, if you see a variable documented in
 *    another template file, that particular variable will ALSO be made available in this file too;
 *    as this file is automatically included as a child of other parent templates.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>

<hr style="margin-top:10em;" />

<?php echo $email_footer_tag; ?>

<?php // CAN-SPAM compliance links; manage/unsubscribe. ?>

<?php if (stripos($parent_template->file(), '/comment-notification/') && !empty($sub)) : ?>

    <?php
    /*
     * Here we define a few more variables of our own.
     * All based on what the template makes available to us;
     * ~ as documented at the top of this file.
     */
    // Summary URL; i.e. comment subscription management area.
    $sub_summary_url = $plugin->utils_url->subManageSummaryUrl($sub->key);

    // Unsubscribes (deletes) the subscription this email is associated with.
    $sub_unsubscribe_url = $plugin->utils_url->subUnsubscribeUrl($sub->key);

    // Unsubscribes (deletes) ALL subscriptions associated w/ their email at the same time.
    $sub_unsubscribe_all_url = $plugin->utils_url->subUnsubscribeAllUrl($sub->email);

    // Subscription creation URL; user may create a new subscription.
    $sub_new_url = $plugin->utils_url->subManageSubNewUrl();
    ?>

    <p style="color:#888888;">
        <strong><?php echo __('Manage Subscriptions', 'comment-mail'); ?></strong><br />
        <?php echo sprintf(__('<a href="%1$s">My Comment Subscriptions</a>', 'comment-mail'), esc_attr($sub_summary_url)); ?><br />
        &nbsp;&#42774;&nbsp; <?php echo sprintf(__('<a href="%1$s">One-Click Unsubscribe</a>', 'comment-mail'), esc_attr($sub_unsubscribe_url)); ?>
    </p>
    <?php // Disable this functionality for now; see http://bit.ly/1OYd4ie ?>
    <?php // @todo Remove completely, or reconsider, Add New Subscription from front-end ?>
    <?php /*
    <p style="color:#888888;">
        <strong><?php echo __('Create New Subscription?', 'comment-mail'); ?></strong><br />
        <?php echo sprintf(__('<a href="%1$s">Add New Comment Subscription</a>', 'comment-mail'), esc_attr($sub_new_url)); ?>
    </p>
     */?>

<?php endif; ?>

<?php // CAN-SPAM compliance links; contact info / mailing address. ?>

<?php
/*
 * Here we define a few more variables of our own.
 * All based on what the template makes available to us;
 * ~ as documented at the top of this file.
 */
// Site home page URL; i.e. to main site.
$home_url = home_url('/'); // Multisite compatible.

// CAN-SPAM postmaster; as configured in plugin options.
$can_spam_postmaster = $plugin->options['can_spam_postmaster'];

// CAN-SPAM mailing address; as configured in plugin options.
$can_spam_mailing_address = $plugin->options['can_spam_mailing_address'];

// Privacy policy URL; as configured in plugin options via the dashboard.
$can_spam_privacy_policy_url = $plugin->options['can_spam_privacy_policy_url'];
?>

<p style="color:#888888;">
    <strong><?php echo __('Contact Info', 'comment-mail'); ?></strong><br />
    <?php echo sprintf(__('Website URL: <a href="%1$s">%2$s</a>', 'comment-mail'), esc_attr($home_url), esc_html($home_url)); ?><br />
    <?php echo sprintf(__('Report Abuse to: <a href="mailto:%1$s">%2$s</a>', 'comment-mail'), esc_attr(urlencode($can_spam_postmaster)), esc_html($can_spam_postmaster)); ?>
    <?php if ($can_spam_privacy_policy_url) : ?><br />
        <?php echo sprintf(__('Privacy Policy: <a href="%1$s">%2$s</a>', 'comment-mail'), esc_attr($can_spam_privacy_policy_url), esc_html($can_spam_privacy_policy_url)); ?>
    <?php endif; ?>
</p>
<p style="color:#888888;">
    <strong><?php echo __('Our Mailing Address', 'comment-mail'); ?></strong><br />
    <?php echo $can_spam_mailing_address; ?>
</p>

<?php if ($plugin->options['email_footer_powered_by_enable']) : ?>
    <hr /><p style="color:#888888;">
        <?php echo $plugin->utils_markup->poweredBy(); ?>
    </p>
<?php endif; ?>

<?php // Close body. ?>
</body>
</html>
