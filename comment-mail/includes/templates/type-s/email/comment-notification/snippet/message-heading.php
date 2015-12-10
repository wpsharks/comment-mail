<h2 style="margin-top:0; font-family:serif;">

	[if is_digest]

		[if sub_comment]
			[if subscribed_to_own_comment]
				New Replies to <a href="[sub_comment_url]">your Comment</a> on "[sub_post_title_clip]"
			[else]
				New Replies to <a href="[sub_comment_url]">Comment ID #[sub_comment_id]</a> on "[sub_post_title_clip]"
			[endif]
		[else]
			New Comments on "<a href="[sub_post_comments_url]">[sub_post_title_clip]</a>"
		[endif]

	[else]

		[if sub_comment]
			[if subscribed_to_own_comment]
				New Reply to <a href="[sub_comment_url]">your Comment</a> on "[sub_post_title_clip]"
			[else]
				New Reply to <a href="[sub_comment_url]">Comment ID #[sub_comment_id]</a> on "[sub_post_title_clip]"
			[endif]
		[else]
			New Comment on "<a href="[sub_post_comments_url]">[sub_post_title_clip]</a>"
		[endif]

	[endif]

</h2>
