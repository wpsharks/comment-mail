<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string $email_header_styles Parsed header `<style>` template file.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @var string $email_header_scripts Parsed header `<script>` template file.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @var string $email_header_easy Parsed easy header template file.
 *    This is a partial header template, incorporated into this full template file;
 *    i.e. a simpler fragment that fits into this larger picture here.
 *
 * @note The `%%title%%` replacement code should remain as-is.
 *    It is replaced by other templates using this header.
 *
 * @var string $template_file Relative path to the current template file.
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
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>%%title%%</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php echo $email_header_styles; ?>
		<?php echo $email_header_scripts; ?>
	</head>
	<body>
		<?php echo $email_header_easy; ?>