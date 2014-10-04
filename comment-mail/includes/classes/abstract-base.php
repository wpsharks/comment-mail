<?php
/**
 * Base Abstraction
 *
 * @package abstract_base
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\abstract_base')) // @TODO
	{
		/**
		 * Base Abstraction
		 *
		 * @package abstract_base
		 * @since 14xxxx First documented version.
		 */
		abstract class abstract_base
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
			 * Read-only property access.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $property Property to get.
			 *
			 * @return mixed Value of the `$property`.
			 *
			 * @throws \exception If `$property` is undefined.
			 */
			public function get($property)
			{
				if(property_exists($this, $property))
					return $this->{$property};

				throw new \exception(__('Undefined property.', $this->plugin->text_domain));
			}
		}
	}
}