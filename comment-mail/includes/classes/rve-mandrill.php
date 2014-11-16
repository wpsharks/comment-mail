<?php
/**
 * Replies via Email; Mandrill Webhook Listener
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\rve_mandrill'))
	{
		/**
		 * Replies via Email; Mandrill Webhook Listener
		 *
		 * @since 141111 First documented version.
		 */
		class rve_mandrill extends abs_base
		{
			/**
			 * @var string Key for this webhook.
			 *
			 * @since 141111 First documented version.
			 */
			protected $key; // Input verification.

			/**
			 * @var array Mandrill input events.
			 *
			 * @since 141111 First documented version.
			 */
			protected $events;

			/**
			 * Class constructor.
			 *
			 * @param string $key Input secret key.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct($key)
			{
				parent::__construct();

				$this->key    = trim((string)$key);
				$this->events = array(); // Initialize.

				$this->prep_webhook();
				$this->maybe_process();
			}

			/**
			 * Prepare webhook.
			 *
			 * @since 141111 First documented version.
			 */
			protected function prep_webhook()
			{
				ignore_user_abort(TRUE);
				nocache_headers();
			}

			/**
			 * Process webhook event.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_process()
			{
				if(!$this->plugin->options['rve_mandrill_enable'])
					return; // Mandrill is disabled currently.

				if($this->key !== static::key())
					return; // Not authorized.

				$this->collect_events();
				$this->process_events();
			}

			/**
			 * Collect Mandrill events.
			 *
			 * @since 141111 First documented version.
			 */
			protected function collect_events()
			{
				$this->events = array(); // Initialize.

				if(empty($_REQUEST['mandrill_events']))
					return; // Nothing to do.

				if(!is_string($_REQUEST['mandrill_events']))
					return; // Expecting JSON-encoded events.

				$events = json_decode(stripslashes($_REQUEST['mandrill_events']));
				if(!is_array($events)) $events = array();

				$this->events = $events; // Collected events.
			}

			/**
			 * Process Mandrill events.
			 *
			 * @since 141111 First documented version.
			 */
			protected function process_events()
			{
				foreach($this->events as $_event) // Iterate all events.
				{
					if(empty($_event->ts) || $_event->ts < strtotime('-7 days'))
						continue; // Missing timestamp; or it's very old.

					if(empty($_event->event) || $_event->event !== 'inbound')
						continue; // Expecting an inbound event.

					if(empty($_event->msg) || !($_event->msg instanceof \stdClass))
						continue; // Expecting a msg object w/ properties.

					$_from_name  = $this->isset_or($_event->msg->from_name, '', 'string');
					$_from_email = $this->isset_or($_event->msg->from_email, '', 'string');

					$_subject = $this->isset_or($_event->msg->subject, '', 'string');
					$_text    = $this->isset_or($_event->msg->text, '', 'string');
					$_html    = $this->isset_or($_event->msg->html, '', 'string');

					if(isset($_event->msg->spam_report->score)) // Do we have a `spam_report`?
						$_spam_score = (float)$_event->msg->spam_report->score;
					else $_spam_score = 0.0; // Always a float value.

					$this->maybe_process_comment_reply(
						array(
							'from_name'  => $_from_name,
							'from_email' => $_from_email,

							'subject'    => $_subject,
							'text'       => $_text,
							'html'       => $_html,

							'spam_score' => $_spam_score,
						));
				}
				unset($_event); // Housekeeping.
			}

			/**
			 * Processes a comment reply.
			 *
			 * @param array $args Input email/event arguments.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_process_comment_reply(array $args)
			{
				$default_args = array(
					'from_name'  => '',
					'from_email' => '',

					'subject'    => '',
					'text'       => '',
					'html'       => '',

					'spam_score' => 0.0,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$from_name  = trim((string)$args['from_name']);
				$from_email = trim((string)$args['from_email']);

				$subject = trim((string)$args['subject']);
				$text    = trim((string)$args['text']);
				$html    = trim((string)$args['html']);

				$spam_score = (float)$args['spam_score'];

				if(!$from_email || !is_email($from_email))
					return; // Invalid from address.

				if($spam_score >= $this->plugin->options['rve_mandrill_max_spam_score'])
					return; // Too spammy. Score exceeds configured maximum.

				$text      = $this->plugin->utils_string->html_to_text($text);
				$html      = $this->plugin->utils_string->html_to_rich_text($html);
				$rich_text = $this->coalesce($html, $text); // Prefer HTML markup.

				if(!$rich_text) return; // No message.
				if(!($sanitized_rich_text = $this->sanitize_rich_text($rich_text)))
					return; // Sanitized rich text is now empty.

				if(!($in_reply_to_comment_id = $this->in_reply_to_comment_id($subject, $rich_text)))
					return; // Unable to determine which comment ID the reply is to.
			}

			/**
			 * Determine comment ID being replied to.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $subject Reply subject line.
			 * @param string $rich_text Rich text message body.
			 *
			 * @return integer Comment ID being replied to, else `0` on failure.
			 */
			protected function in_reply_to_comment_id($subject, $rich_text)
			{
				$comment_id = 0; // Initialize.

				$subject   = trim((string)$subject);
				$rich_text = trim((string)$rich_text);
				$text      = $this->plugin->utils_string->html_to_text($rich_text);

				if(!$comment_id) // Check subject line for a single comment ID marker.
					if($subject && preg_match_all('/~#(?P<comment_id>[1-9][0-9]*)/', $subject, $m) === 1)
					{
						$comment_id = (integer)$m['comment_id'][0];
						if($comment_id === PHP_INT_MAX) $comment_id = 0;
					}
				if(!$comment_id) // Check text for a single comment ID marker.
					if($text && preg_match_all('/~#(?P<comment_id>[1-9][0-9]*)/', $text, $m) === 1)
					{
						$comment_id = (integer)$m['comment_id'][0];
						if($comment_id === PHP_INT_MAX) $comment_id = 0;
					}
				if(!$comment_id) // Check text for a leading comment ID marker.
					if($text && preg_match('/^~#(?P<comment_id>[1-9][0-9]*)/', $text, $m))
					{
						$comment_id = (integer)$m['comment_id'];
						if($comment_id === PHP_INT_MAX) $comment_id = 0;
					}
				return $comment_id; // This will be `0` on failure.
			}

			/**
			 * Sanitize rich text reply message.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $rich_text Rich text message body.
			 *
			 * @return string Sanitized rich text reply message.
			 */
			protected function sanitize_rich_text($rich_text)
			{
				if(!($rich_text = trim((string)$rich_text)))
					return $rich_text; // Empty.

				$rich_text = preg_replace('/~#[1-9][0-9]*\s*/', '', $rich_text);

				// @TODO strip original comment off the end of a reply, where all remaining lines begin with `>`.
			}

			/**
			 * Key for this webhook.
			 *
			 * @since 141111 First documented version.
			 */
			public static function key()
			{
				$plugin = plugin();
				$class  = get_called_class();
				$key    = $plugin->utils_enc->hmac_sha256_sign($class);
			}
		}
	}
}