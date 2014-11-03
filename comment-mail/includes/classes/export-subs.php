<?php
/**
 * Sub Exporter
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\export_subs'))
	{
		/**
		 * Sub Exporter
		 *
		 * @since 14xxxx First documented version.
		 */
		class export_subs extends abs_base
		{
			/**
			 * @var integer Starting row.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $start_from;

			/**
			 * @var integer SQL max limit.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $max_limit;

			/**
			 * @var boolean Include UTF-8 byte order marker?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $include_utf8_bom;

			/**
			 * @var string UTF-8 byte order marker.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $utf8_bom = "\xEF\xBB\xBF";

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 */
			public function __construct(array $request_args = array())
			{
				parent::__construct();

				$default_request_args = array(
					'start_from'       => 1,
					'max_limit'        => 500,
					'include_utf8_bom' => FALSE,
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->start_from       = (integer)$request_args['start_from'];
				$this->max_limit        = (integer)$request_args['max_limit'];
				$this->include_utf8_bom = filter_var($request_args['include_utf8_bom'], FILTER_VALIDATE_BOOLEAN);

				if($this->start_from < 1) // Too low?
					$this->start_from = 1; // At least one.

				if($this->max_limit < 1) $this->max_limit = 1;
				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				if($this->max_limit > $upper_max_limit)
					$this->max_limit = $upper_max_limit;

				$this->maybe_export();
			}

			/**
			 * Export handler.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_export()
			{
				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				$data = ''; // Initialize.

				if(($results = $this->results()))
					$data .= $this->format_csv_line($results[0], TRUE);

				foreach($results as $_result)
					$data .= $this->format_csv_line($_result);
				unset($_result); // Housekeeping.

				if($this->include_utf8_bom && $data)
					$data = $this->utf8_bom.$data;

				$from = $this->start_from + 1;
				$to   = $from + count($results);

				$output_file_args = array(
					'data'                => $data,
					'file_name'           => $this->plugin->slug.'-subs-'.$from.'-'.$to.'.csv',
					'content_type'        => 'text/csv; charset=UTF-8',
					'content_disposition' => 'attachment',
				);
				new output_file($output_file_args);
			}

			/**
			 * Formats a CSV data line.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $row A row object.
			 * @param boolean   $headers Defaults to a `FALSE` value.
			 *    Pass this as `TRUE` to create a line w/ headers.
			 *
			 * @return string A single line for a CSV file.
			 */
			protected function format_csv_line(\stdClass $row, $headers = FALSE)
			{
				$row            = $headers ? array_keys((array)$row) : (array)$row;
				$escaped_values = array_map(array($this->plugin->utils_string, 'esc_csv_dq'), $row);

				return $escaped_values ? '"'.implode('","', $escaped_values).'"'."\n" : '';
			}

			/**
			 * Results query; for exportation.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return \stdClass[] An array of row objects.
			 */
			protected function results()
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " ORDER BY `ID` ASC". // Maintain a consistent order.

				       " LIMIT ".esc_sql($this->start_from - 1).", ".esc_sql($this->max_limit);

				if(($results = $this->plugin->utils_db->wp->get_results($sql)))
					$results = $this->plugin->utils_db->typify_deep($results);

				return $results ? $results : array();
			}
		}
	}
}