<?php
namespace comment_mail;

/**
 * @var plugin      $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var integer     $post_id The post ID for which to obtain comments.
 * @var form_fields $form_fields Form fields class.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo $form_fields->select_row(
	array(
		'placeholder'         => __('— All Comments/Replies —', $plugin->text_domain),
		'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID #', $plugin->text_domain),
		'name'                => 'comment_id', 'required' => FALSE, 'options' => '%%comments%%', 'post_id' => $post_id, 'current_value' => NULL,
		'notes'               => __('If empty, you\'ll be subscribed to all comments/replies; i.e. NOT to a specific comment.', $plugin->text_domain),
		'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'current_value_empty_on_0' => TRUE),
	));
?>