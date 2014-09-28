<?php
/**
 * Sub Inserter
 *
 * @package sub_inserter
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
		 * @package sub_inserter
		 * @since 14xxxx First documented version.
		 */
		class sub_inserter // Sub inserter.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var \stdClass|null Comment object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment; // Set by constructor.

			/**
			 * @var \WP_User|null Current user.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $user; // Set by constructor.

			/**
			 * @var string Subscription type.
			 *    ``, `comments`, `comment`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_type; // Set by constructor.

			/**
			 * @var boolean Override existing sub type?
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $sub_type_override; // Set by constructor.

			/**
			 * @var keygen Key generator.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $keygen; // Set by constructor.

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @param string         $sub_type Type of subscription.
			 *    Please pass one of: `comments`, `comment`.
			 *
			 * @param boolean        $sub_type_override Defaults to a FALSE value.
			 */
			public function __construct($comment_id, $sub_type, $sub_type_override = FALSE)
			{
				$this->plugin = plugin();

				if(($comment_id = (integer)$comment_id))
					$this->comment = get_comment($comment_id);

				$this->sub_type = strtolower((string)$sub_type);
				if(!in_array($this->sub_type, array('comments', 'comment'), TRUE))
					$this->sub_type = ''; // Default type.

				$this->sub_type_override = (boolean)$sub_type_override;

				$this->user = wp_get_current_user();

				$this->keygen = new keygen();

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

				if(!$this->sub_type)
					return; // Not applicable.

				if($this->check_existing())
					return; // Can't subscribe again.

				$this->delete_existing(); // Delete existing.

				$insertion_ip = $last_ip = $ip = $this->current_ip();

				$data = array(
					'key'              => $this->keygen->sub_key(),
					'user_id'          => $this->user->ID,
					'post_id'          => $this->comment->post_ID,
					'comment_id'       => $this->sub_type === 'comment'
						? $this->comment->comment_ID : 0,

					'fname'            => $this->first_name(),
					'lname'            => $this->last_name(),
					'email'            => $this->comment->comment_author_email,
					'insertion_ip'     => $insertion_ip,
					'last_ip'          => $last_ip,

					'status'           => 'unconfirmed',

					'insertion_time'   => time(),
					'last_update_time' => time(),
				);
				$this->plugin->wpdb->insert($this->plugin->db_prefix().'subs', $data);

				if(!($sub_id = $this->plugin->wpdb->insert_id)) // Insertion failure?
					throw new \exception(__('Sub insertion failure.', $this->plugin->text_domain));

				new event_log_inserter(array_merge($data, array('sub_id' => $sub_id, 'ip' => $ip, 'event' => 'subscribed')));

				new sub_confirmer($sub_id); // Send confirmation email now.
			}

			/**
			 * Check existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if a subscription already exists.
			 */
			protected function check_existing()
			{
				$existing_sub = $this->check_existing_sub();

				if($existing_sub && $existing_sub->status === 'subscribed')
					return TRUE; // Same subscription already exists.

				if($existing_sub && $existing_sub->status !== 'subscribed')
				{
					$this->maybe_reconfirm($existing_sub);
					return TRUE; // All done here.
				}
				return FALSE; // Does NOT exist yet.
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
				$sql = "SELECT * FROM `".esc_sql($this->plugin->db_prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->comment->post_ID)."'".

				       ($this->sub_type_override // Existing `$sub_type` must match exactly, else override?
					       ? " AND `comment_id` = '".esc_sql($this->sub_type === 'comment' ? $this->comment->comment_ID : 0)."'"

					       : " AND (`comment_id` = '0'". // If already subscribed to all comments.
					         // Or, if they are trying to subscribed to a specific comment, and they already are.
					         ($this->sub_type === 'comment' ? " OR `comment_id` = '".esc_sql($this->comment->comment_ID)."')" : ")")).

				       ($this->user->ID // Do we have a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->user->ID)."'".
					         "       OR `email` = '".esc_sql($this->comment->comment_author_email)."')"
					       : " AND `email` = '".esc_sql($this->comment->comment_author_email)."'").

				       " ORDER BY `insertion_time` ASC"; // For the loop below.

				if(!($results = $this->plugin->wpdb->get_results($sql)))
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

				return $last_subscribed ? $last_subscribed : $last;
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

				new sub_confirmer($existing_sub->ID); // Resend confirmation email.
			}

			/**
			 * Delete any existing subscription(s).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function delete_existing()
			{
				$sql = "DELETE FROM `".esc_sql($this->plugin->db_prefix().'subs')."`".

				       " WHERE `post_id` = '".esc_sql($this->comment->post_ID)."'".

				       ($this->user->ID // Do we have a user ID?
					       ? " AND (`user_id` = '".esc_sql($this->user->ID)."'".
					         "       OR `email` = '".esc_sql($this->comment->comment_author_email)."')"
					       : " AND `email` = '".esc_sql($this->comment->comment_author_email)."'");

				$this->plugin->wpdb->query($sql); // Delete any existing subscription(s).
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
				return $this->plugin->clean_name($this->comment->comment_author);
			}

			/**
			 * Commenters IP address.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Commenters IP address; else empty string.
			 */
			protected function current_ip()
			{
				return !empty($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
			}
		}
	}
}