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
			 * @param string $file Template file.
			 *
			 * @throws \exception If `$file` is empty.
			 */
			public function __construct($file)
			{
				parent::__construct();

				$this->file = (string)$file;
				$this->file = $this->plugin->utils_string->trim_deep($this->file, '', '/');

				if(!$this->file) // Empty file property?
					throw new \exception(__('Empty file property.', $this->plugin->text_domain));

				// e.g. `site/comment-form/subscription-ops.php`.
				// e.g. `email/confirmation-request-message.php`.
				// becomes: `template_site_comment_form_subscription_ops`.
				// becomes: `template_email_confirmation_request_message`.
				$this->file_option_key = preg_replace('/\.php$/i', '', $this->file);
				$this->file_option_key = str_replace(array('/', '-'), '_', $this->file_option_key);
				$this->file_option_key = 'template_'.$this->file_option_key;

				$this->file_contents = $this->get_file_contents();
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
				if(strpos($this->file, 'site/') === 0)
					$vars = array_merge($vars, $this->site_vars($vars));

				if(strpos($this->file, 'email/') === 0)
					$vars = array_merge($vars, $this->email_vars($vars));

				if(!empty($vars['sub']) && $vars['sub'] instanceof \stdClass)
					$vars = array_merge($vars, $this->sub_vars($vars['sub']));

				return $this->plugin->utils_php->evaluate($this->file_contents, $vars);
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
				if($this->file === 'site/site-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'site/site-footer.php')
					return array(); // Prevent infinite loop.

				if(is_null($site_header_template = &$this->cache_key(__FUNCTION__, 'site_header_template')))
					$site_header_template = new template('site/site-header.php');

				if(is_null($site_footer_template = &$this->cache_key(__FUNCTION__, 'site_footer_template')))
					$site_footer_template = new template('site/site-footer.php');
				/**
				 * @var $site_header_template template For IDEs.
				 * @var $site_footer_template template For IDEs.
				 */
				$site_header = $site_header_template->parse($vars);
				$site_footer = $site_footer_template->parse($vars);

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
				if($this->file === 'email/email-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'email/email-footer.php')
					return array(); // Prevent infinite loop.

				if(is_null($email_header_template = &$this->cache_key(__FUNCTION__, 'email_header_template')))
					$email_header_template = new template('email/email-header.php');

				if(is_null($email_footer_template = &$this->cache_key(__FUNCTION__, 'email_footer_template')))
					$email_footer_template = new template('email/email-footer.php');
				/**
				 * @var $email_header_template template For IDEs.
				 * @var $email_footer_template template For IDEs.
				 */
				$email_header = $email_header_template->parse($vars);
				$email_footer = $email_footer_template->parse($vars);

				return compact('email_header', 'email_footer'); // Header/footer.
			}

			/**
			 * Sub. template vars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $sub Subscription object.
			 *
			 * @return array An array of all sub. template vars.
			 */
			protected function sub_vars(\stdClass $sub)
			{
				$confirm_url     = $this->plugin->utils_url->sub_confirm_url($sub->key);
				$unsubscribe_url = $this->plugin->utils_url->sub_unsubscribe_url($sub->key);
				$manage_url      = $this->plugin->utils_url->sub_manage_url($sub->key);

				return compact('confirm_url', 'unsubscribe_url', 'manage_url');
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
				// e.g. `site/comment-form/subscription-ops.php`.
				// e.g. `email/confirmation-request-message.php`.
				// becomes: `template_site_comment_form_subscription_ops`.
				// becomes: `template_email_confirmation_request_message`.
				if(!empty($this->plugin->options[$this->file_option_key]))
					return $this->plugin->options[$this->file_option_key];

				// e.g. `wp-content/themes/[theme]/comment-mail`.
				// e.g. `wp-content/themes/[theme]/comment-mail/site/comment-form/subscription-ops.php`.
				// e.g. `wp-content/themes/[theme]/comment-mail/email/confirmation-request-message.php`.
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