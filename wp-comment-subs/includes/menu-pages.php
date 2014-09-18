<?php
/**
 * Menu Pages
 *
 * @package wp_comment_subs\menu_pages
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 2
 */
namespace wp_comment_subs // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/plugin.inc.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_pages'))
	{
		/**
		 * Menu Pages
		 *
		 * @since 14xxxx First documented version.
		 * @package wp_comment_subs\menu_pages
		 */
		class menu_pages // Menu pages.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
			}

			/**
			 * Displays plugin options.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function options()
			{
				echo '<form id="plugin-menu-page" class="plugin-menu-page" method="post" enctype="multipart/form-data" action="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, '_wpnonce' => wp_create_nonce())), self_admin_url('/admin.php'))).'">'."\n";

				echo '   <div class="plugin-menu-page-heading">'."\n";

				echo '      <button type="button" class="plugin-menu-page-restore-defaults"'. // Restores default options.
				     '         data-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure about this?', $this->plugin->text_domain)).'"'.
				     '         data-action="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, '_wpnonce' => wp_create_nonce(), __NAMESPACE__ => array('restore_default_options' => '1'))), self_admin_url('/admin.php'))).'">'.
				     '         '.__('Restore', $this->plugin->text_domain).' <i class="fa fa-ambulance"></i></button>'."\n";

				echo '      <div class="plugin-menu-page-panel-togglers" title="'.esc_attr(__('All Panels', $this->plugin->text_domain)).'">'."\n";
				echo '         <button type="button" class="plugin-menu-page-panels-open"><i class="fa fa-chevron-down"></i></button>'."\n";
				echo '         <button type="button" class="plugin-menu-page-panels-close"><i class="fa fa-chevron-up"></i></button>'."\n";
				echo '      </div>'."\n";

				echo '      <div class="plugin-menu-page-upsells">'."\n";
				echo '         <a href="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, __NAMESPACE__.'_pro_preview' => '1')), self_admin_url('/admin.php'))).'"><i class="fa fa-eye"></i> Preview Pro Features</a>'."\n";
				echo '         <a href="'.esc_attr('http://www.websharks-inc.com/product/'.str_replace('_', '-', __NAMESPACE__).'/').'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
				echo '         <a href="'.esc_attr('http://www.websharks-inc.com/r/'.str_replace('_', '-', __NAMESPACE__).'-subscribe/').'" target="_blank"><i class="fa fa-envelope"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				echo '      </div>'."\n";

				echo '      <img src="'.$this->plugin->url('/client-s/images/options.png').'" alt="'.esc_attr(__('Plugin Options', $this->plugin->text_domain)).'" />'."\n";

				echo '   </div>'."\n";

				if(!empty($_REQUEST[__NAMESPACE__.'__updated'])) // Options updated successfully?
				{
					echo '<div class="plugin-menu-page-notice notice">'."\n";
					echo '   <i class="fa fa-thumbs-up"></i> '.__('Options updated successfully.', $this->plugin->text_domain)."\n";
					echo '</div>'."\n";
				}
				if(!empty($_REQUEST[__NAMESPACE__.'__restored'])) // Restored default options?
				{
					echo '<div class="plugin-menu-page-notice notice">'."\n";
					echo '   <i class="fa fa-thumbs-up"></i> '.__('Default options successfully restored.', $this->plugin->text_domain)."\n";
					echo '</div>'."\n";
				}
				if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
				{
					echo '<div class="plugin-menu-page-notice info">'."\n";
					echo '   <a href="'.add_query_arg(urlencode_deep(array('page' => __NAMESPACE__)), self_admin_url('/admin.php')).'" class="pull-right" style="margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					echo '      <i class="fa fa-eye"></i> '.__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="'.esc_attr('http://www.websharks-inc.com/product/'.str_replace('_', '-', __NAMESPACE__).'/').'" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.<br /><small>NOTE: the free version of WP Comment Subs (this LITE version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain)."\n";
					echo '</div>'."\n";
				}
				if(!$this->plugin->options['enable']) // Not enabled yet?
				{
					echo '<div class="plugin-menu-page-notice warning">'."\n";
					echo '   <i class="fa fa-warning"></i> '.__('WP Comment Subs is currently disabled; please review options below.', $this->plugin->text_domain)."\n";
					echo '</div>'."\n";
				}
				echo '   <div class="plugin-menu-page-body">'."\n";

				echo '      <div class="plugin-menu-page-panel">'."\n";

				echo '         <a href="#" class="plugin-menu-page-panel-heading'.((!$this->plugin->options['enable']) ? ' open' : '').'">'."\n";
				echo '            <i class="fa fa-flag"></i> '.__('Enable/Disable', $this->plugin->text_domain)."\n";
				echo '         </a>'."\n";

				echo '         <div class="plugin-menu-page-panel-body'.((!$this->plugin->options['enable']) ? ' open' : '').' clearfix">'."\n";
				echo '            <p><label class="switch-primary"><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="1"'.checked($this->plugin->options['enable'], '1', FALSE).' /> <i class="fa fa-magic fa-flip-horizontal"></i> '.__('Yes, enable WP Comment Subs!', $this->plugin->text_domain).'</label> &nbsp;&nbsp;&nbsp; <label><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="0"'.checked($this->plugin->options['enable'], '0', FALSE).' /> '.__('No, disable.', $this->plugin->text_domain).'</label></p>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";

				echo '      <div class="plugin-menu-page-save">'."\n";
				echo '         <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '      </div>'."\n";

				echo '   </div>'."\n";
				echo '</form>';
			}
		}
	}
}