<?php
/**
 * Menu Pages
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{

	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page'))
	{
		/**
		 * Menu Pages
		 *
		 * @since 141111 First documented version.
		 */
		class menu_page extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $which Which menu page to display?
			 */
			public function __construct($which)
			{
				parent::__construct();

				$which = $this->plugin->utils_string->trim((string)$which, '', '_');
				if($which && method_exists($this, $which.'_'))
					$this->{$which.'_'}();
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function options_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-options-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-options-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0 && isset($_this->plugin->options[$key]))
					{
						if($_this->plugin->options[$key])
							return $_this->plugin->options[$key];

						$data             = template::option_key_data($key);
						$default_template = new template($data->file, $data->type, TRUE);

						return $default_template->file_contents();
					}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				$shortcode_details = function ($shortcodes) use ($_this)
				{
					$detail_lis = array(); // Initialize.

					foreach($shortcodes as $_shortcode => $_details)
						$detail_lis[] = '<li><code>'.esc_html($_shortcode).'</code>&nbsp;&nbsp;'.$_details.'</li>';
					unset($_shortcode, $_details); // Housekeeping.

					if($detail_lis) // If we have shortcodes, let's list them.
						$details = '<ul class="pmp-list-items" style="margin-top:0; margin-bottom:0;">'.implode('', $detail_lis).'</ul>';
					else $details = __('No shortcodes for this template at the present time.', $_this->plugin->text_domain);

					return '<a href="#" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('shortcodes explained', $_this->plugin->text_domain).'</a>';
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-options '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Plugin Options', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Basic Configuration (Required)', $this->plugin->text_domain).
				     '            <small><span'.($this->plugin->install_time() > strtotime('-1 hour') ? ' class="pmp-hilite"' : '').'>'.
				     sprintf(__('Review these basic options and %1$s&trade; will be ready-to-go!', $this->plugin->text_domain), esc_html($this->plugin->name)).'</span></small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Enable %1$s&trade; Functionality?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'field_class'     => 'pmp-if-change',
						               'name'            => 'enable',
						               'current_value'   => $current_value_for('enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => sprintf(__('Yes, enable %1$s&trade; (recommended)', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable %1$s&trade; temporarily', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<div class="pmp-note pmp-warning pmp-if-disabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.sprintf(__('When %1$s&trade; is disabled in this way:', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.__('Comment Subscription Options (options for receiving email notifications regarding comments/replies) no longer appear on comment forms. In short, no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain).'</li>'.
						                                    '      <li>'.sprintf(__('The mail queue processor will stop processing, until such time as the plugin is renabled; i.e., no more email notifications. However, mail queue injections will continue; just no queue processing. This means that when somebody posts a comment, %1$s will still check if there are any subscribers. If there are, %1$s will inject the queue with any notifications that should be sent once queue processing is resumed. If it is desirable that any/all queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %2$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</li>'.
						                                    '   </ul>'.
						                                    '   <p><em>'.sprintf(__('<strong>Note:</strong> If you want to disable %1$s&trade; completely, please deactivate it from the plugins menu in WordPress.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</em></p>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-if-enabled-show"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Allow New Subsciptions?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'new_subs_enable',
						                'current_value'   => $current_value_for('new_subs_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, allow new subscriptions (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disallow new subscriptions temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('If you set this to <code>No</code> (disallow), Comment Subscription Options (options for receiving email notifications regarding comments/replies) no longer appear on comment forms. In short, no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Enable Mail Queue Processing?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'queue_processing_enable',
						                'current_value'   => $current_value_for('queue_processing_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, enable mail queue processing (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disable mail queue processing temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('If you set this to <code>No</code> (disabled), all mail queue processing will stop. In short, no more email notifications will be sent. However, mail queue injections will continue; just no queue processing. This means that when somebody posts a comment, %1$s will still check if there are any subscribers. If there are, %1$s will inject the queue with any notifications that should be sent once queue processing is resumed. If it is desirable that any/all queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %2$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel(__('Enable/Disable', $this->plugin->text_domain), $_panel_body, array('open' => !$this->plugin->options['enable']));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Uninstall on Plugin Deletion, or Safeguard Data?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'uninstall_safeguards_enable',
						               'current_value'   => $current_value_for('uninstall_safeguards_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Safeguards on; i.e., protect my plugin options &amp; comment subscriptions (recommended)', $this->plugin->text_domain),
							               '0' => sprintf(__('Safeguards off; uninstall (completely erase) %1$s on plugin deletion', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<p>'.sprintf(__('By default, if you delete %1$s using the plugins menu in WordPress, no data is lost. However, if you want to completely uninstall %1$s you should turn Safeguards off, and <strong>THEN</strong> deactivate &amp; delete %1$s from the plugins menu in WordPress. This way %1$s will erase your options for the plugin, erase database tables created by the plugin, remove subscriptions, terminate CRON jobs, etc. In short, when Safeguards are off, %1$s erases itself from existence completely when you delete it.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Data Safeguards', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('<code>From</code> Name:', $this->plugin->text_domain),
						               'placeholder'   => __('e.g., MySite.com', $this->plugin->text_domain),
						               'name'          => 'from_name',
						               'current_value' => $current_value_for('from_name'),
						               'notes_after'   => '<p>'.sprintf(__('All emails sent by %1$s will have a specific <code>%3$s: "<strong>Name</strong>" &lt;email&gt;</code> header, indicating that each message was sent by your site; not by a specific individual. It\'s a good idea to use something like: <code>MySite.com</code>. This name will appear beside the subject line in most email clients. Provide the <strong>name only</strong>, excluding quotes please. Examples: <code>MySite.com</code>, <code>Acme&trade;</code>, <code>MyCompany, Inc.</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('<code>From</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g., moderator@mysite.com', $this->plugin->text_domain),
						                'name'          => 'from_email',
						                'current_value' => $current_value_for('from_email'),
						                'notes_after'   => '<p>'.sprintf(__('All emails sent by %1$s will have a specific <code>%3$s: "Name" &lt;<strong>email</strong>&gt;</code> header, indicating that each message was sent by your site; not by a specific individual. It\'s a good idea to use something like: <code>moderator@mysite.com</code>. This email will appear beside the subject line in most email clients. Provide the <strong>email address only</strong>, excluding &lt;&gt; brackets please. Examples: <code>moderator@mysite.com</code>, <code>postmaster@example.com</code>, <code>notifications@example.com</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('<code>Reply-To</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g., noreply@example.com', $this->plugin->text_domain),
						                'name'          => 'reply_to_email',
						                'current_value' => $current_value_for('reply_to_email'),
						                'notes_after'   => '<p>'.sprintf(__('All emails sent by %1$s can have a specific <code>%2$s:</code> email header, which might be different from the address that %1$s messages are actually sent <code>%3$s</code>. This makes it so that if someone happens to reply to an email notification, that reply will be directed to a specific email address that you prefer. Some site owners like to use something like <code>noreply@mysite.com</code>, while others find it best to use a real email address that can monitor replies. It\'s a matter of preference.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'.
						                                   ($this->plugin->is_pro ? '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> If you happen to enable a %1$s&trade; RVE Handler (Replies via Email), this value is ignored in favor of the <code>Reply-To</code> address configured for your RVE Handler. In other words, if you enable Replies via Email, you could simply leave this blank if you like. If RVE is enabled, the <code>Reply-To</code> address for the RVE Handler receives precedence always. The address you configure here will not be applied in that case.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>' : '')
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'        => 'email',
						                'label'       => __('Test Mail Settings?', $this->plugin->text_domain),
						                'placeholder' => __('e.g., me@mysite.com', $this->plugin->text_domain),
						                'name'        => 'mail_test', // Not an actual option key; but the `save_options` handler picks this up.
						                'notes_after' => sprintf(__('Enter an email address to have %1$s&trade; send a test message when you <strong>save</strong> these options, and report back about any success or failure.', $this->plugin->text_domain), esc_html($this->plugin->name)),
					                )).
				                '  </tbody>'.
				                ' </table>';

				echo $this->panel(__('Email Message Headers', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'type'          => 'email',
						               'label'         => __('Postmaster Email Address', $this->plugin->text_domain),
						               'placeholder'   => __('e.g., postmaster@example.com or abuse@example.com', $this->plugin->text_domain),
						               'name'          => 'can_spam_postmaster',
						               'current_value' => $current_value_for('can_spam_postmaster'),
						               'notes_after'   => '<p>'.sprintf(__('This is not the address that emails are sent from. This address is simply displayed at the bottom of each email sent by %1$s, as a way for people to report any abuse of the system.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->textarea_row(
					                array(
						                'label'         => sprintf(__('Mailing Address (Required for %1$s)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/CAN-SPAM_Act_of_2003', __('CAN-SPAM Compliance', $this->plugin->text_domain))),
						                'placeholder'   => __('e.g., 123 Somewhere Street; Somewhere, USA 99999', $this->plugin->text_domain),
						                'cm_mode'       => 'text/html', 'cm_height' => 150,
						                'name'          => 'can_spam_mailing_address',
						                'current_value' => $current_value_for('can_spam_mailing_address'),
						                'notes_before'  => '<p class="pmp-note pmp-notice">'.sprintf(__('Please be sure to provide a mailing address that %1$s can include at the bottom of every email that it sends. This is required for %2$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/CAN-SPAM_Act_of_2003', __('CAN-SPAM Compliance', $this->plugin->text_domain))).'</p>',
						                'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Note:</strong> this needs to be provided in HTML format please. For line breaks please use: <code>&lt;br /&gt;</code>', $this->plugin->text_domain).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'url',
						                'label'         => __('Privacy Policy URL (Optional)', $this->plugin->text_domain),
						                'placeholder'   => __('e.g., http://example.com/privacy-policy/', $this->plugin->text_domain),
						                'name'          => 'can_spam_privacy_policy_url',
						                'current_value' => $current_value_for('can_spam_privacy_policy_url'),
						                'notes_after'   => '<p>'.sprintf(__('If you fill this in, %1$s will display a link to your privacy policy in strategic locations.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('CAN-SPAM Compliance', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Enable "<small><code>Powered by %1$s&trade;</code></small>" in Email Footer?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'email_footer_powered_by_enable',
						               'current_value'   => $current_value_for('email_footer_powered_by_enable'),
						               'allow_arbitrary' => FALSE,
						               'options'         => array(
							               '1' => sprintf(__('Yes, enable "powered by" note at the bottom of all emails sent by %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable "powered by" note', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => sprintf(__('Enable "<small><code>Powered by %1$s&trade;</code></small>" in Site Footer?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'site_footer_powered_by_enable',
						                'current_value'   => $current_value_for('site_footer_powered_by_enable'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '1' => sprintf(__('Yes, enable "powered by" note at the bottom of all pages generated by %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name)),
							                '0' => sprintf(__('No, disable "powered by" note', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                ),
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Powered by Notes', $this->plugin->text_domain), $_panel_body, array('note' => sprintf(__('Help support %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name))));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Advanced Configuration (All Optional)', $this->plugin->text_domain).
				     '            <small>'.__('Recommended for advanced site owners only; already pre-configured for most WP installs.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable Comment Form Subscr. Options Template?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'field_class'     => 'pmp-if-change', // JS change handler.
						               'name'            => 'comment_form_sub_template_enable',
						               'current_value'   => $current_value_for('comment_form_sub_template_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Yes, use built-in template system (recommended)', $this->plugin->text_domain),
							               '0' => __('No, disable built-in template system; I have a deep theme integration of my own', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<p>'.__('The built-in template system is quite flexible already; you can even customize the default template yourself if you want to (as seen below). Therefore, it is not recommended that you disable the default template system. This option only exists for very advanced users; i.e., those who prefer to disable the template completely in favor of their own custom implementation. If you disable the built-in template, you\'ll need to integrate HTML markup of your own into the proper location of your theme.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-if-disabled-show">'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Also Disable Scripts Associated w/ Comment Form Subscr. Options?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'comment_form_sub_scripts_enable',
						                'current_value'   => $current_value_for('comment_form_sub_scripts_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('No, leave scripts associated w/ comment form subscr. options enabled (recommended)', $this->plugin->text_domain),
							                '0' => __('Yes, disable built-in scripts also; I have a deep theme integration of my own', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('For advanced use only. If you disable the built-in template system, you may also want to disable the built-in JavaScript associated w/ this template.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				$_panel_body .= '<div class="pmp-if-enabled-show">'.
				                '  <table>'.
				                '     <tbody>'.
				                ($this->plugin->options['template_type'] === 'a'
					                ? $form_fields->textarea_row( // Advanced PHP-based template.
						                array(
							                'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
							                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							                'cm_mode'       => 'application/x-httpd-php', 'cm_height' => 250,
							                'name'          => 'template__type_'.$this->plugin->options['template_type'].'__site__comment_form__sub_ops___php',
							                'current_value' => $current_value_for('template__type_'.$this->plugin->options['template_type'].'__site__comment_form__sub_ops___php'),
							                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
							                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sub-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
							                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_field_comment/', 'comment_form_field_comment'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form/', 'comment_form')).'</p>'.
							                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						                ))
					                : $form_fields->textarea_row( // Simple snippet-based template.
						                array(
							                'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
							                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							                'cm_mode'       => 'text/html', 'cm_height' => 250,
							                'name'          => 'template__type_'.$this->plugin->options['template_type'].'__site__comment_form__snippet__sub_ops___php',
							                'current_value' => $current_value_for('template__type_'.$this->plugin->options['template_type'].'__site__comment_form__snippet__sub_ops___php'),
							                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
							                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sub-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
							                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_field_comment/', 'comment_form_field_comment'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form/', 'comment_form')).'</p>'.
							                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							                'cm_details'    => $shortcode_details(array(
								                                                      '[css_styles]'          => __('Stylesheet containing a default set of structral styles.', $this->plugin->text_domain),
								                                                      '[inline_icon_svg]'     => __('Inline SVG icon that inherits the color and width of it\'s container automatically. Note, this is a scalable vector graphic that will look great at any size &gt;= 16x16 pixels.', $this->plugin->text_domain),
								                                                      '[sub_type_options]'    => __('Select menu options. Allows a subscriber to choose if they wan\'t to subscribe or not; and in which way.', $this->plugin->text_domain),
								                                                      '[sub_deliver_options]' => __('Select menu options. Allows a subscriber to choose a delivery option; e.g., asap, hourly, daily, weeky. This can be excluded if you wish. A default value of <code>asap</code> will be used in that case.', $this->plugin->text_domain),
								                                                      '[sub_type_id]'         => __('The <code>id=""</code> attribute value used in <code>[sub_type_options]</code>.', $this->plugin->text_domain),
								                                                      '[current_sub_email]'   => __('The current subscriber\'s email address, if it is known to have been confirmed; i.e., if it really is their email address. This will be empty if they have not previously confirmed a subscription.', $this->plugin->text_domain),
								                                                      '[sub_new_url]'         => __('A URL leading to the "Add Subscription" page. This allows a visitor to subscribe w/o commenting even.', $this->plugin->text_domain),
								                                                      '[sub_summary_url]'     => __('A URL leading to the subscription summary page (i.e., the My Subscriptions page). A link to the summary page (i.e., the My Subscriptions page) should only be displayed <code>[if current_sub_email]</code> is known.', $this->plugin->text_domain),
							                                                      )),
						                ))).
				                '     </tbody>'.
				                '  </table>'.

				                ' <hr />'.

				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Default Subscription Option Selected for Commenters:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'comment_form_default_sub_type_option',
						                'current_value'   => $current_value_for('comment_form_default_sub_type_option'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                ''         => __('do not subscribe', $this->plugin->text_domain),
							                'comment'  => __('replies only (recommended)', $this->plugin->text_domain),
							                'comments' => __('all comments/replies', $this->plugin->text_domain),
						                ),
						                'notes_after'     => $this->plugin->options['template_type'] === 'a'
							                ? '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. You can change the wording that appears for these options by editing the template above. However, the default choice is determined systematically, based on the one that you choose here — assuming that you haven\'t dramatically altered code in the template. For most sites, the most logical default choice is: <code>replies only</code>; i.e., the commenter will only receive notifications for replies to the comment they are posting.', $this->plugin->text_domain).'</p>'
							                : '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. For most sites, the most logical default choice is: <code>replies only</code>; i.e., the commenter will only receive notifications for replies to the comment they are posting.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.

				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Default Subscription Delivery Option Selected for Commenters:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'comment_form_default_sub_deliver_option',
						                'current_value'   => $current_value_for('comment_form_default_sub_deliver_option'),
						                'allow_empty'     => FALSE, // Do not offer empty option value.
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => '%%deliver%%', // Predefined options.
						                'notes_after'     => $this->plugin->options['template_type'] === 'a'
							                ? '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. You can change the wording that appears for these options by editing the template above. However, the default choice is determined systematically, based on the one that you choose here — assuming that you haven\'t dramatically altered code in the template. For most sites, the most logical default choice is: <code>asap</code> (aka: instantly); i.e., the commenter will receive instant notifications regarding replies to their comment.', $this->plugin->text_domain).'</p>'
							                : '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. For most sites, the most logical default choice is: <code>asap</code> (aka: instantly); i.e., the commenter will receive instant notifications regarding replies to their comment.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				echo $this->panel(__('Comment Form', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable Auto-Subscribe?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'field_class'     => 'pmp-if-change', // JS change handler.
						               'name'            => 'auto_subscribe_enable',
						               'current_value'   => $current_value_for('auto_subscribe_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Yes, enable Auto-Subscribe (recommended)', $this->plugin->text_domain),
							               '0' => __('No, disable all Auto-Subscribe functionality', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-if-enabled-show">'.
						                                    '  <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When Auto-Subscribe is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '  <ul class="pmp-list-items">'.
						                                    '     <li>'.__('The author of a post can be subscribed to all comments/replies automatically. This way they\'ll receive email notifications w/o needing to go through the normal comment subscription process.', $this->plugin->text_domain).'</li>'.
						                                    '     <li>'.__('A list of other recipients can be added, allowing you to auto-subscribe other email addresses to every post automatically.', $this->plugin->text_domain).'</li>'.
						                                    '  </ul>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-if-enabled-show"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Subscribe Post Authors?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'auto_subscribe_post_author_enable',
						                'current_value'   => $current_value_for('auto_subscribe_post_author_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, auto-subscribe post authors (recommended)', $this->plugin->text_domain),
							                '0' => __('No, post authors will subscribe on their own', $this->plugin->text_domain),
						                ),
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Auto-Subscribe the Following Email Addresses:', $this->plugin->text_domain),
						                'placeholder'   => __('"John" <john@example.com>; jane@example.com; "Susan Smith" <susan@example.com>', $this->plugin->text_domain),
						                'name'          => 'auto_subscribe_recipients',
						                'current_value' => $current_value_for('auto_subscribe_recipients'),
						                'notes_after'   => '<p>'.__('You can enter a list of other email addresses that should be auto-subscribed to all posts. This is a semicolon-delimited list of recipients; e.g., <code>"John" &lt;john@example.com&gt;; jane@example.com; "Susan Smith" &lt;susan@example.com&gt;</code>.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Subscribe Delivery Option:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'auto_subscribe_deliver',
						                'current_value'   => $current_value_for('auto_subscribe_deliver'),
						                'allow_empty'     => FALSE, // Do not offer empty option value.
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => '%%deliver%%', // Predefined options.
						                'notes_after'     => '<p>'.__('Whenever someone is auto-subscribed, this is the delivery option that will be used. Any value that is not <code>asap</code> results in a digest instead of instant notifications.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <hr />'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Auto-Subscribe Post Types (Comma-Delimited):', $this->plugin->text_domain),
						                'placeholder'   => __('e.g., post,page,portfolio,gallery', $this->plugin->text_domain),
						                'name'          => 'auto_subscribe_post_types',
						                'current_value' => $current_value_for('auto_subscribe_post_types'),
						                'notes_after'   => '<p>'.sprintf(__('These are the %2$s that will trigger automatic subscriptions; i.e., %1$s will only auto-subscribe people to these types of posts. The default list is adequate for most sites. However, if you have other %2$s enabled by a theme/plugin, you might wish to include those here. e.g., <code>post,page,portfolio,gallery</code>; where <code>portfolio,gallery</code> might be two %3$s that you add to the default list, if applicable.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types', __('Post Types', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types#Custom_Post_Types', __('Custom Post Types', $this->plugin->text_domain))).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel(__('Auto-Subscribe Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Auto-Confirm Everyone?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'field_class'     => 'pmp-if-change', // JS change handler.
						               'name'            => 'auto_confirm_force_enable',
						               'current_value'   => $current_value_for('auto_confirm_force_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '0' => __('No, require subscriptions to be confirmed via email (highly recommended)', $this->plugin->text_domain),
							               '1' => __('Yes, automatically auto-confirm everyone; i.e., never ask for email confirmation', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-if-enabled-show" style="margin-top:1em !important;">'.
						                                    '   <p class="pmp-note pmp-warning" style="margin:0;">'.__('<strong>WARNING:</strong> Auto-Confirm Everyone is Enabled', $this->plugin->text_domain).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.sprintf(__('Nobody will be required to confirm a subscription. For instance, when someone leaves a comment and chooses to be subscribed (with whatever email address they\'ve entered), that email address will be added to the list w/o getting confirmation from the real owner of that address. This scenario changes slightly if you %1$s before leaving a comment, via WordPress Discussion Settings. If that\'s the case, then depending on the way your users register (i.e., if they are required to verify their email address in some way), this option might be feasible. That said, in 99%% of all cases this option is NOT recommended. If you enable auto-confirmation for everyone, please take extreme caution.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor(admin_url('/options-discussion.php'), __('require users to be logged-in', $this->plugin->text_domain))).'</li>'.
						                                    '      <li>'.sprintf(__('In addition to security issues associated w/ auto-confirming everyone automatically; if you enable this behavior it will also have the negative side-effect of making it slightly more difficult for users to view a summary of their existing subscriptions; i.e., they won\'t get an encrypted <code>%2$s</code> cookie right away via email confirmation, as would normally occur. This is how %1$s identifies a user when they are not currently logged into the site (typical w/ commenters). Therefore, if Auto-Confirm Everyone is enabled, the only way users can view a summary of their subscriptions, is if:', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).
						                                    '        <ul>'.
						                                    '           <li>'.__('They\'re a logged-in user, and you\'ve enabled "All WP Users Confirm Email" below; i.e., a logged-in user\'s email address can be trusted — known to be confirmed already.', $this->plugin->text_domain).'</li>'.
						                                    '           <li>'.sprintf(__('Or, if they click a link to manage their subscription after having received an email notification regarding a new comment. It is at this point that an auto-confirmed subscriber will finally get their encrypted <code>%1$s</code> cookie. That said, it\'s important to note that <em>anyone</em> can manage their subscriptions after receiving an email notification regarding a new comment. In every email notification there is a "Manage My Subscriptions" link provided for them. This link provides access to subscription management through a secret subscription key; not dependent upon a cookie.', $this->plugin->text_domain), esc_html(__NAMESPACE__.'_sub_email')).'</li>'.
						                                    '        </ul>'.
						                                    '     </li>'.
						                                    '   </ul>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-if-disabled-show"><hr />'.
				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Confirm if Already Subscribed w/ the Same IP Address?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'auto_confirm_if_already_subscribed_u0ip_enable',
						                'current_value'   => $current_value_for('auto_confirm_if_already_subscribed_u0ip_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '0' => __('No, do not trust a commenter\'s IP address; always request email confirmation (safest choice)', $this->plugin->text_domain),
							                '1' => __('Yes, if already subscribed to same post; with same email/IP; don\'t require another confirmation', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('IP addresses can be spoofed by an end-user, so it\'s generally recommended that you don\'t enable this. However, the sky won\'t fall if you do. Setting this to <code>Yes</code> will prevent repeat confirmation emails from being sent to commenters who choose to subscribe to <em>replies only</em> every time they comment on a single post. In this scenario; a single commenter, on a single post, may actually be associated with multiple comment subscriptions — one for each of their own comments. We say, "the sky won\'t fall", because even if an IP is spoofed, the underlying email address will have already been confirmed in one way or another. Enabling this option is not the safest route to take, but it might be an acceptable risk for your organization. It\'s really a judgement call on your part.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.
				                '</div>';

				$_panel_body .= '<div class="pmp-if-enabled-show"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('<i class="fa fa-wordpress"></i> <i class="fa fa-users"></i>'.
						                                        ' All WordPress Users Confirm their Email Address?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'all_wp_users_confirm_email',
						                'current_value'   => $current_value_for('all_wp_users_confirm_email'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '0' => __('No, some of my users register &amp; log in w/o confirming their email address (typical, safest answer)', $this->plugin->text_domain),
							                '1' => __('Yes, ALL of my users register &amp; confirm their email address before being allowed to log in', $this->plugin->text_domain),
						                ),
						                'notes_before'    => '<p><em>'.__('Please do a review of your theme and all plugins before answering yes to this question.', $this->plugin->text_domain).'</em></p>',
						                'notes_after'     => '<p>'.sprintf(__('If %1$s sees that a user is currently logged into the site as a real user (i.e., not <em>just</em> a commenter); it can detect the current user\'s email address w/o needing the encrypted <code>%2$s</code> cookie that is normally set via email confirmation. However, in order for this to occur, this option must be set to <code>Yes</code>; i.e., %1$s needs to know that it can trust the email address associated w/ each user account within WordPress before it will read an email address from <code>wp_users</code> table.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).'</p>'.
						                                     '<p class="pmp-note pmp-warning">'.sprintf(__('<strong>Warning:</strong> Please be cautious about how you answer this question. Do all of your users <em>really</em> register and confirm their email address before being allowed to log in? If a user updates their profile, is an email change-of-address always confirmed too? Some themes/plugins make it possible for registration/updates to occur <em>without</em> doing so. If that\'s the case, you should answer <code>No</code> here (default behavior), and just let the encrypted <code>%2$s</code> cookie do it\'s thing. That\'s what it\'s there for <i class="fa fa-smile-o"></i>', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).'</p>'.
						                                     '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> Your answer here does not enable or disable auto-confirmation in any way. It\'s simply a flag that is used by %1$s (internally), to help it make the most logical (safest) decision under certain scenarios that are impacted by the email address of the current user. It\'s important to realize that no matter what you answer here, %1$s will still be fully functional. You can only go wrong by saying <code>Yes</code> when in fact your users do NOT always confirm their email. <strong>If in doubt, please answer <code>No</code> (default behavior)</strong>.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                     '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> If you enable SSO "Single Sign-on" (another %1$s feature), then this setting is ignored; i.e., enabling SSO is an automatic flag which tells %1$s that all WP users do NOT confirm their email address in every scenario.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel(__('Auto-Confirm Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table style="margin:0;">'.
					               ' <tbody>'.
					               $form_fields->select_row(
						               array(
							               'label'           => __('Enable IP Region/Country Tracking?', $this->plugin->text_domain),
							               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							               'name'            => 'geo_location_tracking_enable',
							               'current_value'   => $current_value_for('geo_location_tracking_enable'),
							               'allow_arbitrary' => FALSE, // Must be one of these.
							               'options'         => array(
								               '0' => __('No, do not enable geographic location tracking for IP addresses', $this->plugin->text_domain),
								               '1' => __('Yes, automatically gather geographic region/country codes for each subscription (recommended)', $this->plugin->text_domain),
							               ),
							               'notes_after'     => '<p>'.sprintf(__('If you enable this feature, %1$s will post user IP addresses to the remote %2$s API behind-the-scenes, asking for geographic data associated with each subscription. %1$s will store this information locally in your WP database so that the data can be exported easily, and even used in statistical reporting. <span class="pmp-hilite">This option is highly recommended, but disabled by default</span> since it requires that you understand a remote connection takes place behind-the-scenes when %1$s speaks to the %2$s API.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://www.geoplugin.com/', 'geoPlugin')).'</p>',
						               )).
					               ' </tbody>'.
					               '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                ' <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Give Precedence to <code>$_SERVER[REMOTE_ADDR]</code>?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'prioritize_remote_addr',
							                'current_value'   => $current_value_for('prioritize_remote_addr'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '0' => __('No, search through proxies and other forwarded IP address headers first; in the most logical order (recommended)', $this->plugin->text_domain),
								                '1' => __('Yes, always use $_SERVER[REMOTE_ADDR]; my server deals with advanced IP logic already', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('Most hosting companies do NOT adequately fill <code>$_SERVER[REMOTE_ADDR]</code>. Instead, this is left up to your software (e.g., %1$s). So, unless you know for sure that your hosting company <em>is</em> properly analyzing forwarded IP address headers before filling the <code>$_SERVER[REMOTE_ADDR]</code> environment variable, it is suggested that you simply leave this set to <code>No</code>. This way %1$s will always get a visitor\'s real IP address, even if they\'re behind a proxy; or if your server uses a load balancer that alters <code>$_SERVER[REMOTE_ADDR]</code> inadvertently. You\'ll be happy to know that %1$s supports both IPv4 and IPv6 addresses.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                ' </tbody>'.
					                '</table>';

					echo $this->panel(__('Geo IP Region/Country Tracking', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->input_row(
						               array(
							               'type'          => 'number',
							               'label'         => __('Maximum Chars in Parent Comment Clips:', $this->plugin->text_domain),
							               'placeholder'   => __('e.g., 100', $this->plugin->text_domain),
							               'name'          => 'comment_notification_parent_content_clip_max_chars',
							               'other_attrs'   => 'min="1"',
							               'current_value' => $current_value_for('comment_notification_parent_content_clip_max_chars'),
							               'notes_after'   => '<p>'.sprintf(__('When %1$s notifies someone about a reply to their comment, there will first be a short clip of the original comment displayed to help offer some context; i.e., to show what the reply is pertaining to. How many characters (maximum) do you want to display in that short clip of the parent comment? The recommended setting is <code>100</code> characters, but you can change this to whatever you like. A very large number will prevent the parent comment from being clipped at all.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
						               )).
					               '  </tbody>'.
					               '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Maximum Chars in Other Comment/Reply Clips:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 200', $this->plugin->text_domain),
							                'name'          => 'comment_notification_content_clip_max_chars',
							                'other_attrs'   => 'min="1"',
							                'current_value' => $current_value_for('comment_notification_content_clip_max_chars'),
							                'notes_after'   => '<p>'.sprintf(__('For all other comment/reply notifications, there will be a short clip of the comment, along with a link to [continue reading] on your website. How many characters (maximum) do you want to display in those short clips of the comment or reply? The recommended setting is <code>200</code> characters, but you can change this to whatever you like. A very large number will prevent comments from being clipped at all.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					echo $this->panel(__('Email Notification Clips', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table style="margin:0;">'.
					               ' <tbody>'.
					               $form_fields->select_row(
						               array(
							               'label'           => __('Enable SMTP Integration?', $this->plugin->text_domain),
							               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							               'field_class'     => 'pmp-if-change', // JS change handler.
							               'name'            => 'smtp_enable',
							               'current_value'   => $current_value_for('smtp_enable'),
							               'allow_arbitrary' => FALSE, // Must be one of these.
							               'options'         => array(
								               '0' => __('No, use the wp_mail function (default behavior)', $this->plugin->text_domain),
								               '1' => __('Yes, integrate w/ an SMTP server of my choosing (as configured below)', $this->plugin->text_domain),
							               ),
							               'notes_after'     => '<div class="pmp-if-enabled-show">'.
							                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When SMTP Server Integration is enabled:', $this->plugin->text_domain).'</p>'.
							                                    '   <ul class="pmp-list-items">'.
							                                    '      <li>'.sprintf(__('Instead of using the default <code>%2$s</code> function, %1$s will send email confirmation requests &amp; comment/reply notifications through an SMTP server of your choosing; i.e., all email processed by %1$s will be routed through an SMTP server that you\'ve dedicated to comment subscriptions. This is highly recommended, since it can significantly improve the deliverability rate of emails that are sent by %1$s. In addition, it may also speed up your site (i.e., reduce the burden on your own web server). This is because an SMTP host is generally associated with an external server that is dedicated to email processing.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail')).'</li>'.
							                                    '      <li>'.sprintf(__('Instead of using the <code>%3$s</code>, <code>%4$s</code>, and <code>%2$s</code> email message headers configured elsewhere in %1$s, the values that you configure for the SMTP server will be used instead; i.e., what you configure here will override other email header options in %1$s. This allows you to be specific about what message headers are passed through your SMTP server whenever SMTP functionality is enabled. <strong>With one exception.</strong> If you happen to enable the %1$s&trade; RVE handler (Replies via Email), the SMTP <code>Reply-To</code> header is ignored in favor of the <code>Reply-To</code> address configured for the %1$s&trade; RVE handler. If RVE is enabled, the <code>Reply-To</code> address for the RVE handler receives precedence always.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</li>'.
							                                    '   </ul>'.
							                                    '  <p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> If you are already running a plugin like %2$s (i.e., a plugin that reconfigures the <code>%3$s</code> function globally); that is usually enough, and you should generally NOT enable SMTP integration here also. In other words, if <code>%3$s</code> is already configured globally to route mail through an SMTP server, you would only need the options below if your intention was to override your existing SMTP configuration specifically for %1$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://wordpress.org/plugins/wp-mail-smtp/', 'WP Mail SMTP'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail')).'</p>'.
							                                    '</div>',
						               )).
					               ' </tbody>'.
					               '</table>';

					$_panel_body .= '<div class="pmp-if-enabled-show"><hr />'.

					                '<a href="http://aws.amazon.com/ses/" target="_blank">'.
					                '  <img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/aws-ses-rec.png')).'" class="pmp-right" style="margin:1em 0 0 3em;" />'.
					                '</a>'.

					                ' <table style="width:auto;">'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => __('SMTP Host Name:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., email-smtp.us-east-1.amazonaws.com', $this->plugin->text_domain),
							                'name'          => 'smtp_host',
							                'current_value' => $current_value_for('smtp_host'),
							                'notes_after'   => '<p>'.__('e.g., <code>email-smtp.us-east-1.amazonaws.com</code>, <code>smtp.gmail.com</code>, or another of your choosing.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                ' <table style="width:auto;">'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('SMTP Port Number:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 465', $this->plugin->text_domain),
							                'name'          => 'smtp_port',
							                'current_value' => $current_value_for('smtp_port'),
							                'notes_after'   => '<p>'.__('With Amazon&reg; SES (or GMail&trade;) please use: <code>465</code>', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                ' <table style="width:auto;">'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('SMTP Authentication Type:', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'smtp_secure',
							                'current_value'   => $current_value_for('smtp_secure'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                ''    => __('Plain Text Authentication', $this->plugin->text_domain),
								                'ssl' => __('SSL Authentication (most common)', $this->plugin->text_domain),
								                'tls' => __('TLS Authentication', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.__('With Amazon&reg; SES (or GMail&trade;) over port 465, please choose: <code>SSL</code>', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                '<hr />'.

					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => __('SMTP Username:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., AKIAJSA57DDLS5I6GCA; e.g., me@example.com', $this->plugin->text_domain),
							                'name'          => 'smtp_username',
							                'current_value' => $current_value_for('smtp_username'),
							                'notes_after'   => '<p>'.__('With Amazon&reg; SES use your Access Key ID. With GMail&trade; use your login name, or full email address.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'password',
							                'label'         => __('SMTP Password:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., AWS secret key, or email account password', $this->plugin->text_domain),
							                'name'          => 'smtp_password',
							                'current_value' => $current_value_for('smtp_password'),
							                'notes_after'   => '<p>'.__('With Amazon&reg; SES use your Secret Key. With GMail&trade; use your password.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                '<hr />'.

					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => __('SMTP <code>From</code> and <code>Return-Path</code> Name:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., MySite.com', $this->plugin->text_domain),
							                'name'          => 'smtp_from_name',
							                'current_value' => $current_value_for('smtp_from_name'),
							                'notes_after'   => '<p>'.sprintf(__('The name used in the <code>%3$s:</code> and <code>%4$s:</code> headers; e.g., <code>MySite.com</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'email',
							                'label'         => __('SMTP <code>From</code> and <code>Return-Path</code> Email Address:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., moderator@mysite.com', $this->plugin->text_domain),
							                'name'          => 'smtp_from_email',
							                'current_value' => $current_value_for('smtp_from_email'),
							                'notes_after'   => '<p>'.sprintf(__('Email used in the <code>%3$s:</code> and <code>%4$s:</code> headers; e.g., <code>moderator@mysite.com</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'.
							                                   '<p class="pmp-note pmp-info">'.__('<strong>Note:</strong> most SMTP servers will require this email address to match up with specific users and/or specific domains; else mail is rejected automatically. Please be sure to check the documentation for your SMTP host before entering this address. For instance, with Amazon&reg; SES you will need to setup at least one Verified Sender and then enter that address here. With GMail&trade;, you will need to enter the email address that is associated with the Username/Password you entered above.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                '<hr />'.

					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'email',
							                'label'         => __('SMTP <code>Reply-To</code> Email Address:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., moderator@mysite.com', $this->plugin->text_domain),
							                'name'          => 'smtp_reply_to_email',
							                'current_value' => $current_value_for('smtp_reply_to_email'),
							                'notes_after'   => '<p>'.sprintf(__('Email used in the <code>%2$s:</code> header; e.g., <code>moderator@mysite.com</code>. This makes it so that if someone happens to reply to an email notification, that reply will be directed to a specific email address that you prefer. Some site owners like to use something like <code>noreply@mysite.com</code>, while others find it best to use a real email address that can monitor replies. It\'s a matter of preference.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'.
							                                   '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> If you happen to enable a %1$s&trade; RVE Handler (Replies via Email), this value is ignored in favor of the <code>Reply-To</code> address configured for your RVE Handler. In other words, if you enable Replies via Email, you could simply leave this blank if you like. If RVE is enabled, the <code>Reply-To</code> address for the RVE Handler receives precedence always. The address you configure here will not be applied in that case.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
						                )).
					                '  </tbody>'.
					                ' </table>'.

					                /* This is currently forced to a value of `1`.
					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Force <code>From:</code> &amp; <code>Return-Path:</code> Headers?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'smtp_force_from',
							                'current_value'   => $current_value_for('smtp_force_from'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '1' => __('Yes, always use the "Name" <address> I\'ve given (recommended)', $this->plugin->text_domain),
								                '0' => __('No, use "Name" <address> I\'ve given by default, but allow individual emails to override', $this->plugin->text_domain),
							                ),
						                )).
					                '  </tbody>'.
					                ' </table>'. */

					                '<hr />'.

					                ' <table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'        => 'email',
							                'label'       => __('Test SMTP Server Settings?', $this->plugin->text_domain),
							                'placeholder' => __('e.g., me@mysite.com', $this->plugin->text_domain),
							                'name'        => 'mail_smtp_test', // Not an actual option key; but the `save_options` handler picks this up.
							                'notes_after' => sprintf(__('Enter an email address to have %1$s&trade; send a test message when you save these options, and report back about any success or failure.', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                )).
					                '  </tbody>'.
					                ' </table>'.
					                '</div>';

					echo $this->panel(__('SMTP Server Integration', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table style="margin:0;">'.
					               ' <tbody>'.
					               $form_fields->select_row(
						               array(
							               'label'           => __('Enable RVE (Replies via Email)?', $this->plugin->text_domain),
							               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							               'field_class'     => 'pmp-if-change', // JS change handler.
							               'name'            => 'replies_via_email_enable',
							               'current_value'   => $current_value_for('replies_via_email_enable'),
							               'allow_arbitrary' => FALSE, // Must be one of these.
							               'options'         => array(
								               '0' => __('No, do not allow comment replies via email', $this->plugin->text_domain),
								               '1' => __('Yes, allow subscribers to post comment replies via email (recommended)', $this->plugin->text_domain),
							               ),
							               'notes_after'     => '<div class="pmp-if-enabled-show">'.
							                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When Replies via Email are enabled through an RVE Handler:', $this->plugin->text_domain).'</p>'.
							                                    '   <ul class="pmp-list-items">'.
							                                    '      <li>'.sprintf(__('%1$s&trade; will allow replies to comments via email using a special <code>Reply-To</code> address that you will need to set up by following the instructions provided below. Any other <code>Reply-To</code> address configured elsewhere in %1$s will be overridden by the address you configure here for an RVE Handler. There are no special exceptions to this. An RVE Handler takes precedence over any other <code>Reply-To</code> you configure.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</li>'.
							                                    '      <li>'.sprintf(__('Replies to comments via email will be functional for all types of notifications sent by %1$s (including digest notifications). However, there are a few things worth noting before you enable an RVE Handler. <a href="#" data-toggle="other" data-other=".pmp-rve-details">Click here to toggle important details</a>.', $this->plugin->text_domain), esc_html($this->plugin->name)).
							                                    '        <ul class="pmp-rve-details" style="display:none;">'.
							                                    '           <li>'.sprintf(__('All replies posted via email must be sent to the special <code>Reply-To</code> address that you configure below. Once you configure a <code>Reply-To</code> for an RVE Handler, %1$s will automatically set the <code>Reply-To:</code> header in all email notifications that it sends. This way when somebody replies to a comment notification, their email program will reply to the address required for replies via email to work properly.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</li>'.
							                                    '           <li>'.sprintf(__('The <code>Reply-To</code> address that you configure below, will serve as a base for %1$s to work from. For instance, let\'s say you choose: <code>rve@mandrill.%2$s</code>. This base address will be suffixed automatically (at runtime) with details specific to a particular notification that %1$s sends. Ultimately, <code>rve@mandrill.%2$s</code> will look like: <code>rve<strong>+332-96-kgjdgxr4ldqpdrgjdgxr</strong>@mandrill.%2$s</code>. In this example, the additional details (following the <code>+</code> sign) are there to help %1$s route the reply to the proper location, and to provide a means by which to identify the end-user that is posting a reply.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html($this->plugin->utils_url->current_host_base())).'</li>'.
							                                    '           <li>'.sprintf(__('For single-comment notifications; i.e., where a subscriber chooses delivery type <code>asap</code> (aka: instantly), there is just a single comment in each notification that a subscriber receives. This works best with replies via email, since the <code>Reply-To:</code> header (on its own) is enough for everything to work as expected. Someone replying via email need only hit the Reply button in their email program and start typing. Very simple.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</li>'.
							                                    '           <li>'.sprintf(__('For multi-comment notifications; i.e., where a subscriber chooses a delivery type that is not <code>asap</code> (e.g., <code>hourly</code>, <code>daily</code>, etc.); there can be more than a single comment in each notification they receive. If there is more than one comment in the notification, instructions will be provided to the end-user explaining how to reply. The special <code>Reply-To</code> address is still used in this case. However, they also need to specify which comment they want to reply to. To do this, the end-user must start their reply with a special marker provided by %1$s. Again, if there is more than one comment in the notification, instructions will be provided to the end-user explaining how to reply.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</li>'.
							                                    '           <li>'.sprintf(__('Comments posted via email are still piped through the same underlying WordPress handler that normal on-site comments go through (i.e., <code>/wp-comments-post.php</code>). This means that all of your existing WordPress Discussion Settings (and/or Akismet settings) will still apply to all comments, even if they are posted via email. <strong>With one exception.</strong> When an RVE Handler is enabled, any comments posted via email are allowed through without an end-user being logged-in. If your WordPress Discussion Settings require that users be logged-in to post comments, that will be overridden temporarily whenever a reply via email comes through. Please note that replies posted via email are generally from confirmed subscribers. Any reply via email that is not from a confirmed subscriber will be forced into moderation by %1$s anyway. Otherwise, whatever your current Discussion Settings are configured to allow, will be adhered to for replies via email also. For instance, if you require that all comments be moderated, that will continue to be the case for all replies via email. %1$s will never approve a comment on it\'s own. Approval of comments is always determined by your WP Discussion Settings.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</li>'.
							                                    '           <li>'.sprintf(__('Any reply via email should include one of two things. A copy of the original quoted notification, or a special <code>%2$s</code> marker. Most email clients will include the original message in an email reply, and this is what %1$s will look for. %1$s scans the body of the email looking for an original quoted section and strips it out (along with anything below it). If a reply does not include a quoted section when replying to an email notification, an <code>%2$s</code> marker can be used instead. When %1$s reads <code>%2$s</code>, it will use it as a marker and ignore everything below that line. Everything above <code>%2$s</code> will become the comment reply on your blog. Therefore, you can use the <code>%2$s</code> feature even if you have quoting turned off in your email client. If neither of these are found, the reply is still accepted. However, it will be forced into moderation at all times; i.e., you must approve it manually no matter what the rest of your WordPress Discussion Settings say.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html($this->plugin->is_pro ? $this->plugin->utils_rve->manual_end_divider() : '!END')).'</li>'.
							                                    '        </ul>'.
							                                    '     </li>'.
							                                    '   </ul>'.
							                                    '</div>',
						               )).
					               ' </tbody>'.
					               '</table>';

					$_panel_body .= '<div class="pmp-if-enabled-show pmp-if-nest"><hr />'.

					                '<a href="http://comment-mail.com/kb-article/mandrill-rve-handler/" target="_blank">'.
					                '<img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/mandrill-rec.png')).'" class="pmp-right" style="margin-left:3em;" /></a>'.

					                ' <table style="width:auto; margin-bottom:0;">'.
					                '    <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Choose an RVE Handler:', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'field_class'     => 'pmp-if-change pmp-if-value-match',
							                'name'            => 'replies_via_email_handler',
							                'current_value'   => $current_value_for('replies_via_email_handler'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                ''         => '', // Empty value for the sake of making this somewhat understandable.
								                'mandrill' => __('Mandrill RVE Handler (free; recommended)', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('<strong>Note:</strong> %1$s is currently the only choice here; i.e., we have only integrated this with Mandrill thus far <i class="fa fa-smile-o"></i>', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://help.mandrill.com/entries/21699367-Inbound-Email-Processing-Overview', 'Mandrill')).'</p>'
						                )).
					                '    </tbody>'.
					                ' </table>'.

					                ' <div class="pmp-if-enabled-show pmp-if-value-mandrill pmp-in-if-nest"><hr />'.
					                '    <table>'.
					                '       <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'email',
							                'label'         => __('Mandrill <code>Reply-To</code> Address:', $this->plugin->text_domain),
							                'placeholder'   => sprintf(__('e.g., rve@mandrill.%1$s', $this->plugin->text_domain), $this->plugin->utils_url->current_host_base()),
							                'name'          => 'rve_mandrill_reply_to_email',
							                'current_value' => $current_value_for('rve_mandrill_reply_to_email'),
							                'notes_after'   => '<p class="pmp-note pmp-info">'.sprintf(__('This is really all it takes to get Replies via Email working. However, it requires that you setup a Mandrill account (free) and then configure an Inbound Mailbox Route that will connect to the Webhook URL shown below. <span class="pmp-hilite">Please see %1$s for detailed instructions.</span>', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/mandrill-rve-handler/', __('this wiki article', $this->plugin->text_domain))).'</p>'.
							                                   $this->select_all_field(__('<strong>Mandrill Webhook URL:</strong>', $this->plugin->text_domain), $this->plugin->is_pro ? $this->plugin->utils_url->rve_mandrill_webhook_url() : ''),
						                )).
					                '       </tbody>'.
					                '    </table>'.

					                '    <hr />'.

					                '    <table>'.
					                '       <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Mandrill Max Overall Spam Score Allowed:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 5.0', $this->plugin->text_domain),
							                'name'          => 'rve_mandrill_max_spam_score',
							                'current_value' => $current_value_for('rve_mandrill_max_spam_score'),
							                'notes_after'   => '<p>'.sprintf(__('This is based on %1$s, powered by SpamAssassin. A value of <code>3.0</code> to <code>5.0</code> is suggested here. Any reply via email with a spam score higher than what is configured here, will be forced into moderation and marked as spam. <strong>Note:</strong> this is in addition to any other spam checking plugins that you run; e.g., if you use Akismet, each comment must also pass through Akismet too.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://help.mandrill.com/entries/22092308-What-is-the-format-of-inbound-email-webhooks-', __('checks performed by Mandrill', $this->plugin->text_domain))).'</p>',
						                )).
					                '       </tbody>'.
					                '    </table>'.

					                '    <table>'.
					                '       <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Mandrill SPF Rejection Policy:', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'rve_mandrill_spf_check_enable',
							                'current_value'   => $current_value_for('rve_mandrill_spf_check_enable'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '0' => __('Do not check SPF test results at all; i.e., no SPF rejection policy', $this->plugin->text_domain),
								                '1' => __('Require SPF test result: "pass|neutral|softfail|none"; else flag as spam for moderation (recommended)', $this->plugin->text_domain),
								                '2' => __('Require SPF test result: "pass|neutral|none"; else flag as spam for moderation', $this->plugin->text_domain),
								                '3' => __('Require SPF test result: "pass|neutral"; else flag as spam for moderation', $this->plugin->text_domain),
								                '4' => __('Require SPF test result: "pass"; else flag as spam for moderation', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('This is based on %1$s, powered by SpamAssassin. A value of <code>pass|neutral|softfail|none</code> is suggested here; where <code>|</code> means "or" (i.e., one of these results). Any reply via email that does not pass your rejection policy will be forced into moderation and marked as spam. <strong>Note:</strong> this is in addition to any other spam checking plugins that you run; e.g., if you use Akismet, each comment must also pass through Akismet too.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://help.mandrill.com/entries/22092308-What-is-the-format-of-inbound-email-webhooks-', __('checks performed by Mandrill', $this->plugin->text_domain))).'</p>',
						                )).
					                '       </tbody>'.
					                '    </table>'.

					                '    <table>'.
					                '       <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Mandrill DKIM Rejection Policy:', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'rve_mandrill_dkim_check_enable',
							                'current_value'   => $current_value_for('rve_mandrill_dkim_check_enable'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '0' => __('Do not check DKIM test results at all; i.e., no DKIM rejection policy', $this->plugin->text_domain),
								                '1' => __('If DKIM signature "exists, but it\'s invalid"; flag as spam for moderation (recommended)', $this->plugin->text_domain),
								                '2' => __('If DKIM signature "is missing or invalid"; flag as spam for moderation', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('This is based on %1$s, powered by SpamAssassin. A value of <code>signature exists, but invalid</code> is suggested here. Any reply via email that does not pass your rejection policy will be forced into moderation and marked as spam. <strong>Note:</strong> this is in addition to any other spam checking plugins that you run; e.g., if you use Akismet, each comment must also pass through Akismet too.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://help.mandrill.com/entries/22092308-What-is-the-format-of-inbound-email-webhooks-', __('checks performed by Mandrill', $this->plugin->text_domain))).'</p>',
						                )).
					                '       </tbody>'.
					                '    </table>'.
					                ' </div>'.
					                '</div>';

					echo $this->panel(__('Replies via Email (RVE Handler)', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table style="margin-bottom:0;">'.
					               '  <tbody>'.
					               $form_fields->select_row(
						               array(
							               'label'           => __('Enable Single Sign-on (SSO)?', $this->plugin->text_domain),
							               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							               'field_class'     => 'pmp-if-change', // JS change handler.
							               'name'            => 'sso_enable',
							               'current_value'   => $current_value_for('sso_enable'),
							               'allow_arbitrary' => FALSE, // Must be one of these.
							               'options'         => array(
								               '0' => __('No, disable Single Sign-on (SSO)', $this->plugin->text_domain),
								               '1' => __('Yes, enable Single Sign-on (recommended)', $this->plugin->text_domain),
							               ),
							               'notes_after'     => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sso-services.png')).'" class="pmp-right" />'.
							                                    sprintf(__('As a convenience, SSO allows commenters to login with a popular social network account; e.g., Twitter, Facebook, Google, LinkedIn. <span class="pmp-hilite">This feature is highly recommended, but disabled by default</span>; since it requires some work on your part to set things up properly. Detailed instructions are provided %1$s. When a visitor logs in through an SSO service provider, an account is automatically created for them in WordPress (if one does not exist already). These auto-generated WordPress accounts are created using details obtained from an SSO service provider. Such as first name, last name, email address. SSO users receive a default Role; i.e., whatever the default Role is for your site. Normally the %2$s, but you can change this from your %3$s on standard WP installs. WP Multisite Network installs always use the %2$s. An account created in this way (via SSO) could be logged into like any other WP account (technically), but it will also be connected to the underlying SSO service too. Meaning, a user may simply log into your site in the future w/ the SSO. They won\'t ever need a username/password that is specific to your site.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/sso-integration/', __('here', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities#Subscriber', __('Subscriber Role', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor(admin_url('/options-general.php'), __('WP General Settings', $this->plugin->text_domain))).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					$_panel_body .= '<div class="pmp-if-enabled-show pmp-if-nest">'.

					                ' <p class="pmp-note pmp-info" style="font-size:90%; margin-top:1em !important;">'.sprintf(__('<strong>Note:</strong> In order for SSO to work (i.e., for SSO links to appear in your comment/login forms); your %1$s should "require users to be logged-in" before posting a comment. You must also "allow users to register" in your %2$s. WP Multisite Network installs can enable user registration from their Network Settings area. Now, with those settings configured properly, you will then need to create &amp; fill-in an oAuth App Key/Secret for each service that you\'d like to enable for SSO. Any of the services that you leave empty will simply not be offered as an SSO option to commenters. Please take a look at %3$s.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor(admin_url('/options-discussion.php'), __('WP Discussion Settings', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor(admin_url('/options-general.php'), __('WP General Settings', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/sso-integration/', __('this wiki article for detailed instructions', $this->plugin->text_domain))).'</p>'.

					                ' <div class="pmp-tabs">'.
					                '    <a href="#" data-target=".pmp-tab-pane-twitter" class="pmp-active">'.__('Twitter', $this->plugin->text_domain).'</a>'.
					                '    <a href="#" data-target=".pmp-tab-pane-facebook">'.__('Facebook', $this->plugin->text_domain).'</a>'.
					                '    <a href="#" data-target=".pmp-tab-pane-google">'.__('Google', $this->plugin->text_domain).'</a>'.
					                '    <a href="#" data-target=".pmp-tab-pane-linkedin">'.__('LinkedIn', $this->plugin->text_domain).'</a>'.
					                ' </div>'.

					                ' <div class="pmp-tab-panes">'.

					                '    <div class="pmp-tab-pane-twitter pmp-active">'.
					                '       <table style="margin-bottom:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Twitter oAuth Consumer Key: &nbsp;&nbsp; <small><em>[%1$s]</em></small>', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/sso-integration/#-twitter', __('instructions', $this->plugin->text_domain))),
							                'placeholder'   => __('e.g., kyczbsh6nnwtzrkm882kh7jf8', $this->plugin->text_domain),
							                'name'          => 'sso_twitter_key',
							                'current_value' => $current_value_for('sso_twitter_key'),
							                'notes_after'   => $this->select_all_field(__('<strong>oAuth 1.0a Redirect/Callback URL:</strong>', $this->plugin->text_domain), $this->plugin->is_pro ? $this->plugin->utils_url->sso_action_url('twitter', 'callback') : ''),
						                )).
					                '          </tbody>'.
					                '       </table>'.

					                '       <table style="margin-top:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'password',
							                'label'         => __('Twitter oAuth Consumer Secret:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., gznuef64twbku3qpcdyx8jtfgcyccxsup8yu5gb95f493maf79', $this->plugin->text_domain),
							                'name'          => 'sso_twitter_secret',
							                'current_value' => $current_value_for('sso_twitter_secret'),
						                )).
					                '          </tbody>'.
					                '       </table>'.
					                '    </div>'.

					                '    <div class="pmp-tab-pane-facebook">'.
					                '       <table style="margin-bottom:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Facebook oAuth App ID: &nbsp;&nbsp; <small><em>[%1$s]</em></small>', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/sso-integration/#-facebook', __('instructions', $this->plugin->text_domain))),
							                'placeholder'   => __('e.g., 87df9vcu8njzrrnrgy2u2k2cj', $this->plugin->text_domain),
							                'name'          => 'sso_facebook_key',
							                'current_value' => $current_value_for('sso_facebook_key'),
							                'notes_after'   => $this->select_all_field(__('<strong>oAuth 2.0 Redirect/Callback URL:</strong>', $this->plugin->text_domain), $this->plugin->is_pro ? $this->plugin->utils_url->sso_action_url('facebook', 'callback') : ''),
						                )).
					                '          </tbody>'.
					                '       </table>'.

					                '       <table style="margin-top:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'password',
							                'label'         => __('Facebook oAuth App Secret:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., pqs4vyjmw6rqt23knuajftuv7xxxgxtdwvuajnq7cj5a5ak22j', $this->plugin->text_domain),
							                'name'          => 'sso_facebook_secret',
							                'current_value' => $current_value_for('sso_facebook_secret'),
						                )).
					                '          </tbody>'.
					                '       </table>'.
					                '    </div>'.

					                '    <div class="pmp-tab-pane-google">'.
					                '       <table style="margin-bottom:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Google oAuth Client ID: &nbsp;&nbsp; <small><em>[%1$s]</em></small>', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/sso-integration/#-google', __('instructions', $this->plugin->text_domain))),
							                'placeholder'   => __('e.g., qda788ac23s4m4utvqgkauwhf.apps.googleusercontent.com', $this->plugin->text_domain),
							                'name'          => 'sso_google_key',
							                'current_value' => $current_value_for('sso_google_key'),
							                'notes_after'   => $this->select_all_field(__('<strong>oAuth 2.0 Redirect/Callback URL:</strong>', $this->plugin->text_domain), $this->plugin->is_pro ? $this->plugin->utils_url->sso_action_url('google', 'callback') : ''),
						                )).
					                '          </tbody>'.
					                '       </table>'.

					                '       <table style="margin-top:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'password',
							                'label'         => __('Google oAuth Client Secret:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., djx4zsdyh4grkuw8qpkg382fr8uujmsahfj8x4b8aun437hye2', $this->plugin->text_domain),
							                'name'          => 'sso_google_secret',
							                'current_value' => $current_value_for('sso_google_secret'),
						                )).
					                '          </tbody>'.
					                '       </table>'.
					                '    </div>'.

					                '    <div class="pmp-tab-pane-linkedin">'.
					                '       <table style="margin-bottom:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('LinkedIn oAuth API/Consumer Key: &nbsp;&nbsp; <small><em>[%1$s]</em></small>', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/sso-integration/#-linkedin', __('instructions', $this->plugin->text_domain))),
							                'placeholder'   => __('e.g., swf73zuj2puaug9e5a4ytpcg7', $this->plugin->text_domain),
							                'name'          => 'sso_linkedin_key',
							                'current_value' => $current_value_for('sso_linkedin_key'),
							                'notes_after'   => $this->select_all_field(__('<strong>oAuth 2.0 Redirect/Callback URL:</strong>', $this->plugin->text_domain), $this->plugin->is_pro ? $this->plugin->utils_url->sso_action_url('linkedin', 'callback') : ''),
						                )).
					                '          </tbody>'.
					                '       </table>'.

					                '       <table style="margin-top:0;">'.
					                '          <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'password',
							                'label'         => __('LinkedIn oAuth API/Consumer Secret:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., dtqvgh8qjkne4nhry7w56bzk86dcqr7racy5evmhegpt9gw9c4', $this->plugin->text_domain),
							                'name'          => 'sso_linkedin_secret',
							                'current_value' => $current_value_for('sso_linkedin_secret'),
						                )).
					                '          </tbody>'.
					                '       </table>'.
					                '    </div>'.

					                ' </div>'. // End: tab panes.

					                ' <hr />'. // Begin other advanced (optional) settings.

					                ' <div style="margin-top:1em;">'.
					                '     <i class="fa fa-caret-down"></i>'.
					                '     <a href="#" data-toggle="other" data-other=".pmp-other-sso-settings" class="pmp-dotted-link">'.
					                '    '.__('click to toggle other advanced (optional) SSO settings', $this->plugin->text_domain).'</a>'.
					                ' </div>'.

					                ' <div class="pmp-other-sso-settings" style="display:none;"><hr />'.

					                '    <div class="pmp-if-nest">'.

					                '       <table style="margin-bottom:0;">'.
					                '          <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Enable Comment Form SSO Options Template?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'field_class'     => 'pmp-if-change', // JS change handler.
							                'name'            => 'comment_form_sso_template_enable',
							                'current_value'   => $current_value_for('comment_form_sso_template_enable'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '1' => __('Yes, use built-in template system (recommended)', $this->plugin->text_domain),
								                '0' => __('No, disable built-in template system; I have a deep theme integration of my own', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.__('The built-in template system is quite flexible already; you can even customize the default template yourself if you want to (as seen below). Therefore, it is not recommended that you disable the default template system. This option only exists for very advanced users; i.e., those who prefer to disable the template completely in favor of their own custom implementation. If you disable the built-in template, you\'ll need to integrate HTML markup of your own into the proper location of your theme.', $this->plugin->text_domain).'</p>',
						                )).
					                '          </tbody>'.
					                '       </table>'.

					                '       <div class="pmp-if-disabled-show pmp-if-in-nest">'.
					                '          <table style="margin-bottom:0;">'.
					                '             <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Also Disable Scripts Associated w/ Comment Form SSO Options?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'comment_form_sso_scripts_enable',
							                'current_value'   => $current_value_for('comment_form_sso_scripts_enable'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '1' => __('No, leave scripts associated w/ comment form SSO options enabled (recommended)', $this->plugin->text_domain),
								                '0' => __('Yes, disable built-in scripts also; I have a deep theme integration of my own', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.__('For advanced use only. If you disable the built-in template system, you may also want to disable the built-in JavaScript associated w/ this template.', $this->plugin->text_domain).'</p>',
						                )).
					                '             </tbody>'.
					                '          </table>'.
					                '       </div>'.

					                '       <div class="pmp-if-enabled-show pmp-if-in-nest">'.
					                '          <table>'.
					                '             <tbody>'.
					                ($this->plugin->options['template_type'] === 'a'
						                ? $form_fields->textarea_row( // Advanced PHP-based template.
							                array(
								                'label'         => __('Comment Form SSO Options Template', $this->plugin->text_domain),
								                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
								                'cm_mode'       => 'application/x-httpd-php', 'cm_height' => 250,
								                'name'          => 'template__type_'.$this->plugin->options['template_type'].'__site__comment_form__sso_ops___php',
								                'current_value' => $current_value_for('template__type_'.$this->plugin->options['template_type'].'__site__comment_form__sso_ops___php'),
								                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
								                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sso-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
								                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_must_log_in_after/', 'comment_form_must_log_in_after'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_top/', 'comment_form_top')).'</p>'.
								                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							                ))
						                : $form_fields->textarea_row( // Simple snippet-based template.
							                array(
								                'label'         => __('Comment Form SSO Options Template', $this->plugin->text_domain),
								                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
								                'cm_mode'       => 'text/html', 'cm_height' => 250,
								                'name'          => 'template__type_'.$this->plugin->options['template_type'].'__site__comment_form__snippet__sso_ops___php',
								                'current_value' => $current_value_for('template__type_'.$this->plugin->options['template_type'].'__site__comment_form__snippet__sso_ops___php'),
								                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
								                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sso-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
								                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_must_log_in_after/', 'comment_form_must_log_in_after'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_top/', 'comment_form_top')).'</p>'.
								                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
								                'cm_details'    => $shortcode_details(array(
									                                                      '[css_styles]'    => __('Stylesheet containing a default set of structral styles.', $this->plugin->text_domain),
									                                                      '[service_links]' => __('Links/icons for the SSO services that you have integrated with.', $this->plugin->text_domain),
								                                                      )),
							                ))).
					                '             </tbody>'.
					                '          </table>'.
					                '       </div>'.
					                '    </div>'.

					                '    <hr />'.

					                '    <div class="pmp-if-nest">'.
					                '       <table style="margin-bottom:0;">'.
					                '          <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Enable Login Form SSO Options Template?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'field_class'     => 'pmp-if-change', // JS change handler.
							                'name'            => 'login_form_sso_template_enable',
							                'current_value'   => $current_value_for('login_form_sso_template_enable'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '1' => __('Yes, use built-in template system (recommended)', $this->plugin->text_domain),
								                '0' => __('No, disable built-in template system; I have a deep theme integration of my own', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.__('The built-in template system is quite flexible already; you can even customize the default template yourself if you want to (as seen below). Therefore, it is not recommended that you disable the default template system. This option only exists for very advanced users; i.e., those who prefer to disable the template completely in favor of their own custom implementation. If you disable the built-in template, you\'ll need to integrate HTML markup of your own into the proper location of your theme.', $this->plugin->text_domain).'</p>',
						                )).
					                '          </tbody>'.
					                '       </table>'.

					                '       <div class="pmp-if-disabled-show pmp-if-in-nest">'.
					                '          <table style="margin-bottom:0;">'.
					                '             <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Also Disable Scripts Associated w/ Login Form SSO Options?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'login_form_sso_scripts_enable',
							                'current_value'   => $current_value_for('login_form_sso_scripts_enable'),
							                'allow_arbitrary' => FALSE, // Must be one of these.
							                'options'         => array(
								                '1' => __('No, leave scripts associated w/ login form SSO options enabled (recommended)', $this->plugin->text_domain),
								                '0' => __('Yes, disable built-in scripts also; I have a deep theme integration of my own', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.__('For advanced use only. If you disable the built-in template system, you may also want to disable the built-in JavaScript associated w/ this template.', $this->plugin->text_domain).'</p>',
						                )).
					                '             </tbody>'.
					                '          </table>'.
					                '       </div>'.

					                '       <div class="pmp-if-enabled-show pmp-if-in-nest">'.
					                '          <table>'.
					                '             <tbody>'.
					                ($this->plugin->options['template_type'] === 'a'
						                ? $form_fields->textarea_row( // Advanced PHP-based template.
							                array(
								                'label'         => __('Login Form SSO Options Template', $this->plugin->text_domain),
								                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
								                'cm_mode'       => 'application/x-httpd-php', 'cm_height' => 250,
								                'name'          => 'template__type_'.$this->plugin->options['template_type'].'__site__login_form__sso_ops___php',
								                'current_value' => $current_value_for('template__type_'.$this->plugin->options['template_type'].'__site__login_form__sso_ops___php'),
								                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
								                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sso-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
								                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your login form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_form/', 'login_form'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_footer/', 'login_footer')).'</p>'.
								                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							                ))
						                : $form_fields->textarea_row( // Simple snippet-based template.
							                array(
								                'label'         => __('Login Form SSO Options Template', $this->plugin->text_domain),
								                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
								                'cm_mode'       => 'text/html', 'cm_height' => 250,
								                'name'          => 'template__type_'.$this->plugin->options['template_type'].'__site__login_form__snippet__sso_ops___php',
								                'current_value' => $current_value_for('template__type_'.$this->plugin->options['template_type'].'__site__login_form__snippet__sso_ops___php'),
								                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
								                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sso-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
								                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your login form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_form/', 'login_form'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_footer/', 'login_footer')).'</p>'.
								                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
								                'cm_details'    => $shortcode_details(array(
									                                                      '[css_styles]'    => __('Stylesheet containing a default set of structral styles.', $this->plugin->text_domain),
									                                                      '[service_links]' => __('Links/icons for the SSO services that you have integrated with.', $this->plugin->text_domain),
								                                                      )),
							                ))).
					                '             </tbody>'.
					                '          </table>'.
					                '       </div>'.

					                '    </div>'. // END: if nest.

					                ' </div>'. // END: toggled advanced (optional) settings.

					                '</div>'; // END: if nest.

					echo $this->panel(__('Single Sign-on Integration (SSO)', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Blacklist Patterns (One Per Line)', $this->plugin->text_domain),
							               'placeholder'   => __('e.g., webmaster@*', $this->plugin->text_domain),
							               'name'          => 'email_blacklist_patterns',
							               'rows'          => 15, // Give them some room here.
							               'other_attrs'   => 'spellcheck="false"',
							               'current_value' => $current_value_for('email_blacklist_patterns'),
							               'notes_before'  => '<p>'.__('These email addresses will not be allowed to subscribe.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p>'.__('One pattern per line please. A <code>*</code> wildcard character can be used to match zero or more characters of any kind. A <code>^</code> caret symbol can be used to match zero or more characters that are NOT the <code>@</code> symbol.', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> It is suggested that you blacklist role-based email addresses to avoid sending email notifications to addresses not associated w/ individuals. Role-based email addresses (like admin@, help@, sales@) are email addresses that are not associated with a particular person, but rather with a company, department, position or group of recipients. They are not generally intended for personal use, as they typically include a distribution list of recipients.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Blacklisted Email Addresses', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->input_row(
						               array(
							               'type'          => 'number',
							               'label'         => __('Max Execution Time (In Seconds)', $this->plugin->text_domain),
							               'placeholder'   => __('e.g., 30', $this->plugin->text_domain),
							               'name'          => 'sub_cleaner_max_time',
							               'current_value' => $current_value_for('sub_cleaner_max_time'),
							               'other_attrs'   => 'min="10" max="3600"',
							               'notes_after'   => '<p>'.sprintf(__('The Subscription Cleaner automatically deletes unconfirmed and trashed subscriptions. It runs via %1$s every hour. This setting determines how much time you want to allow each cleaning process to run for. The minimum allowed value is <code>10</code> seconds. Maximum allowed value is <code>3600</code> seconds. A good default value is <code>30</code> seconds. That\'s more than adequate for most sites.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/', 'WP-Cron')).'</p>'
						               )).
					               '  </tbody>'.
					               '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Unconfirmed Expiration Time (<code>%1$s</code> Compatible)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')),
							                'placeholder'   => __('e.g., 60 days', $this->plugin->text_domain),
							                'name'          => 'unconfirmed_expiration_time',
							                'current_value' => $current_value_for('unconfirmed_expiration_time'),
							                'notes_after'   => '<p>'.sprintf(__('How long should unconfirmed subscriptions be kept in the database? e.g., <code>2 days</code>, <code>1 week</code>, <code>2 months</code>. Anything compatible with PHP\'s <code>%1$s</code> function will work here.', $this->plugin->text_domain).'</p>'.
							                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, unconfirmed subscriptions will not be cleaned, they will remain indefinitely.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Trash Expiration Time (<code>%1$s</code> Compatible)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')),
							                'placeholder'   => __('e.g., 60 days', $this->plugin->text_domain),
							                'name'          => 'trashed_expiration_time',
							                'current_value' => $current_value_for('trashed_expiration_time'),
							                'notes_after'   => '<p>'.sprintf(__('How long should trashed subscriptions be kept in the database? e.g., <code>2 days</code>, <code>1 week</code>, <code>2 months</code>. Anything compatible with PHP\'s <code>%1$s</code> function will work here.', $this->plugin->text_domain).'</p>'.
							                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, trashed subscriptions will not be cleaned, they will remain indefinitely.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					echo $this->panel(__('Sub. Cleaner Adjustments', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->input_row(
						               array(
							               'type'          => 'number',
							               'label'         => __('Max Execution Time (In Seconds)', $this->plugin->text_domain),
							               'placeholder'   => __('e.g., 30', $this->plugin->text_domain),
							               'name'          => 'log_cleaner_max_time',
							               'current_value' => $current_value_for('log_cleaner_max_time'),
							               'other_attrs'   => 'min="10" max="3600"',
							               'notes_after'   => '<p>'.sprintf(__('The Log Cleaner can automatically delete very old event log entries. It runs via %1$s every hour. This setting determines how much time you want to allow each cleaning process to run for. The minimum allowed value is <code>10</code> seconds. Maximum allowed value is <code>3600</code> seconds. A good default value is <code>30</code> seconds. That\'s more than adequate for most sites.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/', 'WP-Cron')).'</p>'
						               )).
					               '  </tbody>'.
					               '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Sub. Event Log Expiration Time (<code>%1$s</code> Compatible)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')),
							                'placeholder'   => __('e.g., 7 years', $this->plugin->text_domain),
							                'name'          => 'sub_event_log_expiration_time',
							                'current_value' => $current_value_for('sub_event_log_expiration_time'),
							                'notes_after'   => '<p>'.sprintf(__('How long should should subscription event log entries be kept in the database? e.g., <code>90 days</code>, <code>1 year</code>, <code>10 years</code>. Anything compatible with PHP\'s <code>%1$s</code> function will work here.', $this->plugin->text_domain).'</p>'.
							                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, log entries will not be cleaned; they will remain indefinitely (default behavior). By default, log entries remain indefinitely since these are the underlying data used for statistical reporting. However, if you are not concerned about long-term historical data, feel free to define an expiration time. If you do, it is recommended that your expiration time be <code>1 year</code> (or more) so that statistical reporting will still function properly for short-term data.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'label'         => sprintf(__('Queue Event Log Expiration Time (<code>%1$s</code> Compatible)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')),
							                'placeholder'   => __('e.g., 7 years', $this->plugin->text_domain),
							                'name'          => 'queue_event_log_expiration_time',
							                'current_value' => $current_value_for('queue_event_log_expiration_time'),
							                'notes_after'   => '<p>'.sprintf(__('How long should should queue event log entries be kept in the database? e.g., <code>90 days</code>, <code>1 year</code>, <code>10 years</code>. Anything compatible with PHP\'s <code>%1$s</code> function will work here.', $this->plugin->text_domain).'</p>'.
							                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, log entries will not be cleaned; they will remain indefinitely (default behavior). By default, log entries remain indefinitely since these are the underlying data used for statistical reporting. However, if you are not concerned about long-term historical data, feel free to define an expiration time. If you do, it is recommended that your expiration time be <code>1 year</code> (or more) so that statistical reporting will still function properly for short-term data.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					echo $this->panel(__('Log Cleaner Adjustments', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->input_row(
						               array(
							               'type'          => 'number',
							               'label'         => __('Max Execution Time (In Seconds)', $this->plugin->text_domain),
							               'placeholder'   => __('e.g., 30', $this->plugin->text_domain),
							               'name'          => 'queue_processor_max_time',
							               'current_value' => $current_value_for('queue_processor_max_time'),
							               'other_attrs'   => 'min="10" max="300"',
							               'notes_after'   => '<p>'.sprintf(__('The Queue Processor sends email notifications. It runs via %1$s every 5 minutes. This setting determines how much time you want to allow each process to run for. The minimum allowed value is <code>10</code> seconds. Maximum allowed value is <code>300</code> seconds. A good default value is <code>30</code> seconds. That\'s more than adequate for most sites.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/', 'WP-Cron')).'</p>'
						               )).
					               '  </tbody>'.
					               '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Delay Time (In Milliseconds)', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 250', $this->plugin->text_domain),
							                'name'          => 'queue_processor_delay',
							                'current_value' => $current_value_for('queue_processor_delay'),
							                'other_attrs'   => 'min="0"',
							                'notes_before'  => '<p><em>1000 milliseconds = 1 second; 500 milliseconds = .5 seconds; 250 milliseconds = .25 seconds</em></p>',
							                'notes_after'   => '<p>'.__('The Queue Processor has the ability to send multiple email notifications consecutively when it runs. However, you can force a delay between each email that it sends while it is running. This will help reduce server load and also reduce the chance of your server being flagged as a bulk sender. The minimum allowed value is <code>0</code> milliseconds (<code>0</code> will disable the delay completely). The maximum allowed value (converted to seconds) is <code>([configured Max Execution Time] - 5 seconds)</code>. A good default value is <code>250</code> milliseconds. That\'s perfect for most sites. That said, if you have a lot of emails ending up in the spam folder, try raising this in <code>250</code> millisecond increments until things improve.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Max Email Notifications Per Process (Integer)', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 100', $this->plugin->text_domain),
							                'name'          => 'queue_processor_max_limit',
							                'current_value' => $current_value_for('queue_processor_max_limit'),
							                'other_attrs'   => 'min="1"',
							                'notes_after'   => '<p>'.__('The Queue Processor will pull X number of pending notifications from the database each time it runs, and then work on those for as long as it can, given your configuration above. This setting allows you to control the max number of email notifications that it should work on in each process. In short, you can use this option to control the maximum number of emails that can ever be sent by each queue runner. Keep in mind, the Queue Processor runs once every 5 minutes. The limit that you define here will allow X number of emails to be sent each time that it runs. The minimum allowed value is <code>1</code>. Maximum allowed value is <code>1000</code> (for security reasons). However, this upper limit can be raised further (if absolutely necessary) through a WP filter.', $this->plugin->text_domain).'</p>'.
							                                   '<p class="pmp-note pmp-info">'.__('It\'s important to realize that what you define here may not always be possible; i.e., this is a maximum limit, not an exact number that will always be processed. For instance, if you set this to <code>1000</code> but you change Max Execution Time to <code>10</code>, there is very little chance that 1000 email notifications can be sent in just <code>10</code> seconds. In such a scenario, the Queue Processor will attempt to process up to <code>1000</code>, but stop after <code>10</code> seconds and work on whatever remains later.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Real-Time Queue Processor; Max Email Notifications in Real-Time (Integer)', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 5', $this->plugin->text_domain),
							                'name'          => 'queue_processor_realtime_max_limit',
							                'current_value' => $current_value_for('queue_processor_realtime_max_limit'),
							                'other_attrs'   => 'min="0" max="100"',
							                'notes_after'   => '<p>'.__('In addition to the Queue Processor running via WP-Cron, it can also run in real-time as a comment is being posted (assuming that particular comment is automatically approved; i.e., that it doesn\'t require administrative approval). In cases where it\'s possible, real-time queue processing allows for easier testing and for more-immediate notifications. It is particularly helpful on posts that only have just a few subscribers anyway. There is no mass-mailing needed in such a scenario.', $this->plugin->text_domain).'</p>'.
							                                   '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> It is recommended that you keep this number very low; i.e., just a few notifications should be attempted in real-time. The rest (if there are any) can be handled by queue processes running via WP-Cron. A suggested setting for this option is <code>5</code>. If you set this to <code>0</code> it will effectively disable real-time queue processing if you wish. There\'s an upper limit of <code>100</code> to avoid serious real-time processing delays for end-users. Under no circumstance (no matter what you configure here), will real-time processing ever be allowed to continue for more than <code>10</code> seconds. Therefore, whatever you configure here will be a maximum allowed within the <code>10</code> second timeframe. If you set this too high for completion within <code>10</code> seconds, whatever remains will be processed by WP-Cron queue runners later.', $this->plugin->text_domain).'</p>'
						                )).
					                '  </tbody>'.
					                '</table>';

					echo $this->panel(__('Queue Processor Adjustments', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('WordPress Capability Required to Manage Subscriptions', $this->plugin->text_domain),
						               'placeholder'   => __('e.g., moderate_comments', $this->plugin->text_domain),
						               'name'          => 'manage_cap',
						               'current_value' => $current_value_for('manage_cap'),
						               'notes_after'   => '<p>'.sprintf(__('If you can <code>%2$s</code>, you can always manage subscriptions and %1$s options, no matter what you configure here. However, if you have other users that help manage your site, you can set a specific %3$s they\'ll need in order for %1$s to allow them access. Users w/ this capability will be allowed to manage subscriptions, the mail queue, event logs, and statistics; i.e., everything <em>except</em> change %1$s options. To alter %1$s options you\'ll always need the <code>%2$s</code> capability.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities#'.$this->plugin->cap, $this->plugin->cap), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities', __('WordPress Capability', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Subscription Management Access', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('Don\'t Show Meta Boxes for these Post Types:', $this->plugin->text_domain),
						               'placeholder'   => __('e.g., link,comment,revision,attachment,nav_menu_item,snippet,redirect', $this->plugin->text_domain),
						               'name'          => 'excluded_meta_box_post_types',
						               'current_value' => $current_value_for('excluded_meta_box_post_types'),
						               'notes_after'   => '<p>'.sprintf(__('These are %2$s NOT associated w/ comments in any way; i.e., %1$s will not display its meta boxes in the post editing station for these types of posts. The default list is adequate for most sites. However, if you have other %2$s enabled by a theme/plugin, you might wish to include those here. e.g., <code>portfolio,gallery</code> might be two %3$s that you add to the default list, assuming these are not to be associated w/ comments.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types', __('Post Types', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types#Custom_Post_Types', __('Custom Post Types', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Post Meta Box Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->select_row(
						               array(
							               'label'           => sprintf(__('Simple Templates or Advanced PHP Templates?', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							               'name'            => 'template_type',
							               'current_value'   => $current_value_for('template_type'),
							               'allow_arbitrary' => FALSE,
							               'options'         => array(
								               's' => __('Simple shortcode templates (default; easiest to work with)', $this->plugin->text_domain),
								               'a' => __('Advanced PHP-based templates (for developers and advanced site owners)', $this->plugin->text_domain),
							               ),
							               'notes_after'     => '<p>'.__('<strong>Note:</strong> If you change this setting, any template customizations that you\'ve made in one mode, will need to be done again for the new mode that you select; i.e., when this setting is changed, a new set of templates is loaded for the mode you select. You can always switch back though, and any changes that you made in the previous mode will be restored automatically.', $this->plugin->text_domain).'</p>'.
							                                    '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Tip:</strong> You\'ll notice that by changing this setting, all of the customizable templates in %1$s will be impacted; i.e., when you select %2$s or %3$s from the menu at the top, a new set of templates will load-up; based on the mode that you choose here. You can also switch modes <em>while</em> you\'re editing templates (see: %2$s and/or %3$s). That will impact this setting in the exact same way. Change it here or change it there, no difference.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor($this->plugin->utils_url->email_templates_menu_page_only(), __('Email Templates', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor($this->plugin->utils_url->site_templates_menu_page_only(), __('Site Templates', $this->plugin->text_domain))).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Template-Related Settings', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->input_row(
						               array(
							               'type'          => 'number',
							               'label'         => __('"My Subscriptions" Summary; Max Subscriptions Per Page', $this->plugin->text_domain),
							               'placeholder'   => __('e.g., 25', $this->plugin->text_domain),
							               'name'          => 'sub_manage_summary_max_limit',
							               'current_value' => $current_value_for('sub_manage_summary_max_limit'),
							               'other_attrs'   => 'min="1" max="1000"',
							               'notes_after'   => '<p>'.sprintf(__('On the front-end of %1$s, the "My Subscriptions" summary page will list all of the subscriptions currently associated with a subscriber\'s email address. This controls the maximum number of subscriptions to list per page. Minimum value is <code>1</code> subscription per page. Maximum value is <code>1000</code> subscriptions per page. The recommended setting is <code>25</code> subscriptions per page. Based on your setting here; if there are too many to display on a single page, pagination links will appear automatically.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Select Menu Options; List Posts?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'post_select_options_enable',
							                'current_value'   => $current_value_for('post_select_options_enable'),
							                'allow_arbitrary' => FALSE,
							                'options'         => array(
								                '1' => __('Yes, enable post select menu options', $this->plugin->text_domain),
								                '0' => __('No, disable post selection; users can enter post IDs manually', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('On both the back and front-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing posts for you to choose from. Would you like to enable or disable this feature? If disabled, you will need to enter any post IDs manually instead of being able to choose from a drop-down menu. Since this impacts the front-end too, it is generally a good idea to enable select menu options for your users.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Post Select Menu Options; Include Media?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'post_select_options_media_enable',
							                'current_value'   => $current_value_for('post_select_options_media_enable'),
							                'allow_arbitrary' => FALSE,
							                'options'         => array(
								                '0' => __('No, exclude media attachments (save space); I don\'t receive comments on media', $this->plugin->text_domain),
								                '1' => __('Yes, include enable media attachments in any post select menu options', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('On both the back and front-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing posts for you to choose from. This feature can be enabled/disabled above. If enabled, do you want the post select menu options to include media attachments too? If you have a lot of posts, it\'s a good idea to exclude media attachments from the select menu options to save space. Most people don\'t leave comments on media attachments.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Select Menu Options; List Comments?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'comment_select_options_enable',
							                'current_value'   => $current_value_for('comment_select_options_enable'),
							                'allow_arbitrary' => FALSE,
							                'options'         => array(
								                '1' => __('Yes, enable comment select menu options', $this->plugin->text_domain),
								                '0' => __('No, disable comment selection; users enter comment IDs manually', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('On both the back and front-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing comments (on a given post) for you to choose from. Would you like to enable or disable this feature? If disabled, you will need to enter any comment IDs manually instead of being able to choose from a drop-down menu. Since this impacts the front-end too, it is generally a good idea to enable select menu options for your users.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => __('Select Menu Options; List Users?', $this->plugin->text_domain),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'user_select_options_enable',
							                'current_value'   => $current_value_for('user_select_options_enable'),
							                'allow_arbitrary' => FALSE,
							                'options'         => array(
								                '1' => __('Yes, enable user select menu options', $this->plugin->text_domain),
								                '0' => __('No, disable user selection; I can enter user IDs manually', $this->plugin->text_domain),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('On the back-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing users for you to choose from. Would you like to enable or disable this feature? If disabled, you will need to enter any user IDs manually instead of being able to choose from a drop-down menu.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Maximum Select Menu Options Before Input Fallback:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 2000', $this->plugin->text_domain),
							                'name'          => 'max_select_options',
							                'current_value' => $current_value_for('max_select_options'),
							                'notes_after'   => '<p>'.sprintf(__('If %1$s detects that any select menu may contain more than this number of options (e.g., if you have several thousands posts, comments, users, etc); then it will automatically fallback on a regular text input field instead. This prevents memory issues in browsers that may be unable to deal with super long select menus. Recommended setting for this option is <code>2000</code>.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
							                                   '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Tip:</strong> You\'ll be happy to know that %1$s is quite capable of including hundreds of select menu options w/o issue. It even makes each select menu searchable for you. However, there is a limit to what is possible. We recommend setting this to a value of around <code>1000</code> or more. It should never be set higher than <code>10000</code> though. Most browsers will be unable to deal with that many menu options; no matter the software.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                '  </tbody>'.
					                '</table>';

					$_panel_body .= '<hr />';

					$_panel_body .= '<table>'.
					                '  <tbody>'.
					                $form_fields->select_row(
						                array(
							                'label'           => sprintf(__('Display %1$s&trade; Logo in Admin Area?', $this->plugin->text_domain), esc_html($this->plugin->name)),
							                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
							                'name'            => 'menu_pages_logo_icon_enable',
							                'current_value'   => $current_value_for('menu_pages_logo_icon_enable'),
							                'allow_arbitrary' => FALSE,
							                'options'         => array(
								                '1' => sprintf(__('Yes, enable logo in back-end administrative areas for %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name)),
								                '0' => sprintf(__('No, disable logo in back-end administrative areas for %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name)),
							                ),
							                'notes_after'     => '<p>'.sprintf(__('Enabling/disabling the logo in back-end areas does not impact any functionality; it\'s simply a personal preference.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
						                )).
					                '  </tbody>'.
					                '</table>';

					echo $this->panel(__('Misc. UI-Related Settings', $this->plugin->text_domain), $_panel_body, array('pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function import_export_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-import-export '.$this->plugin->slug.'-menu-page-area').'">'."\n";

				echo '   '.$this->heading(__('Import/Export', $this->plugin->text_domain), 'logo.png').
				     '   '.$this->notes(); // Heading/notifications.

				echo '   <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '      <h2 class="pmp-section-heading">'.
				     '         '.__('Import/Export Subscriptions', $this->plugin->text_domain).
				     '         <small>'.sprintf(__('This allows you to import/export %1$s&trade; subscriptions.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '      </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_form_field_args = array(
						'ns_id_suffix'   => '-import-subs-form',
						'ns_name_suffix' => '[import]',
						'class_prefix'   => 'pmp-import-subs-form-',
					);
					$_form_fields     = new form_fields($_form_field_args);

					$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

					$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Import New %1$s&trade; Subscriptions, or Update Existing Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
					$_panel_body .= ' <p>'.sprintf(__('The importation routine will accept direct CSV input in the textarea below, or you can choose to upload a prepared CSV file.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
					$_panel_body .= ' <p class="pmp-note pmp-notice" style="font-size:90%;">'.sprintf(__('<strong>Note:</strong> The format required for importation is %2$s. For mass updates, an <code>"ID"</code> is the only column that is absolutely required. The <code>"ID"</code> column (if present) indicates that you want to update an existing subscription with a particular ID. However, for new subscriptions; please omit the <code>"ID"</code> column. When importing new subscriptions, your CSV file need only contain the <code>"email"</code> and <code>"post_id"</code> columns. There are %3$s w/ a full list of all possible import columns. In either case (direct input or file upload) the first line should be a list of columns you\'re importing; aka: headers.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Comma-separated_values', 'CSV (Comma Separated Values)'), $this->plugin->utils_markup->x_anchor('http://comment-mail.com/kb-article/csv-importexport-tools/', __('additional details here', $this->plugin->text_domain))).'</p>';
					$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Tip:</strong> If you\'re looking for more elaborate examples, you can simply use the "CSV Export" panel on this page. The easiest way to see how this works is by looking at a CSV export file generated by %1$s&trade; itself. That\'s the format you should follow please. In fact, you could even pull an export, make changes to the file, and then import that modified file to mass update existing subscriptions.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
					$_panel_body .= ' <p class="pmp-note pmp-warning" style="font-size:90%;">'.sprintf(__('<strong>Upper Limits:</strong> There is an upper limit of <code>5000</code> lines allowed per import; i.e., you must limit each import to this number of lines so as to avoid extremely long-running PHP processes. In addition, given your current web host (i.e., PHP configuration); if you choose to upload a prepared CSV file, the maximum allowed file upload size is currently: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($this->plugin->utils_fs->bytes_abbr($this->plugin->utils_env->max_upload_size()))).'</p>';

					$_panel_body .= ' <table>'.
					                '   <tbody>'.
					                $_form_fields->textarea_row(
						                array(
							                'label'         => __('Direct CSV Input Data:', $this->plugin->text_domain),
							                'placeholder'   => __('"email", "post_id", "status"'."\n".'"john@example.com", "1", "subscribed"', $this->plugin->text_domain),
							                'name'          => 'data',
							                'rows'          => 15,
							                'current_value' => !empty($_REQUEST[__NAMESPACE__]['import']['data']) ? trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['import']['data'])) : NULL,
							                'notes_before'  => '<p>'.__('The first line of this input should be CSV headers; e.g., <code>"email", "post_id", "status"</code>', $this->plugin->text_domain).'</p>',
						                )).
					                '   </tbody>'.
					                ' </table>';

					$_panel_body .= ' <hr />';

					$_panel_body .= ' <table>'.
					                '   <tbody>'.
					                $_form_fields->input_row(
						                array(
							                'type'         => 'file',
							                'label'        => __('Or, a Prepared CSV File Upload:', $this->plugin->text_domain),
							                'placeholder'  => __('e.g., comment-subscriptions.csv', $this->plugin->text_domain),
							                'name'         => 'data_file',
							                'notes_before' => '<p>'.__('The first line of this file should be CSV headers; e.g., <code>"email", "post_id", "status"</code>', $this->plugin->text_domain).'</p>',
							                'notes_after'  => '<p>'.__('If you upload a file, it will be used instead of any direct input above; i.e., a file takes precedence over direct input.', $this->plugin->text_domain).'</p>',
						                )).
					                '   </tbody>'.
					                ' </table>';

					if(!$this->plugin->options['auto_confirm_force_enable'])
					{
						$_panel_body .= ' <hr />';

						$_panel_body .= '  <table>'.
						                '    <tbody>'.
						                $_form_fields->input_row(
							                array(
								                'type'           => 'checkbox',
								                'label'          => __('Process Email Confirmations?', $this->plugin->text_domain),
								                'checkbox_label' => __('Yes, send an email confirmation to anyone being inserted or updated with an <code>unconfirmed</code> status.', $this->plugin->text_domain),
								                'name'           => 'process_confirmations',
								                'current_value'  => '1',
								                'notes_before'   => '<p class="pmp-note pmp-warning">'.__('<strong>Warning:</strong> Please be cautious with this choice. If you import new subscriptions and don\'t specify a particular status, the default status is <code>unconfirmed</code>. Thus, checking this box will attempt to confirm new subscriptions via email. Depending on the number of subscriptions you\'re importing, this could be a very large number of emails going out all at one time! Please use this with extreme caution.', $this->plugin->text_domain).'</p>'
							                )).
						                '    </tbody>'.
						                '  </table>';
					}
					$_panel_body .= ' <hr />';

					$_panel_body .= ' <div style="display:none;">'.
					                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'subs')).
					                ' </div>';

					$_panel_body .= ' <button type="submit" style="width:100%;">'.
					                '  '.__('Import Now', $this->plugin->text_domain).' <i class="fa fa-upload"></i>'.
					                ' </button>';

					$_panel_body .= '</form>';

					echo $this->panel(__('CSV Import and/or Mass Update', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>', 'pro_only' => TRUE));

					unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if(import_stcr::data_exists())
				{
					$_form_field_args = array(
						'ns_id_suffix'   => '-import-stcr-form',
						'ns_name_suffix' => '[import]',
						'class_prefix'   => 'pmp-import-stcr-form-',
					);
					$_form_fields     = new form_fields($_form_field_args);

					$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'"'.
					               ' target="'.esc_attr(__NAMESPACE__.'_import_stcr_iframe').'" novalidate="novalidate">'."\n";

					$_panel_body .= ' <table style="table-layout:auto;">'.
					                '    <tbody>'.
					                '       <tr>';
					$_panel_body .= '          <td style="white-space:nowrap;">'.
					                '             <button type="submit" class="pmp-left">'.
					                '                '.__('Begin StCR Auto-Importation', $this->plugin->text_domain).' <i class="fa fa-magic"></i>'.
					                '             </button>'.
					                '          </td>';
					$_panel_body .= '          <td style="width:100%;">'.
					                '             <p>'.sprintf(__('%1$s&trade; has detected that you have data in your WordPress database tables containing comment subscriptions associated with Subscribe to Comments Reloaded (StCR). If you would like %1$s to import this data automagically, please click this button to proceed.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
					                '          </td>';
					$_panel_body .= '       </tr>'.
					                '    </tbody>'.
					                ' </table>';

					$_panel_body .= ' <div style="display:none;">'.
					                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'stcr')).
					                ' </div>';

					$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Note:</strong> This process may take several minutes. %1$s will work through each post in your database, collecting all of the StCR subscriptions that exist (just a few at a time to prevent any script timeouts). The status bar below may refresh several times during this process. When it\'s complete, you should see a message that reads "<strong>Import complete!</strong>", along with a few details regarding the importation. When it is finished, you may <a href="%2$s">click here</a> to view a list of all subscriptions; which will include any that were imported from StCR. If the importation is interrupted for any reason, you may simply click the button again and %1$s will resume where it left off.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_attr($this->plugin->utils_url->subs_menu_page_only())).'</p>';

					$_panel_body .= '</form>';

					$_panel_body .= '<iframe src="'.esc_attr($this->plugin->utils_url->to('/client-s/blanks/cccccc.html')).'" name="'.esc_attr(__NAMESPACE__.'_import_stcr_iframe').'" class="pmp-import-iframe-output"></iframe>';

					echo $this->panel(__('Subscribe to Comments Reloaded (StCR)', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>', 'open' => (!$this->plugin->is_pro && !$this->plugin->utils_env->is_pro_preview()) || !import_stcr::ever_imported()));

					unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_form_field_args = array(
						'ns_id_suffix'   => '-export-subs-form',
						'ns_name_suffix' => '[export]',
						'class_prefix'   => 'pmp-export-subs-form-',
					);
					$_form_fields     = new form_fields($_form_field_args);

					$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

					$_total_subs_in_db = $this->plugin->utils_sub->query_total(NULL, array('auto_discount_trash' => FALSE));
					$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Export All of your %1$s&trade; Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
					$_panel_body .= ' <p>'.sprintf(__('There are currently %1$s in the database. You can export these in sets of however many you like, as configured below.', $this->plugin->text_domain), esc_html($this->plugin->utils_i18n->subscriptions($_total_subs_in_db))).'</p>';

					$_panel_body .= ' <table>'.
					                '    <tbody>'.
					                $_form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Start Position:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 1', $this->plugin->text_domain),
							                'name'          => 'start_from',
							                'current_value' => '1',
							                'other_attrs'   => 'min="1"',
							                'notes_after'   => '<p>'.__('e.g., If you already downloaded the first 1000, set this to <code>1001</code> to export the next set.', $this->plugin->text_domain).'</p>'
						                )).
					                '    </tbody>'.
					                ' </table>';

					$_panel_body .= '  <table>'.
					                '    <tbody>'.
					                $_form_fields->input_row(
						                array(
							                'type'          => 'number',
							                'label'         => __('Max Subscriptions in this Set:', $this->plugin->text_domain),
							                'placeholder'   => __('e.g., 1000', $this->plugin->text_domain),
							                'name'          => 'max_limit',
							                'current_value' => '1000',
							                'other_attrs'   => 'min="1" max="5000"',
							                'notes_after'   => '<p>'.__('e.g., If you start from <code>1</code> and set this to <code>1000</code>, you will get the first 1000 DB rows. If you want the next 1000 rows, set Start Position to <code>1001</code> and leave this as-is.', $this->plugin->text_domain).'</p>'.
							                                   '<p class="pmp-note pmp-warning">'.__('<strong>Upper Limit:</strong> There is an upper limit of <code>5000</code> per file to prevent extremely slow DB queries; i.e., you cannot set this higher than <code>5000</code>.', $this->plugin->text_domain).'</p>'
						                )).
					                '    </tbody>'.
					                '  </table>';

					$_panel_body .= ' <hr />';

					$_panel_body .= '  <table>'.
					                '    <tbody>'.
					                $_form_fields->input_row(
						                array(
							                'type'           => 'checkbox',
							                'label'          => __('Include UTF-8 BOM (Byte Order Marker)?', $this->plugin->text_domain),
							                'checkbox_label' => __('Yes, my spreadsheet application needs this to detect UTF-8 encoding properly.', $this->plugin->text_domain),
							                'name'           => 'include_utf8_bom',
							                'current_value'  => '1',
						                )).
					                '    </tbody>'.
					                '  </table>';

					$_panel_body .= ' <hr />';

					$_panel_body .= ' <div style="display:none;">'.
					                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'subs')).
					                ' </div>';

					$_panel_body .= ' <button type="submit" style="width:100%;">'.
					                '  '.__('Download CSV Export File', $this->plugin->text_domain).' <i class="fa fa-download"></i>'.
					                ' </button>';

					$_panel_body .= '</form>';

					echo $this->panel(__('CSV Export (File Download)', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-download"></i>', 'pro_only' => TRUE));

					unset($_form_field_args, $_form_fields, $_total_subs_in_db, $_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					echo '      <h2 class="pmp-section-heading">'.
					     '         '.__('Import/Export Config. Options', $this->plugin->text_domain).
					     '         <small>'.sprintf(__('This allows you to import/export %1$s&trade; configuration options.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
					     '      </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_form_field_args = array(
						'ns_id_suffix'   => '-import-ops-form',
						'ns_name_suffix' => '[import]',
						'class_prefix'   => 'pmp-import-ops-form-',
					);
					$_form_fields     = new form_fields($_form_field_args);

					$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

					$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Import a New Set of %1$s&trade; Config. Options', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
					$_panel_body .= ' <p>'.sprintf(__('Configuration options are imported using a JSON-encoded file obtained from another copy of %1$s&trade;.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
					$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Tip:</strong> To save time you can import your options from another WordPress installation where you\'ve already configured %1$s&trade; before.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';

					$_panel_body .= ' <table>'.
					                '   <tbody>'.
					                $_form_fields->input_row(
						                array(
							                'type'        => 'file',
							                'label'       => __('JSON Config. Options File:', $this->plugin->text_domain),
							                'placeholder' => __('e.g., config-options.json', $this->plugin->text_domain),
							                'name'        => 'data_file',
						                )).
					                '   </tbody>'.
					                ' </table>';

					$_panel_body .= ' <div style="display:none;">'.
					                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'ops')).
					                ' </div>';

					$_panel_body .= ' <button type="submit" style="width:100%;">'.
					                '  '.__('Import JSON Config. Options File', $this->plugin->text_domain).' <i class="fa fa-upload"></i>'.
					                ' </button>';

					$_panel_body .= '</form>';

					echo $this->panel(__('Import Config. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>', 'pro_only' => TRUE));

					unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
				{
					$_form_field_args = array(
						'ns_id_suffix'   => '-export-ops-form',
						'ns_name_suffix' => '[export]',
						'class_prefix'   => 'pmp-export-ops-form-',
					);
					$_form_fields     = new form_fields($_form_field_args);

					$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

					$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Export All of your %1$s&trade; Config. Options', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
					$_panel_body .= ' <p>'.__('Configuration options are downloaded as a JSON-encoded file.', $this->plugin->text_domain).'</p>';
					$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.__('<strong>Tip:</strong> Export your configuration on this site, and then import it into another WordPress installation to save time in the future.', $this->plugin->text_domain).'</p>';

					$_panel_body .= ' <div style="display:none;">'.
					                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'ops')).
					                ' </div>';

					$_panel_body .= ' <button type="submit" style="width:100%;">'.
					                '  '.__('Download JSON Config. Options File', $this->plugin->text_domain).' <i class="fa fa-download"></i>'.
					                ' </button>';

					$_panel_body .= '</form>';

					echo $this->panel(__('Export Config. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-download"></i>', 'pro_only' => TRUE));

					unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				echo '   </div>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function email_templates_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-email-templates-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-email-templates-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0 && isset($_this->plugin->options[$key]))
					{
						if($_this->plugin->options[$key])
							return $_this->plugin->options[$key];

						$data             = template::option_key_data($key);
						$default_template = new template($data->file, $data->type, TRUE);

						return $default_template->file_contents();
					}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				$shortcode_details = function ($shortcodes) use ($_this)
				{
					$detail_lis = array(); // Initialize.

					foreach($shortcodes as $_shortcode => $_details)
						$detail_lis[] = '<li><code>'.esc_html($_shortcode).'</code>&nbsp;&nbsp;'.$_details.'</li>';
					unset($_shortcode, $_details); // Housekeeping.

					if($detail_lis) // If we have shortcodes, let's list them.
						$details = '<ul class="pmp-list-items" style="margin-top:0; margin-bottom:0;">'.implode('', $detail_lis).'</ul>';
					else $details = __('No shortcodes for this template at the present time.', $_this->plugin->text_domain);

					return '<a href="#" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('shortcodes explained', $_this->plugin->text_domain).'</a>';
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-email-templates '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Email Templates', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				if($this->plugin->is_pro) // Only possible in the pro version.
				{
					echo '      <div class="pmp-template-types pmp-right">'.
					     '         <span>'.__('Template Mode:', $this->plugin->text_domain).'</span>'.
					     '         <a href="'.esc_attr($this->plugin->utils_url->set_template_type('s')).'"'.($this->plugin->options['template_type'] === 's' ? ' class="pmp-active"' : '').'>'.__('simple', $this->plugin->text_domain).'</a>'.
					     '         <a href="'.esc_attr($this->plugin->utils_url->set_template_type('a')).'"'.($this->plugin->options['template_type'] === 'a' ? ' class="pmp-active"' : '').'>'.__('advanced', $this->plugin->text_domain).'</a>'.
					     '      </div>';
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->options['template_type'] === 's') // Simple snippet-based templates.
				{
					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Email Header/Footer Templates', $this->plugin->text_domain).
					     '            <small>'.__('These are used in all emails; i.e., global header/footer.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Header Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__snippet__header_tag___php',
							               'current_value' => $current_value_for('template__type_s__email__snippet__header_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template represents the meat of the email header design. If you would like to rebrand or enhance email messages, this is the file that we suggest you edit. This file contains the <code>&lt;header&gt;</code> tag, which is pulled together into a full, final, and complete HTML document. In other words, there is no reason to use <code>&lt;html&gt;&lt;body&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;header&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;header&gt;</code>; i.e., you can add any HTML that you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[home_url]'          => __('Site home page URL; i.e., back to main site.', $this->plugin->text_domain),
								                                                     '[blog_name_clip]'    => __('A clip of the blog\'s name; as configured in WordPress.', $this->plugin->text_domain),
								                                                     '[current_host_path]' => __('Current <code>domain/path</code> with support for multisite network child blogs.', $this->plugin->text_domain),
								                                                     '[icon_bubbles_url]'  => __('Icon URL; to the plugin\'s icon image.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Header Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Footer Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__snippet__footer_tag___php',
							               'current_value' => $current_value_for('template__type_s__email__snippet__footer_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template allows you to create a custom email footer design. If you would like to rebrand or enhance email messages, this is the file that we suggest you edit. This file contains the <code>&lt;footer&gt;</code> tag, which is pulled together into a full, final, and complete HTML document. In other words, there is no reason to use <code>&lt;/body&gt;&lt;/html&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;footer&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;footer&gt;</code>; i.e., you can add any HTML that you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array()),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Footer Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Email Subscr. Confirmation Templates', $this->plugin->text_domain).
					     '            <small>'.sprintf(__('Email subject line &amp; message used in %1$s&trade; confirmation requests.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Subscr. Confirmation Subject Line Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__sub_confirmation__snippet__subject___php',
							               'current_value' => $current_value_for('template__type_s__email__sub_confirmation__snippet__subject___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this is merely a subject line for email confirmation requests. Customize if you like, but not necessary. Note that extra whitespace in subject templates is stripped automatically at runtime. That\'s why this template is able to break things down into multiple lines. This is for clarity only. In the end, the email will always contain a one-line subject of course. Multiline subjects are unsupported by the vast majority of email clients anyway.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[if sub_comment]'               => __('If the subscription is for a specific comment; i.e., not the entire post.', $this->plugin->text_domain),
								                                                     '[if subscribed_to_own_comment]' => __('If they are subscribing to their own comment.', $this->plugin->text_domain),
								                                                     '[sub_post_title_clip]'          => __('A short clip of the full post title.', $this->plugin->text_domain),
								                                                     '[sub_comment_id]'               => __('Comment ID; if applicable, they are subscribed to.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Subscr. Confirmation Subject', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Subscr. Confirmation Message Body Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__sub_confirmation__snippet__message___php',
							               'current_value' => $current_value_for('template__type_s__email__sub_confirmation__snippet__message___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the body of an email message that is sent to request a subscription confirmation. Note that it is not necessary to create a header/footer for this template. This template pulls together a global email header/footer design that have already been configured elsewhere; i.e., all you need here is the message body. You\'ll notice that the first line of the message body is a link that a user may click to complete confirmation. If you modify this template, it is suggested that you always keep this link at the top of the email. It is (by far) the most important element in this message. End users need a way to confirm their subscription.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[if sub_comment]'               => __('If the subscription is for a specific comment; i.e., not the entire post.', $this->plugin->text_domain),
								                                                     '[if subscribed_to_own_comment]' => __('If they are subscribing to their own comment.', $this->plugin->text_domain),
								                                                     '[if sub_post_comments_open]'    => __('If comments are still open the underlying post they are subscribing to.', $this->plugin->text_domain),
								                                                     '[sub_fname]'                    => __('Subscriber\'s first name.', $this->plugin->text_domain),
								                                                     '[sub_confirm_url]'              => __('Confirmation URL. Clicking this URL will confirm the subscription.', $this->plugin->text_domain),
								                                                     '[sub_post_comments_url]'        => __('URL to comments on the post they\'re subscribed to.', $this->plugin->text_domain),
								                                                     '[sub_post_title_clip]'          => __('A short clip of the full post title.', $this->plugin->text_domain),
								                                                     '[sub_comment_url]'              => __('URL to comment they\'re subscribed to; if applicable.', $this->plugin->text_domain),
								                                                     '[sub_comment_id]'               => __('Comment ID; if applicable, they are subscribed to.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Subscr. Confirmation Message Body', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Comment Notification Email Templates', $this->plugin->text_domain).
					     '            <small>'.sprintf(__('Email subject line &amp; message used in %1$s&trade; notifications.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Subject Line Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__comment_notification__snippet__subject___php',
							               'current_value' => $current_value_for('template__type_s__email__comment_notification__snippet__subject___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this is merely a subject line for email notifications. Customize if you like, but not necessary. Note that extra whitespace in subject templates is stripped automatically at runtime. That\'s why this template is able to break things down into multiple lines. This is for clarity only. In the end, the email will always contain a one-line subject of course. Multiline subjects are unsupported by the vast majority of email clients anyway.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[if is_digest]'                 => __('A notification may contain one (or more) comments. Is this a digest?', $this->plugin->text_domain),
								                                                     '[if sub_comment]'               => __('If the subscription is to a specific comment; i.e., not the entire post.', $this->plugin->text_domain),
								                                                     '[if subscribed_to_own_comment]' => __('Subscribed to their own comment?', $this->plugin->text_domain),
								                                                     '[sub_post_title_clip]'          => __('A short clip of the full post title.', $this->plugin->text_domain),
								                                                     '[sub_comment_id]'               => __('Comment ID; if applicable, they are subscribed to.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Subject', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Message Heading Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__comment_notification__snippet__message_heading___php',
							               'current_value' => $current_value_for('template__type_s__email__comment_notification__snippet__message_heading___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the heading in an email message that is sent to notify an end-user about one or more comments on your blog. You\'ll notice that there are several conditional tags in this template. An email notification can include one (or more) comments; i.e., some subscribers may choose to receive notifications in the form of a digest. This template has the job of dealing with either case; i.e., one comment in the notification, or more than one.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[if is_digest]'                 => __('A notification may contain one (or more) comments. Is this a digest?', $this->plugin->text_domain),
								                                                     '[if sub_comment]'               => __('If the subscription is to a specific comment; i.e., not the entire post.', $this->plugin->text_domain),
								                                                     '[if subscribed_to_own_comment]' => __('Subscribed to their own comment?', $this->plugin->text_domain),
								                                                     '[sub_post_comments_url]'        => __('URL to comments on the post they\'re subscribed to.', $this->plugin->text_domain),
								                                                     '[sub_post_title_clip]'          => __('A short clip of the full post title.', $this->plugin->text_domain),
								                                                     '[sub_comment_url]'              => __('URL to comment they\'re subscribed to; if applicable.', $this->plugin->text_domain),
								                                                     '[sub_comment_id]'               => __('Comment ID; if applicable, they are subscribed to.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Message Heading', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Message In-Response-To Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__comment_notification__snippet__message_in_response_to___php',
							               'current_value' => $current_value_for('template__type_s__email__comment_notification__snippet__message_in_response_to___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the blurp that will appear before a comment that\'s a reply; i.e., not a new comment, but a response to someone. This helps to offer the reader some context when they receive the notification.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[comment_parent_url]'    => __('Parent comment URL.', $this->plugin->text_domain),
								                                                     '[comment_parent_id]'     => __('Parent comment ID.', $this->plugin->text_domain),
								                                                     '[comment_parent_author]' => __('Parent comment author name.', $this->plugin->text_domain),
								                                                     '[comment_parent_clip]'   => __('A shorter clip of the full parent comment message body.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Message In-Response-To', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Message Reply-From Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__comment_notification__snippet__message_reply_from___php',
							               'current_value' => $current_value_for('template__type_s__email__comment_notification__snippet__message_reply_from___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the blurp that will appear for a comment that\'s a reply; i.e., not a new comment, but a response to someone. In the final email, this will come just after the In-Response-To template (as seen above).', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[comment_url]'      => __('URL to comment reply.', $this->plugin->text_domain),
								                                                     '[comment_id]'       => __('Comment reply ID.', $this->plugin->text_domain),
								                                                     '[comment_time_ago]' => __('How long ago the comment reply was posted (human readable).', $this->plugin->text_domain),
								                                                     '[comment_author]'   => __('Comment reply author\'s name.', $this->plugin->text_domain),
								                                                     '[comment_clip]'     => __('A shorter clip of the full comment reply message body.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Message Reply-From', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Message Comment-From Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__email__comment_notification__snippet__message_comment_from___php',
							               'current_value' => $current_value_for('template__type_s__email__comment_notification__snippet__message_comment_from___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the blurp that will appear for a new comment; i.e., one that\'s not a reply, but a new top-level comment.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[comment_url]'      => __('URL to comment.', $this->plugin->text_domain),
								                                                     '[comment_id]'       => __('Comment ID.', $this->plugin->text_domain),
								                                                     '[comment_time_ago]' => __('How long ago the comment was posted (human readable).', $this->plugin->text_domain),
								                                                     '[comment_author]'   => __('Comment author\'s name.', $this->plugin->text_domain),
								                                                     '[comment_clip]'     => __('A shorter clip of the full comment message body.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Message Comment-From', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				else if($this->plugin->is_pro && $this->plugin->options['template_type'] === 'a') // Advanced PHP-based templates.
				{
					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Email Header/Footer Templates', $this->plugin->text_domain).
					     '            <small>'.__('These are used in all emails; i.e., global header/footer.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Header Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__header___php',
							               'current_value' => $current_value_for('template__type_a__email__header___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes the opening <code>&lt;html&gt;&lt;body&gt;</code> tags, and it pulls together a few other components; i.e., the Header Styles, Header Scripts, and Header Tag templates. These other components can be configured separately. For this reason, it is normally not necessary to edit this file. Instead, we suggest editing the "Email Header Tag" template. The choice is yours though.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Header', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Header Styles Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__header_styles___php',
							               'current_value' => $current_value_for('template__type_a__email__header_styles___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template establishes just a few basic styles for email messages. If you modify the default set of email templates, it might be helpful to add a few new styles of your own here. That said, for emails, it is generally a good idea to use inline <code>style=""</code> attributes instead of a stylesheet. For some things it\'s OK, but for the most part, inline styles are better for emails; i.e., they are the most compatible across various email clients.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Header Styles', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Header Scripts Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__header_scripts___php',
							               'current_value' => $current_value_for('template__type_a__email__header_scripts___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template only exists for the sake of being thorough. Using <code>&lt;script&gt;</code> tags in email messages is NOT recommended. They will mostly likely be excluded by popular email clients anyway. For this reason, you will find that this template comes empty by default.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Header Scripts', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Header Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__header_tag___php',
							               'current_value' => $current_value_for('template__type_a__email__header_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template represents the meat of the email header design. If you would like to rebrand or enhance the default template, this is the file that we suggest you edit. This file contains the <code>&lt;header&gt;</code> tag, which is pulled together by the primary Email Header Template to create the full, final, complete HTML header markup. In other words, there is no reason to use <code>&lt;html&gt;&lt;body&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;header&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;header&gt;</code>; i.e., you can add any HTML that you like. As with all templates, you can also use PHP tags if necessary, and even WordPress functionality if you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Header Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					echo '<hr />'; /* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Footer Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__footer_tag___php',
							               'current_value' => $current_value_for('template__type_a__email__footer_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template is by far the easiest way to create a custom email footer design. If you would like to rebrand or enhance the default template, this is the file that we suggest you edit. This file contains the <code>&lt;footer&gt;</code> tag, which is pulled together by the primary Email Footer Template to create the full, final, complete HTML footer markup. In other words, there is no reason to use <code>&lt;/body&gt;&lt;/html&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;footer&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;footer&gt;</code>; i.e., you can add any HTML that you like. As with all templates, you can also use PHP tags if necessary, and even WordPress functionality if you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Footer Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Email Footer Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__footer___php',
							               'current_value' => $current_value_for('template__type_a__email__footer___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template deals with the closing <code>&lt;/body&gt;&lt;/html&gt;</code> tags. It also pulls together a few specific details from your config. options, in order to establish edit/unsubscribe links; along with a mailing address. These are needed for you to remain CAN-SPAM compliant. Please note, it is normally not necessary to edit this file. Instead, we suggest editing the "Email Footer Tag" template. The choice is yours though.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Email Footer', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Email Subscr. Confirmation Templates', $this->plugin->text_domain).
					     '            <small>'.sprintf(__('Email subject line &amp; message used in %1$s&trade; confirmation requests.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Subscr. Confirmation Subject Line Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__sub_confirmation__subject___php',
							               'current_value' => $current_value_for('template__type_a__email__sub_confirmation__subject___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this is merely a subject line for email confirmation requests. Customize if you like, but not necessary. Note that extra whitespace in subject templates is stripped automatically at runtime. That\'s why this template is able to break things down into multiple lines. This is for clarity only. In the end, the email will always contain a one-line subject of course. Multiline subjects are unsupported by the vast majority of email clients anyway.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Subscr. Confirmation Subject', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Subscr. Confirmation Message Body Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__sub_confirmation__message___php',
							               'current_value' => $current_value_for('template__type_a__email__sub_confirmation__message___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the body of an email message that is sent to request a subscription confirmation. Note that it is not necessary to create a header/footer for this template. This template pulls together a global email header/footer design that have already been configured elsewhere; i.e., all you need here is the message body. You\'ll notice that the first line of the message body is a link that a user may click to complete confirmation. If you modify this template, it is suggested that you always keep this link at the top of the email. It is (by far) the most important element in this message. End users need a way to confirm their subscription.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Subscr. Confirmation Message Body', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Comment Notification Email Templates', $this->plugin->text_domain).
					     '            <small>'.sprintf(__('Email subject line &amp; message used in %1$s&trade; notifications.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Subject Line Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__comment_notification__subject___php',
							               'current_value' => $current_value_for('template__type_a__email__comment_notification__subject___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this is merely a subject line for email notifications. Customize if you like, but not necessary. Note that extra whitespace in subject templates is stripped automatically at runtime. That\'s why this template is able to break things down into multiple lines. This is for clarity only. In the end, the email will always contain a one-line subject of course. Multiline subjects are unsupported by the vast majority of email clients anyway.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Subject', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Notification Message Body Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__email__comment_notification__message___php',
							               'current_value' => $current_value_for('template__type_a__email__comment_notification__message___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e., you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the body of an email message that is sent to notify an end-user about one or more comments on your blog. Note that it is not necessary to create a header/footer for this template. This template pulls together a global email header/footer design that have already been configured elsewhere; i.e., all you need here is the message body. You\'ll notice that there are several PHP conditional tags in this template. An email notification can include one (or more) comments; i.e., some subscribers may choose to receive notifications in the form of a digest. This template has the job of dealing with either case; i.e., one comment in the notification, or more than one.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Notification Message Body', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function site_templates_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-site-templates-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-site-templates-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0 && isset($_this->plugin->options[$key]))
					{
						if($_this->plugin->options[$key])
							return $_this->plugin->options[$key];

						$data             = template::option_key_data($key);
						$default_template = new template($data->file, $data->type, TRUE);

						return $default_template->file_contents();
					}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				$shortcode_details = function ($shortcodes) use ($_this)
				{
					$detail_lis = array(); // Initialize.

					foreach($shortcodes as $_shortcode => $_details)
						$detail_lis[] = '<li><code>'.esc_html($_shortcode).'</code>&nbsp;&nbsp;'.$_details.'</li>';
					unset($_shortcode, $_details); // Housekeeping.

					if($detail_lis) // If we have shortcodes, let's list them.
						$details = '<ul class="pmp-list-items" style="margin-top:0; margin-bottom:0;">'.implode('', $detail_lis).'</ul>';
					else $details = __('No shortcodes for this template at the present time.', $_this->plugin->text_domain);

					return '<a href="#" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('shortcodes explained', $_this->plugin->text_domain).'</a>';
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-site-templates '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Site Templates', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				if($this->plugin->is_pro) // Only possible in the pro version.
				{
					echo '      <div class="pmp-template-types pmp-right">'.
					     '         <span>'.__('Template Mode:', $this->plugin->text_domain).'</span>'.
					     '         <a href="'.esc_attr($this->plugin->utils_url->set_template_type('s')).'"'.($this->plugin->options['template_type'] === 's' ? ' class="pmp-active"' : '').'>'.__('simple', $this->plugin->text_domain).'</a>'.
					     '         <a href="'.esc_attr($this->plugin->utils_url->set_template_type('a')).'"'.($this->plugin->options['template_type'] === 'a' ? ' class="pmp-active"' : '').'>'.__('advanced', $this->plugin->text_domain).'</a>'.
					     '      </div>';
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->options['template_type'] === 's') // Simple snippet-based templates.
				{
					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Site Header/Footer Templates', $this->plugin->text_domain).
					     '            <small>'.__('These are used in all portions of the front-end UI; i.e., global header/footer.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Header Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__snippet__header_tag___php',
							               'current_value' => $current_value_for('template__type_s__site__snippet__header_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template represents the meat of the front-end header design. If you would like to rebrand or enhance site templates, this is the file that we suggest you edit. This file contains the <code>&lt;header&gt;</code> tag, which is pulled together into a full, final, and complete HTML document. In other words, there is no reason to use <code>&lt;html&gt;&lt;body&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;header&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;header&gt;</code>; i.e., you can add any HTML that you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[home_url]'          => __('Site home page URL; i.e., back to main site.', $this->plugin->text_domain),
								                                                     '[blog_name_clip]'    => __('A clip of the blog\'s name; as configured in WordPress.', $this->plugin->text_domain),
								                                                     '[current_host_path]' => __('Current <code>host/path</code> with support for multisite network child blogs.', $this->plugin->text_domain),
								                                                     '[icon_bubbles_url]'  => __('Icon URL; to the plugin\'s icon image.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Header Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Footer Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__snippet__footer_tag___php',
							               'current_value' => $current_value_for('template__type_s__site__snippet__footer_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template allows you to create a custom front-end footer design. If you would like to rebrand or enhance site templates, this is the file that we suggest you edit. This file contains the <code>&lt;footer&gt;</code> tag, which is pulled together into a full, final, and complete HTML document. In other words, there is no reason to use <code>&lt;/body&gt;&lt;/html&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;footer&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;footer&gt;</code>; i.e., you can add any HTML that you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[home_url]'                    => __('Site home page URL; i.e., back to main site.', $this->plugin->text_domain),
								                                                     '[blog_name_clip]'              => __('A clip of the blog\'s name; as configured in WordPress.', $this->plugin->text_domain),
								                                                     '[can_spam_privacy_policy_url]' => __('Privacy policy URL; as configured in plugin options via the dashboard.', $this->plugin->text_domain),
								                                                     '[sub_summary_return_url]'      => __('Summary return URL; w/ all summary navigation vars preserved.', $this->plugin->text_domain),
								                                                     '[powered_by]'                  => __('Plugin\'s powered-by link &amp; branding; if enabled in plugin config. options.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Footer Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Subscr. Action Templates', $this->plugin->text_domain).
					     '            <small>'.__('These are shown to a subscriber when they confirm and/or unsubscribe.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Subscr. Confirmed Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__sub_actions__snippet__confirmed___php',
							               'current_value' => $current_value_for('template__type_s__site__sub_actions__snippet__confirmed___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just confirmed their subscription via email (i.e., the page displayed after a user clicks the confirmation link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[if sub_comment]'               => __('If the subscription is to a specific comment; i.e., not the entire post.', $this->plugin->text_domain),
								                                                     '[if subscribed_to_own_comment]' => __('Subscribed to their own comment?', $this->plugin->text_domain),
								                                                     '[sub_fname]'                    => __('Subscriber\'s first name.', $this->plugin->text_domain),
								                                                     '[sub_email]'                    => __('Subscriber\'s email address.', $this->plugin->text_domain),
								                                                     '[sub_deliver_label]'            => __('Delivery option label; e.g., asap, hourly, daily, weekly.', $this->plugin->text_domain),
								                                                     '[sub_deliver_description]'      => __('A brief description of the delivery option.', $this->plugin->text_domain),
								                                                     '[sub_edit_url]'                 => __('Subscription edit URL; i.e., so they can make any last-minute changes.', $this->plugin->text_domain),
								                                                     '[sub_post_comments_url]'        => __('URL to comments on the post they\'re subscribed to.', $this->plugin->text_domain),
								                                                     '[sub_post_title_clip]'          => __('A short clip of the full post title.', $this->plugin->text_domain),
								                                                     '[sub_post_id]'                  => __('The post ID they\'re subscribed to.', $this->plugin->text_domain),
								                                                     '[sub_comment_url]'              => __('URL to comment they\'re subscribed to; if applicable.', $this->plugin->text_domain),
								                                                     '[sub_comment_id]'               => __('Comment ID they\'re subscribed to; if applicable.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Subscr. Confirmed', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Unsubscribed Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__sub_actions__snippet__unsubscribed___php',
							               'current_value' => $current_value_for('template__type_s__site__sub_actions__snippet__unsubscribed___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just unsubscribed from a subscription (i.e., the page displayed after a user clicks an unsubscribe link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[if sub_post]'                  => __('If the post they were subscribed to still exists.', $this->plugin->text_domain),
								                                                     '[if sub_comment]'               => __('If the subscription was to a specific comment; i.e., not the entire post.', $this->plugin->text_domain),
								                                                     '[if subscribed_to_own_comment]' => __('They were subscribed to their own comment?', $this->plugin->text_domain),
								                                                     '[sub_fname]'                    => __('Subscriber\'s first name.', $this->plugin->text_domain),
								                                                     '[sub_email]'                    => __('Subscriber\'s email address.', $this->plugin->text_domain),
								                                                     '[sub_post_comments_url]'        => __('URL to comments on the post they were subscribed to.', $this->plugin->text_domain),
								                                                     '[sub_post_title_clip]'          => __('A short clip of the full post title.', $this->plugin->text_domain),
								                                                     '[sub_post_id]'                  => __('The post ID they were subscribed to.', $this->plugin->text_domain),
								                                                     '[sub_comment_url]'              => __('URL to comment they were subscribed to; if applicable.', $this->plugin->text_domain),
								                                                     '[sub_comment_id]'               => __('Comment ID they were subscribed to; if applicable.', $this->plugin->text_domain),
								                                                     '[sub_unsubscribe_all_url]'      => __('Unsubscribes (deletes) ALL subscriptions associated w/ their email address.', $this->plugin->text_domain),
								                                                     '[sub_new_url]'                  => __('Subscription creation URL; i.e., so they can add a new subscription if they like.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Unsubscribed', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Unsubscribed All Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__sub_actions__snippet__unsubscribed_all___php',
							               'current_value' => $current_value_for('template__type_s__site__sub_actions__snippet__unsubscribed_all___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just unsubscribed from all of their subscriptions (i.e., the page displayed after a user clicks the "unsubscribe all" link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[sub_email]'   => __('Subscriber\'s email address.', $this->plugin->text_domain),
								                                                     '[sub_new_url]' => __('Subscription creation URL; i.e., so they can add a new subscription if they like.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Unsubscribed All', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Subscription Option Templates', $this->plugin->text_domain).
					     '            <small>'.__('Provides options that allow commenters to subscribe &amp; receive notifications.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__comment_form__snippet__sub_ops___php',
							               'current_value' => $current_value_for('template__type_s__site__comment_form__snippet__sub_ops___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the HTML snippet that is displayed below your comment form; providing end-users with a way to create a subscription.', $this->plugin->text_domain).
							                                  ' '.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_field_comment/', 'comment_form_field_comment'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form/', 'comment_form')).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     '[css_styles]'          => __('Stylesheet containing a default set of structral styles.', $this->plugin->text_domain),
								                                                     '[inline_icon_svg]'     => __('Inline SVG icon that inherits the color and width of it\'s container automatically. Note, this is a scalable vector graphic that will look great at any size &gt;= 16x16 pixels.', $this->plugin->text_domain),
								                                                     '[sub_type_options]'    => __('Select menu options. Allows a subscriber to choose if they wan\'t to subscribe or not; and in which way.', $this->plugin->text_domain),
								                                                     '[sub_deliver_options]' => __('Select menu options. Allows a subscriber to choose a delivery option; e.g., asap, hourly, daily, weeky. This can be excluded if you wish. A default value of <code>asap</code> will be used in that case.', $this->plugin->text_domain),
								                                                     '[sub_type_id]'         => __('The <code>id=""</code> attribute value used in <code>[sub_type_options]</code>.', $this->plugin->text_domain),
								                                                     '[current_sub_email]'   => __('The current subscriber\'s email address, if it is known to have been confirmed; i.e., if it really is their email address. This will be empty if they have not previously confirmed a subscription.', $this->plugin->text_domain),
								                                                     '[sub_new_url]'         => __('A URL leading to the "Add Subscription" page. This allows a visitor to subscribe w/o commenting even.', $this->plugin->text_domain),
								                                                     '[sub_summary_url]'     => __('A URL leading to the subscription summary page (i.e., the My Subscriptions page). A link to the summary page (i.e., the My Subscriptions page) should only be displayed <code>[if current_sub_email]</code> is known.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Form Subscr. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					if($this->plugin->is_pro || $this->plugin->utils_env->is_pro_preview())
					{
						echo '         <h2 class="pmp-section-heading">'.
						     '            '.__('Single Sign-on Templates', $this->plugin->text_domain).
						     '            <small>'.__('Provides options that allow commenters to login w/ popular social network accounts.', $this->plugin->text_domain).'</small>'.
						     '         </h2>';

						/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

						$_panel_body = '<table>'.
						               '  <tbody>'.
						               $form_fields->textarea_row(
							               array(
								               'label'         => __('Comment Form SSO Options Template', $this->plugin->text_domain),
								               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
								               'cm_mode'       => 'text/html',
								               'name'          => 'template__type_s__site__comment_form__snippet__sso_ops___php',
								               'current_value' => $current_value_for('template__type_s__site__comment_form__snippet__sso_ops___php'),
								               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
								                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the HTML snippet that is displayed above your comment form; providing end-users with a way to login with a popular social network account. This will only be applicable if you have Single Sign-on (SSO) enabled in your config. options.', $this->plugin->text_domain).
								                                  ' '.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_must_log_in_after/', 'comment_form_must_log_in_after'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_top/', 'comment_form_top')).'</p>',
								               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
								               'cm_details'    => $shortcode_details(array(
									                                                     '[css_styles]'    => __('Stylesheet containing a default set of structral styles.', $this->plugin->text_domain),
									                                                     '[service_links]' => __('Links/icons for the SSO services that you have integrated with.', $this->plugin->text_domain),
								                                                     )),
							               )).
						               '  </tbody>'.
						               '</table>';

						echo $this->panel(__('Comment Form SSO Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

						unset($_panel_body); // Housekeeping.

						/* ----------------------------------------------------------------------------------------- */

						$_panel_body = '<table>'.
						               '  <tbody>'.
						               $form_fields->textarea_row(
							               array(
								               'label'         => __('Login Form SSO Options Template', $this->plugin->text_domain),
								               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
								               'cm_mode'       => 'text/html',
								               'name'          => 'template__type_s__site__login_form__snippet__sso_ops___php',
								               'current_value' => $current_value_for('template__type_s__site__login_form__snippet__sso_ops___php'),
								               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
								                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the HTML snippet that is displayed within your login form; providing end-users with a way to login with a popular social network account. This will only be applicable if you have Single Sign-on (SSO) enabled in your config. options.', $this->plugin->text_domain).
								                                  ' '.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your login form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_form/', 'login_form'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_footer/', 'login_footer')).'</p>',
								               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
								               'cm_details'    => $shortcode_details(array(
									                                                     '[css_styles]'    => __('Stylesheet containing a default set of structral styles.', $this->plugin->text_domain),
									                                                     '[service_links]' => __('Links/icons for the SSO services that you have integrated with.', $this->plugin->text_domain),
								                                                     )),
							               )).
						               '  </tbody>'.
						               '</table>';

						echo $this->panel(__('Login Form SSO Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

						unset($_panel_body); // Housekeeping.
					}
				}
				/* ----------------------------------------------------------------------------------------- */

				else if($this->plugin->is_pro && $this->plugin->options['template_type'] === 'a') // Advanced PHP-based templates.
				{
					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Site Header/Footer Templates', $this->plugin->text_domain).
					     '            <small>'.__('These are used in all portions of the front-end UI; i.e., global header/footer.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Header Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__header___php',
							               'current_value' => $current_value_for('template__type_a__site__header___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes the opening <code>&lt;html&gt;&lt;body&gt;</code> tags, and it pulls together a few other components; i.e., the Header Styles, Header Scripts, and Header Tag templates. These other components can be configured separately. For this reason, it is normally not necessary to edit this file. Instead, we suggest editing the "Site Header Tag" template. The choice is yours though.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Header', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Header Styles Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__header_styles___php',
							               'current_value' => $current_value_for('template__type_a__site__header_styles___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Tip:</strong> this template establishes just a few basic styles needed by other front-end templates listed on this page. If you modify the default set of templates, it might be helpful to add a few new styles of your own here. That said, this software uses the %1$s. Therefore, you already have a full set of mobile-first design functionality available to you, even before you add styles of your own.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://getbootstrap.com/css/', __('Bootstrap CSS/JS libraries', $this->plugin->text_domain))).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Header Styles', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Header Scripts Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__header_scripts___php',
							               'current_value' => $current_value_for('template__type_a__site__header_scripts___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Tip:</strong> this template establishes just a few basic JavaScript libraries needed by other front-end templates listed on this page. If you modify the default set of templates, it might be helpful to add a few new scripts of your own here. That said, this software uses the %1$s. Therefore, you already have a full set of mobile-first design functionality available to you, even before you add scripts of your own.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://getbootstrap.com/css/', __('Bootstrap CSS/JS libraries', $this->plugin->text_domain))).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Header Scripts', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Header Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__header_tag___php',
							               'current_value' => $current_value_for('template__type_a__site__header_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template represents the meat of the front-end header design. If you would like to rebrand or enhance the default template, this is the file that we suggest you edit. This file contains the <code>&lt;header&gt;</code> tag, which is pulled together by the primary Site Header Template to create the full, final, complete HTML header markup. In other words, there is no reason to use <code>&lt;html&gt;&lt;body&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;header&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;header&gt;</code>; i.e., you can add any HTML that you like. As with all templates, you can also use PHP tags if necessary, and even WordPress functionality if you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Header Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					echo '<hr />'; /* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Footer Tag Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__footer_tag___php',
							               'current_value' => $current_value_for('template__type_a__site__footer_tag___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template is by far the easiest way to create a custom front-end footer design. If you would like to rebrand or enhance the default template, this is the file that we suggest you edit. This file contains the <code>&lt;footer&gt;</code> tag, which is pulled together by the primary Site Footer Template to create the full, final, complete HTML footer markup. In other words, there is no reason to use <code>&lt;/body&gt;&lt;/html&gt;</code> tags here, they are produced elsewhere. Please note, while this template is focused on the <code>&lt;footer&gt;</code> tag, you are not limited to <em>just</em> the <code>&lt;footer&gt;</code>; i.e., you can add any HTML that you like. As with all templates, you can also use PHP tags if necessary, and even WordPress functionality if you like.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Footer Tag', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Site Footer Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__footer___php',
							               'current_value' => $current_value_for('template__type_a__site__footer___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e., you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template deals with the closing <code>&lt;/body&gt;&lt;/html&gt;</code> tags. It is normally not necessary to edit this file. Instead, we suggest editing the "Site Footer Tag" template. The choice is yours though.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Site Footer', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Subscr. Action Templates', $this->plugin->text_domain).
					     '            <small>'.__('These are shown to a subscriber when they confirm and/or unsubscribe.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Subscr. Confirmed Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sub_actions__confirmed___php',
							               'current_value' => $current_value_for('template__type_a__site__sub_actions__confirmed___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just confirmed their subscription via email (i.e., the page displayed after a user clicks the confirmation link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Subscr. Confirmed', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Unsubscribed Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sub_actions__unsubscribed___php',
							               'current_value' => $current_value_for('template__type_a__site__sub_actions__unsubscribed___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just unsubscribed from a subscription (i.e., the page displayed after a user clicks an unsubscribe link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Unsubscribed', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Unsubscribed All Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sub_actions__unsubscribed_all___php',
							               'current_value' => $current_value_for('template__type_a__site__sub_actions__unsubscribed_all___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just unsubscribed from all of their subscriptions (i.e., the page displayed after a user clicks the "unsubscribe all" link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Unsubscribed All', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Subscr. Summary Templates', $this->plugin->text_domain).
					     '            <small>'.__('Related to the Summary (aka: "My Subscriptions") page and add/edit form.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Summary Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sub_actions__manage_summary___php',
							               'current_value' => $current_value_for('template__type_a__site__sub_actions__manage_summary___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who is managing their subscriptions (i.e., the page displayed after a user clicks the "manage my subscriptions" link). Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Summary', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Add/Edit Form Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sub_actions__manage_sub_form___php',
							               'current_value' => $current_value_for('template__type_a__site__sub_actions__manage_sub_form___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who would like to add a new subscription (i.e., the page displayed after a user clicks the "add subscription" or "subscribe without commenting" link). This same template also contains the form that a subscriber may use to edit an existing subscription; i.e., it must be able to deal with both scenarios. Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Add/Edit Form', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Add/Edit Form Template (Comment ID Row via AJAX)', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sub_actions__manage_sub_form_comment_id_row_via_ajax___php',
							               'current_value' => $current_value_for('template__type_a__site__sub_actions__manage_sub_form_comment_id_row_via_ajax___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template contains content delivered via AJAX. When a subscriber is using the Add/Edit Form Template, and they choose a particular Post ID from a list of options, the comments for the post ID they select will be collected and displayed automagically for them. This table row will contain a list of those comments for the post ID they selected. If you edit this, please make sure that this template only contains a table row, and nothing more. The underlying JavaScript/AJAX routines will always expect this template to produce a single <code>&lt;tr&gt;</code> tag with a list of select menu options.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment ID Row via AJAX', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Subscription Option Templates', $this->plugin->text_domain).
					     '            <small>'.__('Provides options that allow commenters to subscribe &amp; receive notifications.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__comment_form__sub_ops___php',
							               'current_value' => $current_value_for('template__type_a__site__comment_form__sub_ops___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the HTML snippet that is displayed below your comment form; providing end-users with a way to create a subscription.', $this->plugin->text_domain).
							                                  ' '.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_field_comment/', 'comment_form_field_comment'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form/', 'comment_form')).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Form Subscr. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Form Scripts for Subscr. Options', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__comment_form__sub_op_scripts___php',
							               'current_value' => $current_value_for('template__type_a__site__comment_form__sub_op_scripts___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template contains just a few lines of JavaScript needed by the default Comment Form Subscr. Options Template. Customize if you like, but not necessary.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Form Scripts for Subscr. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Single Sign-on Templates', $this->plugin->text_domain).
					     '            <small>'.__('Provides options that allow commenters to login w/ popular social network accounts.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Form SSO Options Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__comment_form__sso_ops___php',
							               'current_value' => $current_value_for('template__type_a__site__comment_form__sso_ops___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the HTML snippet that is displayed above your comment form; providing end-users with a way to login with a popular social network account. This will only be applicable if you have Single Sign-on (SSO) enabled in your config. options.', $this->plugin->text_domain).
							                                  ' '.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_must_log_in_after/', 'comment_form_must_log_in_after'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_top/', 'comment_form_top')).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Form SSO Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Comment Form Scripts for SSO Options', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__comment_form__sso_op_scripts___php',
							               'current_value' => $current_value_for('template__type_a__site__comment_form__sso_op_scripts___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template contains just a few lines of JavaScript needed by the default Comment Form SSO Options Template. Customize if you like, but not necessary.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Comment Form Scripts for SSO Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					echo '<hr />'; /* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Login Form SSO Options Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__login_form__sso_ops___php',
							               'current_value' => $current_value_for('template__type_a__site__login_form__sso_ops___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the HTML snippet that is displayed within your login form; providing end-users with a way to login with a popular social network account. This will only be applicable if you have Single Sign-on (SSO) enabled in your config. options.', $this->plugin->text_domain).
							                                  ' '.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook as a fallback. This is how the template is integrated into your login form automatically. If both of these hooks are missing from your WP theme (e.g., SSO options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_form/', 'login_form'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/login_footer/', 'login_footer')).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Login Form SSO Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Login Form Scripts for SSO Options', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__login_form__sso_op_scripts___php',
							               'current_value' => $current_value_for('template__type_a__site__login_form__sso_op_scripts___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this template contains just a few lines of JavaScript needed by the default Login Form SSO Options Template. Customize if you like, but not necessary.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Login Form Scripts for SSO Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.

					echo '<hr />'; /* ----------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('SSO Registration Completion Template', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__sso_actions__complete___php',
							               'current_value' => $current_value_for('template__type_a__site__sso_actions__complete___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e., you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template allows you to customize the content of the page that is displayed to a user who has just logged-in through an SSO service provider for the first time. This is only applicable if you have Single Sign-on (SSO) enabled in your config. options. Also, this particular page is only displayed when there is information missing and/or considered private by the SSO service provider. For instance, Twitter will not share a user\'s email address through any of their APIs (i.e., there is no way to collect the email address behind-the-scenes when it comes to Twitter). Therefore, this template exists as a way for your site to collect that last bit of information before you allow them to log in for the first time. Note that it is not necessary to create a header/footer for this template. This template pulls together a global front-end header/footer design that have already been configured elsewhere; i.e., all you need here is the content.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Single Sign-on Registration Completion', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'pro_only' => TRUE));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function subs_()
			{
				switch(!empty($_REQUEST['action']) ? $_REQUEST['action'] : '')
				{
					case 'new': // Add new subscription.

						$this->_sub_new(); // Display form.

						break; // Break switch handler.

					case 'edit': // Edit existing subscription.

						$this->_sub_edit(); // Display form.

						break; // Break switch handler.

					case '': // Also the default case handler.
					default: // Everything else is handled by subs. table.

						echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-subs '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
						echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

						echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug).'"></i>'.
						     '       <a href="'.esc_attr($this->plugin->utils_url->new_sub_short()).'" class="add-new-h2">'.__('Add New', $this->plugin->text_domain).'</a></h2>'."\n";

						new menu_page_subs_table(); // Displays table.

						echo '   </form>';
						echo '</div>'."\n";
				}
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function _sub_new()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-new '.$this->plugin->slug.'-menu-page-form '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only(array('action'))).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; New Subscription', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i></h2>'."\n";

				new menu_page_sub_new_form(); // Displays form to add new subscription.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function _sub_edit()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-edit '.$this->plugin->slug.'-menu-page-form '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only(array('action', 'subscription'))).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Edit Subscription', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i></h2>'."\n";

				new menu_page_sub_edit_form(!empty($_REQUEST['subscription']) ? (integer)$_REQUEST['subscription'] : 0); // Displays form.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function sub_event_log_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-event-log '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Subscriptions &raquo; Event Log', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-history"></i></h2>'."\n";

				new menu_page_sub_event_log_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function queue_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-queue '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Queued (Pending) Notifications', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-envelope-o"></i></h2>'."\n";

				new menu_page_queue_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function queue_event_log_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-queue-event-log '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Queue &raquo; Event Log', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-paper-plane"></i></h2>'."\n";

				new menu_page_queue_event_log_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function stats_()
			{
				if(!$this->plugin->is_pro)
					return ''; // Pro only.

				$_this                = $this;
				$timezone             = $this->plugin->utils_date->i18n('T');
				$current_value_for    = function ($key) use ($_this)
				{
					return isset($_REQUEST[__NAMESPACE__]['stats'][$key])
						? trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['stats'][$key])) : NULL;
				};
				$current_postbox_view = $current_value_for('view'); // Current statistical view.

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-stats '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";

				echo '   <h2>'.sprintf(__('%1$s&trade; &raquo; Statistics/Charts', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-bar-chart"></i></h2>'."\n";

				echo '   <div class="pmp-postbox-container postbox-container">'.
				     '      <div class="pmp-postbox-holder metabox-holder">';

				/* ----------------------------------------------------------------------------------------- */

				$date_info        = // For use in JavaScript alerts (as seen below).
					__('You can type (or select) a particular date/time. Upon clicking the input field a date picker will open for you.', $this->plugin->text_domain)."\n\n".
					__('TIP: you can also type things like: now, 30 days ago, -30 days, -2 weeks, and more. Anything compatible with PHP\'s strtotime() function will work here.', $this->plugin->text_domain)."\n\n".
					__('As expected, relative dates like: -30 days; are based on your current local time when used in the From Date; i.e., your current local time -30 days.', $this->plugin->text_domain)."\n\n".
					__('However, relative dates used in the To Date are slightly different. With the exception of the phrase "now", relative To Date phrases are relative to the From Date you\'ve given.', $this->plugin->text_domain)."\n\n".
					__('Typing (or selecting) a specific date in either field will behave as expected; i.e., you get data from (or to) that specific date. Only relative dates (i.e., phrases) are impacted by the above.', $this->plugin->text_domain);
				$date_info_anchor = '<a href="#" onclick="alert(\''.esc_attr($this->plugin->utils_string->esc_js_sq($date_info)).'\'); return false;" style="text-decoration:none;">'.__('[?]', $this->plugin->text_domain).'</a>';

				/* ----------------------------------------------------------------------------------------- */

				echo '<hr style="margin-top:0;" />'; // Dividing line before each view category.

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_postbox_view = 'subs_overview'; // This statistical view.

				$_form_field_args = array(
					'ns_id_suffix'   => '-stats-form-'.str_replace('_', '-', $_postbox_view),
					'ns_name_suffix' => '[stats_chart_data_via_ajax]',
					'class_prefix'   => 'pmp-stats-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_postbox_chart_type_options = array(
					'@optgroup_open_subscr_totals'                  => __('Subscr. Totals', $this->plugin->text_domain),
					'event_subscribed_totals'                       => __('Total Subscriptions', $this->plugin->text_domain),
					'@optgroup_close_subscr_totals'                 => '', // Close this group.

					'@optgroup_open_subscr_totals_post_popularity'  => __('Post Popularity', $this->plugin->text_domain),
					'event_subscribed_most_popular_posts'           => __('Most Popular Posts', $this->plugin->text_domain),
					'event_subscribed_least_popular_posts'          => __('Least Popular Posts', $this->plugin->text_domain),
					'@optgroup_close_subscr_totals_post_popularity' => '', // Close this group.

					'@optgroup_open_subscr_totals_geo_popularity'   => __('Geographic Popularity', $this->plugin->text_domain),
					'event_subscribed_audience_by_geo_country'      => __('Audience by Country', $this->plugin->text_domain),
					'event_subscribed_audience_by_geo_us_region'    => __('Audience by US Region', $this->plugin->text_domain),
					'event_subscribed_audience_by_geo_ca_region'    => __('Audience by CA Region', $this->plugin->text_domain),
					'@optgroup_close_subscr_totals_geo_popularity'  => '', // Close this group.

					'@optgroup_open_status_change_percentages'      => __('Status Change Percentages', $this->plugin->text_domain),
					'event_confirmation_percentages'                => __('Confirmation Percentages', $this->plugin->text_domain),
					'event_suspension_percentages'                  => __('Suspension Percentages', $this->plugin->text_domain),
					'event_unsubscribe_percentages'                 => __('Unsubscribe Percentages', $this->plugin->text_domain),
					'@optgroup_close_status_change_percentages'     => '', // Close this group.
				);
				if(!$this->plugin->options['geo_location_tracking_enable'])
					unset($_postbox_chart_type_options['@optgroup_open_subscr_totals_geo_popularity'],
						$_postbox_chart_type_options['event_subscribed_audience_by_geo_country'],
						$_postbox_chart_type_options['event_subscribed_audience_by_geo_us_region'],
						$_postbox_chart_type_options['event_subscribed_audience_by_geo_ca_region'],
						$_postbox_chart_type_options['@optgroup_close_subscr_totals_geo_popularity']);

				$_postbox_body = $this->stats_view(
					$_postbox_view,
					array(
						array('hidden_input' => $_form_fields->hidden_input(
							array(
								'name'          => 'view',
								'current_value' => $_postbox_view,
							))),
						$_form_fields->select_row(
							array(
								'label'           => __('Chart Type', $this->plugin->text_domain),
								'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
								'name'            => 'type',
								'current_value'   => $this->coalesce($current_value_for('type'), 'event_confirmation_percentages'),
								'allow_arbitrary' => FALSE,
								'options'         => $_postbox_chart_type_options,
							)),
						array('row'      => $_form_fields->select_row(
							array(
								'label'           => __('Exclude', $this->plugin->text_domain),
								'placeholder'     => __('One or More...', $this->plugin->text_domain),
								'name'            => 'exclude',
								'current_value'   => $this->coalesce($current_value_for('exclude'), NULL),
								'other_attrs'     => 'multiple="multiple"',
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'systematics' => __('Systematics (i.e., Show User-Initiated Events Only)', $this->plugin->text_domain),
								),
							)), 'colspan' => 3, 'ends_row' => TRUE),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('From Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., 7 days ago; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y', strtotime('-7 days')))),
								'name'          => 'from',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('from'), '7 days ago'),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('To Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., now; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y'))),
								'name'          => 'to',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('to'), 'now'),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Breakdown By', $this->plugin->text_domain),
								'placeholder'     => __('e.g., hours, days, weeks, months, years', $this->plugin->text_domain),
								'name'            => 'by',
								'current_value'   => $this->coalesce($current_value_for('by'), 'days'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'hours'  => __('hours', $this->plugin->text_domain),
									'days'   => __('days', $this->plugin->text_domain),
									'weeks'  => __('weeks', $this->plugin->text_domain),
									'months' => __('months', $this->plugin->text_domain),
									'years'  => __('years', $this->plugin->text_domain),
								),
							)),
					),
					array('auto_chart' => $current_postbox_view === $_postbox_view));

				echo $this->postbox(__('Subscriptions Overview', $this->plugin->text_domain), $_postbox_body,
				                    array('icon' => '<i class="fa fa-bar-chart"></i>', 'open' => !$current_postbox_view || $current_postbox_view === $_postbox_view));

				unset($_postbox_view, $_postbox_chart_type_options, $_postbox_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_postbox_view = 'subs_overview_by_post_id'; // This statistical view.

				$_form_field_args = array(
					'ns_id_suffix'   => '-stats-form-'.str_replace('_', '-', $_postbox_view),
					'ns_name_suffix' => '[stats_chart_data_via_ajax]',
					'class_prefix'   => 'pmp-stats-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_postbox_chart_type_options = array(
					'@optgroup_open_subscr_totals'                 => __('Subscr. Totals', $this->plugin->text_domain),
					'event_subscribed_totals'                      => __('Total Subscriptions (for Post ID)', $this->plugin->text_domain),
					'@optgroup_close_subscr_totals'                => '', // Close this group.

					'@optgroup_open_subscr_totals_geo_popularity'  => __('Geographic Popularity', $this->plugin->text_domain),
					'event_subscribed_audience_by_geo_country'     => __('Audience by Country (for Post ID)', $this->plugin->text_domain),
					'event_subscribed_audience_by_geo_us_region'   => __('Audience by US Region (for Post ID)', $this->plugin->text_domain),
					'event_subscribed_audience_by_geo_ca_region'   => __('Audience by CA Region (for Post ID)', $this->plugin->text_domain),
					'@optgroup_close_subscr_totals_geo_popularity' => '', // Close this group.

					'@optgroup_open_status_change_percentages'     => __('Status Change Percentages', $this->plugin->text_domain),
					'event_confirmation_percentages'               => __('Confirmation Percentages (for Post ID)', $this->plugin->text_domain),
					'event_suspension_percentages'                 => __('Suspension Percentages (for Post ID)', $this->plugin->text_domain),
					'event_unsubscribe_percentages'                => __('Unsubscribe Percentages (for Post ID)', $this->plugin->text_domain),
					'@optgroup_close_status_change_percentages'    => '', // Close this group.
				);
				if(!$this->plugin->options['geo_location_tracking_enable'])
					unset($_postbox_chart_type_options['@optgroup_open_subscr_totals_geo_popularity'],
						$_postbox_chart_type_options['event_subscribed_audience_by_geo_country'],
						$_postbox_chart_type_options['event_subscribed_audience_by_geo_us_region'],
						$_postbox_chart_type_options['event_subscribed_audience_by_geo_ca_region'],
						$_postbox_chart_type_options['@optgroup_close_subscr_totals_geo_popularity']);

				$_postbox_body = $this->stats_view(
					$_postbox_view,
					array(
						array('hidden_input' => $_form_fields->hidden_input(
							array(
								'name'          => 'view',
								'current_value' => $_postbox_view,
							))),
						$_form_fields->select_row(
							array(
								'label'           => __('Chart Type', $this->plugin->text_domain),
								'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
								'name'            => 'type',
								'current_value'   => $this->coalesce($current_value_for('type'), 'event_confirmation_percentages'),
								'allow_arbitrary' => FALSE,
								'options'         => $_postbox_chart_type_options,
							)),
						$_form_fields->select_row(
							array(
								'label'               => __('Post ID', $this->plugin->text_domain),
								'placeholder'         => __('Select an Option...', $this->plugin->text_domain),
								'name'                => 'post_id',
								'current_value'       => $this->coalesce($current_value_for('post_id'), NULL),
								'options'             => '%%posts%%',
								'input_fallback_args' => array(
									'type'                     => 'number',
									'placeholder'              => '',
									'maxlength'                => 20,
									'current_value_empty_on_0' => TRUE,
									'other_attrs'              => 'min="1" max="18446744073709551615"',
								),
							)),
						array('row'      => $_form_fields->select_row(
							array(
								'label'           => __('Exclude', $this->plugin->text_domain),
								'placeholder'     => __('One or More...', $this->plugin->text_domain),
								'name'            => 'exclude',
								'current_value'   => $this->coalesce($current_value_for('exclude'), NULL),
								'other_attrs'     => 'multiple="multiple"',
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'systematics' => __('Systematics (i.e., Show User-Initiated Events Only)', $this->plugin->text_domain),
								),
							)), 'colspan' => 2, 'ends_row' => TRUE),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('From Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., 7 days ago; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y', strtotime('-7 days')))),
								'name'          => 'from',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('from'), '7 days ago'),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('To Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., now; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y'))),
								'name'          => 'to',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('to'), 'now'),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Breakdown By', $this->plugin->text_domain),
								'placeholder'     => __('e.g., hours, days, weeks, months, years', $this->plugin->text_domain),
								'name'            => 'by',
								'current_value'   => $this->coalesce($current_value_for('by'), 'days'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'hours'  => __('hours', $this->plugin->text_domain),
									'days'   => __('days', $this->plugin->text_domain),
									'weeks'  => __('weeks', $this->plugin->text_domain),
									'months' => __('months', $this->plugin->text_domain),
									'years'  => __('years', $this->plugin->text_domain),
								),
							)),
					),
					array('auto_chart' => $current_postbox_view === $_postbox_view));

				echo $this->postbox(__('Subscriptions by Post ID', $this->plugin->text_domain), $_postbox_body,
				                    array('icon' => '<i class="fa fa-bar-chart"></i>', 'open' => !$current_postbox_view || $current_postbox_view === $_postbox_view));

				unset($_postbox_view, $_postbox_chart_type_options, $_postbox_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '<hr />'; // Dividing line before each view category.

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_postbox_view = 'queued_notifications_overview'; // This statistical view.

				$_form_field_args = array(
					'ns_id_suffix'   => '-stats-form-'.str_replace('_', '-', $_postbox_view),
					'ns_name_suffix' => '[stats_chart_data_via_ajax]',
					'class_prefix'   => 'pmp-stats-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_postbox_chart_type_options = array(
					'@optgroup_open_queued_notification_totals'          => __('Processed Notification Totals', $this->plugin->text_domain),
					'event_processed_totals'                             => __('Total Processed Notifications', $this->plugin->text_domain),
					'@optgroup_close_queued_notification_totals'         => '', // Close this group.

					'@optgroup_open_processed_notification_percentages'  => __('Processed Notification Percentages', $this->plugin->text_domain),
					'event_processed_percentages'                        => __('Processed Percentages', $this->plugin->text_domain),
					'event_notified_percentages'                         => __('Notified Percentages', $this->plugin->text_domain),
					'event_invalidated_percentages'                      => __('Invalidated Percentages', $this->plugin->text_domain),
					'@optgroup_close_processed_notification_percentages' => '', // Close this group.
				);
				$_postbox_body               = $this->stats_view(
					$_postbox_view,
					array(
						array('hidden_input' => $_form_fields->hidden_input(
							array(
								'name'          => 'view',
								'current_value' => $_postbox_view,
							))),
						array('row'      => $_form_fields->select_row(
							array(
								'label'           => __('Chart Type', $this->plugin->text_domain),
								'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
								'name'            => 'type',
								'current_value'   => $this->coalesce($current_value_for('type'), 'event_queued_notification_processed_percentages'),
								'allow_arbitrary' => FALSE,
								'options'         => $_postbox_chart_type_options,
							)), 'colspan' => 4, 'ends_row' => TRUE),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('From Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., 7 days ago; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y', strtotime('-7 days')))),
								'name'          => 'from',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('from'), '7 days ago'),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('To Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., now; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y'))),
								'name'          => 'to',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('to'), 'now'),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Breakdown By', $this->plugin->text_domain),
								'placeholder'     => __('e.g., hours, days, weeks, months, years', $this->plugin->text_domain),
								'name'            => 'by',
								'current_value'   => $this->coalesce($current_value_for('by'), 'days'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'hours'  => __('hours', $this->plugin->text_domain),
									'days'   => __('days', $this->plugin->text_domain),
									'weeks'  => __('weeks', $this->plugin->text_domain),
									'months' => __('months', $this->plugin->text_domain),
									'years'  => __('years', $this->plugin->text_domain),
								),
							)),
					),
					array('auto_chart' => $current_postbox_view === $_postbox_view));

				echo $this->postbox(__('Queued Notifications Overview', $this->plugin->text_domain), $_postbox_body,
				                    array('icon' => '<i class="fa fa-bar-chart"></i>', 'open' => !$current_postbox_view || $current_postbox_view === $_postbox_view));

				unset($_postbox_view, $_postbox_chart_type_options, $_postbox_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_postbox_view = 'queued_notifications_overview_by_post_id'; // This statistical view.

				$_form_field_args = array(
					'ns_id_suffix'   => '-stats-form-'.str_replace('_', '-', $_postbox_view),
					'ns_name_suffix' => '[stats_chart_data_via_ajax]',
					'class_prefix'   => 'pmp-stats-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_postbox_chart_type_options = array(
					'@optgroup_open_queued_notification_totals'          => __('Processed Notification Totals', $this->plugin->text_domain),
					'event_processed_totals'                             => __('Total Processed Notifications (for Post ID)', $this->plugin->text_domain),
					'@optgroup_close_queued_notification_totals'         => '', // Close this group.

					'@optgroup_open_processed_notification_percentages'  => __('Processed Notification Percentages', $this->plugin->text_domain),
					'event_processed_percentages'                        => __('Processed Percentages (for Post ID)', $this->plugin->text_domain),
					'event_notified_percentages'                         => __('Notified Percentages (for Post ID)', $this->plugin->text_domain),
					'event_invalidated_percentages'                      => __('Invalidated Percentages (for Post ID)', $this->plugin->text_domain),
					'@optgroup_close_processed_notification_percentages' => '', // Close this group.
				);
				$_postbox_body               = $this->stats_view(
					$_postbox_view,
					array(
						array('hidden_input' => $_form_fields->hidden_input(
							array(
								'name'          => 'view',
								'current_value' => $_postbox_view,
							))),
						$_form_fields->select_row(
							array(
								'label'           => __('Chart Type', $this->plugin->text_domain),
								'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
								'name'            => 'type',
								'current_value'   => $this->coalesce($current_value_for('type'), 'event_queued_notification_processed_percentages'),
								'allow_arbitrary' => FALSE,
								'options'         => $_postbox_chart_type_options,
							)),
						array('row'      => $_form_fields->select_row(
							array(
								'label'               => __('Post ID', $this->plugin->text_domain),
								'placeholder'         => __('Select an Option...', $this->plugin->text_domain),
								'name'                => 'post_id',
								'current_value'       => $this->coalesce($current_value_for('post_id'), NULL),
								'options'             => '%%posts%%',
								'input_fallback_args' => array(
									'type'                     => 'number',
									'placeholder'              => '',
									'maxlength'                => 20,
									'current_value_empty_on_0' => TRUE,
									'other_attrs'              => 'min="1" max="18446744073709551615"',
								),
							)), 'colspan' => 3, 'ends_row' => TRUE),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('From Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., 7 days ago; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y', strtotime('-7 days')))),
								'name'          => 'from',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('from'), '7 days ago'),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('To Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g., now; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y'))),
								'name'          => 'to',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('to'), 'now'),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Breakdown By', $this->plugin->text_domain),
								'placeholder'     => __('e.g., hours, days, weeks, months, years', $this->plugin->text_domain),
								'name'            => 'by',
								'current_value'   => $this->coalesce($current_value_for('by'), 'days'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'hours'  => __('hours', $this->plugin->text_domain),
									'days'   => __('days', $this->plugin->text_domain),
									'weeks'  => __('weeks', $this->plugin->text_domain),
									'months' => __('months', $this->plugin->text_domain),
									'years'  => __('years', $this->plugin->text_domain),
								),
							)),
					),
					array('auto_chart' => $current_postbox_view === $_postbox_view));

				echo $this->postbox(__('Queued Notifications by Post ID', $this->plugin->text_domain), $_postbox_body,
				                    array('icon' => '<i class="fa fa-bar-chart"></i>', 'open' => !$current_postbox_view || $current_postbox_view === $_postbox_view));

				unset($_postbox_view, $_postbox_chart_type_options, $_postbox_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '      </div>'.
				     '   </div>';

				echo '</div>';

				return NULL;
			}

			/**
			 * Displays pro updater.
			 *
			 * @since 141111 First documented version.
			 */
			protected function pro_updater_()
			{
				if(!$this->plugin->is_pro)
					return ''; // Pro only.

				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-pro-updater-form',
					'ns_name_suffix' => '[pro_update]',
					'class_prefix'   => 'pmp-pro-updater-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0 && isset($_this->plugin->options[$key]))
					{
						if($_this->plugin->options[$key])
							return $_this->plugin->options[$key];

						$data             = template::option_key_data($key);
						$default_template = new template($data->file, $data->type, TRUE);

						return $default_template->file_contents();
					}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-pro-updater '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '   	'.$this->heading(__('Pro Updater', $this->plugin->text_domain), 'logo.png').
				     '   	'.$this->notes(); // Heading/notifications.

				echo '   	<div class="pmp-body">'."\n";

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body  = '<i class="fa fa-user fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
		        $_panel_body .= '<p style="margin-top:0;">'.sprintf(__('From this page you can update to the latest version of %1$s Pro for WordPress. %1$s Pro is a premium product available for purchase @ <a href="%2$s" target="_blank">%3$s</a>. In order to connect with our update servers, we ask that you supply your account login details for <a href="%2$s" target="_blank">%3$s</a>. If you prefer not to provide your password, you can use your License Key in place of your password. Your License Key is located under "My Account" when you log in @ <a href="%2$s" target="_blank">%3$s</a>. This will authenticate your copy of %1$s Pro; providing you with access to the latest version. You only need to enter these credentials once. %1$s Pro will save them in your WordPress database.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_attr($this->plugin->utils_url->product_page()), esc_html(parse_url($this->plugin->product_url, PHP_URL_HOST))).'</p>'."\n";

				$_panel_body .= ' <table>'.
								'   <tbody>'.
								$form_fields->input_row(
									array(
										'name'          => 'username',
										'label'         => __('Customer Username', $this->plugin->text_domain),
										'placeholder'   => __('e.g., johndoe22', $this->plugin->text_domain),
										'current_value' => $current_value_for('pro_update_username'),
									)).
								'   </tbody>'.
								' </table>';

				$_panel_body .= ' <table>'.
								'   <tbody>'.
								$form_fields->input_row(
									array(
										'type'          => 'password',
										'name'          => 'password',
										'label'         => __('Customer Password or Product License Key', $this->plugin->text_domain),
										'current_value' => $current_value_for('pro_update_password'),
									)).
								'   </tbody>'.
								' </table>';

				echo $this->panel(__('Update Credentials', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-key"></i>', 'open' => TRUE, 'pro_only' => TRUE));

				unset($_panel_body); // Housekeeping.

		        /* ----------------------------------------------------------------------------------------- */

				$_panel_body = ' <table>'.
								'   <tbody>'.
								$form_fields->select_row(
									array(
										'name'            => 'check', 'label' => '',
										'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
										'current_value'   => $current_value_for('pro_update_check'),
										'allow_arbitrary' => FALSE, 'options' => array(
											'1' => __('Yes, display a notification in my WordPress Dashboard when a new version is available.', $this->plugin->text_domain),
											'0' => __('No, do not display any update notifications in my WordPress Dashboard.', $this->plugin->text_domain),
										),
										'notes_after' => '<p>'.sprintf(__('When a new version of %1$s Pro becomes available, %1$s Pro can display a notification in your WordPress Dashboard prompting you to return to this page and perform an upgrade.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
									)).
								'   </tbody>'.
								' </table>';

				echo $this->panel(__('Update Notifier', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-bullhorn"></i>', 'open' => TRUE, 'pro_only' => TRUE));

				unset($_panel_body); // Housekeeping.

		        /* ----------------------------------------------------------------------------------------- */

		        echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Update Now', $this->plugin->text_domain).' <i class="fa fa-magic"></i></button>'."\n";
				echo '         </div>'."\n";

				/* ----------------------------------------------------------------------------------------- */

				echo '   	</div>';
				echo '   </form>';
				echo '</div>';
			}

			/**
			 * Constructs menu page heading.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $title Title of this menu page.
			 * @param string $logo_icon Logo/icon for this menu page.
			 *
			 * @return string The heading for this menu page.
			 */
			protected function heading($title, $logo_icon = '')
			{
				$title     = (string)$title;
				$logo_icon = (string)$logo_icon;
				$heading   = ''; // Initialize.

				$heading .= '<div class="pmp-heading">'."\n";

				if($logo_icon && $this->plugin->options['menu_pages_logo_icon_enable'])
					$heading .= '  <img class="pmp-logo-icon" src="'.$this->plugin->utils_url->to('/client-s/images/'.$logo_icon).'" alt="'.esc_attr($title).'" />'."\n";

				$heading .= '  <div class="pmp-heading-links">'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->main_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__) ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-gears"></i> '.__('Options', $this->plugin->text_domain).'</a>'."\n";

				if(!$this->plugin->is_pro) // Display pro preview/upgrade related links?
				{
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->pro_preview()).'"'.
					            ($this->plugin->utils_env->is_pro_preview() ? ' class="pmp-active"' : '').'>'.
					            '<i class="fa fa-eye"></i> '.__('Preview Pro Features', $this->plugin->text_domain).'</a>'."\n";

					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
				}
				if($this->plugin->is_pro) // Display import options for pro users.
				{
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->import_export_menu_page_only()).'"'.
					            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') ? ' class="pmp-active"' : '').'>'.
					            '<i class="fa fa-upload"></i> '.__('Import/Export', $this->plugin->text_domain).

					            ((!$this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') && import_stcr::data_exists() && !import_stcr::ever_imported())
						            ? '<span class="pmp-blink">'.__('StCR Auto-Import', $this->plugin->text_domain).'</span>' : '').'</a>'."\n";
				}
				else if(import_stcr::data_exists()) // Lite version exposes import functionality for StCR users.
				{
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->import_export_menu_page_only()).'"'.
					            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') ? ' class="pmp-active"' : '').'>'.
					            '<i class="fa fa-upload"></i> '.__('Import/Export', $this->plugin->text_domain).

					            ((!$this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') && import_stcr::data_exists() && !import_stcr::ever_imported())
						            ? '<span class="pmp-blink">'.__('StCR Auto-Import', $this->plugin->text_domain).'</span>' : '').'</a>'."\n";
				}
				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->email_templates_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_email_templates') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-code"></i> '.__('Email Templates', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->site_templates_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_site_templates') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-code"></i> '.__('Site Templates', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '     <a href="#" data-pmp-action="'.esc_attr($this->plugin->utils_url->restore_default_options()).'" data-pmp-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure?', $this->plugin->text_domain)).'"><i class="fa fa-ambulance"></i> '.__('Restore Default Options', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->subscribe_page()).'" target="_blank"><i class="fa fa-envelope-o"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="wsi wsi-comment-mail"></i> '.esc_html($this->plugin->site_name).'</a>'."\n";

				$heading .= '  </div>'."\n";

				$heading .= '</div>'."\n";

				return $heading; // Menu page heading.
			}

			/**
			 * All-panel togglers.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Markup for all-panel togglers.
			 */
			protected function all_panel_togglers()
			{
				$togglers = '<div class="pmp-all-panel-togglers">'."\n";
				$togglers .= ' <a href="#" class="pmp-panels-open" title="'.esc_attr(__('Open All Panels', $this->plugin->text_domain)).'"><i class="fa fa-chevron-circle-down"></i></a>'."\n";
				$togglers .= ' <a href="#" class="pmp-panels-close" title="'.esc_attr(__('Close All Panels', $this->plugin->text_domain)).'"><i class="fa fa-chevron-circle-up"></i></a>'."\n";
				$togglers .= '</div>'."\n";

				return $togglers; // Toggles all panels open/closed.
			}

			/**
			 * Constructs menu page notes.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string The notes for this menu page.
			 */
			protected function notes()
			{
				$notes = ''; // Initialize notes.

				if($this->plugin->utils_env->is_pro_preview())
				{
					$notes .= '<div class="pmp-note pmp-notice">'."\n";
					$notes .= '  <a href="'.esc_attr($this->plugin->utils_url->page_only()).'" style="float:right; margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notes .= '  <i class="fa fa-arrow-down"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New advanced option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notes .= '  '.sprintf(__('<small><strong>MORE:</strong> in addition to what you see below, the pro version also includes import/export functionality, stats/graphs, and advanced PHP-based template options. [<a href="%2$s" target="_blank">learn more</a>]</small>', $this->plugin->text_domain), esc_html($this->plugin->name), esc_attr($this->plugin->utils_url->product_page()))."\n";
					$notes .= '</div>'."\n";
				}
				if($this->plugin->install_time() > strtotime('-48 hours') && $this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_*_templates'))
				{
					$notes .= '<div class="pmp-note pmp-notice">'."\n";
					$notes .= '  '.__('All templates come preconfigured; customization is optional <i class="fa fa-smile-o"></i>', $this->plugin->text_domain)."\n";
					$notes .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_menu_page(__NAMESPACE__) && (get_option('comment_moderation') || get_option('comment_whitelist')))
				{
					$notes .= '<div class="pmp-note pmp-notice">'."\n";
					$notes .= '  '.sprintf(__('<strong>Note:</strong> Your <a href="%1$s">Discussion Settings</a> indicate that comment moderation is enabled. That\'s fine. However, please remember that no emails will be sent until a comment (or reply) is approved.', $this->plugin->text_domain), esc_attr(admin_url('/options-discussion.php')))."\n";
					$notes .= '</div>'."\n";
				}
				return $notes; // All notices; if any apply.
			}

			/**
			 * Constructs a menu page stats view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string       $view Statistical view specification.
			 *
			 * @param array|string $form_fields An array of form fields needed for this view.
			 *    Each element should contain a nested array of row or hidden input arguments.
			 *
			 *    Or, any non-array element will be converted to a string `row` property;
			 *       i.e., a non-array item is considered a row w/o any other args.
			 *
			 * @param array        $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page stats view.
			 */
			protected function stats_view($view, array $form_fields = array(), array $args = array())
			{
				if(!$this->plugin->is_pro)
					return ''; // Pro only.

				$view = trim(strtolower((string)$view));

				$default_args            = array(
					'auto_chart' => FALSE,
				);
				$default_form_field_args = array(
					'hidden_input' => '',
					'row'          => '',
					'colspan'      => 1,
					'ends_row'     => FALSE,
				);
				$args                    = array_merge($default_args, $args);
				$args                    = array_intersect_key($args, $default_args);

				$auto_chart = (boolean)$args['auto_chart'];
				$slug       = str_replace('_', '-', $view);

				$view = '<div class="'.esc_attr('pmp-stats-view pmp-stats-view-'.$slug).'" data-view="'.esc_attr($slug).'">'."\n";
				$view .= '  <form novalidate="novalidate" onsubmit="return false;">'."\n";

				foreach($form_fields as $_form_field_args)
				{
					if(!is_array($_form_field_args)) // Force array.
						$_form_field_args = array('row' => (string)$_form_field_args);

					$_form_field_args = array_merge($default_form_field_args, $_form_field_args);
					$_form_field_args = array_intersect_key($_form_field_args, $default_form_field_args);

					if($_form_field_args['hidden_input']) // Actually a hidden input field?
						$view .= '<div style="display:none;">'.$_form_field_args['hidden_input'].'</div>'."\n";
				}
				unset($_form_field_args); // Housekeeping.

				$view .= '     <table class="pmp-stats-view-table">'."\n";
				$view .= '        <tbody>'."\n";
				$view .= '           <tr>'."\n";

				foreach($form_fields as $_form_field_args)
				{
					if(!is_array($_form_field_args)) // Force array.
						$_form_field_args = array('row' => (string)$_form_field_args);

					$_form_field_args = array_merge($default_form_field_args, $_form_field_args);
					$_form_field_args = array_intersect_key($_form_field_args, $default_form_field_args);

					if($_form_field_args['hidden_input']) continue; // Already included these above.

					$view .= '           <td class="pmp-stats-view-col"'.($_form_field_args['colspan'] > 1 ? ' colspan="'.esc_attr($_form_field_args['colspan']).'"' : '').'>'."\n";
					$view .= '              <table><tbody>'.$_form_field_args['row'].'</tbody></table>'."\n";
					$view .= '           </td>'."\n";

					if($_form_field_args['ends_row']) $view .= '</tr><tr>';
				}
				unset($_form_field_args); // Housekeeping.

				$view .= '              <td class="pmp-stats-view-col pmp-stats-view-submit">'."\n";
				$view .= '                 <button type="button" class="button button-primary"'.($auto_chart ? ' data-auto-chart' : '').'>'."\n";
				$view .= '                    '.__('Display Chart', $this->plugin->text_domain)."\n";
				$view .= '                 </button>'."\n";
				$view .= '              </td>'."\n";

				$view .= '           </tr>'."\n";
				$view .= '        </tbody>'."\n";
				$view .= '     </table>'."\n";

				$view .= '  </form>'."\n";
				$view .= '</div>'."\n";

				return $view; // Markup for this stats view.
			}

			/**
			 * Constructs a menu page panel.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $title Panel title.
			 * @param string $body Panel body; i.e., HTML markup.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page panel.
			 */
			protected function panel($title, $body, array $args = array())
			{
				$title = (string)$title;
				$body  = (string)$body;

				$default_args = array(
					'note'              => '',
					'icon'              =>
						'<i class="fa fa-gears"></i>',
					'pro_only'          => FALSE,
					'pro_preview_force' => FALSE,
					'open'              => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$note              = trim((string)$args['note']);
				$icon              = trim((string)$args['icon']);
				$pro_only          = (boolean)$args['pro_only'];
				$pro_preview_force = (boolean)$args['pro_preview_force'];
				$open              = (boolean)$args['open'];

				if($pro_only && !$this->plugin->is_pro && !$pro_preview_force && !$this->plugin->utils_env->is_pro_preview())
					return ''; // Not applicable; not pro, or not a pro preview.

				$panel = '<div class="pmp-panel'.esc_attr($pro_only && !$this->plugin->is_pro ? ' pmp-pro-preview' : '').'">'."\n";
				$panel .= '   <a href="#" class="pmp-panel-heading'.($open ? ' open' : '').'">'."\n";
				$panel .= '      '.$icon.' '.$title."\n";
				$panel .= $note ? '<span class="pmp-panel-heading-note">'.$note.'</span>' : '';
				$panel .= '   </a>'."\n";

				$panel .= '   <div class="pmp-panel-body'.($open ? ' open' : '').' pmp-clearfix">'."\n";

				$panel .= '      '.$body."\n";

				$panel .= '   </div>'."\n";
				$panel .= '</div>'."\n";

				return $panel; // Markup for this panel.
			}

			/**
			 * Constructs a menu page postbox.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $title Postbox title.
			 * @param string $body Postbox body; i.e., HTML markup.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page postbox.
			 */
			protected function postbox($title, $body, array $args = array())
			{
				$title = (string)$title;
				$body  = (string)$body;

				$default_args = array(
					'note'     => '',
					'icon'     =>
						'<i class="fa fa-gears"></i>',
					'pro_only' => FALSE,
					'open'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$note     = trim((string)$args['note']);
				$icon     = trim((string)$args['icon']);
				$pro_only = (boolean)$args['pro_only'];
				$open     = (boolean)$args['open'];

				$id = 'pb-'.md5($title.$icon.$note); // Auto-generate.

				if($pro_only && !$this->plugin->is_pro && !$this->plugin->utils_env->is_pro_preview())
					return ''; // Not applicable; not pro, or not a pro preview.

				$postbox = '<div id="'.esc_attr($id).'"'. // Expected by `postbox.js` in WP core.
				           ' class="pmp-postbox postbox'.esc_attr((!$open ? ' closed' : ''). // Add `closed` class.
				                                                  ($pro_only && !$this->plugin->is_pro ? ' pmp-pro-preview' : '')).'">'."\n";
				$postbox .= '  <div class="pmp-postbox-handle handlediv"><br /></div>'."\n";

				$postbox .= '  <h3 class="pmp-postbox-hndle hndle">'."\n";
				$postbox .= '      '.$icon.' '.$title."\n";
				$postbox .= $note ? '<span class="pmp-postbox-hndle-note">'.$note.'</span>' : '';
				$postbox .= '  </h3>'."\n";

				$postbox .= '  <div class="pmp-postbox-inside inside">'."\n";
				$postbox .= '     '.$body."\n";
				$postbox .= '  </div>'."\n";

				$postbox .= '</div>'."\n";

				return $postbox; // Markup for this postbox.
			}

			/**
			 * Constructs a select-all input field value.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $label_markup HTML markup for label.
			 * @param string $value Current value to be selected in the input field.
			 *
			 * @return string Markup for this select-all input field value.
			 */
			protected function select_all_field($label_markup, $value)
			{
				$label_markup = trim((string)$label_markup);
				$value        = trim((string)$value);

				return // Select-all input field value.

					'<table style="table-layout:auto;">'.
					'  <tr>'.
					'     <td style="display:table-cell; white-space:nowrap;">'.
					'        '.$label_markup.
					'     </td>'.
					'     <td style="display:table-cell; width:100%;" title="'.__('select all; copy', $this->plugin->text_domain).'">'.
					'        <input type="text" value="'.esc_attr($value).'" readonly="readonly" data-toggle="select-all" style="cursor:pointer; color:#333333; background:#FFFFFF;" />'.
					'     </td>'.
					'  </tr>'.
					'</table>';
			}
		}
	}
}
