<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string $email_footer_easy Parsed easy footer template file.
 *    This is a partial footer template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @var string $template_file Relative path to the current template file.
 *
 * @note This file is automatically included as a child of other templates.
 *    Therefore, this template will ALSO receive any variable(s) passed to the parent template file,
 *    where the parent automatically calls upon this template. In short, if you see a variable documented in
 *    another template file, that particular variable will ALSO be made available in this file too;
 *    as this file is automatically included as a child of other parent templates.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo $email_footer_easy; ?>

<hr />

<?php // Open table to hold important footer links. ?>

<table>
	<tbody>

	<?php // CAN-SPAM compliance links; manage/unsubscribe. ?>

	<?php if(stripos($template_file, 'notification') !== FALSE && !empty($sub)): ?>

		<?php
		/*
		 * Here we define a few more variables of our own.
		 * All based on what the template makes available to us;
		 * ~ as documented at the top of this file.
		 */
		// Summary URL; i.e. comment subscription management area.
		$sub_summary_url = $plugin->utils_url->sub_manage_summary_url($sub->key);

		// Unsubscribes (deletes) the subscription this email is associated with.
		$sub_unsubscribe_url = $plugin->utils_url->sub_unsubscribe_url($sub->key);

		// Unsubscribes (deletes) ALL subscriptions associated w/ their email at the same time.
		$sub_unsubscribe_all_url = $plugin->utils_url->sub_unsubscribe_all_url($sub->email);

		// Subscription creation URL; user may create a new subscription.
		$sub_new_url = $plugin->utils_url->sub_manage_sub_new_url();
		?>

		<tr>
			<td>
				<p style="font-size:90%; color:#888888;">
					<strong><?php echo __('Manage Subscription', $plugin->text_domain); ?></strong><br />
					<?php echo sprintf(__('<a href="%1$s">My Comment Subscriptions (Summary)</a>', $plugin->text_domain), esc_attr($sub_summary_url)); ?><br />
					<?php echo sprintf(__('<a href="%1$s">One-Click Unsubscribe</a>', $plugin->text_domain), esc_attr($sub_unsubscribe_url)); ?>
				</p>
			</td>
			<td style="padding-left:25px;">
				<p style="font-size:90%; color:#888888;">
					<strong><?php echo __('Create New Subscription?', $plugin->text_domain); ?></strong><br />
					<?php echo sprintf(__('<a href="%1$s">Add New Comment Subscription</a>', $plugin->text_domain), esc_attr($sub_new_url)); ?>
				</p>
			</td>
		</tr>

		<tr style="height:10px;">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

	<?php endif; ?>

	<?php // CAN-SPAM compliance links; contact info / mailing address. ?>

	<?php
	/*
	 * Here we define a few more variables of our own.
	 * All based on what the template makes available to us;
	 * ~ as documented at the top of this file.
	 */
	// Site home page URL; i.e. to main site.
	$home_url = home_url('/'); // Multisite compatible.

	// CAN-SPAM postmaster; as configured in plugin options.
	$can_spam_postmaster = $plugin->options['can_spam_postmaster'];

	// CAN-SPAM mailing address; as configured in plugin options.
	$can_spam_mailing_address = $plugin->options['can_spam_mailing_address'];
	?>

	<tr>
		<td>
			<p style="font-size:90%; color:#888888;">
				<strong><?php echo __('Contact Info', $plugin->text_domain); ?></strong><br />
				<?php echo sprintf(__('Website URL: <a href="%1$s">%2$s</a>', $plugin->text_domain), esc_attr($home_url), esc_html($home_url)); ?><br />
				<?php echo sprintf(__('Report Abuse to: <a href="mailto:%1$s">%2$s</a>', $plugin->text_domain), esc_attr(urlencode($can_spam_postmaster)), esc_html($can_spam_postmaster)); ?>
			</p>
		</td>
		<td style="padding-left:25px;">
			<p style="font-size:90%; color:#888888;">
				<strong><?php echo __('Our Mailing Address is:', $plugin->text_domain); ?></strong><br />
				<?php echo $can_spam_mailing_address; ?>
			</p>
		</td>
	</tr>

	<?php // Close table. ?>

	</tbody>
</table>

<?php // Close body. ?>

</body>
</html>