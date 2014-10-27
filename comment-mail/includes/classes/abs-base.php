<?php
/**
 * Base Abstraction
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\abs_base'))
	{
		/**
		 * Base Abstraction
		 *
		 * @since 14xxxx First documented version.
		 */
		abstract class abs_base
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin;

			/**
			 * @var array Instance cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $cache = array();

			/**
			 * @var array Global static cache ref.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $static = array();

			/**
			 * @var array Global static cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $___static = array();

			/**
			 * @var \stdClass Overload properties.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $___overload;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				$class = get_called_class();

				if(empty(static::$___static[$class]))
					static::$___static[$class] = array();
				$this->static = &static::$___static[$class];

				$this->___overload = new \stdClass;
			}

			/**
			 * Magic/overload `isset()` checker.
			 *
			 * @param string $property Property to check.
			 *
			 * @return boolean TRUE if `isset($this->___overload->{$property})`.
			 *
			 * @see http://php.net/manual/en/language.oop5.overloading.php
			 */
			public function __isset($property)
			{
				$property = (string)$property; // Force string.

				return is_object($this->___overload) && isset($this->___overload->{$property});
			}

			/**
			 * Magic/overload property getter.
			 *
			 * @param string $property Property to get.
			 *
			 * @return mixed The value of `$this->___overload->{$property}`.
			 *
			 * @throws \exception If the `$___overload` property is undefined.
			 *
			 * @see http://php.net/manual/en/language.oop5.overloading.php
			 */
			public function __get($property)
			{
				$property = (string)$property; // Force string.

				if(is_object($this->___overload) && property_exists($this->___overload, $property))
					return $this->___overload->{$property};

				throw new \exception(__('Undefined overload property.', $this->text_domain));
			}

			/**
			 * Magic/overload property setter.
			 *
			 * @param string $property Property to set.
			 * @param mixed  $value The value for this property.
			 *
			 * @throws \exception We do NOT allow magic/overload properties to be set.
			 *    Magic/overload properties in this class are read-only.
			 *
			 * @see http://php.net/manual/en/language.oop5.overloading.php
			 */
			public function __set($property, $value)
			{
				$property = (string)$property; // Force string.

				throw new \exception(__('Refused to set overload property.', $this->plugin->text_domain));
			}

			/**
			 * Magic `unset()` handler.
			 *
			 * @param string $property Property to unset.
			 *
			 * @throws \exception We do NOT allow magic/overload properties to be unset.
			 *    Magic/overload properties in this class are read-only.
			 *
			 * @see http://php.net/manual/en/language.oop5.overloading.php
			 */
			public function __unset($property)
			{
				$property = (string)$property; // Force string.

				throw new \exception(__('Refused to unset overload property.', $this->plugin->text_domain));
			}

			/*
			 * Protected Core Utilities
			 */

			/**
			 * Utility Method; `isset()` or what?
			 *
			 * @param mixed  $var A variable; by reference.
			 * @param mixed  $or If `$var` is not set, return this.
			 * @param string $type Force a particular type if `isset()`?
			 *
			 * @return mixed `$var` if `isset()`; else `$or`.
			 */
			protected function isset_or(&$var, $or = NULL, $type = '')
			{
				if(isset($var))
				{
					if($type) // Set type?
						settype($var, $type);
					return $var;
				}
				return $or; // Do not cast `$or`.
			}

			/**
			 * Utility Method; `!empty()` or what?
			 *
			 * @param mixed  $var A variable; by reference.
			 * @param mixed  $or If `$var` is empty, return this.
			 * @param string $type Force a particular type if `!empty()`?
			 *
			 * @return mixed `$var` if `!empty()`; else `$or`.
			 */
			protected function not_empty_or(&$var, $or = NULL, $type = '')
			{
				if(!empty($var))
				{
					if($type) // Set type?
						settype($var, $type);
					return $var;
				}
				return $or; // Do not cast `$or`.
			}

			/*
			 * Cache key generation helpers.
			 */

			/**
			 * Construct and acquire a cache key.
			 *
			 * @param string      $function `__FUNCTION__` is suggested here.
			 *    i.e. the calling function name in the calling class.
			 *
			 * @param mixed|array $args The arguments to the calling function.
			 *    Using `func_get_args()` to the caller might suffice in some cases.
			 *    That said, it's generally a good idea to customize this a bit.
			 *    This should include the cachable arguments only.
			 *
			 * @param string      $___prop For internal use only. This defaults to `cache`.
			 *    See also: {@link static_key()} where a value of `static` is used instead.
			 *
			 * @return mixed|null Returns the current value for the cache key.
			 *    Or, this returns `NULL` if the key is not set yet.
			 *
			 * @note This function returns by reference. The use of `&` is highly recommended when calling this utility.
			 *    See also: <http://php.net/manual/en/language.references.return.php>
			 */
			protected function &cache_key($function, $args = array(), $___prop = 'cache')
			{
				$function = (string)$function;
				$args     = (array)$args;

				if(!isset($this->{$___prop}[$function]))
					$this->{$___prop}[$function] = NULL;
				$cache_key = &$this->{$___prop}[$function];

				foreach($args as $_arg) // Use each arg as a key.
				{
					switch(gettype($_arg))
					{
						case 'integer':
							$_key = (integer)$_arg;
							break; // Break switch handler.

						case 'double':
						case 'float':
							$_key = (string)$_arg;
							break; // Break switch handler.

						case 'boolean':
							$_key = (integer)$_arg;
							break; // Break switch handler.

						case 'array':
						case 'object':
							$_key = sha1(serialize($_arg));
							break; // Break switch handler.

						case 'NULL':
						case 'resource':
						case 'unknown type':
						default: // Default case handler.
							$_key = (string)$_arg;
					}
					if(!isset($cache_key[$_key]))
						$cache_key[$_key] = NULL;
					$cache_key = &$cache_key[$_key];
				}
				return $cache_key;
			}

			/**
			 * Construct and acquire a static key.
			 *
			 * @param string      $function See {@link cache_key()}.
			 * @param mixed|array $args See {@link cache_key()}.
			 *
			 * @return mixed|null See {@link cache_key()}.
			 *
			 * @note This function returns by reference. The use of `&` is highly recommended when calling this utility.
			 *    See also: <http://php.net/manual/en/language.references.return.php>
			 */
			protected function &static_key($function, $args = array())
			{
				$key = &$this->cache_key($function, $args, 'static');

				return $key; // By reference.
			}
		}
	}
}