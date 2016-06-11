<?php
/**
 * File System Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * File System Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsFs extends AbsBase
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
     * Finds a writable tmp directory.
     *
     * @since 150329 Improving tmp directory detection.
     *
     * @throws \exception On any failure.
     *
     * @return string Writable tmp directory.
     */
    public function tmpDir()
    {
        $tmp_dir = $this->nSeps(get_temp_dir());

        if (!$tmp_dir || !@is_dir($tmp_dir) || !@is_writable($tmp_dir)) {
            throw new \exception(__('Unable to find a writable tmp directory.', 'comment-mail'));
        }
        return $tmp_dir; // Writable tmp directory.
    }

    /**
     * Adds tmp suffix to a directory|file `/path`.
     *
     * @since 141111 First documented version.
     *
     * @param string $path Directory|file `/path`.
     *
     * @return string Suffixed directory|file `/path`.
     */
    public function tmpSuffix($path)
    {
        $path = (string) $path; // Force string value.
        $path = rtrim($path, DIRECTORY_SEPARATOR.'\\/');

        return $path.'-'.str_replace('.', '', uniqid('', true)).'-tmp';
    }

    /**
     * Lowercase file extension.
     *
     * @since 141111 First documented version.
     *
     * @param string $path Directory|file `/path`.
     *
     * @return string File extension (lowercase).
     */
    public function extension($path)
    {
        return strtolower(ltrim((string) strrchr(basename((string) $path), '.'), '.'));
    }

    /**
     * Normalizes `/path` separators.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $path                 Directory|file `/path`.
     * @param bool  $allow_trailing_slash Defaults to FALSE.
     *                                    If TRUE; and `$path` contains a trailing slash; we'll leave it there.
     *
     * @return string Normalized directory|file `/path`.
     */
    public function nSeps($path, $allow_trailing_slash = false)
    {
        $path = (string) $path; // Force string value.

        if (!isset($path[0])) {
            return ''; // Empty.
        }
        if (strpos($path, '://' !== false)) { // A stream wrapper?
            $stream_wrapper_regex = '/^(?P<stream_wrapper>[a-zA-Z0-9]+)\:\/\//';
            if (preg_match($stream_wrapper_regex, $path, $stream_wrapper)) {
                $path = preg_replace($stream_wrapper_regex, '', $path);
            }
        }
        if (strpos($path, ':' !== false)) { // A Windows® drive letter?
            $drive_letter_regex = '/^(?P<drive_letter>[a-zA-Z])\:[\/\\\\]/';
            if (preg_match($drive_letter_regex, $path)) { // It has a Windows® drive letter?
                $path = preg_replace_callback($drive_letter_regex, create_function('$m', 'return strtoupper($m[0]);'), $path);
            }
        }
        $path = preg_replace('/\/+/', '/', str_replace([DIRECTORY_SEPARATOR, '\\', '/'], '/', $path));
        $path = ($allow_trailing_slash) ? $path : rtrim($path, '/'); // Strip trailing slashes.

        if (!empty($stream_wrapper[0])) { // Stream wrapper (force lowercase).
            $path = strtolower($stream_wrapper[0]).$path;
        }
        return $path; // Normalized now.
    }

    /**
     * Checks an uploaded file `/path`.
     *
     * @since 141111 First documented version.
     *
     * @param string $path                  A file `/path` to check.
     *                                      If it's an uploaded file, use the `tmp_name`.
     * @param bool   $require_uploaded_file Defaults to a `FALSE` value.
     *
     * @throws \exception If a security flag is triggered for any reason.
     */
    public function checkPathSecurity($path, $require_uploaded_file = false)
    {
        $path = (string) $path; // Force string value.

        if (!isset($path[0])) {
            return; // Empty.
        }
        if ($require_uploaded_file && (empty($_FILES) || !is_uploaded_file($path))) {
            throw new \exception(sprintf(__('Security flag. Not an uploaded file: `%1$s`.', 'comment-mail'), $path));
        }
        $path = $this->nSeps($path); // Normalize separators for remaining checks.

        if (strpos($path, '~') !== false // A backup file?
            || strpos($path, './') !== false || strpos($path, '..') !== false
            || strpos($path, '/.') !== false || stripos(basename($path), 'config') !== false
        ) {
            throw new \exception(sprintf(__('Security flag. Dangerous file path: `%1$s`.', 'comment-mail'), $path));
        }
    }

    /**
     * Abbreviated byte notation for file sizes.
     *
     * @param float $bytes     File size in bytes. A (float) value.
     *                         We need this converted to a (float), so it's possible to deal with numbers beyond that of an integer.
     * @param int   $precision Number of decimals to use.
     *
     * @return string Byte notation.
     */
    public function bytesAbbr($bytes, $precision = 2)
    {
        $bytes     = (float) $bytes;
        $precision = (integer) $precision;

        $precision = $precision >= 0 ? $precision : 2;
        $units     = ['bytes', 'kbs', 'MB', 'GB', 'TB'];

        $bytes = $bytes > 0 ? $bytes : 0;
        $power = floor(($bytes ? log($bytes) : 0) / log(1024));

        $abbr_bytes = round($bytes / pow(1024, $power), $precision);
        $abbr       = $units[min($power, count($units) - 1)];

        if ($abbr_bytes === (float) 1 && $abbr === 'bytes') {
            $abbr = 'byte'; // Quick fix here.
        } elseif ($abbr_bytes === (float) 1 && $abbr === 'kbs') {
            $abbr = 'kb'; // Quick fix here.
        }
        return $abbr_bytes.' '.$abbr;
    }

    /**
     * Converts an abbreviated byte notation into bytes.
     *
     * @param string $string A string value in byte notation.
     *
     * @return float A float indicating the number of bytes.
     */
    public function abbrBytes($string)
    {
        $string = trim((string) $string);

        $notation = '/^(?P<value>[0-9\.]+)\s*(?P<modifier>bytes|byte|kbs|kb|k|mb|m|gb|g|tb|t)$/i';

        if (!preg_match($notation, $string, $_op)) {
            return (float) 0;
        }
        $value    = (float) $_op['value'];
        $modifier = strtolower($_op['modifier']);
        unset($_op); // Housekeeping.

        switch ($modifier) {// Fall through based on modifier.

            case 't':
            case 'tb':
                $value *= 1024;
            // else fall through...

            case 'g':
            case 'gb':
                $value *= 1024;
            // else fall through...

            case 'm':
            case 'mb':
                $value *= 1024;
            // else fall through...

            case 'k':
            case 'kb':
            case 'kbs':
                $value *= 1024;
        }
        return (float) $value;
    }

    /**
     * Inline SVG plugin icon.
     *
     * @return string SVG icon for inline markup.
     */
    public function inlineIconSvg()
    {
        if (!is_null($icon = &$this->staticKey(__FUNCTION__))) {
            return $icon; // Already cached this.
        }
        return $icon = file_get_contents(dirname(dirname(__DIR__)).'/client-s/images/inline-icon.svg');
    }
}
