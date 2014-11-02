<?php
namespace comment_mail;

/**
 * @var plugin         $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @var string         $site_header Parsed site header template.
 * @var string         $site_footer Parsed site footer template.
 *
 * @var string         $sub_key Key granting access to summary; if applicable.
 *
 *    Note that a `$sub_key` is only present if the summary is accessed w/ a key.
 *    The summary is allowed to be accessed w/o a key also, and in this case
 *    the summary for the current user (and/or the current email cookie)
 *    is display instead. Therefore, this value is mostly irrelevant
 *    for templates — only provided for the sake of being thorough.
 *
 * @var \stdClass      $sub_email Email address that we're displaying the summary for.
 *
 *    Note that we may also display a summary of any comment subscriptions
 *    that are indirectly related to this email address, but still belong to the
 *    current user. e.g. if the `$sub_email` has been associated with one or more user IDs
 *    within WordPress, subscriptions for those user IDs will be summarized also.
 *    See `$sub_user_ids` for access to the array of associated WP user IDs.
 *
 * @var integer[]      $sub_user_ids An array of any WP user IDs associated w/ the email address.
 *
 * @var \stdClass      $query_vars Nav/query vars; consisting of: `page`, `per_page`, `post_id`, `status`.
 *    Note that `post_id` will be `NULL` when there is no specific post ID filter applied to the list of `$subs`.
 *    Note that `status` will be empty when there is no specific status filter applied to the list of `$subs`.
 *
 * @var \stdClass[]    $subs An array of all subscriptions to display as part of the summary on this `$query_vars->page`.
 *    Note that all query vars/filters/etc. will have already been applied; a template simply needs to iterate and display a table row for each of these.
 *
 * @var \stdClass|null $pagination_vars Pagination vars; consisting of: `page`, `per_page`, `total_subs`, `total_pages`.
 *    Note that `page` and `per_page` are simply duplicated here for convenience; same as you'll find in `$query_vars`.
 *
 * @var boolean        $processing Are we (i.e. did we) process an action? e.g. a deletion from the list perhaps.
 *
 * @var array          $processing_errors An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_error_codes An array of any/all processing error codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_errors_html An array of any/all processing errors.
 *    Array keys are error codes; array values are predefined error messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $processing_successes An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in plain text format.
 *
 * @var array          $processing_success_codes An array of any/all processing success codes.
 *    This includes the codes only; i.e. w/o the full array of predefined messages.
 *
 * @var array          $processing_successes_html An array of any/all processing successes.
 *    Array keys are success codes; array values are predefined success messages.
 *    Note that predefined messages in this array are in HTML format.
 *
 * @var array          $error_codes An array of any/all major error codes; excluding processing error codes.
 *    Note that you should NOT display the summary at all, if any major error exist here.
 */
?>