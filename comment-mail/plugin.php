<?php
/*
Version: 14xxxx
Text Domain: comment-mail
Plugin Name: Comment Mail

Author: WebSharks, Inc.
Author URI: http://www.websharks-inc.com

Plugin URI: http://www.websharks-inc.com/product/comment-mail/
Description: A WordPress plugin enabling email subscriptions for comments.

Enables email subscriptions for comments in WordPress.
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

if(require(dirname(__FILE__).'/includes/wp-php53.php')) // TRUE if running PHP v5.3+.
	require_once dirname(__FILE__).'/plugin.inc.php';
else wp_php53_notice('Comment Mail™');