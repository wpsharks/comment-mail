<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin   $plugin Plugin class.
 * @var Template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var array    $sso_services An array of all "configured" SSO service identifiers.
 *    e.g. `twitter`, `facebook`, `google`, `linkedin`; if one or more of these are configured by the site owner.
 *    Services are "configured" when they have been given an oAuth key/secret in plugin options.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>

<div class="comment-sso-ops">
    <span class="csso-label">
        <?php echo __('Login with:', 'comment-mail'); ?>
    </span>
    <?php foreach ($sso_services as $_sso_service) : ?>
        <a href="<?php echo esc_attr($plugin->utils_url->ssoActionUrl($_sso_service)); ?>" class="<?php echo esc_attr('csso-link csso-'.$_sso_service); ?>"></a>
    <?php endforeach; ?>
</div>

<?php // Styles used in this template. ?>

<style type="text/css">
    .must-log-in
    {
        margin-bottom : 0;
    }
    .comment-sso-ops
    {
        margin : .25em 0 1em 0;
    }
    .comment-sso-ops .csso-label
    {
        vertical-align : middle;
        display        : inline-block;

        opacity        : 0.5;
    }
    .comment-sso-ops .csso-link
    {
        width               : 2em;
        max-width           : 48px;

        height              : 2em;
        max-height          : 48px;

        border-radius       : 5px;

        vertical-align      : middle;
        display             : inline-block;

        margin              : 0 0 0 .25em;

        background-size     : 100%;
        background-position : 0 0;

        -webkit-transition  : all ease 0.1s;
        -moz-transition     : all ease 0.1s;
        -o-transition       : all ease 0.1s;
        -ms-transition      : all ease 0.1s;
        transition          : all ease 0.1s;
    }
    .comment-sso-ops .csso-link:hover
    {
        background-position : 0 -2em;
    }
    <?php foreach ($sso_services as $_sso_service) : ?>
        <?php echo '.comment-sso-ops .csso-link.csso-'.esc_html($_sso_service).
        ' { background-image : url("'.esc_url($plugin->utils_url->to('/src/client-s/images/sso-'.$_sso_service.'.png')).'"); }'."\n"; ?>
    <?php endforeach; ?>
</style>
