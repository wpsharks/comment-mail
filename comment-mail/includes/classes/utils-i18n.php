<?php
/**
 * i18n Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_i18n'))
	{
		/**
		 * i18n Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_i18n extends abstract_base
		{
			/**
			 * `X subscriber` or `X subscribers`.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer $counter Total subscribers; i.e. a counter value.
			 *
			 * @return string The phrase `X subscriber` or `X subscribers`.
			 */
			public function subscribers($counter)
			{
				$counter = (integer)$counter; // Force integer.

				return sprintf(_n('%1$s subscriber', '%1$s subscribers', $counter, $this->plugin->text_domain), $counter);
			}

			/**
			 * Action past tense translation.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $action An action; e.g. `confirm`, `delete`, `unconfirm`, etc.
			 *
			 * @return string The string translation for the given `$action`.
			 */
			public function action_ed($action)
			{
				$action = strtolower(trim((string)$action));

				switch($action) // Convert to past tense.
				{
					case 'reconfirm':
						return __('reconfirmed', $this->plugin->text_domain);

					case 'confirm':
						return __('confirmed', $this->plugin->text_domain);

					case 'unconfirm':
						return __('unconfirmed', $this->plugin->text_domain);

					case 'suspend':
						return __('suspended', $this->plugin->text_domain);

					case 'trash':
						return __('trashed', $this->plugin->text_domain);

					case 'delete':
						return __('deleted', $this->plugin->text_domain);
				}
				return __(rtrim($action, 'ed').'ed', $this->plugin->text_domain);
			}

			/**
			 * Status label translation.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $status A status e.g. `approve`, `hold`, `unconfirmed`, etc.
			 *
			 * @return string The string translation for the given `$status`.
			 */
			public function status_label($status)
			{
				$status = strtolower(trim((string)$status));

				switch($status) // Convert to label.
				{
					case 'approve':
						return __('approved', $this->plugin->text_domain);

					case 'hold':
						return __('pending', $this->plugin->text_domain);

					case 'trash':
						return __('trashed', $this->plugin->text_domain);

					case 'spam':
						return __('spammy', $this->plugin->text_domain);

					case 'delete':
						return __('deleted', $this->plugin->text_domain);

					case 'open':
						return __('open', $this->plugin->text_domain);

					case 'closed':
						return __('closed', $this->plugin->text_domain);

					case 'unconfirmed':
						return __('unconfirmed', $this->plugin->text_domain);

					case 'subscribed':
						return __('subscribed', $this->plugin->text_domain);

					case 'suspended':
						return __('suspended', $this->plugin->text_domain);

					case 'trashed':
						return __('trashed', $this->plugin->text_domain);
				}
				return __(rtrim($status, 'ed').'ed', $this->plugin->text_domain);
			}

			/**
			 * Subscr. type label translation.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $subscr_type A subscr. type; i.e. `comments`, `comment`.
			 *
			 * @return string The string translation for the given `$subscr_type`.
			 */
			public function subscr_type_label($subscr_type)
			{
				$subscr_type = strtolower(trim((string)$subscr_type));

				switch($subscr_type) // Convert to label.
				{
					case 'comments':
						return __('all comments', $this->plugin->text_domain);

					case 'comment':
						return __('replies only', $this->plugin->text_domain);
				}
				return __($subscr_type, $this->plugin->text_domain);
			}
		}
	}
}