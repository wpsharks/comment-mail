<h2 style="margin-top:0; font-weight:normal;">
    [sub_fname], please <a href="[sub_confirm_url]"><strong>click here to confirm</strong></a> your subscription.
</h2>

<hr />

<p>

    [if sub_comment]
        [if subscribed_to_own_comment]
            You are receiving this email because you asked to be notified about replies to <a href="[sub_comment_url]">your comment</a>; on:
        [else]
            You are receiving this email because you asked to be notified about replies to <a href="[sub_comment_url]">this comment</a>; on:
        [endif]
    [else]
        You are receiving this email because you asked to be notified about all comments/replies to:
    [endif]

</p>

<p>
    <span style="font-size:120%;">
        [sub_post_title_clip]
    </span>
</p>

<p>

    [if sub_comment]
        <a href="[sub_comment_url]">[sub_comment_url]</a>
    [else]
        <a href="[sub_post_comments_url]">[sub_post_comments_url]</a>
    [endif]

</p>
