<?php
/**
 * Sub Cleaner
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sub_cleaner'))
	{
		/**
		 * Sub Cleaner
		 *
		 * @since 14xxxx First documented version.
		 */
		class sub_cleaner extends abstract_base
		{
			/**
			 * @var integer Start time.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $start_time;

			/**
			 * @var integer Total cleaned subs.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $cleaned;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
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
				else $this->max_time = (integer)$this->plugin->options['sub_cleaner_max_time'];

				if($this->max_time < 10) $this->max_time = 10;
				if($this->max_time > 3600) $this->max_time = 3600;

				$this->cleaned = 0; // Initialize.

				$this->prep_cron_job();
				$this->clean_nonexistent_users();
				$this->maybe_clean_unconfirmed_expirations();
				$this->maybe_clean_trashed_expirations();
			}

			/**
			 * Total subs cleaned.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function cleaned()
			{
				return $this->cleaned;
			}

			/**
			 * Prep CRON job.
			 *
			 * @since 14xxxx First documented version.
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
			 * Cleanup nonexistent users.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @note This does NOT cover multisite `capabilities`.
			 *    That's intentional. There is too much room for error in that case.
			 *    We have `wpmu_delete_user` and `remove_user_from_blog` hooks for this anyway.
			 *    We also have a `delete_user` hook too, for normal WP installs.
			 *
			 *    This routine is just here to help keep things extra tidy on normal WP installs.
			 */
			protected function clean_nonexistent_users()
			{
				if($this->is_out_of_time())
					return; // Not enough time.

				$user_ids = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->wp->users)."`";

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".
				       " WHERE `user_id` != '0' AND `user_id` NOT IN(".$user_ids.")";

				if(($ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->cleaned += $this->plugin->utils_sub->bulk_delete($ids, array('cleaning' => TRUE));
			}

			/**
			 * Cleanup unconfirmed subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_clean_unconfirmed_expirations()
			{
				if($this->is_out_of_time())
					return; // Not enough time.

				if(!$this->plugin->options['unconfirmed_expiration_time'])
					return; // Not applicable; functionality disabled.

				if(!($exp_time = strtotime('-'.$this->plugin->options['unconfirmed_expiration_time'])))
					return; // Invalid time. Not compatible with `strtotime()`.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `status` = 'unconfirmed'".
				       " AND `last_update_time` < '".esc_sql($exp_time)."'";

				if(($ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->cleaned += $this->plugin->utils_sub->bulk_delete($ids, array('cleaning' => TRUE));
			}

			/**
			 * Cleanup trashed subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_clean_trashed_expirations()
			{
				if($this->is_out_of_time())
					return; // Not enough time.

				if(!$this->plugin->options['trashed_expiration_time'])
					return; // Not applicable; functionality disabled.

				if(!($exp_time = strtotime('-'.$this->plugin->options['trashed_expiration_time'])))
					return; // Invalid time. Not compatible with `strtotime()`.

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

				       " WHERE `status` = 'trashed'".
				       " AND `last_update_time` < '".esc_sql($exp_time)."'";

				if(($ids = $this->plugin->utils_db->wp->get_col($sql)))
					$this->cleaned += $this->plugin->utils_sub->bulk_delete($ids, array('cleaning' => TRUE));
			}

			/**
			 * Out of time yet?
			 *
			 * @since 14xxxx First documented version.
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