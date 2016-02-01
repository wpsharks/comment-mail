[if is_digest]

	[if sub_comment]
		[if subscribed_to_own_comment]
			New Replies to your Comment on <em>[sub_post_title_clip]</em>
		[else]
			New Replies to Comment ID #[sub_comment_id] on <em>[sub_post_title_clip]</em>
		[endif]
	[else]
		New Comments on <em>[sub_post_title_clip]</em>
	[endif]

[else]

	[if sub_comment]
		[if subscribed_to_own_comment]
			New Reply to your Comment on <em>[sub_post_title_clip]</em>
		[else]
			New Reply to Comment ID #[sub_comment_id] on <em>[sub_post_title_clip]</em>
		[endif]
	[else]
		New Comment on <em>[sub_post_title_clip]</em>
	[endif]

[endif]
