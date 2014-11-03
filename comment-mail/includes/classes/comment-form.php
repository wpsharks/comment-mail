<?php
/**
 * Comment Form
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_form'))
	{
		/**
		 * Comment Form
		 *
		 * @since 14xxxx First documented version.
		 */
		class comment_form extends abs_base
		{
			/**
			 * @var integer Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $post_id;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $post_id Post ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($post_id)
			{
				parent::__construct();

				$this->post_id = (integer)$post_id;

				$this->maybe_display_sub_ops();
			}

			/**
			 * Display subscription options.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function maybe_display_sub_ops()
			{
				if(!$this->post_id)
					return; // Nothing to do.

				if(!$this->plugins->options['enable'])
					return; // Disabled currently.

				if(!$this->plugins->options['new_subs_enable'])
					return; // Disabled currently.

				// @TODO
				// $_POST[__NAMESPACE__.'_sub_type']
				// $_POST[__NAMESPACE__.'_sub_deliver']
				// templates/site/sub-ops.php
			}
		}
	}
}