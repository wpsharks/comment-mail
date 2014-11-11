<?php
/**
 * Post Small Meta Box
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class post_small_meta_box extends abs_base
		{
			/**
			 * @var \WP_Post A WP post object.
			 *
			 * @since 141111 First documented version.
			 */
			protected $post;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \WP_Post $post A WP post object reference.
			 */
			public function __construct(\WP_Post $post)
			{
				parent::__construct();

				$this->post = $post;

				$this->display();
			}

			/**
			 * Display meta box.
			 *
			 * @since 141111 First documented version.
			 */
			protected function display()
			{
				$post_comment_status // Translate/standardize this.
					= $this->plugin->utils_db->post_comment_status__($this->post->comment_status);

				$total_subs        = $this->plugin->utils_sub->query_total($this->post->ID);
				$total_subs_bubble = $this->plugin->utils_markup->subs_count($this->post->ID, $total_subs,
				                                                             array(
					                                                             'subscriptions' => TRUE,
					                                                             'style'         => 'display:block; font-size:1.5em;',
				                                                             ));
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-area').'">'.

				     '  '.$total_subs_bubble. // In block format; i.e. 100% width.

				     '   <h4 style="margin:1em 0 .25em 0;">'.__('Most Recent Subscriptions', $this->plugin->text_domain).'</h4>'.
				     '   '.$this->plugin->utils_markup->last_x_subs(5, $this->post->ID, array('group_by_email' => TRUE)).

				     '</div>';

				if($post_comment_status !== 'open' && !$this->post->comment_count)
					return; // For future implementation.
			}
		}
	}
}