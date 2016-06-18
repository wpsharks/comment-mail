<?php
/**
 * Upgrader (Version-Specific)
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\upgrader_vs'))
	{
		/**
		 * Upgrader (Version-Specific)
		 *
		 * @since 141111 First documented version.
		 */
		class upgrader_vs extends abs_base
		{
			/**
			 * @var string Previous version.
			 *
			 * @since 141111 First documented version.
			 */
			protected $prev_version;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $prev_version Version they are upgrading from.
			 */
			public function __construct($prev_version)
			{
				parent::__construct();

				$this->prev_version = (string)$prev_version;

				$this->run_handlers(); // Run upgrade(s).
			}

			/**
			 * Runs upgrade handlers in the proper order.
			 *
			 * @since 141111 First documented version.
			 */
			protected function run_handlers()
			{
				if(version_compare($this->prev_version, '141115', '<'))
					$this->from_lt_v141115();
			}

			/**
			 * Upgrading from a version prior to our rewrite.
			 *
			 * @since 141111 First documented version.
			 */
			protected function from_lt_v141115()
			{
				$sql1 = "ALTER TABLE `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				        " ADD `insertion_region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region code at time of insertion.',".
				        " ADD `insertion_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country code at time of insertion.',".

				        " ADD `last_region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known geographic region code.',".
				        " ADD `last_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known geographic country code.'";

				$sql2 = "ALTER TABLE `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

				        " ADD `region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; at the time of the event.',".
				        " ADD `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; at the time of the event.',".

				        " ADD `region_before` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; before the event, if applicable.',".
				        " ADD `country_before` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; before the event, if applicable.'";

				$sql3 = "ALTER TABLE `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".

				        " ADD `region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; at the time of the event.',".
				        " ADD `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; at the time of the event.'";

				if($this->plugin->utils_db->wp->query($sql1) === FALSE
				   || $this->plugin->utils_db->wp->query($sql2) === FALSE
				   || $this->plugin->utils_db->wp->query($sql3) === FALSE
				) throw new \exception(__('Query failure.', 'comment-mail'));
			}
		}
	}
}
