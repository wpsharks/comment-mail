<h2 style="margin-top:0;">

    [if is_digest]

        [if sub_comment]
            [if subscribed_to_own_comment]
                New Replies to Your Comment on <em>[sub_post_title_clip]</em>
            [else]
                New Replies to <a href="[sub_comment_url]">a Comment</a> on <em>[sub_post_title_clip]</em>
            [endif]
        [else]
            New Comments on <em><a href="[sub_post_comments_url]">[sub_post_title_clip]</a></em>
        [endif]

    [else]

        [if sub_comment]
            [if subscribed_to_own_comment]
                New Reply to Your Comment on <em>[sub_post_title_clip]</em>
            [else]
                New Reply to <a href="[sub_comment_url]">a Comment</a> on <em>[sub_post_title_clip]</em>
            [endif]
        [else]
            New Comment on <em><a href="[sub_post_comments_url]">[sub_post_title_clip]</a></em>
        [endif]

    [endif]

</h2>
