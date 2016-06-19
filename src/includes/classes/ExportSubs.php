<?php
/**
 * Sub Exporter.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Exporter.
 *
 * @since 141111 First documented version.
 */
class ExportSubs extends AbsBase
{
    /**
     * @type int Starting row.
     *
     * @since 141111 First documented version.
     */
    protected $start_from;

    /**
     * @type int SQL max limit.
     *
     * @since 141111 First documented version.
     */
    protected $max_limit;

    /**
     * @type bool Include UTF-8 byte order marker?
     *
     * @since 141111 First documented version.
     */
    protected $include_utf8_bom;

    /**
     * @type string UTF-8 byte order marker.
     *
     * @since 141111 First documented version.
     */
    protected $utf8_bom = "\xEF\xBB\xBF";

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Arguments to the constructor.
     *                            These should NOT be trusted; they come from a `$_REQUEST` action.
     */
    public function __construct(array $request_args = [])
    {
        parent::__construct();

        $default_request_args = [
            'start_from'       => 1,
            'max_limit'        => 1000,
            'include_utf8_bom' => false,
        ];
        $request_args = array_merge($default_request_args, $request_args);
        $request_args = array_intersect_key($request_args, $default_request_args);

        $this->start_from       = (integer) $request_args['start_from'];
        $this->max_limit        = (integer) $request_args['max_limit'];
        $this->include_utf8_bom = filter_var($request_args['include_utf8_bom'], FILTER_VALIDATE_BOOLEAN);

        if ($this->start_from < 1) {
            $this->start_from = 1;
        }
        if ($this->max_limit < 1) {
            $this->max_limit = 1;
        }
        $upper_max_limit = (integer) apply_filters(__CLASS__.'_upper_max_limit', 5000);
        if ($this->max_limit > $upper_max_limit) {
            $this->max_limit = $upper_max_limit;
        }
        $this->maybeExport();
    }

    /**
     * Export handler.
     *
     * @since 141111 First documented version.
     */
    protected function maybeExport()
    {
        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        $data = ''; // Initialize.

        if (($results = $this->results())) {
            $data .= $this->formatCsvLine($results[0], true);
        }
        foreach ($results as $_result) {
            $data .= $this->formatCsvLine($_result);
        }
        unset($_result); // Housekeeping.

        if ($this->include_utf8_bom && $data) {
            $data = $this->utf8_bom.$data;
        }
        $from = $this->start_from;
        $to   = $from - 1 + count($results);

        $output_file_args = [
            'data'                => $data,
            'file_name'           => SLUG_TD.'-subs-'.$from.'-'.$to.'.csv',
            'content_type'        => 'text/csv; charset=UTF-8',
            'content_disposition' => 'attachment',
        ];
        new OutputFile($output_file_args);
    }

    /**
     * Formats a CSV data line.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass $row     A row object.
     * @param bool      $headers Defaults to a `FALSE` value.
     *                           Pass this as `TRUE` to create a line w/ headers.
     *
     * @return string A single line for a CSV file.
     */
    protected function formatCsvLine(\stdClass $row, $headers = false)
    {
        $row            = $headers ? array_keys((array) $row) : (array) $row;
        $escaped_values = array_map([$this->plugin->utils_string, 'escCsvDq'], $row);

        return $escaped_values ? '"'.implode('","', $escaped_values).'"'."\n" : '';
    }

    /**
     * Results query; for exportation.
     *
     * @since 141111 First documented version.
     *
     * @return \stdClass[] An array of row objects.
     */
    protected function results()
    {
        $sql = 'SELECT * FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               ' ORDER BY `ID` ASC'.// Maintain a consistent order.

               ' LIMIT '.esc_sql($this->start_from - 1).', '.esc_sql($this->max_limit);

        if (($results = $this->plugin->utils_db->wp->get_results($sql))) {
            $results = $this->plugin->utils_db->typifyDeep($results);
        }
        return $results ? $results : [];
    }
}
