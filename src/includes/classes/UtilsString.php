<?php
/**
 * String Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * String Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsString extends AbsBase
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
     * Strips slashes.
     *
     * @since 141111 First documented version.
     *
     * @param string $string See {@link strip_deep()}.
     *
     * @return string See {@link strip_deep()}.
     */
    public function strip($string)
    {
        return $this->stripDeep((string) $string);
    }

    /**
     * Strips slashes in strings deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $values Anything can be converted into a stripped string.
     *                      Actually, objects can't, but this recurses into objects.
     *
     * @return string|array|object Stripped string, array, object.
     */
    public function stripDeep($values)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->stripDeep($_values);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // Stripped deeply.
        }
        $string = (string) $values;

        return stripslashes($string);
    }

    /**
     * Trims string.
     *
     * @since 141111 First documented version.
     *
     * @param string $string      See {@link trim_deep()}.
     * @param string $chars       See {@link trim_deep()}.
     * @param string $extra_chars See {@link trim_deep()}.
     *
     * @return string See {@link trim_deep()}.
     */
    public function trim($string, $chars = '', $extra_chars = '')
    {
        return $this->trimDeep((string) $string, $chars, $extra_chars);
    }

    /**
     * Trims strings deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed  $values      Any value can be converted into a trimmed string.
     *                            Actually, objects can't, but this recurses into objects.
     * @param string $chars       Specific chars to trim.
     *                            Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
     * @param string $extra_chars Additional chars to trim.
     *
     * @return string|array|object Trimmed string, array, object.
     */
    public function trimDeep($values, $chars = '', $extra_chars = '')
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->trimDeep($_values, $chars, $extra_chars);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // Trimmed deeply.
        }
        $string      = (string) $values;
        $chars       = (string) $chars;
        $extra_chars = (string) $extra_chars;

        $chars = isset($chars[0]) ? $chars : " \r\n\t\0\x0B";
        $chars = $chars.$extra_chars; // Concatenate.

        return trim($string, $chars);
    }

    /**
     * Trims/strips string.
     *
     * @since 141111 First documented version.
     *
     * @param string $string      See {@link trim_strip_deep()}.
     * @param string $chars       See {@link trim_strip_deep()}.
     * @param string $extra_chars See {@link trim_strip_deep()}.
     *
     * @return string See {@link trim_strip_deep()}.
     */
    public function trimStrip($string, $chars = '', $extra_chars = '')
    {
        return $this->trimStripDeep((string) $string, $chars, $extra_chars);
    }

    /**
     * Trims and strips slashes in strings deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed  $values      Any value can be converted into a trimmed/stripped string.
     *                            Actually, objects can't, but this recurses into objects.
     * @param string $chars       Specific chars to trim.
     *                            Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass.
     * @param string $extra_chars Additional chars to trim.
     *
     * @return string|array|object Trimmed/stripped string, array, object.
     */
    public function trimStripDeep($values, $chars = '', $extra_chars = '')
    {
        return $this->trimDeep($this->stripDeep($values), $chars, $extra_chars);
    }

    /**
     * Trims HTML markup.
     *
     * @param string $string      A string value.
     * @param string $chars       Other specific chars to trim (HTML whitespace is always trimmed).
     *                            Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass this argument and specify additional chars only.
     * @param string $extra_chars Additional specific chars to trim.
     *
     * @return string Trimmed string (HTML whitespace is always trimmed).
     */
    public function trimHtml($string, $chars = '', $extra_chars = '')
    {
        return $this->trimHtmlDeep($string, $chars, $extra_chars);
    }

    /**
     * Trims HTML markup deeply.
     *
     * @param mixed  $values      Any value can be converted into a trimmed string.
     *                            Actually, objects can't, but this recurses into objects.
     * @param string $chars       Other specific chars to trim (HTML whitespace is always trimmed).
     *                            Defaults to PHP's trim: " \r\n\t\0\x0B". Use an empty string to bypass this argument and specify additional chars only.
     * @param string $extra_chars Additional specific chars to trim.
     *
     * @return string|array|object Trimmed string, array, object (HTML whitespace is always trimmed).
     */
    public function trimHtmlDeep($values, $chars = '', $extra_chars = '')
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->trimHtmlDeep($_values, $chars, $extra_chars);
            }
            unset($_key, $_values); // Housekeeping.

            return $this->trimDeep($values, $chars, $extra_chars);
        }
        $string = (string) $values;

        if (is_null($whitespace = &$this->staticKey(__FUNCTION__, 'whitespace'))) {
            $whitespace = implode('|', array_keys($this->html_whitespace));
        }
        $string = preg_replace('/^(?:'.$whitespace.')+|(?:'.$whitespace.')+$/i', '', $string);

        return $this->trim($string, $chars, $extra_chars);
    }

    /**
     * Escape single quotes.
     *
     * @since 141111 First documented version.
     *
     * @param string $string See {@link esc_sq_deep()}.
     * @param int    $times  See {@link esc_sq_deep()}.
     *
     * @return string See {@link esc_sq_deep()}.
     */
    public function escSq($string, $times = 1)
    {
        return $this->escSqDeep((string) $string, $times);
    }

    /**
     * Escapes single quotes deeply.
     *
     * @param mixed $values Any value can be converted into an escaped string.
     *                      Actually, objects can't, but this recurses into objects.
     * @param int   $times  Number of escapes. Defaults to `1`.
     *
     * @return string|array|object Escaped string, array, object.
     */
    public function escSqDeep($values, $times = 1)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->escSqDeep($_values, $times);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        $string = (string) $values;
        $times  = abs((integer) $times);

        return str_replace("'", str_repeat('\\', $times)."'", $string);
    }

    /**
     * Escapes JS line breaks (removes "\r"); and escapes single quotes.
     *
     * @param string $string A string value.
     * @param int    $times  Number of escapes. Defaults to `1`.
     *
     * @return string Escaped string, ready for JavaScript.
     */
    public function escJsSq($string, $times = 1)
    {
        return $this->escJsSqDeep((string) $string, $times);
    }

    /**
     * Escapes JS; and escapes single quotes deeply.
     *
     * @note This follows {@link http://www.json.org JSON} standards, with TWO exceptions.
     *    1. Special handling for line breaks: `\r\n` and `\r` are converted to `\n`.
     *    2. This does NOT escape double quotes; only single quotes.
     *
     * @param mixed $value Any value can be converted into an escaped string.
     *                     Actually, objects can't, but this recurses into objects.
     * @param int   $times Number of escapes. Defaults to `1`.
     *
     * @return string|array|object Escaped string, array, object (ready for JavaScript).
     */
    public function escJsSqDeep($value, $times = 1)
    {
        if (is_array($value) || is_object($value)) {
            foreach ($value as $_key => &$_value) {
                $_value = $this->escJsSqDeep($_value, $times);
            }
            unset($_key, $_value); // Housekeeping.

            return $value; // All done.
        }
        $value = str_replace(["\r\n", "\r", '"'], ["\n", "\n", '%%!dq!%%'], (string) $value);
        $value = str_replace(['%%!dq!%%', "'"], ['"', "\\'"], trim(json_encode($value), '"'));

        return str_replace('\\', str_repeat('\\', abs((integer) $times) - 1).'\\', $value);
    }

    /**
     * Escape double quotes.
     *
     * @since 141111 First documented version.
     *
     * @param string $string See {@link esc_dq_deep()}.
     * @param int    $times  See {@link esc_dq_deep()}.
     *
     * @return string See {@link esc_dq_deep()}.
     */
    public function escDq($string, $times = 1)
    {
        return $this->escDqDeep((string) $string, $times);
    }

    /**
     * Escapes double quotes deeply.
     *
     * @param mixed $values Any value can be converted into an escaped string.
     *                      Actually, objects can't, but this recurses into objects.
     * @param int   $times  Number of escapes. Defaults to `1`.
     *
     * @return string|array|object Escaped string, array, object.
     */
    public function escDqDeep($values, $times = 1)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->escDqDeep($_values, $times);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        $string = (string) $values;
        $times  = abs((integer) $times);

        return str_replace('"', str_repeat('\\', $times).'"', $string);
    }

    /**
     * Escape double quotes for CSV.
     *
     * @since 141111 First documented version.
     *
     * @param string $string See {@link esc_csv_dq_deep()}.
     * @param int    $times  See {@link esc_csv_dq_deep()}.
     *
     * @return string See {@link esc_csv_dq_deep()}.
     */
    public function escCsvDq($string, $times = 1)
    {
        return $this->escCsvDqDeep((string) $string, $times);
    }

    /**
     * Escapes double quotes deeply; for CSV.
     *
     * @param mixed $values Any value can be converted into an escaped string.
     *                      Actually, objects can't, but this recurses into objects.
     * @param int   $times  Number of escapes. Defaults to `1`.
     *
     * @return string|array|object Escaped string, array, object.
     */
    public function escCsvDqDeep($values, $times = 1)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->escCsvDqDeep($_values, $times);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        $string = (string) $values;
        $times  = abs((integer) $times);

        return str_replace('"', str_repeat('"', $times).'"', $string);
    }

    /**
     * String replace ONE time (caSe-insensitive).
     *
     * @since 141111 First documented version.
     *
     * @param string $needle  See {@link str_replace_once_deep()}.
     * @param string $replace See {@link str_replace_once_deep()}.
     * @param string $string  See {@link str_replace_once_deep()}.
     *
     * @return string See {@link str_replace_once_deep()}.
     */
    public function iReplaceOnce($needle, $replace, $string)
    {
        return $this->replaceOnceDeep($needle, $replace, (string) $string, true);
    }

    /**
     * String replace ONE time deeply (caSe-insensitive).
     *
     * @since 141111 First documented version.
     *
     * @param string $needle  See {@link replace_once_deep()}.
     * @param string $replace See {@link replace_once_deep()}.
     * @param mixed  $values  See {@link replace_once_deep()}.
     *
     * @return string|array|object See {@link replace_once_deep()}.
     */
    public function iReplaceOnceDeep($needle, $replace, $values)
    {
        return $this->replaceOnceDeep($needle, $replace, $values, true);
    }

    /**
     * String replace ONE time.
     *
     * @since 141111 First documented version.
     *
     * @param string $needle           See {@link str_replace_once_deep()}.
     * @param string $replace          See {@link str_replace_once_deep()}.
     * @param string $string           See {@link str_replace_once_deep()}.
     * @param bool   $caSe_insensitive See {@link str_replace_once_deep()}.
     *
     * @return string See {@link str_replace_once_deep()}.
     */
    public function strReplaceOnce($needle, $replace, $string, $caSe_insensitive = false)
    {
        return $this->replaceOnceDeep($needle, $replace, (string) $string, $caSe_insensitive);
    }

    /**
     * String replace ONE time deeply.
     *
     * @since 141111 First documented version.
     *
     * @param string $needle           A string to search/replace.
     * @param string $replace          What to replace `$needle` with.
     * @param mixed  $values           The haystack(s) to search in.
     * @param bool   $caSe_insensitive Defaults to a `FALSE` value.
     *                                 Pass this as `TRUE` to a caSe-insensitive search/replace.
     *
     * @return string|array|object The `$haystacks`, with `$needle` replaced with `$replace` ONE time only.
     */
    public function replaceOnceDeep($needle, $replace, $values, $caSe_insensitive = false)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->replaceOnceDeep($needle, $replace, $_values, $caSe_insensitive);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        $needle  = (string) $needle;
        $replace = (string) $replace;
        $string  = (string) $values;

        $caSe_strpos = $caSe_insensitive ? 'stripos' : 'strpos';
        if (($needle_strpos = $caSe_strpos($string, $needle)) === false) {
            return $string; // Nothing to replace.
        }
        return (string) substr_replace($string, $replace, $needle_strpos, strlen($needle));
    }

    /**
     * Quote regex meta chars deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed       $values    Input string(s) to mid-clip.
     * @param null|string $delimiter Delimiter to use; if applicable.
     *
     * @return string|array|object Quoted string(s).
     */
    public function pregQuoteDeep($values, $delimiter = null)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->pregQuoteDeep($_values, $delimiter);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        $string = (string) $values;

        return preg_quote($string, $delimiter);
    }

    /**
     * Normalizes end of line chars.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Any input string to normalize.
     *
     * @return string With normalized end of line chars.
     */
    public function nEols($string)
    {
        return $this->nEolsDeep((string) $string);
    }

    /**
     * Normalizes end of line chars deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $values Any value can be converted into a normalized string.
     *                      Actually, objects can't, but this recurses into objects.
     *
     * @return string|array|object With normalized end of line chars deeply.
     */
    public function nEolsDeep($values)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->nEolsDeep($_values);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        $string = (string) $values;

        $string = str_replace(["\r\n", "\r"], "\n", $string);
        $string = preg_replace('/'."\n".'{3,}/', "\n\n", $string);

        return $string; // With normalized line endings.
    }

    /**
     * Normalizes HTML whitespace.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Any input string to normalize.
     *
     * @return string With normalized HTML whitespace.
     */
    public function nHtmlWhitespace($string)
    {
        return $this->nHtmlWhitespaceDeep((string) $string);
    }

    /**
     * Normalizes HTML whitespace deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $values Any value can be converted into a normalized string.
     *                      Actually, objects can't, but this recurses into objects.
     *
     * @return string|array|object With normalized HTML whitespace deeply.
     */
    public function nHtmlWhitespaceDeep($values)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->nHtmlWhitespaceDeep($_values);
            }
            unset($_key, $_values); // Housekeeping.

            return $this->nEolsDeep($values); // All done.
        }
        $string = (string) $values;

        if (is_null($whitespace = &$this->staticKey(__FUNCTION__, 'whitespace'))) {
            $whitespace = implode('|', array_keys($this->html_whitespace));
        }
        $string = preg_replace('/('.$whitespace.')('.$whitespace.')('.$whitespace.')+/i', '${1}${2}', $string);

        return $this->nEols($string); // With normalized HTML whitespace.
    }

    /**
     * Clips a string to X chars.
     *
     * @since 141111 First documented version.
     *
     * @param string $string         See {@link clip_deep()}.
     * @param int    $max_length     See {@link clip_deep()}.
     * @param bool   $force_ellipsis See {@link clip_deep()}.
     *
     * @return string See {@link clip_deep()}.
     */
    public function clip($string, $max_length = 45, $force_ellipsis = false)
    {
        return $this->clipDeep((string) $string, $max_length, $force_ellipsis);
    }

    /**
     * Clips string(s) to X chars deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $values         Input string(s) to clip.
     * @param int   $max_length     Defaults to a value of `45`.
     * @param bool  $force_ellipsis Defaults to a value of `FALSE`.
     *
     * @return string|array|object Clipped string(s).
     */
    public function clipDeep($values, $max_length = 45, $force_ellipsis = false)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->clipDeep($_values, $max_length, $force_ellipsis);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        if (!($string = (string) $values)) {
            return $string; // Empty.
        }
        $max_length = (integer) $max_length;
        $max_length = $max_length < 6 ? 6 : $max_length;

        $string = $this->htmlToText($string, ['br2nl' => false]);
        $string = str_replace('"', "'", $string);

        if (strlen($string) > $max_length) {
            $string = (string) substr($string, 0, $max_length - 5).'[...]';
        } elseif ($force_ellipsis && strlen($string) + 5 > $max_length) {
            $string = (string) substr($string, 0, $max_length - 5).'[...]';
        } else {
            $string .= $force_ellipsis ? '[...]' : '';
        }
        return $string; // Clipped.
    }

    /**
     * Mid-clips a string to X chars.
     *
     * @since 141111 First documented version.
     *
     * @param string $string     See {@link mid_clip_deep()}.
     * @param int    $max_length See {@link mid_clip_deep()}
     *
     * @return string See {@link mid_clip_deep()}
     */
    public function midClip($string, $max_length = 45)
    {
        return $this->midClipDeep((string) $string, $max_length);
    }

    /**
     * Mid-clips string(s) to X chars deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $values     Input string(s) to mid-clip.
     * @param int   $max_length Defaults to a value of `45`.
     *
     * @return string|array|object Mid-clipped string(s).
     */
    public function midClipDeep($values, $max_length = 45)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->midClipDeep($_values, $max_length);
            }
            unset($_key, $_values); // Housekeeping.

            return $values; // All done.
        }
        if (!($string = (string) $values)) {
            return $string; // Empty.
        }
        $max_length = (integer) $max_length;
        $max_length = $max_length < 6 ? 6 : $max_length;

        $string = $this->htmlToText($string, ['br2nl' => false]);
        $string = str_replace('"', "'", $string);

        if (strlen($string) <= $max_length) {
            return $string; // Nothing to do.
        }
        $full_string     = $string;
        $half_max_length = floor($max_length / 2);

        $first_clip = $half_max_length - 5;
        $string     = ($first_clip >= 1) // Something?
            ? substr($full_string, 0, $first_clip).'[...]'
            : '[...]'; // Ellipsis only.

        $second_clip = strlen($full_string) - ($max_length - strlen($string));
        $string .= ($second_clip >= 0 && $second_clip >= $first_clip)
            ? substr($full_string, $second_clip) : ''; // Nothing more.

        return $string; // Mid-clipped.
    }

    /**
     * Is a string in HTML format?
     *
     * @since 141111 First documented version.
     *
     * @param string $string Any input string to test here.
     *
     * @return bool TRUE if string is HTML.
     */
    public function isHtml($string)
    {
        if (!$string || !is_string($string)) {
            return false; // Not possible.
        }
        return strpos($string, '<') !== false && preg_match('/\<[^<>]+\>/', $string);
    }

    /**
     * Encodes all HTML entities.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Any input string to encode.
     * @param bool   $double Double encode existing HTML entities?
     *
     * @return string String w/ HTML entities encoded.
     */
    public function htmlEntitiesEncode($string, $double = false)
    {
        if (!($string = trim((string) $string))) {
            return $string; // Not possible.
        }
        $decode_flags = ENT_QUOTES;

        if (defined('ENT_HTML5')) { // PHP 5.4+ only.
            $decode_flags |= ENT_HTML5;
        }
        $string = wp_check_invalid_utf8($string);

        return htmlentities($string, $decode_flags, 'UTF-8', (boolean) $double);
    }

    /**
     * Decodes all HTML entities.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Any input string to decode.
     *
     * @return string String w/ HTML entities decoded.
     */
    public function htmlEntitiesDecode($string)
    {
        if (!($string = trim((string) $string))) {
            return $string; // Not possible.
        }
        $decode_flags = ENT_QUOTES;

        if (defined('ENT_HTML5')) { // PHP 5.4+ only.
            $decode_flags |= ENT_HTML5;
        }
        $string = wp_check_invalid_utf8($string);

        return html_entity_decode($string, $decode_flags, 'UTF-8');
    }

    /**
     * Convert plain text to HTML markup.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Input string to convert.
     *
     * @return string Plain text converted to HTML markup.
     */
    public function textToHtml($string)
    {
        if (!($string = trim((string) $string))) {
            return $string; // Not possible.
        }
        $string = esc_html($string);
        $string = $this->htmlEntitiesEncode($string);
        $string = nl2br($this->nEols($string));

        $string = make_clickable($string);
        $string = $this->trimHtml($this->nHtmlWhitespace($string));

        return $string; // HTML markup now.
    }

    /**
     * Convert HTML markup converted to plain text.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Input string to convert.
     * @param array  $args   Any additional behavioral args.
     *
     * @return string HTML markup converted to plain text.
     */
    public function htmlToText($string, array $args = [])
    {
        if (!($string = trim((string) $string))) {
            return $string; // Not possible.
        }
        $default_args = [
            'br2nl' => true,

            'strip_content_in_tags' => $this->invisible_tags,
            'inject_eol_after_tags' => $this->block_tags,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $br2nl = (boolean) $args['br2nl']; // Allow line breaks?

        $strip_content_in_tags            = (array) $args['strip_content_in_tags'];
        $strip_content_in_tags_regex_frag = implode('|', $this->pregQuoteDeep($strip_content_in_tags));

        $inject_eol_after_tags            = (array) $args['inject_eol_after_tags'];
        $inject_eol_after_tags_regex_frag = implode('|', $this->pregQuoteDeep($inject_eol_after_tags));

        $string = preg_replace('/\<('.$strip_content_in_tags_regex_frag.')(?:\>|\s[^>]*\>).*?\<\/\\1\>/is', '', $string);
        $string = preg_replace('/\<\/(?:'.$inject_eol_after_tags_regex_frag.')\>/i', '${0}'."\n", $string);
        $string = preg_replace('/\<(?:'.$inject_eol_after_tags_regex_frag.')(?:\/\s*\>|\s[^\/>]*\/\s*\>)/i', '${0}'."\n", $string);

        $string = strip_tags($string, $br2nl ? '<br>' : '');
        $string = $this->htmlEntitiesDecode($string);
        $string = str_replace("\xC2\xA0", ' ', $string);

        if ($br2nl) { // Allow line breaks in this case.
            $string = preg_replace('/\<br(?:\>|\/\s*\>|\s[^\/>]*\/\s*\>)/', "\n", $string);
            $string = $this->nEols($string); // Normalize line breaks.
            $string = preg_replace('/[ '."\t\x0B".']+/', ' ', $string);
        } else {
            $string = preg_replace('/\s+/', ' ', $string); // One line only.
        }
        $string = trim($string); // Trim things up now.

        return $string; // Plain text now.
    }

    /**
     * Convert HTML to rich text; w/ allowed tags only.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Input string to convert.
     * @param array  $args   Any additional behavioral args.
     *
     * @return string HTML to rich text; w/ allowed tags only.
     */
    public function htmlToRichText($string, array $args = [])
    {
        if (!($string = trim((string) $string))) {
            return $string; // Not possible.
        }
        $default_args = [
            'br2nl' => true,

            'allowed_tags' => [
                'a',
                'strong', 'b',
                'i', 'em',
                'ul', 'ol', 'li',
                'code', 'pre',
                'q', 'blockquote',
            ],
            'allowed_attributes' => [
                'href',
            ],

            'strip_content_in_tags' => $this->invisible_tags,
            'inject_eol_after_tags' => $this->block_tags,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $br2nl = (boolean) $args['br2nl']; // Allow line breaks?

        $allowed_tags = (array) $args['allowed_tags'];
        if ($br2nl) {
            $allowed_tags[] = 'br'; // Allow `<br>` in this case.
        }
        $allowed_tags       = array_unique(array_map('strtolower', $allowed_tags));
        $allowed_attributes = (array) $args['allowed_attributes'];

        $strip_content_in_tags            = (array) $args['strip_content_in_tags'];
        $strip_content_in_tags            = array_map('strtolower', $strip_content_in_tags);
        $strip_content_in_tags            = array_diff($strip_content_in_tags, $allowed_tags);
        $strip_content_in_tags_regex_frag = implode('|', $this->pregQuoteDeep($strip_content_in_tags));

        $inject_eol_after_tags            = (array) $args['inject_eol_after_tags'];
        $inject_eol_after_tags            = array_map('strtolower', $inject_eol_after_tags);
        $inject_eol_after_tags            = array_diff($inject_eol_after_tags, $allowed_tags);
        $inject_eol_after_tags_regex_frag = implode('|', $this->pregQuoteDeep($inject_eol_after_tags));

        $string = preg_replace('/\<('.$strip_content_in_tags_regex_frag.')(?:\>|\s[^>]*\>).*?\<\/\\1\>/is', '', $string);
        $string = preg_replace('/\<\/(?:'.$inject_eol_after_tags_regex_frag.')\>/i', '${0}'."\n", $string);
        $string = preg_replace('/\<(?:'.$inject_eol_after_tags_regex_frag.')(?:\/\s*\>|\s[^\/>]*\/\s*\>)/i', '${0}'."\n", $string);

        $string = strip_tags($string, $allowed_tags ? '<'.implode('><', $allowed_tags).'>' : '');
        $string = $this->stripHtmlAttributes($string, compact('allowed_attributes'));
        $string = force_balance_tags($string); // Force balanced HTML tags.

        if ($br2nl) { // Allow line breaks in this case.
            $string = preg_replace('/\<br(?:\>|\/\s*\>|\s[^\/>]*\/\s*\>)/', "\n", $string);
            $string = $this->nEols($string); // Normalize line breaks.
            $string = preg_replace('/[ '."\t\x0B".']+/', ' ', $string);
        } else {
            $string = preg_replace('/\s+/', ' ', $string); // One line only.
        }
        $string = $this->trimHtml($this->nHtmlWhitespace($string));

        return $string; // Rich text markup now.
    }

    /**
     * Strips HTML attributes.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Any input string to strip.
     * @param array  $args   Any additional behavioral args.
     *
     * @return string String w/ HTML attributes stripped.
     */
    public function stripHtmlAttributes($string, array $args = [])
    {
        $default_args = [
            'allowed_attributes' => [],
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $allowed_attributes = // Force lowercase.
            array_map('strtolower', (array) $args['allowed_attributes']);

        $regex_tags  = '/(?P<open>\<[\w\-]+)(?P<attrs>[^>]+)(?P<close>\>)/i';
        $regex_attrs = '/\s+(?P<attr>[\w\-]+)(?:\s*\=\s*(["\']).*?\\2|\s*\=[^\s]*)?/is';

        return preg_replace_callback(
            $regex_tags,
            function ($m) use ($allowed_attributes, $regex_attrs) {
                return $m['open'].preg_replace_callback(
                    $regex_attrs,
                    function ($m) use ($allowed_attributes) {
                        return in_array(strtolower($m['attr']), $allowed_attributes, true) ? $m[0] : '';
                    },
                    $m['attrs']
                ).$m['close']; // With modified attributes.

            },
            $string
        ); // Removes attributes; leaving only those allowed explicitly.
    }

    /**
     * Strips PHP tags.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Input string to strip.
     *
     * @return string String w/ all PHP tags stripped away.
     */
    public function stripPhpTags($string)
    {
        return preg_replace(
            '/'.// Open regex; pattern delimiter.

            '(?:'.// Any of these.

            '\<\?php.*?\?\>'.
            '|'.
            '\<\?\=.*?\?\>'.
            '|'.
            '\<\?.*?\?\>'.
            '|'.
            '\<%.*?%\>'.
            '|'.
            '\<script\s+[^>]*?language\s*\=\s*(["\'])php\\1[^>]*\>.*?\<\s*\/\s*script\s*\>'.
            '|'.
            '\<script\s+[^>]*?language\s*\=\s*php[^>]*\>.*?\<\s*\/\s*script\s*\>'.

            ')'.// Close regex group.

            '/is',
            '',
            (string) $string
        );
    }

    /**
     * A very simple markdown parser.
     *
     * @since 150113 First documented version.
     *
     * @param string $string Input string to convert.
     * @param array  $args   Any additional behavioral args.
     *
     * @return string Markdown converted to HTML markup.
     */
    public function markdown($string, array $args = [])
    {
        if (!($string = trim((string) $string))) {
            return $string; // Not possible.
        }
        $default_args = [
            'oembed' => false,
            'breaks' => true,
            'no_p'   => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $oembed = (boolean) $args['oembed'];
        $breaks = (boolean) $args['breaks'];
        $no_p   = (boolean) $args['no_p'];

        if ($oembed && strpos($string, '://') !== false) {
            $_spcsm           = $this->spcsmTokens($string, [], __FUNCTION__);
            $_oembed_args     = array_merge(wp_embed_defaults(), ['discover' => false]);
            $_spcsm['string'] = preg_replace_callback(
                '/^\s*(https?:\/\/[^\s"]+)\s*$/im',
                function ($m) use ($_oembed_args) {
                    $oembed = wp_oembed_get($m[1], $_oembed_args);
                    return $oembed ? $oembed : $m[0];
                },
                $_spcsm['string']
            );
            $string = $this->spcsmRestore($_spcsm);

            unset($_spcsm, $_oembed_args); // Housekeeping.
        }
        if (is_null($parsedown = &$this->cacheKey(__FUNCTION__, 'parsedown'))) {
            $parsedown = new \ParsedownExtra(); // Singleton.
        }
        $parsedown->setBreaksEnabled($breaks);
        $html = $parsedown->text($string);

        if ($no_p) { // Remove `<p></p>` wrap?
            $html = preg_replace('/^\<p\>/i', '', $html);
            $html = preg_replace('/\<\/p\>$/i', '', $html);
        }
        return $html; // Gotta love Parsedown :-)
    }

    /**
     * A very simple markdown parser.
     *
     * @since 141111 First documented version.
     *
     * @param string $string See {@link markdown()}.
     * @param array  $args   See {@link markdown()}.
     *
     * @return string See {@link markdown()}.
     */
    public function markdownNoP($string, array $args = [])
    {
        return $this->markdown($string, array_merge($args, ['no_p' => true]));
    }

    /**
     * Wraps inline markup (and optional leader) inside `<p></p>` tags.
     *
     * @since 141111 First documented version.
     *
     * @param string $string Input markup to wrap.
     * @param string $leader `<[block]>$leader`.
     *                       If `$string` is NOT already wrapped, this comes after first opening `<p>` tag; the most common occurrence here.
     *                       If `$string` IS already wrapped, this is placed after the first block-level open tag (IF it's an inline container; e.g. `<p>`, `<div>`).
     *
     *    In short, `$leader` goes inside the first block-level open tag, even if that's not a `<p>` tag; so long as it's a block container.
     *       See: {@link $block_container_tags}; e.g. `<p>`, `<div>` are containers; whereas `<ul>` may not contain arbitrary inline tags.
     *       If the first block-level open tag is NOT an inline container; a new `<p></p>` is prepended to hold the leader properly.
     *
     * @return string Inline markup (and optional leader) inside `<p></p>` (or existing block-level) tags.
     *                If markup is already wrapped inside a block-level tag, we simply inject `$leader` and leave everything else as-is.
     *                If markup contains any block-level elements, they'll be moved after `<p></p>` tags to prevent HTML nesting issues.
     *                If markup is empty, this simply returns an empty string; indicating failure.
     */
    public function pWrap($string, $leader = '')
    {
        if (!($string = trim((string) $string))) {
            return ''; // Not possible.
        }
        $leader         = trim((string) $leader);
        $string_is_html = $this->plugin->utils_string->isHtml($string);

        $block_tag_open_regex                   = '/(\<(?:'.implode('|', $this->pregQuoteDeep($this->block_tags)).')(?:\s[^>]*?)?\>)/i';
        $leading_block_tag_open_regex           = '/^'.substr($block_tag_open_regex, 1); // Ditto; same as above, but beginning of the string.
        $leading_block_container_tag_open_regex = '/^(\<(?:'.implode('|', $this->pregQuoteDeep($this->block_container_tags)).')(?:\s[^>]*?)?\>)/i';

        if ($string_is_html) { // Contains HTML markup?
            if (preg_match($leading_block_tag_open_regex, $string)) { // Wrapped already?
                if (preg_match($leading_block_container_tag_open_regex, $string)) {
                    return preg_replace($leading_block_container_tag_open_regex, '${1}'.$leader, $string);
                }
                return '<p>'.$leader.'</p>'.$string; // Best we can do; given the circumstance.
            }
        }
        $inline_markup           = $string; // Initialize.
        $markup_blocks_remaining = ''; // Initialize.

        if ($string_is_html) { // Quick check; contains HTML markup?
            if (($notice_markup_parts = preg_split($block_tag_open_regex, $string, 2, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE))) {
                // We know the first part is NOT a block-level tag since the "leading" check above did not fire.
                $inline_markup           = array_shift($notice_markup_parts); // First part; inline.
                $markup_blocks_remaining = implode('', $notice_markup_parts); // Remaining parts.
            }
        }
        return '<p>'.$leader.$inline_markup.'</p>'.$markup_blocks_remaining;
    }

    /**
     * Shortcode/pre/code/samp/MD tokens.
     *
     * @param string $string        Input string to tokenize.
     * @param array  $tokenize_only Can be used to limit what is tokenized.
     * @param string $marker        Optional marker suffix.
     *
     * @return array Array with: `string`, `tokens`, `marker`.
     */
    public function spcsmTokens($string, array $tokenize_only = [], $marker = '')
    {
        $marker = str_replace('.', '', uniqid('', true)).
                  ($marker ? sha1($marker) : '');

        if (!($string = trim((string) $string))) { // Nothing to tokenize.
            return ['string' => $string, 'tokens' => [], 'marker' => $marker];
        }
        $spcsm = // Convert string to an array w/ token details.
            ['string' => $string, 'tokens' => [], 'marker' => $marker];

        shortcodes: // Target point; `[shortcode][/shortcode]`.

        if ($tokenize_only && !in_array('shortcodes', $tokenize_only, true)) {
            goto pre; // Not tokenizing these.
        }
        if (empty($GLOBALS['shortcode_tags']) || strpos($spcsm['string'], '[') === false) {
            goto pre; // No `[` shortcodes.
        }
        $spcsm['string'] = preg_replace_callback(
            '/'.get_shortcode_regex().'/s',
            function ($m) use (&$spcsm) {
                $spcsm['tokens'][] = $m[0]; // Tokenize.
                return '%#%spcsm-'.$spcsm['marker'].'-'.(count($spcsm['tokens']) - 1).'%#%'; #

            },
            $spcsm['string']
        ); // Shortcodes replaced by tokens.

        pre: // Target point; HTML `<pre>` tags.

        if ($tokenize_only && !in_array('pre', $tokenize_only, true)) {
            goto code; // Not tokenizing these.
        }
        if (stripos($spcsm['string'], '<pre') === false) {
            goto code; // Nothing to tokenize here.
        }
        $pre = // HTML `<pre>` tags.
            '/(?P<tag_open_bracket>\<)'.// Opening `<` bracket.
            '(?P<tag_open_name>pre)'.// Tag name; e.g. a `pre` tag.
            '(?P<tag_open_attrs_bracket>\>|\s+[^>]*\>)'.// Attributes & `>`.
            '(?P<tag_contents>.*?)'.// Tag contents (multiline possible).
            '(?P<tag_close>\<\/\\2\>)/is'; // e.g. closing `</pre>` tag.

        $spcsm['string'] = preg_replace_callback(
            $pre,
            function ($m) use (&$spcsm) {
                $spcsm['tokens'][] = $m[0]; // Tokenize.
                return '%#%spcsm-'.$spcsm['marker'].'-'.(count($spcsm['tokens']) - 1).'%#%'; #

            },
            $spcsm['string']
        ); // Tags replaced by tokens.

        code: // Target point; HTML `<code>` tags.

        if ($tokenize_only && !in_array('code', $tokenize_only, true)) {
            goto samp; // Not tokenizing these.
        }
        if (stripos($spcsm['string'], '<code') === false) {
            goto samp; // Nothing to tokenize here.
        }
        $code = // HTML `<code>` tags.
            '/(?P<tag_open_bracket>\<)'.// Opening `<` bracket.
            '(?P<tag_open_name>code)'.// Tag name; e.g. a `code` tag.
            '(?P<tag_open_attrs_bracket>\>|\s+[^>]*\>)'.// Attributes & `>`.
            '(?P<tag_contents>.*?)'.// Tag contents (multiline possible).
            '(?P<tag_close>\<\/\\2\>)/is'; // e.g. closing `</code>` tag.

        $spcsm['string'] = preg_replace_callback(
            $code,
            function ($m) use (&$spcsm) {
                $spcsm['tokens'][] = $m[0]; // Tokenize.
                return '%#%spcsm-'.$spcsm['marker'].'-'.(count($spcsm['tokens']) - 1).'%#%'; #

            },
            $spcsm['string']
        ); // Tags replaced by tokens.

        samp: // Target point; HTML `<samp>` tags.

        if ($tokenize_only && !in_array('samp', $tokenize_only, true)) {
            goto md_fences; // Not tokenizing these.
        }
        if (stripos($spcsm['string'], '<samp') === false) {
            goto md_fences; // Nothing to tokenize here.
        }
        $samp = // HTML `<samp>` tags.
            '/(?P<tag_open_bracket>\<)'.// Opening `<` bracket.
            '(?P<tag_open_name>samp)'.// Tag name; e.g. a `samp` tag.
            '(?P<tag_open_attrs_bracket>\>|\s+[^>]*\>)'.// Attributes & `>`.
            '(?P<tag_contents>.*?)'.// Tag contents (multiline possible).
            '(?P<tag_close>\<\/\\2\>)/is'; // e.g. closing `</samp>` tag.

        $spcsm['string'] = preg_replace_callback(
            $samp,
            function ($m) use (&$spcsm) {
                $spcsm['tokens'][] = $m[0]; // Tokenize.
                return '%#%spcsm-'.$spcsm['marker'].'-'.(count($spcsm['tokens']) - 1).'%#%'; #

            },
            $spcsm['string']
        ); // Tags replaced by tokens.

        md_fences: // Target point; Markdown pre/code fences.

        if ($tokenize_only && !in_array('md_fences', $tokenize_only, true)) {
            goto md_links; // Not tokenizing these.
        }
        if (strpos($spcsm['string'], '~') === false && strpos($spcsm['string'], '`') === false) {
            goto md_links; // Nothing to tokenize here.
        }
        $md_fences = // Markdown pre/code fences.
            '/(?P<fence_open>~{3,}|`{3,}|`)'.// Opening fence.
            '(?P<fence_contents>.*?)'.// Contents (multiline possible).
            '(?P<fence_close>\\1)/is'; // Closing fence; ~~~, ```, `.

        $spcsm['string'] = preg_replace_callback(
            $md_fences,
            function ($m) use (&$spcsm) {
                $spcsm['tokens'][] = $m[0]; // Tokenize.
                return '%#%spcsm-'.$spcsm['marker'].'-'.(count($spcsm['tokens']) - 1).'%#%'; #

            },
            $spcsm['string']
        ); // Fences replaced by tokens.

        md_links: // Target point; [Markdown](links).
        // This also tokenizes [Markdown]: <link> "definitions".
        // This routine includes considerations for images also.

        // NOTE: The tokenizer does NOT deal with links that reference definitions, as this is not necessary.
        //    So, while we DO tokenize <link> "definitions" themselves, the [actual][references] to
        //    these definitions do not need to be tokenized; i.e. it is not necessary here.

        if ($tokenize_only && !in_array('md_links', $tokenize_only, true)) {
            goto finale; // Not tokenizing these.
        }
        $spcsm['string'] = preg_replace_callback(
            [
                '/^[ ]*(?:\[[^\]]+\])+[ ]*\:[ ]*(?:\<[^>]+\>|\S+)(?:[ ]+.+)?$/m',
                '/\!?\[(?:(?R)|[^\]]*)\]\([^)]+\)(?:\{[^}]*\})?/',
            ],
            function ($m) use (&$spcsm) {
                $spcsm['tokens'][] = $m[0]; // Tokenize.
                return '%#%spcsm-'.$spcsm['marker'].'-'.(count($spcsm['tokens']) - 1).'%#%'; #

            },
            $spcsm['string']
        ); // Shortcodes replaced by tokens.

        finale: // Target point; grand finale (return).

        return $spcsm; // Array w/ string, tokens, and marker.
    }

    /**
     * Shortcode/pre/code/samp/MD restoration.
     *
     * @param array $spcsm `string`, `tokens`, `marker`.
     *
     * @return string The `string` w/ tokens restored now.
     */
    public function spcsmRestore(array $spcsm)
    {
        if (!isset($spcsm['string'])) {
            return ''; // Not possible.
        }
        if (!($string = trim((string) $spcsm['string']))) {
            return $string; // Nothing to restore.
        }
        $tokens = isset($spcsm['tokens']) ? (array) $spcsm['tokens'] : [];
        $marker = isset($spcsm['marker']) ? (string) $spcsm['marker'] : '';

        if (!$tokens || !$marker || strpos($string, '%#%') === false) {
            return $string; // Nothing to restore in this case.
        }
        foreach (array_reverse($tokens, true) as $_token => $_value) {
            // Must go in reverse order so nested tokens unfold properly.
            $string = str_replace('%#%spcsm-'.$marker.'-'.$_token.'%#%', $_value, $string);
        }
        unset($_token, $_value); // Housekeeping.

        return $string; // Restoration complete.
    }

    /**
     * Get first name from a full name, user, or email address.
     *
     * @since 141111 First documented version.
     *
     * @param string                   $name          The full name; or display name.
     * @param \WP_User|int|string|null $user_id_email A WP User object, WP user ID, or email address.
     *                                                If provided, we make every attempt to pull a name from this source.
     * @param int                      $max_length    The maximum length of the name.
     *
     * @return string First name, else full name; else whatever we can get from `$user_id_email`.
     */
    public function firstName($name = '', $user_id_email = null, $max_length = 50)
    {
        $name       = $this->cleanName($name);
        $max_length = abs((integer) $max_length);

        if ($name && strpos($name, ' ', 1) !== false) {
            list($fname) = explode(' ', $name, 2);
        } else {
            $fname = $name; // One part in this case.
        }
        if ($fname && ($fname = (string) substr(trim($fname), 0, $max_length))) {
            return $fname; // All set; nothing more to do here.
        }
        if (($user = $user_id_email) instanceof \WP_User
            || (is_integer($user_id_email) && ($user = new \WP_User($user_id_email)))
        ) { // Find first non-empty data values (in order of precedence).
            $name  = $this->coalesce($user->first_name, $user->display_name, $user->user_login);
            $email = $this->coalesce($user->user_email);

            if ($name || $email) { // Only if we got something.
                return $this->firstName($name, $email, $max_length);
            }
        } elseif (is_string($user_id_email) && ($email = $user_id_email)) {
            return $this->emailName($email, $max_length);
        }
        return ''; // Default value; i.e. failure.
    }

    /**
     * Name from email address.
     *
     * @since 141111 First documented version.
     *
     * @param string $string     Input email address.
     * @param int    $max_length The maximum length of the name.
     *
     * @return string Name from email address; else an empty string.
     */
    public function emailName($string, $max_length = 50)
    {
        if (!($string = trim((string) $string))) {
            return ''; // Not possible.
        }
        $max_length = abs((integer) $max_length);

        return (string) ucfirst(substr(strstr($string, '@', true), 0, $max_length));
    }

    /**
     * Get last name from a full name or user.
     *
     * @since 141111 First documented version.
     *
     * @param string            $name       The full name; or display name.
     * @param \WP_User|int|null $user_id    A WP User object, or WP user ID.
     *                                      If provided, we make every attempt to pull a name from this source.
     * @param int               $max_length The maximum length of the name.
     *
     * @return string First name, else full name; else whatever we can get from `$user_id_email`.
     */
    public function lastName($name = '', $user_id = null, $max_length = 100)
    {
        $name       = $this->cleanName($name);
        $max_length = abs((integer) $max_length);

        if ($name && strpos($name, ' ', 1) !== false) {
            list(, $lname) = explode(' ', $name, 2);
        } else {
            $lname = ''; // One part in this case.
        }
        if ($lname && ($lname = (string) substr(trim($lname), 0, $max_length))) {
            return $lname; // All set; nothing more to do here.
        }
        if (($user = $user_id) instanceof \WP_User
            || (is_integer($user_id) && ($user = new \WP_User($user_id)))
        ) { // Find first non-empty data values (in order of precedence).
            if (($lname = $user->last_name)) {
                return $lname = (string) substr(trim($lname), 0, $max_length);
            }
            if (($name = $this->coalesce($user->display_name))) {
                return $this->lastName($name, null, $max_length);
            }
        }
        return ''; // Default value; i.e. failure.
    }

    /**
     * Cleans a full name.
     *
     * @since 141111 First documented version.
     *
     * @param string $string See {@link clean_names_deep()}.
     *
     * @return string See {@link clean_names_deep()}.
     */
    public function cleanName($string)
    {
        return $this->cleanNamesDeep((string) $string);
    }

    /**
     * Cleans full name(s) deeply.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $values Input name(s) to clean.
     *
     * @return string|array|object Having cleaned name(s) deeply.
     */
    public function cleanNamesDeep($values)
    {
        if (is_array($values) || is_object($values)) {
            foreach ($values as $_key => &$_values) {
                $_values = $this->cleanNamesDeep($_values);
            }
            unset($_values); // Housekeeping.

            return $values; // All done.
        }
        $string = trim((string) $values); // Trim string.
        $string = $string ? str_replace('"', '', $string) : '';
        $string = $string ? preg_replace('/^(?:Mr\.?|Mrs\.?|Ms\.?|Dr\.?)\s+/i', '', $string) : '';
        $string = $string ? preg_replace('/\s+(?:Sr\.?|Jr\.?|IV|I+)$/i', '', $string) : '';
        $string = $string ? preg_replace('/\s+/', ' ', $string) : '';
        $string = $string ? trim($string) : ''; // Trim again.

        return $string; // Cleaned up now.
    }

    /**
     * HTML whitespace. Keys are actually regex patterns here.
     *
     * @type array HTML whitespace. Keys are actually regex patterns here.
     */
    public $html_whitespace = [
        '\0'                      => "\0",
        '\x0B'                    => "\x0B",
        '\s'                      => "\r\n\t ",
        '\xC2\xA0'                => "\xC2\xA0",
        '&nbsp;'                  => '&nbsp;',
        '\<br\>'                  => '<br>',
        '\<br\s*\/\>'             => '<br/>',
        '\<p\>(?:&nbsp;)*\<\/p\>' => '<p></p>',
    ];

    /**
     * HTML5 invisible tags.
     *
     * @type array HTML5 invisible tags.
     */
    public $invisible_tags = [
        'head',
        'title',
        'style',
        'script',
    ];

    /**
     * HTML5 block-level tags.
     *
     * @type array HTML5 block-level tags.
     */
    public $block_tags = [
        'address',
        'article',
        'aside',
        'audio',
        'blockquote',
        'canvas',
        'dd',
        'div',
        'dl',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'form',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'header',
        'hgroup',
        'hr',
        'noscript',
        'ol',
        'output',
        'p',
        'pre',
        'section',
        'table',
        'tfoot',
        'ul',
        'video',
    ];

    /**
     * @type array Block container tags.
     *            i.e. block tags that serve as inline containers.
     *
     * @since 141111 First documented version.
     */
    public $block_container_tags = [
        'p',
        'div',
    ];
}
