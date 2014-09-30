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
			 * @return array Array of all StCR data for the post ID or NULL if no data.
			 *
			 *    If an array is returned, it will be a multidimentional array with each index containing an array of subscription data with the following keys:
			 *
			 *       `string` `$email` The email address of the subscriber
			 *       `string` `$date` The date the subscription was created in local WordPress time with format YYYY-MM-DD HH:MM:SS
			 *       `string` `$status` The status of the subscription; exactly one of the following: Y|R|YC|RC|C|-C
			 *
			 */
			public function get_data_for($post_id)
			{
				$data = array(); // Initialize.

				if(!($post_id = (integer)$post_id))
					return $data; // Not possible.

				global $wpdb; // Global database object reference.
				/** @var \wpdb $wpdb This line for IDEs that need a reference. */

				$_wp_postmeta_stcr_data = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key LIKE '%%_stcr@_%%'", $post_id), OBJECT);

				if(!$_wp_postmeta_stcr_data || count($_wp_postmeta_stcr_data) < 1)
					return null; // No results.

				foreach ($_wp_postmeta_stcr_data as $_row) {
					$_email = str_replace('_stcr@_', '', $_row->meta_key); // Original format: _stcr@_user@example.com

					if(empty($_email) || !is_email($_email))
						continue; // Invalid data.

					$_meta_value = explode('|', $_row->meta_value); // Original format: 2013-03-11 01:31:01|R
					$_date = $_meta_value[0]; // Local WordPress time

					if(strtotime($_date) === FALSE)
						continue; // Invalid data.

					$_status = $_meta_value[1]; // Y|R|YC|RC|C|-C
					if(!in_array($_status, array('Y', 'R', 'YC', 'RC', 'C', '-C')))
						continue; // Invalid data.

					$data[] = array('email' => $_email, 'date' => $_date, 'status' => $_status);
				}

				return (empty($data) ? null : $data);
			}
		}
	}
}