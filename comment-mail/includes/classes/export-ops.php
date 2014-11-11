<?php
/**
 * Options Exporter
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\export_ops'))
	{
		/**
		 * Options Exporter
		 *
		 * @since 141111 First documented version.
		 */
		class export_ops extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 */
			public function __construct(array $request_args = array())
			{
				parent::__construct();

				$default_request_args = array();
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->maybe_export();
			}

			/**
			 * Export handler.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_export()
			{
				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				$options_to_export = $this->plugin->options;
				unset($options_to_export['version'], $options_to_export['crons_setup']);
				$data = json_encode($options_to_export);

				$file_name = $this->plugin->slug.'-options';
				$file_name .= '-'.$this->plugin->utils_url->current_host_path();
				$file_name = trim(preg_replace('/[^a-z0-9]/i', '-', strtolower($file_name)), '-');
				$file_name .= '.json'; // Use a JSON file extension.

				$output_file_args = array(
					'data'                => $data,
					'file_name'           => $file_name,
					'content_type'        => 'application/json; charset=UTF-8',
					'content_disposition' => 'attachment',
				);
				new output_file($output_file_args);
			}
		}
	}
}