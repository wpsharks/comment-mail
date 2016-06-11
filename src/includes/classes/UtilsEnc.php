<?php
/**
 * Encryption Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Encryption Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsEnc extends AbsBase
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
     * Encryption/decryption key to use.
     *
     * @param string $key Force a specific key?
     *
     * @return string Encryption/decryption key.
     */
    public function key($key = '')
    {
        if (($key = trim((string) $key))) {
            return $key;
        }
        return $key = wp_salt();
    }

    /**
     * Generates an HMAC-SHA256 signature.
     *
     * @param string $string Input string/data, to be signed by this routine.
     * @param string $key    Optional. Key used for encryption.
     *                       Defaults to the one configured for the plugin.
     * @param bool   $raw    Optional. Defaults to a FALSE value.
     *                       If true, the signature is returned as raw binary data, as opposed to lowercase hexits.
     *
     * @return string An HMAC-SHA256 signature string. Always 64 characters in length (URL safe).
     */
    public function hmacSha256Sign($string, $key = '', $raw = false)
    {
        return hash_hmac('sha256', (string) $string, $this->key((string) $key), (boolean) $raw);
    }

    /**
     * A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).
     *
     * @since 141111 First documented version.
     *
     * @note  32-bit systems usually have `PHP_INT_MAX` = `2147483647`.
     *    We limit `mt_rand()` to a max of `999999999`.
     *
     * @note  A max possible length of 20 chars assumes this function
     *    will not be called after `Sat, 20 Nov 2286 17:46:39 GMT`.
     *    At which point a UNIX timestamp will grow in size.
     *
     * @note  Key always begins with a `k` to prevent PHP's `is_numeric()`
     *    function from ever thinking it's a number in a different representation.
     *    See: <http://php.net/manual/en/function.is-numeric.php> for further details.
     *
     * @return string A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).
     */
    public function uunnciKey20Max()
    {
        $microtime_19_max = number_format(microtime(true), 9, '.', '');
        // e.g. `9999999999`.`999999999` (max decimals: `9`, max overall precision: `19`).
        // Assuming timestamp is never > 10 digits; i.e. before `Sat, 20 Nov 2286 17:46:39 GMT`.

        list($seconds_10_max, $microseconds_9_max) = explode('.', $microtime_19_max, 2);
        // e.g. `array(`9999999999`, `999999999`)`. Max total digits combined: `19`.

        $seconds_base36      = base_convert($seconds_10_max, '10', '36'); // e.g. max `9999999999`, to base 36.
        $microseconds_base36 = base_convert($microseconds_9_max, '10', '36'); // e.g. max `999999999`, to base 36.
        $mt_rand_base36      = base_convert(mt_rand(1, 999999999), '10', '36'); // e.g. max `999999999`, to base 36.
        $key                 = 'k'.$mt_rand_base36.$seconds_base36.$microseconds_base36; // e.g. `kgjdgxr4ldqpdrgjdgxr`.

        return $key; // Max possible value: `kgjdgxr4ldqpdrgjdgxr` (20 chars).
    }

    /**
     * RIJNDAEL 256: two-way encryption/decryption, with a URL-safe base64 wrapper.
     *
     * @note This falls back on XOR encryption/decryption when/if mcrypt is not possible.
     *
     * @note Usually, it's better to use these `encrypt()` / `decrypt()` functions instead of XOR encryption;
     *    because RIJNDAEL 256 offers MUCH better security. However, `xencrypt()` / `xdecrypt()` offer true consistency,
     *    making them a better choice in certain scenarios. That is, XOR encrypted strings always offer the same representation
     *    of the original string; whereas RIJNDAEL 256 changes randomly, making it difficult to use comparison algorithms.
     *
     * @param string $string   A string of data to encrypt.
     * @param string $key      Optional. Key used for encryption.
     *                         Defaults to the one configured for the plugin.
     * @param bool   $w_md5_cs Optional. Defaults to TRUE (recommended).
     *                         When TRUE, an MD5 checksum is used in the encrypted string.
     *
     * @throws \exception If string encryption fails.
     * @return string Encrypted string.
     *
     */
    public function encrypt($string, $key = '', $w_md5_cs = true)
    {
        $string = (string) $string;
        $key    = (string) $key;

        if (!isset($string[0])) { // Nothing to encrypt?
            return $base64 = ''; // Nothing to do.
        }
        if (!extension_loaded('mcrypt')
            || !in_array('rijndael-256', mcrypt_list_algorithms(), true)
            || !in_array('cbc', mcrypt_list_modes(), true)
        ) {
            return $this->xEncrypt($string, $key, $w_md5_cs);
        }
        $string = '~r2|'.$string; // A short `rijndael-256` identifier.
        $key    = (string) substr($this->key($key), 0, mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
        $iv     = wp_generate_password(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), false);

        if (!is_string($e = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC, $iv)) || !isset($e[0])) {
            throw new \exception(__('String encryption failed; `$e` is NOT string; or it has no length.', 'comment-mail'));
        }
        $e = '~r2:'.$iv.($w_md5_cs ? ':'.md5($e) : '').'|'.$e; // Pack components.

        return $base64 = $this->base64UrlSafeEncode($e);
    }

    /**
     * RIJNDAEL 256: two-way encryption/decryption, with a URL-safe base64 wrapper.
     *
     * @note This falls back on XOR encryption/decryption when/if mcrypt is not possible.
     *
     * @note Usually, it's better to use these `encrypt()` / `decrypt()` functions instead of XOR encryption;
     *    because RIJNDAEL 256 offers MUCH better security. However, `xencrypt()` / `xdecrypt()` offer true consistency,
     *    making them a better choice in certain scenarios. That is, XOR encrypted strings always offer the same representation
     *    of the original string; whereas RIJNDAEL 256 changes randomly, making it difficult to use comparison algorithms.
     *
     * @param string $base64 A string of data to decrypt.
     *                       Should still be base64 encoded.
     * @param string $key    Optional. Key used originally for encryption.
     *                       Defaults to the one configured for the plugin.
     *
     * @throws \exception If a validated RIJNDAEL 256 string decryption fails.
     * @return string Decrypted string, or an empty string if validation fails.
     *                Validation may fail due to an invalid checksum, or a missing component in the encrypted string.
     *                For security purposes, this returns an empty string on validation failures.
     *
     */
    public function decrypt($base64, $key = '')
    {
        $base64 = (string) $base64;
        $key    = (string) $key;

        if (!isset($base64[0])) { // Nothing to decrypt?
            return $string = ''; // Nothing to do.
        }
        if (!extension_loaded('mcrypt')
            || !in_array('rijndael-256', mcrypt_list_algorithms(), true)
            || !in_array('cbc', mcrypt_list_modes(), true)
            || !strlen($e = $this->base64UrlSafeDecode($base64))
            || !preg_match('/^~r2\:(?P<iv>[a-zA-Z0-9]+)(?:\:(?P<md5>[a-zA-Z0-9]+))?\|(?P<e>.*)$/s', $e, $iv_md5_e)
        ) {
            return $this->xDecrypt($base64, $key); // Try XOR decryption instead :-)
        }
        if (!isset($iv_md5_e['iv'][0], $iv_md5_e['e'][0])) {
            return $string = ''; // Components missing.
        }
        if (isset($iv_md5_e['md5'][0]) && $iv_md5_e['md5'] !== md5($iv_md5_e['e'])) {
            return $string = ''; // Invalid checksum; automatic failure.
        }
        $key = (string) substr($this->key($key), 0, mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));

        if (!is_string($string = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $iv_md5_e['e'], MCRYPT_MODE_CBC, $iv_md5_e['iv'])) || !isset($string[0])) {
            throw new \exception(__('String decryption failed; `$string` is NOT a string, or it has no length.', 'comment-mail'));
        }
        if (!strlen($string = preg_replace('/^~r2\|/', '', $string, 1, $r2)) || !$r2) {
            return $string = ''; // Missing packed components.
        }
        return $string = rtrim($string, "\0\4"); // See: <http://www.asciitable.com/>.
    }

    /**
     * XOR two-way encryption/decryption, with a base64 wrapper.
     *
     * @note Usually, it's better to use the `encrypt()` / `decrypt()` functions instead of XOR encryption;
     *    because RIJNDAEL 256 offers MUCH better security. However, `xencrypt()` / `xdecrypt()` offer true consistency,
     *    making them a better choice in certain scenarios. That is, XOR encrypted strings always offer the same representation
     *    of the original string; whereas RIJNDAEL 256 changes randomly, making it difficult to use comparison algorithms.
     *
     * @param string $string   A string of data to encrypt.
     * @param string $key      Optional. Key used for encryption.
     *                         Defaults to the one configured for the plugin.
     * @param bool   $w_md5_cs Optional. Defaults to TRUE (recommended).
     *                         When TRUE, an MD5 checksum is used in the encrypted string.
     *
     * @throws \exception If string encryption fails.
     * @return string Encrypted string.
     *
     */
    public function xEncrypt($string, $key = '', $w_md5_cs = true)
    {
        $string = (string) $string;
        $key    = (string) $key;

        if (!isset($string[0])) { // Nothing to encrypt?
            return $base64 = ''; // Nothing to do.
        }
        for ($key = $this->key($key), $string = '~xe|'.$string, $_i = 1, $e = ''; $_i <= strlen($string); ++$_i) {
            $_char     = (string) substr($string, $_i - 1, 1);
            $_key_char = (string) substr($key, ($_i % strlen($key)) - 1, 1);
            $e .= chr(ord($_char) + ord($_key_char));
        }
        unset($_i, $_char, $_key_char); // Housekeeping.

        if (!isset($e[0])) { // Hmm, unknown encryption failure?
            throw new \exception(__('String encryption failed; `$e` has no length.', 'comment-mail'));
        }
        $e = '~xe'.($w_md5_cs ? ':'.md5($e) : '').'|'.$e; // Pack components.

        return $base64 = $this->base64UrlSafeEncode($e);
    }

    /**
     * XOR two-way encryption/decryption, with a base64 wrapper.
     *
     * @note Usually, it's better to use the `encrypt()` / `decrypt()` functions instead of XOR encryption;
     *    because RIJNDAEL 256 offers MUCH better security. However, `xencrypt()` / `xdecrypt()` offer true consistency,
     *    making them a better choice in certain scenarios. That is, XOR encrypted strings always offer the same representation
     *    of the original string; whereas RIJNDAEL 256 changes randomly, making it difficult to use comparison algorithms.
     *
     * @param string $base64 A string of data to decrypt.
     *                       Should still be base64 encoded.
     * @param string $key    Optional. Key used originally for encryption.
     *                       Defaults to the one configured for the plugin.
     *
     * @throws \exception If a validated XOR string decryption fails.
     * @return string Decrypted string, or an empty string if validation fails.
     *                Validation may fail due to an invalid checksum, or a missing component in the encrypted string.
     *                For security purposes, this returns an empty string on validation failures.
     *
     */
    public function xDecrypt($base64, $key = '')
    {
        $base64 = (string) $base64;
        $key    = (string) $key;

        if (!isset($base64[0])) { // Nothing to decrypt?
            return $string = ''; // Nothing to do.
        }
        if (!strlen($e = $this->base64UrlSafeDecode($base64))
            || !preg_match('/^~xe(?:\:(?P<md5>[a-zA-Z0-9]+))?\|(?P<e>.*)$/s', $e, $md5_e)
        ) {
            return $string = ''; // Components missing.
        }
        if (!isset($md5_e['e'][0])) { // Totally empty?
            return $string = ''; // Components missing.
        }
        if (isset($md5_e['md5'][0]) && $md5_e['md5'] !== md5($md5_e['e'])) {
            return $string = ''; // Invalid checksum; automatic failure.
        }
        for ($key = $this->key($key), $_i = 1, $string = ''; $_i <= strlen($md5_e['e']); ++$_i) {
            $_char     = (string) substr($md5_e['e'], $_i - 1, 1);
            $_key_char = (string) substr($key, ($_i % strlen($key)) - 1, 1);
            $string .= chr(ord($_char) - ord($_key_char));
        }
        unset($_i, $_char, $_key_char); // Housekeeping.

        if (!isset($string[0])) { // Hmm, unknown decryption failure?
            throw new \exception(__('String decryption failed; `$string` has no length.', 'comment-mail'));
        }
        if (!strlen($string = preg_replace('/^~xe\|/', '', $string, 1, $xe)) || !$xe) {
            return $string = ''; // Missing packed components.
        }
        return $string; // We can return the decrypted string now.
    }

    /**
     * Base64 URL-safe encoding.
     *
     * @param string $string             Input string to be base64 encoded.
     * @param array  $url_unsafe_chars   Optional array.
     *                                   An array of un-safe characters.
     *                                   Defaults to: `array('+', '/')`.
     * @param array  $url_safe_chars     Optional array.
     *                                   An array of safe character replacements.
     *                                   Defaults to: `array('-', '_')`.
     * @param string $trim_padding_chars Optional string.
     *                                   A string of padding chars to rtrim.
     *                                   Defaults to: `=`.
     *
     * @throws \exception If the call to `base64_encode()` fails.
     * @return string The base64 URL-safe encoded string.
     *
     */
    public function base64UrlSafeEncode($string, array $url_unsafe_chars = ['+', '/'], array $url_safe_chars = ['-', '_'], $trim_padding_chars = '=')
    {
        $string             = (string) $string;
        $trim_padding_chars = (string) $trim_padding_chars;

        if (!is_string($base64_url_safe = base64_encode($string))) {
            throw new \exception(__('Base64 encoding failed (`$base64_url_safe` is NOT a string).', 'comment-mail'));
        }
        $base64_url_safe = str_replace($url_unsafe_chars, $url_safe_chars, $base64_url_safe);
        $base64_url_safe = isset($trim_padding_chars[0]) ? rtrim($base64_url_safe, $trim_padding_chars) : $base64_url_safe;

        return $base64_url_safe;
    }

    /**
     * Base64 URL-safe decoding.
     *
     * @param string $base64_url_safe    Input string to be base64 decoded.
     * @param array  $url_unsafe_chars   Optional array.
     *                                   An array of un-safe characters.
     *                                   Defaults to: `array('+', '/')`.
     * @param array  $url_safe_chars     Optional array.
     *                                   An array of safe character replacements.
     *                                   Defaults to: `array('-', '_')`.
     * @param string $trim_padding_chars Optional string.
     *                                   A string of padding chars to rtrim.
     *                                   Defaults to: `=`.
     *
     * @throws \exception If the call to `base64_decode()` fails.
     * @return string The decoded string. Or, possibly the original string, if `$base64_url_safe`
     *                was NOT base64 encoded to begin with. Helps prevent accidental data corruption.
     *
     */
    public function base64UrlSafeDecode($base64_url_safe, array $url_unsafe_chars = ['+', '/'], array $url_safe_chars = ['-', '_'], $trim_padding_chars = '=')
    {
        $base64_url_safe    = (string) $base64_url_safe;
        $trim_padding_chars = (string) $trim_padding_chars;

        $string = isset($trim_padding_chars[0]) ? rtrim($base64_url_safe, $trim_padding_chars) : $base64_url_safe;
        $string = isset($trim_padding_chars[0]) ? str_pad($string, strlen($string) % 4, '=', STR_PAD_RIGHT) : $string;
        $string = str_replace($url_safe_chars, $url_unsafe_chars, $string);

        if (!is_string($string = base64_decode($string, true))) {
            throw new \exception(__('Base64 decoding failed (`$string` is NOT a string).', 'comment-mail'));
        }
        return $string;
    }

    /**
     * Gets a cookie value.
     *
     * @param string $name Name of the cookie.
     *
     * @return string Cookie string value.
     */
    public function getCookie($name)
    {
        if (!($name = trim((string) $name))) {
            return ''; // Not possible.
        }
        if (isset($_COOKIE[$name][0]) && is_string($_COOKIE[$name])) {
            $value = $this->decrypt($_COOKIE[$name]);
        }
        return isset($value[0]) ? $value : '';
    }

    /**
     * Sets a cookie.
     *
     * @param string $name          Name of the cookie.
     * @param string $value         Value for this cookie.
     * @param int    $expires_after Optional. Time (in seconds) this cookie should last for. Defaults to `31556926` (one year).
     *                              If this is set to anything <= `0`, the cookie will expire automatically after the current browser session.
     *
     * @throws \exception If headers have already been sent; i.e. if not possible.
     */
    public function setCookie($name, $value, $expires_after = 31556926)
    {
        if (!($name = trim((string) $name))) {
            return; // Not possible.
        }
        $value         = (string) $value;
        $expires_after = (integer) $expires_after;

        $value   = $this->encrypt($value);
        $expires = $expires_after > 0 ? time() + $expires_after : 0;

        if (headers_sent()) { // Headers sent already?
            throw new \exception(__('Doing it wrong! Headers have already been sent.', 'comment-mail'));
        }
        setcookie($name, $value, $expires, COOKIEPATH, COOKIE_DOMAIN);
        setcookie($name, $value, $expires, SITECOOKIEPATH, COOKIE_DOMAIN);

        if ($name !== TEST_COOKIE) {
            $_COOKIE[$name] = $value; // Update in real-time.
        }
    }

    /**
     * Deletes a cookie.
     *
     * @param string $name Name of the cookie.
     *
     * @throws \exception If headers have already been sent; i.e. if not possible.
     */
    public function deleteCookie($name)
    {
        if (!($name = trim((string) $name))) {
            return; // Not possible.
        }
        if (headers_sent()) { // Headers sent already?
            throw new \exception(__('Doing it wrong! Headers have already been sent.', 'comment-mail'));
        }
        setcookie($name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
        setcookie($name, '', time() - 3600, SITECOOKIEPATH, COOKIE_DOMAIN);

        if ($name !== TEST_COOKIE) {
            $_COOKIE[$name] = ''; // Update in real-time.
        }
    }
}
