<?php
/**
 * StCR Importer
 *
 * @package import_stcr
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\import_stcr'))
	{
		/**
		 * StCR Importer
		 *
		 * @package import_stcr
		 * @since 14xxxx First documented version.
		 */
		class import_stcr // Environment utilities.
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
			 * Collect all StCR data for a given post ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $post_id Post ID.
			 *
			 * @return array Array of all StCR data for the post ID.
			 */
			public function get_data_for($post_id)
			{
				$data = array(); // Initialize.

				if(!($post_id = (integer)$post_id))
					return $data; // Not possible.

				// @TODO: Collect all STRC data for the `$post_id` and return an associative (possibly multidimensional array).
				// The format of this array is entirely up to the author. Whatever seems to make the most sense will be fine with me.

				return $data;
			}
		}
	}
}