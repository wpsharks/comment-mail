<?php
/**
 * Menu Pages @TODO
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
		class menu_page extends abstract_base
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

				$which = (string)$which;
				if($which && method_exists($this, $which))
					$this->{$which}();
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function options()
			{
				echo '<div id="'.esc_attr($this->plugin->slug.'-menu-page').'" class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->current_page_nonce_only()).'">'."\n";

				echo '      '.$this->heading(__('Plugin Options', $this->plugin->text_domain), 'options.png').$this->notices(); // Heading/notices.

				echo '      <div class="pmp-body">'."\n";

				echo '         <div class="pmp-panel">'."\n";
				echo '            <a href="#" class="pmp-panel-heading'.((!$this->plugin->options['enable']) ? ' open' : '').'">'."\n";
				echo '               <i class="fa fa-flag"></i> '.__('Enable/Disable', $this->plugin->text_domain)."\n";
				echo '            </a>'."\n";

				echo '            <div class="pmp-panel-body'.((!$this->plugin->options['enable']) ? ' open' : '').' pmp-clearfix">'."\n";
				echo '               <p><label class="pmp-switch-primary"><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="1"'.checked($this->plugin->options['enable'], '1', FALSE).' /> <i class="fa fa-magic fa-flip-horizontal"></i> '.sprintf(__('Yes, enable %1$s™', $this->plugin->text_domain), esc_html($this->plugin->name)).'</label> &nbsp;&nbsp;&nbsp; <label><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="0"'.checked($this->plugin->options['enable'], '0', FALSE).' /> '.__('No, disable.', $this->plugin->text_domain).'</label></p>'."\n";
				echo '            </div>'."\n";
				echo '         </div>'."\n";

				echo '         <div class="pmp-panel">'."\n";
				echo '            <a href="#" class="pmp-panel-heading">'."\n";
				echo '               <i class="fa fa-shield"></i> '.__('Plugin Deletion Safeguards', $this->plugin->text_domain)."\n";
				echo '            </a>'."\n";

				echo '            <div class="pmp-panel-body pmp-clearfix">'."\n";
				echo '               <i class="fa fa-shield fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
				echo '               <h3>'.__('Uninstall on Plugin Deletion; or Safeguard Options?', $this->plugin->text_domain).'</h3>'."\n";
				echo '               <p>'.sprintf(__('<strong>Tip:</strong> By default, if you delete %1$s using the plugins menu in WordPress, nothing is lost. However, if you want to completely uninstall %1$s you should set this to <code>Yes</code> and <strong>THEN</strong> deactivate &amp; delete %1$s from the plugins menu in WordPress. This way %1$s will erase your options for the plugin, erase database tables created by the plugin, remove subscribers, terminate CRON jobs, etc. It erases itself from existence completely.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'."\n";
				echo '               <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][uninstall_on_deletion]">'."\n";
				echo '                     <option value="0"'.selected($this->plugin->options['uninstall_on_deletion'], '0', FALSE).'>'.__('Safeguard my options and subscribers (recommended).', $this->plugin->text_domain).'</option>'."\n";
				echo '                     <option value="1"'.selected($this->plugin->options['uninstall_on_deletion'], '1', FALSE).'>'.sprintf(__('Yes, uninstall (completely erase) %1$s on plugin deletion.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</option>'."\n";
				echo '                  </select></p>'."\n";
				echo '            </div>'."\n";
				echo '         </div>'."\n";

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
			protected function subscribers()
			{
				echo '<div id="'.esc_attr($this->plugin->slug.'-menu-page-table').'" class="'.esc_attr($this->plugin->slug.'-menu-page-subscribers '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->current_page_nonce_only()).'">'."\n";

				echo '      <h2>'.sprintf(__('%1$s™ ⥱ Subscribers', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="dashicons dashicons-groups" style="font-size:inherit; line-height:inherit;"></i></h2>'."\n";
				new subs_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function queue()
			{
				echo '<div id="'.esc_attr($this->plugin->slug.'-menu-page-table').'" class="'.esc_attr($this->plugin->slug.'-menu-page-queue '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->current_page_nonce_only()).'">'."\n";

				echo '      <h2>'.sprintf(__('%1$s™ ⥱ Queued Notifications', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="dashicons dashicons-email" style="font-size:inherit; line-height:inherit;"></i></h2>'."\n";
				new queue_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Constructs menu page heading.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $title Title of this menu page.
			 * @param string $icon Icon for this menu page; e.g. `options.png`.
			 *
			 * @return string The heading for this menu page.
			 */
			protected function heading($title, $icon)
			{
				$heading = ''; // Initialize heading.
				$title   = (string)$title; // Force string.
				$icon    = (string)$icon; // Force string.

				$heading .= '<div class="pmp-heading">'."\n";

				$heading .= '  <button type="button" class="pmp-restore-defaults"'. // Restores default options.
				            '     data-pmp-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure?', $this->plugin->text_domain)).'"'.
				            '     data-pmp-action="'.esc_attr($this->plugin->utils_url->restore_default_options()).'">'.
				            '     '.__('Restore', $this->plugin->text_domain).' <i class="fa fa-ambulance"></i>'.
				            '  </button>'."\n";

				$heading .= '  <div class="pmp-panel-togglers" title="'.esc_attr(__('All Panels', $this->plugin->text_domain)).'">'."\n";
				$heading .= '     <button type="button" class="pmp-panels-open"><i class="fa fa-chevron-down"></i></button>'."\n";
				$heading .= '     <button type="button" class="pmp-panels-close"><i class="fa fa-chevron-up"></i></button>'."\n";
				$heading .= '  </div>'."\n";

				$heading .= '  <div class="pmp-upsells">'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->pro_preview()).'"><i class="fa fa-eye"></i> Preview Pro Features</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->subscribe_page()).'" target="_blank"><i class="fa fa-envelope"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '  </div>'."\n";

				$heading .= '  <img src="'.$this->plugin->utils_url->to('/client-s/images/'.$icon).'" alt="'.esc_attr($title).'" style="max-width:400px;" />'."\n";

				$heading .= '</div>'."\n";

				return $heading; // Menu page heading.
			}

			/**
			 * Constructs menu page notices.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The notices for this menu page.
			 */
			protected function notices()
			{
				$notices = ''; // Initialize notices.

				if($this->plugin->utils_env->is_options_updated())
				{
					$notices .= '<div class="pmp-notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Options updated successfully.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_options_restored())
				{
					$notices .= '<div class="pmp-notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Default options successfully restored.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if($this->plugin->utils_env->is_pro_preview())
				{
					$notices .= '<div class="pmp-info">'."\n";
					$notices .= '  <a href="'.esc_attr($this->plugin->utils_url->current_page_only()).'" style="float:right; margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notices .= '  <i class="fa fa-eye"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notices .= '  '.sprintf(__('<small>NOTE: the free version of %1$s (i.e. this lite version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				if(!$this->plugin->options['enable'] && $this->plugin->utils_env->is_menu_page(__NAMESPACE__))
				{
					$notices .= '<div class="pmp-warning">'."\n";
					$notices .= '  <i class="fa fa-warning"></i> '.sprintf(__('%1$s is currently disabled; please review options below.', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				return $notices; // All notices; if any apply.
			}
		}
	}
}