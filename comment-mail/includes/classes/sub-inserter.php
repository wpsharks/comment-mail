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
		class sub_inserter extends abstract_base
		{
			/**
			 * @var \WP_User|null Subscriber.
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
			 * @var array Based on request args.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $data; // An array.

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
			 * @var boolean An update?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_update;

			/**
			 * @var boolean An insert?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_insert;

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
			 * @param \WP_User|null $user Subscriber.
			 *
			 * @param array         $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 *
			 * @param array         $args Any additional behavioral args.
			 */
			public function __construct($user, array $request_args, array $args = array())
			{
				parent::__construct();

				if($user instanceof \WP_User)
					$this->user = $user;

				if(!isset($user) && !empty($request_args['user_id']))
					$this->user = new \WP_User((integer)$request_args['user_id']);

				else if(!isset($user) && !empty($request_args['email']))
					if(($_user = \WP_User::get_data_by('email', (string)$request_args['email'])))
						$this->user = new \WP_User($_user->ID);
				unset($_user); // Housekeeping.

				$this->current_user = wp_get_current_user();

				$this->is_current_user = FALSE; // Default value.
				if($this->user && $this->user->ID === $this->current_user->ID)
					$this->is_current_user = TRUE; // Even if `ID` is `0`.

				$default_request_args = array(
					'ID'               => NULL,
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
					'process_events'       => TRUE,
					'process_confirmation' => FALSE,
				);
				$args          = array_merge($defaults_args, $args);
				$args          = array_intersect_key($args, $defaults_args);

				$this->process_events       = (boolean)$args['process_events'];
				$this->process_confirmation = (boolean)$args['process_confirmation'];

				$this->is_update = isset($this->data['ID']);
				$this->is_insert = !isset($this->data['ID']);

				$this->errors = array(); // Initialize.

				$this->maybe_update_insert();
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
			 * Public access to errors.
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
			 * Updates a subscriber; or inserts a new one.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_update_insert()
			{
				$this->sanitize_validate();

				if($this->errors) // Have errors?
					return; // Do nothing.

				if($this->is_update)
					$this->update();

				else if($this->is_insert)
					$this->insert();
			}

			/**
			 * Updates a subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function update()
			{
				$this->data['last_update_time'] = time(); // Force this.
				$table                          = $this->plugin->utils_db->prefix().'subs';

				if(!$this->data['ID'] || !($sub_before = $this->plugin->utils_sub->get($this->data['ID'])))
				{
					$this->errors['ID'] = // Fail. Unable to locate a matching ID.
						sprintf(__('Could not find ID: <code>%1$s</code>.', $this->plugin->text_domain),
						        esc_html($this->data['ID'])); // Escape markup.
					return; // Nothing more we can do here.
				}
				if($this->plugin->utils_db->wp->update($table, $this->data, array('ID' => $this->data['ID'])) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));

				$this->plugin->utils_sub->nullify_cache(array($this->data['ID'], $this->data['key']));

				if(!$this->process_events || !isset($this->data['status']))
					return; // Nothing more to do here.

				if($sub_before->status === $this->data['status'])
					return; // Nothing more to do here.

				$sub_after = $this->plugin->utils_sub->get($this->data['ID'], TRUE);

				switch($sub_after->status) // Handle event processing.
				{
					case 'unconfirmed': // Unsubscribing?
						if(in_array($sub_before->status, array('subscribed', 'suspended'), TRUE))
							new sub_event_log_inserter(array_merge((array)$sub_after, array('event' => 'unsubscribed')));
						break; // Break switch handler.

					case 'subscribed': // Subscribing?
						if(in_array($sub_before->status, array('unconfirmed', 'suspended'), TRUE))
							new sub_event_log_inserter(array_merge((array)$sub_after, array('event' => 'subscribed')));
						break; // Break switch handler.

					case 'suspended': // Suspending?
						if(in_array($sub_before->status, array('subscribed'), TRUE))
							new sub_event_log_inserter(array_merge((array)$sub_after, array('event' => 'suspended')));
						break; // Break switch handler.

					case 'trashed': // Like being deleted; same thing really.
						new sub_event_log_inserter(array_merge((array)$sub_after, array('event' => 'unsubscribed')));
						break; // Break switch handler.
				}
			}

			/**
			 * Inserts a subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function insert()
			{
				$this->data['insertion_time']   = time(); // Force this.
				$this->data['last_update_time'] = time(); // Force this.
				$table                          = $this->plugin->utils_db->prefix().'subs';

				if($this->plugin->utils_db->wp->replace($table, $this->data) === FALSE)
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));

				if(!($sub_id = (integer)$this->plugin->utils_db->wp->insert_id))
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));

				$this->plugin->utils_sub->nullify_cache(array($sub_id, $this->data['key']));

				if($this->process_events && $this->data['status'] !== 'trashed')
					new sub_event_log_inserter(array_merge($this->data, array('event' => 'subscribed')));

				if($this->process_confirmation && $this->data['status'] === 'unconfirmed')
					new sub_confirmer($sub_id, array('process_events' => $this->process_events));
			}

			/**
			 * Sanitize/validate request args.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function sanitize_validate()
			{
				foreach($this->data as $_key => &$_value)
				{
					switch($_key) // Validate each arg value.
					{
						case 'ID': // Primary key.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert)
								$_value = NULL; // We'll get a new ID.

							if(($this->is_update || isset($_value)) && ($_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && isset($_value))
								$this->errors[$_key] = sprintf(__('Invalid ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'key': // Unique key.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert) // Force a unique key.
								$_value = $this->plugin->utils_enc->uunnci_key_20_max();

							if(isset($_value) && (!$_value || strlen($_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid key: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !$_value || strlen($_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid key: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'user_id': // User ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 0; // Use a default value.

							if(!$_value && $this->user)
								$_value = $this->user->ID;

							if(isset($_value) && ($_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid user ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid user ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'post_id': // Post ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid post ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || $_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid post ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'comment_id': // Comment ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid comment ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_key] = sprintf(__('Invalid comment ID: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'deliver': // Delivery option.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 'asap'; // Use a default value.

							if(isset($_value) && !in_array($_value, array('asap', 'hourly', 'daily', 'weekly'), TRUE))
								$this->errors[$_key] = sprintf(__('Invalid delivery option: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !in_array($_value, array('asap', 'hourly', 'daily', 'weekly'), TRUE)))
								$this->errors[$_key] = sprintf(__('Invalid delivery option: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'fname': // First name.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(isset($_value)) // Clean the name.
								$_value = $this->plugin->utils_string->clean_name($_value);

							if(isset($_value) && strlen($_value) > 50)
								$this->errors[$_key] = sprintf(__('Invalid first name: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 50))
								$this->errors[$_key] = sprintf(__('Invalid first name: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'lname': // Last name.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(isset($_value)) // Clean the name.
								$_value = $this->plugin->utils_string->clean_name($_value);

							if(isset($_value) && strlen($_value) > 100)
								$this->errors[$_key] = sprintf(__('Invalid last name: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 100))
								$this->errors[$_key] = sprintf(__('Invalid last name: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'email': // Email address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(isset($_value) && (!$_value || !is_email($_value) || strlen($_value) > 100))
								$this->errors[$_key] = sprintf(__('Invalid email address: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !$_value || !is_email($_value) || strlen($_value) > 100))
								$this->errors[$_key] = sprintf(__('Invalid email address: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'insertion_ip': // Insertion IP address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(!$_value && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if(isset($_value) && strlen($_value) > 39)
								$this->errors[$_key] = sprintf(__('Invalid insertion IP: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 39))
								$this->errors[$_key] = sprintf(__('Invalid insertion IP: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'last_ip': // Last known IP address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(!$_value && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if(isset($_value) && strlen($_value) > 39)
								$this->errors[$_key] = sprintf(__('Invalid last IP: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 39))
								$this->errors[$_key] = sprintf(__('Invalid last IP: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'status': // Status.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 'unconfirmed'; // Use a default value.

							if(isset($_value) && !in_array($_value, array('unconfirmed', 'subscribed', 'suspended', 'trashed'), TRUE))
								$this->errors[$_key] = sprintf(__('Invalid status: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || !in_array($_value, array('unconfirmed', 'subscribed', 'suspended', 'trashed'), TRUE)))
								$this->errors[$_key] = sprintf(__('Invalid status: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'insertion_time': // Insertion time.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = time(); // Use a default value.

							if(!$_value) $_value = time();

							if(isset($_value) && strlen((string)$_value) !== 10)
								$this->errors[$_key] = sprintf(__('Invalid insertion time: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen((string)$_value) !== 10))
								$this->errors[$_key] = sprintf(__('Invalid insertion time: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.

						case 'last_update_time': // Last update time.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = time(); // Use a default value.

							if(!$_value) $_value = time();

							if(isset($_value) && strlen((string)$_value) !== 10)
								$this->errors[$_key] = sprintf(__('Invalid last update time: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							else if($this->is_insert && (!isset($_value) || strlen((string)$_value) !== 10))
								$this->errors[$_key] = sprintf(__('Invalid last update time: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($_value));

							break; // Break switch handler.
					}
				}
				unset($_key, $_value); // Housekeeping.
			}
		}
	}
}