<h2 style="margin-top:0; font-weight:normal; font-family:serif;">
	[sub_fname], please <a href="[sub_confirm_url]"><strong>click here to confirm</strong></a> your subscription.
</h2>

<hr />

<p style="margin-left:1em;">

	[if sub_comment]
		[if subscribed_to_own_comment]
			You'll be notified about replies to <a href="[sub_comment_url]">your comment</a>; on:
		[else]
			You'll be notified about replies to <a href="[sub_comment_url]">comment ID #[sub_comment_id]</a>; on:
		[endif]
	[else]
		You'll be notified about all comments/replies to:
	[endif]<br />

	<span style="font-size:120%;">
		“[sub_post_title_clip]”
	</span><br />

	[if sub_comment]
		<a href="[sub_comment_url]">[sub_comment_url]</a>
	[else]
		<a href="[sub_post_comments_url]">[sub_post_comments_url]</a>
	[endif]

</p>