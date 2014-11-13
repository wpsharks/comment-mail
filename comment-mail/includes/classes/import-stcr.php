<?php
/**
 * StCR Importer
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\import_stcr'))
	{
		/**
		 * StCR Importer
		 *
		 * @since 141111 First documented version.
		 */
		class import_stcr extends abs_base
		{
			/**
			 * @var integer Max number of post IDs.
			 *
			 * @since 141111 First documented version.
			 */
			protected $max_post_ids_limit;

			/**
			 * @var array Unimported post IDs.
			 *
			 * @since 141111 First documented version.
			 */
			protected $unimported_post_ids;

			/**
			 * @var array Imported post IDs.
			 *
			 * @since 141111 First documented version.
			 */
			protected $imported_post_ids;

			/**
			 * @var integer Total imported post IDs.
			 *
			 * @since 141111 First documented version.
			 */
			protected $total_imported_post_ids;

			/**
			 * @var integer Total imported subs.
			 *
			 * @since 141111 First documented version.
			 */
			protected $total_imported_subs;

			/**
			 * @var boolean Has more posts to import?
			 *
			 * @since 141111 First documented version.
			 */
			protected $has_more_posts_to_import;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 */
			public function __construct(array $request_args = array())
			{
				parent::__construct();

				$default_request_args = array(
					'max_post_ids_limit' => 15,
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->max_post_ids_limit = (integer)$request_args['max_post_ids_limit'];

				if($this->max_post_ids_limit < 1) // Too low?
					$this->max_post_ids_limit = 1; // At least one.

				$upper_max_post_ids_limit = (integer)apply_filters(__CLASS__.'_upper_max_post_ids_limit', 1000);
				if($this->max_post_ids_limit > $upper_max_post_ids_limit)
					$this->max_post_ids_limit = $upper_max_post_ids_limit;

				$this->has_more_posts_to_import = FALSE; // Initialize.
				$this->unimported_post_ids      = $this->unimported_post_ids($this->max_post_ids_limit + 1);

				if(count($this->unimported_post_ids) > $this->max_post_ids_limit)
				{
					$this->has_more_posts_to_import = TRUE; // Yes, there are more to import later.
					$this->unimported_post_ids      = array_slice($this->unimported_post_ids, 0, $this->max_post_ids_limit);
				}
				$this->imported_post_ids       = array(); // Initialize.
				$this->total_imported_post_ids = $this->total_imported_subs = 0;

				$this->maybe_import(); // Handle importation.
			}

			/**
			 * Import processor.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_import()
			{
				if(!current_user_can($this->plugin->cap))
					return; // Unauthenticated; ignore.

				foreach($this->unimported_post_ids as $_post_id)
				{
					$this->total_imported_post_ids++;
					$this->imported_post_ids[] = $_post_id;

					$this->mark_post_imported($_post_id);
					$this->maybe_import_post($_post_id);
				}
				unset($_post_id); // Housekeeping.

				$this->output_status();
			}

			/**
			 * Mark as imported post ID.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id Post ID.
			 */
			protected function mark_post_imported($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return; // Nothing to do.

				update_post_meta($post_id, __NAMESPACE__.'_imported_stcr_subs', '1');
			}

			/**
			 * Post import processor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id Post ID.
			 */
			protected function maybe_import_post($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				if(!($stcr_subs = $this->stcr_subs_for_post($post_id)))
					return; // No StCR subscribers.

				foreach($stcr_subs as $_email => $_sub)
					$this->maybe_import_sub($post_id, $_sub);
				unset($_email, $_sub); // Housekeeping.
			}

			/**
			 * Sub. import processor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer   $post_id Post ID.
			 * @param \stdClass $sub Subscriber obj. data.
			 */
			protected function maybe_import_sub($post_id, \stdClass $sub)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				if(empty($sub->email) || empty($sub->time) || empty($sub->status))
					return; // Not possible; data missing.

				if($sub->status !== 'Y' && $sub->status !== 'R')
					return; // Not an active subscriber.

				if($sub->status === 'Y') // All comments?
				{
					$sub_insert_data = array(
						'post_id' => $post_id,

						'status'  => 'subscribed',
						'deliver' => 'asap',

						'fname'   => $sub->fname,
						'email'   => $sub->email,
					);
					$sub_inserter    = new sub_inserter($sub_insert_data);
					if($sub_inserter->did_insert()) $this->total_imported_subs++;
				}
				# Otherwise, specific comment(s) only; i.e. "Replies Only".

				foreach($this->sub_comment_ids($post_id, $sub->email) as $_comment_id)
				{
					$_sub_insert_data = array(
						'post_id'    => $post_id,
						'comment_id' => $_comment_id,

						'status'     => 'subscribed',
						'deliver'    => 'asap',

						'fname'      => $sub->fname,
						'email'      => $sub->email,
					);
					$_sub_inserter    = new sub_inserter($_sub_insert_data);
					if($_sub_inserter->did_insert()) $this->total_imported_subs++;
				}
				unset($_comment_id, $_sub_insert_data, $_sub_inserter); // Housekeeping.
			}

			/**
			 * Collect all StCR subscribers for a given post ID.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id Subscribers for which post ID.
			 *
			 * @return \stdClass[] Array of objects; i.e. StCR subscribers for the post ID.
			 *
			 *    Each object in the array will contain the following properties.
			 *
			 *    - `(string)fname` The subscriber's first name (based on email address).
			 *
			 *    - `(string)email` The subscriber's email address (lowercase).
			 *          Note: each key in the array is also indexed by this email address.
			 *
			 *    - `(integer)time` The date the subscription was created; converted to a UTC timestamp.
			 *
			 *    - `(string)status` The status of the subscription. One of: `Y|R`.
			 *          A `Y` indicates they want notifications for all comments.
			 *          An `R` indicates they want notifications for replies only.
			 */
			protected function stcr_subs_for_post($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return array(); // Not possible.

				$sql = "SELECT * FROM `".esc_sql($this->plugin->utils_db->wp->postmeta)."`".

				       " WHERE `post_id` = '".esc_sql($post_id)."'".
				       " AND `meta_key` LIKE '%\\_stcr@\\_%'";

				if(!($results = $this->plugin->utils_db->wp->get_results($sql)))
					return array(); // Nothing to do; no results.

				$subs = array(); // Initialize array of subscribers.

				foreach($results as $_result) // Iterate results.
				{
					// Original format: `_stcr@_user@example.com`.
					$_email = preg_replace('/^.*?_stcr@_/i', '', $_result->meta_key);
					$_email = trim(strtolower($_email));

					if(!$_email || strpos($_email, '@', 1) === FALSE || !is_email($_email))
						continue; // Invalid email address.

					// Original format: `2013-03-11 01:31:01|R`.
					if(!$_result->meta_value || strpos($_result->meta_value, '|', 1) === FALSE)
						continue; // Invalid meta data.

					list($_local_datetime, $_status) = explode('|', $_result->meta_value);

					if(!($_time = strtotime($_local_datetime)))
						continue; // Not `strtotime()` compatible.

					if(($_time = $_time + (get_option('gmt_offset') * 3600)) < 1)
						continue; // Unable to convert date to UTC timestamp.

					// Possible statuses: `Y|R|YC|RC|C|-C`.
					// A `Y` indicates they want notifications for all comments.
					// An `R` indicates they want notifications for replies only.
					// A `C` indicates "suspended" or "unconfirmed".
					if($_status !== 'Y' && $_status !== 'R') // Active?
						continue; // Not an active subscriber.

					if(!isset($subs[$_email]) || ($_status === 'R' && $subs[$_email]->status === 'Y'))
						// Give precedence to any subscription that chose to receive "Replies Only".
						// See: <https://github.com/websharks/comment-mail/issues/7#issuecomment-57252200>
						$subs[$_email] = (object)array('fname' => $this->plugin->utils_string->first_name('', $_email),
						                               'email' => $_email, 'time' => $_time, 'status' => $_status);
				}
				unset($_result, $_email, $_local_datetime, $_status); // Housekeeping.

				return $subs; // Subscribers, for this post ID.
			}

			/**
			 * Subscriber's comment IDs.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id Post ID to check.
			 * @param string  $email Email address (i.e. subscriber).
			 *
			 * @return array Subscriber's comment IDs.
			 */
			protected function sub_comment_ids($post_id, $email)
			{
				$comment_ids = array(); // Initialize.

				if(!($post_id = (integer)$post_id) || !($email = (string)$email))
					return $comment_ids; // Not possible; data missing.

				$sql = "SELECT `comment_ID` FROM  `".esc_sql($this->plugin->utils_db->wp->comments)."`".

				       " WHERE  `comment_post_ID` = '".esc_sql($post_id)."'".
				       " AND  `comment_author_email` = '".esc_sql($email)."'".
				       " AND `comment_approved` IN('approve', 'approved', '1')".

				       " ORDER BY `comment_date` ASC"; // Oldest to newest.

				$comment_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql));

				return $comment_ids; // All of their comment IDs.
			}

			/**
			 * Up to `$max_limit` unimported post IDs.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $max_limit Max IDs to return.
			 *
			 * @return array Up to `$max_limit` unimported post IDs.
			 */
			protected function unimported_post_ids($max_limit = 0)
			{
				if(($max_limit = (integer)$max_limit) < 1)
					$max_limit = $this->max_post_ids_limit + 1;

				$post_ids_with_stcr_meta = // Those with StCR metadata.
					"SELECT DISTINCT `post_id` FROM `".esc_sql($this->plugin->utils_db->wp->postmeta)."`".
					" WHERE `meta_key` LIKE '%\\_stcr@\\_%'";

				$post_ids_imported_already = // Those already imported by this class.
					"SELECT DISTINCT `post_id` FROM `".esc_sql($this->plugin->utils_db->wp->postmeta)."`".
					" WHERE `meta_key` = '".esc_sql(__NAMESPACE__.'_imported_stcr_subs')."'";

				$sql = "SELECT `ID` FROM `".esc_sql($this->plugin->utils_db->wp->posts)."`".

				       " WHERE `post_status` = 'publish'". // Published posts only.
				       " AND `post_type` NOT IN('revision', 'nav_menu_item', 'redirect', 'snippet')".

				       " AND `ID` IN (".$post_ids_with_stcr_meta.")".
				       " AND `ID` NOT IN (".$post_ids_imported_already.")".

				       " LIMIT ".$max_limit; // Limit results.

				$post_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql));

				return $post_ids; // Up to `$max_limit` unimported post IDs.
			}

			/**
			 * Output status; for public API use.
			 *
			 * @since 141111 First documented version.
			 */
			protected function output_status()
			{
				$this->plugin->utils_env->prep_for_output();

				status_header(200); // OK status.
				nocache_headers(); // No browser cache.
				header('Content-Type: text/html; charset=UTF-8');

				$child_status_var = // Child identifier.
					str_replace('\\', '_', __CLASS__).'_child_status';

				$child_status_request_args = array(
					$child_status_var => 1, // Child process identifier.
					__NAMESPACE__     => array('import' => array('type' => 'stcr')),
				);
				$child_status_url          = $this->plugin->utils_url->nonce();
				$child_status_url          = add_query_arg(urlencode_deep($child_status_request_args), $child_status_url);

				if(!empty($_REQUEST[$child_status_var]))
					exit($this->child_output_status());
				exit($this->parent_output_status($child_status_url));
			}

			/**
			 * Parent output status.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $child_status_url Child status URL.
			 *
			 * @return string HTML markup for the status.
			 */
			protected function parent_output_status($child_status_url)
			{
				$status = '<!DOCTYPE html>'."\n";
				$status .= '<html>'."\n";

				$status .= '   <head>'."\n";

				$status .= '      <meta charset="UTF-8" />'."\n";
				$status .= '      <title>'.esc_html(__('StCR Importer', $this->plugin->text_domain)).'</title>'."\n";

				$status .= '      <style type="text/css">'."\n";
				$status .= '         body { background: #CCCCCC; color: #000000; }'."\n";
				$status .= '         body { font-size: 13px; line-height: 1em; font-family: sans-serif; }'."\n";
				$status .= '         body { padding: .5em; text-align: center; }'."\n";
				$status .= '      </style>'."\n";

				$status .= '      <script type="text/javascript"'. // jQuery dependency.
				           '         src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js">'.
				           '      </script>'."\n";

				$status .= '      <script type="text/javascript">'."\n";
				$status .= '         function updateCounters(childTotalPostIds, childTotalSubs)'."\n".
				           '            {'."\n".
				           '               var $totalImportedPostIds = $("#total-imported-post-ids");'."\n".
				           '               var $totalImportedSubs = $("#total-imported-subs");'."\n".

				           '               $totalImportedPostIds.html(Number($totalImportedPostIds.text()) + Number(childTotalPostIds));'."\n".
				           '               $totalImportedSubs.html(Number($totalImportedSubs.text()) + Number(childTotalSubs));'."\n".
				           '            }'."\n";
				$status .= '         function importComplete()'."\n".
				           '            {'."\n".
				           '               $("#importing").remove();'."\n". // Removing importing div/animation.
				           '               $("body").append("<div><strong>'.__('Import complete!', $this->plugin->text_domain).'</strong></div>");'."\n".
				           '            }'."\n";
				$status .= '      </script>'."\n";

				$status .= '   </head>'."\n"; // End `<head>`.

				$status .= '   <body>'."\n"; // Main output status.

				if($this->has_more_posts_to_import) // Import will contiue w/ child processes?
					$status .= '   <div id="importing">'.
					           '      <strong>'.__('Importing StCR Subscribers', $this->plugin->text_domain).'</strong>'.
					           '       &nbsp;&nbsp; <img src="'.esc_html($this->plugin->utils_url->to('/client-s/images/tiny-progress-bar.gif')).'"'.
					           '                        style="width:16px; height:11px; border:0; vertical-align:middle;" />'.
					           '   </div>'."\n";

				$status .= '      <code id="total-imported-post-ids">'.esc_html($this->total_imported_post_ids).'</code> '.__('post IDs', $this->plugin->text_domain).';'.
				           '      <code id="total-imported-subs">'.esc_html($this->total_imported_subs).'</code> '.__('subscriptions', $this->plugin->text_domain).'.'."\n";

				if($this->has_more_posts_to_import) // Import will contiue w/ child processes?
					$status .= '   <iframe src="'.esc_attr((string)$child_status_url).'" style="width:1px; height:1px; border:0; visibility:hidden;"></iframe>';
				else $status .= ' <div><strong>'.__('Import complete!', $this->plugin->text_domain).'</strong></div>';

				$status .= '   </body>'."\n";

				$status .= '</html>';

				return $status; // HTML markup.
			}

			/**
			 * Child output status.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string HTML markup for the status.
			 */
			protected function child_output_status()
			{
				$status = '<!DOCTYPE html>'."\n";
				$status .= '<html>'."\n";

				$status .= '   <head>'."\n";

				$status .= '      <title>...</title>'."\n";
				$status .= '      <meta charset="UTF-8" />'."\n";

				$status .= '      <script type="text/javascript">'."\n";
				$status .= '         parent.updateCounters('.$this->total_imported_post_ids.', '.$this->total_imported_subs.');'."\n";
				$status .= '      </script>'."\n";

				if($this->has_more_posts_to_import)
					$status .= '   <meta http-equiv="refresh" content="1" />';

				else // Import complete; signal the parent output status window.
				{
					$status .= '   <script type="text/javascript">'."\n";
					$status .= '      parent.importComplete();'."\n";
					$status .= '   </script>'."\n";
				}
				$status .= '   </head>'."\n"; // End `<head>`.

				$status .= '   <body>'."\n"; // Child output status.
				$status .= '   </body>'."\n";

				$status .= '</html>';

				return $status; // HTML markup.
			}

			/**
			 * StCR data exists?
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean `TRUE` if StCR data exists.
			 */
			public static function data_exists()
			{
				$plugin = plugin(); // Need this below.

				$sql = "SELECT `meta_id` FROM `".esc_sql($plugin->utils_db->wp->postmeta)."`".
				       " WHERE `meta_key` LIKE '%\\_stcr@\\_%' LIMIT 1";

				return (boolean)$plugin->utils_db->wp->get_var($sql);
			}

			/**
			 * Ever done an StCR import?
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean `TRUE` if ever done an StCR import.
			 */
			public static function ever_imported()
			{
				$plugin = plugin(); // Need this below.

				$like = // e.g. LIKE `%comment\_mail\_imported\_stcr\_subs%`.
					'%'.$plugin->utils_db->wp->esc_like(__NAMESPACE__.'_imported_stcr_subs').'%';

				$sql = "SELECT `meta_id` FROM `".esc_sql($plugin->utils_db->wp->postmeta)."`".
				       " WHERE `meta_key` LIKE '".esc_sql($like)."' LIMIT 1";

				return (boolean)$plugin->utils_db->wp->get_var($sql);
			}
		}
	}
}