<?php
/**
 * Encryption Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_enc'))
	{
		/**
		 * Encryption Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_enc extends abstract_base
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
			 * A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note 32-bit systems usually have `PHP_INT_MAX` = `2147483647`.
			 *    We limit `mt_rand()` to a max of `999999999`.
			 *
			 * @note A max possible length of 20 chars assumes this function
			 *    will not be called after `Sat, 20 Nov 2286 17:46:39 GMT`.
			 *    At which point a UNIX timestamp will grow in size.
			 *
			 * @note Key always begins with a `k` to prevent PHP's `is_numeric()`
			 *    function from ever thinking it's a number in a different representation.
			 *    See: <http://php.net/manual/en/function.is-numeric.php> for further details.
			 *
			 * @return string A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).
			 */
			public function uunnci_key_20_max()
			{
				$microtime_19_max = number_format(microtime(TRUE), 9, '.', '');
				// e.g. `9999999999`.`999999999` (max decimals: `9`, max overall precision: `19`).
				// Assuming timestamp is never > 10 digits; i.e. before `Sat, 20 Nov 2286 17:46:39 GMT`.

				list($seconds_10_max, $microseconds_9_max) = explode('.', $microtime_19_max, 2);
				// e.g. `array(`9999999999`, `999999999`)`. Max total digits combined: `19`.

				$seconds_base36      = base_convert($seconds_10_max, '10', '36'); // e.g. max `9999999999`, to base 36.
				$microseconds_base36 = base_convert($microseconds_9_max, '10', '36'); // e.g. max `999999999`, to base 36.
				$mt_rand_base36      = base_convert(mt_rand(1, 999999999), '10', '36'); // e.g. max `999999999`, to base 36.
				$key                 = 'k'.$mt_rand_base36.$seconds_base36.$microseconds_base36; // e.g. `kgjdgxr4ldqpdrgjdgxr`.

				return $key; // Max possible value: `kgjdgxr4ldqpdrgjdgxr` (20 chars).
			}
		}
	}
}