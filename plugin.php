<?php
/**
 * Plugin.
 *
 * @since     160212 PSR compliance.
 *
 * @copyright WebSharks, Inc. <http://websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
/*
Version: 161213
Text Domain: comment-mail
Plugin Name: Comment Mail

Author: WebSharks, Inc.
Author URI: http://www.websharks-inc.com

Plugin URI: http://comment-mail.com/
Description: A WordPress plugin enabling email subscriptions for comments.

Enables email subscriptions for comments in WordPress.
*/
if (!defined('WPINC')) { // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
$GLOBALS['wp_php_rv'] = '5.4'; //php-required-version//
if (require(__DIR__.'/src/vendor/websharks/wp-php-rv/src/includes/check.php')) {
    require_once __DIR__.'/src/includes/plugin.php';
} else {
    wp_php_rv_notice('Comment Mail');
}
