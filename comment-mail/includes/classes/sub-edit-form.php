<?php
/**
 * Sub. Edit Form
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_edit_form'))
	{
		/**
		 * Sub. Edit Form
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_edit_form extends abstract_base
		{
			/**
			 * @var \stdClass|null Subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $sub_id Subscriber ID.
			 */
			public function __construct($sub_id)
			{
				parent::__construct();

				$sub_id    = (integer)$sub_id; // Force integer.
				$this->sub = $this->plugin->utils_sub->get($sub_id);
			}

			/**
			 * Displays edit form.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_display_form()
			{
				if(!$this->sub)
					return; // Not possible.

				echo '<input;
			}
		}
	}
}