<?php
/**
 * Sub Importer
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\import_subs'))
	{
		/**
		 * Sub Importer
		 *
		 * @since 14xxxx First documented version.
		 */
		class import_subs extends abs_base
		{
			/**
			 * @var string Input data.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $data;

			/**
			 * @var string Input data file.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $data_file;

			/**
			 * @var boolean Process confirmations?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $process_confirmations;

			/**
			 * @var integer SQL max limit.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $max_limit;

			/**
			 * @var integer Total imported subs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $total_imported_subs;

			/**
			 * @var array An array of any/all errors.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $errors;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 *
			 * @throws \exception If a security flag is triggered on `$this->data_file`.
			 */
			public function __construct(array $request_args = array())
			{
				parent::__construct();

				$default_request_args = array(
					'data'                  => '',
					'data_file'             => '',
					'process_confirmations' => FALSE,
					'max_limit'             => 500,
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->data      = trim((string)$request_args['data']);
				$this->data_file = trim((string)$request_args['data_file']);

				if($this->data_file) // Run security flag checks on the path.
					$this->plugin->utils_fs->check_path_security($this->data_file, TRUE);
				if($this->data_file) $this->data = ''; // Favor file over raw data.

				$this->process_confirmations = (boolean)$request_args['process_confirmations'];

				$this->max_limit = (integer)$request_args['max_limit'];

				if($this->max_limit < 1) // Too low?
					$this->max_limit = 1; // At least one.

				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				if($this->max_limit > $upper_max_limit)
					$this->max_limit = $upper_max_limit;

				$this->total_imported_subs = 0; // Initialize.
				$this->errors              = array(); // Initialize.

				$this->maybe_import();
			}

			/**
			 * Import processor.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_import()
			{
				$csv_headers             = array(); // Initialize.
				$current_csv_line_number = $current_csv_line_index = 0;

				if(!($csv_resource_file = $this->csv_resource_file()))
					return; // Not possible; i.e. no resource.

				while(($_csv_line = fgetcsv($csv_resource_file, 0, ',', '"', '"')) !== FALSE)
				{
					$current_csv_line_number++; // Increment line counter.
					$current_csv_line_index++; // Increment line index also.

					$_csv_line = $this->plugin->utils_string->trim_deep($_csv_line);

					if($current_csv_line_index === 1 && !empty($_csv_line[0]))
					{
						foreach($_csv_line as $_csv_header)
							$csv_headers[] = (string)$_csv_header;
						unset($_csv_header); // Housekeeping.

						$current_csv_line_number--;
						continue; // Skip this line.
					}
					if($current_csv_line_index >= 1 && !$csv_headers)
					{
						$this->errors[] = // Missing required headers.
							__('Missing first-line CSV headers; please try again.', $this->plugin->text_domain);
						break; // Stop here; we have no headers in this importation.
					}
					if($current_csv_line_index >= 1 && !in_array('ID', $csv_headers, TRUE))
						if(!in_array('email', $csv_headers, TRUE) || !in_array('post_id', $csv_headers, TRUE))
						{
							$this->errors[] = // Missing required headers.
								__('First-line CSV headers MUST contain (at a minimum); one of:', $this->plugin->text_domain).
								' '.__('<code>"ID"</code>, or <code>"email"</code> together with a <code>"post_id"</code>.', $this->plugin->text_domain);
							break; // Stop here; we have no headers in this importation.
						}
					$_import                     = array(); // Reset this on each pass.
					$_import['ID']               = $this->csv_line_column_value_for('ID', $csv_headers, $_csv_line);
					$_import['key']              = $this->csv_line_column_value_for('key', $csv_headers, $_csv_line);
					$_import['user_id']          = $this->csv_line_column_value_for('user_id', $csv_headers, $_csv_line);
					$_import['post_id']          = $this->csv_line_column_value_for('post_id', $csv_headers, $_csv_line);
					$_import['comment_id']       = $this->csv_line_column_value_for('comment_id', $csv_headers, $_csv_line);
					$_import['deliver']          = $this->csv_line_column_value_for('deliver', $csv_headers, $_csv_line);
					$_import['fname']            = $this->csv_line_column_value_for('fname', $csv_headers, $_csv_line);
					$_import['lname']            = $this->csv_line_column_value_for('lname', $csv_headers, $_csv_line);
					$_import['email']            = $this->csv_line_column_value_for('email', $csv_headers, $_csv_line);
					$_import['insertion_ip']     = $this->csv_line_column_value_for('insertion_ip', $csv_headers, $_csv_line);
					$_import['last_ip']          = $this->csv_line_column_value_for('last_ip', $csv_headers, $_csv_line);
					$_import['status']           = $this->csv_line_column_value_for('status', $csv_headers, $_csv_line);
					$_import['insertion_time']   = $this->csv_line_column_value_for('insertion_time', $csv_headers, $_csv_line);
					$_import['last_update_time'] = $this->csv_line_column_value_for('last_update_time', $csv_headers, $_csv_line);

					$_sub_inserter = new sub_inserter($_import, array(
						'process_confirmation' => $this->process_confirmations,
					)); // Insert; or perhaps update existing subscription.

					if($_sub_inserter->did_insert_update()) // Have insert|update success?
						$this->total_imported_subs++; // Increment counter; this was a success.

					else if($_sub_inserter->has_errors()) // If the inserter has errors for this line; report those.
					{
						$_sub_inserter_errors       = array_values($_sub_inserter->errors()); // Values only; discard keys.
						$_sub_inserter_error_prefix = sprintf(__('_Line #%1$s:_', $this->plugin->text_domain), esc_html($current_csv_line_number));

						foreach($_sub_inserter_errors as &$_sub_inserter_error)
							$_sub_inserter_error = $_sub_inserter_error_prefix.' '.$_sub_inserter_error;
						$this->errors = array_merge($this->errors, $_sub_inserter_errors);
					}
					unset($_sub_inserter, $_sub_inserter_errors, // Housekeeping.
						$_sub_inserter_error, $_sub_inserter_error_prefix);

					if($current_csv_line_number + 1 > $this->max_limit)
						break; // Reached the max limit.
				}
				unset($_csv_line, $_import); // Housekeeping.
				fclose($csv_resource_file); // Close resource file.

				$this->enqueue_notices_and_redirect(); // Issue notices and redirect user.
			}

			/**
			 * Line column value for a particular CSV column.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $csv_column The CSV column value to acquire.
			 * @param array  $csv_headers An array of CSV headers.
			 * @param array  $csv_line Current CSV line data.
			 *
			 * @return string|null The CSV line column value; else `NULL` by default.
			 */
			protected function csv_line_column_value_for($csv_column, array $csv_headers, array $csv_line)
			{
				$key = array_search($csv_column, $csv_headers);

				return $key !== FALSE && isset($csv_line[$key])
				       && is_string($csv_line[$key]) && isset($csv_line[$key][0])
					? (string)$csv_line[$key] : NULL;
			}

			/**
			 * Notices and redirection.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function enqueue_notices_and_redirect()
			{
				$notice_markup = $error_markup = ''; // Initialize.
				$subs_i18n     = $this->plugin->utils_i18n->subscriptions($this->total_imported_subs); // e.g. `X subscription(s)`.
				$notice_markup = sprintf(__('<strong>Imported %1$s successfully.</strong>', $this->plugin->text_domain), esc_html($subs_i18n));

				if($this->errors) // Do we have errors to report also? If so, present these as individual list items.
				{
					$error_markup = __('<strong>The following errors were encountered during importation:</strong>', $this->plugin->text_domain);
					$error_markup .= '<ul class="pmp-list-items"><li>'.implode('</li><li>', $this->errors_html()).'</li></ul>';
				}
				if($notice_markup) // This really should always be displayed; even if we imported `0` subscriptions.
					$this->plugin->enqueue_user_notice($notice_markup, array('transient' => TRUE, 'for_page' => $this->plugin->utils_env->current_menu_page()));

				if($error_markup) // Are there any specific error messages that we can report?
					$this->plugin->enqueue_user_error($error_markup, array('transient' => TRUE, 'for_page' => $this->plugin->utils_env->current_menu_page()));

				wp_redirect($this->plugin->utils_url->page_only()).exit();
			}

			/**
			 * CSV resource file to read from.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return resource|boolean Resource on success.
			 */
			protected function csv_resource_file()
			{
				if($this->data_file)
					return fopen($this->data_file, 'rb');

				if(($csv_resource_file = tmpfile()))
				{
					fwrite($csv_resource_file, $this->data);
					fseek($csv_resource_file, 0);
				}
				return is_resource($csv_resource_file) ? $csv_resource_file : FALSE;
			}

			/**
			 * An array of all errors w/ HTML markup.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array Errors w/ HTML markup.
			 */
			protected function errors_html()
			{
				return array_map(array($this->plugin->utils_string, 'markdown'), $this->errors);
			}
		}
	}
}