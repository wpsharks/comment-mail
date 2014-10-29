<?php
namespace comment_mail;

/**
 * @var plugin         $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string         $site_header Parsed site header template.
 * @var string         $site_footer Parsed site footer template.
 *
 * @var \stdClass|null $sub Subscription object data.
 *    This could be `NULL` if there are any errors.
 *
 * @var array          $error_codes An array of any/all error codes.
 */
?>
<?php echo str_replace('%%title%%', __('Confirmation', $plugin->text_domain), $site_header); ?>

<?php if($error_codes): // Any processing errors? ?>

	<div class="alert alert-danger" role="alert">
		<p style="margin-top:0; font-weight:bold; font-size:120%;">
			<?php echo __('Please review the following error(s):', $plugin->text_domain); ?>
		</p>
		<ul class="list-unstyled" style="margin-bottom:0;">
			<?php foreach($error_codes as $_error_code): ?>
				<li style="margin-top:0; margin-bottom:0;">
					<i class="fa fa-warning fa-fw"></i>
					<?php switch($_error_code)
					{
						case 'missing_sub_key':
							echo __('Subscription key is missing; unable to confirm.', $plugin->text_domain);
							break; // Break switch handler.

						case 'invalid_sub_key':
							echo __('Invalid subscription key; unable to confirm.', $plugin->text_domain);
							break; // Break switch handler.

						case 'sub_already_confirmed':
							echo __('Already confirmed! Thank you.', $plugin->text_domain);
							break; // Break switch handler.

						default: // Anything else that is unexpected/unknown at this time.
							echo __('Unknown error; unable to confirm.', $plugin->text_domain);
					} ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

<?php else: // Confirmed successfully. ?>

	<div class="alert alert-success" role="alert">
		<p style="margin-top:0; margin-bottom:0; font-weight:bold; font-size:120%;">
			<i class="fa fa-check fa-fw"></i>
			<?php echo __('Confirmed successfully. Thank you!', $plugin->text_domain); ?>
		</p>
	</div>

<?php endif; ?>

<?php echo $site_footer; ?>