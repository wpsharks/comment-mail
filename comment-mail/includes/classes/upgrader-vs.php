<?php
/**
 * Upgrader (Version-Specific)
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\upgrader_vs'))
	{
		/**
		 * Upgrader (Version-Specific)
		 *
		 * @since 141111 First documented version.
		 */
		class upgrader_vs extends abs_base
		{
			/**
			 * @var string Previous version.
			 *
			 * @since 141111 First documented version.
			 */
			protected $prev_version;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $prev_version Version they are upgrading from.
			 */
			public function __construct($prev_version)
			{
				parent::__construct();

				$this->prev_version = (string)$prev_version;

				$this->run_handlers(); // Run upgrade(s).
			}

			/**
			 * Runs upgrade handlers in the proper order.
			 *
			 * @since 141111 First documented version.
			 */
			protected function run_handlers()
			{
				$this->from_lt_v141111();
			}

			/**
			 * Upgrading from a version prior to our rewrite.
			 *
			 * @since 141111 First documented version.
			 */
			protected function from_lt_v141111()
			{
			}
		}
	}
}