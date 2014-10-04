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
			 * @var \stdClass|null Comment object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment;

			/**
			 * @var \WP_User|null Subscribing user.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user;

			/**
			 * @var \WP_User|null Current user.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $current_user;

			/**
			 * @var boolean Subscribing current user?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $is_current_user;

			/**
			 * @var string Subscription type.
			 *    ``, `comments`, `comment`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $type;

			/**
			 * @var string Subscription delivery cycle.
			 *    `asap`, `hourly`, `daily`, `weekly`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $deliver;

			/**
			 * @var null|boolean Auto-confirm?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $auto_confirm;

			/**
			 * @var boolean Sub. already exists?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_exists;

			/**
			 * @var integer Insertion ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $insert_id;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \WP_User|null  Subscribing user.
			 *    Use `NULL` to indicate they are NOT a user.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @param string         $type Type of subscription.
			 *    Please pass one of: `comments`, `comment`.
			 *
			 * @param string         $deliver Delivery cycle. Defaults to `asap`.
			 *    Please pass one of: `asap`, `hourly`, `daily`, `weekly`.
			 *
			 * @param null|boolean   $auto_confirm Auto-confirm subscriber?
			 *    If `NULL`, use current plugin option value.
			 */
			public function __construct($user, $comment_id, $type = 'comment', $deliver = 'asap', $auto_confirm = NULL)
			{
				parent::__construct();

				if($user instanceof \WP_User)
					$this->user = $user;

				$this->current_user = wp_get_current_user();

				$this->is_current_user = FALSE; // Default value.
				if($this->user && $this->user->ID === $this->current_user->ID)
					$this->is_current_user = TRUE; // Even if `ID` is `0`.

				if(($comment_id = (integer)$comment_id))
					$this->comment = get_comment($comment_id);

				$this->type = strtolower((string)$type);
				if(!in_array($this->type, array('comments', 'comment'), TRUE))
					$this->type = ''; // Default type.

				$this->deliver = strtolower((string)$deliver);
				if(!in_array($this->deliver, array('asap', 'hourly', 'daily', 'weekly'), TRUE))
					$this->deliver = ''; // Default cycle.

				$this->auto_confirm = isset($auto_confirm) ? (boolean)$auto_confirm : NULL;

				$this->sub_exists = FALSE; // Initialize.
				$this->insert_id  = 0; // Initialize.

				$this->maybe_insert();
			}

			/**
			 * Inserts a new subscriber, if not already in the system.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert()
			{
				if(!$this->comment)
					return; // Not applicable.

				if($this->comment->comment_type !== 'comment')
					return; // Not applicable.

				if(!$this->comment->post_ID || !$this->comment->comment_ID)
					return; // Not applicable.

				if(!$this->comment->comment_author_email)
					return; // Not applicable.

				if(!$this->type || !$this->deliver)
					return; // Not applicable.

				if(($this->sub_exists = $this->check_existing()))
					return; // Can't subscribe again.

				$insertion_ip = $last_ip = $this->user_ip();

				$data = array(
					'key'              => $this->plugin->utils_enc->uunnci_key_20_max(),
					'user_id'          => $this->user ? (integer)$this->user->ID : 0,
					'post_id'          => (integer)$this->comment->post_ID,
					'comment_id'       => $this->type === 'comments'
						? 0 : (integer)$this->comment->comment_ID,
					'deliver'          => $this->deliver,

					'fname'            => $this->first_name(),
					'lname'            => $this->last_name(),
					'email'            => $this->comment->comment_author_email,
					'insertion_ip'     => $insertion_ip,
					'last_ip'          => $last_ip,

					'status'           => 'unconfirmed',

					'insertion_time'   => time(),
					'last_update_time' => time()
				);
				if(!$this->plugin->utils_db->wp->replace($this->plugin->utils_db->prefix().'subs', $data))
					throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

				if(!($sub_id = $this->insert_id = (integer)$this->plugin->utils_db->wp->insert_id))
					throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

				new sub_event_log_inserter(array_merge($data, array('sub_id' => $sub_id, 'event' => 'subscribed')));

				new sub_confirmer($sub_id, $this->auto_confirm); // Confirm; before deletion of others.

				$this->delete_others(); // Delete other subscriptions now.
			}

			/**
			 * Check existing email/sub.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if a subscription already exists.
			 */
			protected function check_existing()
			{
				$existing_sub = $this->check_existing_sub();

				if($existing_sub && $existing_sub->status === 'subscribed')
					return TRUE; // Already exists.

				if($existing_sub && $existing_sub->status !== 'subscribed')
				{
					$this->maybe_reconfirm($existing_sub);
					return TRUE; // Already exists.
				}
				return FALSE; // Nope, does not exist yet.
			}

			/**
			 * Check existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return \stdClass|null Existing sub; else NULL.
			 */
			protected function check_existing_sub()
			{
				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->comment->post_ID)."'".
				       " AND `comment_id` = '".esc_sql($this->type === 'comments' ? 0 : $this->comment->comment_ID)."'".
				       " AND `deliver` = '".esc_sql($this->deliver)."'". // Delivery cycle.

				       ($this->user && $this->user->ID // Has a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->user->ID)."'".
					         "       OR `email` = '".esc_sql($this->comment->comment_author_email)."')"
					       : " AND `email` = '".esc_sql($this->comment->comment_author_email)."'").

				       " ORDER BY `insertion_time` ASC"; // For the loop below.

				if(!($results = $this->plugin->utils_db->wp->get_results($sql)))
					return NULL; // Nothing exists.

				$last = $last_subscribed = NULL; // Initialize.

				foreach($results as $_result) switch($_result->status)
				{
					case 'subscribed': // Subscribed?
						$last = $last_subscribed = $_result;

					default: // Default case handler.
						$last = $_result;
				}
				unset($_result); // Just a little housekeeping.

				return $last_subscribed // Subscribed?
					? $this->plugin->utils_db->typify_deep($last_subscribed)
					: $this->plugin->utils_db->typify_deep($last);
			}

			/**
			 * Maybe resend confirmation email.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param \stdClass $existing_sub Existing sub.
			 */
			protected function maybe_reconfirm(\stdClass $existing_sub)
			{
				if($existing_sub->status === 'subscribed')
					return; // Not applicable.

				if($existing_sub->insertion_time >= strtotime('-15 minutes'))
					return; // Recently subscribed; give em' time.

				new sub_confirmer($existing_sub->ID, $this->auto_confirm);
			}

			/**
			 * Delete other subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @throws \exception If a deletion failure occurs.
			 */
			protected function delete_others()
			{
				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->comment->post_ID)."'".
				       " AND (`comment_id` = '0' OR `comment_id` = '".esc_sql($this->comment->comment_ID)."')".

				       ($this->user && $this->user->ID // Has a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->user->ID)."'".
					         "       OR `email` = '".esc_sql($this->comment->comment_author_email)."')"
					       : " AND `email` = '".esc_sql($this->comment->comment_author_email)."'").

				       " AND `ID` != '".esc_sql($this->insert_id)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
			}

			/**
			 * Commenters first name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters first name; else `name` in email address.
			 */
			protected function first_name()
			{
				$name  = $this->clean_name();
				$fname = $name; // Full name.

				if(strpos($name, ' ', 1) !== FALSE)
					list($fname,) = explode(' ', $name, 2);

				$fname = trim($fname); // Cleanup first name.

				if(!$fname) // Fallback on the email address.
					$fname = strstr($this->comment->comment_author_email, '@', TRUE);

				return $fname; // First name.
			}

			/**
			 * Commenters last name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters last name; else empty string.
			 */
			protected function last_name()
			{
				$name  = $this->clean_name();
				$lname = ''; // Empty string.

				if(strpos($name, ' ', 1) !== FALSE)
					list(, $lname) = explode(' ', $name, 2);

				$lname = trim($lname); // Cleanup last name.

				return $lname; // Last name.
			}

			/**
			 * Commenters clean name.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters clean name; else empty string.
			 */
			protected function clean_name()
			{
				return $this->plugin->utils_string->clean_name($this->comment->comment_author);
			}

			/**
			 * Commenters IP address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters IP address; else empty string.
			 */
			protected function user_ip()
			{
				if($this->user && $this->is_current_user)
					return $this->plugin->utils_env->user_ip();

				return ''; // Not current user.
			}
		}
	}
}