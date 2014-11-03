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
echo str_replace('%%title%%', __('Unsubscribe', $plugin->text_domain), $site_header); ?>

	<div class="unsubscribe">

		<?php if($error_codes): // Any processing errors? ?>

			<div class="alert alert-danger" style="margin:0;">
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
									echo __('Subscription key is missing; unable to unsubscribe.', $plugin->text_domain);
									break; // Break switch handler.

								case 'invalid_sub_key':
									echo __('Invalid subscription key; unable to unsubscribe.', $plugin->text_domain);
									break; // Break switch handler.

								case 'sub_already_unsubscribed':
									echo __('Already unsubscribed! Sorry to see you go.', $plugin->text_domain);
									break; // Break switch handler.

								default: // Anything else that is unexpected/unknown at this time.
									echo __('Unknown error; unable to confirm.', $plugin->text_domain);
							} ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php else: // Unsubscribed successfully. ?>

			<?php
			/*
			 * Here we define a few more variables of our own.
			 * All based on what the template makes available to us;
			 * ~ as documented at the top of this file.
			 */
			// Post they're unsubscribed from.
			$unsub_post            = get_post($sub->post_id);
			$unsub_post_title_clip = $unsub_post ? $plugin->utils_string->clip($unsub_post->post_title, 30) : '';

			// Comment they're unsubscribed from; if applicable.
			$unsub_comment = $sub->comment_id ? get_comment($sub->comment_id) : NULL;

			$unsubscribed_from_own_comment = // Unsubscribed from their own comment?
				$unsub_comment && strcasecmp($unsub_comment->comment_author_email, $sub->email) === 0;
			?>

			<div class="alert alert-success" style="margin:0;">
				<p style="margin-top:0; margin-bottom:0; font-weight:bold; font-size:120%;">
					<i class="fa fa-check fa-fw"></i>
					<?php echo __('Unsubscribed successfully. Sorry to see you go!', $plugin->text_domain); ?>
				</p>
			</div>

			<!-- @TODO: add more information here. -->

		<?php endif; ?>

	</div>

<?php echo $site_footer; ?>