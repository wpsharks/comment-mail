<?php
/**
 * Auto Sub Injector.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Auto Sub Injector.
 *
 * @since 141111 First documented version.
 */
class SubAutoInjector extends AbsBase
{
    /**
     * @type \stdClass|null Post object.
     *
     * @since 141111 First documented version.
     */
    protected $post;

    /**
     * @type \WP_User|null Post author.
     *
     * @since 141111 First documented version.
     */
    protected $post_author;

    /**
     * @type array Auto-subscribable post types.
     *
     * @since 141111 First documented version.
     */
    protected $post_types;

    /**
     * @type bool Process events?
     *
     * @since 141111 First documented version.
     */
    protected $process_events;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param int|string $post_id Post ID.
     * @param array      $args    Any additional behavioral args.
     */
    public function __construct($post_id, array $args = [])
    {
        parent::__construct();

        $post_id = (integer) $post_id;

        if ($post_id) { // Need to have this.
            $this->post = get_post($post_id);
        }
        $defaults_args = [
            'process_events' => true,
        ];
        $args = array_merge($defaults_args, $args);
        $args = array_intersect_key($args, $defaults_args);

        if ($this->post && $this->post->post_author) {
            if ($this->plugin->options['auto_subscribe_post_author_enable']) {
                $this->post_author = new \WP_User($this->post->post_author);
            }
        }
        $this->post_types = strtolower($this->plugin->options['auto_subscribe_post_types']);
        $this->post_types = preg_split('/[;,\s]+/', $this->post_types, null, PREG_SPLIT_NO_EMPTY);

        $enabled_post_types = strtolower($this->plugin->options['enabled_post_types']);
        $enabled_post_types = preg_split('/[;,\s]+/', $enabled_post_types, null, PREG_SPLIT_NO_EMPTY);

        if ($enabled_post_types && $this->post_types) {
            foreach ($this->post_types as $_key => $_post_type) {
                if (!in_array($_post_type, $enabled_post_types, true)) {
                    unset($this->post_types[$_key]);
                }
            }
            unset($_key, $_post_type); // Housekeeping.
        }
        $this->process_events = (boolean) $args['process_events'];

        $this->maybeAutoInject();
    }

    /**
     * Injects subscriptions.
     *
     * @since 141111 First documented version.
     */
    protected function maybeAutoInject()
    {
        if (!$this->post) {
            return; // Not possible.
        }
        if (!$this->post->ID) {
            return; // Not possible.
        }
        if (!$this->plugin->options['auto_subscribe_enable']) {
            return; // Not applicable.
        }
        if (!in_array($this->post->post_type, $this->post_types, true)) {
            return; // Not applicable.
        }
        if (in_array($this->post->post_type, ['revision', 'nav_menu_item'], true)) {
            return; // Not applicable.
        }
        $this->maybeInjectPostAuthor();
        $this->maybeInjectRecipients();
        $this->maybeInjectUsersByRole();
    }

    /**
     * Injects post author.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInjectPostAuthor()
    {
        if (!$this->post_author) {
            return; // Not possible.
        }
        if (!$this->post_author->ID) {
            return; // Not possible.
        }
        if (!$this->post_author->user_email) {
            return; // Not possible.
        }
        if (!$this->plugin->options['auto_subscribe_post_author_enable']) {
            return; // Not applicable.
        }
        $data = [
            'post_id'    => $this->post->ID,
            'user_id'    => $this->post_author->ID,
            'comment_id' => 0, // Subscribe to all comments.
            'deliver'    => $this->plugin->options['auto_subscribe_deliver'],

            'fname' => $this->plugin->utils_string->firstName('', $this->post_author),
            'lname' => $this->plugin->utils_string->lastName('', $this->post_author),
            'email' => $this->post_author->user_email,

            'status' => 'subscribed',
        ];
        new SubInserter(
            $data,
            [
                'process_events' => $this->process_events,
            ]
        );
    }

    /**
     * Injects recipients.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInjectRecipients()
    {
        if (!$this->plugin->options['auto_subscribe_recipients']) {
            return; // Not applicable.
        }
        $recipients = $this->plugin->options['auto_subscribe_recipients'];
        $recipients = $this->plugin->utils_mail->parseAddressesDeep($recipients);

        foreach ($recipients as $_recipient) {
            if (!$_recipient->email) {
                continue; // Not applicable.
            }
            $_data = [
                'post_id'    => $this->post->ID,
                'comment_id' => 0, // Subscribe to all comments.
                'deliver'    => $this->plugin->options['auto_subscribe_deliver'],

                'fname' => $_recipient->fname,
                'lname' => $_recipient->lname,
                'email' => $_recipient->email,

                'status' => 'subscribed',
            ];
            new SubInserter(
                $_data,
                [
                    'process_events' => $this->process_events,
                ]
            );
        }
        unset($_recipient, $_data); // Housekeeping.
    }

    /**
     * Injects users by role.
     *
     * @since 151224 Adding auto-subscribe roles.
     */
    protected function maybeInjectUsersByRole()
    {
        if (!$this->plugin->options['auto_subscribe_roles']) {
            return; // Not applicable.
        }
        $roles = $this->plugin->options['auto_subscribe_roles'];
        $roles = preg_split('/[;,\s]+/', $roles, null, PREG_SPLIT_NO_EMPTY);

        foreach ($roles as $_role) {
            // All users w/ any of these roles.

            foreach ((array) get_users('role='.$_role) as $_user) {
                if (!($_user instanceof \WP_User) || !$_user->user_email) {
                    continue; // Not applicable/possible.
                }
                $_data = [
                    'post_id'    => $this->post->ID,
                    'comment_id' => 0, // Subscribe to all comments.
                    'deliver'    => $this->plugin->options['auto_subscribe_deliver'],
                    'fname'      => $_user->first_name,
                    'lname'      => $_user->last_name,
                    'email'      => $_user->user_email,
                    'status'     => 'subscribed',
                ];
                new SubInserter(
                    $_data,
                    [
                        'process_events' => $this->process_events,
                    ]
                );
            }
            unset($_user, $_data); // Housekeeping.
        }
        unset($_role); // Housekeeping.
    }
}
