<?php
/**
 * Template
 *
 * @package template
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
		 * @package template
		 * @since 14xxxx First documented version.
		 */
		class template // User deletion handler.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var string Template file.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file; // Set by constructor.

			/**
			 * @var string Template file option key.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_option_key; // Set by constructor.

			/**
			 * @var string Template file contents.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $file_contents; // Set by constructor.

			/**
			 * @var php PHP Utilities.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $php; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $file Template file.
			 *
			 * @throws \exception If `$file` is empty.
			 */
			public function __construct($file)
			{
				$this->plugin = plugin();

				$this->file = (string)$file;
				$this->file = $this->plugin->utils_string->trim_deep($this->file, '', '/');

				if(!$this->file) // Empty file property?
					throw new \exception(__('Empty file property.', $this->plugin->text_domain));

				// e.g. `emails/confirmation.php`.
				// e.g. `site/subscription-ops.php`.
				// becomes: `template_emails_confirmation`.
				// becomes: `template_site_subscription_ops`.
				$this->file_option_key = preg_replace('/\.php$/i', '', $this->file);
				$this->file_option_key = str_replace(array('/', '-'), '_', $this->file_option_key);
				$this->file_option_key = 'template_'.$this->file_option_key;

				$this->file_contents = $this->get_file_contents();

				$this->php = new php(); // Utilities.
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
				return $this->php->evaluate($this->file_contents, $vars);
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
				// e.g. `emails/confirmation.php`.
				// e.g. `site/subscription-ops.php`.
				// becomes: `template_emails_confirmation`.
				// becomes: `template_site_subscription_ops`.
				if(!empty($this->plugin->options[$this->file_option_key]))
					return $this->plugin->options[$this->file_option_key];

				// e.g. `wp-content/themes/[theme]/comment-mail`.
				// e.g. `wp-content/themes/[theme]/comment-mail/emails/confirmation.php`.
				// e.g. `wp-content/themes/[theme]/comment-mail/site/subscription-ops.php`.
				$dirs[] = get_stylesheet_directory().'/'.$this->plugin->slug;
				$dirs[] = get_template_directory().'/'.$this->plugin->slug;
				$dirs[] = dirname(dirname(__FILE__)).'/templates';

				foreach($dirs as $_dir /* In order of precedence. */)
					if(is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file) && filesize($_dir.'/'.$this->file))
						return file_get_contents($_dir.'/'.$this->file);
				unset($_dir); // Housekeeping.

				throw new \exception(sprintf('Missing template for: `%1$s`.', $this->plugin->text_domain), $this->file);
			}
		}
	}
}