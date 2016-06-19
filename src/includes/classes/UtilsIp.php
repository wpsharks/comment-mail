<?php
/**
 * IP Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * IP Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsIp extends AbsBase
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
     * @note  This supports both IPv4 and IPv6 addresses.
     */
    public function current()
    {
        if (!is_null($ip = &$this->staticKey(__FUNCTION__))) {
            return $ip; // Cached this already.
        }
        if (!empty($_SERVER['REMOTE_ADDR']) && $this->plugin->options['prioritize_remote_addr']) {
            if (($_valid_public_ip = $this->validPublic($_SERVER['REMOTE_ADDR']))) {
                return $ip = $_valid_public_ip;
            }
        }
        $sources = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_VIA',
            'REMOTE_ADDR',
        ];
        $sources = apply_filters(__METHOD__.'_sources', $sources);

        foreach ($sources as $_source) { // Try each of these; in order.
            if (!empty($_SERVER[$_source])) { // Does the source key exist at all?
                if (($_valid_public_ip = $this->validPublic($_SERVER[$_source]))) {
                    return $ip = $_valid_public_ip;
                }
            }
        }
        unset($_source, $_valid_public_ip); // Housekeeping.

        if (!empty($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) {
            return $ip = strtolower($_SERVER['REMOTE_ADDR']);
        }
        return $ip = 'unknown'; // Not possible.
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
     * @note  This supports both IPv4 and IPv6 addresses.
     */
    public function validPublic($list_of_possible_ips)
    {
        if (!$list_of_possible_ips || !is_string($list_of_possible_ips)) {
            return ''; // Empty or invalid data.
        }
        if (!($list_of_possible_ips = trim($list_of_possible_ips))) {
            return ''; // Not possible; i.e., empty string.
        }
        foreach (preg_split('/[\s;,]+/', $list_of_possible_ips, null, PREG_SPLIT_NO_EMPTY) as $_possible_ip) {
            if (($_valid_public_ip = filter_var(strtolower($_possible_ip), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
                return $_valid_public_ip;
            }
        }
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
        if (($geo = $this->geoData($ip))) {
            return $geo->region;
        }
        return ''; // Empty string on failure.
    }

    /**
     * Current user's geographic region code.
     *
     * @since 141111 First documented version.
     *
     * @return string Current user's geographic region code; if possible.
     */
    public function currentRegion()
    {
        if (($geo = $this->geoData($this->current()))) {
            return $geo->region;
        }
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
        if (($geo = $this->geoData($ip))) {
            return $geo->country;
        }
        return ''; // Empty string on failure.
    }

    /**
     * Current user's geographic country code.
     *
     * @since 141111 First documented version.
     *
     * @return string Current user's geographic country code; if possible.
     */
    public function currentCountry()
    {
        if (($geo = $this->geoData($this->current()))) {
            return $geo->country;
        }
        return ''; // Empty string on failure.
    }

    /**
     * Geographic location data from IP address.
     *
     * @since 141111 First documented version.
     *
     * @param string $ip An IP address to pull geographic data for.
     *
     * @throws \exception If unable to create cache directory.
     * @return \stdClass|bool Geo location data from IP address.
     *                        This returns `FALSE` if not possible to obtain geo location data.
     *
     */
    public function geoData($ip)
    {
        # Valid the input IP address; do we have one?

        if (!($ip = trim(strtolower((string) $ip)))) {
            return false; // Not possible.
        }
        # Is geo-location tracking even enabled?

        if (!$this->plugin->options['geo_location_tracking_enable']) {
            return false; // Not enabled at this time.
        }
        # Check the static object cache.

        if (!is_null($geo = &$this->staticKey(__FUNCTION__, $ip))) {
            return $geo; // Cached this already.
        }
        # Check the filesystem cache; i.e., tmp directory.

        $tmp_dir    = $this->plugin->utils_fs->tmpDir();
        $cache_dir  = $tmp_dir.'/'.SLUG_TD.'/ip-geo-data';
        $cache_file = $cache_dir.'/'.sha1($ip).'.json';

        if (is_file($cache_file) && filemtime($cache_file) >= strtotime('-30 days')) {
            return $geo = json_decode(file_get_contents($cache_file));
        }
        # Initialize request-related variables.

        $region = $country = ''; // Initialize.

        # Perform remote request to the geoPlugin service.

        if (is_wp_error($response = wp_remote_get('http://www.geoplugin.net/json.gp?ip='.urlencode($ip)))
            || !is_object($json = json_decode(wp_remote_retrieve_body($response))) // Unexpected response?
        ) {
            return $geo = false; // Connection failure; use object cache only in this case.
        }
        # Parse response from geoPlugin service.

        if (!empty($json->geoplugin_regionCode)) { // Have a region code?
            $region = strtoupper(str_pad((string) $json->geoplugin_regionCode, 2, '0', STR_PAD_LEFT));
        }
        if (!empty($json->geoplugin_countryCode)) { // Country code?
            $country = strtoupper((string) $json->geoplugin_countryCode);
        }
        # Fill the object cache; based on data validation here.

        $geo = (object) compact('region', 'country'); // Initialize.

        if (strlen($geo->region) !== 2 || strlen($geo->country) !== 2) {
            $geo = false; // Invalid (or insufficient) data.
        }
        # Cache validated response from geoPlugin service; i.e., the object cache.

        if (strcasecmp($tmp_dir, $this->plugin->utils_fs->nSeps(WP_CONTENT_DIR)) !== 0) {
            if (!is_dir($cache_dir) && !mkdir($cache_dir, 0777, true)) {
                throw new \exception('Unable to create `ip-geo-data` cache directory.');
            }
            file_put_contents($cache_file, json_encode($geo)); // Cache it!
        }
        # Return response; either an object or `FALSE` on failure.

        return $geo; // An object; else `FALSE`.
    }
}
