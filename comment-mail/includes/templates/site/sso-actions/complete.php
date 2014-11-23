<?php
namespace comment_mail;

/**
 * @var plugin      $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string      $site_header Parsed site header template.
 * @var string      $site_footer Parsed site footer template.
 *
 * @var form_fields $form_fields Form fields class instance.
 * @var callable    $hidden_inputs Hidden input fields needed by form.
 *
 * @var string      $service The SSO service that we are dealing with here.
 *
 * @var string      $action The action that we are dealing with currently.
 *    This is expected to be one of: `callback` or `complete`. An action value of `callback`, will indicate it's
 *    the first time this form is being displayed after the user returned from the SSO service; where some required data was missing.
 *    A value of `complete` indicates this form was submitted to collect the missing data, but something went wrong; i.e. `$error_codes` may exist.
 *
 * @var string      $sso_id A unique ID established by the SSO service provider for this user.
 *
 * @var string      $redirect_to The underlying URL the user is attempting to access.
 *
 * @var string      $fname Current value for the first name field.
 * @var string      $lname Current value for the last name field.
 * @var string      $email Current value for the email address field.
 *
 * @var array       $error_codes An array of any/all error codes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Complete Registration', $plugin->text_domain), $site_header); ?>

	<div class="complete">

		<h2 style="margin-top:0;">
			<?php echo __('Please Take a Moment to Complete Registration', $plugin->text_domain); ?>
		</h2>

		<hr />

		<?php if($error_codes): // Any processing errors? ?>

			<div class="alert alert-danger">
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

								case 'missing_email':
									echo __('Missing email address; please try again.', $plugin->text_domain);
									break; // Break switch handler.

								case 'invalid_email':
									echo __('Invalid email address; please try again.', $plugin->text_domain);
									break; // Break switch handler.

								case 'email_exists': // Only occurs if an account exists w/ a different underlying SSO ID.
									// Otherwise, for existing accounts w/ a matching SSO ID, they will have already been logged-in automatically.
									echo __('An account w/ this email address already exists.', $plugin->text_domain).
									     ' '.sprintf(__('Please <a href="%1$s">log in</a>.', $plugin->text_domain), esc_attr(wp_login_url($redirect_to)));
									break; // Break switch handler.

								default: // Anything else that is unexpected/unknown at this time.
									echo __('Unknown error; unable to complete registration/login. Sorry!', $plugin->text_domain);
							} ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php endif; // END: error/validation display. ?>

		<form method="post" enctype="multipart/form-data" novalidate="novalidate" class="table-form">
			<table>
				<tbody>
				<?php echo $form_fields->input_row(
					array(
						'type'  => 'email', // For `<input>` type.
						'label' => __('<i class="fa fa-fw fa-envelope-o"></i> Email Address', $plugin->text_domain),
						'name'  => 'email', 'required' => TRUE, 'maxlength' => 100, 'current_value' => $email,
					)); ?>
				<?php echo $form_fields->input_row(
					array(
						'label' => __('<i class="fa fa-fw fa-pencil-square-o"></i> First Name', $plugin->text_domain),
						'name'  => 'fname', 'required' => TRUE, 'maxlength' => 50, 'current_value' => $fname,
					)); ?>
				<?php echo $form_fields->input_row(
					array(
						'label' => __('<i class="fa fa-fw fa-level-up fa-rotate-90"></i> Last Name', $plugin->text_domain),
						'name'  => 'lname', 'required' => FALSE, 'maxlength' => 100, 'current_value' => $lname,
					)); ?>
				</tbody>
			</table>

			<hr />

			<p>
				<?php echo $hidden_inputs(); // Required for processing. ?>

				<?php echo '<input type="submit"'.
				           ' value="'.esc_attr(__('Complete Registration', $plugin->text_domain)).'"'.
				           ' class="btn btn-primary" />'; ?>

			</p>
		</form>

	</div>

<?php echo $site_footer; ?>