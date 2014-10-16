<?php
/**
 * File Output Handler
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\output_file'))
	{
		/**
		 * File Output Handler
		 *
		 * @since 14xxxx First documented version.
		 */
		class output_file extends abstract_base
		{
			/**
			 * @var string Data to output.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $data;

			/**
			 * @var string Data file to output.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $data_file;

			/**
			 * @var string File name to output.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_name;

			/**
			 * @var string Content type.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $content_type;

			/**
			 * @var string Content disposition.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $content_disposition;

			/**
			 * @var integer Chunk size.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $chunk_size;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $request_args Configuration arguments.
			 *
			 * @throws \exception If a security flag is triggered on `$this->data_file`.
			 */
			public function __construct(array $request_args)
			{
				parent::__construct();

				$default_args = array(
					'data'                => '',
					'data_file'           => '',
					'file_name'           => '',
					'content_type'        => '',
					'content_disposition' => 'attachment',
					'chunk_size'          => 2097152,
				);
				$request_args = array_merge($default_args, $request_args);
				$request_args = array_intersect_key($request_args, $default_args);

				$this->data      = (string)$request_args['data'];
				$this->data_file = (string)$request_args['data_file'];

				if($this->data_file) // Run security flag checks on the path.
					$this->plugin->utils_fs->check_path_security($this->data_file);

				if($this->data_file && is_file($this->data_file) && is_readable($this->data_file))
					$this->data = ''; // Favor the data file over raw data.

				$this->file_name           = (string)$request_args['file_name'];
				$this->content_type        = (string)$request_args['content_type'];
				$this->content_disposition = (string)$request_args['content_disposition'];

				$this->chunk_size = (integer)$request_args['chunk_size'];
				$this->chunk_size = $this->chunk_size < 1 ? 1 : $this->chunk_size;

				$this->maybe_output();
			}

			/**
			 * Exports subscribers.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_output()
			{
				$this->prepare();
				$this->send_headers();
				$this->maybe_send_data();
				$this->maybe_send_data_file();
				exit(); // Stop here.
			}

			/**
			 * Prepare environment.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function prepare()
			{
				$this->plugin->utils_env->prep_for_large_output();
			}

			/**
			 * Send headers; always.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function send_headers()
			{
				status_header(200);

				header('Accept-Ranges: none');

				header('Content-Encoding: none');
				header('Content-Type: '.$this->content_type);
				header('Content-Length: '.$this->content_length());

				nocache_headers(); // No browser cache.
				header('Cache-Control: no-cache, must-revalidate, max-age=0');
				header('Cache-Control: post-check=0, pre-check=0', FALSE);

				header('Content-Disposition:'.
				       ' '.$this->content_disposition.';'.
				       ' filename="'.$this->plugin->utils_string->esc_dq($this->file_name).'";'.
				       ' filename*=UTF-8\'\''.rawurlencode($this->file_name));
			}

			/**
			 * Determine content length.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function content_length()
			{
				if($this->data_file)
					return filesize($this->data_file);

				return strlen($this->data);
			}

			/**
			 * Send data; if applicable.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_send_data()
			{
				if($this->data_file)
					return; // Nothing to do here.

				$_bytes_to_read = $this->content_length();

				while($_bytes_to_read > 0) // While we have bytes.
				{
					$_reading_from = $this->content_length - $_bytes_to_read;
					$_reading      = $_bytes_to_read > $this->chunk_size
						? $this->chunk_size : $_bytes_to_read;

					echo substr($this->data, $_reading_from, $_reading);

					$_bytes_to_read -= $_reading;

					flush(); // Flush to browser.
				}
				unset($_bytes_to_read, $_reading_from, $_reading);
			}

			/**
			 * Send data file; if applicable.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_send_data_file()
			{
				if(!$this->data_file)
					return; // Nothing to do here.

				if(!($resource = fopen($this->data_file, 'rb')))
					return; // Not applicable.

				$_bytes_to_read = $this->content_length();

				while($_bytes_to_read > 0) // While we have bytes.
				{
					$_reading_from = $this->content_length - $_bytes_to_read;
					$_reading      = $_bytes_to_read > $this->chunk_size
						? $this->chunk_size : $_bytes_to_read;

					echo fread($resource, $_reading);

					$_bytes_to_read -= $_reading;

					flush(); // Flush to browser.
				}
				unset($_bytes_to_read, $_reading_from, $_reading);

				fclose($resource); // Close resource handle.
			}
		}
	}
}