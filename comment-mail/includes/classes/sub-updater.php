<?php
/**
 * Sub Updater
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_updater'))
	{
		/**
		 * Sub Updater
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_updater extends sub_inserter
		{
			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 *
			 * @param array $args Any additional behavioral args.
			 */
			public function __construct(array $request_args, array $args = array())
			{
				if(!isset($request_args['ID']))
					$request_args['ID'] = -1;

				parent::__construct($request_args, $args);
			}
		}
	}
}