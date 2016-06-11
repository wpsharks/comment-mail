<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin   $plugin Plugin class.
 * @var Template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string   $site_header_styles Parsed header `<style>`s template file.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @var string   $site_header_scripts Parsed header `<script>`s template file.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @var string   $site_header_tag Parsed <header> tag template file.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @note The `%%title%%` replacement code should remain as-is.
 *    It is replaced by other templates using this header.
 *
 * @var Template $parent_template Parent template class reference.
 *
 * @note This file is automatically included as a child of other templates.
 *    Therefore, this template will ALSO receive any variable(s) passed to the parent template file,
 *    where the parent automatically calls upon this template. In short, if you see a variable documented in
 *    another template file, that particular variable will ALSO be made available in this file too;
 *    as this file is automatically included as a child of other parent templates.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta charset="UTF-8" />
    <title>%%title%%</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php echo $site_header_styles; ?>
    <?php echo $site_header_scripts; ?>
</head>
<body>
<div id="wrapper" class="wrapper" role="main">
    <div id="container" class="container">

        <?php echo $site_header_tag; ?>

        <div id="inner-wrapper" class="inner-wrapper panel panel-default">
            <div class="panel-body">
