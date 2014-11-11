<?php
/**
 * Sub Deleter
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_deleter'))
	{
		/**
		 * Sub Deleter
		 *
		 * @since 141111 First documented version.
		 */
		class sub_deleter extends abs_base
		{
			/**
			 * @var \stdClass|null Subscription.
			 *
			 * @since 141111 First documented version.
			 */
			protected $sub; // Subscription.

			/**
			 * @var string Last IP.
			 *
			 * @since 141111 First documented version.
			 */
			protected $last_ip;

			/**
			 * @var integer Overwritten by subscription ID.
			 *
			 * @since 141111 First documented version.
			 */
			protected $oby_sub_id;

			/**
			 * @var boolean Purging?
			 *
			 * @since 141111 First documented version.
			 */
			protected $purging;

			/**
			 * @var boolean Cleaning?
			 *
			 * @since 141111 First documented version.
			 */
			protected $cleaning;

			/**
			 * @var boolean Process events?
			 *
			 * @since 141111 First documented version.
			 */
			protected $process_events;

			/**
			 * @var boolean User initiated?
			 *
			 * @since 141111 First documented version.
			 */
			protected $user_initiated;

			/**
			 * @var string Event type.
			 *
			 * @since 141111 First documented version.
			 */
			protected $event_type;

			/**
			 * @var boolean Deleted?
			 *
			 * @since 141111 First documented version.
			 */
			protected $deleted;

			/**
			 * Class constructor.
			 *
			 * @param integer $sub_id Subscription ID.
			 * @param array   $args Any additional behavior args.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct($sub_id, array $args = array())
			{
				parent::__construct();

				$sub_id    = (integer)$sub_id;
				$this->sub = $this->plugin->utils_sub->get($sub_id);

				$defaults_args = array(
					'last_ip'        => '',

					'oby_sub_id'     => 0,

					'purging'        => FALSE,
					'cleaning'       => FALSE,

					'process_events' => TRUE,

					'user_initiated' => FALSE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				$this->last_ip        = (string)$args['last_ip'];

				$this->oby_sub_id     = (integer)$args['oby_sub_id'];

				$this->purging        = (boolean)$args['purging'];
				$this->cleaning       = (boolean)$args['cleaning'];

				$this->process_events = (boolean)$args['process_events'];

				$this->user_initiated = (boolean)$args['user_initiated'];
				$this->user_initiated = $this->plugin->utils_sub->check_user_initiated_by_admin(
					$this->sub ? $this->sub->email : '', $this->user_initiated
				);
				if($this->user_initiated && !$this->last_ip)
					$this->last_ip = $this->plugin->utils_env->user_ip();

				if($this->oby_sub_id) // Resolve conflicts.
					$this->purging = $this->cleaning = FALSE;
				if($this->purging) $this->cleaning = FALSE;
				if($this->cleaning) $this->purging = FALSE;
				if($this->purging || $this->cleaning)
					$this->oby_sub_id = 0;

				if($this->oby_sub_id)
					$this->event_type = 'overwritten';

				else if($this->purging)
					$this->event_type = 'purged';

				else if($this->cleaning)
					$this->event_type = 'cleaned';

				else $this->event_type = 'deleted';

				$this->deleted = FALSE; // Initialize.

				$this->maybe_delete();
			}

			/**
			 * Public access to deleted property.
			 *
			 * @since 141111 First documented version.
			 */
			public function did_delete()
			{
				return $this->deleted;
			}

			/**
			 * Deletes subscription.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_delete()
			{
				if(!$this->sub)
					return; // Deleted already.

				if($this->sub->status === 'deleted')
					return; // Deleted already.

				$sub_before = (array)$this->sub; // For event logging.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `ID` = '".esc_sql($this->sub->ID)."'";

				if(($this->deleted = $this->plugin->utils_db->wp->query($sql)) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));

				if(!($this->deleted = (boolean)$this->deleted))
					return; // Nothing more to do here.

				$this->sub->status = 'deleted'; // Obj. properties.
				if($this->last_ip) $this->sub->last_ip = $this->last_ip;
				$this->sub->last_update_time = time();

				$this->plugin->utils_sub->nullify_cache(array($this->sub->ID, $this->sub->key));

				if($this->process_events) // Processing events?
				{
					new sub_event_log_inserter(array_merge((array)$this->sub, array(
						'event'          => $this->event_type,
						'oby_sub_id'     => $this->oby_sub_id,
						'user_initiated' => $this->user_initiated,
					)), $sub_before); // Log event data.
				}
			}
		}
	}
}