<?php
/**
 * IP Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{

	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_ip'))
	{
		/**
		 * IP Utilities
		 *
		 * @since 141111 First documented version.
		 */
		class utils_ip extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Get the current visitor's real IP address.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Real IP address; else `unknown` on failure.
			 *
			 * @note This supports both IPv4 and IPv6 addresses.
			 */
			public function current()
			{
				if(!is_null($ip = &$this->static_key(__FUNCTION__)))
					return $ip; // Cached this already.

				if(!empty($_SERVER['REMOTE_ADDR']) && $this->plugin->options['prioritize_remote_addr'])
					if(($_valid_public_ip = $this->valid_public($_SERVER['REMOTE_ADDR'])))
						return ($ip = $_valid_public_ip);

				$sources = array(
					'HTTP_CF_CONNECTING_IP',
					'HTTP_CLIENT_IP',
					'HTTP_X_FORWARDED_FOR',
					'HTTP_X_FORWARDED',
					'HTTP_X_CLUSTER_CLIENT_IP',
					'HTTP_FORWARDED_FOR',
					'HTTP_FORWARDED',
					'HTTP_VIA',
					'REMOTE_ADDR',
				);
				$sources = apply_filters(__METHOD__.'_sources', $sources);

				foreach($sources as $_source) // Try each of these; in order.
				{
					if(!empty($_SERVER[$_source])) // Does the source key exist at all?
						if(($_valid_public_ip = $this->valid_public($_SERVER[$_source])))
							return ($ip = $_valid_public_ip); // A valid public IPv4 or IPv6 address.
				}
				unset($_source, $_valid_public_ip); // Housekeeping.

				if(!empty($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']))
					return ($ip = strtolower($_SERVER['REMOTE_ADDR']));

				return ($ip = 'unknown'); // Not possible.
			}

			/**
			 * Gets a valid/public IP address.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $list_of_possible_ips A single IP or a comma-delimited list of IPs.
			 *
			 * @return string A valid/public IP address (if one is found); else an empty string.
			 *
			 * @note This supports both IPv4 and IPv6 addresses.
			 */
			public function valid_public($list_of_possible_ips)
			{
				if(!$list_of_possible_ips || !is_string($list_of_possible_ips))
					return ''; // Empty or invalid data.

				if(!($list_of_possible_ips = trim($list_of_possible_ips)))
					return ''; // Not possible; i.e., empty string.

				foreach(preg_split('/[\s;,]+/', $list_of_possible_ips, NULL, PREG_SPLIT_NO_EMPTY) as $_possible_ip)
					if(($_valid_public_ip = filter_var(strtolower($_possible_ip), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)))
						return $_valid_public_ip; // A valid public IPv4 or IPv6 address.
				unset($_possible_ip, $_valid_public_ip); // Housekeeping.

				return ''; // Default return value.
			}
		}
	}
}
