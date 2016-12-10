<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin      $plugin Plugin class.
 * @var Template    $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string      $site_header Parsed site header template.
 * @var string      $site_footer Parsed site footer template.
 *
 * @var FormFields $form_fields Form fields class instance.
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
echo str_replace('%%title%%', __('Complete Registration', 'comment-mail'), $site_header); ?>

    <div class="sso-complete">

        <h2 style="margin-top:0;">
            <?php echo __('Please Complete Registration', 'comment-mail'); ?>
        </h2>

        <hr />

        <?php if ($error_codes) : // Any processing errors? ?>

            <div class="alert alert-danger">
                <h4>
                    <?php echo __('Please review the following error(s):', 'comment-mail'); ?>
                </h4>
                <ul class="list-unstyled">
                    <?php foreach ($error_codes as $_error_code) : ?>
                        <li>
                            <i class="fa fa-warning fa-fw" aria-hidden="true"></i>
                            <?php
                            switch ($_error_code) {
                                case 'users_cannot_register':
                                    echo __('Sorry, not accepting new users at this time. Please try again later.', 'comment-mail');
                                    // This will only occur if you forget to enable "anyone can register" in your WP General Settings.
                                    break; // Break switch handler.

                                case 'missing_fname':
                                    echo __('Missing first name; please try again.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'missing_email':
                                    echo __('Missing email address; please try again.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'invalid_email':
                                    echo __('Invalid email address; please try again.', 'comment-mail');
                                    break; // Break switch handler.

                                case 'email_exists': // Only occurs if an account exists w/ a different underlying SSO ID.
                                    // Otherwise, for existing accounts w/ a matching SSO ID, they will have already been logged-in automatically.
                                    echo __('An account w/ this email address already exists.', 'comment-mail').
                                         ' '.sprintf(__('Please <a href="%1$s">log in</a>.', 'comment-mail'), esc_attr(wp_login_url($redirect_to)));
                                    break; // Break switch handler.

                                default: // Anything else that is unexpected/unknown at this time.
                                    echo __('Unknown error; unable to complete registration/login. Sorry!', 'comment-mail');
                            } ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; // END: error/validation display. ?>

        <form method="post" enctype="multipart/form-data" novalidate="novalidate" class="table-form">
            <table>
                <tbody>
                <?php echo $form_fields->inputRow(
                    [
                        'type'  => 'email', // For `<input>` type.
                        'label' => __('<i class="fa fa-fw fa-envelope-o" aria-hidden="true"></i> Email Address', 'comment-mail'),
                        'name'  => 'email', 'required' => true, 'maxlength' => 100, 'current_value' => $email,
                    ]
                ); ?>
                <?php echo $form_fields->inputRow(
                    [
                        'label' => __('<i class="fa fa-fw fa-pencil-square-o" aria-hidden="true"></i> First Name', 'comment-mail'),
                        'name'  => 'fname', 'required' => true, 'maxlength' => 50, 'current_value' => $fname,
                    ]
                ); ?>
                <?php echo $form_fields->inputRow(
                    [
                        'label' => __('<i class="fa fa-fw fa-level-up fa-rotate-90" aria-hidden="true"></i> Last Name', 'comment-mail'),
                        'name'  => 'lname', 'required' => false, 'maxlength' => 100, 'current_value' => $lname,
                    ]
                ); ?>
                </tbody>
            </table>

            <hr />

            <p>
                <?php echo $hidden_inputs(); // Required for processing. ?>

                <?php echo '<input type="submit"'.
                           ' value="'.esc_attr(__('Complete Registration', 'comment-mail')).'"'.
                           ' class="btn btn-primary" />'; ?>

            </p>
        </form>

    </div>

<?php echo $site_footer; ?>
