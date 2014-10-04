<?php
/**
 * Array Utilities
 *
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
		 * @since 14xxxx First documented version.
		 */
		class utils_array extends abstract_base
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
			 * Shuffles an array (preserving keys).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $array An input array to shuffle.
			 *
			 * @return array A shuffled output array, keys preserved.
			 */
			public function shuffle(array $array)
			{
				if(!$array) // Nothing to do.
					return $array;

				$_shuffled = array();
				$_keys     = array_keys($array);
				shuffle($_keys); // Keys only.

				foreach($_keys as $_key)
					$_shuffled[$_key] = $array[$_key];

				$array = $_shuffled; // Overwrite existing.
				unset($_shuffled, $_keys, $_key); // Housekeeping.

				return $array; // Shuffled.
			}

			/**
			 * Shuffles an array (deeply; preserving keys).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $array An input array to shuffle.
			 *
			 * @return array A shuffled output array (deep), keys preserved.
			 */
			public function shuffle_deep(array $array)
			{
				if(!$array) // Nothing to do.
					return $array;

				$array = $this->shuffle($array);

				foreach($array as $_key => &$_value)
					if(is_array($_value)) // Recursion.
						$_value = $this->shuffle($_value);
				unset($_key, $_value); // Housekeeping.

				return $array; // Shuffled deep.
			}

			/**
			 * Nnique values (deeply; preserving keys).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $array An input array.
			 *
			 * @return array The output array, containing only unique array values deeply.
			 *
			 * @note Resource pointers CANNOT be serialized, and will therefore be lost (i.e. corrupted)
			 *    when/if they're nested deeply inside the input array. Resources NOT nested deeply, DO remain intact (this is fine).
			 *    Only resource pointers nested deeply are lost via `serialize()`.
			 */
			public function unique_deep(array $array)
			{
				if(!$array) // Nothing to do.
					return $array;

				foreach($array as $_key => &$_value)
					if(!is_resource($_value))
						$_value = serialize($_value);
				unset($_key, $_value); // Housekeeping.

				$array = array_unique($array);

				foreach($array as $_key => &$_value)
					if(!is_resource($_value))
						$_value = unserialize($_value);
				unset($_key, $_value); // Housekeeping.

				return $array; // Unique deep.
			}
		}
	}
}