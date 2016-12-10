<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin      $plugin Plugin class.
 * @var Template    $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var integer     $post_id The post ID for which to obtain comments.
 * @var FormFields $form_fields Form fields class.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo $form_fields->selectRow(
    [
        'placeholder'         => __('— All Comments/Replies —', 'comment-mail'),
        'label'               => __('<i class="fa fa-fw fa-comment-o" aria-hidden="true"></i> Comment', 'comment-mail'),
        'name'                => 'comment_id', 'required' => false, 'options' => '%%comments%%', 'post_id' => $post_id, 'current_value' => null,
        'input_fallback_args' => ['type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'current_value_empty_on_0' => true],
    ]
);
?>
