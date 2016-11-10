<?php
/**
 * Markup Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Markup Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsMarkup extends AbsBase
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
     * @param string $name  Full name to format.
     * @param string $email Email adddress to format.
     * @param array  $args  Any additional style-related arguments.
     *
     * @return string HTML markup for a "name" <email>; also mid-clipped automatically.
     */
    public function nameEmail($name = '', $email = '', array $args = [])
    {
        $name  = (string) $name;
        $email = (string) $email;

        $default_args = [
            'separator' => ' ',

            'span_title' => true,

            'name_style'  => '',
            'email_style' => '',

            'anchor'    => true,
            'anchor_to' => 'mailto',
            // `mailto|search|summary|[custom URL]`.
            'anchor_target'          => '',
            'anchor_summary_sub_key' => '',
        ];
        $args = array_merge($default_args, $args);

        if (!($separator = (string) $args['separator'])) {
            $separator = ' '; // Must have.
        }
        $span_title = (bool) $args['span_title'];

        $name_style  = trim((string) $args['name_style']);
        $email_style = trim((string) $args['email_style']);

        $anchor                 = (bool) $args['anchor'];
        $anchor_to              = trim((string) $args['anchor_to']);
        $anchor_target          = trim((string) $args['anchor_target']);
        $anchor_summary_sub_key = trim((string) $args['anchor_summary_sub_key']);

        $name       = $name ? $this->plugin->utils_string->cleanName($name) : '';
        $name_clip  = $name ? $this->plugin->utils_string->midClip($name) : '';
        $email_clip = $email ? $this->plugin->utils_string->midClip($email) : '';

        $name_email_attr_value = ($name ? '"'.$name.'"' : '').($name && $email ? ' ' : '').($email ? '<'.$email.'>' : '');
        $name_span_tag         = $name ? '<span style="'.esc_attr($name_style).'">'.esc_html($name_clip).'</span>' : '';

        if ($anchor_to === 'search' && $email) { // Back-end search?
            $anchor_search_url = $this->plugin->utils_url->searchSubsShort('sub_email:'.$email);
        }
        if ($anchor_to === 'summary' && !$anchor_summary_sub_key && $email) {
            $anchor_summary_sub_key = $this->plugin->utils_sub->emailLatestKey($email);
        }
        if ($anchor_to === 'summary' && $anchor_summary_sub_key) { // Front-end summary?
            $summary_anchor_url = $this->plugin->utils_url->subManageSummaryUrl($anchor_summary_sub_key);
        }
        $mailto_anchor_tag  = $email ? '<a href="mailto:'.esc_attr(urlencode($email)).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';
        $search_anchor_tag  = $email && !empty($anchor_search_url) ? '<a href="'.esc_attr($anchor_search_url).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';
        $summary_anchor_tag = $email && !empty($summary_anchor_url) ? '<a href="'.esc_attr($summary_anchor_url).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';
        $custom_anchor_tag  = $anchor_to ? '<a href="'.esc_attr($anchor_to).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($email_style).'">'.esc_html($email_clip).'</a>' : '';

        if ($anchor_to === 'mailto') {
            $anchor_tag = $mailto_anchor_tag; // e.g. `mailto:email`.
        } elseif ($anchor_to === 'search') {
            $anchor_tag = $search_anchor_tag; // i.e. back-end search.
        } elseif ($anchor_to === 'summary') {
            $anchor_tag = $summary_anchor_tag; // i.e. front-end summary.
        } else {
            $anchor_tag = $custom_anchor_tag; // Default behavior; assume a custom URL was given.
        }
        return ($span_title ? '<span title="'.esc_attr($name_email_attr_value).'" style="font-weight:bold;">' : '').

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
     * @param int   $post_id             The post ID.
     * @param int   $post_total_comments Total comments.
     * @param array $args                Any additional style-related arguments.
     *
     * @return string HTML markup for a post comment count bubble.
     */
    public function commentCount($post_id, $post_total_comments, array $args = [])
    {
        $post_id             = (int) $post_id;
        $post_total_comments = (int) $post_total_comments;

        $default_args = [
            'style' => 'float:right; margin-left:5px;',
        ];
        $args = array_merge($default_args, $args);

        $style = (string) $args['style'];

        $post_total_comments_desc = sprintf(_n('%1$s Comment', '%1$s Comments', $post_total_comments, 'comment-mail'), esc_html($post_total_comments));
        $post_edit_comments_url   = $this->plugin->utils_url->postEditCommentsShort($post_id);

        return '<a href="'.esc_attr($post_edit_comments_url).'" class="pmp-post-com-count post-com-count" style="'.esc_attr($style).'" title="'.esc_attr($post_total_comments_desc).'">'.
               '  <span class="pmp-com-count comment-count">'.esc_html($post_total_comments).'</span>'.
               '</a>';
    }

    /**
     * Subscription count bubble.
     *
     * @since 141111 First documented version.
     *
     * @param int   $post_id         The post ID.
     * @param int   $post_total_subs Total subscriptions.
     * @param array $args            Any additional style-related arguments.
     *
     * @return string HTML markup for a post subscription count bubble.
     */
    public function subsCount($post_id, $post_total_subs, array $args = [])
    {
        $post_id         = (int) $post_id;
        $post_total_subs = (int) $post_total_subs;

        $default_args = [
            'style'         => 'float:right; margin-left:5px;',
            'subscriptions' => false,
        ];
        $args = array_merge($default_args, $args);

        $style         = (string) $args['style'];
        $subscriptions = (bool) $args['subscriptions'];

        $post_total_subs_label = $subscriptions // What should label contain?
            ? $this->plugin->utils_i18n->subscriptions($post_total_subs) : $post_total_subs;

        $post_total_subs_desc = sprintf(_n('%1$s Subscription Total (View)', '%1$s Subscriptions Total (View All)', $post_total_subs, 'comment-mail'), esc_html($post_total_subs));
        $post_edit_subs_url   = $this->plugin->utils_url->postEditSubsShort($post_id);

        return '<a href="'.esc_attr($post_edit_subs_url).'" class="pmp-post-sub-count" style="'.esc_attr($style).'" title="'.esc_attr($post_total_subs_desc).'">'.
               '  <span class="pmp-sub-count">'.esc_html($post_total_subs_label).'</span>'.
               '</a>';
    }

    /**
     * Last X subscriptions w/ a given status.
     *
     * @since 141111 First documented version.
     *
     * @param int      $x       The total number to return.
     * @param int|null $post_id Defaults to a `NULL` value.
     *                          i.e. defaults to any post ID. Pass this to limit the query.
     * @param array    $args    Any additional style-related arguments.
     *                          Additional arguments to the underlying `last_x()` call go here too.
     *                          Additional arguments to the underlying `name_email()` call go here too.
     *
     * @return string Markup for last X subscriptions w/ a given status.
     *
     * @see   UtilsSub::lastX()
     */
    public function lastXSubs($x = 0, $post_id = null, array $args = [])
    {
        $default_args = [
            'offset' => 0,

            'status'     => '',
            'sub_email'  => '',
            'user_id'    => null,
            'comment_id' => null,

            'auto_discount_trash'   => true,
            'sub_email_or_user_ids' => false,
            'group_by_email'        => false,
            'no_cache'              => false,

            'show_fname'      => true,
            'show_lname'      => true,
            'show_date'       => true,
            'show_time'       => true,
            'name_email_args' => ['anchor_to' => ''],
            'view_args'       => ['anchor_to' => 'search'],
            'list_style'      => 'margin:0;',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $show_fname      = (bool) $args['show_fname'];
        $show_lname      = (bool) $args['show_lname'];
        $show_date       = (bool) $args['show_date'];
        $show_time       = (bool) $args['show_time'];
        $name_email_args = (array) $args['name_email_args'];
        $view_args       = (array) $args['view_args'];
        $list_style      = trim((string) $args['list_style']);

        foreach ($this->plugin->utils_sub->lastX($x, $post_id, $args) as $_sub) {
            $_name_maybe = ''; // Initialize.
            $_date_maybe = '';

            if ($show_fname) {
                $_name_maybe .= $_sub->fname;
            }
            if ($show_lname) {
                $_name_maybe .= ' '.$_sub->lname;
            }
            if ($show_date) {
                $_date_maybe .= ' '.$_sub->insertion_time;
            }
            if ($show_time) {
                $_date_maybe .= ' '.$_sub->insertion_time;
            }
            $last_x_email_lis[] = '<li>'.// Display varies based on arguments.
                                  ' <i class="'.esc_attr('si si-'.SLUG_TD).'"></i> '.
                                  $this->nameEmail($_name_maybe, $_sub->email, $name_email_args).' on '.esc_html($this->plugin->utils_date->i18n('M jS, Y g:ia', $_sub->insertion_time)).
                                  '<span style="font-style: italic"> ('.esc_html($this->plugin->utils_date->approxTimeDifference($_sub->insertion_time)).') </span> '.'&lsqb;'.'<a href="'.esc_url($this->plugin->utils_url->searchSubsShort('sub_email:'.$_sub->email)).'">view'.'&rsqb;'.'</a>'.
                                  '</li>';
        }
        unset($_sub, $_name_maybe); // Housekeeping.

        if (empty($last_x_email_lis)) { // If no results, add a no subscriptions message.
            $last_x_email_lis[] = '<li style="font-style:italic;">'.
                                  ' '.__('No subscriptions at this time.', 'comment-mail').
                                  '</li>';
        }
        return '<ul class="pmp-last-x-sub-emails pmp-clean-list-items" style="'.esc_attr($list_style).'">'.
               '  '.implode('', $last_x_email_lis).
               '</ul>';
    }

    /**
     * Markup for user select menu options.
     *
     * @since 141111 First documented version.
     *
     * @param int|null $current_user_id Current user ID.
     * @param array    $args            Any additional style-related arguments.
     *                                  Additional arguments to the underlying `all_users()` call go here too.
     *
     * @return string Markup for user select menu options.
     *                This returns an empty string if there are no users (or too many users);
     *                i.e. an input field should be used instead of a select menu.
     *
     * @see   UtilsDb::allUsers()
     */
    public function userSelectOptions($current_user_id = null, array $args = [])
    {
        $selected_user_id = null; // Initialize.
        $current_user_id  = isset($current_user_id)
            ? (int) $current_user_id : null;

        $default_args = [
            'max' => // Plugin option value.
                (int) $this->plugin->options['max_select_options'],
            'fail_on_max' => true,
            'no_cache'    => false,

            'display_emails' => // Show emails?
                is_admin() && current_user_can('list_users'),
            'allow_empty'     => true,
            'allow_arbitrary' => true,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $display_emails  = (bool) $args['display_emails'];
        $allow_empty     = (bool) $args['allow_empty'];
        $allow_arbitrary = (bool) $args['allow_arbitrary'];

        if (!is_admin() || !current_user_can('list_users')) {
            return ''; // Not permitted to do so.
        }
        if (!$this->plugin->options['user_select_options_enable']) {
            return ''; // Use input field instead of options.
        }
        if (!($users = $this->plugin->utils_db->allUsers($args))) {
            return ''; // Use input field instead of options.
        }
        $options = ''; // Initialize.
        if ($allow_empty) { // Allow empty selection?
            $options = '<option value="0"></option>';
        }
        foreach ($users as $_user) { // Iterate users.
            $_selected = ''; // Initialize.

            if (!isset($selected_user_id) && isset($current_user_id)) {
                if (($_selected = selected($_user->ID, $current_user_id, false))) {
                    $selected_user_id = $_user->ID;
                }
            }
            $options .= '<option value="'.esc_attr($_user->ID).'"'.$_selected.'>'.
                        '  '.esc_html(
                            __('User', 'comment-mail').' ID #'.$_user->ID.
                            ' :: '.$_user->user_login.// The user's username; i.e. what they log in with.
                            ' :: "'.$_user->display_name.'"'.($display_emails ? ' <'.$_user->user_email.'>' : '')
                        ).
                        '</option>';
        }
        unset($_user, $_selected); // Housekeeping.

        if ($allow_arbitrary) { // Allow arbitrary select option?
            if (!isset($selected_user_id) && isset($current_user_id) && $current_user_id > 0) {
                $options .= '<option value="'.esc_attr($current_user_id).'" selected="selected">'.
                            '  '.esc_html(__('User', 'comment-mail').' ID #'.$current_user_id).
                            '</option>';
            }
        }
        return $options; // HTML markup.
    }

    /**
     * Markup for post select menu options.
     *
     * @since 141111 First documented version.
     *
     * @param int|null $current_post_id Current post ID.
     * @param array    $args            Any additional style-related arguments.
     *                                  Additional arguments to the underlying `all_posts()` call go here too.
     *
     * @return string Markup for post select menu options.
     *                This returns an empty string if there are no posts (or too many posts);
     *                i.e. an input field should be used instead of a select menu.
     *
     * @see   UtilsDb::allPosts()
     */
    public function postSelectOptions($current_post_id = null, array $args = [])
    {
        $selected_post_id = null; // Initialize.
        $current_post_id  = isset($current_post_id)
            ? (int) $current_post_id : null;

        $default_args = [
            'max' => // Plugin option value.
                (int) $this->plugin->options['max_select_options'],
            'fail_on_max'                => true,
            'for_comments_only'          => false,
            'include_post_types'         => [],
            'exclude_post_types'         => [],
            'exclude_post_statuses'      => [],
            'exclude_password_protected' => !is_admin(),
            'no_cache'                   => false,

            'allow_empty'     => true,
            'allow_arbitrary' => true,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $args['include_post_types'] = (array) $args['include_post_types'];
        if ($this->plugin->options['enabled_post_types']) {
            $enabled_post_types         = strtolower($this->plugin->options['enabled_post_types']);
            $enabled_post_types         = preg_split('/[\s;,]+/', $enabled_post_types, null, PREG_SPLIT_NO_EMPTY);
            $args['include_post_types'] = array_unique(array_merge($args['include_post_types'], $enabled_post_types));
        }
        $args['exclude_post_types'] = (array) $args['exclude_post_types'];
        if (!$this->plugin->options['post_select_options_media_enable']) {
            $args['exclude_post_types'][] = 'attachment';
        }
        if (!$args['exclude_post_statuses'] && !is_admin()) { // If not in an admin area.
            $args['exclude_post_statuses'] = ['future', 'draft', 'pending', 'private'];
        }
        $allow_empty     = (bool) $args['allow_empty'];
        $allow_arbitrary = (bool) $args['allow_arbitrary'];

        if (!$this->plugin->options['post_select_options_enable']) {
            return ''; // Use input field instead of options.
        }
        if (!($posts = $this->plugin->utils_db->allPosts($args))) {
            return ''; // Use input field instead of options.
        }
        $options = ''; // Initialize.
        if ($allow_empty) { // Allow empty selection?
            $options = '<option value="0"></option>';
        }
        $default_post_type_label = __('Post', 'comment-mail');

        foreach ($posts as $_post) { // Iterate posts.
            $_selected = ''; // Initialize.

            if (!isset($selected_post_id) && isset($current_post_id)) {
                if (($_selected = selected($_post->ID, $current_post_id, false))) {
                    $selected_post_id = $_post->ID;
                }
            }
            $_post_type_label = $default_post_type_label;
            if (($_post_type = get_post_type_object($_post->post_type))) {
                $_post_type_label = $_post_type->labels->singular_name;
            }
            if (is_admin()) { // Slightly different format in admin area.
                $options .= '<option value="'.esc_attr($_post->ID).'"'.$_selected.'>'.
                            '  '.esc_html(
                                $_post_type->labels->singular_name.' #'.$_post->ID.':'.
                                ' '.($_post->post_title ? $_post->post_title : __('Untitled', 'comment-mail'))
                            ).
                            '</option>';
            } else { // Front-end display should be friendlier in some ways.
                $options .= '<option value="'.esc_attr($_post->ID).'"'.$_selected.'>'.
                            '  '.esc_html(
                                $this->plugin->utils_date->i18n('M jS, Y', strtotime($_post->post_date_gmt)).
                                ' — '.($_post->post_title ? $_post->post_title : __('Untitled', 'comment-mail'))
                            ).
                            '</option>';
            }
        }
        unset($_post, $_selected, $_post_type, $_post_type_label); // Housekeeping.

        if ($allow_arbitrary) { // Allow arbitrary select option?
            if (!isset($selected_post_id) && isset($current_post_id) && $current_post_id > 0) {
                $options .= '<option value="'.esc_attr($current_post_id).'" selected="selected">'.
                            '  '.esc_html(__('Post', 'comment-mail').' ID #'.$current_post_id).
                            '</option>';
            }
        }
        return $options; // HTML markup.
    }

    /**
     * Markup for comment select menu options.
     *
     * @since 141111 First documented version.
     *
     * @param int      $post_id            A post ID.
     * @param int|null $current_comment_id Current comment ID.
     * @param array    $args               Any additional style-related arguments.
     *                                     Additional arguments to the underlying `all_comments()` call go here too.
     *
     * @return string Markup for comment select menu options.
     *                This returns an empty string if there are no comments (or too many comments);
     *                i.e. an input field should be used instead of a select menu.
     *
     * @see   UtilsDb::allComments()
     */
    public function commentSelectOptions($post_id, $current_comment_id = null, array $args = [])
    {
        if (!($post_id = (int) $post_id)) {
            return ''; // Not possible.
        }
        $selected_comment_id = null; // Initialize.
        $current_comment_id  = isset($current_comment_id)
            ? (int) $current_comment_id : null;

        $default_args = [
            'max' => // Option value.
                (int) $this->plugin->options['max_select_options'],
            'fail_on_max'                 => true,
            'parents_only'                => false,
            'include_post_types'          => [],
            'exclude_post_types'          => [],
            'exclude_post_statuses'       => [],
            'exclude_password_protected'  => !is_admin(),
            'exclude_unapproved_comments' => !is_admin(),
            'no_cache'                    => false,

            'display_emails' => // Show emails?
                is_admin() && current_user_can('moderate_comments'),
            'allow_empty'     => true,
            'allow_arbitrary' => true,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $args['include_post_types'] = (array) $args['include_post_types'];

        if ($this->plugin->options['enabled_post_types']) {
            $enabled_post_types         = strtolower($this->plugin->options['enabled_post_types']);
            $enabled_post_types         = preg_split('/[\s;,]+/', $enabled_post_types, null, PREG_SPLIT_NO_EMPTY);
            $args['include_post_types'] = array_unique(array_merge($args['include_post_types'], $enabled_post_types));
        }
        $args['exclude_post_types'] = (array) $args['exclude_post_types'];

        if (!$this->plugin->options['post_select_options_media_enable']) {
            $args['exclude_post_types'][] = 'attachment';
        }
        if (!$args['exclude_post_statuses'] && !is_admin()) { // If not in an admin area.
            $args['exclude_post_statuses'] = ['future', 'draft', 'pending', 'private'];
        }
        $display_emails  = (bool) $args['display_emails'];
        $allow_empty     = (bool) $args['allow_empty'];
        $allow_arbitrary = (bool) $args['allow_arbitrary'];

        if (!$this->plugin->options['comment_select_options_enable']) {
            return ''; // Use input field instead of options.
        }
        if (!($comments = $this->plugin->utils_db->allComments($post_id, $args))) {
            return ''; // Use input field instead of options.
        }
        $options = ''; // Initialize.
        if ($allow_empty) { // Allow empty selection?
            $options = '<option value="0">'.__('— All Comments/Replies —', 'comment-mail').'</option>';
        }
        foreach ($comments as $_comment) { // Iterate comments.
            $_selected = ''; // Initialize.

            if (!isset($selected_comment_id) && isset($current_comment_id)) {
                if (($_selected = selected($_comment->comment_ID, $current_comment_id, false))) {
                    $selected_comment_id = $_comment->comment_ID;
                }
            }
            if (is_admin()) { // Slightly different format in admin area.
                $options .= '<option value="'.esc_attr($_comment->comment_ID).'"'.$_selected.'>'.
                            '  '.esc_html(
                                '#'.$_comment->comment_ID.': '.$this->plugin->utils_date->i18n('M jS, Y g:i a', strtotime($_comment->comment_date_gmt)).
                                ($_comment->comment_author ? ' — "'.$_comment->comment_author.'"'.($display_emails ? ' <'.$_comment->comment_author_email.'>' : '').' writes:' : ' — ').
                                ' '.$this->commentContentClip($_comment, 45)
                            ).
                            '</option>';
            } else { // Front-end display should be friendlier in some ways.
                $options .= '<option value="'.esc_attr($_comment->comment_ID).'"'.$_selected.'>'.
                            '  '.esc_html(
                                $this->plugin->utils_date->i18n('M jS, Y g:i a', strtotime($_comment->comment_date_gmt)).
                                ($_comment->comment_author ? ' — "'.$_comment->comment_author.'"'.($display_emails ? ' <'.$_comment->comment_author_email.'>' : '').' writes:' : ' — ').
                                ' '.$this->commentContentClip($_comment, 45)
                            ).
                            '</option>';
            }
        }
        unset($_comment, $_selected); // Just a little housekeeping.

        if ($allow_arbitrary) { // Allow arbitrary select option?
            if (!isset($selected_comment_id) && isset($current_comment_id) && $current_comment_id > 0) {
                $options .= '<option value="'.esc_attr($current_comment_id).'" selected="selected">'.
                            '  '.esc_html(__('Comment', 'comment-mail').' ID #'.$current_comment_id).
                            '</option>';
            }
        }
        return $options; // HTML markup.
    }

    /**
     * Markup for deliver select menu options.
     *
     * @since 141111 First documented version.
     *
     * @param string|null $current_deliver Current delivery option.
     * @param array       $args            Any additional style-related arguments.
     *
     * @return string Markup for deliver select menu options.
     *
     * @see   UtilsI18n::deliverLabel()
     */
    public function deliverSelectOptions($current_deliver = null, array $args = [])
    {
        $selected_deliver = null; // Initialize.
        $current_deliver  = isset($current_deliver)
            ? (string) $current_deliver : null;

        $default_args = [
            'allow_empty'     => true,
            'allow_arbitrary' => true,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $allow_empty     = (bool) $args['allow_empty'];
        $allow_arbitrary = (bool) $args['allow_arbitrary'];

        $deliver_options_available = [
            'asap'   => $this->plugin->utils_i18n->deliverLabel('asap'),
            'hourly' => $this->plugin->utils_i18n->deliverLabel('hourly'),
            'daily'  => $this->plugin->utils_i18n->deliverLabel('daily'),
            'weekly' => $this->plugin->utils_i18n->deliverLabel('weekly'),
        ]; // These are hard-coded; i.e. not expected to change.

        $options = ''; // Initialize.
        if ($allow_empty) { // Allow empty selection?
            $options = '<option value=""></option>';
        }
        foreach ($deliver_options_available as $_deliver_option => $_deliver_label) {
            $_selected = ''; // Initialize.

            if (!isset($selected_deliver) && isset($current_deliver)) {
                if (($_selected = selected($_deliver_option, $current_deliver, false))) {
                    $selected_deliver = $_deliver_option;
                }
            }
            $options .= '<option value="'.esc_attr($_deliver_option).'"'.$_selected.'>'.
                        '  '.esc_html($_deliver_label).
                        '</option>';
        }
        unset($_deliver_option, $_deliver_label, $_selected); // Housekeeping.

        if ($allow_arbitrary) { // Allow arbitrary select option?
            if (!isset($selected_deliver) && isset($current_deliver) && $current_deliver) {
                $options .= '<option value="'.esc_attr($current_deliver).'" selected="selected">'.
                            '  '.esc_html($current_deliver).
                            '</option>';
            }
        }
        return $options; // HTML markup.
    }

    /**
     * Markup for status select menu options.
     *
     * @since 141111 First documented version.
     *
     * @param string|null $current_status Current status.
     * @param array       $args           Any additional style-related arguments.
     *
     * @return string Markup for status select menu options.
     *
     * @see   UtilsI18n::statusLabel()
     */
    public function statusSelectOptions($current_status = null, array $args = [])
    {
        $selected_status = null; // Initialize.
        $current_status  = isset($current_status)
            ? (string) $current_status : null;

        $default_args = [
            'allow_empty'                   => true,
            'allow_arbitrary'               => true,
            'ui_protected_data_keys_enable' => !is_admin(),
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $allow_empty                   = (bool) $args['allow_empty'];
        $allow_arbitrary               = (bool) $args['allow_arbitrary'];
        $ui_protected_data_keys_enable = (bool) $args['ui_protected_data_keys_enable'];

        $status_options_available = [
            'unconfirmed' => $this->plugin->utils_i18n->statusLabel('unconfirmed'),
            'subscribed'  => $this->plugin->utils_i18n->statusLabel('subscribed'),
            'suspended'   => $this->plugin->utils_i18n->statusLabel('suspended'),
            'trashed'     => $this->plugin->utils_i18n->statusLabel('trashed'),
        ]; // These are hard-coded; i.e. not expected to change.

        if ($ui_protected_data_keys_enable) { // Front-end UI should limit choices.
            unset($status_options_available['unconfirmed'], $status_options_available['trashed']);
        }
        $options = ''; // Initialize.
        if ($allow_empty) { // Allow empty selection?
            $options = '<option value=""></option>';
        }
        foreach ($status_options_available as $_status_option => $_status_label) {
            $_selected = ''; // Initialize.

            if (!isset($selected_status) && isset($current_status)) {
                if (($_selected = selected($_status_option, $current_status, false))) {
                    $selected_status = $_status_option;
                }
            }
            $options .= '<option value="'.esc_attr($_status_option).'"'.$_selected.'>'.
                        '  '.esc_html($_status_label).
                        '</option>';
        }
        unset($_status_option, $_status_label, $_selected); // Housekeeping.

        if ($allow_arbitrary) { // Allow arbitrary select option?
            if (!$ui_protected_data_keys_enable) { // Front-end UI limits choices.
                if (!isset($selected_status) && isset($current_status) && $current_status) {
                    $options .= '<option value="'.esc_attr($current_status).'" selected="selected">'.
                                '  '.esc_html($current_status).
                                '</option>';
                }
            }
        }
        return $options; // HTML markup.
    }

    /**
     * Markup for select menu options.
     *
     * @since 141111 First documented version.
     *
     * @param array       $given_ops     Options array.
     *                                   Keys are option values; values are labels.
     * @param string|null $current_value The current value.
     * @param array       $args          Any additional style-related arguments.
     *
     * @return string Markup for select menu options.
     */
    public function selectOptions(array $given_ops, $current_value = null, array $args = [])
    {
        $_selected_value = null; // Initialize.
        $current_value   = isset($current_value)
            ? (string) $current_value : null;

        $default_args = [
            'allow_arbitrary' => true,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $allow_arbitrary = (bool) $args['allow_arbitrary'];

        $options = ''; // Initialize.
        // There is no `$allow_empty` argument in this handler.
        // Note that we do NOT setup a default/empty option value here.
        // If you want to `$allow_empty`, provide an empty option of your own please.

        foreach ($given_ops as $_option_value => $_option_label) {
            $_selected     = ''; // Initialize.
            $_option_value = (string) $_option_value;
            $_option_label = (string) $_option_label;

            if (stripos($_option_value, '@optgroup_open') === 0) {
                $options .= '<optgroup label="'.esc_attr($_option_label).'">';
            } elseif (stripos($_option_value, '@optgroup_close') === 0) {
                $options .= '</optgroup>'; // Close.
            } else { // Normal behavior; another option value/label.
                if (!isset($_selected_value) && isset($current_value)) {
                    if (($_selected = selected($_option_value, $current_value, false))) {
                        $_selected_value = $_option_value;
                    }
                }
                $options .= '<option value="'.esc_attr($_option_value).'"'.$_selected.'>'.
                            '  '.esc_html($_option_label).
                            '</option>';
            }
        }
        unset($_option_value, $_option_label, $_selected); // Housekeeping.

        if ($allow_arbitrary) { // Allow arbitrary select option?
            if (!isset($_selected_value) && isset($current_value) && $current_value) {
                $options .= '<option value="'.esc_attr($current_value).'" selected="selected">'.
                            '  '.esc_html($current_value).
                            '</option>';
            }
        }
        unset($_selected_value); // Housekeeping.

        return $options; // HTML markup.
    }

    /**
     * Parses comment content by applying necessary filters.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Comment $comment Comment object.
     *
     * @return string Comment content markup.
     */
    public function commentContent(\WP_Comment $comment)
    {
        $markup = $comment->comment_content; // Initialize.
        $markup = apply_filters('get_comment_text', $markup, $comment, []);
        $markup = apply_filters('comment_text', $markup, $comment, []);

        return trim((string) $markup); // Comment content markup.
    }

    /**
     * Parses comment content by applying necessary filters.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Comment $comment        Comment object.
     * @param int|string  $max_length     Defaults to a value of `100`.
     *                                    To use the default plugin option for notifications, pass the string `notification`.
     *                                    To use the default plugin option for parent notifications, pass `notification_parent`.
     * @param bool        $force_ellipsis Defaults to a value of `FALSE`.
     *
     * @return string Comment content text; after markup/filters and then clipping.
     */
    public function commentContentClip(\WP_Comment $comment, $max_length = 100, $force_ellipsis = false)
    {
        if ($max_length === 'notification') { // An empty string indicates plugin option value.
            $max_length = $this->plugin->options['comment_notification_content_clip_max_chars'];
        } elseif ($max_length === 'notification_parent') { // Option for parent comment clips.
            $max_length = $this->plugin->options['comment_notification_parent_content_clip_max_chars'];
        }
        $max_length = (int) $max_length;
        $markup     = $this->commentContent($comment);
        $clip       = $this->plugin->utils_string->clip($markup, $max_length, $force_ellipsis);

        return trim($clip); // After markup/filters and then clipping.
    }

    /**
     * Parses comment content by applying necessary filters.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_Comment $comment    Comment object.
     * @param int|string  $max_length Defaults to a value of `100`.
     *                                To use the default plugin option for notifications, pass the string `notification`.
     *                                To use the default plugin option for parent notifications, pass `notification_parent`.
     *
     * @return string Comment content text; after markup/filters and then mid-clipping.
     */
    public function commentContentMidClip(\WP_Comment $comment, $max_length = 100)
    {
        if ($max_length === 'notification') { // An empty string indicates plugin option value.
            $max_length = $this->plugin->options['comment_notification_content_clip_max_chars'];
        } elseif ($max_length === 'notification_parent') { // Option for parent comment clips.
            $max_length = $this->plugin->options['comment_notification_parent_content_clip_max_chars'];
        }
        $max_length = (int) $max_length;
        $markup     = $this->commentContent($comment);
        $mid_clip   = $this->plugin->utils_string->midClip($markup, $max_length);

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
    public function poweredBy(array $args = [])
    {
        $default_args = [
            'anchor_to'     => '',
            'anchor_target' => '_blank',
            'anchor_style'  => 'text-decoration:none;',

            'icon_prefix'          => true,
            'for_wordpress_suffix' => true,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $anchor_to = trim((string) $args['anchor_to']);
        $anchor_to = !$anchor_to ? $this->plugin->utils_url->productPage() : $anchor_to;

        $anchor_target        = trim((string) $args['anchor_target']);
        $anchor_style         = trim((string) $args['anchor_style']);
        $icon_prefix          = (bool) $args['icon_prefix'];
        $for_wordpress_suffix = (bool) $args['for_wordpress_suffix'];

        $icon   = '<i class="'.esc_attr('si si-'.SLUG_TD).'"></i>';
        $anchor = '<a href="'.esc_attr($anchor_to).'" target="'.esc_attr($anchor_target).'" style="'.esc_attr($anchor_style).'">'.
                  ($icon_prefix ? $icon.' ' : '').esc_html(NAME).'&trade;'.
                  '</a>';
        $suffix = $for_wordpress_suffix ? ' '.__('for WordPress', 'comment-mail') : '';

        return sprintf(__('Powered by %1$s', 'comment-mail'), $anchor.$suffix);
    }

    /**
     * Constructs markup for an anchor tag.
     *
     * @since 141111 First documented version.
     *
     * @param string $url       URL to link to.
     * @param string $clickable Clickable text/markup.
     * @param array  $args      Any additional specs/behavioral args.
     *
     * @return string Markup for an anchor tag.
     */
    public function anchor($url, $clickable, array $args = [])
    {
        $default_args = [
            'target'   => '',
            'tabindex' => '-1',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $target   = (string) $args['target'];
        $tabindex = (int) $args['tabindex'];

        return '<a href="'.esc_attr($url).'" target="'.esc_attr($target).'" tabindex="'.esc_attr($tabindex).'">'.$clickable.'</a>';
    }

    /**
     * Constructs markup for an external anchor tag.
     *
     * @since 141111 First documented version.
     *
     * @param string $url       URL to link to.
     * @param string $clickable Clickable text/markup.
     * @param array  $args      Any additional specs/behavioral args.
     *
     * @return string Markup for an external anchor tag.
     */
    public function xAnchor($url, $clickable, array $args = [])
    {
        $args = array_merge($args, ['target' => '_blank']);

        return $this->anchor($url, $clickable, $args);
    }

    /**
     * Constructs markup for a plugin menu page path.
     *
     * @since 141111 First documented version.
     *
     * @return string Markup for a plugin menu page path.
     */
    public function pmpPath()
    {
        $path = '<code class="pmp-path">';
        $path .= __('WP Dashboard', 'comment-mail');
        # $path .= ' &#8594; '.__('Comments', 'comment-mail');
        $path .= ' &#8594; '.esc_html(NAME).'&trade;';

        foreach (func_get_args() as $_path_name) {
            $path .= ' &#8594; '.(string) $_path_name;
        }
        $path .= '</code>';

        return $path;
    }

    /**
     * Fills menu page inline SVG icon color.
     *
     * @param string $svg Inline SVG icon markup.
     *
     * @return string Inline SVG icon markup.
     */
    public function colorSvgMenuIcon($svg)
    {
        if (!($color = get_user_option('admin_color'))) {
            $color = 'fresh'; // Default color scheme.
        }
        if (empty($this->wp_admin_icon_colors[$color])) {
            return $svg; // Not possible.
        }
        $icon_colors         = $this->wp_admin_icon_colors[$color];
        $use_icon_fill_color = $icon_colors['base']; // Default base.

        if ($this->plugin->utils_env->isMenuPage(GLOBAL_NS.'*')) {
            $use_icon_fill_color = $icon_colors['current'];
        }
        return str_replace(' fill="currentColor"', ' fill="'.esc_attr($use_icon_fill_color).'"', $svg);
    }

    /**
     * WordPress admin icon color schemes.
     *
     * @var array WP admin icon colors.
     *
     * @note These must be hard-coded, because they don't become available
     *    in core until `admin_init`; i.e., too late for `admin_menu`.
     */
    public $wp_admin_icon_colors = [
        'fresh'     => ['base' => '#999999', 'focus' => '#2EA2CC', 'current' => '#FFFFFF'],
        'light'     => ['base' => '#999999', 'focus' => '#CCCCCC', 'current' => '#CCCCCC'],
        'blue'      => ['base' => '#E5F8FF', 'focus' => '#FFFFFF', 'current' => '#FFFFFF'],
        'midnight'  => ['base' => '#F1F2F3', 'focus' => '#FFFFFF', 'current' => '#FFFFFF'],
        'sunrise'   => ['base' => '#F3F1F1', 'focus' => '#FFFFFF', 'current' => '#FFFFFF'],
        'ectoplasm' => ['base' => '#ECE6F6', 'focus' => '#FFFFFF', 'current' => '#FFFFFF'],
        'ocean'     => ['base' => '#F2FCFF', 'focus' => '#FFFFFF', 'current' => '#FFFFFF'],
        'coffee'    => ['base' => '#F3F2F1', 'focus' => '#FFFFFF', 'current' => '#FFFFFF'],
    ];
}
