<?php
/**
 * Comment Shortlink Redirect
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_shortlink_redirect'))
	{
		/**
		 * Comment Shortlink Redirect
		 *
		 * @since 14xxxx First documented version.
		 */
		class comment_shortlink_redirect extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_redirect();
			}

			/**
			 * Handle redirect.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_redirect()
			{
				if(empty($_REQUEST['c']) || is_admin())
					return; // Nothing to do.

				if(!($comment_id = (integer)$_REQUEST['c']))
					return; // Not applicable.

				if(!($comment_link = get_comment_link($comment_id)))
					return; // Not possible.

				wp_redirect($comment_link, 301).exit();
			}
		}
	}
}