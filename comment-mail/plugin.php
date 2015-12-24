<?php
/**
 * Plugin
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
/*
Version: 151224
Text Domain: comment-mail
Plugin Name: Comment Mail

Author: WebSharks, Inc.
Author URI: http://www.websharks-inc.com

Plugin URI: http://comment-mail.com/
Description: A WordPress plugin enabling email subscriptions for comments.

Enables email subscriptions for comments in WordPress.
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

$GLOBALS['wp_php_rv'] = '5.4'; // Minimum version.
if(require(dirname(__FILE__).'/submodules/wp-php-rv/src/includes/check.php'))
	require_once dirname(__FILE__).'/plugin.inc.php';
else wp_php_rv_notice(basename(dirname(__FILE__)));
