<?php
/**
 * File System Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_fs'))
	{
		/**
		 * File System Utilities
		 *
		 * @since 141111 First documented version.
		 */
		class utils_fs extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Adds tmp suffix to a directory|file `/path`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $path Directory|file `/path`.
			 *
			 * @return string Suffixed directory|file `/path`.
			 */
			public function tmp_suffix($path)
			{
				$path = (string)$path; // Force string value.
				$path = rtrim($path, DIRECTORY_SEPARATOR.'\\/');

				return $path.'-'.str_replace('.', '', uniqid('', TRUE)).'-tmp';
			}

			/**
			 * Normalizes `/path` separators.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed   $path Directory|file `/path`.
			 *
			 * @param boolean $allow_trailing_slash Defaults to FALSE.
			 *    If TRUE; and `$path` contains a trailing slash; we'll leave it there.
			 *
			 * @return string Normalized directory|file `/path`.
			 */
			public function n_seps($path, $allow_trailing_slash = FALSE)
			{
				$path = (string)$path; // Force string value.
				if(!isset($path[0])) return ''; // Empty.

				if(strpos($path, '://' !== FALSE))  // A stream wrapper?
				{
					$stream_wrapper_regex = '/^(?P<stream_wrapper>[a-zA-Z0-9]+)\:\/\//';
					if(preg_match($stream_wrapper_regex, $path, $stream_wrapper))
						$path = preg_replace($stream_wrapper_regex, '', $path);
				}
				if(strpos($path, ':' !== FALSE))  // A Windows® drive letter?
				{
					$drive_letter_regex = '/^(?P<drive_letter>[a-zA-Z])\:[\/\\\\]/';
					if(preg_match($drive_letter_regex, $path)) // It has a Windows® drive letter?
						$path = preg_replace_callback($drive_letter_regex, create_function('$m', 'return strtoupper($m[0]);'), $path);
				}
				$path = preg_replace('/\/+/', '/', str_replace(array(DIRECTORY_SEPARATOR, '\\', '/'), '/', $path));
				$path = ($allow_trailing_slash) ? $path : rtrim($path, '/'); // Strip trailing slashes.

				if(!empty($stream_wrapper[0])) // Stream wrapper (force lowercase).
					$path = strtolower($stream_wrapper[0]).$path;

				return $path; // Normalized now.
			}

			/**
			 * Checks an uploaded file `/path`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string  $path A file `/path` to check.
			 *    If it's an uploaded file, use the `tmp_name`.
			 *
			 * @param boolean $require_uploaded_file Defaults to a `FALSE` value.
			 *
			 * @throws \exception If a security flag is triggered for any reason.
			 */
			public function check_path_security($path, $require_uploaded_file = FALSE)
			{
				$path = (string)$path; // Force string value.
				if(!isset($path[0])) return; // Empty.

				if($require_uploaded_file && (empty($_FILES) || !is_uploaded_file($path)))
					throw new \exception(sprintf(__('Security flag. Not an uploaded file: `%1$s`.', $this->plugin->text_domain), $path));

				$path = $this->n_seps($path); // Normalize separators for remaining checks.

				if(strpos($path, '~') !== FALSE // A backup file?
				   || strpos($path, './') !== FALSE || strpos($path, '..') !== FALSE
				   || strpos($path, '/.') !== FALSE || stripos(basename($path), 'config') !== FALSE
				) throw new \exception(sprintf(__('Security flag. Dangerous file path: `%1$s`.', $this->plugin->text_domain), $path));
			}

			/**
			 * Abbreviated byte notation for file sizes.
			 *
			 * @param float   $bytes File size in bytes. A (float) value.
			 *    We need this converted to a (float), so it's possible to deal with numbers beyond that of an integer.
			 *
			 * @param integer $precision Number of decimals to use.
			 *
			 * @return string Byte notation.
			 */
			public function bytes_abbr($bytes, $precision = 2)
			{
				$bytes     = (float)$bytes;
				$precision = (integer)$precision;

				$precision = $precision >= 0 ? $precision : 2;
				$units     = array('bytes', 'kbs', 'MB', 'GB', 'TB');

				$bytes = $bytes > 0 ? $bytes : 0;
				$power = floor(($bytes ? log($bytes) : 0) / log(1024));

				$abbr_bytes = round($bytes / pow(1024, $power), $precision);
				$abbr       = $units[min($power, count($units) - 1)];

				if($abbr_bytes === (float)1 && $abbr === 'bytes')
					$abbr = 'byte'; // Quick fix here.

				else if($abbr_bytes === (float)1 && $abbr === 'kbs')
					$abbr = 'kb'; // Quick fix here.

				return $abbr_bytes.' '.$abbr;
			}

			/**
			 * Converts an abbreviated byte notation into bytes.
			 *
			 * @param string $string A string value in byte notation.
			 *
			 * @return float A float indicating the number of bytes.
			 */
			public function abbr_bytes($string)
			{
				$string = trim((string)$string);

				$notation = '/^(?P<value>[0-9\.]+)\s*(?P<modifier>bytes|byte|kbs|kb|k|mb|m|gb|g|tb|t)$/i';

				if(!preg_match($notation, $string, $_op))
					return (float)0;

				$value    = (float)$_op['value'];
				$modifier = strtolower($_op['modifier']);
				unset($_op); // Housekeeping.

				switch($modifier) // Fall through based on modifier.
				{
					case 't': // Multiplied four times.
					case 'tb':
						$value *= 1024;
					case 'g': // Multiplied three times.
					case 'gb':
						$value *= 1024;
					case 'm': // Multiple two times.
					case 'mb':
						$value *= 1024;
					case 'k': // One time only.
					case 'kb':
					case 'kbs':
						$value *= 1024;
				}
				return (float)$value;
			}

			/**
			 * Inline SVG plugin icon.
			 *
			 * @return string SVG icon for inline markup.
			 */
			public function inline_icon_svg()
			{
				if(!is_null($icon = &$this->static_key(__FUNCTION__)))
					return $icon; // Already cached this.

				return ($icon = file_get_contents(dirname(dirname(dirname(__FILE__))).'/client-s/images/inline-icon.svg'));
			}
		}
	}
}