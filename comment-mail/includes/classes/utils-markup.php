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
		class utils_markup extends abs_base
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
				       '  <span class="pmp-com-count comment-count">'.esc_html($post_total_comments).'</span>'.
				       '</a>';
			}

			/**
			 * Subscription count bubble.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $post_id The post ID.
			 * @param integer $post_total_subs Total subscriptions.
			 * @param array   $args Any additional style-related arguments.
			 *
			 * @return string HTML markup for a post subscription count bubble.
			 */
			public function subs_count($post_id, $post_total_subs, array $args = array())
			{
				$post_id         = (integer)$post_id;
				$post_total_subs = (integer)$post_total_subs;

				$default_args  = array('style' => 'float:right; margin-left:5px;');
				$args          = array_merge($default_args, $args);
				$args['style'] = (string)$args['style'];

				$post_total_subs_desc = sprintf(_n('%1$s Subscription', '%1$s Subscriptions',
				                                   $post_total_subs, $this->plugin->text_domain), esc_html($post_total_subs));
				$post_edit_subs_url   = $this->plugin->utils_url->post_edit_subs_short($post_id);

				return '<a href="'.esc_attr($post_edit_subs_url).'" class="pmp-post-sub-count" style="'.esc_attr($args['style']).'" title="'.esc_attr($post_total_subs_desc).'">'.
				       '  <span class="pmp-sub-count">'.esc_html($post_total_subs).'</span>'.
				       '</a>';
			}

			/**
			 * Last X subscriptions w/ a given status.
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
			 * @return string Markup for last X subscriptions w/ a given status.
			 *
			 * @see utils_sub::last_x()
			 */
			public function last_x_subs($x = 0, $post_id = NULL, array $args = array())
			{
				$last_x_email_lis = array(); // Initialize.

				$default_args = array(
					'list_style'          => 'margin:0;',
					'anchor_style'        => 'text-decoration:none;',

					'status'              => '',
					'comment_id'          => NULL,
					'auto_discount_trash' => TRUE,
					'group_by_email'      => TRUE,
					'no_cache'            => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$list_style   = (string)$args['list_style'];
				$anchor_style = (string)$args['anchor_style'];

				foreach($this->plugin->utils_sub->last_x($x, $post_id, $args) as $_sub)
					$last_x_email_lis[] = '<li>'. // This is linked up to an edit URL; so the site owner can see more.
					                      ' <a href="'.esc_attr($this->plugin->utils_url->edit_sub_short($_sub->ID)).'" style="'.esc_attr($anchor_style).'">'.
					                      ' <i class="fa fa-user"></i> '.$this->name_email('', $_sub->email, array('anchor' => FALSE)).'</a>'.
					                      '</li>';
				unset($_sub); // Just a little housekeeping.

				if(!$last_x_email_lis) // If no results, add a no subscriptions message.
					$last_x_email_lis[] = '<li style="font-style:italic;">'.
					                      ' '.__('No subscriptions at this time.', $this->plugin->text_domain).
					                      '</li>';

				return '<ul class="pmp-last-x-sub-emails pmp-clean-list-items" style="'.esc_attr($list_style).'">'.
				       '  '.implode('', $last_x_email_lis).
				       '</ul>';
			}

			/**
			 * Markup for user select menu options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|null $current_user_id Current user ID.
			 *
			 * @param array        $args Any additional style-related arguments.
			 *    Additional arguments to the underlying `all_users()` call go here too.
			 *
			 * @return string Markup for user select menu options.
			 *    This returns an empty string if there are no users (or too many users);
			 *    i.e. an input field should be used instead of a select menu.
			 *
			 * @see utils_db::all_users()
			 */
			public function user_select_options($current_user_id = NULL, array $args = array())
			{
				$selected_user_id = NULL; // Initialize.
				$current_user_id  = isset($current_user_id)
					? (integer)$current_user_id : NULL;

				$default_args = array(
					'max'         => 1000,
					'fail_on_max' => TRUE,
					'no_cache'    => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($users = $this->plugin->utils_db->all_users($args)))
					return '';  // No options; use input field instead of select menu.

				$options = '<option value="0"></option>'; // Initialize.

				foreach($users as $_user) // Iterate users.
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_user_id) && isset($current_user_id))
						if(($_selected = selected($_user->ID, $current_user_id, FALSE)))
							$selected_user_id = $_user->ID;

					$options .= '<option value="'.esc_attr($_user->ID).'"'.$_selected.'>'.
					            '  '.esc_html(__('User', $this->plugin->text_domain).' ID #'.$_user->ID.
					                          ' :: '.$_user->user_login. // The user's username.
					                          ' :: "'.$_user->display_name.'" <'.$_user->user_email.'>').
					            '</option>';
				}
				unset($_user, $_selected); // Housekeeping.

				if(!isset($selected_user_id) && isset($current_user_id) && $current_user_id > 0)
					$options .= '<option value="'.esc_attr($current_user_id).'" selected="selected">'.
					            '  '.esc_html(__('User', $this->plugin->text_domain).' ID #'.$current_user_id).
					            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for post select menu options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|null $current_post_id Current post ID.
			 *
			 * @param array        $args Any additional style-related arguments.
			 *    Additional arguments to the underlying `all_posts()` call go here too.
			 *
			 * @return string Markup for post select menu options.
			 *    This returns an empty string if there are no posts (or too many posts);
			 *    i.e. an input field should be used instead of a select menu.
			 *
			 * @see utils_db::all_posts()
			 */
			public function post_select_options($current_post_id = NULL, array $args = array())
			{
				$selected_post_id = NULL; // Initialize.
				$current_post_id  = isset($current_post_id)
					? (integer)$current_post_id : NULL;

				$default_args = array(
					'max'               => 1000,
					'fail_on_max'       => TRUE,
					'for_comments_only' => FALSE,
					'no_cache'          => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($posts = $this->plugin->utils_db->all_posts($args)))
					return ''; // No options; use input field instead of select menu.

				$options                 = '<option value="0"></option>'; // Initialize.
				$default_post_type_label = __('Post', $this->plugin->text_domain);

				foreach($posts as $_post) // Iterate posts.
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_post_id) && isset($current_post_id))
						if(($_selected = selected($_post->ID, $current_post_id, FALSE)))
							$selected_post_id = $_post->ID;

					$_post_type_label = $default_post_type_label;
					if(($_post_type = get_post_type_object($_post->post_type)))
						$_post_type_label = $_post_type->labels->singular_name;

					$options .= '<option value="'.esc_attr($_post->ID).'"'.$_selected.'>'.
					            '  '.esc_html($_post_type->labels->singular_name.' ID #'.$_post->ID.
					                          ' :: '.$_post->post_title).
					            '</option>';
				}
				unset($_post, $_selected, $_post_type, $_post_type_label); // Housekeeping.

				if(!isset($selected_post_id) && isset($current_post_id) && $current_post_id > 0)
					$options .= '<option value="'.esc_attr($current_post_id).'" selected="selected">'.
					            '  '.esc_html(__('Post', $this->plugin->text_domain).' ID #'.$current_post_id).
					            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for comment select menu options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer      $post_id A post ID.
			 * @param integer|null $current_comment_id Current comment ID.
			 *
			 * @param array        $args Any additional style-related arguments.
			 *    Additional arguments to the underlying `all_comments()` call go here too.
			 *
			 * @return string Markup for comment select menu options.
			 *    This returns an empty string if there are no comments (or too many comments);
			 *    i.e. an input field should be used instead of a select menu.
			 *
			 * @see utils_db::all_comments()
			 */
			public function comment_select_options($post_id, $current_comment_id = NULL, array $args = array())
			{
				if(!($post_id = (integer)$post_id))
					return ''; // Not possible.

				$selected_comment_id = NULL; // Initialize.
				$current_comment_id  = isset($current_comment_id)
					? (integer)$current_comment_id : NULL;

				$default_args = array(
					'max'          => 1000,
					'fail_on_max'  => TRUE,
					'parents_only' => FALSE,
					'no_cache'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($comments = $this->plugin->utils_db->all_comments($post_id, $args)))
					return ''; // No options; use input field instead of select menu.

				$options = '<option value="0"></option>'; // Initialize.

				foreach($comments as $_comment) // Iterate comments.
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_comment_id) && isset($current_comment_id))
						if(($_selected = selected($_comment->comment_ID, $current_comment_id, FALSE)))
							$selected_comment_id = $_comment->comment_ID;

					$options .= '<option value="'.esc_attr($_comment->comment_ID).'"'.$_selected.'>'.
					            '  '.esc_html(__('Comment', $this->plugin->text_domain).' ID #'.$_comment->comment_ID.
					                          ' :: "'.$_comment->comment_author.'" <'.$_comment->comment_author_email.'>'.
					                          ' :: '.$this->plugin->utils_date->i18n('M j, Y, g:i a', strtotime($_comment->comment_date_gmt))).
					            '</option>';
				}
				unset($_comment, $_selected); // Just a little housekeeping.

				if(!isset($selected_comment_id) && isset($current_comment_id) && $current_comment_id > 0)
					$options .= '<option value="'.esc_attr($current_comment_id).'" selected="selected">'.
					            '  '.esc_html(__('Comment', $this->plugin->text_domain).' ID #'.$current_comment_id).
					            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for deliver select menu options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string|null $current_deliver Current delivery option.
			 * @param array       $args Any additional style-related arguments.
			 *
			 * @return string Markup for deliver select menu options.
			 *
			 * @see utils_i18n::deliver_label()
			 */
			public function deliver_select_options($current_deliver = NULL, array $args = array())
			{
				$selected_deliver = NULL; // Initialize.
				$current_deliver  = isset($current_deliver)
					? (string)$current_deliver : NULL;

				$default_args = array();
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$options = '<option value=""></option>'; // Initialize.

				foreach(array(
					        'asap'   => $this->plugin->utils_i18n->deliver_label('asap'),
					        'hourly' => $this->plugin->utils_i18n->deliver_label('hourly'),
					        'daily'  => $this->plugin->utils_i18n->deliver_label('daily'),
					        'weekly' => $this->plugin->utils_i18n->deliver_label('weekly'),
				        ) as $_deliver_option => $_deliver_label)
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_deliver) && isset($current_deliver))
						if(($_selected = selected($_deliver_option, $current_deliver, FALSE)))
							$selected_deliver = $_deliver_option;

					$options .= '<option value="'.esc_attr($_deliver_option).'"'.$_selected.'>'.
					            '  '.esc_html($_deliver_label).
					            '</option>';
				}
				unset($_deliver_option, $_deliver_label, $_selected); // Housekeeping.

				if(!isset($selected_deliver) && isset($current_deliver) && $current_deliver)
					$options .= '<option value="'.esc_attr($current_deliver).'" selected="selected">'.
					            '  '.esc_html($current_deliver).
					            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for status select menu options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string|null $current_status Current status.
			 * @param array       $args Any additional style-related arguments.
			 *
			 * @return string Markup for status select menu options.
			 *
			 * @see utils_i18n::status_label()
			 */
			public function status_select_options($current_status = NULL, array $args = array())
			{
				$selected_status = NULL; // Initialize.
				$current_status  = isset($current_status)
					? (string)$current_status : NULL;

				$default_args = array();
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$options = '<option value=""></option>'; // Initialize.

				foreach(array(
					        'unconfirmed' => $this->plugin->utils_i18n->status_label('unconfirmed'),
					        'subscribed'  => $this->plugin->utils_i18n->status_label('subscribed'),
					        'suspended'   => $this->plugin->utils_i18n->status_label('suspended'),
					        'trashed'     => $this->plugin->utils_i18n->status_label('trashed'),
				        ) as $_status_option => $_status_label)
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_status) && isset($current_status))
						if(($_selected = selected($_status_option, $current_status, FALSE)))
							$selected_status = $_status_option;

					$options .= '<option value="'.esc_attr($_status_option).'"'.$_selected.'>'.
					            '  '.esc_html($_status_label).
					            '</option>';
				}
				unset($_status_option, $_status_label, $_selected); // Housekeeping.

				if(!isset($selected_status) && isset($current_status) && $current_status)
					$options .= '<option value="'.esc_attr($current_status).'" selected="selected">'.
					            '  '.esc_html($current_status).
					            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for select menu options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array       $options Associative array.
			 *    Keys are option values; values are labels.
			 *
			 * @param string|null $current_value The current value.
			 *
			 * @param array       $args Any additional style-related arguments.
			 *
			 * @return string Markup for select menu options.
			 */
			public function select_options(array $options, $current_value = NULL, array $args = array())
			{
				$_selected_value = NULL; // Initialize.
				$current_value   = isset($current_value)
					? (string)$current_value : NULL;

				$default_args = array();
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$_options = $options; // Working copy of the options.
				$options  = '<option value=""></option>'; // Initialize.

				foreach($_options as $_option_value => $_option_label)
				{
					$_selected     = ''; // Initialize.
					$_option_value = (string)$_option_value;
					$_option_label = (string)$_option_label;

					if(!isset($_selected_value) && isset($current_value))
						if(($_selected = selected($_option_value, $current_value, FALSE)))
							$_selected_value = $_option_value;

					$options .= '<option value="'.esc_attr($_option_value).'"'.$_selected.'>'.
					            '  '.esc_html($_option_label).
					            '</option>';
				}
				unset($_option_value, $_option_label, $_selected); // Housekeeping.

				if(!isset($_selected_value) && isset($current_value))
					$options .= '<option value="'.esc_attr($current_value).'" selected="selected">'.
					            '  '.esc_html($current_value).
					            '</option>';

				unset($_options, $_selected_value); // Housekeeping.

				return $options; // HTML markup.
			}
		}
	}
}