<?php
/**
 * Event Log Inserter
 *
 * @package event_log_inserter
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\event_log_inserter'))
	{
		/**
		 * Event Log Inserter
		 *
		 * @package event_log_inserter
		 * @since 14xxxx First documented version.
		 */
		class event_log_inserter // Event log inserter.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var array Log entry data.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $entry; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $entry Log entry data.
			 *
			 * @throws \exception If `$entry` is missing required keys.
			 */
			public function __construct(array $entry)
			{
				$this->plugin = plugin();

				$defaults    = array(
					'sub_id'     => 0,
					'user_id'    => 0,
					'post_id'    => 0,
					'comment_id' => 0,

					'fname'      => '',
					'lname'      => '',
					'email'      => '',
					'ip'         => '',

					'event'      => '',

					'time'       => time(),
				);
				$this->entry = array_merge($defaults, $entry);
				$this->entry = array_intersect_key($this->entry, $defaults);

				$this->maybe_insert(); // Record event; if applicable.
			}

			/**
			 * Record event; if applicable.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert()
			{
				if(!$this->entry['sub_id'])
					return; // Not applicable.

				if(!$this->entry['post_id'])
					return; // Not applicable.

				if(!$this->entry['email'])
					return; // Not applicable.

				if(!in_array($this->entry['event'], array('subscribed', 'confirmed', 'unsubscribed'), TRUE))
					return; // Not applicable.

				if(!$this->entry['time'])
					return; // Not applicable.

				$this->plugin->wpdb->insert($this->plugin->db_prefix().'event_log', $this->entry);
			}
		}
	}
}