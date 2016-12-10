<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin   $plugin Plugin class.
 * @var Template $template Template class.
 *
 * Other variables made available in this template file:
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
<?php
/*
 * Here we define a few variables of our own.
 */
// Site home page URL; i.e. back to main site.
$home_url = home_url('/'); // Multisite compatible.

// A clip of the blog's name; as configured in WordPress.
$blog_name_clip = $plugin->utils_string->clip(get_bloginfo('name'));

// Summary return URL; w/ all summary navigation vars preserved.
$current_email          = $this->plugin->utils_sub->currentEmail();
$has_subscriptions      = (boolean) $current_email ? (boolean) $this->plugin->utils_sub->queryTotal(null, ['sub_email' => $current_email, 'status' => 'subscribed', 'sub_email_or_user_ids' => true]) : false;
$sub_summary_return_url = $has_subscriptions ? $plugin->utils_url->subManageSummaryUrl(!empty($sub_key) ? $sub_key : '', null, true) : false;

// Current `host[/path]` with support for multisite network child blogs.
$current_host_path = $plugin->utils_url->currentHostPath();

// Privacy policy URL; as configured in plugin options via the dashboard.
$can_spam_privacy_policy_url = $plugin->options['can_spam_privacy_policy_url'];
?>

<footer class="center-block clearfix">
    <div class="row">

        <div class="col-md-6 text-left">

            <?php if ($parent_template->file() !== 'site/sub-actions/manage-summary.php') : ?>
                <?php // Displays a link leading them back to their subscriptions; if not already there. ?>
                <a href="<?php echo esc_attr($sub_summary_return_url); ?>">
                    <i class="fa fa-arrow-circle-left" aria-hidden="true"></i> <?php echo __('My Comment Subscriptions', 'comment-mail'); ?>
                </a>
                <span class="text-muted">|</span>
            <?php endif; ?>

            <a href="<?php echo esc_attr($home_url); ?>">
                <i class="fa fa-home" aria-hidden="true"></i> <?php echo sprintf(__('Return to <em>%1$s</em>', 'comment-mail'), esc_html($blog_name_clip)); ?>
            </a>

            <?php if ($can_spam_privacy_policy_url) : ?>
                <span class="text-muted">|</span>
                <a href="<?php echo esc_attr($can_spam_privacy_policy_url); ?>">
                    <?php echo __('Privacy Policy', 'comment-mail'); ?>
                </a>
            <?php endif; ?>

        </div>

        <div class="col-md-6 text-right">
            <?php if ($plugin->options['site_footer_powered_by_enable']) : ?>
                <?php echo $plugin->utils_markup->poweredBy(); ?>
            <?php endif; ?>
        </div>

    </div>
</footer>
