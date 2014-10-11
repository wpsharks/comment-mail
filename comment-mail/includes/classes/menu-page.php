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
			public function options()
			{
				echo '<form id="plugin-menu-page" class="plugin-menu-page" method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->current_page_nonce_only()).'">'."\n";

				echo $this->heading(__('Plugin Options', $this->plugin->text_domain), 'options.png').$this->notices(); // Heading/notices.

				echo '   <div class="plugin-menu-page-body">'."\n";

				echo '      <div class="plugin-menu-page-panel">'."\n";

				echo '         <a href="#" class="plugin-menu-page-panel-heading'.((!$this->plugin->options['enable']) ? ' open' : '').'">'."\n";
				echo '            <i class="fa fa-flag"></i> '.__('Enable/Disable', $this->plugin->text_domain)."\n";
				echo '         </a>'."\n";

				echo '         <div class="plugin-menu-page-panel-body'.((!$this->plugin->options['enable']) ? ' open' : '').' clearfix">'."\n";
				echo '            <p><label class="switch-primary"><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="1"'.checked($this->plugin->options['enable'], '1', FALSE).' /> <i class="fa fa-magic fa-flip-horizontal"></i> '.sprintf(__('Yes, enable %1$s™', $this->plugin->text_domain), esc_html($this->plugin->name)).'</label> &nbsp;&nbsp;&nbsp; <label><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="0"'.checked($this->plugin->options['enable'], '0', FALSE).' /> '.__('No, disable.', $this->plugin->text_domain).'</label></p>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";

				echo '      <div class="plugin-menu-page-save">'."\n";
				echo '         <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '      </div>'."\n";

				echo '   </div>'."\n";
				echo '</form>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function subscribers()
			{
				echo '<form id="plugin-menu-page" class="plugin-menu-page" method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->current_page_nonce_only()).'">'."\n";

				echo $this->heading(__('Comment Subscribers', $this->plugin->text_domain), 'subscribers.png').$this->notices(); // Heading/notices.

				echo '   <div class="plugin-menu-page-body">'."\n";

				echo '      <div class="plugin-menu-page-panel">'."\n";

				echo '         <a href="#" class="plugin-menu-page-panel-heading'.((!$this->plugin->options['enable']) ? ' open' : '').'">'."\n";
				echo '            <i class="fa fa-flag"></i> '.__('Comment Subscribers', $this->plugin->text_domain)."\n";
				echo '         </a>'."\n";

				echo '         <div class="plugin-menu-page-panel-body open clearfix">'."\n";
				new subs_table(); // Subscribers table.
				echo '         </div>'."\n";

				echo '      </div>'."\n";

				echo '   </div>'."\n";
				echo '</form>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function queue()
			{
				echo '<form id="plugin-menu-page" class="plugin-menu-page" method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->current_page_nonce_only()).'">'."\n";

				echo $this->heading(__('Queued Notifications', $this->plugin->text_domain), 'queue.png').$this->notices(); // Heading/notices.

				echo '   <div class="plugin-menu-page-body">'."\n";

				echo '      <div class="plugin-menu-page-panel">'."\n";

				echo '         <a href="#" class="plugin-menu-page-panel-heading'.((!$this->plugin->options['enable']) ? ' open' : '').'">'."\n";
				echo '            <i class="fa fa-flag"></i> '.__('Queued Notifications', $this->plugin->text_domain)."\n";
				echo '         </a>'."\n";

				echo '         <div class="plugin-menu-page-panel-body open clearfix">'."\n";
				new queue_table(); // Queue table.
				echo '         </div>'."\n";

				echo '      </div>'."\n";

				echo '   </div>'."\n";
				echo '</form>';
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
				$heading = '<div class="plugin-menu-page-heading">'."\n";

				$heading .= '  <button type="button" class="plugin-menu-page-restore-defaults"'. // Restores default options.
				            '     data-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure?', $this->plugin->text_domain)).'"'.
				            '     data-action="'.esc_attr($this->plugin->utils_url->restore_default_options()).'">'.
				            '     '.__('Restore', $this->plugin->text_domain).' <i class="fa fa-ambulance"></i>'.
				            '  </button>'."\n";

				$heading .= '  <div class="plugin-menu-page-panel-togglers" title="'.esc_attr(__('All Panels', $this->plugin->text_domain)).'">'."\n";
				$heading .= '     <button type="button" class="plugin-menu-page-panels-open"><i class="fa fa-chevron-down"></i></button>'."\n";
				$heading .= '     <button type="button" class="plugin-menu-page-panels-close"><i class="fa fa-chevron-up"></i></button>'."\n";
				$heading .= '  </div>'."\n";

				$heading .= '  <div class="plugin-menu-page-upsells">'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->pro_preview($this->plugin->utils_url->current_page_only())).'"><i class="fa fa-eye"></i> Preview Pro Features</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->subscribe_page()).'" target="_blank"><i class="fa fa-envelope"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '  </div>'."\n";

				$heading .= '  <img src="'.$this->plugin->utils_url->to('/client-s/images/'.$icon).'" alt="'.esc_attr($title).'" style="max-width:400px;" />'."\n";

				$heading .= '</div>'."\n";
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

				if(!empty($_REQUEST[__NAMESPACE__.'__updated'])) // Options updated successfully?
				{
					$notices .= '<div class="plugin-menu-page-notice notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Options updated successfully.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if(!empty($_REQUEST[__NAMESPACE__.'__restored'])) // Restored default options?
				{
					$notices .= '<div class="plugin-menu-page-notice notice">'."\n";
					$notices .= '  <i class="fa fa-thumbs-up"></i> '.__('Default options successfully restored.', $this->plugin->text_domain)."\n";
					$notices .= '</div>'."\n";
				}
				if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
				{
					$notices .= '<div class="plugin-menu-page-notice info">'."\n";
					$notices .= '  <a href="'.add_query_arg($this->plugin->utils_url->current_page_only()).'" class="pull-right" style="margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notices .= '  <i class="fa fa-eye"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notices .= '  '.sprintf(__('<small>NOTE: the free version of %1$s (i.e. this lite version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				if(!$this->plugin->options['enable']) // Not enabled yet?
				{
					$notices .= '<div class="plugin-menu-page-notice warning">'."\n";
					$notices .= '  <i class="fa fa-warning"></i> '.sprintf(__('%1$s is currently disabled; please review options below.', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notices .= '</div>'."\n";
				}
				return $notices; // All notices; if any apply.
			}
		}
	}
}