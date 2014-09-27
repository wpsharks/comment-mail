<?php
/**
 * Key Generator
 *
 * @package keygen
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\keygen'))
	{
		/**
		 * Key Generator
		 *
		 * @package keygen
		 * @since 14xxxx First documented version.
		 */
		class keygen // Key generator.
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
			 * A unique, unguessable, case insensitive key.
			 *
			 * With a max length of 16 chars. Keys are generated based on `microtime()`,
			 *    and with base 36 component values concatenated with a random
			 *    number not to exceed `999999999`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string A unique, unguessable, case insensitive key.
			 */
			public function sub_key()
			{
				list($seconds, $microseconds) = explode('.', microtime(TRUE), 2);
				$seconds_base36      = base_convert($seconds, '10', '36');
				$microseconds_base36 = base_convert($microseconds, '10', '36');

				$mt_rand_base36 = base_convert(mt_rand(1, 999999999), '10', '36');
				$key            = $mt_rand_base36.$seconds_base36.$microseconds_base36;

				return substr($key, 0, 16);
			}
		}
	}
}