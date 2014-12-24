<?php
/**
 * Markup Utilities
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class utils_markup extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Mid-clips a string to X chars.
			 *
			 * @since 141111 First documented version.
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

				$default_args = array(
					'separator'              => ' ',

					'span_title'             => TRUE,

					'name_style'             => '',
					'email_style'            => '',

					'anchor'                 => TRUE,
					'anchor_to'              => 'mailto',
					// `mailto|search|summary|[custom URL]`.
					'anchor_target'          => '',
					'anchor_summary_sub_key' => '',
				);
				$args         = array_merge($default_args, $args);

				if(!($separator = (string)$args['separator']))
					$separator = ' '; // Must have.

				$span_title = (boolean)$args['span_title'];

				$name_style  = trim((string)$args['name_style']);
				$email_style = trim((string)$args['email_style']);

				$anchor                 = (boolean)$args['anchor'];
				$anchor_to              = trim((string)$args['anchor_to']);
				$anchor_target          = trim((string)$args['anchor_target']);
				$anchor_summary_sub_key = trim((string)$args['anchor_summary_sub_key']);

				$name       = $name ? $this->plugin->utils_string->clean_name($name) : '';
				$name_clip  = $name ? $this->plugin->utils_string->mid_clip($name) : '';
				$email_clip = $email ? $this->plugin->utils_string->mid_clip($email) : '';

				$name_email_attr_value = ($name ? '"'.$name.'"' : '').($name && $email ? ' ' : '').($email ? '<'.$email.'>' : '');
				$name_span_tag         = $name ? '<span style="'.esc_attr($name_style).'">'.esc_html($name_clip).'</span>' : '';

				if($anchor_to === 'search' && $email) // Back-end search?
					$anchor_search_url = $this->plugin->utils_url->search_subs_short('sub_email:'.$email);

				if($anchor_to === 'summary' && !$anchor_summary_sub_key && $email)
					$anchor_summary_sub_key = $this->plugin->utils_sub->email_latest_key($email);

				if($anchor_to === 'summary' && $anchor_summary_sub_key) // Front-end summary?
					$summary_anchor_url = $this->plugin->utils_url->sub_manage_summary_url($anchor_summary_sub_key);

				$mailto_anchor_tag  = $email ? '<a href="mailto:'.esc_attr(urlencode($email)).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';
				$search_anchor_tag  = $email && !empty($anchor_search_url) ? '<a href="'.esc_attr($anchor_search_url).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';
				$summary_anchor_tag = $email && !empty($summary_anchor_url) ? '<a href="'.esc_attr($summary_anchor_url).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';
				$custom_anchor_tag  = $anchor_to ? '<a href="'.esc_attr($anchor_to).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';

				if($anchor_to === 'mailto') $anchor_tag = $mailto_anchor_tag; // e.g. `mailto:email`.
				else if($anchor_to === 'search') $anchor_tag = $search_anchor_tag; // i.e. back-end search.
				else if($anchor_to === 'summary') $anchor_tag = $summary_anchor_tag; // i.e. front-end summary.
				else $anchor_tag = $custom_anchor_tag; // Default behavior; assume a custom URL was given.

				return ($span_title ? '<span title="'.esc_attr($name_email_attr_value).'">' : '').

				       ($name ? $name_span_tag : '').
				       ($name && $email ? $separator : '').
				       ($email ? '&lt;'.($anchor && $anchor_tag ? $anchor_tag : esc_html($email_clip)).'&gt;' : '').

				       ($span_title ? '</span>' : '');
			}

			/**
			 * Comment count bubble.
			 *
			 * @since 141111 First documented version.
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

				$default_args = array(
					'style' => 'float:right; margin-left:5px;'
				);
				$args         = array_merge($default_args, $args);

				$style = (string)$args['style'];

				$post_total_comments_desc = sprintf(_n('%1$s Comment', '%1$s Comments', $post_total_comments, $this->plugin->text_domain), esc_html($post_total_comments));
				$post_edit_comments_url   = $this->plugin->utils_url->post_edit_comments_short($post_id);

				return '<a href="'.esc_attr($post_edit_comments_url).'" class="pmp-post-com-count post-com-count" style="'.esc_attr($style).'" title="'.esc_attr($post_total_comments_desc).'">'.
				       '  <span class="pmp-com-count comment-count">'.esc_html($post_total_comments).'</span>'.
				       '</a>';
			}

			/**
			 * Subscription count bubble.
			 *
			 * @since 141111 First documented version.
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

				$default_args = array(
					'style'         => 'float:right; margin-left:5px;',
					'subscriptions' => FALSE,
				);
				$args         = array_merge($default_args, $args);

				$style         = (string)$args['style'];
				$subscriptions = (boolean)$args['subscriptions'];

				$post_total_subs_label = $subscriptions // What should label contain?
					? $this->plugin->utils_i18n->subscriptions($post_total_subs) : $post_total_subs;

				$post_total_subs_desc = sprintf(_n('%1$s Subscription', '%1$s Subscriptions', $post_total_subs, $this->plugin->text_domain), esc_html($post_total_subs));
				$post_edit_subs_url   = $this->plugin->utils_url->post_edit_subs_short($post_id);

				return '<a href="'.esc_attr($post_edit_subs_url).'" class="pmp-post-sub-count" style="'.esc_attr($style).'" title="'.esc_attr($post_total_subs_desc).'">'.
				       '  <span class="pmp-sub-count">'.esc_html($post_total_subs_label).'</span>'.
				       '</a>';
			}

			/**
			 * Last X subscriptions w/ a given status.
			 *
			 * @since 141111 First documented version.
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
				$default_args = array(
					'offset'                => 0,

					'status'                => '',
					'sub_email'             => '',
					'user_id'               => NULL,
					'comment_id'            => NULL,

					'auto_discount_trash'   => TRUE,
					'sub_email_or_user_ids' => FALSE,
					'group_by_email'        => FALSE,
					'no_cache'              => FALSE,

					'show_fname'            => FALSE,
					'show_lname'            => FALSE,
					'name_email_args'       => array('anchor_to' => 'search'),
					'list_style'            => 'margin:0;',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$show_fname      = (boolean)$args['show_fname'];
				$show_lname      = (boolean)$args['show_lname'];
				$name_email_args = (array)$args['name_email_args'];
				$list_style      = trim((string)$args['list_style']);

				foreach($this->plugin->utils_sub->last_x($x, $post_id, $args) as $_sub)
				{
					$_name_maybe = ''; // Initialize.
					if($show_fname) $_name_maybe .= $_sub->fname;
					if($show_lname) $_name_maybe .= ' '.$_sub->lname;

					$last_x_email_lis[] = '<li>'. // Display varies based on arguments.
					                      ' <i class="'.esc_attr('wsi-'.$this->plugin->slug).'"></i> '.
					                      $this->name_email($_name_maybe, $_sub->email, $name_email_args).'</a>'.
					                      '</li>';
				}
				unset($_sub, $_name_maybe); // Housekeeping.

				if(empty($last_x_email_lis)) // If no results, add a no subscriptions message.
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
			 * @since 141111 First documented version.
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
					'max'             => // Plugin option value.
						(integer)$this->plugin->options['max_select_options'],
					'fail_on_max'     => TRUE,
					'no_cache'        => FALSE,

					'display_emails'  => // Show emails?
						is_admin() && current_user_can('list_users'),
					'allow_empty'     => TRUE,
					'allow_arbitrary' => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$display_emails  = (boolean)$args['display_emails'];
				$allow_empty     = (boolean)$args['allow_empty'];
				$allow_arbitrary = (boolean)$args['allow_arbitrary'];

				if(!is_admin() || !current_user_can('list_users'))
					return ''; // Not permitted to do so.

				if(!$this->plugin->options['user_select_options_enable'])
					return ''; // Use input field instead of options.

				if(!($users = $this->plugin->utils_db->all_users($args)))
					return ''; // Use input field instead of options.

				$options = ''; // Initialize.
				if($allow_empty) // Allow empty selection?
					$options = '<option value="0"></option>';

				foreach($users as $_user) // Iterate users.
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_user_id) && isset($current_user_id))
						if(($_selected = selected($_user->ID, $current_user_id, FALSE)))
							$selected_user_id = $_user->ID;

					$options .= '<option value="'.esc_attr($_user->ID).'"'.$_selected.'>'.
					            '  '.esc_html(__('User', $this->plugin->text_domain).' ID #'.$_user->ID.
					                          ' :: '.$_user->user_login. // The user's username; i.e. what they log in with.
					                          ' :: "'.$_user->display_name.'"'.($display_emails ? ' <'.$_user->user_email.'>' : '')).
					            '</option>';
				}
				unset($_user, $_selected); // Housekeeping.

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!isset($selected_user_id) && isset($current_user_id) && $current_user_id > 0)
						$options .= '<option value="'.esc_attr($current_user_id).'" selected="selected">'.
						            '  '.esc_html(__('User', $this->plugin->text_domain).' ID #'.$current_user_id).
						            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for post select menu options.
			 *
			 * @since 141111 First documented version.
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
					'max'                   => // Plugin option value.
						(integer)$this->plugin->options['max_select_options'],
					'fail_on_max'           => TRUE,
					'for_comments_only'     => FALSE,
					'exclude_post_types'    => array(),
					'exclude_post_statuses' => array(),
					'no_cache'              => FALSE,

					'allow_empty'           => TRUE,
					'allow_arbitrary'       => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$args['exclude_post_types'] = (array)$args['exclude_post_types'];
				if(!$this->plugin->options['post_select_options_media_enable'])
					$args['exclude_post_types'][] = 'attachment';

				$allow_empty     = (boolean)$args['allow_empty'];
				$allow_arbitrary = (boolean)$args['allow_arbitrary'];

				if(!$this->plugin->options['post_select_options_enable'])
					return ''; // Use input field instead of options.

				if(!($posts = $this->plugin->utils_db->all_posts($args)))
					return ''; // Use input field instead of options.

				$options = ''; // Initialize.
				if($allow_empty) // Allow empty selection?
					$options = '<option value="0"></option>';

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

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!isset($selected_post_id) && isset($current_post_id) && $current_post_id > 0)
						$options .= '<option value="'.esc_attr($current_post_id).'" selected="selected">'.
						            '  '.esc_html(__('Post', $this->plugin->text_domain).' ID #'.$current_post_id).
						            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for comment select menu options.
			 *
			 * @since 141111 First documented version.
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
					'max'             => // Plugin option value.
						(integer)$this->plugin->options['max_select_options'],
					'fail_on_max'     => TRUE,
					'parents_only'    => FALSE,
					'no_cache'        => FALSE,

					'display_emails'  => // Show emails?
						is_admin() && current_user_can('moderate_comments'),
					'allow_empty'     => TRUE,
					'allow_arbitrary' => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$display_emails  = (boolean)$args['display_emails'];
				$allow_empty     = (boolean)$args['allow_empty'];
				$allow_arbitrary = (boolean)$args['allow_arbitrary'];

				if(!$this->plugin->options['comment_select_options_enable'])
					return ''; // Use input field instead of options.

				if(!($comments = $this->plugin->utils_db->all_comments($post_id, $args)))
					return ''; // Use input field instead of options.

				$options = ''; // Initialize.
				if($allow_empty) // Allow empty selection?
					$options = '<option value="0"></option>';

				foreach($comments as $_comment) // Iterate comments.
				{
					$_selected = ''; // Initialize.

					if(!isset($selected_comment_id) && isset($current_comment_id))
						if(($_selected = selected($_comment->comment_ID, $current_comment_id, FALSE)))
							$selected_comment_id = $_comment->comment_ID;

					$options .= '<option value="'.esc_attr($_comment->comment_ID).'"'.$_selected.'>'.
					            '  '.esc_html(__('Comment', $this->plugin->text_domain).' ID #'.$_comment->comment_ID.
					                          ($_comment->comment_author ? ' :: '.__('by', $this->plugin->text_domain).' "'.$_comment->comment_author.'"'.($display_emails ? ' <'.$_comment->comment_author_email.'>' : '') : '').
					                          ' :: '.$this->plugin->utils_date->i18n('M j, Y g:i a', strtotime($_comment->comment_date_gmt))).
					            '</option>';
				}
				unset($_comment, $_selected); // Just a little housekeeping.

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!isset($selected_comment_id) && isset($current_comment_id) && $current_comment_id > 0)
						$options .= '<option value="'.esc_attr($current_comment_id).'" selected="selected">'.
						            '  '.esc_html(__('Comment', $this->plugin->text_domain).' ID #'.$current_comment_id).
						            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for deliver select menu options.
			 *
			 * @since 141111 First documented version.
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

				$default_args = array(
					'allow_empty'     => TRUE,
					'allow_arbitrary' => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$allow_empty     = (boolean)$args['allow_empty'];
				$allow_arbitrary = (boolean)$args['allow_arbitrary'];

				$deliver_options_available = array(
					'asap'   => $this->plugin->utils_i18n->deliver_label('asap'),
					'hourly' => $this->plugin->utils_i18n->deliver_label('hourly'),
					'daily'  => $this->plugin->utils_i18n->deliver_label('daily'),
					'weekly' => $this->plugin->utils_i18n->deliver_label('weekly'),
				); // These are hard-coded; i.e. not expected to change.

				$options = ''; // Initialize.
				if($allow_empty) // Allow empty selection?
					$options = '<option value=""></option>';

				foreach($deliver_options_available as $_deliver_option => $_deliver_label)
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

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!isset($selected_deliver) && isset($current_deliver) && $current_deliver)
						$options .= '<option value="'.esc_attr($current_deliver).'" selected="selected">'.
						            '  '.esc_html($current_deliver).
						            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for status select menu options.
			 *
			 * @since 141111 First documented version.
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

				$default_args = array(
					'allow_empty'                   => TRUE,
					'allow_arbitrary'               => TRUE,
					'ui_protected_data_keys_enable' => !is_admin(),
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$allow_empty                   = (boolean)$args['allow_empty'];
				$allow_arbitrary               = (boolean)$args['allow_arbitrary'];
				$ui_protected_data_keys_enable = (boolean)$args['ui_protected_data_keys_enable'];

				$status_options_available = array(
					'unconfirmed' => $this->plugin->utils_i18n->status_label('unconfirmed'),
					'subscribed'  => $this->plugin->utils_i18n->status_label('subscribed'),
					'suspended'   => $this->plugin->utils_i18n->status_label('suspended'),
					'trashed'     => $this->plugin->utils_i18n->status_label('trashed'),
				); // These are hard-coded; i.e. not expected to change.

				if($ui_protected_data_keys_enable) // Front-end UI should limit choices.
					unset($status_options_available['unconfirmed'], $status_options_available['trashed']);

				$options = ''; // Initialize.
				if($allow_empty) // Allow empty selection?
					$options = '<option value=""></option>';

				foreach($status_options_available as $_status_option => $_status_label)
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

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!$ui_protected_data_keys_enable) // Front-end UI limits choices.
						if(!isset($selected_status) && isset($current_status) && $current_status)
							$options .= '<option value="'.esc_attr($current_status).'" selected="selected">'.
							            '  '.esc_html($current_status).
							            '</option>';

				return $options; // HTML markup.
			}

			/**
			 * Markup for select menu options.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array       $given_ops Options array.
			 *    Keys are option values; values are labels.
			 *
			 * @param string|null $current_value The current value.
			 *
			 * @param array       $args Any additional style-related arguments.
			 *
			 * @return string Markup for select menu options.
			 */
			public function select_options(array $given_ops, $current_value = NULL, array $args = array())
			{
				$_selected_value = NULL; // Initialize.
				$current_value   = isset($current_value)
					? (string)$current_value : NULL;

				$default_args = array(
					'allow_arbitrary' => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$allow_arbitrary = (boolean)$args['allow_arbitrary'];

				$options = ''; // Initialize.
				// There is no `$allow_empty` argument in this handler.
				// Note that we do NOT setup a default/empty option value here.
				// If you want to `$allow_empty`, provide an empty option of your own please.

				foreach($given_ops as $_option_value => $_option_label)
				{
					$_selected     = ''; // Initialize.
					$_option_value = (string)$_option_value;
					$_option_label = (string)$_option_label;

					if(stripos($_option_value, '@optgroup_open') === 0)
						$options .= '<optgroup label="'.esc_attr($_option_label).'">';

					else if(stripos($_option_value, '@optgroup_close') === 0)
						$options .= '</optgroup>'; // Close.

					else // Normal behavior; another option value/label.
					{
						if(!isset($_selected_value) && isset($current_value))
							if(($_selected = selected($_option_value, $current_value, FALSE)))
								$_selected_value = $_option_value;

						$options .= '<option value="'.esc_attr($_option_value).'"'.$_selected.'>'.
						            '  '.esc_html($_option_label).
						            '</option>';
					}
				}
				unset($_option_value, $_option_label, $_selected); // Housekeeping.

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!isset($_selected_value) && isset($current_value) && $current_value)
						$options .= '<option value="'.esc_attr($current_value).'" selected="selected">'.
						            '  '.esc_html($current_value).
						            '</option>';

				unset($_selected_value); // Housekeeping.

				return $options; // HTML markup.
			}

			/**
			 * Parses comment content by applying necessary filters.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass $comment Comment object.
			 *
			 * @return string Comment content markup.
			 */
			public function comment_content(\stdClass $comment)
			{
				$markup = $comment->comment_content; // Initialize.
				$markup = apply_filters('get_comment_text', $markup, $comment, array());
				$markup = apply_filters('comment_text', $markup, $comment, array());

				return trim((string)$markup); // Comment content markup.
			}

			/**
			 * Parses comment content by applying necessary filters.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass      $comment Comment object.
			 *
			 * @param integer|string $max_length Defaults to a value of `100`.
			 *    To use the default plugin option for notifications, pass the string `notification`.
			 *    To use the default plugin option for parent notifications, pass `notification_parent`.
			 *
			 * @param boolean        $force_ellipsis Defaults to a value of `FALSE`.
			 *
			 * @return string Comment content text; after markup/filters and then clipping.
			 */
			public function comment_content_clip(\stdClass $comment, $max_length = 100, $force_ellipsis = FALSE)
			{
				if($max_length === 'notification') // An empty string indicates plugin option value.
					$max_length = $this->plugin->options['comment_notification_content_clip_max_chars'];

				else if($max_length === 'notification_parent') // Option for parent comment clips.
					$max_length = $this->plugin->options['comment_notification_parent_content_clip_max_chars'];

				$max_length = (integer)$max_length;
				$markup     = $this->comment_content($comment);
				$clip       = $this->plugin->utils_string->clip($markup, $max_length, $force_ellipsis);

				return trim($clip); // After markup/filters and then clipping.
			}

			/**
			 * Parses comment content by applying necessary filters.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass      $comment Comment object.
			 *
			 * @param integer|string $max_length Defaults to a value of `100`.
			 *    To use the default plugin option for notifications, pass the string `notification`.
			 *    To use the default plugin option for parent notifications, pass `notification_parent`.
			 *
			 * @return string Comment content text; after markup/filters and then mid-clipping.
			 */
			public function comment_content_mid_clip(\stdClass $comment, $max_length = 100)
			{
				if($max_length === 'notification') // An empty string indicates plugin option value.
					$max_length = $this->plugin->options['comment_notification_content_clip_max_chars'];

				else if($max_length === 'notification_parent') // Option for parent comment clips.
					$max_length = $this->plugin->options['comment_notification_parent_content_clip_max_chars'];

				$max_length = (integer)$max_length;
				$markup     = $this->comment_content($comment);
				$mid_clip   = $this->plugin->utils_string->mid_clip($markup, $max_length);

				return trim($mid_clip); // After markup/filters and then mid-clipping.
			}

			/**
			 * Generates markup for powered-by link.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Any style-related arguments.
			 *
			 * @return string Markup for powered-by link.
			 */
			public function powered_by(array $args = array())
			{
				$default_args = array(
					'anchor_to'            => '',
					'anchor_target'        => '_blank',
					'anchor_style'         => 'text-decoration:none;',

					'icon_prefix'          => TRUE,
					'for_wordpress_suffix' => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$anchor_to = trim((string)$args['anchor_to']);
				$anchor_to = !$anchor_to ? $this->plugin->utils_url->product_page() : $anchor_to;

				$anchor_target        = trim((string)$args['anchor_target']);
				$anchor_style         = trim((string)$args['anchor_style']);
				$icon_prefix          = (boolean)$args['icon_prefix'];
				$for_wordpress_suffix = (boolean)$args['for_wordpress_suffix'];

				$icon   = '<i class="'.esc_attr('wsi-'.$this->plugin->slug).'"></i>';
				$anchor = '<a href="'.esc_attr($anchor_to).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($anchor_style).'">'.
				          ($icon_prefix ? $icon.' ' : '').esc_html($this->plugin->name).'&trade;'.
				          '</a>';
				$suffix = $for_wordpress_suffix ? ' '.__('for WordPress', $this->plugin->text_domain) : '';

				return sprintf(__('Powered by %1$s', $this->plugin->text_domain), $anchor.$suffix);
			}

			/**
			 * Constructs markup for an anchor tag.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $url URL to link to.
			 * @param string $clickable Clickable text/markup.
			 * @param array  $args Any additional specs/behavioral args.
			 *
			 * @return string Markup for an anchor tag.
			 */
			public function anchor($url, $clickable, array $args = array())
			{
				$default_args = array(
					'target'   => '',
					'tabindex' => '-1',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$target   = (string)$args['target'];
				$tabindex = (integer)$args['tabindex'];

				return '<a href="'.esc_attr($url).'" target="'.esc_attr($target).'" tabindex="'.esc_attr($tabindex).'">'.$clickable.'</a>';
			}

			/**
			 * Constructs markup for an external anchor tag.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $url URL to link to.
			 * @param string $clickable Clickable text/markup.
			 * @param array  $args Any additional specs/behavioral args.
			 *
			 * @return string Markup for an external anchor tag.
			 */
			public function x_anchor($url, $clickable, array $args = array())
			{
				$args = array_merge($args, array('target' => '_blank'));

				return $this->anchor($url, $clickable, $args);
			}

			/**
			 * Constructs markup for a plugin menu page path.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Markup for a plugin menu page path.
			 */
			public function pmp_path()
			{
				$path = '<code class="pmp-path">';
				$path .= __('WP Dashboard', $this->plugin->text_domain);
				# $path .= ' &#10609; '.__('Comments', $this->plugin->text_domain);
				$path .= ' &#10609; '.esc_html($this->plugin->name).'&trade;';

				foreach(func_get_args() as $_path_name)
					$path .= ' &#10609; '.(string)$_path_name;

				$path .= '</code>';

				return $path;
			}
		}
	}
}