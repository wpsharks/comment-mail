<?php
/**
 * SSO Storage
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	use OAuth\Common\Token\TokenInterface;
	use OAuth\Common\Storage\Exception\TokenNotFoundException;
	use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

	if(!class_exists('\\'.__NAMESPACE__.'\\sso_storage'))
	{
		/**
		 * SSO Storage
		 *
		 * @since 141111 First documented version.
		 */
		class sso_storage implements \OAuth\Common\Storage\TokenStorageInterface
		{
			/**
			 * @var plugin Plugin class reference.
			 *
			 * @since 141111 First documented version.
			 */
			protected $plugin;

			/**
			 * @var integer Time to live.
			 *
			 * @since 141111 First documented version.
			 */
			protected $ttl;

			/**
			 * @var string SSO cookie key.
			 *
			 * @since 141111 First documented version.
			 */
			protected $key;

			/**
			 * @var string Transient key.
			 *
			 * @since 141111 First documented version.
			 */
			protected $transient;

			/**
			 * @var array Transient SSO data.
			 *
			 * @since 141111 First documented version.
			 */
			protected $data;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();

				$this->ttl = apply_filters(__CLASS__.'_ttl', 31556926);

				if(!($this->key = $this->plugin->utils_enc->get_cookie(__NAMESPACE__.'_sso_key')))
				{
					$this->key = $this->plugin->utils_enc->uunnci_key_20_max();
					$this->plugin->utils_enc->set_cookie(__NAMESPACE__.'_sso_key', $this->key, $this->ttl);
				}
				$this->transient = __NAMESPACE__.'_sso_'.$this->key;

				if(!($this->data = get_transient($this->transient)))
					$this->data = array(); // Initialize.
			}

			/**
			 * {@inheritDoc}
			 */
			public function hasAccessToken($service)
			{
				return !empty($this->data['tokens'][$service]);
			}

			/**
			 * {@inheritDoc}
			 */
			public function retrieveAccessToken($service)
			{
				if($this->hasAccessToken($service))
					return unserialize($this->data['tokens'][$service]);

				throw new TokenNotFoundException(__('Token not found.', $this->plugin->name));
			}

			/**
			 * {@inheritDoc}
			 */
			public function storeAccessToken($service, TokenInterface $token)
			{
				$this->data['tokens'][$service] = serialize($token);
				set_transient($this->transient, $this->data, $this->ttl);

				return $this; // Allow chaining.
			}

			/**
			 * {@inheritDoc}
			 */
			public function clearToken($service)
			{
				unset($this->data['tokens'][$service]);
				set_transient($this->transient, $this->data, $this->ttl);

				return $this; // Allow chaining.
			}

			/**
			 * {@inheritDoc}
			 */
			public function clearAllTokens()
			{
				unset($this->data['tokens']);
				set_transient($this->transient, $this->data, $this->ttl);

				return $this; // Allow chaining.
			}

			/**
			 * {@inheritDoc}
			 */
			public function hasAuthorizationState($service)
			{
				return !empty($this->data['states'][$service]);
			}

			/**
			 * {@inheritDoc}
			 */
			public function retrieveAuthorizationState($service)
			{
				if($this->hasAuthorizationState($service))
					return unserialize($this->data['states'][$service]);

				throw new AuthorizationStateNotFoundException(__('State not found.', $this->plugin->name));
			}

			/**
			 * {@inheritDoc}
			 */
			public function storeAuthorizationState($service, $state)
			{
				$this->data['states'][$service] = serialize($state);
				set_transient($this->transient, $this->data, $this->ttl);

				return $this; // Allow chaining.
			}

			/**
			 * {@inheritDoc}
			 */
			public function clearAuthorizationState($service)
			{
				unset($this->data['states'][$service]);
				set_transient($this->transient, $this->data, $this->ttl);

				return $this; // Allow chaining.
			}

			/**
			 * {@inheritDoc}
			 */
			public function clearAllAuthorizationStates()
			{
				unset($this->data['states']);
				set_transient($this->transient, $this->data, $this->ttl);

				return $this; // Allow chaining.
			}
		}
	}
}