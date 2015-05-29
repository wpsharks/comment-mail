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
			protected $sub; // Subscr. data.

			/**
			 * @var string Last known IP.
			 *
			 * @since 141111 First documented version.
			 */
			protected $last_ip;

			/**
			 * @var string Last known region.
			 *
			 * @since 141111 First documented version.
			 */
			protected $last_region;

			/**
			 * @var string Last known country.
			 *
			 * @since 141111 First documented version.
			 */
			protected $last_country;

			/**
			 * @var integer Overwritten by subscription ID.
			 *
			 * @since 141111 First documented version.
			 */
			protected $oby_sub_id;

			/**
			 * @var integer Sub ID that did an overwrite; did a replace?
			 *
			 * @since 141111 First documented version.
			 */
			protected $oby_sub_id_did_replace;

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
			 * @var string Event taking place.
			 *
			 * @since 141111 First documented version.
			 */
			protected $event;

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
					'last_ip'                => '',
					'last_region'            => '',
					'last_country'           => '',

					'oby_sub_id'             => 0,
					'oby_sub_id_did_replace' => FALSE,
					'purging'                => FALSE,
					'cleaning'               => FALSE,

					'process_events'         => TRUE,

					'user_initiated'         => FALSE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				$this->last_ip      = trim((string)$args['last_ip']);
				$this->last_region  = trim((string)$args['last_region']);
				$this->last_country = trim((string)$args['last_country']);

				$this->oby_sub_id             = (integer)$args['oby_sub_id'];
				$this->oby_sub_id_did_replace = (boolean)$args['oby_sub_id_did_replace'];
				$this->purging                = (boolean)$args['purging'];
				$this->cleaning               = (boolean)$args['cleaning'];

				$this->process_events = (boolean)$args['process_events'];

				$this->user_initiated = (boolean)$args['user_initiated'];
				$this->user_initiated = $this->plugin->utils_sub->check_user_initiated_by_admin(
					$this->sub ? $this->sub->email : '', $this->user_initiated
				);
				# Auto-fill last IP, region, country if it's the current user.

				if($this->user_initiated && !$this->last_ip)
					$this->last_ip = $this->plugin->utils_ip->current();

				$this->last_region = ''; $this->last_country = '';

				# Auto-resolve conflicts between deletion event types.

				if($this->oby_sub_id)
					$this->purging = $this->cleaning = FALSE;

				if($this->purging)
					$this->cleaning = FALSE;

				if($this->cleaning)
					$this->purging = FALSE;

				if($this->purging || $this->cleaning)
				{
					$this->oby_sub_id             = 0;
					$this->oby_sub_id_did_replace = FALSE;
				}
				# Define the event type based on args.

				if($this->oby_sub_id)
					$this->event = 'overwritten';

				else if($this->purging)
					$this->event = 'purged';

				else if($this->cleaning)
					$this->event = 'cleaned';

				else $this->event = 'deleted';

				# Perform deletion event type.

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
				$this->deleted = (boolean)$this->deleted; // Convert to boolean now.

				$this->sub->status = 'deleted'; // Obj. properties.
				if($this->last_ip) $this->sub->last_ip = $this->last_ip;
				if($this->last_region) $this->sub->last_region = $this->last_region;
				if($this->last_country) $this->sub->last_country = $this->last_country;
				$this->sub->last_update_time = time(); // Updating now by deleting.

				$this->plugin->utils_sub->nullify_cache(array($this->sub->ID, $this->sub->key));

				if($this->process_events) // Processing events?
					if($this->deleted || ($this->event === 'overwritten' && $this->oby_sub_id && $this->oby_sub_id_did_replace))
					{
						new sub_event_log_inserter(array_merge((array)$this->sub, array(
							'event'          => $this->event,
							'oby_sub_id'     => $this->oby_sub_id,
							'user_initiated' => $this->user_initiated,
						)), $sub_before); // Log event data.
					}
			}
		}
	}
}
