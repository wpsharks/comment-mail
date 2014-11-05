<?php
/**
 * Sub. Event Log Inserter
 *
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
		 * @since 14xxxx First documented version.
		 */
		class sub_event_log_inserter extends abs_base
		{
			/**
			 * @var array Log entry data.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $entry;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $entry Log entry data; w/ sub. now.
			 *
			 * @param array $before Log entry data; w/ sub. before.
			 *    Not applicable w/ insertions.
			 *
			 * @throws \exception If `$entry` is missing required keys.
			 */
			public function __construct(array $entry, array $before = array())
			{
				parent::__construct();

				$defaults = array(
					'sub_id'            => 0,
					'key'               => '',

					'oby_sub_id'        => 0,

					'user_id'           => 0,
					'post_id'           => 0,
					'comment_id'        => 0,
					'deliver'           => '',

					'fname'             => '',
					'lname'             => '',
					'email'             => '',
					'ip'                => '',

					'status'            => '',

					'event'             => '',
					'user_initiated'    => 0,

					'time'              => time(),

					/* ----------------- */

					'key_before'        => '',

					'user_id_before'    => 0,
					'post_id_before'    => 0,
					'comment_id_before' => 0,
					'deliver_before'    => '',

					'fname_before'      => '',
					'lname_before'      => '',
					'email_before'      => '',
					'ip_before'         => '',

					'status_before'     => '',
				);
				if(empty($entry['sub_id']) && !empty($entry['ID']))
					$entry['sub_id'] = $entry['ID'];

				if(empty($entry['ip']) && !empty($entry['last_ip']))
					$entry['ip'] = $entry['last_ip'];

				if(empty($entry['ip']) && !empty($entry['insertion_ip']))
					$entry['ip'] = $entry['insertion_ip'];

				foreach($before as $_key => $_value)
				{
					$before[$_key.'_before'] = $_value;
					unset($before[$_key]); // Unset.
				}
				unset($_key, $_value); // Housekeeping.

				if(empty($before['ip_before']) && !empty($before['last_ip_before']))
					$before['ip_before'] = $before['last_ip_before'];

				if(empty($before['ip_before']) && !empty($before['insertion_ip_before']))
					$before['ip_before'] = $before['insertion_ip_before'];

				$this->entry = array_merge($defaults, $entry, $before);
				$this->entry = array_intersect_key($this->entry, $defaults);
				$this->entry = $this->plugin->utils_db->typify_deep($this->entry);

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

				if(!$this->entry['deliver'])
					return; // Not applicable.

				if(!$this->entry['email'])
					return; // Not applicable.

				if(!$this->entry['status'])
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