<?php
/**
 * Post Large Meta Box
 *
 * @since 14xxxx First documented version.
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
		 * @since 14xxxx First documented version.
		 */
		class post_large_meta_box extends abstract_base
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
			 *
			 * @TODO
			 */
			protected function maybe_display()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-area').'">'."\n";
				new subs_table();
				echo '</div>';
			}

			/**
			 * Total subscribers bubble.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function subscriber_count()
			{
				$total_subscribers = $this->plugin->utils_sub->query_total($this->post->ID);

				return $this->plugin->utils_markup->subscriber_count($this->post->ID, $total_subscribers);
			}
		}
	}
}