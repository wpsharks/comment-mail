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
			 * @param string  $name Full name to format.
			 * @param string  $email Email adddress to format.
			 *
			 * @param string  $separator Optional name/email separator.
			 *    This defaults to a ` ` single space.
			 *
			 * @param boolean $force_separator Force the separator?
			 *
			 * @param boolean $span_title Wrap with a `<span title=""`>?
			 *    i.e. Hovering reveals full `"name" <email>`.
			 *
			 * @param string  $name_style Any extra styles for the name.
			 * @param string  $email_style Any extra styles for the email address.
			 *
			 * @return string HTML markup for a "name" <email>; also mid-clipped automatically.
			 */
			public function name_email($name = '', $email = '', $separator = ' ', $force_separator = FALSE, $span_title = TRUE, $name_style = '', $email_style = '')
			{
				$name      = $this->plugin->utils_string->clean_name($name);
				$email     = (string)$email; // Force string.
				$separator = (string)$separator; // Force string.

				return ($span_title // Wrap with a `<span title=""`>?
					? '<span title="'.esc_attr(($name ? '"'.str_replace('"', '', $name).'"' : '').
					                           ($name && $email ? ' ' : ''). // Need separator?
					                           ($email ? '<'.$email.'>' : '')).'">' : '').

				       ($name ? '<span style="'.esc_attr($name_style).'">"'.esc_html($this->plugin->utils_string->mid_clip(str_replace('"', '', $name))).'"</span>' : '').
				       ($name && $email ? $separator : ''). // Need separator here? This defaults to a single ` ` space.
				       ($email ? '&lt;<a href="mailto:'.esc_attr(urlencode($email)).'" style="'.esc_attr($email_style).'">'.
				                 '   '.esc_html($this->plugin->utils_string->mid_clip($email)).
				                 '</a>&gt;' : '').
				       ($force_separator && (!$name || !$email) ? $separator : ''). // Force separator?

				       ($span_title ? // Close span?
					       '</span>' : '');
			}

			/**
			 * Comment count bubble.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id The post ID.
			 * @param integer $post_total_comments Total comments.
			 * @param string  $style Any extra style attributes (optional).
			 *    This defaults to an empty string.
			 *
			 * @return string HTML markup for a post comment count bubble.
			 */
			public function comment_count($post_id, $post_total_comments, $style = '')
			{
				$post_id             = (integer)$post_id; // Force integer.
				$post_total_comments = (integer)$post_total_comments; // Force integer.
				$style               = (string)$style; // Style; force string.

				$post_total_comments_desc = sprintf(_n('%1$s Comment', '%1$s Comments',
				                                       $post_total_comments, $this->plugin->text_domain), esc_html($post_total_comments));
				$post_edit_comments_url   = $this->plugin->utils_url->post_edit_comments_short($post_id);

				return '<a href="'.esc_attr($post_edit_comments_url).'" class="post-com-count" style="'.esc_attr($style).'" title="'.esc_attr($post_total_comments_desc).'">'.
				       '  <span class="comment-count">'.esc_html($post_total_comments).'</span>'.
				       '</a>';
			}

			/**
			 * Subscriber count bubble.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id The post ID.
			 * @param integer $post_total_subscribers Total subscribers.
			 * @param string  $style Any extra style attributes (optional).
			 *    This defaults to an empty string.
			 *
			 * @return string HTML markup for a post subscriber count bubble.
			 */
			public function subscriber_count($post_id, $post_total_subscribers, $style = '')
			{
				$post_id                = (integer)$post_id; // Force integer.
				$post_total_subscribers = (integer)$post_total_subscribers; // Force integer.
				$style                  = (string)$style; // Style; force string.

				$post_total_subscribers_desc = sprintf(_n('%1$s Subscriber', '%1$s Subscribers',
				                                          $post_total_subscribers, $this->plugin->text_domain), esc_html($post_total_subscribers));
				$post_edit_subscribers_url   = $this->plugin->utils_url->post_edit_subscribers_short($post_id);

				return '<a href="'.esc_attr($post_edit_subscribers_url).'" class="post-sub-count" style="'.esc_attr($style).'" title="'.esc_attr($post_total_subscribers_desc).'">'.
				       '  <span class="subscriber-count">'.esc_html($post_total_subscribers).'</span>'.
				       '</a>';
			}
		}
	}
}