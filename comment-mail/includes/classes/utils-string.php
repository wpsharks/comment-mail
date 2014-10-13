<?php
/**
 * String Utilities
 *
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
		 * @since 14xxxx First documented version.
		 */
		class utils_string extends abstract_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
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
			 * Escapes single quotes deeply.
			 *
			 * @param mixed   $value Any value can be converted into an escaped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param integer $times Number of escapes. Defaults to `1`.
			 *
			 * @return string|array|object Escaped string, array, object.
			 */
			public function esc_sq_deep($value, $times = 1)
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as &$_value)
						$_value = $this->esc_sq_deep($_value, $times);
					unset($_value); // Housekeeping.

					return $value; // All done.
				}
				return str_replace("'", str_repeat('\\', abs((integer)$times))."'", (string)$value);
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

			/**
			 * Clips a string to X chars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $string Input string to clip.
			 * @param integer $max_length Defaults to a value of `45`.
			 * @param boolean $force_ellipsis Defaults to a value of `FALSE`.
			 *
			 * @return string Clipped string.
			 */
			public function clip($string, $max_length = 45, $force_ellipsis = FALSE)
			{
				if(!($string = (string)$string))
					return $string; // Empty.

				$max_length = ($max_length < 4) ? 4 : $max_length;

				$string = trim(preg_replace('/\s+/', ' ', strip_tags($string)));

				if(strlen($string) > $max_length)
					$string = (string)substr($string, 0, $max_length - 3).'...';

				else if($force_ellipsis && strlen($string) + 3 > $max_length)
					$string = (string)substr($string, 0, $max_length - 3).'...';

				else $string .= $force_ellipsis ? '...' : '';

				return $string; // Clipped.
			}

			/**
			 * Mid-clips a string to X chars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $string Input string to clip.
			 * @param integer $max_length Defaults to a value of `45`.
			 *
			 * @return string Mid-clipped string.
			 */
			public function mid_clip($string, $max_length = 45)
			{
				if(!($string = (string)$string))
					return $string; // Empty.

				$max_length = ($max_length < 4) ? 4 : $max_length;

				$string = trim(preg_replace('/\s+/', ' ', strip_tags($string)));

				if(strlen($string) <= $max_length)
					return $string; // Nothing to do.

				$full_string     = $string;
				$half_max_length = floor($max_length / 2);

				$first_clip = $half_max_length - 3;
				$string     = ($first_clip >= 1) // Something?
					? substr($full_string, 0, $first_clip).'...'
					: '...'; // Ellipsis only.

				$second_clip = strlen($full_string) - ($max_length - strlen($string));
				$string .= ($second_clip >= 0 && $second_clip >= $first_clip)
					? substr($full_string, $second_clip) : ''; // Nothing more.

				return $string; // Mid-clipped.
			}
		}
	}
}