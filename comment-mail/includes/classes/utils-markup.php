<?php
/**
 * Markup Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_markup'))
	{
		/**
		 * Markup Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_markup extends abstract_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Mid-clips a string to X chars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $name Full name to format.
			 * @param string $email Email adddress to format.
			 * @param array  $args Any additional style-related arguments.
			 *
			 * @return string HTML markup for a "name" <email>; also mid-clipped automatically.
			 */
			public function name_email($name = '', $email = '', array $args = array())
			{
				$name  = (string)$name;
				$email = (string)$email;

				$default_args        = array(
					'separator'       => ' ',
					'force_separator' => FALSE,
					'span_title'      => TRUE,
					'name_style'      => '',
					'email_style'     => '',
					'anchor'          => TRUE,
				);
				$args                = array_merge($default_args, $args);
				$args['separator']   = (string)$args['separator'];
				$args['name_style']  = (string)$args['name_style'];
				$args['email_style'] = (string)$args['email_style'];

				$name            = $name ? $this->plugin->utils_string->clean_name($name) : '';
				$name_clip       = $name ? $this->plugin->utils_string->mid_clip($name) : '';
				$email_clip      = $email ? $this->plugin->utils_string->mid_clip($email) : '';
				$full_name_email = ($name ? '"'.$name.'"' : '').($name && $email ? ' ' : '').($email ? '<'.$email.'>' : '');
				$name_span       = $name ? '<span style="'.esc_attr($args['name_style']).'">"'.esc_html($name_clip).'"</span>' : '';
				$email_anchor    = $email ? '<a href="mailto:'.esc_attr(urlencode($email)).'" style="'.esc_attr($args['email_style']).'">'.esc_html($email_clip).'</a>' : '';

				return ($args['span_title']
					? '<span title="'.esc_attr($full_name_email).'">' : '').

				       ($name ? $name_span : '').
				       ($name && $email ? $args['separator'] : '').
				       ($email ? '&lt;'.($args['anchor'] ? $email_anchor : esc_html($email_clip)).'&gt;' : '').
				       ($args['force_separator'] && (!$name || !$email) ? $args['separator'] : '').

				       ($args['span_title']
					       ? '</span>'
					       : '');
			}

			/**
			 * Comment count bubble.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id The post ID.
			 * @param integer $post_total_comments Total comments.
			 * @param array   $args Any additional style-related arguments.
			 *
			 * @return string HTML markup for a post comment count bubble.
			 */
			public function comment_count($post_id, $post_total_comments, array $args = array())
			{
				$post_id             = (integer)$post_id;
				$post_total_comments = (integer)$post_total_comments;

				$default_args  = array('style' => 'float:right; margin-left:5px;');
				$args          = array_merge($default_args, $args);
				$args['style'] = (string)$args['style'];

				$post_total_comments_desc = sprintf(_n('%1$s Comment', '%1$s Comments',
				                                       $post_total_comments, $this->plugin->text_domain), esc_html($post_total_comments));
				$post_edit_comments_url   = $this->plugin->utils_url->post_edit_comments_short($post_id);

				return '<a href="'.esc_attr($post_edit_comments_url).'" class="pmp-post-com-count post-com-count" style="'.esc_attr($args['style']).'" title="'.esc_attr($post_total_comments_desc).'">'.
				       '  <span class="pmp-comment-count comment-count">'.esc_html($post_total_comments).'</span>'.
				       '</a>';
			}

			/**
			 * Subscriber count bubble.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id The post ID.
			 * @param integer $post_total_subscribers Total subscribers.
			 * @param array   $args Any additional style-related arguments.
			 *
			 * @return string HTML markup for a post subscriber count bubble.
			 */
			public function subscriber_count($post_id, $post_total_subscribers, array $args = array())
			{
				$post_id                = (integer)$post_id;
				$post_total_subscribers = (integer)$post_total_subscribers;

				$default_args  = array('style' => 'float:right; margin-left:5px;');
				$args          = array_merge($default_args, $args);
				$args['style'] = (string)$args['style'];

				$post_total_subscribers_desc = sprintf(_n('%1$s Subscriber', '%1$s Subscribers',
				                                          $post_total_subscribers, $this->plugin->text_domain), esc_html($post_total_subscribers));
				$post_edit_subscribers_url   = $this->plugin->utils_url->post_edit_subscribers_short($post_id);

				return '<a href="'.esc_attr($post_edit_subscribers_url).'" class="pmp-post-sub-count" style="'.esc_attr($args['style']).'" title="'.esc_attr($post_total_subscribers_desc).'">'.
				       '  <span class="pmp-subscriber-count">'.esc_html($post_total_subscribers).'</span>'.
				       '</a>';
			}

			/**
			 * Last X subscribers w/ a given status.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer      $x The total number to return.
			 *
			 * @param integer|null $post_id Defaults to a `NULL` value.
			 *    i.e. defaults to any post ID. Pass this to limit the query.
			 *
			 * @param array        $args Any additional style-related arguments.
			 *    Additional arguments to the underlying `last_x()` call go here too.
			 *    Additional arguments to the underlying `name_email()` call go here too.
			 *
			 * @return string Markup for last X subscribers w/ a given status.
			 *
			 * @see utils_sub::last_x()
			 */
			public function last_x_subs($x = 0, $post_id = NULL, array $args = array())
			{
				$last_x_email_lis = array(); // Initialize.

				$default_args         = array(
					'list_style'          => 'margin:0;',
					'anchor_style'        => 'text-decoration:none;',

					'comment_id'          => NULL,
					'status'              => '',
					'auto_discount_trash' => TRUE,
				);
				$args                 = array_merge($default_args, $args);
				$args['list_style']   = (string)$args['list_style'];
				$args['anchor_style'] = (string)$args['anchor_style'];
				$args['status']       = (string)$args['status'];

				foreach($this->plugin->utils_sub->last_x($x, $post_id, // Plus additional args too.
				                                         $args['comment_id'], $args['status'], $args['auto_discount_trash']) as $_sub)
					$last_x_email_lis[] = '<li>'. // This is linked up to an edit URL; so the site owner can see more.
					                      ' <a href="'.esc_attr($this->plugin->utils_url->edit_subscriber_short($_sub->ID)).'" style="'.esc_attr($args['anchor_style']).'">'.
					                      ' <i class="fa fa-user"></i> '.$this->name_email('', $_sub->email, array('anchor' => FALSE)).'</a>'.
					                      '</li>';
				unset($_sub); // Just a little housekeeping.

				if(!$last_x_email_lis) // If no results, add a no subscribers message.
					$last_x_email_lis[] = '<li style="font-style:italic;">'.
					                      ' '.__('No subscribers at this time.', $this->plugin->text_domain).
					                      '</li>';

				return '<ul class="pmp-last-x-sub-emails pmp-clean-list-items" style="'.esc_attr($args['list_style']).'">'.
				       '  '.implode('', $last_x_email_lis).
				       '</ul>';
			}
		}
	}
}