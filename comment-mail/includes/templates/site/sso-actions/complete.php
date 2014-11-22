<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string $site_header Parsed site header template.
 * @var string $site_footer Parsed site footer template.
 *
 * @var string $action_url Form action URL.
 *
 * @var string $fname Current value for the first name field.
 * @var string $lname Current value for the last name field.
 * @var string $email Current value for the email address field.
 *
 * @var array  $error_codes An array of any/all error codes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Complete Registration', $plugin->text_domain), $site_header); ?>

	<div class="complete">

		<?php if($error_codes): // Any processing errors? ?>

			<div class="alert alert-danger" style="margin:0;">
				<h4>
					<?php echo __('Please review the following error(s):', $plugin->text_domain); ?>
				</h4>
				<ul class="list-unstyled">
					<?php foreach($error_codes as $_error_code): ?>
						<li>
							<i class="fa fa-warning fa-fw"></i> <?php switch($_error_code)
							{
								case 'missing_fname':
									echo __('Missing first name; please try again.', $plugin->text_domain);
									break; // Break switch handler.

								case 'missing_lname':
									echo __('Missing last name; please try again.', $plugin->text_domain);
									break; // Break switch handler.

								case 'missing_email':
									echo __('Missing email address; please try again.', $plugin->text_domain);
									break; // Break switch handler.

								case 'invalid_email':
									echo __('Invalid email address; please try again.', $plugin->text_domain);
									break; // Break switch handler.

								default: // Anything else that is unexpected/unknown at this time.
									echo __('Unknown error; unable to auto-complete registration. Sorry!', $plugin->text_domain);
							} ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php endif; // END: error/validation display. ?>

		<?php // @TODO Test and complete this template. ?>

		<form action="<?php echo esc_attr($action_url); ?>" method="POST">

		</form>

	</div>

<?php echo $site_footer; ?>