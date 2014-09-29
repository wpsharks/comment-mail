<?php
/**
 * String Utilities
 *
 * @package utils_string
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_string'))
	{
		/**
		 * String Utilities
		 *
		 * @package utils_string
		 * @since 14xxxx First documented version.
		 */
		class utils_string // String utilities.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
			}

			/**
			 * Strips slashes in strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $value Any value can be converted into a stripped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @return string|array|object Stripped string, array, object.
			 */
			public function strip_deep($value)
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => &$_value)
						$_value = $this->strip_deep($_value);
					unset($_key, $_value); // Housekeeping.

					return $value; // Stripped deeply.
				}
				return stripslashes((string)$value);
			}

			/**
			 * Trims strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed  $value Any value can be converted into a trimmed string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param string $chars Specific chars to trim.
			 *    Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
			 *
			 * @param string $extra_chars Additional chars to trim.
			 *
			 * @return string|array|object Trimmed string, array, object.
			 */
			public function trim_deep($value, $chars = '', $extra_chars = '')
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => &$_value)
						$_value = $this->trim_deep($_value, $chars, $extra_chars);
					unset($_key, $_value); // Housekeeping.

					return $value; // Trimmed deeply.
				}
				$chars = isset($chars[0]) ? $chars : " \r\n\t\0\x0B";
				$chars = $chars.$extra_chars; // Concatenate.

				return trim((string)$value, $chars);
			}

			/**
			 * Trims and strips slashes in strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed  $value Any value can be converted into a trimmed/stripped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param string $chars Specific chars to trim.
			 *    Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
			 *
			 * @param string $extra_chars Additional chars to trim.
			 *
			 * @return string|array|object Trimmed/stripped string, array, object.
			 */
			public function trim_strip_deep($value, $chars = '', $extra_chars = '')
			{
				return $this->trim_deep($this->strip_deep($value), $chars, $extra_chars);
			}

			/**
			 * Cleans a full name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $name Input name to clean.
			 *
			 * @return string Cleaned name.
			 */
			public function clean_name($name)
			{
				if(!($name = trim((string)$name)))
					return ''; // Nothing to do.

				$name = $name ? preg_replace('/^(?:Mr\.?|Mrs\.?|Ms\.?|Dr\.?)\s+/i', '', $name) : '';
				$name = $name ? preg_replace('/\s+(?:Sr\.?|Jr\.?|IV|I+)$/i', '', $name) : '';
				$name = $name ? preg_replace('/\s+/', ' ', $name) : '';
				$name = $name ? trim($name) : ''; // Trim it up now.

				return $name; // Cleaned up now.
			}
		}
	}
}