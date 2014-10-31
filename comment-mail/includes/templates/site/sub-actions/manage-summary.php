<?php
namespace comment_mail;

/**
 * @var plugin    $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string    $site_header Parsed site header template.
 * @var string    $site_footer Parsed site footer template.
 *
 * @var string    $sub_key Key granting access to summary; iff applicable.
 *
 *    Note that a `$sub_key` is only present if the summary is accessed w/ a key.
 *    The summary is allowed to be accessed w/o a key also, and in this case
 *    the summary for the current user (and/or the current email cookie)
 *    is display instead. Therefore, this value is mostly irrelevant
 *    for templates — only provided for the sake of being thorough.
 *
 * @var \stdClass $sub_email Email address that we're displaying the summary for.
 *    This is always present; assuming there are no major `$error_codes`.
 *
 *    Note that we may also display a summary of any comment subscriptions
 *    that are indirectly related to this email address, but still belong to the
 *    current user. e.g. if the `$sub_email` has been associated with one or more user IDs
 *    within WordPress, subscriptions for those user IDs will be summarized also.
 *    See `$user_ids` for access to the array of associated WP user IDs.
 *
 * @var integer[] An array of any WP user IDs associated w/ the email address.
 *
 * @var boolean   $processing Are we (i.e. did we) process an action?
 *
 * @var array     $processing_errors An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array     $processing_error_codes An array of any/all processing error codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array     $processing_errors_html An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array     $processing_successes An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array     $processing_success_codes An array of any/all processing success codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array     $processing_successes_html An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array     $error_codes An array of any/all major error codes; excluding processing error codes.
 *    Note that you should NOT display the summary at all; if any major error exist.
 */
?>