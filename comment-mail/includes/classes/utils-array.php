<?php
/**
 * Array Utilities
 *
 * @package utils_array
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_array'))
	{
		/**
		 * Array Utilities
		 *
		 * @package utils_array
		 * @since 14xxxx First documented version.
		 */
		class utils_array // Array utilities.
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
			 * Forces an array to contain only unique values (deeply).
			 *
			 * @param array $array An input array.
			 *
			 * @return array The output array, containing only unique array values deeply.
			 *
			 * @note Resource pointers CANNOT be serialized, and will therefore be lost (i.e. corrupted) when/if they're nested deeply inside the input array.
			 *    Resources NOT nested deeply, DO remain intact (this is fine). Only resource pointers nested deeply are lost via `serialize()`.
			 */
			public function unique_deep(array $array)
			{
				foreach($array as &$_value)
				{
					if(!is_resource($_value))
						$_value = serialize($_value);
				}
				unset($_value); // Housekeeping.

				$array = array_unique($array);

				foreach($array as &$_value)
				{
					if(!is_resource($_value))
						$_value = unserialize($_value);
				}
				return $array; // Unique (deeply).
			}
		}
	}
}