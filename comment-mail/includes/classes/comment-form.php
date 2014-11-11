<?php
/**
 * Comment Form
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_form'))
	{
		/**
		 * Comment Form
		 *
		 * @since 141111 First documented version.
		 */
		class comment_form extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_display_sub_ops();
			}

			/**
			 * Display subscription options.
			 *
			 * @since 141111 First documented version.
			 */
			public function maybe_display_sub_ops()
			{
				if(!$this->plugin->options['enable'])
					return; // Disabled currently.

				if(!$this->plugin->options['new_subs_enable'])
					return; // Disabled currently.

				if(!$this->plugin->options['comment_form_template_enable'])
					return; // Disabled currently.

				if(empty($GLOBALS['post']) || !($GLOBALS['post'] instanceof \WP_Post))
					return; // Not possible here.

				$post_id = $GLOBALS['post']->ID; // Current post ID.

				$current // Object w/ `sub_email`, `sub_type`, `sub_deliver`; for this post ID.
					= $this->plugin->utils_sub->current_email_type_deliver_for($post_id, TRUE);

				$sub_email   = $current->sub_email;
				$sub_type    = $current->sub_type; // Note: this can be empty.
				$sub_deliver = $current->sub_deliver;

				$sub_type_id   = str_replace('_', '-', __NAMESPACE__.'_sub_type');
				$sub_type_name = __NAMESPACE__.'_sub_type';

				$sub_deliver_id   = str_replace('_', '-', __NAMESPACE__.'_sub_deliver');
				$sub_deliver_name = __NAMESPACE__.'_sub_deliver';

				$sub_summary_url = $this->plugin->utils_url->sub_manage_summary_url();
				$inline_icon_svg = $this->plugin->utils_fs->inline_icon_svg();

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/comment-form/sub-ops.php');

				echo $template->parse($template_vars);
			}
		}
	}
}