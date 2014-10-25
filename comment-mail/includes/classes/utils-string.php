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
		class utils_string extends abs_base
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
			 * Strips slashes.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string See {@link strip_deep()}.
			 *
			 * @return string See {@link strip_deep()}.
			 */
			public function strip($string)
			{
				return $this->strip_deep((string)$string);
			}

			/**
			 * Strips slashes in strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $values Anything can be converted into a stripped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @return string|array|object Stripped string, array, object.
			 */
			public function strip_deep($values)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->strip_deep($_values);
					unset($_key, $_values); // Housekeeping.

					return $values; // Stripped deeply.
				}
				$string = (string)$values;

				return stripslashes($string);
			}

			/**
			 * Trims string.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string See {@link trim_deep()}.
			 * @param string $chars See {@link trim_deep()}.
			 * @param string $extra_chars See {@link trim_deep()}.
			 *
			 * @return string See {@link trim_deep()}.
			 */
			public function trim($string, $chars = '', $extra_chars = '')
			{
				return $this->trim_deep((string)$string, $chars, $extra_chars);
			}

			/**
			 * Trims strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed  $values Any value can be converted into a trimmed string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param string $chars Specific chars to trim.
			 *    Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
			 *
			 * @param string $extra_chars Additional chars to trim.
			 *
			 * @return string|array|object Trimmed string, array, object.
			 */
			public function trim_deep($values, $chars = '', $extra_chars = '')
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->trim_deep($_values, $chars, $extra_chars);
					unset($_key, $_values); // Housekeeping.

					return $values; // Trimmed deeply.
				}
				$string = (string)$values;
				$chars  = isset($chars[0]) ? $chars : " \r\n\t\0\x0B";
				$chars  = $chars.$extra_chars; // Concatenate.

				return trim($string, $chars);
			}

			/**
			 * Trims/strips string.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string See {@link trim_strip_deep()}.
			 * @param string $chars See {@link trim_strip_deep()}.
			 * @param string $extra_chars See {@link trim_strip_deep()}.
			 *
			 * @return string See {@link trim_strip_deep()}.
			 */
			public function trim_strip($string, $chars = '', $extra_chars = '')
			{
				return $this->trim_strip_deep((string)$string, $chars, $extra_chars);
			}

			/**
			 * Trims and strips slashes in strings deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed  $values Any value can be converted into a trimmed/stripped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param string $chars Specific chars to trim.
			 *    Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
			 *
			 * @param string $extra_chars Additional chars to trim.
			 *
			 * @return string|array|object Trimmed/stripped string, array, object.
			 */
			public function trim_strip_deep($values, $chars = '', $extra_chars = '')
			{
				return $this->trim_deep($this->strip_deep($values), $chars, $extra_chars);
			}

			/**
			 * Escape single quotes.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $string See {@link esc_sq_deep()}.
			 * @param integer $times See {@link esc_sq_deep()}.
			 *
			 * @return string See {@link esc_sq_deep()}.
			 */
			public function esc_sq($string, $times = 1)
			{
				return $this->esc_sq_deep((string)$string, $times);
			}

			/**
			 * Escapes single quotes deeply.
			 *
			 * @param mixed   $values Any value can be converted into an escaped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param integer $times Number of escapes. Defaults to `1`.
			 *
			 * @return string|array|object Escaped string, array, object.
			 */
			public function esc_sq_deep($values, $times = 1)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->esc_sq_deep($_values, $times);
					unset($_key, $_values); // Housekeeping.

					return $values; // All done.
				}
				$string = (string)$values;
				$times  = abs((integer)$times);

				return str_replace("'", str_repeat('\\', $times)."'", $string);
			}

			/**
			 * Escape double quotes.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $string See {@link esc_dq_deep()}.
			 * @param integer $times See {@link esc_dq_deep()}.
			 *
			 * @return string See {@link esc_dq_deep()}.
			 */
			public function esc_dq($string, $times = 1)
			{
				return $this->esc_dq_deep((string)$string, $times);
			}

			/**
			 * Escapes double quotes deeply.
			 *
			 * @param mixed   $values Any value can be converted into an escaped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param integer $times Number of escapes. Defaults to `1`.
			 *
			 * @return string|array|object Escaped string, array, object.
			 */
			public function esc_dq_deep($values, $times = 1)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->esc_dq_deep($_values, $times);
					unset($_key, $_values); // Housekeeping.

					return $values; // All done.
				}
				$string = (string)$values;
				$times  = abs((integer)$times);

				return str_replace('"', str_repeat('\\', $times).'"', $string);
			}

			/**
			 * Escape double quotes for CSV.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $string See {@link esc_csv_dq_deep()}.
			 * @param integer $times See {@link esc_csv_dq_deep()}.
			 *
			 * @return string See {@link esc_csv_dq_deep()}.
			 */
			public function esc_csv_dq($string, $times = 1)
			{
				return $this->esc_csv_dq_deep((string)$string, $times);
			}

			/**
			 * Escapes double quotes deeply; for CSV.
			 *
			 * @param mixed   $values Any value can be converted into an escaped string.
			 *    Actually, objects can't, but this recurses into objects.
			 *
			 * @param integer $times Number of escapes. Defaults to `1`.
			 *
			 * @return string|array|object Escaped string, array, object.
			 */
			public function esc_csv_dq_deep($values, $times = 1)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->esc_csv_dq_deep($_values, $times);
					unset($_key, $_values); // Housekeeping.

					return $values; // All done.
				}
				$string = (string)$values;
				$times  = abs((integer)$times);

				return str_replace('"', str_repeat('"', $times).'"', $string);
			}

			/**
			 * String replace ONE time (caSe-insensitive).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $needle See {@link str_replace_once_deep()}.
			 * @param string $replace See {@link str_replace_once_deep()}.
			 * @param string $string See {@link str_replace_once_deep()}.
			 *
			 * @return string See {@link str_replace_once_deep()}.
			 */
			public function ireplace_once($needle, $replace, $string)
			{
				return $this->replace_once_deep($needle, $replace, (string)$string, TRUE);
			}

			/**
			 * String replace ONE time deeply (caSe-insensitive).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $needle See {@link replace_once_deep()}.
			 * @param string $replace See {@link replace_once_deep()}.
			 * @param mixed  $values See {@link replace_once_deep()}.
			 *
			 * @return string|array|object See {@link replace_once_deep()}.
			 */
			public function ireplace_once_deep($needle, $replace, $values)
			{
				return $this->replace_once_deep($needle, $replace, $values, TRUE);
			}

			/**
			 * String replace ONE time.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $needle See {@link str_replace_once_deep()}.
			 * @param string  $replace See {@link str_replace_once_deep()}.
			 * @param string  $string See {@link str_replace_once_deep()}.
			 * @param boolean $caSe_insensitive See {@link str_replace_once_deep()}.
			 *
			 * @return string See {@link str_replace_once_deep()}.
			 */
			public function str_replace_once($needle, $replace, $string, $caSe_insensitive = FALSE)
			{
				return $this->replace_once_deep($needle, $replace, (string)$string, $caSe_insensitive);
			}

			/**
			 * String replace ONE time deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $needle A string to search/replace.
			 * @param string  $replace What to replace `$needle` with.
			 * @param mixed   $values The haystack(s) to search in.
			 *
			 * @param boolean $caSe_insensitive Defaults to a `FALSE` value.
			 *    Pass this as `TRUE` to a caSe-insensitive search/replace.
			 *
			 * @return string|array|object The `$haystacks`, with `$needle` replaced with `$replace` ONE time only.
			 */
			public function replace_once_deep($needle, $replace, $values, $caSe_insensitive = FALSE)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->replace_once_deep($needle, $replace, $_values, $caSe_insensitive);
					unset($_key, $_values); // Housekeeping.

					return $values; // All done.
				}
				$needle  = (string)$needle;
				$replace = (string)$replace;
				$string  = (string)$values;

				$caSe_strpos = $caSe_insensitive ? 'stripos' : 'strpos';
				if(($needle_strpos = $caSe_strpos($string, $needle)) === FALSE)
					return $string; // Nothing to replace.

				return (string)substr_replace($string, $replace, $needle_strpos, strlen($needle));
			}

			/**
			 * Cleans a full name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string See {@link clean_names_deep()}.
			 *
			 * @return string See {@link clean_names_deep()}.
			 */
			public function clean_name($string)
			{
				return $this->clean_names_deep((string)$string);
			}

			/**
			 * Cleans full name(s) deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $values Input name(s) to clean.
			 *
			 * @return string|array|object Having cleaned name(s) deeply.
			 */
			public function clean_names_deep($values)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->clean_names_deep($_values);
					unset($_values); // Housekeeping.

					return $values; // All done.
				}
				$string = trim((string)$values); // Trim string.
				$string = $string ? str_replace('"', '', $string) : '';
				$string = $string ? preg_replace('/^(?:Mr\.?|Mrs\.?|Ms\.?|Dr\.?)\s+/i', '', $string) : '';
				$string = $string ? preg_replace('/\s+(?:Sr\.?|Jr\.?|IV|I+)$/i', '', $string) : '';
				$string = $string ? preg_replace('/\s+/', ' ', $string) : '';
				$string = $string ? trim($string) : ''; // Trim again.

				return $string; // Cleaned up now.
			}

			/**
			 * Clips a string to X chars.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $string See {@link clip_deep()}.
			 * @param integer $max_length See {@link clip_deep()}.
			 * @param boolean $force_ellipsis See {@link clip_deep()}.
			 *
			 * @return string See {@link clip_deep()}.
			 */
			public function clip($string, $max_length = 45, $force_ellipsis = FALSE)
			{
				return $this->clip_deep((string)$string, $max_length, $force_ellipsis);
			}

			/**
			 * Clips string(s) to X chars deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $values Input string(s) to clip.
			 * @param integer $max_length Defaults to a value of `45`.
			 * @param boolean $force_ellipsis Defaults to a value of `FALSE`.
			 *
			 * @return string|array|object Clipped string(s).
			 */
			public function clip_deep($values, $max_length = 45, $force_ellipsis = FALSE)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->clip_deep($_values, $max_length, $force_ellipsis);
					unset($_key, $_values); // Housekeeping.

					return $values; // All done.
				}
				if(!($string = (string)$values))
					return $string; // Empty.

				$max_length = (integer)$max_length;
				$max_length = $max_length < 4 ? 4 : $max_length;

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
			 * @param string  $string See {@link mid_clip_deep()}.
			 * @param integer $max_length See {@link mid_clip_deep()}
			 *
			 * @return string See {@link mid_clip_deep()}
			 */
			public function mid_clip($string, $max_length = 45)
			{
				return $this->mid_clip_deep((string)$string, $max_length);
			}

			/**
			 * Mid-clips string(s) to X chars deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed   $values Input string(s) to mid-clip.
			 * @param integer $max_length Defaults to a value of `45`.
			 *
			 * @return string|array|object Mid-clipped string(s).
			 */
			public function mid_clip_deep($values, $max_length = 45)
			{
				if(is_array($values) || is_object($values))
				{
					foreach($values as $_key => &$_values)
						$_values = $this->mid_clip_deep($_values, $max_length);
					unset($_key, $_values); // Housekeeping.

					return $values; // All done.
				}
				if(!($string = (string)$values))
					return $string; // Empty.

				$max_length = (integer)$max_length;
				$max_length = $max_length < 4 ? 4 : $max_length;

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

			/**
			 * Is a string in HTML format?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string Any input string to test here.
			 *
			 * @return boolean TRUE if string is HTML.
			 */
			public function is_html($string)
			{
				if(!$string || !is_string($string))
					return FALSE; // Not possible.

				return strpos($string, '<') !== FALSE && preg_match('/\<[^<>]+\>/', $string);
			}

			/**
			 * Convert plain text to HTML markup.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string Input string to convert.
			 *
			 * @return string Plain text converted to HTML markup.
			 */
			public function to_html($string)
			{
				if(!($string = trim((string)$string)))
					return $string; // Not possible.

				return nl2br(make_clickable(esc_html($string)));
			}

			/**
			 * A very simple markdown parser.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $string Input string to convert.
			 *
			 * @return string Markdown converted to HTML markup.
			 */
			public function s_md_to_html($string)
			{
				if(!($string = trim((string)$string)))
					return $string; // Not possible.

				if($this->is_html($string))
					return $string; // Not applicable.

				$html = $this->to_html($string);
				$html = preg_replace('/`{3,}([^`]+?)`+/', '<pre><code>'.'${1}'.'</code></pre>', $html);
				$html = preg_replace('/`+([^`]+?)`+/', '<code>'.'${1}'.'</code>', $html);

				return $html;
			}
		}
	}
}