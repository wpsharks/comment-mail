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
			 * @return string Real IP address, else `unknown` on failure.
			 *
			 * @note This supports both IPv4 and IPv6 addresses.
			 */
			public function current()
			{
				if(!is_null($ip = &$this->static_key(__FUNCTION__)))
					return $ip; // Cached this already.

				$_s = $_SERVER; // Copy of current `$_SERVER` vars.

				if($this->plugin->options['prioritize_remote_addr'])
					if(($REMOTE_ADDR = $this->valid_public($_s['REMOTE_ADDR'])))
						return ($ip = $REMOTE_ADDR);

				$sources = array(
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
					if(!isset($$_source) && ($$_source = $this->valid_public($_s[$_source])))
						return ($ip = $$_source); // A valid, public IPv4 or IPv6 address.
				unset($_source); // Housekeeping.

				if(!empty($_s['REMOTE_ADDR']) && is_string($_s['REMOTE_ADDR']))
					return ($ip = strtolower($_s['REMOTE_ADDR']));

				return ($ip = 'unknown'); // Not possible.
			}

			/**
			 * Gets a valid/public IP address.
			 *
			 * @param string $possible_ips A single IP, or a possible comma-delimited list of IPs.
			 *    Pass by reference to avoid PHP notices while checking multiple sources.
			 *
			 * @return string A valid/public IP address (if one is found), else an empty string.
			 *
			 * @note This supports both IPv4 and IPv6 addresses.
			 */
			public function valid_public(&$possible_ips)
			{
				if(!$possible_ips || !is_string($possible_ips))
					return ''; // Empty or invalid data.

				foreach(preg_split('/[\s;,]+/', trim($possible_ips), NULL, PREG_SPLIT_NO_EMPTY) as $_possible_ip)
					if(($_possible_ip = filter_var(strtolower($_possible_ip), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)))
						return $_possible_ip; // A valid, public IPv4 or IPv6 address.
				unset($_possible_ip); // Housekeeping.

				return ''; // Default return value.
			}

			/**
			 * Geographic region code for given IP address.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $ip An IP address to pull geographic data for.
			 *
			 * @return string Geographic region code for given IP address.
			 */
			public function region($ip)
			{
				if(($geo = $this->geo_data($ip)))
					return $geo->region;

				return ''; // Empty string on failure.
			}

			/**
			 * Current user's geographic region code.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current user's geographic region code; if possible.
			 */
			public function current_region()
			{
				if(($geo = $this->geo_data($this->current())))
					return $geo->region;

				return ''; // Empty string on failure.
			}

			/**
			 * Geographic country code for given IP address.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $ip An IP address to pull geographic data for.
			 *
			 * @return string Geographic country code for given IP address.
			 */
			public function country($ip)
			{
				if(($geo = $this->geo_data($ip)))
					return $geo->country;

				return ''; // Empty string on failure.
			}

			/**
			 * Current user's geographic country code.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Current user's geographic country code; if possible.
			 */
			public function current_country()
			{
				if(($geo = $this->geo_data($this->current())))
					return $geo->country;

				return ''; // Empty string on failure.
			}

			/**
			 * Geographic location data from IP address.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $ip An IP address to pull geographic data for.
			 *
			 * @return \stdClass|boolean Geo location data from IP address.
			 *    This returns `FALSE` if not possible to obtain geo location data.
			 */
			public function geo_data($ip)
			{
				if(!($ip = trim(strtolower((string)$ip))))
					return FALSE; // Not possible.

				if(!$this->plugin->options['geo_location_tracking_enable'])
					return FALSE; // Not enabled at this time.

				if(!is_null($geo = &$this->static_key(__FUNCTION__, $ip)))
					return $geo; // Cached this already.

				if(!is_wp_error($response = wp_remote_get('http://www.geoplugin.net/json.gp?ip='.urlencode($ip))))
					if(($body = wp_remote_retrieve_body($response)) && is_object($json = json_decode($body)))
						if(!empty($json->geoplugin_regionCode) && !empty($json->geoplugin_countryCode))
						{
							$region  = strtoupper(str_pad((string)$json->geoplugin_regionCode, 2, '0', STR_PAD_LEFT));
							$country = strtoupper((string)$json->geoplugin_countryCode);

							if(strlen($region) === 2 && strlen($country) === 2)
								return ($geo = (object)compact('region', 'country'));
						}
				return ($geo = FALSE); // Default behavior.
			}
		}
	}
}