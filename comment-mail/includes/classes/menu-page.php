<?php
/**
 * Menu Pages
 *
 * @since 14xxxx First documented version.
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
		 * @since 14xxxx First documented version.
		 */
		class menu_page extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
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
			 * Displays menu page. @TODO
			 *
			 * @since 14xxxx First documented version.
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
					if(strpos($key, 'template__') === 0)
						if(isset($_this->plugin->options[$key]))
						{
							if($_this->plugin->options[$key])
								return $_this->plugin->options[$key];

							$file             = template::option_key_to_file($key);
							$default_template = new template($file, TRUE);

							return $default_template->file_contents();
						}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-options '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Plugin Options', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notifications(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Enable %1$s&trade; Functionality?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'enable',
						               'current_value'   => $current_value_for('enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => sprintf(__('Yes, enable %1$s&trade; (recommended)', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable %1$s&trade; temporarily', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<div class="pmp-note pmp-warning pmp-panel-if-disabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.sprintf(__('When %1$s&trade; is disabled in this way:', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.__('Comment Subscription Options (options for receiving email notifications regarding comments/replies) no longer appear on comment forms. In short, no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain).'</li>'.
						                                    '      <li>'.sprintf(__('The mail queue processor will stop processing, until such time as the plugin is renabled; i.e. no more email notifications. Mail queue injections continue, but no queue processing. If it is desirable that any queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %1$s.', $this->plugin->text_domain), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</li>'.
						                                    '   </ul>'.
						                                    '   <p><em>'.sprintf(__('<strong>Note:</strong> If you want to disable %1$s&trade; completely, please deactivate it from the plugins menu in WordPress.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</em></p>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled-show"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Allow New Subsciptions?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
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
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'queue_processing_enable',
						                'current_value'   => $current_value_for('queue_processing_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, enable mail queue processing (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disable mail queue processing temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('If you set this to <code>No</code> (disabled), all mail queue processing will stop; until such time as the plugin is renabled. In short, no more email notifications will be sent. Mail queue injections continue, but no queue processing. If it is desirable that any queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %1$s.', $this->plugin->text_domain), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel('Enable/Disable', $_panel_body, array('open' => !$this->plugin->options['enable']));

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Uninstall on Plugin Deletion, or Safeguard Options?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'uninstall_safeguards_enable',
						               'current_value'   => $current_value_for('uninstall_safeguards_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Safeguards on; i.e. protect my plugin options &amp; comment subscriptions (recommended)', $this->plugin->text_domain),
							               '0' => sprintf(__('Safeguards off; uninstall (completely erase) %1$s on plugin deletion', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<p>'.sprintf(__('By default, if you delete %1$s using the plugins menu in WordPress, no data is lost. However, if you want to completely uninstall %1$s you should turn Safeguards off, and <strong>THEN</strong> deactivate &amp; delete %1$s from the plugins menu in WordPress. This way %1$s will erase your options for the plugin, erase database tables created by the plugin, remove subscriptions, terminate CRON jobs, etc. In short, when Safeguards are off, %1$s erases itself from existence completely when you delete it.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel('Plugin Deletion Safeguards', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable Comment Form Subscr. Options Template?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'comment_form_template_enable',
						               'current_value'   => $current_value_for('comment_form_template_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Yes, use built-in template system (recommended)', $this->plugin->text_domain),
							               '0' => __('No, disable built-in template system; I have a deep theme integration of my own', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<p>'.__('The built-in template system is quite flexible already; you can even customize the default template yourself if you want to (as seen below). Therefore, it is not recommended that you disable the default template system. This option only exists for very advanced users; i.e. those who prefer to disable the template completely in favor of their own custom implementation. If you disable the built-in template, you\'ll need to integrate HTML markup of your own into the proper location of your theme.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-disabled-show">'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Also Disable Scripts Associated w/ Comment Form Subscr. Options?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'comment_form_scripts_enable',
						                'current_value'   => $current_value_for('comment_form_scripts_enable'),
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

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->textarea_row(
					                array(
						                'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
						                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						                'cm_mode'       => 'application/x-httpd-php',
						                'name'          => 'template__site__comment_form__sub_ops',
						                'current_value' => $current_value_for('template__site__comment_form__sub_ops'),
						                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sub-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
						                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g. subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_field_comment/', 'comment_form_field_comment'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form/', 'comment_form')).'</p>'.
						                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					                )).
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
						                'notes_after'     => '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. You can change the wording that appears for these options by editing the template above. However, the default choice is determined systematically, based on the one that you choose here — assuming that you haven\'t dramatically altered code in the template. For most sites, the most logical default choice is: <code>replies only</code>; i.e. the commenter will only receive notifications for replies to the comment they are posting.', $this->plugin->text_domain).'</p>',
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
						                'notes_after'     => '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. You can change the wording that appears for these options by editing the template above. However, the default choice is determined systematically, based on the one that you choose here — assuming that you haven\'t dramatically altered code in the template. For most sites, the most logical default choice is: <code>asap</code> (aka: instantly); i.e. the commenter will receive instant notifications regarding replies to their comment.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				echo $this->panel('Comment Form Subscription Options', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('WordPress Capability Required to Manage Subscriptions', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. moderate_comments', $this->plugin->text_domain),
						               'name'          => 'manage_cap',
						               'current_value' => $current_value_for('manage_cap'),
						               'notes_after'   => '<p>'.sprintf(__('If you can <code>%2$s</code>, you can always manage subscriptions and %1$s options, no matter what you configure here. However, if you have other users that help manage your site, you can set a specific %3$s they\'ll need in order for %1$s to allow them access. Users w/ this capability will be allowed to manage subscriptions, the mail queue, event logs, and statistics; i.e. everything <em>except</em> change %1$s options. To alter %1$s options you\'ll always need the <code>%2$s</code> capability.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities#'.$this->plugin->cap, $this->plugin->cap), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities', __('WordPress Capability', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel('Subscription Management Access', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable Auto-Subscribe?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'auto_subscribe_enable',
						               'current_value'   => $current_value_for('auto_subscribe_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Yes, enable Auto-Subscribe (recommended)', $this->plugin->text_domain),
							               '0' => __('No, disable all Auto-Subscribe functionality', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-panel-if-enabled-show">'.
						                                    '  <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When Auto-Subscribe is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '  <ul class="pmp-list-items">'.
						                                    '     <li>'.__('The author of a post can be subscribed to all comments/replies automatically. This way they\'ll receive email notifications w/o needing to go through the normal comment subscription process.', $this->plugin->text_domain).'</li>'.
						                                    '     <li>'.__('A list of other recipients can be added, allowing you to auto-subscribe other email addresses to every post automatically.', $this->plugin->text_domain).'</li>'.
						                                    '  </ul>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Subscribe Post Authors?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
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
						                'notes_after'   => '<p>'.__('You can enter a list of other email addresses that should be auto-subscribed to all posts. This is a semicolon-delimited list of recipients; e.g. <code>"John" &lt;john@example.com&gt;; jane@example.com; "Susan Smith" &lt;susan@example.com&gt;</code>.', $this->plugin->text_domain).'</p>',
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
						                'placeholder'   => __('e.g. post,page,portfolio,gallery', $this->plugin->text_domain),
						                'name'          => 'auto_subscribe_post_types',
						                'current_value' => $current_value_for('auto_subscribe_post_types'),
						                'notes_after'   => '<p>'.sprintf(__('These are the %2$s that will trigger automatic subscriptions; i.e. %1$s will only auto-subscribe people to these types of posts. The default list is adequate for most sites. However, if you have other %2$s enabled by a theme/plugin, you might wish to include those here. e.g. <code>post,page,portfolio,gallery</code>; where <code>portfolio,gallery</code> might be two %3$s that you add to the default list, if applicable.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types', __('Post Types', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types#Custom_Post_Types', __('Custom Post Types', $this->plugin->text_domain))).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel('Auto-Subscribe Settings', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Auto-Confirm Everyone?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'auto_confirm_force_enable',
						               'current_value'   => $current_value_for('auto_confirm_force_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '0' => __('No, require subscriptions to be confirmed via email (highly recommended)', $this->plugin->text_domain),
							               '1' => __('Yes, automatically auto-confirm everyone; i.e. never ask for email confirmation', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-panel-if-enabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When Auto-Confirm Everyone is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.sprintf(__('Nobody will be required to confirm a subscription. For instance, when someone leaves a comment and chooses to be subscribed (with whatever email address they\'ve entered), that email address will be added to the list w/o getting confirmation from the real owner of that address. This scenario changes slightly if you %1$s before leaving a comment — via WordPress Discussion Settings. If that\'s the case, then depending on the way your users register (i.e. if they are required to verify their email address in some way), this option might be feasible. That said, in 99%% of all cases this option is NOT recommended. If you enable auto-confirmation for everyone, please take extreme caution.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Settings_Discussion_Screen', __('require users to be logged-in', $this->plugin->text_domain))).'</li>'.
						                                    '      <li>'.sprintf(__('In addition to security issues associated w/ auto-confirming everyone automatically; if you enable this behavior it will also have the negative side-effect of making it slightly more difficult for users to view a summary of their existing subscriptions; i.e. they won\'t get an encrypted <code>%2$s</code> cookie right away via email confirmation, as would normally occur. This is how %1$s identifies a user when they are not currently logged into the site (typical w/ commenters). Therefore, if Auto-Confirm Everyone is enabled, the only way users can view a summary of their subscriptions, is if:', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).
						                                    '        <ul>'.
						                                    '           <li>'.__('They\'re a logged-in user, and you\'ve enabled "All WP Users Confirm Email" below; i.e. a logged-in user\'s email address can be trusted — known to be confirmed already.', $this->plugin->text_domain).'</li>'.
						                                    '           <li>'.sprintf(__('Or, if they click a link to manage their subscription after having received an email notification regarding a new comment. It is at this point that an auto-confirmed subscriber will finally get their encrypted <code>%1$s</code> cookie. That said, it\'s important to note that <em>anyone</em> can manage their subscriptions after receiving an email notification regarding a new comment. In every email notification there is a "Manage My Subscriptions" link provided for them. This link provides access to subscription management through a secret subscription key; not dependent upon a cookie.', $this->plugin->text_domain), esc_html(__NAMESPACE__.'_sub_email')).'</li>'.
						                                    '        </ul>'.
						                                    '     </li>'.
						                                    '   </ul>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-disabled-show"><hr />'.
				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Confirm if Already Subscribed w/ the Same IP Address?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
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

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                ' <tbody>'.
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
						                'notes_after'     => '<p>'.sprintf(__('If %1$s sees that a user is currently logged into the site as a real user (i.e. not <em>just</em> a commenter); it can detect the current user\'s email address w/o needing the encrypted <code>%2$s</code> cookie that is normally set via email confirmation. However, in order for this to occur, this option must be set to <code>Yes</code>; i.e. %1$s needs to know that it can trust the email address associated w/ each user account within WordPress before it will read an email address from <code>wp_users</code> table.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).'</p>'.
						                                     '<p class="pmp-note pmp-warning">'.sprintf(__('<strong>Warning:</strong> Please be cautious about how you answer this question. Do all of your users <em>really</em> register and confirm their email address before being allowed to log in? If a user updates their profile, is an email change-of-address always confirmed too? Some themes/plugins make it possible for registration/updates to occur <em>without</em> doing so. If that\'s the case, you should answer <code>No</code> here (default behavior), and just let the encrypted <code>%2$s</code> cookie do it\'s thing. That\'s what it\'s there for <i class="fa fa-smile-o"></i>', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).'</p>'.
						                                     '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> Your answer here does NOT enable or disable auto-confirmation in any way. It\'s simply a flag that is used by %1$s (internally), to help it make the most logical (safest) decision under certain scenarios that are impacted by the email address of the current user. It\'s important to realize that no matter what you answer here, %1$s will still be fully functional. <strong>If in doubt, please answer <code>No</code> (default behavior)</strong>.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
					                )).
				                ' </tbody>'.
				                '</table>';

				echo $this->panel('Auto-Confirm Settings', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable SMTP Integration?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'smtp_enable',
						               'current_value'   => $current_value_for('smtp_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '0' => __('No, use the wp_mail function (default behavior)', $this->plugin->text_domain),
							               '1' => __('Yes, integrate w/ an SMTP server of my choosing (as configured below)', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-panel-if-enabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When SMTP Server Integration is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.sprintf(__('Instead of using the default <code>%2$s</code> function, %1$s will send email confirmation requests &amp; comment/reply notifications through an SMTP server of your choosing; i.e. all email processed by %1$s will be routed through an SMTP server that you\'ve dedicated to comment subscriptions. This is highly recommended, since it can significantly improve the deliverability rate of emails that are sent by %1$s. In addition, it may also speed up your site (i.e. reduce the burden on your own web server). This is because an SMTP host is generally associated with an external server that is dedicated to email processing.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail')).'</li>'.
						                                    '   </ul>'.
						                                    '  <p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> If you are already running a plugin like %2$s (i.e. a plugin that reconfigures the <code>%3$s</code> function globally); that is usually enough, and you should generally NOT enable SMTP integration here also. In other words, if <code>%3$s</code> is already configured globally to route mail through an SMTP server, you would only need the options below if your intention was to override your existing SMTP configuration specifically for %1$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://wordpress.org/plugins/wp-mail-smtp/', 'WP Mail SMTP'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail')).'</p>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.

				                '<a href="http://aws.amazon.com/ses/" target="_blank">'.
				                '  <img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/aws-ses-rec.png')).'" class="pmp-right" style="margin:1em 0 0 3em;" />'.
				                '</a>'.

				                ' <table style="width:auto;">'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('SMTP Host Name:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. email-smtp.us-east-1.amazonaws.com', $this->plugin->text_domain),
						                'name'          => 'smtp_host',
						                'current_value' => $current_value_for('smtp_host'),
						                'notes_after'   => '<p>'.__('e.g. <code>email-smtp.us-east-1.amazonaws.com</code>, <code>smtp.gmail.com</code>, or another of your choosing.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                ' <table style="width:auto;">'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('SMTP Port Number:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 465', $this->plugin->text_domain),
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
							                ''    => __('', $this->plugin->text_domain),
							                ' '   => __('Plain Text Authentication', $this->plugin->text_domain),
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
						                'placeholder'   => __('e.g. AKIAJSA57DDLS5I6GCA; e.g. me@example.com', $this->plugin->text_domain),
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
						                'placeholder'   => __('e.g. AWS secret key, or email account password', $this->plugin->text_domain),
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
						                'placeholder'   => __('e.g. MySite.com', $this->plugin->text_domain),
						                'name'          => 'smtp_from_name',
						                'current_value' => $current_value_for('smtp_from_name'),
						                'notes_after'   => '<p>'.__('The name used in the <code>From:</code> and <code>Return-Path:</code> headers; e.g. <code>MySite.com</code>', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('SMTP <code>From</code> and <code>Return-Path</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. moderator@mysite.com', $this->plugin->text_domain),
						                'name'          => 'smtp_from_email',
						                'current_value' => $current_value_for('smtp_from_email'),
						                'notes_after'   => '<p>'.__('Email used in the <code>From:</code> and <code>Return-Path:</code> headers; e.g. <code>moderator@mysite.com</code>', $this->plugin->text_domain).'</p>'.
						                                   '<p class="pmp-note pmp-info">'.__('<strong>Note:</strong> most SMTP servers will require this email address to match up with specific users and/or specific domains; else mail is rejected automatically. Please be sure to check the documentation for your SMTP host before entering this address. For instance, with Amazon&reg; SES you will need to setup at least one Verified Sender and then enter that address here. With GMail&trade;, you will need to enter the email address that is associated with the Username/Password you entered above.', $this->plugin->text_domain).'</p>'
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
						                'placeholder' => __('e.g. me@mysite.com', $this->plugin->text_domain),
						                'name'        => 'smtp_test', // Not an actual option key; but the `save_options` handler picks this up.
						                'notes_after' => sprintf(__('Enter an email address to have %1$s&trade; send a test message when you save these options, and report back about any success or failure.', $this->plugin->text_domain), esc_html($this->plugin->name)),
					                )).
				                '  </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel('SMTP Server Integration', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'type'          => 'email',
						               'label'         => __('Postmaster Email Address', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. postmaster@example.com or abuse@example.com', $this->plugin->text_domain),
						               'name'          => 'can_spam_postmaster',
						               'current_value' => $current_value_for('can_spam_postmaster'),
						               'notes_after'   => '<p>'.sprintf(__('This is NOT the address that emails are sent from. This address is simply displayed at the bottom of each email sent by %1$s, as a way for people to report any abuse of the system.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->textarea_row(
					                array(
						                'label'         => sprintf(__('Mailing Address (Required for %1$s)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/CAN-SPAM_Act_of_2003', __('CAN-SPAM Compliance', $this->plugin->text_domain))),
						                'placeholder'   => __('e.g. 123 Somewhere Street; Somewhere, USA 99999', $this->plugin->text_domain),
						                'cm_mode'       => 'text/html',
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
						                'placeholder'   => __('e.g. http://example.com/privacy-policy/', $this->plugin->text_domain),
						                'name'          => 'can_spam_privacy_policy_url',
						                'current_value' => $current_value_for('can_spam_privacy_policy_url'),
						                'notes_after'   => '<p>'.sprintf(__('If you fill this in, %1$s will display a link to your privacy policy in strategic locations.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel('CAN-SPAM Compliance', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Blacklist Patterns (One Per Line)', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. webmaster@*', $this->plugin->text_domain),
						               'name'          => 'email_blacklist_patterns',
						               'rows'          => 15, // Give them some room here.
						               'other_attrs'   => 'spellcheck="false"',
						               'current_value' => $current_value_for('email_blacklist_patterns'),
						               'notes_after'   => '<p>'.__('One pattern per line please. A <code>*</code> wildcard character can be used to match zero or more characters of any kind. A <code>^</code> caret symbol can be used to match zero or more characters that are NOT the <code>@</code> symbol.', $this->plugin->text_domain).'</p>'.
						                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> It is suggested that you blacklist role-based email addresses to avoid sending email notifications to addresses not associated w/ individuals. Role-based email addresses (like admin@, help@, sales@) are email addresses that are not associated with a particular person, but rather with a company, department, position or group of recipients. They are not generally intended for personal use, as they typically include a distribution list of recipients.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel('Blacklisted Email Addresses', $_panel_body, array());

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
			 * @since 14xxxx First documented version.
			 */
			protected function stats_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-stats '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Statistics', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notifications(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.__('Coming soon...', $this->plugin->text_domain)."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
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

						echo '      <h2>'.sprintf(__('%1$s&trade; &#10609; Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug).'"></i>'.
						     '       <a href="'.esc_attr($this->plugin->utils_url->new_sub_short()).'" class="add-new-h2">'.__('Add New', $this->plugin->text_domain).'</a></h2>'."\n";

						new menu_page_subs_table(); // Displays table.

						echo '   </form>';
						echo '</div>'."\n";
				}
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function _sub_new()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-new '.$this->plugin->slug.'-menu-page-form '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only(array('action'))).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &#10609; New Subscription', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i></h2>'."\n";

				new menu_page_sub_new_form(); // Displays form to add new subscription.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function _sub_edit()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-edit '.$this->plugin->slug.'-menu-page-form '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only(array('action', 'subscription'))).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &#10609; Edit Subscription', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i></h2>'."\n";

				new menu_page_sub_edit_form(!empty($_REQUEST['subscription']) ? (integer)$_REQUEST['subscription'] : 0); // Displays form.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function sub_event_log_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-event-log '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &#10609; Subscription Event Log', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-history"></i></h2>'."\n";

				new menu_page_sub_event_log_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function queue_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-queue '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &#10609; Queued (Pending) Notifications', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-envelope-o"></i></h2>'."\n";

				new menu_page_queue_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function queue_event_log_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-queue-event-log '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &#10609; Queue Event Log', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-paper-plane"></i></h2>'."\n";

				new menu_page_queue_event_log_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Constructs menu page heading.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $title Title of this menu page.
			 * @param string $logo_icon Logo/icon for this menu page.
			 *
			 * @return string The heading for this menu page.
			 */
			protected function heading($title, $logo_icon)
			{
				$title     = (string)$title;
				$logo_icon = (string)$logo_icon;
				$heading   = ''; // Initialize.

				$heading .= '<div class="pmp-heading">'."\n";

				$heading .= '  <img class="pmp-logo-icon" src="'.$this->plugin->utils_url->to('/client-s/images/'.$logo_icon).'" alt="'.esc_attr($title).'" />'."\n";

				$heading .= '  <div class="pmp-heading-links">'."\n";

				if(!$this->plugin->is_pro)
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->pro_preview()).'"><i class="fa fa-eye"></i> Preview Pro Features</a>'."\n";

				if(!$this->plugin->is_pro)
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->subscribe_page()).'" target="_blank"><i class="fa fa-envelope"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '     <a href="#" data-pmp-action="'.esc_attr($this->plugin->utils_url->restore_default_options()).'" data-pmp-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure?', $this->plugin->text_domain)).'"><i class="fa fa-ambulance"></i> '.__('Restore Default Options', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  </div>'."\n";

				$heading .= '</div>'."\n";

				return $heading; // Menu page heading.
			}

			/**
			 * Constructs menu page notifications.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The notifications for this menu page.
			 */
			protected function notifications()
			{
				$notices = ''; // Initialize notifications.

				if($this->plugin->utils_env->is_options_updated())
				{
					$notices .= '<div class="pmp-note pmp-notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Options updated successfully.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_options_restored())
				{
					$notices .= '<div class="pmp-note pmp-notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Default options successfully restored.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_pro_preview())
				{
					$notices .= '<div class="pmp-note pmp-info">'."\n";
					$notices .= '  <a href="'.esc_attr($this->plugin->utils_url->page_only()).'" style="float:right; margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notices .= '  <i class="fa fa-eye"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notices .= '  '.sprintf(__('<small>NOTE: the free version of %1$s (i.e. this lite version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				if(!$this->plugin->options['enable'] && $this->plugin->utils_env->is_menu_page(__NAMESPACE__))
				{
					$notices .= '<div class="pmp-note pmp-warning">'."\n";
					$notices .= '  <i class="fa fa-warning"></i> '.sprintf(__('%1$s is currently disabled; please review options below.', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				return $notices; // All notices; if any apply.
			}

			/**
			 * Constructs a menu page panel.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $title Panel title.
			 * @param string $body Panel body; i.e. HTML markup.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page panel.
			 */
			protected function panel($title, $body, array $args = array())
			{
				$title = (string)$title;
				$body  = (string)$body;

				$default_args = array(
					'icon'             =>
						'<i class="fa fa-gears"></i>',

					'pro_preview_only' => FALSE,
					'open'             => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$icon = (string)$args['icon'];

				$pro_preview_only = (boolean)$args['pro_preview_only'];
				$open             = (boolean)$args['open'];

				if($pro_preview_only && !$this->plugin->utils_env->is_pro_preview())
					return ''; // Not applicable.

				$panel = '<div class="pmp-panel">'."\n";
				$panel .= '   <a href="#" class="pmp-panel-heading'.($open ? ' open' : '').'">'."\n";
				$panel .= '      '.$icon.' '.$title."\n";
				$panel .= '   </a>'."\n";

				$panel .= '   <div class="pmp-panel-body'.($open ? ' open' : '').' pmp-clearfix">'."\n";

				$panel .= '      '.$body."\n";

				$panel .= '   </div>'."\n";
				$panel .= '</div>'."\n";

				return $panel; // Markup for this panel.
			}
		}
	}
}