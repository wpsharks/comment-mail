<?php
/**
 * Log Cleaner
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\log_cleaner'))
	{
		/**
		 * Log Cleaner
		 *
		 * @since 141111 First documented version.
		 */
		class log_cleaner extends abs_base
		{
			/**
			 * @var integer Start time.
			 *
			 * @since 141111 First documented version.
			 */
			protected $start_time;

			/**
			 * @var integer Max execution time.
			 *
			 * @since 141111 First documented version.
			 */
			protected $max_time;

			/**
			 * @var integer Total cleaned entries.
			 *
			 * @since 141111 First documented version.
			 */
			protected $cleaned;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer|null $max_time Max time (in seconds).
			 *
			 *    This cannot be less than `10` seconds.
			 *    This cannot be greater than `3600` seconds.
			 */
			public function __construct($max_time = NULL)
			{
				parent::__construct();

				$this->start_time = time();

				if(isset($max_time)) // Argument is set?
					$this->max_time = (integer)$max_time; // This takes precedence.
				else $this->max_time = (integer)$this->plugin->options['log_cleaner_max_time'];

				if($this->max_time < 10) $this->max_time = 10;
				if($this->max_time > 3600) $this->max_time = 3600;

				$this->cleaned = 0; // Initialize.

				$this->prep_cron_job();
				$this->maybe_clean_sub_event_log_entries();
				$this->maybe_clean_queue_event_log_entries();
			}

			/**
			 * Total log entries cleaned.
			 *
			 * @since 141111 First documented version.
			 */
			public function cleaned()
			{
				return $this->cleaned;
			}

			/**
			 * Prep CRON job.
			 *
			 * @since 141111 First documented version.
			 */
			protected function prep_cron_job()
			{
				ignore_user_abort(TRUE);

				@set_time_limit($this->max_time); // Max time only (first).
				// Doing this first in case the time below exceeds an upper limit.
				// i.e. hosts may prevent this from being set higher than `$max_time`.

				// The following may not work, but we can try :-)
				@set_time_limit(min(3600, $this->max_time + 30)); // If possible.
			}

			/**
			 * Cleanup sub. event log entries.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_clean_sub_event_log_entries()
			{
				if($this->is_out_of_time())
					return; // Not enough time.

				if(!$this->plugin->options['sub_event_log_expiration_time'])
					return; // Not applicable; functionality disabled.

				if(!($exp_time = strtotime('-'.$this->plugin->options['sub_event_log_expiration_time'])))
					return; // Invalid time. Not compatible with `strtotime()`.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".
				       " WHERE `time` < '".esc_sql($exp_time)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
			}

			/**
			 * Cleanup queue event log entries.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_clean_queue_event_log_entries()
			{
				if($this->is_out_of_time())
					return; // Not enough time.

				if(!$this->plugin->options['queue_event_log_expiration_time'])
					return; // Not applicable; functionality disabled.

				if(!($exp_time = strtotime('-'.$this->plugin->options['queue_event_log_expiration_time'])))
					return; // Invalid time. Not compatible with `strtotime()`.

				$sql = "DELETE FROM `".esc_sql($this->plugin->utils_db->prefix().'queue_event_log')."`".
				       " WHERE `time` < '".esc_sql($exp_time)."'";

				if($this->plugin->utils_db->wp->query($sql) === FALSE)
					throw new \exception(__('Deletion failure.', $this->plugin->text_domain));
			}

			/**
			 * Out of time yet?
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean TRUE if out of time.
			 */
			protected function is_out_of_time()
			{
				if((time() - $this->start_time) >= ($this->max_time - 5))
					return TRUE; // Out of time.

				return FALSE; // Let's keep cleaning!
			}
		}
	}
}