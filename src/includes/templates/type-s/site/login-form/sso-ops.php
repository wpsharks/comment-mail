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

<?php ob_start(); ?>
    <style type="text/css">
        .login-sso-ops
        {
            text-align : center;
            margin     : 0 0 1em 0;
        }
        .login-sso-ops .lsso-label
        {
            vertical-align : middle;
            display        : inline-block;

            opacity        : 0.5;
        }
        .login-sso-ops .lsso-link
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
        .login-sso-ops .lsso-link:hover
        {
            background-position : 0 -2em;
        }
        body > .login-sso-ops /* When hooked to `login_footer`. */
        {
            background   : #FFFFFF;
            border       : 1px solid #333333;
            border-width : 1px 0 1px 0;

            margin       : 2em 0 0 0;
            padding      : .5em 0 .5em 0;
        }
        <?php foreach ($sso_services as $_sso_service) : ?>
            <?php echo '.login-sso-ops .lsso-link.lsso-'.esc_html($_sso_service).
            ' { background-image : url("'.esc_url($plugin->utils_url->to('/src/client-s/images/sso-'.$_sso_service.'.png')).'"); }'."\n"; ?>
        <?php endforeach; ?>
    </style>
<?php $css_styles = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php foreach ($sso_services as $_sso_service) : ?>
    <a href="<?php echo esc_attr($plugin->utils_url->ssoActionUrl($_sso_service)); ?>" class="<?php echo esc_attr('lsso-link lsso-'.$_sso_service); ?>"></a>
<?php endforeach; ?>
<?php $service_links = ob_get_clean(); ?>

<?php echo $template->snippet(
    'sso-ops.php',
    [
        '[css_styles]'    => $css_styles,
        '[service_links]' => $service_links,
    ]
); ?>
