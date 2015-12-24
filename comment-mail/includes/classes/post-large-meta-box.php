<?php
/**
 * Post Large Meta Box
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\post_large_meta_box'))
	{
		/**
		 * Post Large Meta Box
		 *
		 * @since 141111 First documented version.
		 */
		class post_large_meta_box extends abs_base
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
			 * Display meta box. @TODO
			 *
			 * @since 141111 First documented version.
			 */
			protected function display()
			{
				$post_comment_status // Translate/standardize this.
					= $this->plugin->utils_db->post_comment_status__($this->post->comment_status);

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-area').'">'."\n";
				echo __('Coming soon...', $this->plugin->text_domain);
				echo '</div>';

				if($post_comment_status !== 'open' && !$this->post->comment_count)
					return; // For future implementation.
			}
		}
	}
}