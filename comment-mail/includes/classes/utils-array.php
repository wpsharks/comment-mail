<?php
/**
 * Array Utilities
 *
 * @since 141111 First documented version.
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
		 * @since 141111 First documented version.
		 */
		class utils_array extends abs_base
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
			 * Unique values deeply (preserving keys).
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $array An input array.
			 *
			 * @return array The output array, containing only unique array values deeply.
			 *
			 * @note Resource pointers CANNOT be serialized, and will therefore be lost (i.e. corrupted)
			 *    when/if they're nested deeply inside the input array. Resources NOT nested deeply, DO remain intact (this is fine).
			 *    Only resource pointers nested deeply are lost via `serialize()`.
			 *
			 * @see \array_unique()
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

			/**
			 * Prepend a key/value pair onto an array.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array          $array An input array; by reference.
			 * @param string|integer New array key; string or integer.
			 * @param mixed          $value New array value.
			 *
			 * @return integer Like {@link \array_unshift()}, returns the new number of elements.
			 *
			 * @throws \exception If the input `$key` is not an integer|string.
			 *
			 * @see \array_unshift()
			 */
			public function unshift_assoc(array &$array, $key, $value)
			{
				if(!is_integer($key) && !is_string($key))
					throw new \exception(__('Invalid `$key` arg.', 'comment-mail'));

				unset($array[$key]); // Unset first.

				$array       = array_reverse($array, TRUE);
				$array[$key] = $value; // Add to the end here.
				$array       = array_reverse($array, TRUE);

				return count($array); // New number of elements.
			}

			/**
			 * Shuffles an array (preserving keys).
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $array An input array; by reference.
			 *
			 * @return boolean Like {@link \shuffle()}, this returns `TRUE`.
			 *
			 * @see \shuffle()
			 */
			public function shuffle_assoc(array &$array)
			{
				if(!$array) // Nothing to do.
					return TRUE;

				$_shuffled = array();
				$_keys     = array_keys($array);
				shuffle($_keys); // Keys only.

				foreach($_keys as $_key)
					$_shuffled[$_key] = $array[$_key];

				$array = $_shuffled; // Overwrite existing.
				unset($_shuffled, $_keys, $_key); // Housekeeping.

				return TRUE; // Always returns `TRUE`.
			}

			/**
			 * Shuffles an array deeply (preserving keys).
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $array An input array; by reference.
			 *
			 * @return boolean Like {@link \shuffle()}, this returns `TRUE`.
			 *
			 * @see \shuffle()
			 * @see shuffle_assoc()
			 */
			public function shuffle_assoc_deep(array &$array)
			{
				if(!$array) // Nothing to do.
					return TRUE;

				$this->shuffle_assoc($array);

				foreach($array as $_key => &$_value)
					if(is_array($_value)) // Recursion.
						$this->shuffle_assoc_deep($_value);
				unset($_key, $_value); // Housekeeping.

				return TRUE; // Always returns `TRUE`.
			}

			/**
			 * Removes `NULL` key/values.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $array An input array to work from.
			 *
			 * @return array Keys preserved; `NULL` key/values removed though.
			 */
			public function remove_nulls(array $array)
			{
				if(!$array) // Nothing to do.
					return $array;

				foreach($array as $_key => &$_value)
					if(is_null($_value)) unset($array[$_key]);
				unset($_key, $_value); // Housekeeping.

				return $array; // No `NULL` values.
			}

			/**
			 * Removes `NULL` key/values deeply.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $array An input array to work from.
			 *
			 * @return array Keys preserved; `NULL` key/values removed though.
			 */
			public function remove_nulls_deep(array $array)
			{
				if(!$array) // Nothing to do.
					return $array;

				$array = $this->remove_nulls($array);

				foreach($array as $_key => &$_value)
					if(is_array($_value)) // Recursion.
						$_value = $this->remove_nulls_deep($_value);
				unset($_key, $_value); // Housekeeping.

				return $array; // `NULL` values removed deeply.
			}
		}
	}
}
