<?php
/**
 * Chart Data; for Stats
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\chart_data'))
	{
		/**
		 * Chart Data; for Stats
		 *
		 * @since 141111 First documented version.
		 */
		class chart_data extends abs_base
		{
			/**
			 * @var string Input view.
			 *
			 * @since 141111 First documented version.
			 */
			protected $input_view;

			/**
			 * @var string Current view.
			 *
			 * @since 141111 First documented version.
			 */
			protected $view;

			/**
			 * @var \stdClass Chart specs.
			 *
			 * @since 141111 First documented version.
			 */
			protected $chart;

			/**
			 * @var array Any errors.
			 *
			 * @since 141111 First documented version.
			 */
			protected $errors;

			/**
			 * @var array Charts colors.
			 *
			 * @since 141111 First documented version.
			 */
			protected $colors = array(
				'fillColor'       => '#339E2B',
				'strokeColor'     => '#194F16',
				'highlightFill'   => '#346098',
				'highlightStroke' => '#172C48',
			);

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $request_args Arguments to the constructor.
			 *    These should NOT be trusted; they come from a `$_REQUEST` action.
			 *
			 * @throws \exception If a security flag is triggered on `$this->data_file`.
			 */
			public function __construct(array $request_args = array())
			{
				parent::__construct();

				$default_request_args = array(
					'view'                => '',

					'type'                => '',
					'post_id'             => '',
					'user_initiated_only' => FALSE,

					'from'                => '',
					'to'                  => '',

					'by'                  => '',
				);
				$request_args         = array_merge($default_request_args, $request_args);
				$request_args         = array_intersect_key($request_args, $default_request_args);

				$this->input_view = $this->view = trim(strtolower((string)$request_args['view']));
				if($this->input_view === 'subs_overview_by_post_id')
					$this->view = 'subs_overview';

				$this->chart = new \stdClass; // Object properties.

				$this->chart->type                = trim((string)$request_args['type']);
				$this->chart->post_id             = (integer)$request_args['post_id'];
				$this->chart->user_initiated_only = (boolean)$request_args['user_initiated_only'];

				$this->chart->from_time = $this->plugin->utils_string->trim((string)$request_args['from'], '', ',;');
				$this->chart->to_time   = $this->plugin->utils_string->trim((string)$request_args['to'], '', ',;');

				$this->chart->by = trim(strtolower((string)$request_args['by']));

				$this->errors = array(); // Initialize.

				$this->maybe_output();
			}

			/**
			 * Chart data output; in JSON format.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_output()
			{
				if(!current_user_can($this->plugin->manage_cap))
					if(!current_user_can($this->plugin->cap))
						return; // Unauthenticated; ignore.

				if($this->chart_is_valid() && !$this->errors)
					echo json_encode($this->{$this->view.'_'}());

				else if($this->errors) // Return `errors` property w/ markup.
					echo json_encode(array('errors' => $this->errors_markup()));
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview_()
			{
				return $this->{__FUNCTION__.'_'.$this->chart->type}();
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data; for ChartJS.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview__subscribed_totals()
			{
				$labels = $data = array(); // Initialize.

				foreach($this->chart->time_periods as $_time_period)
					$labels[] = $_time_period['from_label'].' - '.$_time_period['to_label'];
				unset($_time_period); // Housekeeping.

				foreach($this->chart->time_periods as $_time_period)
				{
					$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`". // Calc enable.
					       " FROM `".esc_sql($this->plugin->utils_db->prefix().'subs')."`".

					       " WHERE 1=1". // Initialize where clause.

					       ($this->chart->post_id // Specific post ID?
						       ? " AND `post_id` = '".esc_sql($this->chart->post_id)."'" : '').

					       " AND `status` IN('subscribed')".

					       " AND `insertion_time`". // In this time period only.
					       "       BETWEEN '".esc_sql($_time_period['from_time'])."'".
					       "          AND '".esc_sql($_time_period['to_time'])."'".

					       " LIMIT 1"; // Only need one to check.

					if($this->plugin->utils_db->wp->query($sql) === FALSE)
						throw new \exception(__('Query failure.', $this->plugin->text_domain));

					$data[] = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()");
				}
				unset($_time_period); // Housekeeping.

				return array('data'    => array('labels'   => $labels,
				                                'datasets' => array(
					                                array_merge($this->colors, array(
						                                'label' => __('Actual/Current Subscr. Totals', $this->plugin->text_domain),
						                                'data'  => $data,
					                                )),
				                                )),
				             'options' => array(
					             'scaleLabel'      => '<%=value%>',
					             'tooltipTemplate' => '<%if (label){%><%=label%>: <%}%><%= value %> '.__('subscriptions', $this->plugin->text_domain),
				             ));
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data; for ChartJS.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview__event_subscribed_totals()
			{
				$labels = $data = array(); // Initialize.

				foreach($this->chart->time_periods as $_time_period)
					$labels[] = $_time_period['from_label'].' - '.$_time_period['to_label'];
				unset($_time_period); // Housekeeping.

				foreach($this->chart->time_periods as $_time_period)
				{
					$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`". // Calc enable.
					       " FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

					       " WHERE 1=1". // Initialize where clause.

					       ($this->chart->post_id // Specific post ID?
						       ? " AND `post_id` = '".esc_sql($this->chart->post_id)."'" : '').

					       " AND `event` IN('inserted', 'updated')".

					       " AND `status` IN('subscribed')".
					       " AND `status_before` IN('', 'unconfirmed')".

					       ($this->chart->user_initiated_only // User initiated only?
						       ? " AND `user_initiated` > '0'" : '').

					       " AND `time`". // In this time period only.
					       "       BETWEEN '".esc_sql($_time_period['from_time'])."'".
					       "          AND '".esc_sql($_time_period['to_time'])."'".

					       " GROUP BY `sub_id`". // Unique subs only.

					       " LIMIT 1"; // Only need one to check.

					if($this->plugin->utils_db->wp->query($sql) === FALSE)
						throw new \exception(__('Query failure.', $this->plugin->text_domain));

					$data[] = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()");
				}
				unset($_time_period); // Housekeeping.

				return array('data'    => array('labels'   => $labels,
				                                'datasets' => array(
					                                array_merge($this->colors, array(
						                                'label' => __('Subscr. Totals (Based on Event Logs)', $this->plugin->text_domain),
						                                'data'  => $data,
					                                )),
				                                )),
				             'options' => array(
					             'scaleLabel'      => '<%=value%>',
					             'tooltipTemplate' => '<%if (label){%><%=label%>: <%}%><%= value %> '.__('subscriptions', $this->plugin->text_domain),
				             ));
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data; for ChartJS.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview__event_confirmation_totals()
			{
				$labels = $data = array(); // Initialize.

				foreach($this->chart->time_periods as $_time_period)
					$labels[] = $_time_period['from_label'].' - '.$_time_period['to_label'];
				unset($_time_period); // Housekeeping.

				foreach($this->chart->time_periods as $_time_period)
				{
					$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`". // Calc enable.
					       " FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

					       " WHERE 1=1". // Initialize where clause.

					       ($this->chart->post_id // Specific post ID?
						       ? " AND `post_id` = '".esc_sql($this->chart->post_id)."'" : '').

					       " AND `event` IN('inserted','updated')".

					       " AND `status` IN('subscribed')".
					       " AND `status_before` IN('','unconfirmed')".

					       ($this->chart->user_initiated_only // User initiated only?
						       ? " AND `user_initiated` > '0'" : '').

					       " AND `time`". // In this time period only.
					       "       BETWEEN '".esc_sql($_time_period['from_time'])."'".
					       "          AND '".esc_sql($_time_period['to_time'])."'".

					       " GROUP BY `sub_id`". // Unique subs only.

					       " LIMIT 1"; // Only need one to check.

					if($this->plugin->utils_db->wp->query($sql) === FALSE)
						throw new \exception(__('Query failure.', $this->plugin->text_domain));

					$data[] = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()");
				}
				unset($_time_period); // Housekeeping.

				return array('data'    => array('labels'   => $labels,
				                                'datasets' => array(
					                                array_merge($this->colors, array(
						                                'label' => __('Confirmation Totals (Based on Event Logs)', $this->plugin->text_domain),
						                                'data'  => $data,
					                                )),
				                                )),
				             'options' => array(
					             'scaleLabel'      => '<%=value%>',
					             'tooltipTemplate' => '<%if (label){%><%=label%>: <%}%><%= value %> '.__('subscriptions', $this->plugin->text_domain),
				             ));
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data; for ChartJS.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview__event_unsubscribe_totals()
			{
				$labels = $data = array(); // Initialize.

				foreach($this->chart->time_periods as $_time_period)
					$labels[] = $_time_period['from_label'].' - '.$_time_period['to_label'];
				unset($_time_period); // Housekeeping.

				foreach($this->chart->time_periods as $_time_period)
				{
					$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`". // Calc enable.
					       " FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

					       " WHERE 1=1". // Initialize where clause.

					       ($this->chart->post_id // Specific post ID?
						       ? " AND `post_id` = '".esc_sql($this->chart->post_id)."'" : '').

					       " AND `event` IN('updated','deleted')".

					       " AND `status` IN('trashed','deleted')".
					       " AND `status_before` IN('subscribed','suspended')".

					       ($this->chart->user_initiated_only // User initiated only?
						       ? " AND `user_initiated` > '0'" : '').

					       " AND `time`". // In this time period only.
					       "       BETWEEN '".esc_sql($_time_period['from_time'])."'".
					       "          AND '".esc_sql($_time_period['to_time'])."'".

					       " GROUP BY `sub_id`". // Unique subs only.

					       " LIMIT 1"; // Only need one to check.

					if($this->plugin->utils_db->wp->query($sql) === FALSE)
						throw new \exception(__('Query failure.', $this->plugin->text_domain));

					$data[] = (integer)$this->plugin->utils_db->wp->get_var("SELECT FOUND_ROWS()");
				}
				unset($_time_period); // Housekeeping.

				return array('data'    => array('labels'   => $labels,
				                                'datasets' => array(
					                                array_merge($this->colors, array(
						                                'label' => __('Unsubscribe Totals (Based on Event Logs)', $this->plugin->text_domain),
						                                'data'  => $data,
					                                )),
				                                )),
				             'options' => array(
					             'scaleLabel'      => '<%=value%>',
					             'tooltipTemplate' => '<%if (label){%><%=label%>: <%}%><%= value %> '.__('subscriptions', $this->plugin->text_domain),
				             ));
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data; for ChartJS.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview__event_subscribed_most_popular_posts()
			{
				$labels = $data = array(); // Initialize.

				$sql = "SELECT COUNT(*) AS `total_subs`, `post_id`".
				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

				       " WHERE 1=1". // Initialize where clause.

				       " AND `post_id` > '0'".

				       " AND `event` IN('inserted', 'updated')".

				       " AND `status` IN('subscribed')".
				       " AND `status_before` IN('', 'unconfirmed')".

				       ($this->chart->user_initiated_only // User initiated only?
					       ? " AND `user_initiated` > '0'" : '').

				       " AND `time`". // In this time period only.
				       "       BETWEEN '".esc_sql($this->chart->from_time)."'".
				       "          AND '".esc_sql($this->chart->to_time)."'".

				       " GROUP BY `post_id`, `sub_id`".

				       " ORDER BY `total_subs` DESC".

				       " LIMIT 25";

				if(($results = $this->plugin->utils_db->wp->get_results($sql)))
					foreach(($results = $this->plugin->utils_db->typify_deep($results)) as $_result)
					{
						$_result_post       = get_post($_result->post_id);
						$_result_post_title = $_result_post ? ' — '.$this->plugin->utils_string->clip($_result_post->post_title, 20) : '';

						$labels[] = sprintf(__('Post ID #%1$s%2$s', $this->plugin->text_domain), $_result->post_id, $_result_post_title);
						$data[]   = (integer)$_result->total_subs;
					}
				unset($_result, $_result_post, $_result_post_title); // Housekeeping.

				if(empty($labels)) $labels[] = '—'; // Must have something.
				if(empty($data)) $data[] = 0; // Must have something.

				return array('data'    => array('labels'   => $labels,
				                                'datasets' => array(
					                                array_merge($this->colors, array(
						                                'label' => __('Subscr. Totals (Based on Event Logs)', $this->plugin->text_domain),
						                                'data'  => $data,
					                                )),
				                                )),
				             'options' => array(
					             'scaleLabel'      => '<%=value%>',
					             'tooltipTemplate' => '<%if (label){%><%=label%>: <%}%><%= value %> '.__('subscriptions', $this->plugin->text_domain),
				             ));
			}

			/**
			 * Chart data for a particular view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return array An array of all chart data; for ChartJS.
			 *
			 * @throws \exception If there is a query failure.
			 */
			protected function subs_overview__event_subscribed_least_popular_posts()
			{
				$labels = $data = array(); // Initialize.

				$sql = "SELECT COUNT(*) AS `total_subs`, `post_id`".
				       " FROM `".esc_sql($this->plugin->utils_db->prefix().'sub_event_log')."`".

				       " WHERE 1=1". // Initialize where clause.

				       " AND `post_id` > '0'".

				       " AND `event` IN('inserted', 'updated')".

				       " AND `status` IN('subscribed')".
				       " AND `status_before` IN('', 'unconfirmed')".

				       ($this->chart->user_initiated_only // User initiated only?
					       ? " AND `user_initiated` > '0'" : '').

				       " AND `time`". // In this time period only.
				       "       BETWEEN '".esc_sql($this->chart->from_time)."'".
				       "          AND '".esc_sql($this->chart->to_time)."'".

				       " GROUP BY `post_id`, `sub_id`".

				       " ORDER BY `total_subs` ASC".

				       " LIMIT 25";

				if(($results = $this->plugin->utils_db->wp->get_results($sql)))
					foreach(($results = $this->plugin->utils_db->typify_deep($results)) as $_result)
					{
						$_result_post       = get_post($_result->post_id);
						$_result_post_title = $_result_post ? ' — '.$this->plugin->utils_string->clip($_result_post->post_title, 20) : '';

						$labels[] = sprintf(__('Post ID #%1$s%2$s', $this->plugin->text_domain), $_result->post_id, $_result_post_title);
						$data[]   = (integer)$_result->total_subs;
					}
				unset($_result, $_result_post, $_result_post_title); // Housekeeping.

				if(empty($labels)) $labels[] = '—'; // Must have something.
				if(empty($data)) $data[] = 0; // Must have something.

				return array('data'    => array('labels'   => $labels,
				                                'datasets' => array(
					                                array_merge($this->colors, array(
						                                'label' => __('Subscr. Totals (Based on Event Logs)', $this->plugin->text_domain),
						                                'data'  => $data,
					                                )),
				                                )),
				             'options' => array(
					             'scaleLabel'      => '<%=value%>',
					             'tooltipTemplate' => '<%if (label){%><%=label%>: <%}%><%= value %> '.__('subscriptions', $this->plugin->text_domain),
				             ));
			}

			/**
			 * Validates chart data.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return boolean `TRUE` if chart data validates.
			 */
			protected function chart_is_valid()
			{
				if(!$this->view || !method_exists($this, $this->view.'_'))
					$this->errors[] = __('Invalid Chart View.Please try again.', $this->plugin->text_domain);

				if(!method_exists($this, $this->view.'__'.$this->chart->type))
					$this->errors[] = __('Missing or invalid Chart Type. Please try again.', $this->plugin->text_domain);

				if($this->input_view === 'subs_overview_by_post_id' && $this->chart->post_id <= 0)
					$this->errors[] = __('Missing or invalid Post ID. Please try again.', $this->plugin->text_domain);

				if(!$this->chart->from_time || !$this->chart->to_time)
					$this->errors[] = __('Missing or invalid Date(s). Please try again.', $this->plugin->text_domain);

				if(!in_array($this->chart->by, array('hours', 'days', 'weeks', 'months', 'years'), TRUE))
					$this->errors[] = __('Missing or invalid Breakdown. Please try again.', $this->plugin->text_domain);

				if(!$this->errors) $this->parse_times_setup_periods(); // Times/periods.

				if(!$this->errors) // If no errors thus far, let's do one last on the times.
				{
					if(!$this->chart->from_time || !$this->chart->to_time)
						$this->errors[] = __('Missing or invalid Date(s). Please try again.', $this->plugin->text_domain);

					else if($this->chart->from_time >= $this->chart->to_time)
						$this->errors[] = __('From Date >= To Date. Please try again.', $this->plugin->text_domain);

					else if(empty($this->chart->time_periods))
						$this->errors[] = __('Not enough data for that time period and/or Breakdown. Please try again.', $this->plugin->text_domain);

					else if(count($this->chart->time_periods) > ($time_periods_max_limit = apply_filters(__CLASS__.'_time_periods_max_limit', 100)))
						$this->errors[] = sprintf(__('Too many time periods needed. Please try again. Based on your configuration of this chart, there would need to be more than `%1$s` bars to represent the data that you want. This would require _many_ DB queries, and it would be very difficult to read the chart. Please broaden your Breakdown or reduce the difference between From Date and To Date.', $this->plugin->text_domain), $time_periods_max_limit);
				}
				return empty($this->errors); // If no errors we're good-to-go!
			}

			/**
			 * Chart time periods.
			 *
			 * @since 141111 First documented version.
			 */
			protected function parse_times_setup_periods()
			{
				# Parse "from" time as a local timestamp.

				$local_relative_from_time_base = // GMT offset base.
					time() + (get_option('gmt_offset') * 3600);

				$this->chart->from_time = // Convert to timestamp; i.e. parse string.
					(integer)strtotime($this->chart->from_time, $local_relative_from_time_base);

				# Parse "to" time as a local timestamp.

				if($this->chart->from_time) // Only possible if we got a valid "from" time.
				{
					$local_relative_to_time_base =// GMT offset base; with one exception for the word `now`.
						preg_match('/^now$/', $this->chart->to_time) ? time() + (get_option('gmt_offset') * 3600)
							: $this->chart->from_time; // Else use current local "from" time as the base.

					$this->chart->to_time = (integer)strtotime($this->chart->to_time, $local_relative_to_time_base);
				}
				else $this->chart->to_time = 0; // Cannot use this if the "from" time is incorrect.

				# Invalid times before we even begin? e.g. One of the `strtotime()` calls choked above?

				if(!$this->chart->from_time || !$this->chart->to_time)
					$this->errors[] = __('Missing or invalid Date(s). Please try again.', $this->plugin->text_domain);

				else if($this->chart->from_time >= $this->chart->to_time)
					$this->errors[] = __('From Date >= To Date. Please try again.', $this->plugin->text_domain);

				if($this->errors) return; // Nothing more we can do here.

				/* ---------------------------------------------------------- */

				# Begin date rounding; and also establish time period calculation variables.

				switch($this->chart->by)
				{
					case 'hours': // Breakdown by hours?

						$by_seconds = 3600;
						$by_format  = 'M jS, Y @ g:i a';

						$this->chart->from_time = strtotime(date('Y-m-d H', $this->chart->from_time).':00');
						$this->chart->to_time   = strtotime(date('Y-m-d H', $this->chart->to_time).':59');

						$current_local_year = date('Y', time() + (get_option('gmt_offset') * 3600));
						if(date('Y', $this->chart->from_time) === $current_local_year)
							if(date('Y', $this->chart->to_time) === $current_local_year)
								$by_format = str_replace(', Y', '', $by_format);

						break; // Break switch handler.

					case 'days': // Breakdown by days?

						$by_seconds = 86400;
						$by_format  = 'M jS, Y @ g:i a';

						$this->chart->from_time = strtotime(date('Y-m-d', $this->chart->from_time).' 00:00');
						$this->chart->to_time   = strtotime(date('Y-m-d', $this->chart->to_time).' 23:59');

						$current_local_year = date('Y', time() + (get_option('gmt_offset') * 3600));
						if(date('Y', $this->chart->from_time) === $current_local_year)
							if(date('Y', $this->chart->to_time) === $current_local_year)
								$by_format = str_replace(', Y', '', $by_format);

						break; // Break switch handler.

					case 'weeks': // Breakdown by weeks?

						$by_seconds = 604800;
						$by_format  = 'D M jS, Y';

						if(strcasecmp(date('D', $this->chart->from_time), 'sun') === 0)
							$from_last_sunday = $this->chart->from_time;
						else $from_last_sunday = strtotime('last Sunday', $this->chart->from_time);

						$this->chart->from_time = strtotime(date('Y-m-d', $from_last_sunday).' 00:00');

						if(strcasecmp(date('D', $this->chart->to_time), 'sat') === 0)
							$to_next_saturday = $this->chart->to_time;
						else $to_next_saturday = strtotime('next Saturday', $this->chart->to_time);

						$this->chart->to_time = strtotime(date('Y-m-d', $to_next_saturday).' 23:59');

						$current_local_year = date('Y', time() + (get_option('gmt_offset') * 3600));
						if(date('Y', $this->chart->from_time) === $current_local_year)
							if(date('Y', $this->chart->to_time) === $current_local_year)
								$by_format = str_replace(', Y', '', $by_format);

						break; // Break switch handler.

					case 'months': // Breakdown by months?

						$by_seconds = 2592000;
						$by_format  = 'M jS, Y';

						$this->chart->from_time = strtotime(date('Y-m', $this->chart->from_time).'-01 00:00');

						$to_month             = date('n', $this->chart->to_time);
						$to_year              = date('Y', $this->chart->to_time);
						$cal_days_in_to_month = cal_days_in_month(CAL_GREGORIAN, $to_month, $to_year);
						$cal_days_in_to_month = str_pad($cal_days_in_to_month, 2, '0', STR_PAD_LEFT);

						$this->chart->to_time = strtotime(date('Y-m', $this->chart->to_time).'-'.$cal_days_in_to_month.' 23:59');

						$current_local_year = date('Y', time() + (get_option('gmt_offset') * 3600));
						if(date('Y', $this->chart->from_time) === $current_local_year)
							if(date('Y', $this->chart->to_time) === $current_local_year)
								$by_format = str_replace(', Y', '', $by_format);

						break; // Break switch handler.

					case 'years': // Breakdown by years?

						$by_seconds = 31536000;
						$by_format  = 'M Y';

						$this->chart->from_time = strtotime(date('Y', $this->chart->from_time).'-01-01 00:00');
						$this->chart->to_time   = strtotime(date('Y', $this->chart->to_time).'-12-31 23:59');

						break; // Break switch handler.

					default: // Unexpected breakdown "by" syntax?
						throw new \exception(__('Unexcpected Breakdown.', $this->plugin->text_domain));
				}
				# Invalid times after adjustments/rounding above?

				if(!$this->chart->from_time || !$this->chart->to_time)
					$this->errors[] = __('Missing or invalid Date(s). Please try again.', $this->plugin->text_domain);

				else if($this->chart->from_time >= $this->chart->to_time)
					$this->errors[] = __('From Date >= To Date. Please try again.', $this->plugin->text_domain);

				if($this->errors) return; // Nothing more we can do here.

				# Now let's convert the local times into UTC times.

				$this->chart->from_time -= get_option('gmt_offset') * 3600;
				$this->chart->to_time -= get_option('gmt_offset') * 3600;

				# Construct time periods based on "from" and "to" now.

				$_this            = $this; // Reference needed for this closure.
				$time_offset_bump = function ($time_offset) use ($_this, $by_seconds)
				{
					if($by_seconds !== 2592000)
						return $by_seconds;

					$current_month             = date('n', $_this->chart->from_time + $time_offset);
					$current_year              = date('Y', $_this->chart->from_time + $time_offset);
					$cal_days_in_current_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

					return $cal_days_in_current_month * 86400;
				};
				for($_period = 0, $_time_offset = 0;
				    $this->chart->from_time + $_time_offset + $time_offset_bump($_time_offset) <= $this->chart->to_time;
				    $_period++, $_time_offset += $time_offset_bump($_time_offset))
				{
					$this->chart->time_periods[$_period] = array(
						'from_time'  => $this->chart->from_time + $_time_offset,
						'from_label' => $this->plugin->utils_date->i18n($by_format, $this->chart->from_time + $_time_offset),

						'to_time'    => $this->chart->from_time + $_time_offset + $time_offset_bump($_time_offset) - 1,
						'to_label'   => $this->plugin->utils_date->i18n($by_format, $this->chart->from_time + $_time_offset + $time_offset_bump($_time_offset) - 1),
					);
				}
				unset($_period, $_time_offset); // Housekeeping.
			}

			/**
			 * Markup for display of errors.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Markup for errors display.
			 */
			protected function errors_markup()
			{
				$errors_html = // Convert all errors to HTML markup.
					array_map(array($this->plugin->utils_string, 'markdown_no_p'), $this->errors);

				return '<div class="pmp-note pmp-error" style="margin:1em 0 0 0;">'.

				       ' <p style="margin:0 0 .5em 0; font-weight:bold;">'.
				       '    <i class="fa fa-warning"></i> '.__('Please review the following error(s):', $this->plugin->text_domain).
				       ' </p>'.

				       ' <ul class="pmp-list-items" style="margin-top:0; margin-bottom:0;">'.
				       '    <li>'.implode('</li><li>', $errors_html).'</li>'.
				       ' </ul>'.

				       '</div>';
			}
		}
	}
}