<div class="alert alert-success">
    <h4 style="margin:0;">
        <i class="fa fa-check fa-fw" aria-hidden="true"></i> Confirmed successfully. Thank you[if sub_fname] [sub_fname][endif]!
    </h4>
</div>

<h4>
    [if sub_comment]
        [if subscribed_to_own_comment]
            You'll be notified about replies to <a href="[sub_comment_url]">your comment</a>; on:
        [else]
            You'll be notified about replies to <a href="[sub_comment_url]">comment ID #[sub_comment_id]</a>; on:
        [endif]
    [else]
        You'll be notified about all comments/replies to:
    [endif]
</h4>

<h4>
    <i class="fa fa-thumb-tack" aria-hidden="true"></i>
    [if sub_comment]
        <em><a href="[sub_comment_url]">[sub_post_title_clip]</a></em>
    [else]
        <em><a href="[sub_post_comments_url]">[sub_post_title_clip]</a></em>
    [endif]
</h4>

<hr style="margin:0 0 1em 0;" />

<p>
    Your email address is: &lt;<code>[sub_email]</code>&gt;;<br />
    You chose delivery option: <code>[sub_deliver_label]</code>; [sub_deliver_description].
</p>

<hr style="margin:0 0 1em 0;" />

<h5 style="font-style:italic; margin:0;">
    <i class="fa fa-lightbulb-o" aria-hidden="true"></i> If any of this is incorrect, please <a href="[sub_edit_url]">click here to edit</a> your subscription.
</h5>
