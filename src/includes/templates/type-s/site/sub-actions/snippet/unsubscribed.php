<div class="alert alert-success" style="margin:0;">
    <h4 style="margin:0;">
        <i class="fa fa-check fa-fw" aria-hidden="true"></i> Unsubscribed successfully. Sorry to see you go!
    </h4>
</div>

<div class="alert alert-danger text-center pull-right" style="margin:1em 1em 1em 1em;">
    <a class="text-danger" href="[sub_unsubscribe_all_url]" data-action="[sub_unsubscribe_all_url]"
       data-confirmation="Delete (unsubscribe) ALL subscriptions associated with your email address? Are you absolutely sure?"
       title="Delete (unsubscribe) ALL subscriptions associated with your email address?">
        <i class="fa fa-times-circle" aria-hidden="true"></i> Unsubscribe All?</a>
</div>

<h4>
    [if sub_comment]
        [if subscribed_to_own_comment]
            You'll no longer be notified about replies to <a href="[sub_comment_url]">your comment</a>; on:
        [else]
            You'll no longer be notified about replies to <a href="[sub_comment_url]">comment ID #[sub_comment_id]</a>; on:
        [endif]
    [else]
        You'll no longer be notified about comments/replies to:
    [endif]
</h4>

<h4>
    <i class="fa fa-thumb-tack" aria-hidden="true"></i>
    [if sub_post && sub_comment]
        <em><a href="[sub_comment_url]">[sub_post_title_clip]</a></em>
    [elseif sub_post]
        <em><a href="[sub_post_comments_url]">[sub_post_title_clip]</a></em>
    [else]
        Post ID #<code>[sub_post_id]</code>
    [endif]
</h4>

<hr style="margin:0 0 1em 0;" />

<h5 style="font-style:italic; margin:0;">
    <i class="fa fa-frown-o" aria-hidden="true"></i> Too many emails? ~ Please feel free to <a href="[sub_new_url]">add a new/different subscription</a> if you like!
</h5>
