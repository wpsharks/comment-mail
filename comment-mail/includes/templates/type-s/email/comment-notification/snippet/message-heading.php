<h2 style="margin-top:0;">

	[if is_digest]

		[if sub_comment]
			[if subscribed_to_own_comment]
				New Replies to Your Comment on [sub_post_title_clip]
			[else]
				New Replies to <a href="[sub_comment_url]">a Comment</a> on [sub_post_title_clip]
			[endif]
		[else]
			New Comments on <a href="[sub_post_comments_url]">[sub_post_title_clip]</a>
		[endif]

	[else]

		[if sub_comment]
			[if subscribed_to_own_comment]
				New Reply to Your Comment on [sub_post_title_clip]
			[else]
				New Reply to <a href="[sub_comment_url]">a Comment</a> on [sub_post_title_clip]
			[endif]
		[else]
			New Comment on <a href="[sub_post_comments_url]">[sub_post_title_clip]</a>
		[endif]

	[endif]

</h2>
