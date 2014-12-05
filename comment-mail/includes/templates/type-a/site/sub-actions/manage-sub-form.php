<?php
namespace comment_mail;
/**
 * @var plugin         $plugin Plugin class.
 * @var template       $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string         $site_header Parsed site header template.
 * @var string         $site_footer Parsed site footer template.
 *
 * @var boolean        $is_edit Editing an existing subscription?
 * @var string         $sub_key Current subscription key; if editing.
 * @var \stdClass|null $sub Subscription object data; if editing.
 *
 * @var form_fields    $form_fields Form fields class instance.
 * @var callable       $current_value_for Current value for a form field.
 * @var callable       $hidden_inputs Hidden input fields needed by form.
 *
 * @var boolean        $processing Are we (i.e. did we) process a form submission?
 *
 * @var array          $processing_errors An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_error_codes An array of any/all processing error codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_errors_html An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $processing_successes An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_success_codes An array of any/all processing success codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_successes_html An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var boolean        $processing_email_key_change Success; but w/ an email & key change?
 *    This particular case should be handled differently. It's a successful update; but also results
 *    in an error message. An error, because a change of address always results in a key change too.
 *    Since both the email & key have changed, their existing key is now useless; i.e. no longer valid.
 *    When this occurs, we display successes; but nothing else. Messages in the list of successess
 *    will instruct the user to check their email to complete the confirmation process.
 *
 * @var array          $error_codes An array of any/all major error codes; excluding processing error codes.
 *    Note that you should NOT display the form at all, if any major error exist here.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', $is_edit ? __('Edit Subscription', $plugin->text_domain)
	: __('Add New Subscription', $plugin->text_domain), $site_header); ?>

	<div class="manage-sub-form">

		<?php if($error_codes // Changed email; i.e. nullified existing key?
		         && $error_codes[0] === 'invalid_sub_key_after_email_key_change'
		         && $processing && $processing_successes && $processing_email_key_change
		): ?>

			<div class="alert alert-success" style="margin:0;">
				<h4>
					<?php echo __('Submission accepted; nice work!', $plugin->text_domain); ?>
				</h4>
				<ul class="list-unstyled">
					<?php foreach($processing_successes_html as $_success_code => $_success_html): ?>
						<li>
							<i class="fa fa-check fa-fw"></i> <?php echo $_success_html; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<p style="margin-top:1em;">
					<a href="<?php echo esc_attr($plugin->utils_url->sub_manage_summary_url($sub_key, NULL, TRUE)); ?>">
						<i class="fa fa-arrow-circle-left"></i> <?php echo __('Back to My Subscriptions', $plugin->text_domain); ?>
					</a>
				</p>
			</div>

		<?php elseif ($error_codes): // Any other major errors? ?>

			<div class="alert alert-danger" style="margin:0;">
				<h4>
					<?php echo __('Please review the following error(s):', $plugin->text_domain); ?>
				</h4>
				<ul class="list-unstyled">
					<?php foreach($error_codes as $_error_code): ?>
						<li>
							<i class="fa fa-warning fa-fw"></i> <?php switch($_error_code)
							{
								case 'missing_sub_key':
									echo __('Subscription key is missing; unable to edit.', $plugin->text_domain);
									break; // Break switch handler.

								case 'invalid_sub_key':
									echo __('Invalid subscription key; unable to edit.', $plugin->text_domain);
									break; // Break switch handler.

								case 'new_subs_disabled':
									echo __('Sorry; not accepting new subscriptions at this time.', $plugin->text_domain);
									break; // Break switch handler.

								default: // Anything else that is unexpected/unknown at this time.
									echo __('Unknown error; unable to add/edit. Sorry!', $plugin->text_domain);
							} ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php else: // Display form; there are no major errors. ?>

			<?php
			/*
			 * Here we define a few more variables of our own.
			 * All based on what the template makes available to us;
			 * ~ as documented at the top of this file.
			 */
			// Site home page URL; i.e. back to main site.
			$home_url = home_url('/'); // Multisite compatible.

			// Summary return URL; w/ all summary navigation vars preserved.
			$sub_summary_return_url = $plugin->utils_url->sub_manage_summary_url($sub_key, NULL, TRUE);
			?>

			<?php if ($processing && $processing_errors): // Any processing errors? ?>

				<div class="alert alert-danger">
					<h4>
						<?php echo __('Please review the following error(s):', $plugin->text_domain); ?>
					</h4>
					<ul class="list-unstyled">
						<?php foreach($processing_errors_html as $_error_code => $_error_html): ?>
							<li>
								<i class="fa fa-warning fa-fw"></i> <?php echo $_error_html; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

			<?php endif; ?>

			<?php if ($processing && $processing_successes): // Any processing successes? ?>

				<div class="alert alert-success">
					<h4>
						<?php echo __('Submission accepted; nice work!', $plugin->text_domain); ?>
					</h4>
					<ul class="list-unstyled">
						<?php foreach($processing_successes_html as $_success_code => $_success_html): ?>
							<li>
								<i class="fa fa-check fa-fw"></i> <?php echo $_success_html; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<p style="margin-top:1em;">
						<a href="<?php echo esc_attr($sub_summary_return_url); ?>">
							<i class="fa fa-arrow-circle-left"></i> <?php echo __('Back to My Subscriptions', $plugin->text_domain); ?>
						</a>
					</p>
				</div>

			<?php endif; ?>

				<h2 style="margin-top:0;">
					<a href="<?php echo esc_attr($sub_summary_return_url); ?>" title="<?php echo __('Back to My Subscriptions', $plugin->text_domain); ?>">
						<i class="fa fa-arrow-circle-left pull-right"></i>
					</a>
					<?php if($is_edit): ?>
						<?php echo __('Edit Subscription', $plugin->text_domain); ?>
					<?php else: // Creating a new subscription. ?>
						<?php echo __('Add New Subscription', $plugin->text_domain); ?>
					<?php endif; ?>
				</h2>

			<hr />

			<form method="post" enctype="multipart/form-data" novalidate="novalidate" class="table-form">
				<table>
					<tbody>

					<?php echo $form_fields->select_row(
						array(
							'placeholder'         => __('Select a Post ID...', $plugin->text_domain),
							'label'               => __('<i class="fa fa-fw fa-thumb-tack"></i> Post ID #', $plugin->text_domain),
							'name'                => 'post_id', 'required' => TRUE, 'options' => '%%posts%%', 'current_value' => $current_value_for('post_id'),
							'notes_after'         => __('Required; the Post ID you\'re subscribed to.', $plugin->text_domain),
							'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'placeholder' => '', 'current_value_empty_on_0' => TRUE),
						)); ?>
					<?php echo $form_fields->select_row(
					// Note: if you change this row; also change the AJAX template variation.
						array(
							'placeholder'         => __('— All Comments/Replies —', $plugin->text_domain),
							'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID #', $plugin->text_domain),
							'name'                => 'comment_id', 'required' => FALSE, 'options' => '%%comments%%', 'post_id' => $current_value_for('post_id'), 'current_value' => $current_value_for('comment_id'),
							'notes_after'         => __('If empty, you\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $plugin->text_domain),
							'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'current_value_empty_on_0' => TRUE),
						)); ?>
					<?php /* -------------------------------------------------------------------- */ ?>
					<?php echo $form_fields->horizontal_line_row(/* -------------------------------------------------------------------- */); ?>
					<?php /* -------------------------------------------------------------------- */ ?>

					<?php echo $form_fields->input_row(
						array(
							'type'  => 'email', // For `<input>` type.
							'label' => __('<i class="fa fa-fw fa-envelope-o"></i> Email', $plugin->text_domain),
							'name'  => 'email', 'required' => TRUE, 'maxlength' => 100, 'current_value' => $current_value_for('email'),
						)); ?>
					<?php echo $form_fields->input_row(
						array(
							'label' => __('<i class="fa fa-fw fa-pencil-square-o"></i> First Name', $plugin->text_domain),
							'name'  => 'fname', 'required' => TRUE, 'maxlength' => 50, 'current_value' => $current_value_for('fname'),
						)); ?>
					<?php echo $form_fields->input_row(
						array(
							'label' => __('<i class="fa fa-fw fa-level-up fa-rotate-90"></i> Last Name', $plugin->text_domain),
							'name'  => 'lname', 'required' => FALSE, 'maxlength' => 100, 'current_value' => $current_value_for('lname'),
						)); ?>
					<?php /* -------------------------------------------------------------------- */ ?>
					<?php echo $form_fields->horizontal_line_row(/* -------------------------------------------------------------------- */); ?>
					<?php /* -------------------------------------------------------------------- */ ?>

					<?php if($is_edit) // Only for edits.
						// New subscriptions always start w/ an `unconfirmed` status.
						echo $form_fields->select_row(
							array(
								'placeholder' => __('Select a Status...', $plugin->text_domain),
								'label'       => __('<i class="fa fa-fw fa-flag-o"></i> Status', $plugin->text_domain),
								'name'        => 'status', 'required' => TRUE, 'options' => '%%status%%', 'current_value' => $current_value_for('status'),
							)); ?>
					<?php echo $form_fields->select_row(
						array(
							'placeholder' => __('Select a Delivery Option...', $plugin->text_domain),
							'label'       => __('<i class="fa fa-fw fa-paper-plane-o"></i> Deliver', $plugin->text_domain),
							'name'        => 'deliver', 'required' => TRUE, 'options' => '%%deliver%%', 'current_value' => $current_value_for('deliver'),
							'notes_after' => __('Any value that is not <code>asap</code> results in a digest instead of instant notifications.', $plugin->text_domain),
						)); ?>

					</tbody>
				</table>

				<hr />

				<p>
					<?php echo $hidden_inputs(); // Required for processing. ?>

					<?php echo '   <input type="submit"'.
					           ($is_edit  // Are they editing?
						           ? ' value="'.esc_attr(__('Update Subscription', $plugin->text_domain)).'"'
						           : ' value="'.esc_attr(__('Create Subscription', $plugin->text_domain)).'"').
					           '    class="btn btn-primary" />'; ?>

				</p>
			</form>
			<?php
			/* Javascript needed by this template.
			 --------------------------------------------------------------------------------------------------------------------- */
			?>
			<script type="text/javascript">
				(function($) // Primary closure w/ jQuery; strict standards.
				{
					'use strict'; // Strict standards enable.

					var plugin = {}, $window = $(window), $document = $(document),

						namespace = '<?php echo $plugin->utils_string->esc_js_sq(__NAMESPACE__); ?>',
						namespaceSlug = '<?php echo $plugin->utils_string->esc_js_sq(str_replace('_', '-', __NAMESPACE__)); ?>',

						ajaxEndpoint = '<?php echo $plugin->utils_string->esc_js_sq(home_url('/')); ?>',
						pluginUrl = '<?php echo $plugin->utils_string->esc_js_sq(rtrim($plugin->utils_url->to('/'), '/')); ?>',

						chosenOps = {search_contains: true, disable_search_threshold: 10, allow_single_deselect: true};

					/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

					plugin.onReady = function() // On DOM ready handler.
					{
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

						var subFormPostIdProps = { // Initialize.
							$select : $('.manage-sub-form form tr.manage-sub-form-post-id select'),
							$input  : $('.manage-sub-form form tr.manage-sub-form-post-id input'),
							progress: '<img src="' + pluginUrl + '/client-s/images/tiny-progress-bar.gif" />'
						};
						if(subFormPostIdProps.$select.length) // Have select options?
							subFormPostIdProps.lastId = $.trim(subFormPostIdProps.$select.val());
						else subFormPostIdProps.lastId = $.trim(subFormPostIdProps.$input.val());

						subFormPostIdProps.handler = function()
						{
							var $this = $(this), commentIdProps = {},
								requestVars = {}; // Initialize these vars.

							subFormPostIdProps.newId = $.trim($this.val());
							if(subFormPostIdProps.newId === subFormPostIdProps.lastId)
								return; // Nothing to do; i.e. no change, new post ID is the same.
							subFormPostIdProps.lastId = subFormPostIdProps.newId; // Update last ID.

							commentIdProps.$lastRow = $('.manage-sub-form form tr.manage-sub-form-comment-id'),
								commentIdProps.$lastChosenContainer = commentIdProps.$lastRow.find('.chosen-container'),
								commentIdProps.$lastInput = commentIdProps.$lastRow.find(':input');

							if(!commentIdProps.$lastRow.length || !commentIdProps.$lastInput.length)
								return; // Nothing we can do here; expecting a comment ID row.

							commentIdProps.$lastChosenContainer.remove(), // New progress bar.
								commentIdProps.$lastInput.replaceWith($(subFormPostIdProps.progress));

							requestVars[namespace] = {manage: {sub_form_comment_id_row_via_ajax: {post_id: subFormPostIdProps.newId}}},
								$.get(ajaxEndpoint, requestVars, function(newCommentIdRowMarkup)
								{
									commentIdProps.$newRow = $(newCommentIdRowMarkup),
										commentIdProps.$lastRow.replaceWith(commentIdProps.$newRow),
										commentIdProps.$newRow.find('select').chosen(chosenOps);
								});
						};
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

						subFormPostIdProps.$select.on('change', subFormPostIdProps.handler).chosen(chosenOps),
							subFormPostIdProps.$input.on('blur', subFormPostIdProps.handler);

						$('.manage-sub-form form tr.manage-sub-form-comment-id select').chosen(chosenOps);
						$('.manage-sub-form form tr.manage-sub-form-status select').chosen(chosenOps);
						$('.manage-sub-form form tr.manage-sub-form-deliver select').chosen(chosenOps);

						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

						$('.manage-sub-form form').on('submit', function(e)
						{
							var $this = $(this),
								errors = '', // Initialize.
								missingRequiredFields = [];

							$this.find('.form-required :input[required]')
								.each(function(/* Missing required fields? */)
								      {
									      var $this = $(this),
										      val = $.trim($this.val());

									      if(typeof val === 'undefined' || val === '0' || val === '')
										      missingRequiredFields.push(this);
								      });
							$.each(missingRequiredFields, function()
							{
								errors += $.trim($this.find('label[for="' + this.id + '"]').text().replace(/\s+/g, ' ')) + '\n';
							});
							if((errors = $.trim(errors)).length)
							{
								e.preventDefault(),
									e.stopImmediatePropagation(),
									alert(errors);
								return false;
							}
						});
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
					};
					$document.ready(plugin.onReady); // On DOM ready handler.

				})(jQuery); // Fire primary closure; with jQuery.
			</script>
			<?php /* ---------------------------------------------------------------------------------------------------------- */ ?>

		<?php endif; // END: display when no major errors. ?>
	</div>

<?php echo $site_footer; ?>