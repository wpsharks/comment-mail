<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin           $plugin      Plugin class.
 * @var Template         $template    Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass        $sub         Subscription object data.
 *
 * @var \WP_Post         $sub_post    Post they're subscribed to.
 *
 * @var \WP_Comment|null $sub_comment Comment they're subcribed to; if applicable.
 *
 * -------------------------------------------------------------------
 * @note Extra whitespace in subject templates is stripped automatically.
 * That's why this template is able to break things down into multiple lines.
 * In the end, the email will contain a one-line subject of course.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php
/*
 * Here we define a few more variables of our own.
 * All based on what the template makes available to us;
 * ~ as documented at the top of this file.
 */
// A shorter clip of the full post title.
$sub_post_title_clip = $plugin->utils_string->clip($sub_post->post_title, 30);

// Subscribed to their own comment?
$subscribed_to_own_comment = $sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;
?>

<?php echo $template->snippet(
    'subject.php',
    [
        'sub_comment'               => $sub_comment,
        'subscribed_to_own_comment' => $subscribed_to_own_comment,

        '[sub_post_title_clip]' => $sub_post_title_clip,

        '[sub_comment_id]' => $sub_comment ? $sub_comment->comment_ID : 0,
    ]
); ?>
