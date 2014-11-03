<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * @note This file is automatically included as a child of other templates.
 *    Therefore, this template will ALSO receive any variable(s) passed to the parent template file,
 *    where the parent automatically calls upon this template. In short, if you see a variable documented in
 *    another template file, that particular variable will ALSO be made available in this file too;
 *    as this file is automatically included as a child of other parent templates.
 */
?>
<style type="text/css">
	@import url('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css');
	@import url('//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css');
	@import url('<?php echo $plugin->utils_url->to('/client-s/css/bootstrap-chosen.min.css'); ?>');
	/**/
	@import url('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
	@import url('<?php echo $plugin->utils_url->to('/submodules/sharkicons/styles.min.css'); ?>');
	/**/
	@import url('//fonts.googleapis.com/css?family=Bitter:400,400italic,700|Noto+Serif:400,400italic,700,700italic|Noto+Sans:400,400italic,700,700italic&amp;subset=latin');
</style>

<style type="text/css">
	/*
	Main html/body.
	*/
	html, body
	{
		background : #EEEEEE;
	}
	/*
	Main wrapper/container/inner-wrapper.
	*/
	.wrapper
	{
	}
	.wrapper > .container
	{
		margin-top    : 20px;
		margin-bottom : 20px;
	}
	.wrapper > .container > .panel.inner-wrapper
	{
	}
	/*
	Fonts.
	*/
	body, .font-body
	{
		font-family : 'Noto Serif', serif;
	}
	h1, .h1, h2, .h2, h3, .h3, h4, .h4
	{
		font-weight : 700;
		font-family : 'Bitter', serif;
	}
	h3.panel-title
	{
		font-weight : 400;
	}
	h5, .h5, h6, .h6
	{
		font-weight : 400;
		font-family : 'Bitter', serif;
	}
	.font-serify
	{
		font-family : 'Bitter', serif;
	}
	.font-serif
	{
		font-family : 'Noto Serif', serif;
	}
	.font-sans-serif
	{
		font-family : 'Noto Sans', sans-serif;
	}
	/*
	Misc. global styles.
	*/
	form label
	{
		cursor : pointer;
	}
	/*
	Subscription summary.
	*/
	.manage-summary .subs-table a
	{
		text-decoration : none;
	}
	.manage-summary .subs-table tr .hover-links
	{
		visibility : hidden;
		margin     : 0 0 0 20px;
	}
	.manage-summary .subs-table tr:hover .hover-links
	{
		visibility : visible;
	}
	.manage-summary .subs-table tr .hover-links .text-muted
	{
		opacity : 0.5;
	}
	/*
	Subscription add/edit form.
	*/
	.manage-sub-form form table
	{
		width : 100%;
	}
	.manage-sub-form form th
	{
		width : 250px;
	}
	.manage-sub-form form table td
	{
		padding : 10px 0 10px 10px;
	}
	.manage-sub-form form table .description
	{
		opacity    : 0.8;
		font-style : italic;
	}
	.manage-sub-form form input[type='submit']
	{
		width : 100%;
	}
	@media (max-width : 991px)
	{
		.manage-sub-form form table th,
		.manage-sub-form form table td
		{
			padding-top : 0;
			width       : 100%;
			display     : block;
		}
	}
</style>