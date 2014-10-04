<?php
/**
 * Post Deletion Handler
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\post_delete'))
	{
		/**
		 * Post Deletion Handler
		 *
		 * @since 14xxxx First documented version.
		 */
		class post_delete extends abstract_base
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
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $post_id Post ID.
			 */
			public function __construct($post_id)
			{
				parent::__construct();

				$this->post_id = (integer)$post_id;

				$this->maybe_delete();
			}

			/**
			 * Delete subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_delete()
			{
				if(!$this->post_id)
					return; // Nothing to do.

				new sub_deleter($this->post_id);
			}
		}
	}
}