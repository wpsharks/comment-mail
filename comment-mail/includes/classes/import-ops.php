<?php
/**
 * Options Importer
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\import_ops'))
	{
		/**
		 * Options Importer
		 *
		 * @since 141111 First documented version.
		 */
		class import_ops extends abs_base
		{
			/**
			 * @var string Input data.
			 *
			 * @since 141111 First documented version.
			 */
			protected $data;

			/**
			 * @var string Input data file.
			 *
			 * @since 141111 First documented version.
			 */
			protected $data_file;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
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
					'data'      => '',
					'data_file' => '',
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->data      = trim((string)$request_args['data']);
				$this->data_file = trim((string)$request_args['data_file']);

				if($this->data_file) // Run security flag checks on the path.
					$this->plugin->utils_fs->check_path_security($this->data_file, TRUE);
				if($this->data_file) $this->data = ''; // Favor file over raw data.

				$this->maybe_import();
			}

			/**
			 * Import processor.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_import()
			{
				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				if($this->data_file) // File takes precedence.
					$options_to_import = json_decode(file_get_contents($this->data_file), TRUE);
				else $options_to_import = json_decode($this->data, TRUE);

				$options_to_import = (array)$options_to_import; // Force array.
				unset($options_to_import['version'], $options_to_import['crons_setup']);

				$this->plugin->options_save($options_to_import);

				$this->enqueue_notices_and_redirect();
			}

			/**
			 * Notices and redirection.
			 *
			 * @since 141111 First documented version.
			 */
			protected function enqueue_notices_and_redirect()
			{
				$notice_markup = sprintf(__('<strong>Imported %1$s&trade; config. options successfully.</strong>', $this->plugin->text_domain), esc_html($this->plugin->name));

				$this->plugin->enqueue_user_notice($notice_markup, array('transient' => TRUE, 'for_page' => $this->plugin->utils_env->current_menu_page()));

				wp_redirect($this->plugin->utils_url->page_only()).exit();
			}
		}
	}
}