<?php
/**
 * Comment Form After
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_form_after'))
	{
		/**
		 * Comment Form After
		 *
		 * @since 141111 First documented version.
		 */
		class comment_form_after extends abs_base
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

				if(!$this->plugin->options['comment_form_sub_template_enable'])
					return; // Disabled currently.

				if(empty($GLOBALS['post']) || !($GLOBALS['post'] instanceof \WP_Post))
					return; // Not possible here.

				$post_id = $GLOBALS['post']->ID; // Current post ID.

				$current_info = // Current info; for this post ID.
					$this->plugin->utils_sub->current_email_latest_info(
						array('post_id' => $post_id, 'comment_form_defaults' => TRUE)
					);
				// @TODO What if they have a subscription, but not on this post?
				$current      = (object)array(
					'sub_email'   => $current_info->email,
					'sub_type'    => $current_info->type,
					'sub_deliver' => $current_info->deliver,
				);
				unset($current_info); // Ditch this now.

				$sub_email   = $current->sub_email;
				$sub_type    = $current->sub_type;
				$sub_deliver = $current->sub_deliver;

				$sub_type_id   = str_replace('_', '-', __NAMESPACE__.'_sub_type');
				$sub_type_name = __NAMESPACE__.'_sub_type';

				$sub_deliver_id   = str_replace('_', '-', __NAMESPACE__.'_sub_deliver');
				$sub_deliver_name = __NAMESPACE__.'_sub_deliver';

				$sub_summary_url = $this->plugin->utils_url->sub_manage_summary_url();
				$sub_new_url     = $this->plugin->utils_url->sub_manage_sub_new_url(NULL, NULL, compact('post_id'));
				$inline_icon_svg = $this->plugin->utils_fs->inline_icon_svg();

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new template('site/comment-form/sub-ops.php');

				echo $template->parse($template_vars);
			}
		}
	}
}