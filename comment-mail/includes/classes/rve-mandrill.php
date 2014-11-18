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
			 * Key for this webhook.
			 *
			 * @since 141111 First documented version.
			 */
			public static function key()
			{
				$plugin = plugin();
				$class  = get_called_class();
				return $plugin->utils_enc->hmac_sha256_sign($class);
			}

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
				if(!$this->plugin->options['replies_via_email_enable'])
					return; // Replies via email are disabled currently.

				if($this->plugin->options['replies_via_email_handler'] !== 'mandrill')
					return; // Mandrill is not the currently selection RVE handler.

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

				$events = json_decode(trim(stripslashes($_REQUEST['mandrill_events'])));
				if(!is_array($events)) $events = array(); // Force array.

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

					$_reply_to_email = $this->isset_or($_event->msg->email, '', 'string');

					$_from_name  = $this->isset_or($_event->msg->from_name, '', 'string');
					$_from_email = $this->isset_or($_event->msg->from_email, '', 'string');

					$_subject = $this->isset_or($_event->msg->subject, '', 'string');

					$_text_body = $this->isset_or($_event->msg->text, '', 'string');
					$_html_body = $this->isset_or($_event->msg->html, '', 'string');

					if(isset($_event->msg->spam_report->score))
						$_spam_score = (float)$_event->msg->spam_report->score;
					else $_spam_score = 0.0; // Default value.

					if(isset($_event->msg->spf->result))
						$_spf_result = strtolower((string)$_event->msg->spf->result);
					else $_spf_result = 'none'; // Default value.

					if(isset($_event->msg->dkim->signed))
						$_dkim_signed = (boolean)$_event->msg->dkim->signed;
					else $_dkim_signed = FALSE; // Default value.

					if(isset($_event->msg->dkim->valid))
						$_dkim_valid = (boolean)$_event->msg->dkim->valid;
					else $_dkim_valid = FALSE; // Default value.

					$this->maybe_process_comment_reply(
						array(
							'reply_to_email' => $_reply_to_email,

							'from_name'      => $_from_name,
							'from_email'     => $_from_email,

							'subject'        => $_subject,

							'text_body'      => $_text_body,
							'html_body'      => $_html_body,

							'spam_score'     => $_spam_score,

							'spf_result'     => $_spf_result,

							'dkim_signed'    => $_dkim_signed,
							'dkim_valid'     => $_dkim_valid,
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
					'reply_to_email' => '',

					'from_name'      => '',
					'from_email'     => '',

					'subject'        => '',

					'text_body'      => '',
					'html_body'      => '',

					'spam_score'     => 0.0,

					'spf_result'     => 'none',

					'dkim_signed'    => FALSE,
					'dkim_valid'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$reply_to_email = trim((string)$args['reply_to_email']);

				$from_name  = trim((string)$args['from_name']);
				$from_email = trim((string)$args['from_email']);

				$subject = trim((string)$args['subject']);

				$text_body = trim((string)$args['text_body']);
				$html_body = trim((string)$args['html_body']);

				$spam_score = (float)$args['spam_score'];

				$spf_result = trim(strtolower((string)$args['spf_result']));

				$dkim_signed = (boolean)$args['dkim_signed'];
				$dkim_valid  = (boolean)$args['dkim_valid'];

				$force_status = NULL; // Initialize.

				if(!$reply_to_email) // Must have this.
					return; // Missing `Reply-To:` address.

				$text_body = $this->plugin->utils_string->html_to_text($text_body);
				$html_body = $this->plugin->utils_string->html_to_rich_text($html_body);
				if(!($rich_text_body = $this->coalesce($html_body, $text_body)))
					return; // Empty reply; nothing to do here.

				if($spam_score >= (float)$this->plugin->options['rve_mandrill_max_spam_score'])
					$force_status = 'spam'; // Force this to be considered `spam`.

				if(($spf_check = (integer)$this->plugin->options['rve_mandrill_spf_check_enable']))
					if(($spf_check === 1 && !in_array($spf_result, array('pass', 'neutral', 'softfail', 'none'), TRUE))
					   || ($spf_check === 2 && !in_array($spf_result, array('pass', 'neutral', 'none'), TRUE))
					   || ($spf_check === 3 && !in_array($spf_result, array('pass', 'neutral'), TRUE))
					   || ($spf_check === 4 && !in_array($spf_result, array('pass'), TRUE))
					) $force_status = 'spam'; // Force this to be considered `spam`.

				if(($dkim_check = (integer)$this->plugin->options['rve_mandrill_dkim_check_enable']))
					if(($dkim_check === 1 && $dkim_signed && !$dkim_valid) || ($dkim_check === 2 && (!$dkim_signed || !$dkim_valid)))
						$force_status = 'spam'; // Force this to be considered `spam`.

				$post_comment_args = compact(
					'reply_to_email',

					'from_name',
					'from_email',

					'subject',

					'rich_text_body',

					'force_status'
				);
				$this->plugin->utils_rve->maybe_post_comment($post_comment_args);
			}
		}
	}
}