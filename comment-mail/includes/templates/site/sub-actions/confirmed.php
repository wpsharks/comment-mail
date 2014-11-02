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
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Confirmation', $plugin->text_domain), $site_header); ?>

	<div class="confirm">

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

			<?php
			/*
			 * Here we define a few more variables of our own.
			 * All based on what the template makes available to us;
			 * ~ as documented at the top of this file.
			 */
			// Post they're subscribed to.
			$sub_post               = get_post($sub->post_id);
			$sub_post_comments_url  = get_comments_link($sub->post_id);
			$sub_post_comments_open = comments_open($sub->post_id);
			$sub_post_title_clip    = $sub_post ? $plugin->utils_string->clip($sub_post->post_title) : '';

			// Comment they're subscribed to; if applicable;
			$sub_comment     = $sub->comment_id ? get_comment($sub->comment_id) : NULL;
			$sub_comment_url = $sub->comment_id ? get_comment_link($sub->comment_id) : '';

			$subscribed_to_own_comment = // Subscribed to their own comment?
				$sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;

			// Subscriber's `"name" <email>` w/ HTML markup enhancements.
			$sub_name_email_markup = $plugin->utils_markup->name_email($sub->fname.' '.$sub->lname, $sub->email);

			// Subscriber's last known IP address.
			$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', $plugin->text_domain);

			// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
			$sub_last_update_time_ago = $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time);
			?>

			<div class="alert alert-success" role="alert">
				<p style="margin-top:0; margin-bottom:0; font-weight:bold; font-size:120%;">
					<i class="fa fa-check fa-fw"></i>
					<?php echo __('Confirmed successfully. Thank you!', $plugin->text_domain); ?>
				</p>
			</div>

		<?php endif; ?>

	</div>

<?php echo $site_footer; ?>