<?php
/**
 * Template
 *
 * @since 14xxxx First documented version.
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
		 * @since 14xxxx First documented version.
		 */
		class template extends abs_base
		{
			/**
			 * @var string Template file.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file;

			/**
			 * @var string Template file option key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_option_key;

			/**
			 * @var boolean Force default template?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $force_default;

			/**
			 * @var string Template file contents.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_contents;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $file Template file.
			 * @param boolean $force_default Force default template?
			 *
			 * @throws \exception If `$file` is empty.
			 */
			public function __construct($file, $force_default = FALSE)
			{
				parent::__construct();

				$this->file = (string)$file;
				$this->file = $this->plugin->utils_string->trim_deep($this->file, '', '/');
				$this->file = $this->plugin->utils_fs->n_seps($this->file);

				if(!$this->file) // Empty file property?
					throw new \exception(__('Empty file property.', $this->plugin->text_domain));

				$this->file_option_key = $this->file; // Initialize.
				$this->file_option_key = preg_replace('/\.php$/i', '', $this->file_option_key);
				$this->file_option_key = str_replace('/', '__', $this->file_option_key);
				$this->file_option_key = str_replace('-', '_', $this->file_option_key);
				$this->file_option_key = 'template__'.$this->file_option_key;

				$this->force_default = (boolean)$force_default;

				$this->file_contents = $this->get_file_contents();
			}

			/**
			 * Public access to file contents.
			 *
			 * @since 14xxxx First documented version.
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
			 * @since 14xxxx First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return string Parsed template file contents.
			 */
			public function parse(array $vars = array())
			{
				$vars['plugin'] = plugin(); // Plugin class.

				if(!isset($vars['template_file'])) // Don't override in site/email children.
					$vars['template_file'] = $this->file; // Template file name.

				if(strpos($this->file, 'site/') === 0)
					$vars = array_merge($vars, $this->site_vars($vars));

				if(strpos($this->file, 'email/') === 0)
					$vars = array_merge($vars, $this->email_vars($vars));

				return trim($this->plugin->utils_php->evaluate($this->file_contents, $vars));
			}

			/**
			 * Site template vars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return array An array of all site template vars.
			 */
			protected function site_vars(array $vars = array())
			{
				if(strpos($this->file, 'site/site-header') === 0)
					return array(); // Prevent infinite loop.

				if(strpos($this->file, 'site/site-footer') === 0)
					return array(); // Prevent infinite loop.

				// All header-related templates.

				if(is_null($site_header_template = &$this->cache_key(__FUNCTION__, 'site_header_template')))
					$site_header_template = new template('site/site-header.php');

				if(is_null($site_header_styles_template = &$this->cache_key(__FUNCTION__, 'site_header_styles_template')))
					$site_header_styles_template = new template('site/site-header-styles.php');

				if(is_null($site_header_scripts_template = &$this->cache_key(__FUNCTION__, 'site_header_scripts_template')))
					$site_header_scripts_template = new template('site/site-header-scripts.php');

				if(is_null($site_header_easy_template = &$this->cache_key(__FUNCTION__, 'site_header_easy_template')))
					$site_header_easy_template = new template('site/site-header-easy.php');

				$site_header_styles  = $site_header_styles_template->parse($vars);
				$site_header_scripts = $site_header_scripts_template->parse($vars);
				$site_header_easy    = $site_header_easy_template->parse($vars);
				$site_header_vars    = compact('site_header_styles', 'site_header_scripts', 'site_header_easy');
				$site_header         = $site_header_template->parse(array_merge($vars, $site_header_vars));

				// All footer-related templates.

				if(is_null($site_footer_easy_template = &$this->cache_key(__FUNCTION__, 'site_footer_easy_template')))
					$site_footer_easy_template = new template('site/site-footer-easy.php');

				if(is_null($site_footer_template = &$this->cache_key(__FUNCTION__, 'site_footer_template')))
					$site_footer_template = new template('site/site-footer.php');

				$site_footer_easy = $site_footer_easy_template->parse($vars);
				$site_footer_vars = compact('site_footer_easy'); // Only one for now.
				$site_footer      = $site_footer_template->parse(array_merge($vars, $site_footer_vars));

				return compact('site_header', 'site_footer'); // Header/footer.
			}

			/**
			 * Email template vars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $vars Optional array of variables to parse.
			 *
			 * @return array An array of all email template vars.
			 */
			protected function email_vars(array $vars = array())
			{
				if(strpos($this->file, 'email/email-header') === 0)
					return array(); // Prevent infinite loop.

				if(strpos($this->file, 'email/email-footer') === 0)
					return array(); // Prevent infinite loop.

				// All header-related templates.

				if(is_null($email_header_template = &$this->cache_key(__FUNCTION__, 'email_header_template')))
					$email_header_template = new template('email/email-header.php');

				if(is_null($email_header_styles_template = &$this->cache_key(__FUNCTION__, 'email_header_styles_template')))
					$email_header_styles_template = new template('email/email-header-styles.php');

				if(is_null($email_header_scripts_template = &$this->cache_key(__FUNCTION__, 'email_header_scripts_template')))
					$email_header_scripts_template = new template('email/email-header-scripts.php');

				if(is_null($email_header_easy_template = &$this->cache_key(__FUNCTION__, 'email_header_easy_template')))
					$email_header_easy_template = new template('email/email-header-easy.php');

				$email_header_styles  = $email_header_styles_template->parse($vars);
				$email_header_scripts = $email_header_scripts_template->parse($vars);
				$email_header_easy    = $email_header_easy_template->parse($vars);
				$email_header_vars    = compact('email_header_styles', 'email_header_scripts', 'email_header_easy');
				$email_header         = $email_header_template->parse(array_merge($vars, $email_header_vars));

				// All footer-related templates.

				if(is_null($email_footer_easy_template = &$this->cache_key(__FUNCTION__, 'email_footer_easy_template')))
					$email_footer_easy_template = new template('email/email-footer-easy.php');

				if(is_null($email_footer_template = &$this->cache_key(__FUNCTION__, 'email_footer_template')))
					$email_footer_template = new template('email/email-footer.php');

				$email_footer_easy = $email_footer_easy_template->parse($vars);
				$email_footer_vars = compact('email_footer_easy'); // Only one for now.
				$email_footer      = $email_footer_template->parse(array_merge($vars, $email_footer_vars));

				// Add "powered by" note at the bottom of all email templates?

				if(!$this->plugin->is_pro || $this->plugin->options['email_footer_powered_by_enable'])
				{
					$powered_by   = '<hr />'. // Leading divider to help separate this.
					                '<p style="color:#888888;">'. // Powered by note at the bottom of all emails.
					                ' ~ '.$this->plugin->utils_markup->powered_by(). // e.g. `powered by Comment Mail™ for WordPress`.
					                ' &lt;<a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank" style="text-decoration:none;">'.
					                esc_html($this->plugin->utils_url->product_page()).'</a>&gt;'.
					                '</p>';
					$email_footer = str_ireplace('</body>', $powered_by.'</body>', $email_footer); // Before closing body tag.
				}
				return compact('email_header', 'email_footer'); // Header/footer.
			}

			/**
			 * Template file contents.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If unable to locate the template.
			 */
			protected function get_file_contents()
			{
				if($this->force_default)
					goto default_template;

				check_theme_dirs: // Target point.

				$dirs = array(); // Initialize.
				// e.g. `wp-content/themes/[theme]/[plugin slug]`.
				// e.g. `wp-content/themes/[theme]/[plugin slug]/site/comment-form/subscription-ops.php`.
				// e.g. `wp-content/themes/[theme]/[plugin slug]/email/confirmation-request-message.php`.
				$dirs[] = get_stylesheet_directory().'/'.$this->plugin->slug;
				$dirs[] = get_template_directory().'/'.$this->plugin->slug;

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; templates CAN be empty.
					if(is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file))
						return file_get_contents($_dir.'/'.$this->file);
				unset($_dir); // Housekeeping.

				check_option_key: // Target point.

				// e.g. `site/comment-form/sub-ops.php`.
				// e.g. `email/confirmation-request-message.php`.
				// becomes: `template__site__comment_form__sub_ops`.
				// becomes: `template__email__confirmation_request_message`.
				if(!empty($this->plugin->options[$this->file_option_key]))
					return $this->plugin->options[$this->file_option_key];

				default_template: // Target point; default template.

				// Default template directory.
				$dirs   = array(); // Initialize.
				$dirs[] = dirname(dirname(__FILE__)).'/templates';

				foreach($dirs as $_dir /* In order of precedence. */)
					// Note: don't check `filesize()` here; templates CAN be empty.
					if(is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file))
						return file_get_contents($_dir.'/'.$this->file);
				unset($_dir); // Housekeeping.

				throw new \exception(sprintf(__('Missing template for: `%1$s`.', $this->plugin->text_domain), $this->file));
			}

			/**
			 * Transforms an option key into a file path.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $file_option_key Option key.
			 *
			 * @return string Relative file path matching the input option key.
			 */
			public static function option_key_to_file($file_option_key)
			{
				$file = $file_option_key; // Initialize.

				$file = preg_replace('/^template__/', '', $file);
				$file = str_replace('__', '/', $file);
				$file = str_replace('_', '-', $file);

				return $file.'.php'; // Relative file path.
			}
		}
	}
}