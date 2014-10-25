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
			 * Core Utilities
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
			public function isset_or(&$var, $or = NULL, $type = '')
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
			public function not_empty_or(&$var, $or = NULL, $type = '')
			{
				if(!empty($var))
				{
					if($type) // Set type?
						settype($var, $type);
					return $var;
				}
				return $or; // Do not cast `$or`.
			}
		}
	}
}