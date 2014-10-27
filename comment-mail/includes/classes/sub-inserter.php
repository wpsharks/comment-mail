<?php
/**
 * Sub Inserter
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_inserter'))
	{
		/**
		 * Sub Inserter
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_inserter extends abs_base
		{
			/**
			 * @var array Based on request args.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $data;

			/**
			 * @var boolean Auto-confirm?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $auto_confirm;

			/**
			 * @var boolean Process events?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $process_events;

			/**
			 * @var boolean Process confirmation?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $process_confirmation;

			/**
			 * @var boolean User initiated?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user_initiated;

			/**
			 * @var boolean User-initiated data key protections?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $ui_protected_data_keys_enable;

			/**
			 * @var \WP_User|null Initiating user.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $ui_protected_data_user;

			/**
			 * @var boolean Interpret `0` as current user?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $current_user_0;

			/**
			 * @var boolean Did we validate?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $validated;

			/**
			 * @var boolean An insert?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_insert;

			/**
			 * @var boolean Did we insert?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $inserted;

			/**
			 * @var integer Insertion ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $insert_id;

			/**
			 * @var boolean An update?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_update;

			/**
			 * @var \stdClass|null Subscription.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub; // On update only.

			/**
			 * @var string Status before update.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $status_before;

			/**
			 * @var boolean Did we update?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $updated;

			/**
			 * @var array An array of any duplicate key IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $duplicate_key_ids;

			/**
			 * @var array An array of any other duplicate IDs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $other_duplicate_ids;

			/**
			 * @var \WP_User|null Subscription.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user; // Subscriber.

			/**
			 * @var \WP_User|null Current user.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $current_user;

			/**
			 * @var boolean Subscriber is current user?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_current_user;

			/**
			 * @var array An array of any errors.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $errors;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 *
			 * @param array $args Any additional behavioral args.
			 *
			 *    Regarding user-initiated actions. The following arguments may apply.
			 *
			 *       • `user_initiated` This is a low-level argument by itself; mostly for event processing.
			 *             This is intended to identify user-initiated inserts/updates; but mostly for the sake of event processing.
			 *             However, this argument MUST be defined if you intend to use any other user-initiated arguments.
			 *
			 *       • `ui_protected_data_keys_enable` is a secondary argument to indicate that it's both a user-initiated event
			 *             and that we also WANT data key protections enabled. For instance, when a user is inserting/updating on their own.
			 *
			 *             • This flag exists so that it's still possible for us to systematically update something on behalf of a user,
			 *                without triggering the additional protected data key validations, should those be unwanted at times.
			 *
			 *       • `ui_protected_data_user` This is the only way to insert/update a specific user ID
			 *             whenever the `ui_protected_data_keys_enable` flag is `TRUE`; i.e. the `user_id` is a protected/nullified key.
			 *             Thus, the only way to push a user ID through is by passing it through args; using a trusted data source.
			 *
			 * @warning Generally speaking, user-initiated actions (i.e. `user_initiated`) should NOT be allowed to push request arguments
			 *    into this method that may update protected data keys such as: `user_id`, `insertion_ip`, `last_update_time`, and others.
			 *
			 *    To enable validation of protected data keys please pass `user_initiated` + `ui_protected_data_keys_enable` as `TRUE`.
			 *       This automatically removes/sanitizes/validates protected data keys before an insertion or update occurs.
			 *
			 *    Note: on insert/update with `user_initiated` + `ui_protected_data_keys_enable`, the only way to set the `user_id` is by
			 *       passing the `ui_protected_data_user` argument also — the initiating user; i.e. the user doing an insert/update.
			 *
			 *    Note: updates w/ `user_initiated` + `ui_protected_data_keys_enable` require a `key` to successfully complete the update.
			 *       i.e. An input `key` is validated against the `ID` in the input request args during update. It must match up!
			 *
			 *    The following keys can never be inserted/updated with `user_initiated` + `ui_protected_data_keys_enable`:
			 *
			 *       • `user_id` (requires `ui_protected_data_user` instead)
			 *
			 *       • `insertion_ip` (inserted/updated systematically)
			 *       • `last_ip` (inserted/updated systematically)
			 *
			 *       • `insertion_time` (inserted/updated systematically)
			 *       • `last_update_time` (inserted/updated systematically)
			 *
			 *    ~ On insert, `status` is always `unconfirmed` (by force).
			 *
			 *    ~ On update, `status` can by anything except `trashed`.
			 *       A user cannot trash themselves.
			 */
			public function __construct(array $request_args, array $args = array())
			{
				parent::__construct();

				$default_request_args = array(
					'ID'               => NULL,
					// A key is always auto-generated on insert.
					// A key can NEVER be updated by anyone — ever!
					// A key is required to update w/ `ui_protected_data_keys_enable`.
					'key'              => NULL,

					'user_id'          => NULL,
					'post_id'          => NULL,
					'comment_id'       => NULL,

					'deliver'          => NULL,

					'fname'            => NULL,
					'lname'            => NULL,
					'email'            => NULL,

					'insertion_ip'     => NULL,
					'last_ip'          => NULL,

					'status'           => NULL,

					'insertion_time'   => NULL,
					'last_update_time' => NULL,
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);
				$this->data           = $request_args; // A copy of the request args.

				$defaults_args = array(
					'auto_confirm'                  => NULL,

					'process_events'                => TRUE,
					'process_confirmation'          => FALSE,

					'user_initiated'                => FALSE,
					'ui_protected_data_keys_enable' => FALSE,
					'ui_protected_data_user'        => NULL,

					'current_user_0'                => NULL,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				if(isset($args['auto_confirm']))
					$this->auto_confirm = (boolean)$args['auto_confirm'];

				$this->process_events       = (boolean)$args['process_events'];
				$this->process_confirmation = (boolean)$args['process_confirmation'];

				$this->user_initiated                = (boolean)$args['user_initiated'];
				$this->ui_protected_data_keys_enable = // Applicable only w/ `user_initiated`.
					$this->user_initiated && (boolean)$args['ui_protected_data_keys_enable'];

				if($this->user_initiated && $this->ui_protected_data_keys_enable)
				{
					$this->data['user_id']          = NULL; // Nullify.
					$this->data['insertion_ip']     = NULL; // Nullify.
					$this->data['insertion_time']   = NULL; // Nullify.
					$this->data['last_update_time'] = NULL; // Nullify.
					// Additional sanitizing/validation occurs elsewhere.
				}
				if($this->user_initiated && $this->ui_protected_data_keys_enable)
					if($args['ui_protected_data_user'] instanceof \WP_User)
					{
						$this->user                   = $args['ui_protected_data_user'];
						$this->ui_protected_data_user = $args['ui_protected_data_user'];
					}
				if(isset($args['current_user_0']))
					$this->current_user_0 = (boolean)$args['current_user_0'];
				else $this->current_user_0 = $this->user_initiated;

				if(!$this->user_initiated) // Only if a user initiated this action.
					$this->current_user_0 = FALSE; // Force `FALSE` in this case.

				$this->validated = FALSE; // Initialize.

				$this->is_insert = !isset($this->data['ID']);
				$this->inserted  = FALSE; // Initialize.
				$this->insert_id = 0; // Initialize.

				$this->status_before = ''; // Initialize; see below.
				$this->is_update     = isset($this->data['ID']);
				$this->updated       = FALSE; // Initialize.
				if($this->is_update && $this->data['ID'])
				{
					$this->sub           = $this->plugin->utils_sub->get($this->data['ID']);
					$this->status_before = $this->sub->status; // For updates only.
				}
				$this->duplicate_key_ids   = array(); // Initialize.
				$this->other_duplicate_ids = array(); // Initialize.

				if(!isset($this->user) || !$this->user->ID)
					if($this->data['user_id'] === 0) // No user?
						$this->user = new \WP_User(0);

				if(!isset($this->user) || !$this->user->ID)
					if((integer)$this->data['user_id'] > 0) // Have a user ID?
						$this->user = new \WP_User((integer)$this->data['user_id']);

				if(!isset($this->user) || !$this->user->ID)
					if($this->is_update && $this->sub && $this->sub->user_id)
						$this->user = new \WP_User($this->sub->user_id);

				if(!isset($this->user) || !$this->user->ID)
					if((string)$this->data['email']) // A potentially new email?
						if(($_user = \WP_User::get_data_by('email', (string)$this->data['email'])))
							$this->user = new \WP_User($_user->ID);
				unset($_user); // Housekeeping.

				if(!isset($this->user) || !$this->user->ID)
					if($this->is_update && $this->sub && $this->sub->email)
						if(($_user = \WP_User::get_data_by('email', $this->sub->email)))
							$this->user = new \WP_User($_user->ID);
				unset($_user); // Housekeeping.

				$this->current_user    = wp_get_current_user();
				$this->is_current_user = FALSE; // Initialize.

				if(($this->user && ($this->user->ID || $this->current_user_0)
				    && $this->user->ID === $this->current_user->ID)
				) $this->is_current_user = TRUE; // Even if `0`.

				$this->errors = array(); // Initialize.

				$this->maybe_insert_update();
			}

			/**
			 * Subscription object reference.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return \stdClass|null Subscription.
			 */
			public function sub()
			{
				return $this->sub;
			}

			/**
			 * Did insert|update successfully?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean Did we insert|update?
			 */
			public function did_insert_update()
			{
				return $this->inserted || $this->updated;
			}

			/**
			 * Inserted successfully?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean Did we insert?
			 */
			public function did_insert()
			{
				return $this->inserted;
			}

			/**
			 * Insertion ID.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return integer Insertion ID; if applicable.
			 */
			public function insert_id()
			{
				return $this->insert_id;
			}

			/**
			 * Updated successfully?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean Did we update?
			 */
			public function did_update()
			{
				return $this->updated;
			}

			/**
			 * Do we have errors?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean `TRUE` if has errors.
			 */
			public function has_errors()
			{
				return !empty($this->errors);
			}

			/**
			 * Array of any errors.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of any/all errors.
			 */
			public function errors()
			{
				return $this->errors;
			}

			/**
			 * Array of any errors using HTML markup.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of any/all errors.
			 */
			public function errors_html()
			{
				return array_map(array($this->plugin->utils_string, 'markdown'), $this->errors);
			}

			/**
			 * Updates a subscription; or inserts a new one.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert_update()
			{
				$this->sanitize_validate_data();

				if($this->errors) // Have errors?
					return; // Do nothing.

				if($this->is_insert)
					$this->insert();

				else if($this->is_update)
					$this->update();
			}

			/**
			 * Inserts a subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If an insertion failure occurs.
			 */
			protected function insert()
			{
				$this->check_auto_confirm_before_insert_update();
				$this->collect_duplicate_key_ids_before_insert();

				$this->data['insertion_time']   = time(); // Force this.
				$this->data['last_update_time'] = time(); // Force this too.
				$table                          = $this->plugin->utils_db->prefix().'subs';
				$data_to_insert                 = $this->plugin->utils_array->remove_nulls($this->data);

				if($this->plugin->utils_db->wp->replace($table, $data_to_insert) === FALSE)
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));

				if(!($this->insert_id = (integer)$this->plugin->utils_db->wp->insert_id))
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));

				$this->inserted = TRUE; // Flag a `TRUE` now; i.e. the insertion was a success.

				$this->overwrite_duplicate_key_ids_after_insert(); // Before nullifying cache.

				$this->plugin->utils_sub->nullify_cache(array($this->insert_id, $this->data['key']));

				if(!($this->sub = $this->plugin->utils_sub->get($this->insert_id, TRUE)))
					throw new \exception(__('Sub after insert failure.', $this->plugin->text_domain));

				if($this->process_events) // Processing events? i.e. log this insertion?
				{
					new sub_event_log_inserter(array_merge((array)$this->sub, array(
						'event'          => 'inserted',
						'status_before'  => '', // New insertion.
						'user_initiated' => $this->user_initiated,
					))); // Log event data.
				}
				if($this->process_confirmation && $this->sub->status === 'unconfirmed')
					new sub_confirmer($this->sub->ID, array(
						'auto_confirm'   => $this->auto_confirm,
						'process_events' => $this->process_events,
					)); // With behavioral args.

				$this->overwrite_any_others_after_insert_update(); // Overwrites any others.
			}

			/**
			 * Updates a subscription.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If an update failure occurs.
			 */
			protected function update()
			{
				$this->check_auto_confirm_before_insert_update();
				$this->overwrite_duplicate_key_ids_before_update();

				$this->data['last_update_time'] = time(); // Force this.
				$table                          = $this->plugin->utils_db->prefix().'subs';
				$data_to_update                 = $this->plugin->utils_array->remove_nulls($this->data);

				if($this->plugin->utils_db->wp->update($table, $data_to_update, array('ID' => $this->sub->ID)) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));

				$this->updated = TRUE; // Flag as `TRUE` now; i.e. the update was a success.

				$this->plugin->utils_sub->nullify_cache(array($this->sub->ID, $this->sub->key));

				if(!($sub_after = $this->plugin->utils_sub->get($this->sub->ID, TRUE)))
					throw new \exception(__('Sub after update failure.', $this->plugin->text_domain));

				foreach($sub_after as $_property => $_value) // Updates object properties.
					$this->sub->{$_property} = $_value; // Update property references.
				$this->sub = $sub_after; // Now change object reference.
				unset($_property, $_value); // Housekeeping.

				if($this->process_events) // Processing events? i.e. log this update?
				{
					new sub_event_log_inserter(array_merge((array)$this->sub, array(
						'event'          => 'updated',
						'status_before'  => $this->status_before,
						'user_initiated' => $this->user_initiated,
					))); // Log event data.
				}
				if($this->process_confirmation && $this->sub->status === 'unconfirmed')
					new sub_confirmer($this->sub->ID, array(
						'auto_confirm'   => $this->auto_confirm,
						'process_events' => $this->process_events,
					)); // With behavioral args.

				$this->overwrite_any_others_after_insert_update(); // Overwrites any others.
			}

			/**
			 * Check if we can/should auto-confirm in this instance.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note Only if {@link $auto_confirm} is `NULL` (i.e. the default value).
			 */
			protected function check_auto_confirm_before_insert_update()
			{
				if(isset($this->auto_confirm))
					return; // Already set.

				if(!$this->process_confirmation)
					return; // Not applicable.

				if(($this->new_value_for('status')) !== 'unconfirmed')
					return; // Not applicable.

				$new_post_id = $this->new_value_for('post_id');
				$new_user_id = $this->new_value_for('user_id');
				$new_email   = $this->new_value_for('email');

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($new_post_id)."'".

				       ($new_user_id // Has a user ID?
					       ? " AND (`user_id` = '".esc_sql($new_user_id)."'".
					         "       OR `email` = '".esc_sql($new_email)."')"
					       : " AND `email` = '".esc_sql($new_email)."'").

				       " AND `status` = 'subscribed'".

				       " LIMIT 1"; // One to check.

				if((integer)$this->plugin->utils_db->wp->get_var($sql))
					$this->auto_confirm = TRUE; // Auto-confirm.
			}

			/**
			 * Collects duplicate key IDs before an insert occurs.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This also caches the underlying subs for deletion later.
			 *    It's import NOT to nullify the cache until these are dealt with
			 *    in the subsequent call to {@link overwrite_duplicate_key_ids_after_insert()}.
			 */
			protected function collect_duplicate_key_ids_before_insert()
			{
				$new_user_id    = $this->new_value_for('user_id');
				$new_post_id    = $this->new_value_for('post_id');
				$new_comment_id = $this->new_value_for('comment_id');
				$new_email      = $this->new_value_for('email');

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `user_id` = '".esc_sql($new_user_id)."'".
				       " AND `post_id` = '".esc_sql($new_post_id)."'".
				       " AND `comment_id` = '".esc_sql($new_comment_id)."'".
				       " AND `email` = '".esc_sql($new_email)."'";

				if(($this->duplicate_key_ids = $this->plugin->utils_db->wp->get_col($sql)))
					foreach($this->duplicate_key_ids as $_duplicate_key_id)
						$this->plugin->utils_sub->get($_duplicate_key_id); // Cache.
				unset($_duplicate_key_id); // Housekeeping.
			}

			/**
			 * Overwrites duplicate key IDs after an insert occurs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function overwrite_duplicate_key_ids_after_insert()
			{
				if(!$this->duplicate_key_ids)
					return; // Not necessary.

				$this->plugin->utils_sub->bulk_delete(
					$this->duplicate_key_ids, array(
						'oby_sub_id'     => $this->insert_id,
						'process_events' => $this->process_events,
					));
			}

			/**
			 * Overwrites duplicate key IDs before an update occurs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function overwrite_duplicate_key_ids_before_update()
			{
				if(!isset($this->data['user_id'])
				   && !isset($this->data['post_id'])
				   && !isset($this->data['comment_id'])
				   && !isset($this->data['email'])
				) return; // Not necessary.

				$new_user_id    = $this->new_value_for('user_id');
				$new_post_id    = $this->new_value_for('post_id');
				$new_comment_id = $this->new_value_for('comment_id');
				$new_email      = $this->new_value_for('email');

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `user_id` = '".esc_sql($new_user_id)."'".
				       " AND `post_id` = '".esc_sql($new_post_id)."'".
				       " AND `comment_id` = '".esc_sql($new_comment_id)."'".
				       " AND `email` = '".esc_sql($new_email)."'".

				       " AND `ID` != '".esc_sql($this->sub->ID)."'";

				if(($this->duplicate_key_ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->plugin->utils_sub->bulk_delete(
						$this->duplicate_key_ids, array(
							'oby_sub_id'     => $this->sub->ID,
							'process_events' => $this->process_events,
						));
			}

			/**
			 * Overwrites any other subscriptions after an insert|update occurs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function overwrite_any_others_after_insert_update()
			{
				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->sub->post_id)."'".
				       " AND (`comment_id` = '0' OR `comment_id` = '".esc_sql($this->sub->comment_id)."')".

				       ($this->sub->user_id // Has a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->sub->user_id)."'".
					         "       OR `email` = '".esc_sql($this->sub->email)."')"
					       : " AND `email` = '".esc_sql($this->sub->email)."'").

				       " AND `ID` != '".esc_sql($this->sub->ID)."'";

				if(($this->other_duplicate_ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->plugin->utils_sub->bulk_delete(
						$this->other_duplicate_ids, array(
							'oby_sub_id'     => $this->sub->ID,
							'process_events' => $this->process_events,
						));
			}

			/**
			 * New value for a key/property.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $key_prop The key/property to acquire.
			 *
			 * @return mixed The key/property value after an insert/update.
			 *
			 * @throws \exception If unable to acquire key/property value.
			 */
			protected function new_value_for($key_prop)
			{
				if(($key_prop = trim((string)$key_prop)))
				{
					if($this->is_insert && isset($this->data[$key_prop]))
						return $this->data[$key_prop];

					if($this->is_update && isset($this->data[$key_prop]))
						return $this->data[$key_prop];

					if($this->is_update && isset($this->sub->{$key_prop}))
						return $this->sub->{$key_prop};
				}
				throw new \exception(sprintf(__('Missing key/prop: `%1$s`.', $this->plugin->text_domain), $key_prop));
			}

			/**
			 * Sanitizes/validates request args; i.e. {@link $data}.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note Fill the {@link $errors} property on validation failure(s).
			 */
			protected function sanitize_validate_data()
			{
				foreach($this->data as $_key => &$_value)
				{
					switch($_key) // Validate each arg value.
					{
						case 'ID': // Primary key.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert)
								$_value = NULL; // Nullify.

							if($this->is_insert && isset($_value)) // Just to be thorough.
								$this->errors[$_key] = sprintf(__('Invalid; insertion w/ ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if(($this->is_update || isset($_value)) && ($_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_update && (!$this->sub || !$this->sub->ID || $_value !== $this->sub->ID))
								$this->errors[$_key] = sprintf(__('Invalid ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'key': // Unique key.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert) // Force a unique key.
								$_value = $this->plugin->utils_enc->uunnci_key_20_max();

							if(isset($_value) && (!$_value || strlen($_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid key: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !$_value || strlen($_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid key: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_update && $this->user_initiated && $this->ui_protected_data_keys_enable // Must have a matching key!
							        && (!isset($_value) || !$_value || strlen($_value) > 20 || !$this->sub || !$this->sub->key || $_value !== $this->sub->key)
							) $this->errors[$_key] = sprintf(__('Invalid key: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							if($this->is_update) $_value = NULL; // Nullify now; a key can never be changed by anyone!

							break; // Break switch handler.

						case 'user_id': // User ID.

							if($this->user_initiated)
								if($this->ui_protected_data_keys_enable)
									$_value = NULL; // Nullify protected key.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !$_value)
								$_value = 0; // Use a default value.

							if($this->user && $this->user->ID) // Match w/ `user`.
								$_value = $this->user->ID;

							if(isset($_value) && ($_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid user ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid user ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'post_id': // Post ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !$_value)
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid post ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || $_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid post ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'comment_id': // Comment ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !$_value)
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid comment ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid comment ID: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'deliver': // Delivery option.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = 'asap'; // Use a default value.

							if(isset($_value) && !in_array($_value, array('asap', 'hourly', 'daily', 'weekly'), TRUE))
								$this->errors[$_key] = sprintf(__('Invalid delivery option: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !in_array($_value, array('asap', 'hourly', 'daily', 'weekly'), TRUE)))
								$this->errors[$_key] = sprintf(__('Invalid delivery option: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'fname': // First name.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = ''; // Use a default value.

							if($this->is_insert && !$_value && $this->user && $this->user->first_name)
								$_value = (string)substr($this->user->first_name, 0, 50);

							if($this->is_insert && !$_value && $this->data['email'])
								$_value = $this->plugin->utils_string->email_name($this->data['email'], 50);

							if($this->is_update && isset($_value) && !$_value && $this->user && $this->user->first_name)
								$_value = (string)substr($this->user->first_name, 0, 50);

							if($this->is_update && isset($_value) && !$_value && $this->data['email'])
								$_value = $this->plugin->utils_string->email_name($this->data['email'], 50);

							if($this->is_update && isset($_value) && !$_value && $this->sub && $this->sub->email)
								$_value = $this->plugin->utils_string->email_name($this->sub->email, 50);

							if(isset($_value)) // Clean the name.
								$_value = $this->plugin->utils_string->clean_name($_value);

							if(isset($_value) && strlen($_value) > 50)
								$this->errors[$_key] = sprintf(__('Invalid first name: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 50))
								$this->errors[$_key] = sprintf(__('Invalid first name: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'lname': // Last name.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = ''; // Use a default value.

							if($this->is_insert && !$_value && $this->user && $this->user->last_name)
								$_value = (string)substr($this->user->last_name, 0, 100);

							if($this->is_update && isset($_value) && !$_value && $this->user && $this->user->last_name)
								$_value = (string)substr($this->user->last_name, 0, 100);

							if(isset($_value)) // Clean the name.
								$_value = $this->plugin->utils_string->clean_name($_value);

							if(isset($_value) && strlen($_value) > 100)
								$this->errors[$_key] = sprintf(__('Invalid last name: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 100))
								$this->errors[$_key] = sprintf(__('Invalid last name: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'email': // Email address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = ''; // Use a default value.

							if($this->is_insert && !$_value && $this->user && $this->user->user_email)
								$_value = $this->user->user_email;

							if(isset($_value) && (!$_value || !is_email($_value) || strlen($_value) > 100))
								$this->errors[$_key] = sprintf(__('Invalid email address: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !$_value || !is_email($_value) || strlen($_value) > 100))
								$this->errors[$_key] = sprintf(__('Invalid email address: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'insertion_ip': // Insertion IP address.

							if($this->user_initiated)
								if($this->ui_protected_data_keys_enable)
									$_value = NULL; // Nullify protected key.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = ''; // Use a default value.

							if($this->is_insert && !$_value && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if($this->is_update && !$_value && $this->sub && !$this->sub->insertion_ip && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if(isset($_value) && strlen($_value) > 39)
								$this->errors[$_key] = sprintf(__('Invalid insertion IP: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 39))
								$this->errors[$_key] = sprintf(__('Invalid insertion IP: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'last_ip': // Last known IP address.

							if($this->user_initiated)
								if($this->ui_protected_data_keys_enable)
									$_value = NULL; // Nullify protected key.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = ''; // Use a default value.

							if(!$_value && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if(isset($_value) && strlen($_value) > 39)
								$this->errors[$_key] = sprintf(__('Invalid last IP: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 39))
								$this->errors[$_key] = sprintf(__('Invalid last IP: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'status': // Status.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !$_value)
								$_value = 'unconfirmed'; // Use a default value.

							if($this->is_update && $this->user_initiated && $this->data['email'] && $this->sub)
								if(strcasecmp($this->data['email'], $this->sub->email) !== 0) // Email changing?
									// NOTE: if `process_confirmation` is not `TRUE`, this silently unconfirms a user.
									// User-initiated actions that change the email should set `process_confirmation = TRUE`.
									$_value = 'unconfirmed'; // User must reconfirm when they change email addresses.

							if(isset($_value) && !in_array($_value, array('unconfirmed', 'subscribed', 'suspended', 'trashed'), TRUE))
								$this->errors[$_key] = sprintf(__('Invalid status: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !in_array($_value, array('unconfirmed', 'subscribed', 'suspended', 'trashed'), TRUE)))
								$this->errors[$_key] = sprintf(__('Invalid status: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && $this->user_initiated && $this->ui_protected_data_keys_enable && $_value !== 'unconfirmed')
								$this->errors[$_key] = sprintf(__('Invalid status: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_update && isset($_value) && $this->user_initiated && $this->ui_protected_data_keys_enable
							        && !in_array($_value, array('unconfirmed', 'subscribed', 'suspended'), TRUE) // Cannot `trash` themselves.
							) $this->errors[$_key] = sprintf(__('Invalid status: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							// However, they SHOULD be allowed to delete/unsubscribe; which is a separate issue altogether; i.e. not covered here.

							// NOTE: a user should only be shown two options in a UI. Those should include `subscribed`, `suspended`.
							// We only allow `unconfirmed` here because that does no harm; and so email address changes can occur properly.
							//    ~ i.e. user-initiated email address changes will always result in an `unconfirmed` status update (as seen above).

							// NOTE: a user should NEVER be allowed to edit subscriptions that do not belong to them; i.e. for which they have not already been confirmed.
							//    Thus, changing the status back and forth always assumes that we are dealing w/ a user who has already been confirmed in one way or another.
							//    For this reason, it's OK for a user to change the status from `subscribed` to `unconfirmed`, and then back to `subscribed`.
							//    It is safe to assume here that a user would NOT have a key if they had not already been confirmed in some way.
							//    See also: the `key` check up above for `user_initiated` + `ui_protected_data_keys_enable` actions.
							//    See also: the `email` change of address check above w/ `user_initiated`.

							break; // Break switch handler.

						case 'insertion_time': // Insertion time.

							if($this->user_initiated)
								if($this->ui_protected_data_keys_enable)
									$_value = NULL; // Nullify protected key.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !$_value)
								$_value = time(); // Use a default value.

							if(isset($_value) && strlen((string)$_value) !== 10)
								$this->errors[$_key] = sprintf(__('Invalid insertion time: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen((string)$_value) !== 10))
								$this->errors[$_key] = sprintf(__('Invalid insertion time: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'last_update_time': // Last update time.

							if($this->user_initiated)
								if($this->ui_protected_data_keys_enable)
									$_value = NULL; // Nullify protected key.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !$_value)
								$_value = time(); // Use a default value.

							if(!$_value) $_value = time(); // Update time.

							if(isset($_value) && strlen((string)$_value) !== 10)
								$this->errors[$_key] = sprintf(__('Invalid last update time: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen((string)$_value) !== 10))
								$this->errors[$_key] = sprintf(__('Invalid last update time: `%1$s`.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.
					}
				}
				unset($_key, $_value); // Housekeeping.

				$this->validated = TRUE; // Flag as `TRUE`; data validated.
			}
		}
	}
}