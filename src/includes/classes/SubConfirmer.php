<?php
/**
 * Sub Confirmer.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Confirmer.
 *
 * @since 141111 First documented version.
 */
class SubConfirmer extends AbsBase
{
    /**
     * @var \stdClass|null Subscription.
     *
     * @since 141111 First documented version.
     */
    protected $sub;

    /**
     * @var null|bool Auto-confirm?
     *
     * @since 141111 First documented version.
     */
    protected $auto_confirm;

    /**
     * @var bool Process events?
     *
     * @since 141111 First documented version.
     */
    protected $process_events;

    /**
     * @var bool Proces list server?
     *
     * @since 150922 Adding list server.
     */
    protected $process_list_server;

    /**
     * @var bool User initiated?
     *
     * @since 141111 First documented version.
     */
    protected $user_initiated;

    /**
     * @var bool Auto confirmed?
     *
     * @since 141111 First documented version.
     */
    protected $auto_confirmed;

    /**
     * @var bool Confirming via email?
     *
     * @since 141111 First documented version.
     */
    protected $confirming_via_email;

    /**
     * @var bool Sent an email?
     *
     * @since 141111 First documented version.
     */
    protected $sent_email_successfully;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int   $sub_id Subscriber ID.
     * @param array $args   Any additional behavioral args.
     */
    public function __construct($sub_id, array $args = [])
    {
        parent::__construct();

        $sub_id    = (int) $sub_id;
        $this->sub = $this->plugin->utils_sub->get($sub_id);

        $defaults_args = [
            'auto_confirm' => null,

            'process_events'      => true,
            'process_list_server' => false,

            'user_initiated' => false,
        ];
        $args = array_merge($defaults_args, $args);
        $args = array_intersect_key($args, $defaults_args);

        if (isset($args['auto_confirm'])) {
            $this->auto_confirm = (bool) $args['auto_confirm'];
        }
        $this->process_events      = (bool) $args['process_events'];
        $this->process_list_server = (bool) $args['process_list_server'];

        $this->user_initiated = (bool) $args['user_initiated'];
        $this->user_initiated = $this->plugin->utils_sub->checkUserInitiatedByAdmin(
            $this->sub ? $this->sub->email : '',
            $this->user_initiated
        );
        $this->auto_confirmed          = false;
        $this->confirming_via_email    = false;
        $this->sent_email_successfully = false;

        $this->maybeSendConfirmationRequest();
    }

    /**
     * Auto-confirmed?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if auto-confirmed.
     */
    public function autoConfirmed()
    {
        return $this->auto_confirmed;
    }

    /**
     * Confirming via email?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if confirming via email.
     */
    public function confirmingViaEmail()
    {
        return $this->confirming_via_email;
    }

    /**
     * Sent email successfully?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if sent email successfully.
     */
    public function sentEmailSuccessfully()
    {
        return $this->sent_email_successfully;
    }

    /**
     * Send confirmation request.
     *
     * @since 141111 First documented version.
     */
    protected function maybeSendConfirmationRequest()
    {
        if (!$this->sub) {
            return; // Not possible.
        }
        if (!$this->sub->email) {
            return; // Not possible.
        }
        if ($this->sub->status === 'subscribed') {
            return; // Nothing to do.
        }
        if ($this->maybeAutoConfirm()) {
            return; // Nothing more to do.
        }
        $sub                 = $this->sub;
        $sub_post            = $sub_comment            = null;
        $process_list_server = $this->process_list_server;

        if (!($sub_post = get_post($this->sub->post_id))) {
            return; // Post no longer exists.
        }
        if ($this->sub->comment_id && !($sub_comment = get_comment($this->sub->comment_id))) {
            return; // Comment no longer exists.
        }
        $template_vars = get_defined_vars(); // Everything above.

        $subject_template = new Template('email/sub-confirmation/subject.php');
        $message_template = new Template('email/sub-confirmation/message.php');

        $subject = trim(preg_replace('/\s+/', ' ', $subject_template->parse($template_vars)));
        $message = $message_template->parse($template_vars); // With confirmation link.

        if (!$subject || !$message) { // Missing one of these?
            return; // One or more corrupted/empty template files.
        }
        $this->confirming_via_email    = true; // Flag this scenario.
        $this->sent_email_successfully = $this->plugin->utils_mail->send(
            $this->sub->email,
            $subject,
            $message
        );
    }

    /**
     * Auto-confirm, if possible.
     *
     * @since 141111 First documented version.
     *
     * @return bool TRUE if auto-confirmed in some way.
     */
    protected function maybeAutoConfirm()
    {
        $can_auto_confirm_args = [
            'post_id' => $this->sub->post_id,

            'sub_user_id' => $this->sub->user_id,
            'sub_email'   => $this->sub->email,
            'sub_last_ip' => $this->sub->last_ip,

            'user_initiated' => $this->user_initiated,
            'auto_confirm'   => $this->auto_confirm,
        ];
        $can_auto_confirm = $this->plugin->utils_sub->canAutoConfirm($can_auto_confirm_args);

        if ($can_auto_confirm) { // Possible to auto-confirm?
            $this->plugin->utils_sub->confirm(
                $this->sub->ID,
                [
                    'process_events' => $this->process_events,
                    'user_initiated' => $this->user_initiated,
                ]
            ); // With behavioral args.

            return $this->auto_confirmed = true;
        }
        return $this->auto_confirmed = false;
    }
}
