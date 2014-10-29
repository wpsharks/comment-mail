<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string $email_easy_header Parsed easy header template file contents.
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
	</head>
	<body>
		<?php echo $email_easy_header; ?>