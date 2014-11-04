<?php
/**
 * Menu Page Table Base
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\WP_List_Table')) // WP core.
		require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_table_base'))
	{
		/**
		 * Menu Page Table Base
		 *
		 * @since 14xxxx First documented version.
		 */
		abstract class menu_page_table_base extends \WP_List_Table
		{
			/*
			 * Protected properties.
			 */

			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin;

			/**
			 * @var string Singular item name.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $singular_name;

			/**
			 * @var string Singular item label.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $singular_label;

			/**
			 * @var string Plural item name.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plural_name;

			/**
			 * @var string Plural item label.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plural_label;

			/**
			 * @var string Regex for sub IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_ids_regex;

			/**
			 * @var string Regex for user IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_ids_regex;

			/**
			 * @var string Regex for post IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_ids_regex;

			/**
			 * @var string Regex for comment IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment_ids_regex;

			/**
			 * @var string Regex for `AND`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $and_regex;

			/**
			 * @var string Regex for statuses.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $statuses_regex;

			/**
			 * @var string Regex for events.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $events_regex;

			/**
			 * @var array Merged result sets.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $merged_result_sets;

			/*
			 * Class constructor.
			 */

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $args Constructor arguments.
			 */
			public function __construct(array $args = array())
			{
				$this->plugin = plugin();

				$this->singular_name = !empty($args['singular_name'])
					? (string)$args['singular_name'] : 'item';

				$this->singular_label = !empty($args['singular_label'])
					? (string)$args['singular_label'] : 'item';

				$this->plural_name = !empty($args['plural_name'])
					? (string)$args['plural_name'] : 'items';

				$this->plural_label = !empty($args['plural_label'])
					? (string)$args['plural_label'] : 'items';

				$args = array(
					'singular' => $this->singular_name, 'plural' => $this->plural_name,
					'screen'   => !empty($args['screen']) ? (string)$args['screen']
						: $this->plugin->menu_page_hooks[__NAMESPACE__.'_'.$this->plural_name],
				);
				parent::__construct($args); // Parent constructor.

				$this->items = array(); // Initialize.

				// Filters; i.e. `:`= filter; `::` = navigable filter.
				$this->sub_ids_regex     = '/\bsub_ids?\:(?P<sub_ids>[0-9|;,]+)/i';
				$this->user_ids_regex    = '/\buser_ids?\:(?P<user_ids>[0-9|;,]+)/i';
				$this->post_ids_regex    = '/\bpost_ids?\:(?P<post_ids>[0-9|;,]+)/i';
				$this->comment_ids_regex = '/\bcomment_ids?\:(?P<comment_ids>[0-9|;,]+)/i';
				$this->statuses_regex    = '/\bstatus(?:es)?\:\:(?P<statuses>[\w|;,]+)/i';
				$this->events_regex      = '/\bevents?\:\:(?P<events>[\w|;,]+)/i';

				$this->and_regex = '/(?:^|\s+)\+(?:\s+|$)/i';
				// Must NOT conflict with SQL: <http://bit.ly/1tNam85>

				$this->merged_result_sets = array(); // Initialize.

				$this->maybe_process_bulk_action();
				$this->prepare_items();
				$this->display();
			}

			/*
			 * Public column-related methods.
			 */

			/**
			 * Table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all table columns.
			 */
			public function get_columns()
			{
				return static::get_columns_();
			}

			/**
			 * Table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all table columns.
			 *
			 * @extenders Extenders should normally override this.
			 */
			public static function get_columns_()
			{
				return array();
			}

			/**
			 * Hidden table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all hidden table columns.
			 */
			public function get_hidden_columns()
			{
				return static::get_hidden_columns_();
			}

			/**
			 * Hidden table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all hidden table columns.
			 *
			 * @extenders Extenders should normally override this.
			 */
			public static function get_hidden_columns_()
			{
				return array();
			}

			/**
			 * Searchable fulltext table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all fulltext searchables.
			 */
			public function get_ft_searchable_columns()
			{
				return static::get_ft_searchable_columns_();
			}

			/**
			 * Searchable fulltext table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all fulltext searchables.
			 *
			 * @extenders Extenders should normally override this.
			 */
			public static function get_ft_searchable_columns_()
			{
				return array();
			}

			/**
			 * Searchable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all searchables.
			 */
			public function get_searchable_columns()
			{
				return static::get_searchable_columns_();
			}

			/**
			 * Searchable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all searchables.
			 *
			 * @extenders Extenders should normally override this.
			 */
			public static function get_searchable_columns_()
			{
				return array();
			}

			/**
			 * Unsortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all unsortable table columns.
			 */
			public function get_unsortable_columns()
			{
				$unsortable_columns   = static::get_unsortable_columns_();
				$unsortable_columns[] = 'cb'; // Always unsortable.

				return array_unique($unsortable_columns);
			}

			/**
			 * Unsortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all unsortable table columns.
			 *
			 * @extenders Extenders should normally override this.
			 */
			public static function get_unsortable_columns_()
			{
				return array();
			}

			/**
			 * Sortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all sortable table columns.
			 */
			public function get_sortable_columns()
			{
				return static::get_sortable_columns_();
			}

			/**
			 * Sortable table columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all sortable table columns.
			 */
			public static function get_sortable_columns_()
			{
				$sortable_columns     = array(); // Initialize.
				$unsortable_columns   = static::get_unsortable_columns_();
				$unsortable_columns[] = 'cb'; // Always unsortable.
				$unsortable_columns   = array_unique($unsortable_columns);

				foreach(array_keys(static::get_columns_()) as $_column)
					if(!in_array($_column, $unsortable_columns, TRUE))
						$sortable_columns[$_column] = array($_column, FALSE);
				unset($_column); // Housekeeping.

				return $sortable_columns;
			}

			/*
			 * Public filter-related methods.
			 */

			/**
			 * Navigable table filters.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all navigable table filters.
			 */
			public function get_navigable_filters()
			{
				return static::get_navigable_filters_();
			}

			/**
			 * Navigable table filters.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all navigable table filters.
			 *
			 * @extenders Extenders should normally override this.
			 */
			public static function get_navigable_filters_()
			{
				return array();
			}

			/*
			 * Protected column-related methods.
			 */

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_cb(\stdClass $item)
			{
				return '<input type="checkbox" name="'.esc_attr($this->plural_name).'[]" value="'.esc_attr($item->ID).'" />';
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_key(\stdClass $item)
			{
				if(!isset($item->key))
					return '—'; // Not possible.

				if(!$item->key)
					return '—'; // Not possible.

				return '<code>'.esc_html((string)$item->key).'</code>';
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_subscr_type(\stdClass $item)
			{
				if(!isset($item->subscr_type))
					return '—'; // Not possible.

				if(!$item->subscr_type)
					return '—'; // Not possible.

				return esc_html($this->plugin->utils_i18n->subscr_type_label($item->subscr_type));
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_sub_id(\stdClass $item)
			{
				if(!isset($item->sub_id))
					return '—'; // Not possible.

				if(!$item->sub_id)
					return '—'; // Not possible.

				$id_only = '<i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i>'.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->sub_id).'</span>';

				if(empty($this->merged_result_sets['subs'][$item->sub_id]))
					return $id_only; // All we can do.

				$name_email_args = array(
					'separator'          => '<br />',
					'email_style'        => 'font-weight:bold;',
					'anchor_to'          => 'summary',
					'anchor_target'      => '_blank',
					'summary_anchor_key' => $item->sub_key,
				);
				$name            = $item->sub_fname.' '.$item->sub_lname; // Concatenate.
				$sub_info        = '<i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i>'.
				                   ' <span style="font-weight:bold;" title="'.esc_attr($item->sub_key).'">ID #'.esc_html($item->sub_id).'</span>'.
				                   ' '.$this->plugin->utils_markup->name_email($name, $item->sub_email, $name_email_args);

				$edit_url = $this->plugin->utils_url->edit_sub_short($item->sub_id);

				$row_actions = array(
					'edit' => '<a href="'.esc_attr($edit_url).'">'.__('Edit Subscr.', $this->plugin->text_domain).'</a>',
				);
				return $sub_info.$this->row_actions($row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_oby_sub_id(\stdClass $item)
			{
				if(!isset($item->oby_sub_id))
					return '—'; // Not possible.

				if(!$item->oby_sub_id)
					return '—'; // Not possible.

				$id_only = '<i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i>'.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->oby_sub_id).'</span>';

				if(empty($this->merged_result_sets['subs'][$item->oby_sub_id]))
					return $id_only; // All we can do.

				$name_email_args = array(
					'separator'          => '<br />',
					'email_style'        => 'font-weight:bold;',
					'anchor_to'          => 'summary',
					'anchor_target'      => '_blank',
					'summary_anchor_key' => $item->oby_sub_key,
				);
				$name            = $item->oby_sub_fname.' '.$item->oby_sub_lname; // Concatenate.
				$oby_sub_info    = '<i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i>'.
				                   ' <span style="font-weight:bold;" title="'.esc_attr($item->oby_sub_key).'">ID #'.esc_html($item->oby_sub_id).'</span>'.
				                   ' '.$this->plugin->utils_markup->name_email($name, $item->oby_sub_email, $name_email_args);

				$edit_url = $this->plugin->utils_url->edit_sub_short($item->oby_sub_id);

				$row_actions = array(
					'edit' => '<a href="'.esc_attr($edit_url).'">'.__('Edit Subscr.', $this->plugin->text_domain).'</a>',
				);
				return $oby_sub_info.$this->row_actions($row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_user_id(\stdClass $item)
			{
				if(!isset($item->user_id))
					return '—'; // Not possible.

				if(!$item->user_id)
					return '—'; // Not possible.

				$id_only = '<i class="fa fa-user"></i>'. // If it's all we can do.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->user_id).'</span>';

				if(empty($this->merged_result_sets['users'][$item->user_id]))
					return $id_only; // All we can do.

				$name_email_args = array(
					'separator'   => '<br />',
					'email_style' => 'font-weight:normal;',
				);
				$user_info       = '<i class="fa fa-user"></i>'. // e.g. ♙ ID "Name" <email>; w/ username in hover title.
				                   ' <span style="font-weight:bold;" title="'.esc_attr($item->user_login).'">ID #'.esc_html($item->user_id).'</span>'.
				                   ' '.$this->plugin->utils_markup->name_email($item->user_display_name, $item->user_email, $name_email_args);

				$edit_url = $this->plugin->utils_url->edit_user_short($item->user_id);

				$row_actions = array(
					'edit' => '<a href="'.esc_attr($edit_url).'">'.__('Edit User', $this->plugin->text_domain).'</a>',
				);
				return $user_info.$this->row_actions($row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_post_id(\stdClass $item)
			{
				if(!isset($item->post_id))
					return '—'; // Not possible.

				if(!$item->post_id)
					return '—'; // Not possible.

				$id_only = '<i class="fa fa-thumb-tack"></i>'. // If it's all we can do.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->post_id).'</span>';

				if(empty($this->merged_result_sets['posts'][$item->post_id]))
					return $id_only; // All we can do.

				if(!$item->post_type || !$item->post_title)
					return $id_only; // All we can do.

				if(!($post_type = get_post_type_object($item->post_type)))
					return $id_only; // All we can do.

				$post_type_label        = $post_type->labels->singular_name;
				$post_title_clip        = $this->plugin->utils_string->mid_clip($item->post_title);
				$post_date              = $this->plugin->utils_date->i18n('M j, Y', strtotime($item->post_date_gmt));
				$post_date_ago          = $this->plugin->utils_date->approx_time_difference(strtotime($item->post_date_gmt));
				$post_comments_status   = $this->plugin->utils_i18n->status_label($this->plugin->utils_db->post_comment_status__($item->post_comment_status));
				$post_edit_comments_url = $this->plugin->utils_url->post_edit_comments_short($item->post_id);
				$post_total_subs        = $this->plugin->utils_sub->query_total($item->post_id);
				$post_total_comments    = (integer)$item->post_comment_count; // Total comments.

				$post_info = $this->plugin->utils_markup->subs_count($item->post_id, $post_total_subs).
				             $this->plugin->utils_markup->comment_count($item->post_id, $post_total_comments).
				             '<i class="fa fa-thumb-tack"></i>'. // Start w/ a thumb tack icon; works w/ any post type.
				             ' <span style="font-weight:bold;">'.esc_html($post_type_label).' ID #'.esc_html($item->post_id).'</span>'.
				             ' <span style="font-style:italic;">('.__('comments', $this->plugin->text_domain).' '.esc_html($post_comments_status).')</span><br />'.
				             '<span title="'.esc_attr($post_date).'">&ldquo;'.esc_html($post_title_clip).'&rdquo;</span>';

				$post_view_url    = $this->plugin->utils_url->post_short($item->post_id);
				$post_edit_url    = $this->plugin->utils_url->post_edit_short($item->post_id);
				$post_row_actions = array(
					'edit' => '<a href="'.esc_attr($post_edit_url).'">'.sprintf(__('Edit %1$s', $this->plugin->text_domain), esc_html($post_type_label)).'</a>',
					'view' => '<a href="'.esc_attr($post_view_url).'">'.sprintf(__('View', $this->plugin->text_domain), esc_html($post_type_label)).'</a>',
				);
				return $post_info.$this->row_actions($post_row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_comment_parent_id(\stdClass $item)
			{
				if(!isset($item->comment_parent_id))
					return '—'; // Not possible.

				if(!$item->comment_parent_id)
					return '—'; // Not possible.

				$id_only = '<i class="fa fa-comment"></i>'. // If it's all we can do.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->comment_parent_id).'</span>';

				if(empty($this->merged_result_sets['comments'][$item->comment_parent_id]))
					return $id_only; // All we can do.

				$name_email_args          = array(
					'email_style' => 'font-weight:normal;',
				);
				$comment_parent_date_time = $this->plugin->utils_date->i18n('M j, Y, g:i a', strtotime($item->comment_parent_date_gmt));
				$comment_parent_time_ago  = $this->plugin->utils_date->approx_time_difference(strtotime($item->comment_parent_date_gmt));
				$comment_parent_status    = $this->plugin->utils_i18n->status_label($this->plugin->utils_db->comment_status__($item->comment_parent_approved));

				$comment_parent_info = '<i class="fa fa-comment"></i>'. // Start w/ a comment bubble icon.
				                       ' <span style="font-weight:bold;">'.esc_html(__('Comment', $this->plugin->text_domain)).' ID #'.esc_html($item->comment_parent_id).'</span>'.
				                       ' <span style="font-style:italic;">('.esc_html($comment_parent_status).')</span><br />'.
				                       '<span style="font-style:italic;">'.__('by:', $this->plugin->text_domain).'</span>'.
				                       ' '.$this->plugin->utils_markup->name_email($item->comment_parent_author, $item->comment_parent_author_email, $name_email_args);

				$comment_parent_view_url    = $this->plugin->utils_url->comment_short($item->comment_parent_id);
				$comment_parent_edit_url    = $this->plugin->utils_url->comment_edit_short($item->comment_parent_id);
				$comment_parent_row_actions = array(
					'edit' => '<a href="'.esc_attr($comment_parent_edit_url).'">'.__('Edit Comment', $this->plugin->text_domain).'</a>',
					'view' => '<a href="'.esc_attr($comment_parent_view_url).'">'.__('View', $this->plugin->text_domain).'</a>',
				);
				return $comment_parent_info.$this->row_actions($comment_parent_row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_comment_id(\stdClass $item)
			{
				if(!isset($item->comment_id))
					return '—'; // Not possible.

				if(!$item->comment_id)
					return '— all —'; // All of them.

				$id_only = '<i class="fa fa-comment"></i>'. // If it's all we can do.
				           ' <span style="font-weight:bold;">ID #'.esc_html($item->comment_id).'</span>';

				if(empty($this->merged_result_sets['comments'][$item->comment_id]))
					return esc_html($item->comment_id);

				$name_email_args   = array(
					'email_style' => 'font-weight:normal;',
				);
				$comment_date_time = $this->plugin->utils_date->i18n('M j, Y, g:i a', strtotime($item->comment_date_gmt));
				$comment_time_ago  = $this->plugin->utils_date->approx_time_difference(strtotime($item->comment_date_gmt));
				$comment_status    = $this->plugin->utils_i18n->status_label($this->plugin->utils_db->comment_status__($item->comment_approved));

				$comment_info = '<i class="fa fa-comment"></i>'. // Start w/ a comment bubble icon.
				                ' <span style="font-weight:bold;">'.esc_html(__('Comment', $this->plugin->text_domain)).' ID #'.esc_html($item->comment_id).'</span>'.
				                ' <span style="font-style:italic;">('.esc_html($comment_status).')</span><br />'.
				                '<span style="font-style:italic;">'.__('by:', $this->plugin->text_domain).'</span>'.
				                ' '.$this->plugin->utils_markup->name_email($item->comment_author, $item->comment_author_email, $name_email_args);

				$comment_view_url    = $this->plugin->utils_url->comment_short($item->comment_id);
				$comment_edit_url    = $this->plugin->utils_url->comment_edit_short($item->comment_id);
				$comment_row_actions = array(
					'edit' => '<a href="'.esc_attr($comment_edit_url).'">'.__('Edit Comment', $this->plugin->text_domain).'</a>',
					'view' => '<a href="'.esc_attr($comment_view_url).'">'.__('View', $this->plugin->text_domain).'</a>',
				);
				return $comment_info.$this->row_actions($comment_row_actions);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_deliver(\stdClass $item)
			{
				if(!isset($item->deliver))
					return '—'; // Not possible.

				if(!$item->deliver)
					return '—'; // Not possible.

				return esc_html($this->plugin->utils_i18n->deliver_label($item->deliver));
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_status_before(\stdClass $item)
			{
				if(!isset($item->status_before))
					return '—'; // Not possible.

				if(!$item->status_before)
					return '—'; // Not possible.

				return esc_html($this->plugin->utils_i18n->status_label($item->status_before));
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_status(\stdClass $item)
			{
				if(!isset($item->status))
					return '—'; // Not possible.

				if(!$item->status)
					return '—'; // Not possible.

				return esc_html($this->plugin->utils_i18n->status_label($item->status));
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_note_code(\stdClass $item)
			{
				if(!isset($item->note_code))
					return '—'; // Not possible.

				if(!$item->note_code)
					return '—'; // Not applicable.

				$note = $this->plugin->utils_event->queue_note_code($item->note_code);
				$note = $this->plugin->utils_string->markdown_no_p($note); // HTML markup.

				return $note; // HTML markup via simple MD parsing.
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_user_initiated(\stdClass $item)
			{
				if(!isset($item->user_initiated))
					return '—'; // Not possible.

				return esc_html($item->user_initiated ? 'yes' : 'no');
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_hold_until_time(\stdClass $item)
			{
				if(!isset($item->hold_until_time))
					return '—'; // Not possible.

				if(!$item->hold_until_time)
					return __('n/a; awaiting processing', $this->plugin->text_domain);

				return esc_html($this->plugin->utils_date->i18n('M j, Y, g:i a', $item->hold_until_time)).'<br />'.
				       '<span style="font-style:italic;">('.esc_html($this->plugin->utils_date->approx_time_difference(time(), $item->hold_until_time, '')).')</span>'.
				       ' '.__('~ part of a digest', $this->plugin->text_domain);
			}

			/**
			 * Table column handler.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $item Item object; i.e. a row from the DB.
			 * @param string    $property Column we need to build markup for.
			 *
			 * @return string HTML markup for this table column.
			 */
			protected function column_default(\stdClass $item, $property)
			{
				if(!($property = trim((string)$property)))
					return '—'; // Not applicable.

				$value = isset($item->{$property}) ? $item->{$property} : '';

				if(($property === 'time' || substr($property, -5) === '_time') && is_integer($value))
					$value = $value <= 0 ? '—' // Use a default value of `—` in this case.
						: esc_html($this->plugin->utils_date->i18n('M j, Y, g:i a', $value)).'<br />'.
						  '<span style="font-style:italic;">('.esc_html($this->plugin->utils_date->approx_time_difference($value)).')</span>';

				else if(($property === 'ID' || substr($property, -3) === '_id') && is_integer($value))
					$value = $value <= 0 ? '—' : esc_html((string)$value);

				else $value = esc_html($this->plugin->utils_string->mid_clip((string)$value));

				return isset($value[0]) ? $value : '—'; // Allow for `0`.
			}

			/*
			 * Protected parameter-related methods.
			 */

			/**
			 * Get raw search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Raw search query; w/ search tokens.
			 */
			protected function get_raw_search_query()
			{
				$s = !empty($_REQUEST['s'])
					? trim(stripslashes((string)$_REQUEST['s']))
					: ''; // Not searching.

				if(!isset($s[0])) return '';

				$_GET['s'] = $_REQUEST['s'] = addslashes($s);
				if(isset($_POST['s'])) $_POST['s'] = addslashes($s);

				return $s; // Raw query.
			}

			/**
			 * Clean search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Clean search query; minus search tokens.
			 */
			protected function get_clean_search_query()
			{
				$s = $this->get_raw_search_query();

				$s = $s ? preg_replace($this->sub_ids_regex, '', $s) : '';
				$s = $s ? preg_replace($this->user_ids_regex, '', $s) : '';
				$s = $s ? preg_replace($this->post_ids_regex, '', $s) : '';
				$s = $s ? preg_replace($this->comment_ids_regex, '', $s) : '';
				$s = $s ? preg_replace($this->statuses_regex, '', $s) : '';
				$s = $s ? preg_replace($this->events_regex, '', $s) : '';
				$s = $s ? preg_replace($this->and_regex, '', $s) : '';
				$s = $s ? trim(preg_replace('/\s+/', ' ', $s)) : '';

				return $s; // Search search query.
			}

			/**
			 * A clean `$_POST['search-submit']`?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if a clean `$_POST['search-submit']`.
			 */
			protected function is_clean_search_submit_post()
			{
				return !empty($_POST['search-submit']) && $this->get_clean_search_query();
			}

			/**
			 * Get sub IDs in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Sub IDs in the search query.
			 */
			protected function get_sub_ids_in_search_query()
			{
				$sub_ids = array(); // Initialize.
				$s       = $this->get_raw_search_query();

				if($s && preg_match_all($this->sub_ids_regex, $s, $_m))
					foreach(preg_split('/[|;,]+/', implode(',', $_m['sub_ids']), NULL, PREG_SPLIT_NO_EMPTY) as $_sub_id)
						if(($_sub_id = (integer)$_sub_id) > 0) $sub_ids[$_sub_id] = $_sub_id;
				unset($_m, $_sub_id); // Housekeeping.

				return $sub_ids;
			}

			/**
			 * Get user IDs in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array User IDs in the search query.
			 */
			protected function get_user_ids_in_search_query()
			{
				$user_ids = array(); // Initialize.
				$s        = $this->get_raw_search_query();

				if($s && preg_match_all($this->user_ids_regex, $s, $_m))
					foreach(preg_split('/[|;,]+/', implode(',', $_m['user_ids']), NULL, PREG_SPLIT_NO_EMPTY) as $_user_id)
						if(($_user_id = (integer)$_user_id) > 0) $user_ids[$_user_id] = $_user_id;
				unset($_m, $_user_id); // Housekeeping.

				return $user_ids;
			}

			/**
			 * Get post IDs in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Post IDs in the search query.
			 */
			protected function get_post_ids_in_search_query()
			{
				$post_ids = array(); // Initialize.
				$s        = $this->get_raw_search_query();

				if($s && preg_match_all($this->post_ids_regex, $s, $_m))
					foreach(preg_split('/[|;,]+/', implode(',', $_m['post_ids']), NULL, PREG_SPLIT_NO_EMPTY) as $_post_id)
						if(($_post_id = (integer)$_post_id) > 0) $post_ids[$_post_id] = $_post_id;
				unset($_m, $_post_id); // Housekeeping.

				return $post_ids;
			}

			/**
			 * Get comment IDs in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Comment IDs in the search query.
			 */
			protected function get_comment_ids_in_search_query()
			{
				$comment_ids = array(); // Initialize.
				$s           = $this->get_raw_search_query();

				if($s && preg_match_all($this->comment_ids_regex, $s, $_m))
					foreach(preg_split('/[|;,]+/', implode(',', $_m['comment_ids']), NULL, PREG_SPLIT_NO_EMPTY) as $_comment_id)
						if(($_comment_id = (integer)$_comment_id) > 0) $comment_ids[$_comment_id] = $_comment_id;
				unset($_m, $_comment_id); // Housekeeping.

				return $comment_ids;
			}

			/**
			 * Get statuses in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Statuses in the search query.
			 */
			protected function get_statuses_in_search_query()
			{
				$statuses = array(); // Initialize.
				$s        = $this->get_raw_search_query();

				if($s && preg_match_all($this->statuses_regex, $s, $_m))
					foreach(preg_split('/[|;,]+/', implode(',', $_m['statuses']), NULL, PREG_SPLIT_NO_EMPTY) as $_status)
						if(isset($_status[0])) $statuses[$_status] = $_status;
				unset($_m, $_status); // Housekeeping.

				return $statuses;
			}

			/**
			 * Get events in the search query.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Events in the search query.
			 */
			protected function get_events_in_search_query()
			{
				$events = array(); // Initialize.
				$s      = $this->get_raw_search_query();

				if($s && preg_match_all($this->events_regex, $s, $_m))
					foreach(preg_split('/[|;,]+/', implode(',', $_m['events']), NULL, PREG_SPLIT_NO_EMPTY) as $_event)
						if(isset($_event[0])) $events[$_event] = $_event;
				unset($_m, $_event); // Housekeeping.

				return $events;
			}

			/**
			 * Are we dealing w/ an `AND` search?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if it's an `AND` search.
			 */
			protected function is_and_search_query()
			{
				$s = $this->get_raw_search_query();

				return $s && preg_match($this->and_regex, $s);
			}

			/**
			 * Get orderby value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The orderby value.
			 */
			protected function get_orderby()
			{
				$orderby = !empty($_REQUEST['orderby'])
					? strtolower(trim(stripslashes((string)$_REQUEST['orderby'])))
					: ''; // Not specified explicitly by site owner.

				if(!$orderby || !in_array($orderby, array_keys($this->get_columns()), TRUE))
					$orderby = $this->get_clean_search_query() && $this->get_ft_searchable_columns() ? 'relevance' : '';

				if($this->is_clean_search_submit_post()) // Force `orderby`.
					$orderby = $this->get_ft_searchable_columns() ? 'relevance' : '';

				$orderby = !$orderby ? $this->get_default_orderby() : $orderby;

				$_GET['orderby'] = $_REQUEST['orderby'] = addslashes($orderby);
				if(isset($_POST['orderby'])) $_POST['orderby'] = addslashes($orderby);

				return $orderby; // Current orderby.
			}

			/**
			 * Get default orderby value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The default orderby value.
			 *
			 * @extenders Extenders should normally override this.
			 */
			protected function get_default_orderby()
			{
				return ''; // Default orderby.
			}

			/**
			 * Get order value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The order value.
			 */
			protected function get_order()
			{
				$order = !empty($_REQUEST['order'])
					? strtolower(trim(stripslashes((string)$_REQUEST['order'])))
					: ''; // Not specified explicitly by site owner.

				if(!$order || !in_array($order, array('asc', 'desc'), TRUE))
					$order = $this->get_clean_search_query() ? 'desc' : '';

				if($this->is_clean_search_submit_post())
					$order = 'desc'; // Force by relevance.

				$order = !$order ? $this->get_default_order() : $order;

				$_GET['order'] = $_REQUEST['order'] = addslashes($order);
				if(isset($_POST['order'])) $_POST['order'] = addslashes($order);

				return $order; // Current order.
			}

			/**
			 * Get default order value.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string The default order value.
			 *
			 * @extenders Extenders should normally override this.
			 */
			protected function get_default_order()
			{
				return ''; // Default order.
			}

			/*
			 * Public query-related methods.
			 */

			/**
			 * Runs DB query; sets pagination args.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @extenders Extenders should ALWAYS override this.
			 */
			public function prepare_items() // The heart of this class.
			{
				/*
				 * This is just a simple example showing
				 * only the most useful getters/setters/helpers.
				 */
				$per_page                    = $this->get_per_page();
				$current_offset              = $this->get_current_offset();
				$clean_search_query          = $this->get_clean_search_query();
				$sub_ids_in_search_query     = $this->get_sub_ids_in_search_query();
				$user_ids_in_search_query    = $this->get_user_ids_in_search_query();
				$post_ids_in_search_query    = $this->get_post_ids_in_search_query();
				$comment_ids_in_search_query = $this->get_comment_ids_in_search_query();
				$statuses_in_search_query    = $this->get_statuses_in_search_query();
				$events_in_search_query      = $this->get_events_in_search_query();
				$is_and_search_query         = $this->is_and_search_query();
				$orderby                     = $this->get_orderby();
				$order                       = $this->get_order();

				$this->set_items(array()); // `$this->items` = an array of \stdClass objects.
				$this->set_total_items_available((integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()"));

				$this->prepare_items_merge_subscr_type_property();
				$this->prepare_items_merge_sub_properties();
				$this->prepare_items_merge_user_properties();
				$this->prepare_items_merge_post_properties();
				$this->prepare_items_merge_comment_properties();
			}

			/**
			 * Prepares searchable columns.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $template The template to use for matches.
			 *    Defaults to `= %1$s` for exact matches.
			 *
			 * @return string A sequence of `OR ` checks for SQL matches.
			 */
			protected function prepare_searchable_or_cols($template = '')
			{
				if(!$this->get_searchable_columns())
					return ''; // Not applicable.

				if(!($clean_search_query = $this->get_clean_search_query()))
					return ''; // Not applicable.

				$search_like_cols            = ''; // Initialize.
				$esc_clean_search_query      = esc_sql($clean_search_query);
				$esc_like_clean_search_query = esc_sql($this->plugin->utils_db->wp->esc_like($clean_search_query));

				if(!($template = trim(str_replace(array("'", '"'), '', (string)$template))))
					$template = '= %1$s'; // Use the default template.

				foreach($this->get_searchable_columns() as $_column)
					$search_like_cols .= " OR `".esc_sql($_column)."`". // Using the template.
					                     " ".sprintf($template, "'".$esc_clean_search_query."'", "'".$esc_like_clean_search_query."'");
				unset($_column); // Housekeeping.

				return $search_like_cols;
			}

			/*
			 * Protected query-related getters/setters.
			 */

			/**
			 * Gets configured items per page.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return integer Configured items per page.
			 */
			protected function get_per_page()
			{
				$max_limit       = $this->plugin->utils_user->screen_option($this->screen, 'per_page');
				$upper_max_limit = (integer)apply_filters(get_class($this).'_upper_max_limit', 1000);

				$max_limit = $max_limit < 1 ? 1 : $max_limit;
				$max_limit = $max_limit > $upper_max_limit ? 100 : $max_limit;

				return $max_limit;
			}

			/**
			 * Gets current page number.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return integer Current page number.
			 */
			protected function get_current_page()
			{
				return ($current_page = $this->get_pagenum());
			}

			/**
			 * Gets current SQL offset.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return integer Current SQL offset value.
			 */
			protected function get_current_offset()
			{
				return ($current_offset = ($this->get_current_page() - 1) * $this->get_per_page());
			}

			/**
			 * Sets total items and pagination args.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass[] An array of \stdClass objects.
			 *
			 * @return \stdClass[] Returns the items.
			 */
			protected function set_items($items)
			{
				return ($this->items = $items);
			}

			/**
			 * Sets total items and pagination args.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $calc_found_rows Total found rows using `SQL_CALC_FOUND_ROWS`.
			 *
			 * @return integer Total items available; i.e. number of found rows.
			 */
			protected function set_total_items_available($calc_found_rows)
			{
				$per_page    = $this->get_per_page();
				$total_items = (integer)$calc_found_rows;
				$total_pages = ceil($total_items / $per_page);
				$this->set_pagination_args(compact('per_page', 'total_items', 'total_pages'));

				return $total_items;
			}

			/*
			 * Protected query-related helpers.
			 */

			/**
			 * Assists w/ DB query; i.e. item preparations.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function prepare_items_merge_subscr_type_property()
			{
				foreach($this->items as $_item)
				{
					$_item->subscr_type = NULL; // Initialize.

					if(!isset($_item->post_id, $_item->comment_id))
						continue; // Not possible.

					if($_item->post_id && !$_item->comment_id)
						$_item->subscr_type = 'comments';

					else if($_item->post_id && $_item->comment_id)
						$_item->subscr_type = 'comment';
				}
				unset($_item); // Housekeeping.

				$this->items = $this->plugin->utils_db->typify_deep($this->items);
			}

			/**
			 * Assists w/ DB query; i.e. item preparations.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function prepare_items_merge_sub_properties()
			{
				$sub_ids = array(); // Initialize.

				foreach($this->items as $_item)
					if(!empty($_item->sub_id)) // Has a sub ID?
						$sub_ids[$_item->sub_id] = $_item->sub_id;
				unset($_item); // Housekeeping.

				foreach($this->items as $_item)
					if(!empty($_item->oby_sub_id)) // Overwritten by a sub ID?
						$sub_ids[$_item->oby_sub_id] = $_item->oby_sub_id;
				unset($_item); // Housekeeping.

				$sql_columns      = array(
					'ID',
					'key',
					'user_id',
					'post_id',
					'comment_id',
					'deliver',
					'fname',
					'lname',
					'email',
					'insertion_ip',
					'last_ip',
					'status',
					'insertion_time',
					'last_update_time',
				);
				$sql_item_columns = $sql_columns;
				unset($sql_item_columns[0]); // Exclude `ID`.

				$sql = "SELECT `".implode('`,`', array_map('esc_sql', $sql_columns))."`".
				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `ID` IN('".implode("','", array_map('esc_sql', $sub_ids))."')";

				if($sub_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					$this->merged_result_sets['subs'] = $results = $this->plugin->utils_db->typify_deep($results);

				foreach($this->items as $_item)
				{
					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'sub_') !== 0)
							$_item->{'sub_'.$_sql_item_column} = NULL;
						else $_item->{$_sql_item_column} = NULL;

					if(!isset($_item->sub_id))
						continue; // Not possible.

					if(empty($results) || empty($results[$_item->sub_id]))
						continue; // Not possible.

					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'sub_') !== 0)
							$_item->{'sub_'.$_sql_item_column} = $results[$_item->sub_id]->{$_sql_item_column};
						else $_item->{$_sql_item_column} = $results[$_item->sub_id]->{$_sql_item_column};
				}
				unset($_item, $_sql_item_column); // Housekeeping.

				foreach($this->items as $_item)
				{
					foreach($sql_item_columns as $_sql_item_column)
						$_item->{'oby_sub_'. // Prefix each of these.
						         preg_replace('/^sub_/i', '', $_sql_item_column)} = NULL;

					if(!isset($_item->oby_sub_id))
						continue; // Not possible.

					if(empty($results) || empty($results[$_item->oby_sub_id]))
						continue; // Not possible.

					foreach($sql_item_columns as $_sql_item_column)
						$_item->{'oby_sub_'. // Prefix each of these.
						         preg_replace('/^sub_/i', '', $_sql_item_column)}
							= $results[$_item->oby_sub_id]->{$_sql_item_column};
				}
				unset($_item, $_sql_item_column); // Housekeeping.

				$this->items = $this->plugin->utils_db->typify_deep($this->items);
			}

			/**
			 * Assists w/ DB query; i.e. item preparations.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function prepare_items_merge_user_properties()
			{
				$user_ids = array(); // Initialize.

				foreach($this->items as $_item)
					if(!empty($_item->user_id)) // Has a user ID?
						$user_ids[$_item->user_id] = $_item->user_id;
				unset($_item); // Housekeeping.

				$sql_columns      = array(
					'ID',
					'user_login',
					'user_nicename',
					'user_email',
					'user_url',
					'user_registered',
					'user_activation_key',
					'user_status',
					'display_name',
				);
				$sql_item_columns = $sql_columns;
				unset($sql_item_columns[0]); // Exclude `ID`.

				$sql = "SELECT `".implode('`,`', array_map('esc_sql', $sql_columns))."`".
				       " FROM `".esc_sql($this->plugin->utils_db->wp->users)."`".
				       " WHERE `ID` IN('".implode("','", array_map('esc_sql', $user_ids))."')";

				if($user_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					$this->merged_result_sets['users'] = $results = $this->plugin->utils_db->typify_deep($results);

				foreach($this->items as $_item)
				{
					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'user_') !== 0)
							$_item->{'user_'.$_sql_item_column} = NULL;
						else $_item->{$_sql_item_column} = NULL;

					if(!isset($_item->user_id))
						continue; // Not possible.

					if(empty($results) || empty($results[$_item->user_id]))
						continue; // Not possible.

					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'user_') !== 0)
							$_item->{'user_'.$_sql_item_column} = $results[$_item->user_id]->{$_sql_item_column};
						else $_item->{$_sql_item_column} = $results[$_item->user_id]->{$_sql_item_column};
				}
				unset($_item, $_sql_item_column); // Housekeeping.

				$this->items = $this->plugin->utils_db->typify_deep($this->items);
			}

			/**
			 * Assists w/ DB query; i.e. item preparations.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function prepare_items_merge_post_properties()
			{
				$post_ids = array(); // Initialize.

				foreach($this->items as $_item)
					if(!empty($_item->post_id)) // Has a post ID?
						$post_ids[$_item->post_id] = $_item->post_id;
				unset($_item); // Housekeeping.

				$sql_columns      = array(
					'ID',
					'post_title',
					'post_status',
					'comment_status',
					'post_date_gmt',
					'post_type',
					'comment_count',
				);
				$sql_item_columns = $sql_columns;
				unset($sql_item_columns[0]); // Exclude `ID`.

				$sql = "SELECT `".implode('`,`', array_map('esc_sql', $sql_columns))."`".
				       " FROM `".esc_sql($this->plugin->utils_db->wp->posts)."`".
				       " WHERE `ID` IN('".implode("','", array_map('esc_sql', $post_ids))."')";

				if($post_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					$this->merged_result_sets['posts'] = $results = $this->plugin->utils_db->typify_deep($results);

				foreach($this->items as $_item)
				{
					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'post_') !== 0)
							$_item->{'post_'.$_sql_item_column} = NULL;
						else $_item->{$_sql_item_column} = NULL;

					if(!isset($_item->post_id))
						continue; // Not possible.

					if(empty($results) || empty($results[$_item->post_id]))
						continue; // Not possible.

					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'post_') !== 0)
							$_item->{'post_'.$_sql_item_column} = $results[$_item->post_id]->{$_sql_item_column};
						else $_item->{$_sql_item_column} = $results[$_item->post_id]->{$_sql_item_column};
				}
				unset($_item, $_sql_item_column); // Housekeeping.

				$this->items = $this->plugin->utils_db->typify_deep($this->items);
			}

			/**
			 * Assists w/ DB query; i.e. item preparations.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function prepare_items_merge_comment_properties()
			{
				$comment_ids = array(); // Initialize.

				foreach($this->items as $_item)
					if(!empty($_item->comment_id)) // Has a comment ID?
						$comment_ids[$_item->comment_id] = $_item->comment_id;
				unset($_item); // Housekeeping.

				foreach($this->items as $_item)
					if(!empty($_item->comment_parent_id)) // Has a comment parent ID?
						$comment_ids[$_item->comment_parent_id] = $_item->comment_parent_id;
				unset($_item); // Housekeeping.

				$sql_columns      = array(
					'comment_ID',
					'comment_author',
					'comment_author_email',
					'comment_date_gmt',
					'comment_approved',
					'comment_type',
					'comment_parent',
				);
				$sql_item_columns = $sql_columns;
				unset($sql_item_columns[0]); // Exclude `comment_ID`.

				$sql = "SELECT `".implode('`,`', array_map('esc_sql', $sql_columns))."`".
				       " FROM `".esc_sql($this->plugin->utils_db->wp->comments)."`".
				       " WHERE `comment_ID` IN('".implode("','", array_map('esc_sql', $comment_ids))."')";

				if($comment_ids && ($results = $this->plugin->utils_db->wp->get_results($sql, OBJECT_K)))
					$this->merged_result_sets['comments'] = $results = $this->plugin->utils_db->typify_deep($results);

				foreach($this->items as $_item)
				{
					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'comment_') !== 0)
							$_item->{'comment_'.$_sql_item_column} = NULL;
						else $_item->{$_sql_item_column} = NULL;

					if(!isset($_item->comment_id))
						continue; // Not possible.

					if(empty($results) || empty($results[$_item->comment_id]))
						continue; // Not possible.

					foreach($sql_item_columns as $_sql_item_column)
						if(strpos($_sql_item_column, 'comment_') !== 0)
							$_item->{'comment_'.$_sql_item_column} = $results[$_item->comment_id]->{$_sql_item_column};
						else $_item->{$_sql_item_column} = $results[$_item->comment_id]->{$_sql_item_column};
				}
				unset($_item, $_sql_item_column); // Housekeeping.

				foreach($this->items as $_item)
				{
					foreach($sql_item_columns as $_sql_item_column)
						$_item->{'comment_parent_'. // Prefix each of these.
						         preg_replace('/^comment_/i', '', $_sql_item_column)} = NULL;

					if(!isset($_item->comment_parent_id))
						continue; // Not possible.

					if(empty($results) || empty($results[$_item->comment_parent_id]))
						continue; // Not possible.

					foreach($sql_item_columns as $_sql_item_column)
						$_item->{'comment_parent_'. // Prefix each of these.
						         preg_replace('/^comment_/i', '', $_sql_item_column)}
							= $results[$_item->comment_parent_id]->{$_sql_item_column};
				}
				unset($_item, $_sql_item_column); // Housekeeping.

				$this->items = $this->plugin->utils_db->typify_deep($this->items);
			}

			/*
			 * Protected action-related methods.
			 */

			/**
			 * Bulk actions for this table.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all bulk actions.
			 *
			 * @extenders Extenders should normally override this.
			 */
			protected function get_bulk_actions()
			{
				return array();
			}

			/**
			 * Bulk action handler for this table.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_process_bulk_action()
			{
				if(!($bulk_action = stripslashes((string)$this->current_action())))
					return; // Nothing to do; no action requested here.

				if(!$this->plugin->utils_url->has_valid_nonce('bulk-'.$this->plural_name))
					return; // Unauthenticated; ignore.

				if(empty($_REQUEST[$this->plural_name]) || !is_array($_REQUEST[$this->plural_name]))
					return; // Nothing to do; i.e. no boxes were checked in this case.

				if(!($ids = array_map('intval', $_REQUEST[$this->plural_name])))
					return; // Nothing to do; i.e. we have no IDs.

				if(!current_user_can($this->plugin->manage_cap))
					if(!current_user_can($this->plugin->cap))
						return; // Unauthenticated; ignore.

				$counter = $this->process_bulk_action($bulk_action, $ids);

				if(method_exists($this->plugin->utils_i18n, $this->plural_name))
					$this->plugin->enqueue_user_notice
					(
						sprintf(__('Action complete. %1$s %2$s.', $this->plugin->text_domain),
						        esc_html($this->plugin->utils_i18n->{$this->plural_name}($counter)),
						        esc_html($this->plugin->utils_i18n->action_ed($bulk_action))),

						array('transient' => TRUE, 'for_page' => $this->plugin->utils_env->current_menu_page())
					);
				$redirect_to = $this->plugin->utils_url->page_table_nav_vars_only();

				if(headers_sent()) // Output started already?
					exit('      <script type="text/javascript">'.
					     "         document.getElementsByTagName('body')[0].style.display = 'none';".
					     "         location.href = '".$this->plugin->utils_string->esc_js_sq($redirect_to)."';".
					     '      </script>'.
					     '   </body>'.
					     '</html>');
				wp_redirect($redirect_to).exit();
			}

			/**
			 * Bulk action handler for this table.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $bulk_action The bulk action to process.
			 * @param array  $ids The bulk action IDs to process.
			 *
			 * @return integer Number of actions processed successfully.
			 *
			 * @extenders Extenders should normally override this.
			 */
			protected function process_bulk_action($bulk_action, array $ids)
			{
				return !empty($counter) ? (integer)$counter : 0;
			}

			/*
			 * Public display-related methods.
			 */

			/**
			 * Display search box.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $text The search button `value=""`.
			 *    This will default to a value of `Search`.
			 *
			 * @param string $input_id The search input `id=""` attribute.
			 *    This parameter is always forced to an id-compatible value.
			 *    This will default to a value of `get_class($this).'::'.__FUNCTION__`.
			 *
			 * @throws \exception If unable to do `name="search-submit"` replacement.
			 */
			public function search_box($text = '', $input_id = '')
			{
				if(!$this->get_ft_searchable_columns()
				   && !$this->get_searchable_columns()
				   && !$this->get_navigable_filters()
				) return; // Not applicable.

				$text     = (string)$text;
				$input_id = (string)$input_id;
				$text     = !$text ? __('Search', $this->plugin->text_domain) : esc_html($text);
				$input_id = !$input_id ? get_class($this).'::'.__FUNCTION__ : $input_id;
				$input_id = trim(preg_replace('/[^a-z0-9\-]/i', '-', $input_id), '-');

				ob_start(); // Open an output buffer.
				parent::search_box($text, $input_id);
				$search_box = ob_get_clean();

				$regex = '/\bname\s*\=\s*(["\']).*?\\1\s+id\s*\=\s*(["\'])search\-submit\\2/i';

				if($search_box) // Only if there is a search box; it doesn't always display.
					if(!($search_box = preg_replace($regex, 'name="search-submit" id="search-submit"', $search_box, 1, $replacements)) || !$replacements)
						throw new \exception(__('Unable to set `name="search-submit"` attribute.', $this->plugin->text_domain));

				echo $search_box; // Display.
			}

			/**
			 * Display search query filter descs.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function search_query_filter_descriptions()
			{
				$sub_ids           = $this->get_sub_ids_in_search_query();
				$user_ids          = $this->get_user_ids_in_search_query();
				$post_ids          = $this->get_post_ids_in_search_query();
				$comment_ids       = $this->get_comment_ids_in_search_query();
				$statuses          = $this->get_statuses_in_search_query();
				$events            = $this->get_events_in_search_query();
				$navigable_filters = $this->get_navigable_filters();
				$raw_search_query  = $this->get_raw_search_query();

				$query_contains_filters           = $sub_ids || $user_ids || $post_ids || $comment_ids;
				$query_contains_navigable_filters = !empty($statuses) || !empty($events);
				$navigable_filters_exist          = !empty($navigable_filters);

				if(!$query_contains_filters && !$navigable_filters_exist)
					return; // Nothing to do here.

				$subs    = $users = $posts = $comments = array(); // Array of bject references.
				$sub_lis = $user_lis = $post_lis = $comment_lis = $navigable_filter_lis = $unknown_lis = array();

				foreach($sub_ids as $_sub_id)
					if(($_sub = $this->plugin->utils_sub->get($_sub_id)))
						$subs[] = $_sub;
				unset($_sub_id, $_sub); // Housekeeping.

				foreach($user_ids as $_user_id)
					if(($_user = new \WP_User($_user_id)) && $_user->ID)
						$users[] = $_user;
				unset($_user_id, $_user); // Housekeeping.

				foreach($post_ids as $_post_id)
					if(($_post = get_post($_post_id)))
						$posts[] = $_post;
				unset($_post_id, $_post); // Housekeeping.

				foreach($comment_ids as $_comment_id)
					if(($_comment = get_comment($_comment_id)))
						$comments[] = $_comment;
				unset($_comment_id, $_comment); // Housekeeping.

				foreach($subs as $_sub) // `\stdClass` objects.
				{
					/** @var $_sub \stdClass Reference for IDEs. */

					if(isset($sub_lis[$_sub->ID]))
						continue; // Duplicate.

					$_name_email_args = array(
						'email_style'        => 'font-weight:bold;',
						'anchor_to'          => 'summary',
						'anchor_target'      => '_blank',
						'summary_anchor_key' => $_sub->key,
					);
					$_sub_name        = $_sub->fname.' '.$_sub->lname; // Concatenate.
					$_sub_edit_link   = $this->plugin->utils_url->edit_sub_short($_sub->ID);

					$sub_lis[$_sub->ID] = '<li>'. // ♙ ID "Name" <email> [edit].
					                      '<i class="fa fa-user"></i>'. // e.g. ♙ ID "Name" <email>; w/ key in hover title.
					                      ' <span style="font-weight:bold;" title="'.esc_attr($_sub->key).'">ID #'.esc_html($_sub->ID).'</span>'.
					                      ' '.$this->plugin->utils_markup->name_email($_sub_name, $_sub->email, $_name_email_args).
					                      ($_sub_edit_link // Only if they can edit the subscription ID; else this will be empty.
						                      ? ' [<a href="'.esc_attr($_sub_edit_link).'">'.__('edit', $this->plugin->text_domain).'</a>]' : '').
					                      '</li>';
				}
				unset($_sub, $_name_email_args, $_sub_name, $_sub_edit_link); // Housekeeping.

				foreach($users as $_user) // `\WP_User` objects.
				{
					/** @var $_user \WP_User Reference for IDEs. */

					if(isset($user_lis[$_user->ID]))
						continue; // Duplicate.

					$_name_email_args = array(
						'email_style' => 'font-weight:normal;',
					);
					$_user_edit_link  = get_edit_user_link($_user->ID);

					$user_lis[$_user->ID] = '<li>'. // ♙ ID "Name" <email> [edit].
					                        '<i class="fa fa-user"></i>'. // e.g. ♙ ID "Name" <email>; w/ key in hover title.
					                        ' <span style="font-weight:bold;" title="'.esc_attr($_user->user_login).'">ID #'.esc_html($_user->ID).'</span>'.
					                        ' '.$this->plugin->utils_markup->name_email($_user->display_name, $_user->user_email, $_name_email_args).
					                        ($_user_edit_link // Only if they can edit the user ID; else this will be empty.
						                        ? ' [<a href="'.esc_attr($_user_edit_link).'">'.__('edit', $this->plugin->text_domain).'</a>]' : '').
					                        '</li>';
				}
				unset($_user, $_name_email_args, $_user_edit_link); // Housekeeping.

				foreach($posts as $_post) // `\WP_Post` objects.
				{
					/** @var $_post \WP_Post Reference for IDEs. */

					if(isset($post_lis[$_post->ID]))
						continue; // Duplicate.

					if(!($_post_type = get_post_type_object($_post->post_type)))
						continue; // Unable to determine type.

					$_post_permalink  = get_permalink($_post->ID);
					$_post_edit_link  = get_edit_post_link($_post->ID, '');
					$_post_title_clip = $this->plugin->utils_string->mid_clip($_post->post_title);
					$_post_type_label = $_post_type->labels->singular_name;

					$post_lis[$_post->ID] = '<li>'. // Type ID: <title> [edit].
					                        '  <span style="font-weight:bold;">'.esc_html($_post_type_label).'</span>'.
					                        '  <span style="font-weight:bold;">ID #'.esc_html($_post->ID).':</span>'.
					                        '  &ldquo;<a href="'.esc_attr($_post_permalink).'" target="_blank">'.esc_html($_post_title_clip).'</a>&rdquo;'.
					                        ($_post_edit_link // Only if they can edit the post ID; else this will be empty.
						                        ? ' [<a href="'.esc_attr($_post_edit_link).'">'.__('edit', $this->plugin->text_domain).'</a>]' : '').
					                        '</li>';
				}
				unset($_post, $_post_type, $_post_permalink, $_post_edit_link, $_post_title_clip, $_post_type_label); // Housekeeping.

				foreach($comments as $_comment) // `\stdClass` objects.
				{
					/** @var $_comment \stdClass Reference for IDEs. */
					/** @var $_post \WP_Post Reference for IDEs. */

					if(isset($comment_lis[$_comment->comment_ID]))
						continue; // Duplicate.

					if(!($_post = get_post($_comment->comment_post_ID)))
						continue; // Unable to get underlying post.

					if(!($_post_type = get_post_type_object($_post->post_type)))
						continue; // Unable to determine type.

					$_name_email_args = array(
						'email_style' => 'font-weight:normal;',
					);
					$_post_permalink  = get_permalink($_post->ID);
					$_post_edit_link  = get_edit_post_link($_post->ID, '');
					$_post_title_clip = $this->plugin->utils_string->mid_clip($_post->post_title);
					$_post_type_label = $_post_type->labels->singular_name;

					$_comment_permalink    = get_comment_link($_comment->comment_ID);
					$_comment_edit_link    = get_edit_comment_link($_comment->comment_ID);
					$_comment_content_clip = $this->plugin->utils_string->clip($_comment->comment_content, 100);

					$comment_lis[$_comment->comment_ID] = '<li>'. // Type ID: <title> [edit].
					                                      '   <span style="font-weight:normal;">'.esc_html($_post_type_label).'</span>'.
					                                      '   <span style="font-weight:normal;">ID #'.esc_html($_post->ID).':</span>'.
					                                      '   &ldquo;<a href="'.esc_attr($_post_permalink).'" target="_blank">'.esc_html($_post_title_clip).'</a>&rdquo;'.
					                                      ($_post_edit_link // Only if they can edit the post ID; else this will be empty.
						                                      ? ' [<a href="'.esc_attr($_post_edit_link).'">'.__('edit', $this->plugin->text_domain).'</a>]' : '').

					                                      '   <ul>'. // Nest comment under post.
					                                      '      <li>'. // Comment ID: <author> [edit] ... followed by a content clip.
					                                      '         <span style="font-weight:bold;">'.__('Comment', $this->plugin->text_domain).'</span>'.
					                                      '         <span style="font-weight:bold;">ID <a href="'.esc_attr($_comment_permalink).'" target="_blank">#'.esc_html($_comment->comment_ID).'</a>:</span>'.
					                                      '         '.$this->plugin->utils_markup->name_email($_comment->comment_author, $_comment->comment_author_email, $_name_email_args).
					                                      ($_comment_edit_link // Only if they can edit the comment ID; else this will be empty.
						                                      ? '     [<a href="'.esc_attr($_comment_edit_link).'">'.__('edit', $this->plugin->text_domain).'</a>]' : '').
					                                      '         <blockquote>'.esc_html($_comment_content_clip).'</blockquote>'.
					                                      '      </li>'.
					                                      '   </ul>'.
					                                      '</li>';
				}
				unset($_comment, $_post, $_post_type, $_name_email_args, $_post_permalink, $_post_edit_link, $_post_title_clip, $_post_type_label, $_comment_permalink, $_comment_edit_link, $_comment_content_clip); // Housekeeping.

				foreach($navigable_filters as $_navigable_filter_s => $_navigable_filter_label)
				{
					if(!$navigable_filter_lis) // `all` first; i.e. a way to remove all navigable filters.
						$navigable_filter_lis[] = '<li>'. // List item for special navigable filter `all`.
						                          '   <a href="'.esc_attr($this->plugin->utils_url->table_search_filter('::')).'"'.
						                          (!$query_contains_navigable_filters ? ' class="pmp-active"' : '').'>'.
						                          '      '.__('all', $this->plugin->text_domain).
						                          '   </a>'.
						                          '</li>';
					$navigable_filter_lis[] = '<li>'. // List item for a navigable filter in this table.
					                          '   <a href="'.esc_attr($this->plugin->utils_url->table_search_filter($_navigable_filter_s)).'"'.
					                          (stripos($raw_search_query, $_navigable_filter_s) !== FALSE ? ' class="pmp-active"' : '').'>'.
					                          '      <span style="'.esc_attr($_navigable_filter_s === 'status::trashed' ? 'font-style:italic;' : '').'">'.
					                          '         '.esc_html($_navigable_filter_label).'</span>'.
					                          '   </a>'.
					                          '</li>';
				}
				unset($_navigable_filter_s, $_navigable_filter_label); // Housekeeping.

				$filter_lis_exist           = $sub_lis || $user_lis || $post_lis || $comment_lis; // Have any of these?
				$navigable_filter_lis_exist = !empty($navigable_filter_lis); // Have any navigable list items?

				if($query_contains_filters) // If query contains non-navigable filters.
				{
					if(!$filter_lis_exist) // Unable to build list items for search filter(s)?
						$unknown_lis[] = '<li>'.sprintf(__('Unknown filter(s). Unable to build list items for: <code>%1$s</code>', $this->plugin->text_domain),
						                                esc_html($this->get_raw_search_query())).'</li>';
					echo '<h3>'. // Display.
					     '   <i class="fa fa-filter"></i>'. // Filter icon.
					     '   '.sprintf(__('<strong>Search Filters Applied</strong> :: only showing %1$s for:', $this->plugin->text_domain), esc_html($this->plural_label)).
					     '</h3>';
					if($sub_lis) echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $sub_lis).'</ul>';
					if($user_lis) echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $user_lis).'</ul>';
					if($post_lis) echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $post_lis).'</ul>';
					if($comment_lis) echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $comment_lis).'</ul>';
					if($unknown_lis) echo '<ul class="pmp-search-filters pmp-filters pmp-list-items">'.implode('', $unknown_lis).'</ul>';
				}
				if($navigable_filter_lis_exist && $navigable_filter_lis)
					echo '<ul class="pmp-navigable-filters pmp-filters pmp-clean-list-items">'.
					     ' <li>'.__('Navigable Filters:', $this->plugin->text_domain).'</li>'.
					     ' '.implode('', $navigable_filter_lis).
					     '</ul>';
			}

			/**
			 * Prints column headers.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param boolean $with_id Add an `id=""` attribute?
			 */
			public function print_column_headers($with_id = TRUE)
			{
				ob_start(); // Open an output buffer.
				parent::print_column_headers($with_id);
				$column_headers = ob_get_clean();

				$regex = '/\b(href\s*\=\s*)(["\'])(.+?)(\\2)/i';

				if(($raw_search_query = $this->get_raw_search_query()))
					$column_headers = preg_replace_callback($regex, function ($m) use ($raw_search_query)
					{
						$m[3] = wp_specialchars_decode($m[3], ENT_QUOTES);
						$m[3] = add_query_arg('s', urlencode($raw_search_query), $m[3]);
						return $m[1].$m[2].esc_attr($m[3]).$m[4]; #

					}, $column_headers);

				echo $column_headers; // Display.
			}

			/**
			 * Prints no items message.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function no_items()
			{
				echo esc_html(sprintf(__('No %1$s to display.', $this->plugin->text_domain), $this->plural_label));
			}

			/**
			 * Display the table.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function display()
			{
				$this->search_box(); // When applicable.
				$this->search_query_filter_descriptions(); // When applicable.
				parent::display(); // Call parent handler now.
			}
		}
	}
}