<?php
/**
 * Uninstaller
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

$GLOBALS['wp_php_rv'] = '5.4'; // Minimum version.
if(require(dirname(__FILE__).'/submodules/wp-php-rv/src/includes/check.php'))
	require_once dirname(__FILE__).'/uninstall.inc.php';
