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
			 *
			 * @throws \exception If unable to create cache directory.
			 */
			public function geo_data($ip)
			{
				# Valid the input IP address; do we have one?

				if(!($ip = trim(strtolower((string)$ip))))
					return FALSE; // Not possible.

				# Is geo-location tracking even enabled?

				if(!$this->plugin->options['geo_location_tracking_enable'])
					return FALSE; // Not enabled at this time.

				# Check the static object cache.

				if(!is_null($geo = &$this->static_key(__FUNCTION__, $ip)))
					return $geo; // Cached this already.

				# Check the filesystem cache; i.e., tmp directory.

				$tmp_dir    = $this->plugin->utils_fs->tmp_dir();
				$cache_dir  = $tmp_dir.'/'.$this->plugin->slug.'/ip-geo-data';
				$cache_file = $cache_dir.'/'.sha1($ip).'.json';

				if(is_file($cache_file) && filemtime($cache_file) >= strtotime('-30 days'))
					return ($geo = json_decode(file_get_contents($cache_file)));

				# Initialize request-related variables.

				$region = $country = ''; // Initialize.

				# Perform remote request to the geoPlugin service.

				if(is_wp_error($response = wp_remote_get('http://www.geoplugin.net/json.gp?ip='.urlencode($ip)))
				   || !is_object($json = json_decode(wp_remote_retrieve_body($response))) // Unexpected response?
				) return ($geo = FALSE); // Connection failure; use object cache only in this case.

				# Parse response from geoPlugin service.

				if(!empty($json->geoplugin_regionCode)) // Have a region code?
					$region = strtoupper(str_pad((string)$json->geoplugin_regionCode, 2, '0', STR_PAD_LEFT));

				if(!empty($json->geoplugin_countryCode)) // Country code?
					$country = strtoupper((string)$json->geoplugin_countryCode);

				# Fill the object cache; based on data validation here.

				$geo = (object)compact('region', 'country'); // Initialize.

				if(strlen($geo->region) !== 2 || strlen($geo->country) !== 2)
					$geo = FALSE; // Invalid (or insufficient) data.

				# Cache validated response from geoPlugin service; i.e., the object cache.

				if(strcasecmp($tmp_dir, $this->plugin->utils_fs->n_seps(WP_CONTENT_DIR)) !== 0)
				{
					if(!is_dir($cache_dir) && !mkdir($cache_dir, 0777, TRUE))
						throw new \exception('Unable to create `ip-geo-data` cache directory.');

					file_put_contents($cache_file, json_encode($geo)); // Cache it!
				}
				# Return response; either an object or `FALSE` on failure.

				return $geo; // An object; else `FALSE`.
			}
		}
	}
}