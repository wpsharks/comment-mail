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

				$_panel_body = '<table>'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'       => sprintf(__('Enable %1$s&trade; Functionality?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder' => __('Select an Option...', $this->plugin->text_domain),
						               'name'        => 'enable', 'current_value' => $current_value_for('enable'),
						               'options'     => array(
							               '1' => sprintf(__('Yes, enable %1$s&trade; (recommended)', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable %1$s&trade; temporarily', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after' => '<div class="pmp-notif-warning pmp-panel-if-disabled-show">'.
						                                '   <p style="font-weight:bold; font-size:110%; margin:0;">'.sprintf(__('When %1$s&trade; is disabled in this way:', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                '   <ul class="pmp-list-items">'.
						                                '      <li>'.sprintf(__('Comment subscription options (for receiving email notifications regarding comments/replies) no longer appear on comment forms; i.e. no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain)).'</li>'.
						                                '      <li>'.sprintf(__('The mail queue processor will stop processing, until such time as the plugin is renabled; i.e. no more email notifications. Mail queue injections continue, but no queue processing. If it is desirable that any queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %1$s.', $this->plugin->text_domain), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</li>'.
						                                '   </ul>'.
						                                '<p><em>'.sprintf(__('<strong>Note:</strong> If you want to disable %1$s&trade; completely, please deactivate it from the plugins menu in WordPress.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</em></p>'.
						                                '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'       => sprintf(__('Allow New Subsciptions?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder' => __('Select an Option...', $this->plugin->text_domain),
						                'name'        => 'new_subs_enable', 'current_value' => $current_value_for('new_subs_enable'), 'field_class' => 'no-if-enabled',
						                'options'     => array(
							                '1' => __('Yes, allow new subscriptions (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disallow new subscriptions temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after' => '<p>'.sprintf(__('If you disallow, comment subscription options (for receiving email notifications regarding comments/replies) no longer appear on comment forms; i.e. no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain)).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'       => sprintf(__('Enable Mail Queue Processing?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder' => __('Select an Option...', $this->plugin->text_domain),
						                'name'        => 'queue_processing_enable', 'current_value' => $current_value_for('queue_processing_enable'), 'field_class' => 'no-if-enabled',
						                'options'     => array(
							                '1' => __('Yes, enable mail queue processing (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disable mail queue processing temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after' => '<p>'.sprintf(__('If disabled, all mail queue processing will stop, until such time as the plugin is renabled; i.e. no more email notifications. Mail queue injections continue, but no queue processing. If it is desirable that any queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %1$s.', $this->plugin->text_domain), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</p>',
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
						               'label'       => sprintf(__('Uninstall on Plugin Deletion, or Safeguard Options?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder' => __('Select an Option...', $this->plugin->text_domain),
						               'name'        => 'uninstall_safeguards_enable', 'current_value' => $current_value_for('uninstall_safeguards_enable'),
						               'options'     => array(
							               '1' => __('Safeguards on; i.e. protect my plugin options &amp; comment subscriptions (recommended)', $this->plugin->text_domain),
							               '0' => sprintf(__('Safeguards off; uninstall (completely erase) %1$s on plugin deletion', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after' => '<p>'.sprintf(__('By default, if you delete %1$s using the plugins menu in WordPress, no data is lost. However, if you want to completely uninstall %1$s you should turn Safeguards off, and <strong>THEN</strong> deactivate &amp; delete %1$s from the plugins menu in WordPress. This way %1$s will erase your options for the plugin, erase database tables created by the plugin, remove subscriptions, terminate CRON jobs, etc. In short, when Safeguards are off, %1$s erases itself from existence completely when you delete it.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel('Plugin Deletion Safeguards', $_panel_body, array());

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'       => sprintf(__('Enable Comment Form Subscr. Options Template?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder' => __('Select an Option...', $this->plugin->text_domain),
						               'name'        => 'comment_form_template_enable', 'current_value' => $current_value_for('comment_form_template_enable'),
						               'options'     => array(
							               '1' => __('Yes, use built-in template system (recommended)', $this->plugin->text_domain),
							               '0' => sprintf(__('No, disable built-in template system; I have a deep theme integration of my own', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after' => '<p>'.sprintf(__('The built-in template system is quite flexible already; you can even customize the default template yourself if you want to. Therefore, it is not recommended that you disable the default template system. This option only exists for very advanced users; i.e. those who prefer to disable the template completely in favor of their own custom implementation. If you disable the built-in template, you\'ll need to integrate HTML markup of your own into the proper location of your theme.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-disabled-show">'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'       => sprintf(__('Also Disable Scripts Associated w/ Comment Form Subscr. Options?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder' => __('Select an Option...', $this->plugin->text_domain),
						                'name'        => 'comment_form_scripts_enable', 'current_value' => $current_value_for('comment_form_scripts_enable'), 'field_class' => 'no-if-enabled',
						                'options'     => array(
							                '1' => __('No, leave scripts associated w/ comment form subscr. options template enabled (recommended)', $this->plugin->text_domain),
							                '0' => sprintf(__('Yes, disable built-in scripts also; I have a deep theme integration of my own', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                ),
						                'notes_after' => '<p>'.sprintf(__('For advanced use only. If you disable the built-in template system, you may also want to disable the built-in JavaScript associated w/ this template.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				$_panel_body .= '<div class="pmp-panel-if-enabled-show"><hr />'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->textarea_row(
					                array(
						                'label'        => sprintf(__('Comment Form Subscr. Options Template', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder'  => __('Template Content...', $this->plugin->text_domain),
						                'name'         => 'template__site__comment_form__sub_ops', 'current_value' => $current_value_for('template__site__comment_form__sub_ops'), 'cm_mode' => 'application/x-httpd-php',
						                'notes_before' => '<p class="pmp-notif-notice">'.sprintf(__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, there are a few themes out there :-) If your theme is not playing well with the default template; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain)).'</p>',
						                'notes_after'  => '<p>'.sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>comment_form_field_comment</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>comment_form</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g. subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                  '<p class="pmp-notif-info">'.sprintf(__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain)).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				echo $this->panel('Comment Form Subscription Options', $_panel_body, array());

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
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->pro_preview()).'"><i class="fa fa-eye"></i> Preview Pro Features</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
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
					$notices .= '<div class="pmp-notif-notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Options updated successfully.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_options_restored())
				{
					$notices .= '<div class="pmp-notif-notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Default options successfully restored.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_pro_preview())
				{
					$notices .= '<div class="pmp-notif-info">'."\n";
					$notices .= '  <a href="'.esc_attr($this->plugin->utils_url->page_only()).'" style="float:right; margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notices .= '  <i class="fa fa-eye"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notices .= '  '.sprintf(__('<small>NOTE: the free version of %1$s (i.e. this lite version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				if(!$this->plugin->options['enable'] && $this->plugin->utils_env->is_menu_page(__NAMESPACE__))
				{
					$notices .= '<div class="pmp-notif-warning">'."\n";
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