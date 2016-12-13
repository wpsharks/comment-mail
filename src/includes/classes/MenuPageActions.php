<?php
/**
 * Menu Page Actions.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Menu Page Actions.
 *
 * @since 141111 First documented version.
 */
class MenuPageActions extends AbsBase
{
    /**
     * @var array Valid actions.
     *
     * @since 141111 First documented version.
     */
    protected $valid_actions;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->valid_actions = [
            'save_options',
            'set_template_type',
            'restore_default_options',

            'dismiss_notice',

            'import',
            'export',

            'sub_form',
            'sub_form_comment_id_row_via_ajax',
            'sub_form_user_id_info_via_ajax',

            'stats_chart_data_via_ajax',

            'process_queue',

            
        ];
        $this->maybeHandle();
    }

    /**
     * Action handler.
     *
     * @since 141111 First documented version.
     */
    protected function maybeHandle()
    {
        if (!is_admin()) {
            return; // Not applicable.
        }
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Not applicable.
        }
        if (!$this->plugin->utils_url->hasValidNonce()) {
            return; // Unauthenticated; ignore.
        }
        foreach ((array) $_REQUEST[GLOBAL_NS] as $_action => $_request_args) {
            if ($_action && in_array($_action, $this->valid_actions, true)) {
                $_method = preg_replace_callback('/_(.)/', function ($m) {
                    return strtoupper($m[1]);
                }, strtolower($_action));
                $this->{$_method}($this->plugin->utils_string->trimStripDeep($_request_args));
            }
        }
        unset($_action, $_method, $_request_args); // Housekeeping.
    }

    /**
     * Saves options.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function saveOptions($request_args)
    {
        $request_args = (array) $request_args;

        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        $this->plugin->optionsSave($request_args);

        $notice_markup = // Notice regarding options having been updated successfully.
            sprintf(__('%1$s&trade; options updated successfully.', 'comment-mail'), esc_html(NAME));
        $this->plugin->enqueueUserNotice($notice_markup, ['transient' => true]);

        if (!empty($request_args['mail_test']) && ($mail_test_to = trim((string) $request_args['mail_test']))) {
            $mail_test = $this->plugin->utils_mail->test(
                $mail_test_to, // To the address specificed in the request args.
                sprintf(__('Test Email Message sent by %1$s™', 'comment-mail'), NAME),
                sprintf(__('Test email message sent by %1$s&trade; from: <code>%2$s</code>.', 'comment-mail'), esc_html(NAME), esc_html($this->plugin->utils_url->currentHostPath()))
            );
            $this->plugin->enqueueUserNotice($mail_test->results_markup, ['transient' => true]);
        }
        if (!empty($request_args['mail_smtp_test']) && ($mail_smtp_test_to = trim((string) $request_args['mail_smtp_test']))) {
            $mail_smtp_test = $this->plugin->utils_mail->smtpTest(
                $mail_smtp_test_to, // To the address specificed in the request args.
                sprintf(__('Test Email Message sent by %1$s™', 'comment-mail'), NAME),
                sprintf(__('Test email message sent by %1$s&trade; from: <code>%2$s</code>.', 'comment-mail'), esc_html(NAME), esc_html($this->plugin->utils_url->currentHostPath()))
            );
            $this->plugin->enqueueUserNotice($mail_smtp_test->results_markup, ['transient' => true]);
        }
        wp_redirect($this->plugin->utils_url->optionsUpdated());
        exit();
    }

    /**
     * Sets template type/mode.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function setTemplateType($request_args)
    {
        $template_type = (string) $request_args;

        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        $this->plugin->optionsSave(compact('template_type'));

        $notice_markup = // Notice regarding options having been updated successfully.

            sprintf(
                __('Template mode updated to: <code>%2$s</code>.', 'comment-mail'),
                esc_html(NAME),
                $template_type === 'a' ? __('advanced', 'comment-mail') : __('simple', 'comment-mail')
            ).

            ' '.($template_type === 'a' // Provide an additional note; to help explain what just occured in this scenario.
                ? 'A new set of templates has been loaded below. This mode uses advanced PHP-based templates. Recommended for advanced customization.</i>'
                : 'A new set of templates has been loaded below. This mode uses simple shortcode templates. Easiest to work with <i class="fa fa-smile-o"></i>');

        $this->plugin->enqueueUserNotice($notice_markup, ['transient' => true]);

        wp_redirect($this->plugin->utils_url->templateTypeUpdated());
        exit();
    }

    /**
     * Restores defaults options.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function restoreDefaultOptions($request_args)
    {
        $request_args = null; // Not used here.

        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        delete_option(GLOBAL_NS.'_options');
        $this->plugin->options = $this->plugin->default_options;

        ImportStcr::deletePostMetaKeys(); // Reset import tracking.

        $notice_markup = // Notice regarding options having been retored successfully.
            sprintf(__('%1$s&trade; default options restored successfully.', 'comment-mail'), esc_html(NAME));
        $this->plugin->enqueueUserNotice($notice_markup, ['transient' => true]);

        wp_redirect($this->plugin->utils_url->defaultOptionsRestored());
        exit();
    }

    /**
     * Dismisses a persistent notice.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function dismissNotice($request_args)
    {
        $request_args = (array) $request_args;

        if (empty($request_args['notice_key'])) {
            return; // Not possible.
        }
        if (!current_user_can($this->plugin->manage_cap)) {
            if (!current_user_can($this->plugin->cap)) {
                return; // Unauthenticated; ignore.
            }
        }
        $notices = get_option(GLOBAL_NS.'_notices');
        if (!is_array($notices)) {
            $notices = [];
        }
        unset($notices[$request_args['notice_key']]);
        update_option(GLOBAL_NS.'_notices', $notices);

        wp_redirect($this->plugin->utils_url->noticeDismissed());
        exit();
    }

    /**
     * Runs a specific import type.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function import($request_args)
    {
        $request_args = (array) $request_args;

        if (empty($request_args['type']) || !is_string($request_args['type'])) {
            return; // Missing and/or invalid import type.
        }
        if (!in_array($request_args['type'], ['subs', 'stcr', 'ops'], true)) {
            return; // Invalid import type.
        }
        if (!class_exists($class = '\\'.__NAMESPACE__.'\\Import'.ucfirst($request_args['type']))) {
            return; // Invalid import type.
        }
        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        if (!empty($_FILES[GLOBAL_NS]['tmp_name']['import']['data_file'])) {
            $request_args['data_file'] = $_FILES[GLOBAL_NS]['tmp_name']['import']['data_file'];
        }
        $importer = new $class($request_args); // Instantiate.
    }

    /**
     * Runs a specific export type.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function export($request_args)
    {
        $request_args = (array) $request_args;

        if (empty($request_args['type']) || !is_string($request_args['type'])) {
            return; // Missing and/or invalid import type.
        }
        if (!in_array($request_args['type'], ['subs', 'ops'], true)) {
            return; // Invalid import type.
        }
        if (!class_exists($class = '\\'.__NAMESPACE__.'\\Export'.ucfirst($request_args['type']))) {
            return; // Invalid import type.
        }
        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        $exporter = new $class($request_args); // Instantiate.
    }

    /**
     * Processes sub. form inserts/updates.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     *
     * @see   MenuPageSubFormBase::process()
     */
    protected function subForm($request_args)
    {
        if (!($request_args = (array) $request_args)) {
            return; // Empty request args.
        }
        if (!current_user_can($this->plugin->manage_cap)) {
            if (!current_user_can($this->plugin->cap)) {
                return; // Unauthenticated; ignore.
            }
        }
        MenuPageSubFormBase::process($request_args);
    }

    /**
     * Acquires comment ID row via AJAX.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     *
     * @see   MenuPageSubFormBase::commentIdRowViaAjax()
     */
    protected function subFormCommentIdRowViaAjax($request_args)
    {
        $request_args = (array) $request_args;

        if (!isset($request_args['post_id'])) {
            exit; // Missing post ID.
        }
        if (($post_id = (int) $request_args['post_id']) < 0) {
            exit; // Invalid post ID.
        }
        if (!current_user_can($this->plugin->manage_cap)) {
            if (!current_user_can($this->plugin->cap)) {
                exit; // Unauthenticated; ignore.
            }
        }
        exit(MenuPageSubFormBase::commentIdRowViaAjax($post_id));
    }

    /**
     * Acquires user ID info via AJAX.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     *
     * @see   MenuPageSubFormBase::userIdInfoViaAjax()
     */
    protected function subFormUserIdInfoViaAjax($request_args)
    {
        $request_args = (array) $request_args;

        if (!isset($request_args['user_id'])) {
            exit; // Missing user ID.
        }
        if (($user_id = (int) $request_args['user_id']) < 0) {
            exit; // Invalid user ID.
        }
        if (!current_user_can($this->plugin->manage_cap)) {
            if (!current_user_can($this->plugin->cap)) {
                exit; // Unauthenticated; ignore.
            }
        }
        header('Content-Type: application/json; charset=UTF-8');

        exit(MenuPageSubFormBase::userIdInfoViaAjax($user_id));
    }

    /**
     * Outputs chart data for stats.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function statsChartDataViaAjax($request_args)
    {
        $request_args = (array) $request_args;

        if (!current_user_can($this->plugin->manage_cap)) {
            if (!current_user_can($this->plugin->cap)) {
                exit; // Unauthenticated; ignore.
            }
        }
        header('Content-Type: application/json; charset=UTF-8');

        new ChartData($request_args); // With JSON output.

        exit(); // Stop after output; always.
    }

    /**
     * Process the mail queue.
     *
     * @since 161202 Adding manual queue processor.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function processQueue($request_args)
    {
        $request_args = null; // Not used here.

        if (current_user_can($this->plugin->manage_cap)) {
            $QueueProcessor    = new QueueProcessor('manual');
            $processed_entries = $QueueProcessor->processedEntries();

            $notice_markup = // Notice regarding queue processor running successfully.
                sprintf(_n('A single queue-runner processed <code>%1$s</code> entry.', 'A single queue-runner processed <code>%1$s</code> entries.', $processed_entries, 'comment-mail'), esc_html($processed_entries)).'<br />'.
                sprintf(__('<small><em><strong>Note:</strong> Each queue runner works for up to %1$s seconds and it will process up to %2$s queued emails. If additional emails remain in the queue (and they\'re ready to be sent) you can run again to continue processing.</em></small>', 'comment-mail'), esc_html($this->plugin->options['queue_processor_max_time']), esc_html($this->plugin->options['queue_processor_max_limit']));
            $this->plugin->enqueueUserNotice($notice_markup, ['transient' => true]);
        }
        wp_redirect($this->plugin->utils_url->queueMenuPageOnly()).exit();
    }

    
}
