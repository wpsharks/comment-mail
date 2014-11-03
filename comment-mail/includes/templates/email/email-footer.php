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

<table>
	<tbody>
	<tr>
		<td>
			<p style="font-size:90%; color:#888888;">
				<strong><?php echo __('Contact Info:', $plugin->text_domain); ?></strong><br />
				<?php echo sprintf(__('Website URL: <a href="%1$s">%2$s</a>', $plugin->text_domain), esc_attr(home_url('/')), esc_html(home_url('/'))); ?><br />
				<?php echo sprintf(__('Report Abuse to: <a href="mailto:%1$s">%2$s</a>', $plugin->text_domain), esc_attr(urlencode($plugin->options['can_spam_postmaster'])), esc_html($plugin->options['can_spam_postmaster'])); ?>
			</p>
		</td>
		<td style="padding-left:25px;">
			<p style="font-size:90%; color:#888888;">
				<strong><?php echo __('Our Mailing Address is:', $plugin->text_domain); ?></strong><br />
				<?php echo $plugin->options['can_spam_mailing_address']; ?>
			</p>
		</td>
	</tr>
	</tbody>
</table>

</body>
</html>