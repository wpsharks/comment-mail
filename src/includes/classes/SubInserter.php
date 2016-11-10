<?php
/**
 * Sub Inserter.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Sub Inserter.
 *
 * @since 141111 First documented version.
 */
class SubInserter extends AbsBase
{
    /* Related to the data. */

    /**
     * @var array Based on request args.
     *
     * @since 141111 First documented version.
     */
    protected $data;

    /**
     * @var bool Did we validate?
     *
     * @since 141111 First documented version.
     */
    protected $validated;

    /* Related to inserts. */

    /**
     * @var bool An insert?
     *
     * @since 141111 First documented version.
     */
    protected $is_insert;

    /**
     * @var bool Did we insert?
     *
     * @since 141111 First documented version.
     */
    protected $inserted;

    /**
     * @var bool Did we replace?
     *
     * @since 141111 First documented version.
     */
    protected $replaced;

    /**
     * @var int Insertion ID.
     *
     * @since 141111 First documented version.
     */
    protected $insert_id;

    /* Related to updates. */

    /**
     * @var bool An update?
     *
     * @since 141111 First documented version.
     */
    protected $is_update;

    /**
     * @var \stdClass|null Subscription.
     *
     * @since 141111 First documented version.
     */
    protected $sub; // On update only.

    /**
     * @var bool Did we update?
     *
     * @since 141111 First documented version.
     */
    protected $updated;

    /**
     * @var bool Email & key changed?
     *
     * @since 141111 First documented version.
     */
    protected $email_key_changed;

    /* Related to args/flags. */

    /**
     * @var bool Auto-confirm?
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
     * @var bool Process confirmation?
     *
     * @since 141111 First documented version.
     */
    protected $process_confirmation;

    /**
     * @var bool Process list server?
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
     * @var bool User-initiated data key protections?
     *
     * @since 141111 First documented version.
     */
    protected $ui_protected_data_keys_enable;

    /**
     * @var \WP_User|null Initiating user.
     *
     * @since 141111 First documented version.
     */
    protected $ui_protected_data_user;

    /**
     * @var bool Interpret `0` as current?
     *
     * @since 141111 First documented version.
     */
    protected $user_allow_0;

    /**
     * @var bool Keep existing?
     *
     * @since 141111 First documented version.
     */
    protected $keep_existing;

    /**
     * @var bool Keep existing?
     *
     * @since 141111 First documented version.
     */
    protected $check_blacklist;

    /* Related to user. */

    /**
     * @var \WP_User|null Subscriber.
     *
     * @since 141111 First documented version.
     */
    protected $user; // Subscriber.

    /**
     * @var bool Subscriber is current user?
     *
     * @since 141111 First documented version.
     */
    protected $is_current_user;

    /* Related to duplicates. */

    /**
     * @var array An array of any duplicate key IDs.
     *
     * @since 141111 First documented version.
     */
    protected $duplicate_key_ids;

    /**
     * @var array An array of any other duplicate IDs.
     *
     * @since 141111 First documented version.
     */
    protected $other_duplicate_ids;

    /* Other misc. properties. */

    /**
     * @var sub_confirmer|null Sub confirmer.
     *
     * @since 141111 First documented version.
     */
    protected $sub_confirmer;

    /* Related to error/success reporting. */

    /**
     * @var array An array of any errors.
     *
     * @since 141111 First documented version.
     */
    protected $errors;

    /**
     * @var array An array of any successes.
     *
     * @since 141111 First documented version.
     */
    protected $successes;

    /*
     * A lengthy class constructor.
     */

    /**
     * Class constructor.
     *
     * @since   141111 First documented version.
     *
     * @param array $request_args Arguments to the constructor.
     *                            These should NOT be trusted; they come from a `$_REQUEST` action.
     * @param array $args         Any additional behavioral args.
     *
     *    Regarding user-initiated actions. The following arguments may apply.
     *
     *       • `user_initiated` This is intended to identify user-initiated inserts/updates.
     *             This argument MUST be defined if you intend to use any other user-initiated arguments.
     *
     *       • `ui_protected_data_keys_enable` is a secondary argument to indicate that it's both a user-initiated event
     *             and that we also WANT data key protections enabled. For instance, when a user is inserting/updating on their own.
     *
     *             • This flag exists so that it's still possible for us to systematically update something on behalf of a user,
     *                without triggering the additional protected data key validations, should those be unwanted at times.
     *                Use of `user_initiated` w/o data key protections should be careful to sanitize all input data.
     *
     *       • `ui_protected_data_user` This is the only way to insert/update a specific user ID
     *             whenever the `ui_protected_data_keys_enable` flag is `TRUE`; i.e. the `user_id` is a protected/nullified key.
     *             Thus, the only way to push a user ID through is by passing it through args; using a trusted data source.
     *
     * @warning Generally speaking, user-initiated actions (i.e. `user_initiated`) should NOT be allowed to push request arguments
     *    into this method that may update protected data keys such as: `key`, `user_id`, `insertion_ip`, `last_update_time`, and others.
     *
     *    To enable validation of protected data keys please pass `user_initiated` + `ui_protected_data_keys_enable` as `TRUE`.
     *       This automatically removes/sanitizes/validates protected data keys before an insertion or update occurs.
     *
     *    Note: on insert/update with `user_initiated` + `ui_protected_data_keys_enable`, the only way to set the `user_id` is by
     *       passing the `ui_protected_data_user` argument also — the initiating user; i.e. the user doing an insert/update.
     *
     *    Note: updates w/ `user_initiated` + `ui_protected_data_keys_enable` require a read-only `key` to successfully complete the update.
     *       i.e. An input `key` is validated against the `ID` in the input request args during update. It must match up!
     *
     *    The following keys can never be inserted/updated with `user_initiated` + `ui_protected_data_keys_enable`:
     *
     *       • `key`; never; only inserted/updated systematically (no exceptions).
     *          ~ This is only updated systematically when an email changes.
     *
     *       • `user_id`; requires `ui_protected_data_user`.
     *
     *       • `insertion_ip`; inserted/updated systematically.
     *       • `insertion_region`; inserted/updated systematically.
     *       • `insertion_country`; inserted/updated systematically.
     *
     *       • `last_ip`; inserted/updated systematically.
     *       • `last_region`; inserted/updated systematically.
     *       • `last_country`; inserted/updated systematically.
     *
     *       • `insertion_time`; inserted/updated systematically.
     *       • `last_update_time`; inserted/updated systematically.
     *
     *    ~ On insert, `status` is always `unconfirmed` by force.
     *
     *    ~ On update, `status` can by anything except `trashed`.
     *       A user cannot trash themselves under any circumstance.
     */
    public function __construct(array $request_args, array $args = [])
    {
        parent::__construct();

        /* Related to the data. */

        $default_request_args = [
            'ID' => null,
            // A key is always auto-generated on insert.
            // A key can NEVER be updated by anyone — only systematically!
            // A read-only key is required to update w/ `ui_protected_data_keys_enable`.
            'key' => null,

            'user_id'    => null,
            'post_id'    => null,
            'comment_id' => null,

            'deliver' => null,

            'fname' => null,
            'lname' => null,
            'email' => null,

            'insertion_ip'      => null,
            'insertion_region'  => null,
            'insertion_country' => null,

            'last_ip'      => null,
            'last_region'  => null,
            'last_country' => null,

            'status' => null,

            'insertion_time'   => null,
            'last_update_time' => null,
        ];
        $request_args = array_merge($default_request_args, $request_args);
        $request_args = array_intersect_key($request_args, $default_request_args);

        $this->data      = $request_args; // A copy of the request args.
        $this->validated = false; // Initialize; not validated yet, obviously.

        if (isset($this->data['ID'])) {
            $this->data['ID'] = (int) $this->data['ID'];
        }
        /* Related to inserts. */

        $this->is_insert = !isset($this->data['ID']);
        $this->inserted  = false; // Initialize.
        $this->replaced  = false; // Initialize.
        $this->insert_id = 0; // Initialize.

        /* Related to updates. */

        $this->is_update = isset($this->data['ID']);
        if ($this->is_update && $this->data['ID']) {
            $this->sub = $this->plugin->utils_sub->get($this->data['ID']);
        }
        $this->updated           = false; // Initialize.
        $this->email_key_changed = false; // Initialize.

        /* Related to args/flags. */

        $defaults_args = [
            'auto_confirm' => null,

            'process_events'       => true,
            'process_confirmation' => false,
            'process_list_server'  => false,

            'user_initiated'                => false,
            'ui_protected_data_keys_enable' => false,
            'ui_protected_data_user'        => null,

            'user_allow_0' => null,

            'keep_existing' => false,

            'check_blacklist' => true,
        ];
        $args = array_merge($defaults_args, $args);
        $args = array_intersect_key($args, $defaults_args);

        if (isset($args['auto_confirm'])) {
            $this->auto_confirm = (bool) $args['auto_confirm'];
        }
        $this->process_events       = (bool) $args['process_events'];
        $this->process_confirmation = (bool) $args['process_confirmation'];
        $this->process_list_server  = (bool) $args['process_list_server'];

        $this->user_initiated = (bool) $args['user_initiated'];
        $this->user_initiated = $this->plugin->utils_sub->checkUserInitiatedByAdmin(
            $this->data['email'] ? $this->data['email'] : ($this->is_update && $this->sub ? $this->sub->email : ''),
            $this->user_initiated
        );
        $this->ui_protected_data_keys_enable = $this->user_initiated && $args['ui_protected_data_keys_enable'];
        $this->ui_protected_data_user        = null; // Recording here; but can't be filled until later below.

        if ($this->user_initiated) { // Protected data keys?
            if ($this->ui_protected_data_keys_enable) {
                $this->data['user_id'] = null;

                $this->data['insertion_ip']      = null;
                $this->data['insertion_region']  = null;
                $this->data['insertion_country'] = null;

                $this->data['last_ip']      = null;
                $this->data['last_region']  = null;
                $this->data['last_country'] = null;

                $this->data['insertion_time']   = null;
                $this->data['last_update_time'] = null;
            }
        }
        if (isset($args['user_allow_0'])) { // Iff `$this->user_initiated` also.
            $this->user_allow_0 = $this->user_initiated && $args['user_allow_0'];
        } else {
            $this->user_allow_0 = $this->user_initiated; // Defaults to this value.
        }
        $this->keep_existing = (bool) $args['keep_existing'];

        $this->check_blacklist = (bool) $args['check_blacklist'];

        /* Related to user. */

        if (!isset($this->user) || !$this->user->ID) {
            if ((int) $this->data['user_id'] > 0) { // A potentially new user ID?
                $this->user = new \WP_User((int) $this->data['user_id']);
            }
        }
        if ($this->user_initiated && $this->ui_protected_data_keys_enable
            && !isset($this->data['user_id']) && $args['ui_protected_data_user'] instanceof \WP_User
        ) {
            $this->user = $this->ui_protected_data_user = $args['ui_protected_data_user'];
        }
        if (!isset($this->user) || !$this->user->ID) {
            if ($this->user_initiated) {
                $this->user = wp_get_current_user();
            }
        }
        if (!isset($this->user) || !$this->user->ID) {
            if ($this->is_update && $this->sub && $this->sub->user_id) {
                $this->user = new \WP_User($this->sub->user_id);
            }
        }
        if (!isset($this->user) || !$this->user->ID) {
            if ((string) $this->data['email']) { // A potentially new email address?
                if (($_user = \WP_User::get_data_by('email', (string) $this->data['email']))) {
                    $this->user = new \WP_User($_user->ID);
                }
            }
        }
        unset($_user); // Housekeeping.

        if (!isset($this->user) || !$this->user->ID) {
            if ($this->is_update && $this->sub && $this->sub->email) {
                if (($_user = \WP_User::get_data_by('email', $this->sub->email))) {
                    $this->user = new \WP_User($_user->ID);
                }
            }
        }
        unset($_user); // Housekeeping.

        if (!isset($this->user) || !$this->user->ID) {
            if ($this->user_allow_0 && $this->data['user_id'] === 0) {
                $this->user = new \WP_User(0);
            }
        }
        if (!$this->user_allow_0 && $this->user && !$this->user->ID) {
            $this->user = null; // Do not allow `0` in this case.
        }
        /* Related to current user. */

        if ($this->user_initiated) {
            $this->is_current_user = true;
        } else {
            $this->is_current_user = // `$this->user` is current user?
                $this->user && $this->plugin->utils_user->isCurrent($this->user, $this->user_allow_0);
        }
        /* Related to duplicates. */

        $this->duplicate_key_ids   = []; // Initialize.
        $this->other_duplicate_ids = []; // Initialize.

        /* Related to success/error reporting. */

        $this->errors    = []; // Initialize.
        $this->successes = []; // Initialize.

        /* OK, let's do this. */

        $this->maybeInsertUpdate();
    }

    /*
     * Public API methods.
     */

    /**
     * Subscription object reference.
     *
     * @since 141111 First documented version.
     *
     * @return \stdClass|null Subscription.
     */
    public function sub()
    {
        return $this->sub;
    }

    /**
     * Did insert|update successfully?
     *
     * @since 141111 First documented version.
     *
     * @return bool Did we insert|update?
     */
    public function didInsertUpdate()
    {
        return $this->inserted || $this->updated;
    }

    /**
     * Inserted successfully?
     *
     * @since 141111 First documented version.
     *
     * @return bool Did we insert?
     */
    public function didInsert()
    {
        return $this->inserted;
    }

    /**
     * Insert caused a replace?
     *
     * @since 141111 First documented version.
     *
     * @return bool Did we replace?
     */
    public function didReplace()
    {
        return $this->replaced;
    }

    /**
     * Insertion ID.
     *
     * @since 141111 First documented version.
     *
     * @return int Insertion ID; if applicable.
     */
    public function insertId()
    {
        return $this->insert_id;
    }

    /**
     * Updated successfully?
     *
     * @since 141111 First documented version.
     *
     * @return bool Did we update?
     */
    public function didUpdate()
    {
        return $this->updated;
    }

    /**
     * Email & key changed?
     *
     * @since 141111 First documented version.
     *
     * @return bool Email & key changed?
     */
    public function emailKeyChanged()
    {
        return $this->email_key_changed;
    }

    /**
     * Instance of sub confirmer.
     *
     * @since 141111 First documented version.
     *
     * @return sub_confirmer|null Sub confirmer; if applicable.
     */
    public function subConfirmer()
    {
        return $this->sub_confirmer;
    }

    /**
     * Do we have errors?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if has errors.
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Array of any errors.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of any/all errors.
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Array of any error codes.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of any/all error codes.
     */
    public function errorCodes()
    {
        return array_keys($this->errors);
    }

    /**
     * Array of any errors w/ HTML markup.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of any/all errors.
     */
    public function errorsHtml()
    {
        return array_map([$this->plugin->utils_string, 'markdownNoP'], $this->errors);
    }

    /**
     * Do we have errors?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if has errors.
     */
    public function hasSuccesses()
    {
        return !empty($this->successes);
    }

    /**
     * Array of any successes.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of any/all successes.
     */
    public function successes()
    {
        return $this->successes;
    }

    /**
     * Array of any success codes.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of any/all success codes.
     */
    public function successCodes()
    {
        return array_keys($this->successes);
    }

    /**
     * Array of any successes w/ HTML markup.
     *
     * @since 141111 First documented version.
     *
     * @return array An array of any/all errors.
     */
    public function successesHtml()
    {
        return array_map([$this->plugin->utils_string, 'markdownNoP'], $this->successes);
    }

    /*
     * Insert/update related methods.
     */

    /**
     * Updates a subscription; or inserts a new one.
     *
     * @since 141111 First documented version.
     */
    protected function maybeInsertUpdate()
    {
        $this->sanitizeValidateData();

        if ($this->errors) { // Have errors?
            return; // Do nothing.
        }
        if ($this->is_insert) {
            $this->insert();
        } elseif ($this->is_update) {
            $this->update();
        }
    }

    /**
     * Inserts a subscription.
     *
     * @since 141111 First documented version.
     *
     * @throws \exception If an insertion failure occurs.
     */
    protected function insert()
    {
        if ($this->checkExistingBeforeInsert()) {
            return; // Already exists.
        }
        $this->checkAutoConfirmBeforeInsertUpdate();
        $this->collectDuplicateKeyIdsBeforeInsert();

        $table          = $this->plugin->utils_db->prefix().'subs';
        $data_to_insert = $this->plugin->utils_array->removeNulls($this->data);
        unset($data_to_insert['ID']); // We never want to insert an ID.

        if (($insert_replace = $this->plugin->utils_db->wp->replace($table, $data_to_insert)) === false) {
            throw new \exception(__('Insert/replace failure.', 'comment-mail'));
        }
        if (!($this->insert_id = (int) $this->plugin->utils_db->wp->insert_id)) {
            throw new \exception(__('Insert/replace failure.', 'comment-mail'));
        }
        $this->inserted = true; // Flag as `TRUE` now; i.e. the Insert/replace was a success.

        if ($insert_replace > 1) {
            $this->replaced = true; // Modified more than a single row?
        }
        $this->overwriteDuplicateKeyIdsAfterInsert(); // Before nullifying cache.

        $this->plugin->utils_sub->nullifyCache([$this->insert_id, $this->data['key']]);

        if (!($this->sub = $this->plugin->utils_sub->get($this->insert_id, true))) {
            throw new \exception(__('Sub after insert failure.', 'comment-mail'));
        }
        $this->successes['inserted_successfully'] = __('Subscription created successfully.', 'comment-mail');

        if ($this->process_events) { // Processing events? i.e. log this insertion?
            new SubEventLogInserter(
                array_merge(
                    (array) $this->sub,
                    [
                        'event'          => 'inserted',
                        'user_initiated' => $this->user_initiated,
                    ]
                )
            ); // Log event data.
        }
        if (($this->auto_confirm || $this->process_confirmation) && $this->sub->status === 'unconfirmed') {
            $this->sub_confirmer = new SubConfirmer(
                $this->sub->ID,
                [
                    'auto_confirm'        => $this->auto_confirm,
                    'process_events'      => $this->process_events,
                    'process_list_server' => $this->process_list_server && !$this->auto_confirm,
                    'user_initiated'      => $this->user_initiated,
                ]
            ); // With behavioral args.

            if ($this->sub_confirmer->sentEmailSuccessfully()) {
                $this->successes['sent_confirmation_email_successfully'] = __('Request for email confirmation sent successfully.', 'comment-mail');
            }
            if ($this->process_list_server && $this->auto_confirm) {
                $this->plugin->utils_list_server->maybeSubscribe(
                    [
                        'double_optin' => false,
                        'email'        => $this->sub->email,
                        'fname'        => $this->sub->fname,
                        'lname'        => $this->sub->lname,
                        'ip'           => $this->sub->last_ip,
                    ]
                );
            }
        }
        $this->overwriteAnyOthersAfterInsertUpdate(); // Overwrites any others.
    }

    /**
     * Updates a subscription.
     *
     * @since 141111 First documented version.
     *
     * @throws \exception If an update failure occurs.
     */
    protected function update()
    {
        $this->checkAutoConfirmBeforeInsertUpdate();
        $this->overwriteDuplicateKeyIdsBeforeUpdate();

        $sub_before = (array) $this->sub; // For event logging.

        $table          = $this->plugin->utils_db->prefix().'subs';
        $data_to_update = $this->plugin->utils_array->removeNulls($this->data);
        unset($data_to_update['ID']); // We don't need to update the `ID`.

        if ($this->plugin->utils_db->wp->update($table, $data_to_update, ['ID' => $this->sub->ID]) === false) {
            throw new \exception(__('Update failure.', 'comment-mail'));
        }
        $this->updated = true; // Flag as `TRUE` now; i.e. the update was a success.

        $this->plugin->utils_sub->nullifyCache([$this->sub->ID, $this->sub->key]);

        if (!($sub_after = $this->plugin->utils_sub->get($this->sub->ID, true))) {
            throw new \exception(__('Sub after update failure.', 'comment-mail'));
        }
        foreach ($sub_after as $_property => $_value) { // Updates object properties.
            $this->sub->{$_property} = $_value; // Update property references.
        }
        $this->sub = $sub_after; // Now change object reference.

        unset($_property, $_value); // Housekeeping.

        $this->successes['updated_successfully'] = __('Subscription updated successfully.', 'comment-mail');

        if ($this->process_events) { // Processing events? i.e. log this update?
            new SubEventLogInserter(
                array_merge(
                    (array) $this->sub,
                    [
                        'event'          => 'updated',
                        'user_initiated' => $this->user_initiated,
                    ]
                ),
                $sub_before
            ); // Log event data.
        }
        if (($this->auto_confirm || $this->process_confirmation) && $this->sub->status === 'unconfirmed') {
            $this->sub_confirmer = new SubConfirmer(
                $this->sub->ID,
                [
                    'auto_confirm'        => $this->auto_confirm,
                    'process_events'      => $this->process_events,
                    'process_list_server' => $this->process_list_server && !$this->auto_confirm,
                    'user_initiated'      => $this->user_initiated,
                ]
            ); // With behavioral args.

            if ($this->sub_confirmer->sentEmailSuccessfully()) {
                $this->successes['sent_confirmation_email_successfully'] = __('Request for email confirmation sent successfully.', 'comment-mail');
            }
            if ($this->process_list_server && $this->auto_confirm) {
                $this->plugin->utils_list_server->maybeSubscribe(
                    [
                        'double_optin' => false,
                        'email'        => $this->sub->email,
                        'fname'        => $this->sub->fname,
                        'lname'        => $this->sub->lname,
                        'ip'           => $this->sub->last_ip,
                    ]
                );
            }
        } elseif ($this->process_list_server && $this->sub->status === 'subscribed') {
            $this->plugin->utils_list_server->maybeSubscribe(
                [
                    'double_optin' => false,
                    'email'        => $this->sub->email,
                    'fname'        => $this->sub->fname,
                    'lname'        => $this->sub->lname,
                    'ip'           => $this->sub->last_ip,
                ]
            );
        }
        $this->overwriteAnyOthersAfterInsertUpdate(); // Overwrites any others.
    }

    /*
     * For insert; check existing subscription(s).
     */

    /**
     * Is there an existing subscription that will suffice?
     *
     * @since 141111 First documented version.
     *
     * @return bool `TRUE` if there's an existing subscription that will suffice.
     */
    protected function checkExistingBeforeInsert()
    {
        if (!$this->keep_existing) {
            return false; // Not applicable.
        }
        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `post_id` = '".esc_sql($this->data['post_id'])."'".
               " AND `comment_id` = '".esc_sql($this->data['comment_id'])."'".

               " AND `user_id` = '".esc_sql($this->data['user_id'])."'".
               " AND `email` = '".esc_sql($this->data['email'])."'".

               " AND `fname` = '".esc_sql($this->data['fname'])."'".
               " AND `lname` = '".esc_sql($this->data['lname'])."'".

               " AND `status` = 'subscribed'".// Only if `subscribed`.
               " AND `deliver` = '".esc_sql($this->data['deliver'])."'";

        return (bool) $this->plugin->utils_db->wp->get_var($sql);
    }

    /*
     * Insert/update helpers.
     */

    /**
     * Check if we can/should auto-confirm in this instance.
     *
     * @since 141111 First documented version.
     *
     * @note  Only if {@link $auto_confirm} is `NULL` (i.e. the default value).
     */
    protected function checkAutoConfirmBeforeInsertUpdate()
    {
        if (isset($this->auto_confirm)) {
            return; // Already set.
        }
        if (($this->newValueFor('status')) !== 'unconfirmed') {
            return; // Not applicable.
        }
        $new_post_id = $this->newValueFor('post_id');
        $new_user_id = $this->newValueFor('user_id');
        $new_email   = $this->newValueFor('email');
        $new_last_ip = $this->newValueFor('last_ip');

        $can_auto_confirm_args = [
            'post_id' => $new_post_id,

            'sub_user_id' => $new_user_id,
            'sub_email'   => $new_email,
            'sub_last_ip' => $new_last_ip,

            'user_initiated' => $this->user_initiated,
            'auto_confirm'   => $this->auto_confirm,
        ];
        $this->auto_confirm = $this->plugin->utils_sub->canAutoConfirm($can_auto_confirm_args);
    }

    /*
     * For insert; duplicate key ID handlers.
     */

    /**
     * Collects duplicate key IDs before an insert occurs.
     *
     * @since 141111 First documented version.
     *
     * @note  This also caches the underlying subs for deletion later.
     *    It's import NOT to nullify the cache until these are dealt with
     *    in the subsequent call to {@link overwrite_duplicate_key_ids_after_insert()}.
     */
    protected function collectDuplicateKeyIdsBeforeInsert()
    {
        $new_user_id    = $this->newValueFor('user_id');
        $new_post_id    = $this->newValueFor('post_id');
        $new_comment_id = $this->newValueFor('comment_id');
        $new_email      = $this->newValueFor('email');

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `user_id` = '".esc_sql($new_user_id)."'".
               " AND `post_id` = '".esc_sql($new_post_id)."'".
               " AND `comment_id` = '".esc_sql($new_comment_id)."'".
               " AND `email` = '".esc_sql($new_email)."'";

        if (($this->duplicate_key_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            foreach ($this->duplicate_key_ids as $_duplicate_key_id) {
                $this->plugin->utils_sub->get($_duplicate_key_id);
            }
        }
        unset($_duplicate_key_id); // Housekeeping.
    }

    /**
     * Overwrites duplicate key IDs after an insert occurs.
     *
     * @since 141111 First documented version.
     */
    protected function overwriteDuplicateKeyIdsAfterInsert()
    {
        if (!$this->duplicate_key_ids) {
            return; // Not necessary.
        }
        $this->plugin->utils_sub->bulkDelete(
            $this->duplicate_key_ids,
            [
                'oby_sub_id'             => $this->insert_id,
                'oby_sub_id_did_replace' => $this->replaced,
                'process_events'         => $this->process_events,
            ]
        );
    }

    /*
     * For update; duplicate key ID handlers.
     */

    /**
     * Overwrites duplicate key IDs before an update occurs.
     *
     * @since 141111 First documented version.
     */
    protected function overwriteDuplicateKeyIdsBeforeUpdate()
    {
        if (!isset($this->data['user_id'])
            && !isset($this->data['post_id'])
            && !isset($this->data['comment_id'])
            && !isset($this->data['email'])
        ) {
            return; // Not necessary.
        }
        $new_user_id    = $this->newValueFor('user_id');
        $new_post_id    = $this->newValueFor('post_id');
        $new_comment_id = $this->newValueFor('comment_id');
        $new_email      = $this->newValueFor('email');

        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `user_id` = '".esc_sql($new_user_id)."'".
               " AND `post_id` = '".esc_sql($new_post_id)."'".
               " AND `comment_id` = '".esc_sql($new_comment_id)."'".
               " AND `email` = '".esc_sql($new_email)."'".

               " AND `ID` != '".esc_sql($this->sub->ID)."'";

        if (($this->duplicate_key_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->plugin->utils_sub->bulkDelete(
                $this->duplicate_key_ids,
                [
                    'oby_sub_id'     => $this->sub->ID,
                    'process_events' => $this->process_events,
                ]
            );
        }
    }

    /*
     * For insert/update; overwrite helpers.
     */

    /**
     * Overwrites any other subscriptions after an insert|update occurs.
     *
     * @since 141111 First documented version.
     */
    protected function overwriteAnyOthersAfterInsertUpdate()
    {
        $sql = 'SELECT `ID` FROM `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

               " WHERE `post_id` = '".esc_sql($this->sub->post_id)."'".

               (!$this->sub->comment_id ? '' // If all comments now; overwrite everything else.
                   : " AND (`comment_id` = '0' OR `comment_id` = '".esc_sql($this->sub->comment_id)."')").

               ($this->sub->user_id // Has a user ID?
                   ? " AND (`user_id` = '".esc_sql($this->sub->user_id)."'".
                     "       OR `email` = '".esc_sql($this->sub->email)."')"
                   : " AND `email` = '".esc_sql($this->sub->email)."'").

               " AND `ID` != '".esc_sql($this->sub->ID)."'";

        if (($this->other_duplicate_ids = array_map('intval', $this->plugin->utils_db->wp->get_col($sql)))) {
            $this->plugin->utils_sub->bulkDelete(
                $this->other_duplicate_ids,
                [
                    'oby_sub_id'     => $this->sub->ID,
                    'process_events' => $this->process_events,
                ]
            );
        }
    }

    /*
     * Other internal utilities.
     */

    /**
     * New value for a key/property.
     *
     * @since 141111 First documented version.
     *
     * @param string $key_prop The key/property to acquire.
     *
     * @throws \exception If unable to acquire key/property value.
     *
     * @return mixed The key/property value after an insert/update.
     */
    protected function newValueFor($key_prop)
    {
        if (($key_prop = trim((string) $key_prop))) {
            if ($this->is_insert && isset($this->data[$key_prop])) {
                return $this->data[$key_prop];
            }
            if ($this->is_update && isset($this->data[$key_prop])) {
                return $this->data[$key_prop];
            }
            if ($this->is_update && isset($this->sub->{$key_prop})) {
                return $this->sub->{$key_prop};
            }
        }
        throw new \exception(sprintf(__('Missing key/prop: `%1$s`.', 'comment-mail'), $key_prop));
    }

    /*
     * Lengthy validation handler.
     */

    /**
     * Sanitizes/validates request args; i.e. {@link $data}.
     *
     * @since 141111 First documented version.
     *
     * @note  Fill the {@link $errors} property on validation failure(s).
     */
    protected function sanitizeValidateData()
    {
        foreach ($this->data as $_key => &$_value) {
            switch ($_key) {// Validate each arg value.

                case 'ID': // Primary key.

                    if (isset($_value)) {
                        $_value = (int) $_value;
                    }
                    if ($this->is_insert) {
                        $_value = null; // Nullify.
                    }
                    if ($this->is_insert && isset($_value)) { // Just to be thorough.
                        $this->errors['invalid_sub_id'] = sprintf(__('Invalid; insertion w/ ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif (($this->is_update || isset($_value)) && ($_value < 1 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_id'] = sprintf(__('Invalid ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_update && (!$this->sub || !$this->sub->ID || $_value !== $this->sub->ID)) {
                        $this->errors['invalid_sub_id'] = sprintf(__('Invalid ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'key': // Unique/secret key.

                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert) { // Force a unique key.
                        $_value = $this->plugin->utils_enc->uunnciKey20Max();
                    }
                    if (isset($_value) && (!$_value || strlen($_value) > 20)) {
                        $this->errors['invalid_sub_key'] = sprintf(__('Invalid key: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || !$_value || strlen($_value) > 20)) {
                        $this->errors['invalid_sub_key'] = sprintf(__('Invalid key: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_update && $this->user_initiated && $this->ui_protected_data_keys_enable // Must have a matching key!
                               && (!isset($_value) || !$_value || strlen($_value) > 20 || !$this->sub || !$this->sub->key || $_value !== $this->sub->key)
                    ) {
                        $this->errors['invalid_sub_key'] = sprintf(__('Invalid key: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    if ($this->is_update) { // If updating, always nullify the key now.
                        // Key changes may ONLY occur systematically; as seen in the section below.
                        $_value = null; // Nullify now; a key can never be changed by anyone!
                    }
                    if ($this->is_update && $this->data['email'] && $this->sub) { // Possible email change?
                        if (strcasecmp((string) $this->data['email'], $this->sub->email) !== 0) { // Email changing?
                            // Actions that change email should also change/nullify the existing key.
                            $this->email_key_changed = true; // Flag for API calls.
                            $_value                  = $this->plugin->utils_enc->uunnciKey20Max();
                        }
                    }
                    if (empty($this->errors['invalid_sub_key'])) {
                        if (isset($_value) && (!$_value || strlen($_value) > 20)) {
                            $this->errors['invalid_sub_key'] = sprintf(__('Invalid key: `%1$s`.', 'comment-mail'), esc_html($_value));
                        }
                    }
                    break; // Break switch handler.

                case 'user_id': // User ID.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (int) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = 0; // Use a default value.
                    }
                    if ($this->user) { // Match w/ `user`.
                        $_value = $this->user->ID;
                    }
                    if (isset($_value) && ($_value < 0 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_user_id'] = sprintf(__('Invalid user ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_user_id'] = sprintf(__('Invalid user ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'post_id': // Post ID.

                    if (isset($_value)) {
                        $_value = (int) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = 0; // Use a default value.
                    }
                    if (isset($_value) && ($_value < 1 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_post_id'] = sprintf(__('Invalid post ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || $_value < 1 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_post_id'] = sprintf(__('Invalid post ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->user_initiated && $this->ui_protected_data_keys_enable && ($_post = get_post($_value))
                               && (in_array($_post->post_status, ['future', 'draft', 'pending', 'private'], true) || ($_post->post_password && post_password_required($_post)))
                    ) {
                        $this->errors['invalid_sub_post_id'] = sprintf(__('Invalid post ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'comment_id': // Comment ID.

                    if (isset($_value)) {
                        $_value = (int) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = 0; // Use a default value.
                    }
                    if (isset($_value) && ($_value < 0 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_comment_id'] = sprintf(__('Invalid comment ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || $_value < 0 || strlen((string) $_value) > 20)) {
                        $this->errors['invalid_sub_comment_id'] = sprintf(__('Invalid comment ID: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'deliver': // Delivery option.

                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = 'asap'; // Use a default value.
                    }
                    if (isset($_value) && !in_array($_value, ['asap', 'hourly', 'daily', 'weekly'], true)) {
                        $this->errors['invalid_sub_delivery_option'] = sprintf(__('Invalid delivery option: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || !in_array($_value, ['asap', 'hourly', 'daily', 'weekly'], true))) {
                        $this->errors['invalid_sub_delivery_option'] = sprintf(__('Invalid delivery option: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'fname': // First name.

                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if ($this->is_insert && !$_value && $this->user) {
                        $_value = $this->plugin->utils_string->firstName('', $this->user);
                    }
                    if ($this->is_insert && !$_value && $this->data['email']) {
                        $_value = $this->plugin->utils_string->emailName((string) $this->data['email']);
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->user) {
                        $_value = $this->plugin->utils_string->firstName('', $this->user);
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->data['email']) {
                        $_value = $this->plugin->utils_string->emailName((string) $this->data['email']);
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub && $this->sub->email) {
                        $_value = $this->plugin->utils_string->emailName($this->sub->email);
                    }
                    if (isset($_value)) { // Clean the name.
                        $_value = $this->plugin->utils_string->cleanName($_value);
                    }
                    if (isset($_value) && strlen($_value) > 50) {
                        $this->errors['invalid_sub_first_name'] = sprintf(__('Invalid first name: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 50)) {
                        $this->errors['invalid_sub_first_name'] = sprintf(__('Invalid first name: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'lname': // Last name.

                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if ($this->is_insert && !$_value && $this->user) {
                        $_value = $this->plugin->utils_string->lastName('', $this->user);
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->user) {
                        $_value = $this->plugin->utils_string->lastName('', $this->user);
                    }
                    if (isset($_value)) { // Clean the name.
                        $_value = $this->plugin->utils_string->cleanName($_value);
                    }
                    if (isset($_value) && strlen($_value) > 100) {
                        $this->errors['invalid_sub_last_name'] = sprintf(__('Invalid last name: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 100)) {
                        $this->errors['invalid_sub_last_name'] = sprintf(__('Invalid last name: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'email': // Email address.

                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    // Disabling this; not necessary.
                    // Also, we don't want the email changing w/o us knowing about it in the `status` check.
                    //    i.e. changing the email here could occur after the status validation later.
                    // if($this->is_insert && !$_value && $this->user && $this->user->user_email)
                    //	$_value = $this->user->user_email;

                    if (isset($_value) && (!$_value || !is_email($_value) || strlen($_value) > 100)) {
                        $this->errors['invalid_sub_email'] = sprintf(__('Invalid email address: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || !$_value || !is_email($_value) || strlen($_value) > 100)) {
                        $this->errors['invalid_sub_email'] = sprintf(__('Invalid email address: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif (isset($_value) && $this->check_blacklist && $this->plugin->utils_sub->emailIsBlacklisted($_value)) {
                        $this->errors['blacklisted_sub_email'] = sprintf(__('Blacklisted email address: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'insertion_ip': // Insertion IP address.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if ($this->is_insert && !$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->current();
                    }
                    if ($this->is_insert && !$_value && $this->data['last_ip']) {
                        $_value = (string) $this->data['last_ip'];
                    }
                    if ($this->is_update && $this->sub && !$this->sub->insertion_ip && !$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->current();
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->data['last_ip']) {
                        $_value = (string) $this->data['last_ip'];
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub) {
                        $_value = $this->coalesce($this->sub->insertion_ip, $this->sub->last_ip);
                    }
                    if (isset($_value) && strlen($_value) > 39) {
                        $this->errors['invalid_sub_insertion_ip'] = sprintf(__('Invalid insertion IP: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 39)) {
                        $this->errors['invalid_sub_insertion_ip'] = sprintf(__('Invalid insertion IP: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'insertion_region': // Insertion region code.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if ($this->is_insert && !$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->currentRegion();
                    }
                    if ($this->is_insert && !$_value && ($this->data['insertion_ip'] || $this->data['last_ip'])) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->data['insertion_ip'], $this->data['last_ip']));
                    }
                    if ($this->is_update && $this->sub && !$this->sub->insertion_region && !$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->currentRegion(); // Auto-fill on update.
                    }
                    if ($this->is_update && $this->sub && !$this->sub->insertion_region && !$_value) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->sub->insertion_ip, $this->sub->last_ip));
                    }
                    if ($this->is_update && isset($_value) && !$_value && ($this->data['insertion_ip'] || $this->data['last_ip'])) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->data['insertion_ip'], $this->data['last_ip']));
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->sub->insertion_ip, $this->sub->last_ip));
                    }
                    if (isset($_value) && strlen($_value) > 2) {
                        $this->errors['invalid_sub_insertion_region'] = sprintf(__('Invalid insertion region: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 2)) {
                        $this->errors['invalid_sub_insertion_region'] = sprintf(__('Invalid insertion region: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'insertion_country': // Insertion country code.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if ($this->is_insert && !$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->currentCountry();
                    }
                    if ($this->is_insert && !$_value && ($this->data['insertion_ip'] || $this->data['last_ip'])) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->data['insertion_ip'], $this->data['last_ip']));
                    }
                    if ($this->is_update && $this->sub && !$this->sub->insertion_country && !$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->currentCountry(); // Auto-fill on update.
                    }
                    if ($this->is_update && $this->sub && !$this->sub->insertion_country && !$_value) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->sub->insertion_ip, $this->sub->last_ip));
                    }
                    if ($this->is_update && isset($_value) && !$_value && ($this->data['insertion_ip'] || $this->data['last_ip'])) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->data['insertion_ip'], $this->data['last_ip']));
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->sub->insertion_ip, $this->sub->last_ip));
                    }
                    if (isset($_value) && strlen($_value) > 2) {
                        $this->errors['invalid_sub_insertion_country'] = sprintf(__('Invalid insertion country: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 2)) {
                        $this->errors['invalid_sub_insertion_country'] = sprintf(__('Invalid insertion country: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'last_ip': // Last known IP address.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if (!$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->current();
                    }
                    if ($this->is_insert && !$_value && $this->data['insertion_ip']) {
                        $_value = (string) $this->data['insertion_ip'];
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->data['insertion_ip']) {
                        $_value = (string) $this->data['insertion_ip'];
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub) {
                        $_value = $this->coalesce($this->sub->last_ip, $this->sub->insertion_ip);
                    }
                    if (isset($_value) && strlen($_value) > 39) {
                        $this->errors['invalid_sub_last_ip'] = sprintf(__('Invalid last IP: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 39)) {
                        $this->errors['invalid_sub_last_ip'] = sprintf(__('Invalid last IP: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'last_region': // Last known region code.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if (!$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->currentRegion();
                    }
                    if ($this->is_insert && !$_value && ($this->data['last_ip'] || $this->data['insertion_ip'])) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->data['last_ip'], $this->data['insertion_ip']));
                    }
                    if ($this->is_update && isset($_value) && !$_value && ($this->data['last_ip'] || $this->data['insertion_ip'])) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->data['last_ip'], $this->data['insertion_ip']));
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub) {
                        $_value = $this->plugin->utils_ip->region($this->coalesce($this->sub->last_ip, $this->sub->insertion_ip));
                    }
                    if (isset($_value) && strlen($_value) > 2) {
                        $this->errors['invalid_sub_last_region'] = sprintf(__('Invalid last region: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 2)) {
                        $this->errors['invalid_sub_last_region'] = sprintf(__('Invalid last region: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'last_country': // Last known country code.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = ''; // Use a default value.
                    }
                    if (!$_value && $this->is_current_user) {
                        $_value = $this->plugin->utils_ip->currentCountry();
                    }
                    if ($this->is_insert && !$_value && ($this->data['last_ip'] || $this->data['insertion_ip'])) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->data['last_ip'], $this->data['insertion_ip']));
                    }
                    if ($this->is_update && isset($_value) && !$_value && ($this->data['last_ip'] || $this->data['insertion_ip'])) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->data['last_ip'], $this->data['insertion_ip']));
                    }
                    if ($this->is_update && isset($_value) && !$_value && $this->sub) {
                        $_value = $this->plugin->utils_ip->country($this->coalesce($this->sub->last_ip, $this->sub->insertion_ip));
                    }
                    if (isset($_value) && strlen($_value) > 2) {
                        $this->errors['invalid_sub_last_country'] = sprintf(__('Invalid last country: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen($_value) > 2)) {
                        $this->errors['invalid_sub_last_country'] = sprintf(__('Invalid last country: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'status': // Status.

                    if (isset($_value)) {
                        $_value = (string) $_value;
                    }
                    if ($this->is_insert && !$_value) {
                        $_value = 'unconfirmed'; // Use a default value.
                    }
                    if ($this->is_update && $this->user_initiated && $this->data['email'] && $this->sub) {
                        if (strcasecmp((string) $this->data['email'], $this->sub->email) !== 0) { // Email changing?
                            // NOTE: if `process_confirmation` is not `TRUE`, this silently unconfirms the subscriber.
                            // User-initiated actions that change email should set `process_confirmation = TRUE`.
                            $_value = 'unconfirmed'; // User MUST reconfirm when they change email addresses.
                        }
                    }
                    if (isset($_value) && !in_array($_value, ['unconfirmed', 'subscribed', 'suspended', 'trashed'], true)) {
                        $this->errors['invalid_sub_status'] = sprintf(__('Invalid status: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || !in_array($_value, ['unconfirmed', 'subscribed', 'suspended', 'trashed'], true))) {
                        $this->errors['invalid_sub_status'] = sprintf(__('Invalid status: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && $this->user_initiated && $this->ui_protected_data_keys_enable && $_value !== 'unconfirmed') {
                        $this->errors['invalid_sub_status'] = sprintf(__('Invalid status: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_update && isset($_value) && $this->user_initiated && $this->ui_protected_data_keys_enable
                               && !in_array($_value, ['unconfirmed', 'subscribed', 'suspended'], true) // Cannot `trash` themselves.
                    ) {
                        $this->errors['invalid_sub_status'] = sprintf(__('Invalid status: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    // However, they SHOULD be allowed to delete/unsubscribe; which is a separate issue altogether; i.e. not covered here.

                    // NOTE: a user should only be shown two options in a UI. Those should include `subscribed`, `suspended`.
                    // We only allow `unconfirmed` here because that does no harm; and so email address changes can occur properly.
                    //    ~ i.e. user-initiated email address changes will always result in an `unconfirmed` status update (as seen above).

                    // NOTE: a user should NEVER be allowed to edit subscriptions that do not belong to them; i.e. for which they have not already been confirmed.
                    //    Thus, changing the status back and forth always assumes that we are dealing w/ a user who has already been confirmed in one way or another.
                    //    For this reason, it's OK for a user to change the status from `subscribed` to `unconfirmed`, and then back to `subscribed`.
                    //    It is safe to assume here that a user would NOT have a key if they had not already been confirmed in some way.
                    //    See also: the `key` check up above for `user_initiated` + `ui_protected_data_keys_enable` actions.
                    //    See also: the `email` change of address check above w/ `user_initiated`.
                    //    See also: the `email` change of address check above w/ `key`.

                    break; // Break switch handler.

                case 'insertion_time': // Insertion time.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (int) $_value;
                    }
                    if ($this->is_insert && $_value < 1) {
                        $_value = time(); // Use a default value.
                    }
                    if (isset($_value) && strlen((string) $_value) !== 10) {
                        $this->errors['invalid_sub_insertion_time'] = sprintf(__('Invalid insertion time: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen((string) $_value) !== 10)) {
                        $this->errors['invalid_sub_insertion_time'] = sprintf(__('Invalid insertion time: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.

                case 'last_update_time': // Last update time.

                    if ($this->user_initiated) {
                        if ($this->ui_protected_data_keys_enable) {
                            $_value = null; // Nullify protected key.
                        }
                    }
                    if (isset($_value)) {
                        $_value = (int) $_value;
                    }
                    if ($this->is_insert && $_value < 1) {
                        $_value = time(); // Use a default value.
                    }
                    if ($_value < 1) {
                        $_value = time(); // Update time.
                    }
                    if (isset($_value) && strlen((string) $_value) !== 10) {
                        $this->errors['invalid_sub_last_update_time'] = sprintf(__('Invalid last update time: `%1$s`.', 'comment-mail'), esc_html($_value));
                    } elseif ($this->is_insert && (!isset($_value) || strlen((string) $_value) !== 10)) {
                        $this->errors['invalid_sub_last_update_time'] = sprintf(__('Invalid last update time: `%1$s`.', 'comment-mail'), esc_html($_value));
                    }
                    break; // Break switch handler.
            }
        }
        unset($_key, $_value, $_post); // Housekeeping.

        $this->validated = true; // Flag as `TRUE`; data validated.
    }
}
