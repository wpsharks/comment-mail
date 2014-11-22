<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var array  $sso_services An array of all configured SSO service identifiers.
 *    e.g. `twitter`, `facebook`, `google`, `linkedin`; if one or more of these are configured.
 *    Services are configured when they have been given an oAuth key/secret in plugin options.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>

<div class="comment-sso-ops">
	<?php foreach($sso_services as $_sso_service): ?>
		<?php echo $_sso_service.'<br />'; ?>
	<?php endforeach; ?>
</div>

<?php // Styles used in this template. ?>

<style type="text/css">
	.comment-sso-ops
	{
	}
</style>