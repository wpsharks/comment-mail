<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string $site_easy_header Parsed easy header template file contents.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @note The `%%title%%` replacement code should remain as-is.
 *    It is replaced by other templates using this header.
 */
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>%%title%%</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<link type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" media="all" />
	<link type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="all" />
	<link type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css" rel="stylesheet" media="all" />
	<link type="text/css" href="<?php echo esc_attr($plugin->utils_url->to('/client-s/css/bootstrap-chosen.min.css')); ?>" rel="stylesheet" media="all" />
	<link type="text/css" href="//fonts.googleapis.com/css?family=Bitter:400,400italic,700|Noto+Serif:400,400italic,700,700italic|Noto+Sans:400,400italic,700,700italic&amp;subset=latin" rel="stylesheet" media="all" />

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

	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
</head>
<body>
<div id="wrapper" class="wrapper" role="main">
	<div id="container" class="container">

		<?php echo $site_easy_header; ?>

		<div id="inner-wrapper" class="inner-wrapper panel panel-default">
			<div class="panel-body">