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
			 * @var template Template instance.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $email_template; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $sub_id Comment ID.
			 */
			public function __construct($sub_id)
			{
				$this->plugin = plugin();

				$sub_id               = (integer)$sub_id;
				$this->sub            = $this->maybe_get_sub($sub_id);
				$this->email_template = new template('emails/confirmation.php');

				$this->maybe_send_confirmation_request();
			}

			/**
			 * Get subscriber object data.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $sub_id Subscriber ID.
			 *
			 * @return \stdClass|null Subscriber object on success.
			 */
			protected function maybe_get_sub($sub_id)
			{
				if(!($sub_id = (integer)$sub_id))
					return NULL; // Not possible.

				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `ID` = '".esc_sql($sub_id)."'".
				       " LIMIT 1";

				return $this->plugin->wpdb->get_row($sql);
			}

			/**
			 * Send confirmation request.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_send_confirmation_request()
			{
				if(!$this->sub)
					return; // Not possible.

				// @TODO
				// @TODO Add template variables for all emails.
				//    For instance, we need a universal header/footer and unsub link.
				//       Along with maybe a CAN-SPAM compliant blurp with the site contact info.
			}
		}
	}
}