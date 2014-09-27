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
			 * @var object|null Comment object (now).
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
			 * Class constructor.
			 *
			 * @param integer|string $comment_id Comment ID.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct($comment_id)
			{
				$this->plugin = plugin();

				$comment_id = (integer)$comment_id;

				if($comment_id) // If possible.
					$this->comment = get_comment($comment_id);

				$this->user = wp_get_current_user();

				if(!empty($_POST[__NAMESPACE__.'_subscribe']))
					$this->sub_type = $this->plugin->trim_strip_deep((string)$_POST[__NAMESPACE__.'_subscribe']);

				$this->sub_type = strtolower($this->sub_type);
				if(!in_array($this->sub_type, array('comments', 'comment'), TRUE))
					$this->sub_type = ''; // Default type.

				$this->maybe_insert(); // If applicable.
			}

			/**
			 * Inserts a new subscriber, if not already in the system.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert()
			{
				if(!$this->sub_type)
					return; // Not applicable.

				if(!$this->comment)
					return; // Not applicable.

				if($this->comment->comment_type !== 'comment')
					return; // Not applicable.

				if(!$this->comment->post_ID || !$this->comment->comment_ID)
					return; // Not applicable.

				if(!$this->comment->comment_author_email)
					return; // Not applicable.

				if($this->check_existing()) // Exists?
					return; // Can't subscribe them again.

				$this->delete_existing(); // Delete existing.

				$insertion_ip = $last_ip = $ip = $this->current_ip();

				$data = array(
					'key'              => $this->key_gen(),
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
				       " AND `comment_id` = '".esc_sql($this->sub_type === 'comment' ? $this->comment->comment_ID : 0)."'".

				       " AND ((`user_id` > '0' AND `user_id` = '".esc_sql($this->user->ID)."')".
				       "     OR `email` = '".esc_sql($this->comment->comment_author_email)."')".

				       " LIMIT 1"; // We should only have one anyway.

				return $this->plugin->wpdb->get_row($sql);
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

				new sub_confirmer($existing_sub->ID); // Send confirmation email now.
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

				       " AND ((`user_id` > '0' AND `user_id` = '".esc_sql($this->user->ID)."')".
				       "     OR `email` = '".esc_sql($this->comment->comment_author_email)."')";

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
				$name = $this->clean_name();

				$fname = $name; // Defaults to the full name.

				if(strpos($name, ' ', 1) !== FALSE) // Last name also?
					list($fname,) = explode(' ', $this->comment->comment_author, 2);

				$fname = trim($fname); // Cleanup the user's first name.

				if(!$fname) $fname = strstr($this->comment->comment_author_email, '@', TRUE);

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
				$name = $this->clean_name();

				$lname = ''; // Defaults to an empty string.

				if(strpos($name, ' ', 1) !== FALSE) // Last name also?
					list(, $lname) = explode(' ', $this->comment->comment_author, 2);

				$lname = trim($lname); // Cleanup the user's last name.

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
				$name = $this->comment->comment_author;
				$name = preg_replace('/^(?:Mr\.|Mrs\.|Ms\.|Dr\.)\s+/i', '', $name);
				$name = preg_replace('/\s+(?:Sr\.|Jr\.)$/i', '', $name);
				$name = trim($name); // Cleanup the name.

				return $name; // Cleaned up.
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

			/**
			 * A unique, unguessable, case insensitive key for each row.
			 *
			 * With a max length of 16 chars. Keys are generated based on `microtime()`,
			 *    and with base 36 component values concatenated with a random
			 *    number not to exceed `999999999`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string A unique, unguessable, case insensitive key.
			 */
			protected function key_gen()
			{
				list($seconds, $microseconds) = explode('.', microtime(TRUE), 2);
				$seconds_base36      = base_convert($seconds, '10', '36');
				$microseconds_base36 = base_convert($microseconds, '10', '36');

				$mt_rand_base36 = base_convert(mt_rand(1, 999999999), '10', '36');
				$key            = $mt_rand_base36.$seconds_base36.$microseconds_base36;

				return substr($key, 0, 16);
			}
		}
	}
}