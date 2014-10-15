<?php
/**
 * Post Small Meta Box
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\post_small_meta_box'))
	{
		/**
		 * Post Small Meta Box
		 *
		 * @since 14xxxx First documented version.
		 */
		class post_small_meta_box extends abstract_base
		{
			/**
			 * @var \WP_Post A WP post object.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \WP_Post $post A WP post object reference.
			 */
			public function __construct(\WP_Post $post)
			{
				parent::__construct();

				$this->post = $post;

				$this->maybe_display();
			}

			/**
			 * Display meta box.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display()
			{
				$post_comment_status // Translate/standardize this.
					= $this->plugin->utils_db->post_comment_status__($this->post->comment_status);

				if($post_comment_status !== 'open')
					return; // Not applicable.

				$total_subs        = $this->plugin->utils_sub->query_total($this->post->ID);
				$total_subs_bubble = $this->plugin->utils_markup->subscriber_count($this->post->ID, $total_subs);

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-area').'">'.
				     '   <h4 style="margin-bottom:5px;">'.$total_subs_bubble.__('Most Recent Subscribers:', $this->plugin->text_domain).'</h4>'.
				     '   '.$this->plugin->utils_markup->last_x_subs(5, $this->post->ID).
				     '</div>';
			}
		}
	}
}