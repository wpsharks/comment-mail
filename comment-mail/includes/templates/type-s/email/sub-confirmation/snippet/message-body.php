<p style="margin-left:1em;">
	You\'ll be notified about replies to []; on:<br />

	<span style="font-size:120%;">
		“<?php echo esc_html($sub_post_title_clip); ?>”
	</span><br />

	<?php if($sub_comment): // A specific comment? ?>
		<a href="<?php echo esc_attr($sub_comment_url); ?>">
			<?php echo esc_html($sub_comment_url); ?>
		</a>
	<?php else: // Subscribing to all comments/replies. ?>
		<a href="<?php echo esc_attr($sub_post_comments_url); ?>">
			<?php echo esc_html($sub_post_comments_url); ?>
		</a>
	<?php endif; ?>
</p>