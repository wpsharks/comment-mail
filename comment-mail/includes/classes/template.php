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
			 * @var array Instance cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $cache = array();

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

				// e.g. `site/comment-form/subscription-ops.php`.
				// e.g. `email/confirmation-request-message.php`.
				// becomes: `template_site_comment_form_subscription_ops`.
				// becomes: `template_email_confirmation_request_message`.
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
				if(strpos($this->file, 'site/') === 0)
					$vars = array_merge($vars, $this->site_vars($vars));

				if(strpos($this->file, 'email/') === 0)
					$vars = array_merge($vars, $this->email_vars($vars));

				if(!empty($vars['sub']) && $vars['sub'] instanceof \stdClass)
					$vars = array_merge($vars, $this->sub_vars($vars['sub']));

				return $this->php->evaluate($this->file_contents, $vars);
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
				if($this->file === 'site/common-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'site/common-footer.php')
					return array(); // Prevent infinite loop.

				if(!isset($this->cache[__FUNCTION__]['site_header_template']))
					$this->cache[__FUNCTION__]['site_header_template'] = new template('site/common-header.php');
				$site_header_template = &$this->cache[__FUNCTION__]['site_header_template'];

				if(!isset($this->cache[__FUNCTION__]['site_footer_template']))
					$this->cache[__FUNCTION__]['site_footer_template'] = new template('site/common-footer.php');
				$site_footer_template = &$this->cache[__FUNCTION__]['site_footer_template'];
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
				if($this->file === 'email/common-header.php')
					return array(); // Prevent infinite loop.

				if($this->file === 'email/common-footer.php')
					return array(); // Prevent infinite loop.

				if(!isset($this->cache[__FUNCTION__]['email_header_template']))
					$this->cache[__FUNCTION__]['email_header_template'] = new template('email/common-header.php');
				$email_header_template = &$this->cache[__FUNCTION__]['email_header_template'];

				if(!isset($this->cache[__FUNCTION__]['email_footer_template']))
					$this->cache[__FUNCTION__]['email_footer_template'] = new template('email/common-footer.php');
				$email_footer_template = &$this->cache[__FUNCTION__]['email_footer_template'];
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
			 * @param \stdClass $sub Subscriber object.
			 *
			 * @return array An array of all sub. template vars.
			 */
			protected function sub_vars(\stdClass $sub)
				// @TODO Add link to subscriptions management panel.
			{
				$confirm_url     = add_query_arg(urlencode_deep(array(__NAMESPACE__ => array('confirm' => $sub->key))), home_url('/'));
				$unsubscribe_url = add_query_arg(urlencode_deep(array(__NAMESPACE__ => array('unsubscribe' => $sub->key))), home_url('/'));

				return compact('confirm_url', 'unsubscribe_url');
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