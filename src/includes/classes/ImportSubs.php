<?php
/**
 * Sub Importer.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Importer.
 *
 * @since 141111 First documented version.
 */
class ImportSubs extends AbsBase
{
    /**
     * @type string Input data.
     *
     * @since 141111 First documented version.
     */
    protected $data;

    /**
     * @type string Input data file.
     *
     * @since 141111 First documented version.
     */
    protected $data_file;

    /**
     * @type bool Process confirmations?
     *
     * @since 141111 First documented version.
     */
    protected $process_confirmations;

    /**
     * @type int SQL max limit.
     *
     * @since 141111 First documented version.
     */
    protected $max_limit;

    /**
     * @type int Total imported subs.
     *
     * @since 141111 First documented version.
     */
    protected $total_imported_subs;

    /**
     * @type array An array of any/all errors.
     *
     * @since 141111 First documented version.
     */
    protected $errors;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Arguments to the constructor.
     *                            These should NOT be trusted; they come from a `$_REQUEST` action.
     *
     * @throws \exception If a security flag is triggered on `$this->data_file`.
     */
    public function __construct(array $request_args = [])
    {
        parent::__construct();

        $default_request_args = [
            'data'                  => '',
            'data_file'             => '',
            'process_confirmations' => false,
            'max_limit'             => 5000,
        ];
        $request_args = array_merge($default_request_args, $request_args);
        $request_args = array_intersect_key($request_args, $default_request_args);

        $this->data      = trim((string) $request_args['data']);
        $this->data_file = trim((string) $request_args['data_file']);

        if ($this->data_file) { // Run security flag checks on the path.
            $this->plugin->utils_fs->checkPathSecurity($this->data_file, true);
        }
        if ($this->data_file) {
            $this->data = ''; // Favor file over raw data.
        }
        $this->process_confirmations = (boolean) $request_args['process_confirmations'];

        $this->max_limit = (integer) $request_args['max_limit'];

        if ($this->max_limit < 1) {
            $this->max_limit = 1; // At least one.
        }
        $upper_max_limit = (integer) apply_filters(__CLASS__.'_upper_max_limit', 5000);
        if ($this->max_limit > $upper_max_limit) {
            $this->max_limit = $upper_max_limit;
        }
        $this->total_imported_subs = 0; // Initialize.
        $this->errors              = []; // Initialize.

        $this->maybeImport();
    }

    /**
     * Import processor.
     *
     * @since 141111 First documented version.
     */
    protected function maybeImport()
    {
        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        $csv_headers             = []; // Initialize.
        $current_csv_line_number = $current_csv_line_index = 0;

        if (!($csv_resource_file = $this->csvResourceFile())) {
            return; // Not possible; i.e. no resource.
        }
        while (($_csv_line = fgetcsv($csv_resource_file, 0, ',', '"', '"')) !== false) {
            ++$current_csv_line_number; // Increment line counter.
            ++$current_csv_line_index; // Increment line index also.

            $_csv_line = $this->plugin->utils_string->trimDeep($_csv_line);

            if ($current_csv_line_index === 1 && !empty($_csv_line[0])) {
                foreach ($_csv_line as $_csv_header) {
                    $csv_headers[] = (string) $_csv_header;
                }
                unset($_csv_header); // Housekeeping.

                --$current_csv_line_number;
                continue; // Skip this line.
            }
            if ($current_csv_line_index >= 1 && !$csv_headers) {
                $this->errors[] = // Missing required headers.
                    __('Missing first-line CSV headers; please try again.', 'comment-mail');
                break; // Stop here; we have no headers in this importation.
            }
            if ($current_csv_line_index >= 1 && !in_array('ID', $csv_headers, true)) {
                if (!in_array('email', $csv_headers, true) || !in_array('post_id', $csv_headers, true)) {
                    $this->errors[] = // Missing required headers.
                        __('First-line CSV headers MUST contain (at a minimum); one of:', 'comment-mail').
                        ' '.__('<code>"ID"</code>, or <code>"email"</code> together with a <code>"post_id"</code>.', 'comment-mail');
                    break; // Stop here; we have no headers in this importation.
                }
            }
            $_import = []; // Reset this on each pass.

            $_import['ID']  = $this->csvLineColumnValueFor('ID', $csv_headers, $_csv_line);
            $_import['key'] = $this->csvLineColumnValueFor('key', $csv_headers, $_csv_line);

            $_import['user_id']    = $this->csvLineColumnValueFor('user_id', $csv_headers, $_csv_line);
            $_import['post_id']    = $this->csvLineColumnValueFor('post_id', $csv_headers, $_csv_line);
            $_import['comment_id'] = $this->csvLineColumnValueFor('comment_id', $csv_headers, $_csv_line);

            $_import['deliver'] = $this->csvLineColumnValueFor('deliver', $csv_headers, $_csv_line);
            $_import['status']  = $this->csvLineColumnValueFor('status', $csv_headers, $_csv_line);

            $_import['fname'] = $this->csvLineColumnValueFor('fname', $csv_headers, $_csv_line);
            $_import['lname'] = $this->csvLineColumnValueFor('lname', $csv_headers, $_csv_line);
            $_import['email'] = $this->csvLineColumnValueFor('email', $csv_headers, $_csv_line);

            $_import['insertion_ip']      = $this->csvLineColumnValueFor('insertion_ip', $csv_headers, $_csv_line);
            $_import['insertion_region']  = $this->csvLineColumnValueFor('insertion_region', $csv_headers, $_csv_line);
            $_import['insertion_country'] = $this->csvLineColumnValueFor('insertion_country', $csv_headers, $_csv_line);

            $_import['last_ip']      = $this->csvLineColumnValueFor('last_ip', $csv_headers, $_csv_line);
            $_import['last_region']  = $this->csvLineColumnValueFor('last_region', $csv_headers, $_csv_line);
            $_import['last_country'] = $this->csvLineColumnValueFor('last_country', $csv_headers, $_csv_line);

            $_import['insertion_time']   = $this->csvLineColumnValueFor('insertion_time', $csv_headers, $_csv_line);
            $_import['last_update_time'] = $this->csvLineColumnValueFor('last_update_time', $csv_headers, $_csv_line);

            $_sub_inserter = new SubInserter($_import, ['process_confirmation' => $this->process_confirmations]);

            if ($_sub_inserter->didInsertUpdate()) { // Have insert|update success?
                ++$this->total_imported_subs; // Increment counter; this was a success.
            } elseif ($_sub_inserter->hasErrors()) { // If the inserter has errors for this line; report those.
                $_sub_inserter_errors       = array_values($_sub_inserter->errors()); // Values only; discard keys.
                $_sub_inserter_error_prefix = sprintf(__('_Line #%1$s:_', 'comment-mail'), esc_html($current_csv_line_number));

                foreach ($_sub_inserter_errors as &$_sub_inserter_error) {
                    $_sub_inserter_error = $_sub_inserter_error_prefix.' '.$_sub_inserter_error;
                }
                $this->errors = array_merge($this->errors, $_sub_inserter_errors);
            }
            unset($_sub_inserter, $_sub_inserter_errors); // Housekeeping.
            unset($_sub_inserter_error, $_sub_inserter_error_prefix);

            if ($current_csv_line_number + 1 > $this->max_limit) {
                break; // Reached the max limit.
            }
        }
        unset($_csv_line, $_import); // Housekeeping.
        fclose($csv_resource_file); // Close resource file.

        $this->enqueueNoticesAndRedirect(); // Issue notices and redirect user.
    }

    /**
     * Line column value for a particular CSV column.
     *
     * @since 141111 First documented version.
     *
     * @param string $csv_column  The CSV column value to acquire.
     * @param array  $csv_headers An array of CSV headers.
     * @param array  $csv_line    Current CSV line data.
     *
     * @return string|null The CSV line column value; else `NULL` by default.
     */
    protected function csvLineColumnValueFor($csv_column, array $csv_headers, array $csv_line)
    {
        $key = array_search($csv_column, $csv_headers, true);

        return $key !== false && isset($csv_line[$key])
               && is_string($csv_line[$key]) && isset($csv_line[$key][0])
            ? (string) $csv_line[$key] : null;
    }

    /**
     * Notices and redirection.
     *
     * @since 141111 First documented version.
     */
    protected function enqueueNoticesAndRedirect()
    {
        $notice_markup = $error_markup = ''; // Initialize.
        $subs_i18n     = $this->plugin->utils_i18n->subscriptions($this->total_imported_subs); // e.g. `X subscription(s)`.
        $notice_markup = sprintf(__('<strong>Imported %1$s successfully.</strong>', 'comment-mail'), esc_html($subs_i18n));

        if ($this->errors) {
            // Do we have errors to report also? If so, present these as individual list items.
            $error_markup = __('<strong>The following errors were encountered during importation:</strong>', 'comment-mail');
            $error_markup .= '<ul class="pmp-list-items"><li>'.implode('</li><li>', $this->errorsHtml()).'</li></ul>';
        }
        if ($notice_markup) { // This really should always be displayed; even if we imported `0` subscriptions.
            $this->plugin->enqueueUserNotice($notice_markup, ['transient' => true, 'for_page' => $this->plugin->utils_env->currentMenuPage()]);
        }
        if ($error_markup) { // Are there any specific error messages that we can report?
            $this->plugin->enqueueUserError($error_markup, ['transient' => true, 'for_page' => $this->plugin->utils_env->currentMenuPage()]);
        }
        wp_redirect($this->plugin->utils_url->pageOnly());
        exit();
    }

    /**
     * CSV resource file to read from.
     *
     * @since 141111 First documented version.
     *
     * @return resource|bool Resource on success.
     */
    protected function csvResourceFile()
    {
        if ($this->data_file) {
            return fopen($this->data_file, 'rb');
        }
        if (($csv_resource_file = tmpfile())) {
            fwrite($csv_resource_file, $this->data);
            fseek($csv_resource_file, 0);
        }
        return is_resource($csv_resource_file) ? $csv_resource_file : false;
    }

    /**
     * An array of all errors w/ HTML markup.
     *
     * @since 141111 First documented version.
     *
     * @return array Errors w/ HTML markup.
     */
    protected function errorsHtml()
    {
        return array_map([$this->plugin->utils_string, 'markdownNoP'], $this->errors);
    }
}
