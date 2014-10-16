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
			 * @var array Request args.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $request_args;

			/**
			 * @var boolean Process events?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $process_events;

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
			 * @var boolean Is an admin menu page?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_menu_page;

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
			 * @param \WP_User|null $user Subscribing user.
			 *    Use `NULL` to indicate they are NOT a user.
			 *
			 * @param array         $request_args Array of subscriber data.
			 *
			 * @param boolean       $process_events Should we process any events?
			 */
			public function __construct($user, array $request_args, $process_events = FALSE)
			{
				parent::__construct();

				if($user instanceof \WP_User)
					$this->user = $user;

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
				$this->request_args   = array_merge($default_request_args, $request_args);
				$this->request_args   = array_intersect_key($request_args, $default_request_args);

				$this->process_events = (boolean)$process_events;

				$this->is_update    = isset($this->request_args['ID']);
				$this->is_insert    = !isset($this->request_args['ID']);
				$this->is_menu_page = $this->plugin->utils_env->is_menu_page();
				$this->errors       = array(); // Initialize.

				$this->maybe_update_insert();
			}

			/**
			 * Updates a subscriber; or inserts a new one.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_update_insert()
			{
				$this->sanitize_validate_request_args();

				if($this->errors) // Have errors?
					return; // Do nothing.

				if($this->is_update)
					$this->update();

				else if($this->is_insert)
					$this->insert();

				new sub_event_log_inserter(array_merge($data, array('sub_id' => $sub_id, 'event' => 'subscribed')));

				new sub_confirmer($sub_id, $this->auto_confirm); // Confirm; before deletion of others.
			}

			/**
			 * Updates a subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function update()
			{
				$args                     = $this->request_args;
				$args['last_update_time'] = time(); // Force this.
				$ID                       = $this->request_args['ID'];
				$table                    = $this->plugin->utils_db->prefix().'subs';

				if($this->plugin->utils_db->wp->update($table, $args, compact('ID')) === FALSE)
					throw new \exception(__('Update failure.', $this->plugin->text_domain));
			}

			/**
			 * Inserts a subscriber.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function insert()
			{
				$args                     = $this->request_args;
				$args['last_update_time'] = time(); // Force this.
				$table                    = $this->plugin->utils_db->prefix().'subs';

				if($this->plugin->utils_db->wp->replace($table, $args) === FALSE)
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));
			}

			/**
			 * Sanitize/validate request args.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function sanitize_validate_request_args()
			{
				foreach($this->request_args as $_arg => &$_value)
				{
					switch($_arg) // Validate each arg value.
					{
						case 'ID': // Primary key.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert)
								$_value = NULL; // We'll get a new ID.

							if(($this->is_update || isset($_value)) && ($_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid ID.', $this->plugin->text_domain);

							else if($this->is_insert && isset($_value))
								$this->errors[$_arg] = __('Invalid ID.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'key': // Unique key.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert) // Force a unique key.
								$_value = $this->plugin->utils_enc->uunnci_key_20_max();

							if(isset($_value) && (!$_value || strlen($_value) > 20))
								$this->errors[$_arg] = __('Invalid key.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || !$_value || strlen($_value) > 20))
								$this->errors[$_arg] = __('Invalid key.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'user_id': // User ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid user ID.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid user ID.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'post_id': // Post ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid post ID.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || $_value < 1 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid post ID.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'comment_id': // Comment ID.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 0; // Use a default value.

							if(isset($_value) && ($_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid comment ID.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string)$_value) > 20))
								$this->errors[$_arg] = __('Invalid comment ID.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'deliver': // Delivery option.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 'asap'; // Use a default value.

							if(isset($_value) && !in_array($_value, array('asap', 'hourly', 'daily', 'weekly'), TRUE))
								$this->errors[$_arg] = __('Invalid delivery option.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || !in_array($_value, array('asap', 'hourly', 'daily', 'weekly'), TRUE)))
								$this->errors[$_arg] = __('Invalid delivery option.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'fname': // First name.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(isset($_value)) // Clean the name.
								$_value = $this->plugin->utils_string->clean_name($_value);

							if(isset($_value) && strlen($_value) > 50)
								$this->errors[$_arg] = __('Invalid first name.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 50))
								$this->errors[$_arg] = __('Invalid first name.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'lname': // Last name.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(isset($_value)) // Clean the name.
								$_value = $this->plugin->utils_string->clean_name($_value);

							if(isset($_value) && strlen($_value) > 100)
								$this->errors[$_arg] = __('Invalid last name.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 100))
								$this->errors[$_arg] = __('Invalid last name.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'email': // Email address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(isset($_value) && (!$_value || !is_email($_value) || strlen($_value) > 100))
								$this->errors[$_arg] = __('Invalid email address.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || !$_value || !is_email($_value) || strlen($_value) > 100))
								$this->errors[$_arg] = __('Invalid email address.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'insertion_ip': // Insertion IP address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(!$_value && !$this->is_menu_page && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if(isset($_value) && strlen($_value) > 39)
								$this->errors[$_arg] = __('Invalid insertion IP.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 39))
								$this->errors[$_arg] = __('Invalid insertion IP.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'last_ip': // Last known IP address.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = ''; // Use a default value.

							if(!$_value && !$this->is_menu_page && $this->is_current_user)
								$_value = $this->plugin->utils_env->user_ip();

							if(isset($_value) && strlen($_value) > 39)
								$this->errors[$_arg] = __('Invalid last IP.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || strlen($_value) > 39))
								$this->errors[$_arg] = __('Invalid last IP.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'status': // Status.

							if(isset($_value))
								$_value = (string)$_value;

							if($this->is_insert && !isset($_value))
								$_value = 'unconfirmed'; // Use a default value.

							if(isset($_value) && !in_array($_value, array('unconfirmed', 'subscribed', 'suspended', 'trashed'), TRUE))
								$this->errors[$_arg] = __('Invalid status.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || !in_array($_value, array('unconfirmed', 'subscribed', 'suspended', 'trashed'), TRUE)))
								$this->errors[$_arg] = __('Invalid status.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'insertion_time': // Insertion time.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = time(); // Use a default value.

							if(!$_value) $_value = time();

							if(isset($_value) && strlen((string)$_value) !== 10)
								$this->errors[$_arg] = __('Invalid insertion time.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || strlen((string)$_value) !== 10))
								$this->errors[$_arg] = __('Invalid insertion time.', $this->plugin->text_domain);

							break; // Break switch handler.

						case 'last_update_time': // Last update time.

							if(isset($_value))
								$_value = (integer)$_value;

							if($this->is_insert && !isset($_value))
								$_value = time(); // Use a default value.

							if(!$_value) $_value = time();

							if(isset($_value) && strlen((string)$_value) !== 10)
								$this->errors[$_arg] = __('Invalid last update time.', $this->plugin->text_domain);

							else if($this->is_insert && (!isset($_value) || strlen((string)$_value) !== 10))
								$this->errors[$_arg] = __('Invalid last update time.', $this->plugin->text_domain);

							break; // Break switch handler.
					}
				}
				unset($_arg, $_value); // Housekeeping.
			}
		}
	}
}