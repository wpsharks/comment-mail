<?php
/**
 * Template
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\template'))
	{
		/**
		 * Template
		 *
		 * @since 141111 First documented version.
		 */
		class template extends abs_base
		{
			/**
			 * @var string Type of template.
			 *
			 * @since 141111 First documented version.
			 */
			protected $type;

			/**
			 * @var string Template file.
			 *
			 * @since 141111 First documented version.
			 */
			protected $file;

			/**
			 * @var string Snippet sub-directory.
			 *
			 * @since 141111 First documented version.
			 */
			protected $snippet_sub_dir;

			/**
			 * @var boolean Force default template?
			 *
			 * @since 141111 First documented version.
			 */
			protected $force_default;

			/**
			 * @var string Template file contents.
			 *
			 * @since 141111 First documented version.
			 */
			protected $file_contents;

			/**
			 * @var array Current vars.
			 *
			 * @since 141111 First documented version.
			 */
			protected $current_vars;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string      $file Template file.
			 *
			 * @param string|null $type Template type. Defaults to an empty string.
			 *    An empty string (or `NULL`) indicates the currently configured type.
			 *
			 * @param boolean     $force_default Force default template?
			 *
			 * @throws \exception If `$file` is empty.
			 */
			public function __construct($file, $type = '', $force_default = FALSE)
			{
				parent::__construct();

				if($type) // Use a specific type?
					$this->type = trim(strtolower((string)$type));
				if(!$this->type) $this->type = $this->plugin->options['template_type'];

				if(!$this->type) // Empty type property?
					throw new \exception(__('Empty type.', $this->plugin->text_domain));

				$this->file = (string)$file; // Initialize.
				$this->file = $this->plugin->utils_string->trim_deep($this->file, '', '/');
				$this->file = $this->plugin->utils_fs->n_seps($this->file);

				if(!$this->file) // Empty file property?
					throw new \exception(__('Empty file.', $this->plugin->text_domain));

				$this->snippet_sub_dir = dirname($this->file).'/snippet';
				$this->force_default   = (boolean)$force_default;
				$this->file_contents   = $this->get_file_contents();
				$this->current_vars    = array(); // Initialize.
			}

			/**
			 * Public access to file; relative path.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Template file; relative path.
			 */
			public function file()
			{
				return $this->file;
			}

			/**
			 * Public access to file contents.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Unparsed template file contents.
			 */
			public function file_contents()
			{
				return $this->file_contents;
			}

			/**
			 * Parse template file.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return string Parsed template file contents.
			 */
			public function parse(array $vars = array())
			{
				$vars['plugin'] = plugin(); // Plugin class.

				$vars['template'] = $this; // Template reference.

				if(strpos($this->file, 'site/') === 0)
					$vars = array_merge($vars, $this->site_vars($vars));

				if(strpos($this->file, 'email/') === 0)
					$vars = array_merge($vars, $this->email_vars($vars));

				$this->current_vars = &$vars; // Setup current variables.

				return trim($this->plugin->utils_php->evaluate($this->file_contents, $vars));
			}

			/**
			 * Parse snippet file.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $file File path, relative to snippet sub-directory.
			 * @param array  $shortcodes_vars Optional array shortcodes/variables.
			 *
			 * @return string Parsed snippet file contents.
			 */
			public function snippet($file, array $shortcodes_vars = array())
			{
				$file = (string)$file; // Force string.
				$file = $this->plugin->utils_string->trim_deep($file, '', '/');
				$file = $this->plugin->utils_fs->n_seps($file);

				$shortcodes_vars = // Merge w/ current vars.
					array_merge($this->current_vars, $shortcodes_vars);
				$shortcodes      = array(); // Initialize.

				foreach($shortcodes_vars as $_key => $_value)
					if(is_string($_key) && preg_match('/^\[(?:[^\s\[\]]+?)\]$/', $_key))
						if(is_string($_value) || is_integer($_value) || is_float($_value))
							$shortcodes[$_key] = (string)$_value;
				unset($_key, $_value); // Housekeeping.

				$snippet = trim($this->snippet_file_contents($file));

				$sc_conditionals = new sc_conditionals($snippet, $shortcodes_vars);
				$snippet         = $sc_conditionals->parse(); // Evaluates [if expression] logic.

				$snippet = str_ireplace(array_keys($shortcodes), array_values($shortcodes), $snippet);
				$snippet = do_shortcode($snippet); // Support WordPress shortcodes also.

				return $snippet; // Final snippet output.
			}

			/**
			 * Site template vars.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return array An array of all site template vars.
			 */
			protected function site_vars(array $vars = array())
			{
				if(strpos($this->file, 'site/header') === 0)
					return array(); // Prevent infinite loop.

				if(strpos($this->file, 'site/footer') === 0)
					return array(); // Prevent infinite loop.

				// Parent template reference.

				$vars['parent_template'] = $this; // Parent reference.

				// All header-related templates.

				if(is_null($site_header_template = &$this->cache_key(__FUNCTION__, 'site_header_template')))
					$site_header_template = new template('site/header.php');

				if(is_null($site_header_styles_template = &$this->cache_key(__FUNCTION__, 'site_header_styles_template')))
					$site_header_styles_template = new template('site/header-styles.php');

				if(is_null($site_header_scripts_template = &$this->cache_key(__FUNCTION__, 'site_header_scripts_template')))
					$site_header_scripts_template = new template('site/header-scripts.php');

				if(is_null($site_header_tag_template = &$this->cache_key(__FUNCTION__, 'site_header_tag_template')))
					$site_header_tag_template = new template('site/header-tag.php');

				$site_header_styles  = $site_header_styles_template->parse($vars);
				$site_header_scripts = $site_header_scripts_template->parse($vars);
				$site_header_tag     = $site_header_tag_template->parse($vars);
				$site_header_vars    = compact('site_header_styles', 'site_header_scripts', 'site_header_tag');
				$site_header         = $site_header_template->parse(array_merge($vars, $site_header_vars));

				// All footer-related templates.

				if(is_null($site_footer_tag_template = &$this->cache_key(__FUNCTION__, 'site_footer_tag_template')))
					$site_footer_tag_template = new template('site/footer-tag.php');

				if(is_null($site_footer_template = &$this->cache_key(__FUNCTION__, 'site_footer_template')))
					$site_footer_template = new template('site/footer.php');

				$site_footer_tag  = $site_footer_tag_template->parse($vars);
				$site_footer_vars = compact('site_footer_tag'); // Only one for now.
				$site_footer      = $site_footer_template->parse(array_merge($vars, $site_footer_vars));

				return compact('site_header', 'site_footer'); // Header/footer.
			}

			/**
			 * Email template vars.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return array An array of all email template vars.
			 */
			protected function email_vars(array $vars = array())
			{
				if(strpos($this->file, 'email/header') === 0)
					return array(); // Prevent infinite loop.

				if(strpos($this->file, 'email/footer') === 0)
					return array(); // Prevent infinite loop.

				// Parent template reference.

				$vars['parent_template'] = $this; // Parent reference.

				// All header-related templates.

				if(is_null($email_header_template = &$this->cache_key(__FUNCTION__, 'email_header_template')))
					$email_header_template = new template('email/header.php');

				if(is_null($email_header_styles_template = &$this->cache_key(__FUNCTION__, 'email_header_styles_template')))
					$email_header_styles_template = new template('email/header-styles.php');

				if(is_null($email_header_scripts_template = &$this->cache_key(__FUNCTION__, 'email_header_scripts_template')))
					$email_header_scripts_template = new template('email/header-scripts.php');

				if(is_null($email_header_tag_template = &$this->cache_key(__FUNCTION__, 'email_header_tag_template')))
					$email_header_tag_template = new template('email/header-tag.php');

				$email_header_styles  = $email_header_styles_template->parse($vars);
				$email_header_scripts = $email_header_scripts_template->parse($vars);
				$email_header_tag     = $email_header_tag_template->parse($vars);
				$email_header_vars    = compact('email_header_styles', 'email_header_scripts', 'email_header_tag');
				$email_header         = $email_header_template->parse(array_merge($vars, $email_header_vars));

				// All footer-related templates.

				if(is_null($email_footer_tag_template = &$this->cache_key(__FUNCTION__, 'email_footer_tag_template')))
					$email_footer_tag_template = new template('email/footer-tag.php');

				if(is_null($email_footer_template = &$this->cache_key(__FUNCTION__, 'email_footer_template')))
					$email_footer_template = new template('email/footer.php');

				$email_footer_tag  = $email_footer_tag_template->parse($vars);
				$email_footer_vars = compact('email_footer_tag'); // Only one for now.
				$email_footer      = $email_footer_template->parse(array_merge($vars, $email_footer_vars));

				return compact('email_header', 'email_footer'); // Header/footer.
			}

			/**
			 * Template file contents.
			 *
			 * @since 141111 First documented version.
			 *
			 * @throws \exception If unable to locate the template.
			 */
			protected function get_file_contents()
			{
				if($this->force_default)
					goto default_template;

				check_theme_dirs: // Target point.

				$dirs = array(); // Initialize.
				// e.g. `wp-content/themes/[theme]/[plugin slug]/type-a/[site/comment-form/file.php]`
				$dirs[] = get_stylesheet_directory().'/'.$this->plugin->slug.'/type-'.$this->type;
				$dirs[] = get_template_directory().'/'.$this->plugin->slug.'/type-'.$this->type;

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; templates CAN be empty.
					if(is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file))
						return file_get_contents($_dir.'/'.$this->file);
				unset($_dir); // Housekeeping.

				check_option_key: // Target point.

				// e.g. type `a` for `site/comment-form/file.php`.
				// becomes: `template__type_a__site__comment_form__file`.
				$option_key = static::data_option_key(array('type' => $this->type, 'file' => $this->file));

				if(!empty($this->plugin->options[$option_key]))
					return $this->plugin->options[$option_key];

				default_template: // Target point; default template.

				// Default template directory.
				$dirs   = array(); // Initialize.
				$dirs[] = dirname(dirname(__FILE__)).'/templates/type-'.$this->type;

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; templates CAN be empty.
					if(is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file))
						return file_get_contents($_dir.'/'.$this->file);
				unset($_dir); // Housekeeping.

				throw new \exception(sprintf(__('Missing template: `type-%1$s/%2$s`.', $this->plugin->text_domain), $this->type, $this->file));
			}

			/**
			 * Snippet file contents.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $file File path, relative to snippet sub-directory.
			 *
			 * @return string Snippet file contents; for the requested snippet.
			 *
			 * @throws \exception If unable to locate the snippet.
			 */
			protected function snippet_file_contents($file)
			{
				if($this->force_default)
					goto default_snippet;

				check_theme_dirs: // Target point.

				$dirs = array(); // Initialize.
				// e.g. `wp-content/themes/[theme]/[plugin slug]/type-a/[site/comment-form/snippet/file.php]`
				$dirs[] = get_stylesheet_directory().'/'.$this->plugin->slug.'/type-'.$this->type.'/'.$this->snippet_sub_dir;
				$dirs[] = get_template_directory().'/'.$this->plugin->slug.'/type-'.$this->type.'/'.$this->snippet_sub_dir;

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; snippets CAN be empty.
					if(is_file($_dir.'/'.$file) && is_readable($_dir.'/'.$file))
						return file_get_contents($_dir.'/'.$file);
				unset($_dir); // Housekeeping.

				check_option_key: // Target point.

				// e.g. type `a` for `site/comment-form/snippet/file.php`.
				// becomes: `template__type_a__site__comment_form__snipppet__file`.
				$option_key = static::data_option_key(array('type' => $this->type, 'file' => $this->snippet_sub_dir.'/'.$file));

				if(!empty($this->plugin->options[$option_key]))
					return $this->plugin->options[$option_key];

				default_snippet: // Target point; default snippet.

				// Default snippet directory.
				$dirs   = array(); // Initialize.
				$dirs[] = dirname(dirname(__FILE__)).'/templates/type-'.$this->type.'/'.$this->snippet_sub_dir;

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; templates CAN be empty.
					if(is_file($_dir.'/'.$file) && is_readable($_dir.'/'.$file))
						return file_get_contents($_dir.'/'.$file);
				unset($_dir); // Housekeeping.

				throw new \exception(sprintf(__('Missing snippet: `%1$s`.', $this->plugin->text_domain), 'type-'.$this->type.'/'.$this->snippet_sub_dir.'/'.$file));
			}

			/**
			 * Transforms an option key into a type & file path.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $option_key Template option key.
			 *
			 * @return \stdClass Object w/ two properties: `type` and `file`.
			 */
			public static function option_key_data($option_key)
			{
				$plugin = plugin(); // Plugin class.

				$type       = $file = ''; // Initialize.
				$option_key = trim(strtolower((string)$option_key));

				if(preg_match('/^template__type_(?P<type>.+?)__/', $option_key, $_m))
					$type = trim(strtolower((string)$_m['type'])); // Key has type?
				if(!$type) $type = $plugin->options['template_type'];
				unset($_m); // Just a little housekeeping.

				$file = $option_key; // Initialize.
				$file = preg_replace('/^template__type_.+?__/', '', $file);
				$file = str_replace('_', '-', str_replace('__', '/', $file));
				$file .= '.php'; // Add `.php` extension also.

				$file = $plugin->utils_string->trim_deep($file, '', '/');
				$file = $plugin->utils_fs->n_seps($file);

				return (object)compact('type', 'file');
			}

			/**
			 * Transforms option data (type/file) into a plugin option key.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param \stdClass|array Two properties: `type`, `file`.
			 *
			 * @return string The plugin option key for the given template data.
			 */
			public static function data_option_key($data)
			{
				$plugin = plugin(); // Plugin class.

				$type = $file = ''; // Initialize.
				if(is_array($data)) $data = (object)$data;
				if(!is_object($data)) $data = new \stdClass;

				if(!empty($data->type)) // Specific type?
					$type = trim(strtolower((string)$data->type));
				if(!$type) $type = $plugin->options['template_type'];

				if(!empty($data->file)) // In case it is empty.
					$file = trim(strtolower((string)$data->file));
				$file = $plugin->utils_string->trim_deep($file, '', '/');
				$file = $plugin->utils_fs->n_seps($file);

				$option_key = $file; // Initialize.
				$option_key = preg_replace('/\.php$/i', '', $option_key);
				$option_key = str_replace('/', '__', $option_key);
				$option_key = str_replace('-', '_', $option_key);
				$option_key = 'template__type_'.$type.'__'.$option_key;

				return $option_key; // Plugin option key.
			}
		}
	}
}