<?php
/**
 * Stub.
 *
 * @since 160212 PSR compliance.
 */
// @codingStandardsIgnoreFile
namespace WebSharks\CommentMail;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
require_once dirname(__DIR__).'/vendor/autoload.php';

${__FILE__}['version'] = '161213'; //version//
${__FILE__}['plugin']  = dirname(dirname(__DIR__)).'/plugin.php';
${__FILE__}['ns_path'] = str_replace('\\', '/', __NAMESPACE__);
${__FILE__}['is_pro']  = strtolower(basename(${__FILE__}['ns_path'])) === 'pro';

define(__NAMESPACE__.'\\QV_PREFIX', 'cm_');
define(__NAMESPACE__.'\\SHORT_NAME', 'CM');
define(__NAMESPACE__.'\\NAME', 'Comment Mail');
define(__NAMESPACE__.'\\DOMAIN', 'comment-mail.com');
define(__NAMESPACE__.'\\GLOBAL_NS', 'comment_mail');
define(__NAMESPACE__.'\\SLUG_TD', 'comment-mail');
define(__NAMESPACE__.'\\TRANSIENT_PREFIX', 'cmtmail_');
define(__NAMESPACE__.'\\VERSION', ${__FILE__}['version']);
define(__NAMESPACE__.'\\PLUGIN_FILE', ${__FILE__}['plugin']);
define(__NAMESPACE__.'\\IS_PRO', ${__FILE__}['is_pro']);

unset(${__FILE__}); // Housekeeping.
