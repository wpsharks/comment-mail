<?php
/**
 * API Classes.
 *
 * @since 160212 PSR compliance.
 */
namespace WebSharks\CommentMail;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
class_alias(__NAMESPACE__.'\\ApiBase', GLOBAL_NS);
