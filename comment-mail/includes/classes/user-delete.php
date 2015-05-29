<?php
/**
 * User Deletion Handler
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\user_delete'))
	{
		/**
		 * User Deletion Handler
		 *
		 * @since 141111 First documented version.
		 */
		class user_delete extends abs_base
		{
			/**
			 * @var integer User ID.
			 *
			 * @since 141111 First documented version.
			 */
			protected $user_id;

			/**
			 * @var integer Blog ID.
			 *
			 * @since 141111 First documented version.
			 */
			protected $blog_id;

			/**
			 * @var boolean Switched blog?
			 *
			 * @since 141111 First documented version.
			 */
			protected $switched_blog;

			/**
			 * Class constructor.
			 *
			 * @param integer|string $user_id User ID.
			 * @param integer|string $blog_id Blog ID. Defaults to `0` (current blog).
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct($user_id, $blog_id = 0)
			{
				parent::__construct();

				$this->switched_blog = FALSE;
				$this->user_id       = (integer)$user_id;
				$this->blog_id       = (integer)$blog_id;

				$this->maybe_purge_subs();
			}

			/**
			 * Purges subscriptions.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_purge_subs()
			{
				if(!$this->user_id)
					return; // Nothing to do.

				if($this->blog_id && $this->blog_id !== $GLOBALS['blog_id'])
				{
					switch_to_blog($this->blog_id);
					$this->switched_blog = TRUE;
				}
				new sub_purger(0, 0, $this->user_id);

				if($this->blog_id && $this->switched_blog)
				{
					restore_current_blog();
					$this->switched_blog = FALSE;
				}
			}
		}
	}
}
