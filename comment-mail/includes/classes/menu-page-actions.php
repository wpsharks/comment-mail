<?php
/**
 * Menu Page Actions
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page_actions'))
	{
		/**
		 * Menu Page Actions
		 *
		 * @since 14xxxx First documented version.
		 */
		class menu_page_actions extends abstract_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_handle()
			{
				if(!is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Not applicable.

				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				if(empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce']))
					return; // Unauthenticated; ignore.

				foreach((array)$_REQUEST[__NAMESPACE__] as $action => $args)
					if($action && is_string($action) && method_exists($this, $action))
						$this->{$action}($this->plugin->utils_string->trim_strip_deep($args));
			}

			/**
			 * Restores defaults options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function restore_default_options($args)
			{
				$args = NULL; // Not used here.

				delete_option(__NAMESPACE__.'_options');
				$this->plugin->options = $this->plugin->default_options;

				wp_redirect($this->plugin->utils_url->options_restored()).exit();
			}

			/**
			 * Saves options.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function save_options($args)
			{
				$args = (array)$args; // Expecting an array.

				$this->plugin->options = array_merge($this->plugin->default_options, $this->plugin->options, $args);
				$this->plugin->options = array_intersect_key($this->plugin->options, $this->plugin->default_options);
				update_option(__NAMESPACE__.'_options', $this->plugin->options); // Update.

				wp_redirect($this->plugin->utils_url->options_updated()).exit();
			}

			/**
			 * Dismisses a persistent notice.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function dismiss_notice($args)
			{
				$args = (array)$args; // Expecting an array.

				if(empty($args['key'])) // Missing key?
					return; // Nothing to dismiss.

				$notices = get_option(__NAMESPACE__.'_notices');
				if(!is_array($notices)) $notices = array();

				unset($notices[$args['key']]); // Dismiss.
				update_option(__NAMESPACE__.'_notices', $notices);

				wp_redirect($this->plugin->utils_url->notice_dismissed()).exit();
			}

			/**
			 * Dismisses a persistent error.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function dismiss_error($args)
			{
				$args = (array)$args; // Expecting an array.

				if(empty($args['key'])) // Missing key?
					return; // Nothing to dismiss.

				$errors = get_option(__NAMESPACE__.'_errors');
				if(!is_array($errors)) $errors = array();

				unset($errors[$args['key']]); // Dismiss.
				update_option(__NAMESPACE__.'_errors', $errors);

				wp_redirect($this->plugin->utils_url->error_dismissed()).exit();
			}

			/**
			 * Runs a specific import type.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $args Input argument(s).
			 */
			protected function import($args)
			{
				$args = (array)$args; // Expecting an array.

				if(empty($args['type']) || !is_string($args['type']))
					return; // Missing and/or invalid import type.

				if(!in_array($args['type'], array('stcr'), TRUE))
					return; // Invalid import type.

				$class    = 'import_'.$args['type'];
				$importer = new $class; // Instantiate.

				/**
				 * @var $importer import_stcr For IDEs.
				 */
				$importer->output_status();
			}
		}
	}
}