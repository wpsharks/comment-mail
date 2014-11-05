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
 *
 * @var \WP_Post|null  $sub_post Post they were subscribed to.
 *    This will be `NULL` if there are any `$error_codes`.
 *
 * @var \stdClass|null $sub_comment Comment they were subcribed to; if applicable.
 *
 * @var array          $error_codes An array of any/all error codes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Unsubscribe', $plugin->text_domain), $site_header); ?>

	<div class="unsubscribe">

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
									echo __('Unknown error; unable to unsubscribe. Sorry!', $plugin->text_domain).
									     ' '.sprintf(__('Please contact &lt;%1$s&gt; for assistance.', $plugin->text_domain),
									                 esc_html($plugin->options['can_spam_postmaster']));
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
			// URL to comments on the post they were subscribed to.
			$sub_post_comments_url = get_comments_link($sub_post->ID);

			// Are comments still open on this post?
			$sub_post_comments_open = comments_open($sub_post->ID);

			// A shorter clip of the full post title.
			$sub_post_title_clip = $plugin->utils_string->clip($sub_post->post_title, 70);

			// URL to comment they were subscribed to; if applicable.
			$sub_comment_url = $sub_comment ? get_comment_link($sub_comment->comment_ID) : '';

			// They were subscribed to their own comment?
			$subscribed_to_own_comment = $sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;

			// Former subscription delivery option label; i.e. a translated display of the option value.
			$sub_deliver_label = $plugin->utils_i18n->deliver_label($sub->deliver);

			// Subscriber's `"name" <email>` w/ HTML markup enhancements.
			$sub_name_email_markup = $plugin->utils_markup->name_email($sub->fname.' '.$sub->lname, $sub->email);

			// Subscriber's last known IP address.
			$sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', $plugin->text_domain);

			// Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
			$sub_last_update_time_ago = $plugin->utils_date->i18n_utc('M jS, Y @ g:i a T', $sub->last_update_time);

			// Unsubscribes (deletes) ALL subscriptions associated w/ their email address.
			$sub_unsubscribe_all_url = $plugin->utils_url->sub_unsubscribe_all_url($sub->email);

			// Subscription creation URL; i.e. so they can add a new subscription if they like.
			$sub_new_url = $plugin->utils_url->sub_manage_sub_new_url();
			?>

			<div class="alert alert-success" style="margin:0;">
				<h4 style="margin:0;">
					<i class="fa fa-check fa-fw"></i> <?php echo __('Unsubscribed successfully. Sorry to see you go!', $plugin->text_domain); ?>
				</h4>
			</div>

			<div class="alert alert-warning text-center pull-right" style="margin:10px 0 20px 20px;">
				<a href="<?php echo esc_attr($sub_unsubscribe_all_url); ?>"
					data-action="<?php echo esc_attr($sub_unsubscribe_all_url); ?>"
					data-confirmation="<?php echo __('Delete (unsubscribe) ALL subscriptions associated with your email address? Are you absolutely sure?', $plugin->text_domain); ?>"
					title="<?php echo __('Delete (unsubscribe) ALL subscriptions associated with your email address?', $plugin->text_domain); ?>">
					<?php echo __('Unsubscribe All', $plugin->text_domain); ?> <i class="fa fa-times-circle pull-right"></i>
				</a>
			</div>

			<h4>
				<?php if($sub_comment): // Unsubscribed from a specific comment? ?>

					<?php if($subscribed_to_own_comment): ?>
						<?php echo sprintf(__('You\'ll no longer be notified about replies to <a href="%1$s">your comment</a>; on:', $plugin->text_domain), esc_html($sub_comment_url)); ?>
					<?php else: // The comment was not authored by this subscriber; i.e. it's not their own. ?>
						<?php echo sprintf(__('You\'ll no longer be notified about replies to <a href="%1$s">comment ID #%2$s</a>; on:', $plugin->text_domain), esc_html($sub_comment_url), esc_html($sub_comment->comment_ID)); ?>
					<?php endif; ?>

				<?php else: // All comments/replies on this post. ?>
					<?php echo __('You\'ll no longer be notified about all comments/replies to:', $plugin->text_domain); ?>
				<?php endif; ?>
			</h4>

			<h4>
				<i class="fa fa-thumb-tack"></i>
				<?php if($sub_comment): // A specific comment? ?>
					&ldquo;<a href="<?php echo esc_attr($sub_comment_url); ?>"><?php echo esc_html($sub_post_title_clip); ?></a>&rdquo;
				<?php else: // Unsubscribing from all comments/replies to this post. ?>
					&ldquo;<a href="<?php echo esc_attr($sub_post_comments_url); ?>"><?php echo esc_html($sub_post_title_clip); ?></a>&rdquo;
				<?php endif; ?>
			</h4>

			<hr style="margin:0 0 10px 0;" />

			<h5 style="font-style:italic; margin:0;">
				<i class="fa fa-frown-o"></i> <?php echo sprintf(__('Too many emails? ~ Please feel free to <a href="%1$s">add a new/different subscription</a> if you like!', $plugin->text_domain), esc_attr($sub_new_url)); ?>
			</h5>

			<?php
			/* Javascript used in this template.
			 ------------------------------------------------------------------------------------------------------------------------ */
			?>
			<script type="text/javascript">
				(function($) // Primary closure w/ jQuery; strict standards.
				{
					'use strict'; // Strict standards enable.

					var plugin = {}, $window = $(window), $document = $(document),

						namespace = '<?php echo $plugin->utils_string->esc_js_sq(__NAMESPACE__); ?>',
						namespaceSlug = '<?php echo $plugin->utils_string->esc_js_sq(str_replace('_', '-', __NAMESPACE__)); ?>',

						ajaxEndpoint = '<?php echo $plugin->utils_string->esc_js_sq(home_url('/')); ?>',
						pluginUrl = '<?php echo $plugin->utils_string->esc_js_sq(rtrim($plugin->utils_url->to('/'), '/')); ?>';

					/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

					plugin.onReady = function() // On DOM ready handler.
					{
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

						$('.unsubscribe').find('[data-action]').on('click', function(e)
						{
							e.preventDefault(), e.stopImmediatePropagation();

							var $this = $(this), data = $this.data();
							if(typeof data.confirmation !== 'string' || confirm(data.confirmation))
								location.href = data.action;
						});
						/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
					};
					$document.ready(plugin.onReady); // On DOM ready handler.
				})(jQuery);
			</script>
			<?php /* ---------------------------------------------------------------------------------------------------------- */ ?>

		<?php endif; // END: if unsubscribed successfully w/ no major errors. ?>

	</div>

<?php echo $site_footer; ?>