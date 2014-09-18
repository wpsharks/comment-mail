<?php
/*
Version: 14xxxx
Text Domain: wp-comment-subs
Plugin Name: WP Comment Subs

Author: WebSharks, Inc.
Author URI: http://www.websharks-inc.com

Plugin URI: http://www.websharks-inc.com/product/wp-comment-subs/
Description: A WordPress plugin enabling email subscriptions for comments.

Enables email subscriptions for comments in WordPress.
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

if(require(dirname(__FILE__).'/includes/wp-php53.php')) // TRUE if running PHP v5.3+.
	require_once dirname(__FILE__).'/plugin.inc.php';
else wp_php53_notice('WP Comment Subs');