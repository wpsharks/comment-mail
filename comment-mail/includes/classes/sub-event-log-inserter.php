<?php
/**
 * Sub. Event Log Inserter
 *
 * @package sub_event_log_inserter
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_event_log_inserter'))
	{
		/**
		 * Sub. Event Log Inserter
		 *
		 * @package sub_event_log_inserter
		 * @since 14xxxx First documented version.
		 */
		class sub_event_log_inserter // Sub. event log inserter.
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

				$defaults = array(
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
				if(empty($entry['sub_id']) && !empty($entry['ID']))
					$entry['sub_id'] = $entry['ID'];

				if(empty($entry['ip']) && !empty($entry['last_ip']))
					$entry['ip'] = $entry['last_ip'];

				if(empty($entry['ip']) && !empty($entry['insertion_ip']))
					$entry['ip'] = $entry['insertion_ip'];

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

				if(!$this->entry['event'])
					return; // Not applicable.

				if(!$this->entry['time'])
					return; // Not applicable.

				if(!$this->plugin->utils_db->wp->insert($this->plugin->utils_db->prefix().'sub_event_log', $this->entry))
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));
			}
		}
	}
}