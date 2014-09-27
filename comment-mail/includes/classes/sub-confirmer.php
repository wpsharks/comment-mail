<?php
/**
 * Sub Confirmer
 *
 * @package sub_confirmer
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_confirmer'))
	{
		/**
		 * Sub Confirmer
		 *
		 * @package sub_confirmer
		 * @since 14xxxx First documented version.
		 */
		class sub_confirmer // Sub confirmer.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var \stdClass|null Subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @param integer|string $sub_id Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($sub_id)
			{
				$this->plugin = plugin();

				$sub_id = (integer)$sub_id;
				// @TODO
				// templates/emails/confirmation.php
			}
		}
	}
}