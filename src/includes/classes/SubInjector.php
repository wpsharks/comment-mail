<?php
/**
 * Sub Injector.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Injector.
 *
 * @since 141111 First documented version.
 */
class SubInjector extends AbsBase
{
    /**
     * @type \WP_User|null Subscription.
     *
     * @since 141111 First documented version.
     */
    protected $user;

    /**
     * @type \stdClass|null Comment.
     *
     * @since 141111 First documented version.
     */
    protected $comment;

    /**
     * @type string Subscription type.
     *
     * @since 141111 First documented version.
     */
    protected $type;

    /**
     * @type string Subscription delivery option.
     *
     * @since 141111 First documented version.
     */
    protected $deliver;

    /**
     * @type null|bool Auto-confirm?
     *
     * @since 141111 First documented version.
     */
    protected $auto_confirm;

    /**
     * @type bool Process events?
     *
     * @since 141111 First documented version.
     */
    protected $process_events;

    /**
     * @type bool Process list server?
     *
     * @since 150922 Adding list server.
     */
    protected $process_list_server;

    /**
     * @type bool User initiated?
     *
     * @since 141111 First documented version.
     */
    protected $user_initiated;

    /**
     * @type bool Keep existing?
     *
     * @since 141111 First documented version.
     */
    protected $keep_existing;

    /**
     * @type sub_inserter|null Sub inserter.
     *
     * @since 141111 First documented version.
     */
    protected $sub_inserter;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param \WP_User|null $user       Subscribing user.
     * @param int|string    $comment_id Comment ID.
     * @param array         $args       Any additional behavioral args.
     */
    public function __construct(\WP_User $user = null, $comment_id = 0, array $args = [])
    {
        parent::__construct();

        $this->user = $user; // \WP_user|null.

        $comment_id = (integer) $comment_id;

        if ($comment_id) { // Need to have this.
            $this->comment = get_comment($comment_id);
        }
        $defaults_args = [
            'type'    => 'comment',
            'deliver' => 'asap',

            'auto_confirm' => null,

            'process_events'      => true,
            'process_list_server' => false,

            'user_initiated' => false,

            'keep_existing' => false,
        ];
        $args = array_merge($defaults_args, $args);
        $args = array_intersect_key($args, $defaults_args);

        $this->type    = trim(strtolower((string) $args['type']));
        $this->deliver = trim(strtolower((string) $args['deliver']));
        $this->deliver = !$this->deliver ? 'asap' : $this->deliver;

        if (isset($args['auto_confirm'])) {
            $this->auto_confirm = (boolean) $args['auto_confirm'];
        }
        $this->process_events      = (boolean) $args['process_events'];
        $this->process_list_server = (boolean) $args['process_list_server'];

        $this->user_initiated = (boolean) $args['user_initiated'];
        $this->user_initiated = $this->plugin->utils_sub->checkUserInitiatedByAdmin(
            $this->comment ? $this->comment->comment_author_email : '',
            $this->user_initiated
        );
        $this->keep_existing = (boolean) $args['keep_existing'];

        $this->maybeInject();
    }

    /**
     * Sub inserter.
     *
     * @since 141111 First documented version.
     *
     * @return sub_inserter|null Sub inserter.
     */
    public function subInserter()
    {
        return $this->sub_inserter;
    }

    /**
     * Injects a new subscription.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInject()
    {
        if (!$this->comment) {
            return; // Not possible.
        }
        if (!$this->comment->comment_post_ID) {
            return; // Not possible.
        }
        if (!$this->comment->comment_ID) {
            return; // Not possible.
        }
        if (!$this->comment->comment_author_email) {
            return; // Not possible.
        }
        if ($this->comment->comment_type
            && $this->comment->comment_type !== 'comment'
        ) {
            return; // Not applicable.
        }
        $data = [
            'post_id'    => $this->comment->comment_post_ID,
            'user_id'    => $this->user ? $this->user->ID : null,
            'comment_id' => $this->type === 'comments' ? 0 : $this->comment->comment_ID,
            'deliver'    => $this->deliver, // Delivery option.

            'fname' => $this->plugin->utils_string->firstName($this->comment->comment_author, $this->comment->comment_author_email),
            'lname' => $this->plugin->utils_string->lastName($this->comment->comment_author),
            'email' => $this->comment->comment_author_email,
        ];
        $this->sub_inserter = new SubInserter(
            $data,
            [
                'process_confirmation' => true, // Always.
                'auto_confirm'         => $this->auto_confirm,
                'process_events'       => $this->process_events,
                'process_list_server'  => $this->process_list_server,
                'user_initiated'       => $this->user_initiated,
                'keep_existing'        => $this->keep_existing,
            ]
        );
    }
}
