<?php
/**
 * Date Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Date Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsDate extends AbsBase
{
    /**
     * Date translations (in local time, as configured by WordPress®).
     *
     * @param string $format Date format. Same formats allowed by PHP's `date()` function.
     *                       This is optional. Defaults to: `get_option('date_format').' '.get_option('time_format')`.
     *
     * @note       There are several PHP constants that can be used here, making `$format` easier to deal with.
     *    Please see: {@link http://www.php.net/manual/en/class.datetime.php#datetime.constants.types}.
     *
     * @param int  $time Optional timestamp. A UNIX timestamp, based on UTC time.
     *                   This defaults to the current time if NOT passed in explicitly.
     *                   WARNING: Please make sure `$time` is based on UTC.
     * @param bool $utc  Defaults to FALSE (recommended).
     *                   If this is TRUE the time will be returned in UTC time instead of local time.
     *
     * @return string Date translation (in local time, as configured by WordPress®).
     *                Or, if `$utc` is TRUE the time will be returned in UTC time, instead of local time.
     *
     * @note       This uses `date_i18n()` from WordPress®. We supplement this built-in WordPress® function,
     *    by forcing the `$time` argument value at all times. WordPress® has some issues with its default for `$time`,
     *    but we can force `$time` to resolve those problems completely here.
     *
     * @difference Unless `$utc` is TRUE, this method always adds the UTC offset to `$time`,
     *    which is something that WordPress® DOES NOT DO by default. Making this a better alternative.
     *    Passing `$utc` as TRUE here, means that we are passing a UTC time in; and we expect a UTC time out.
     *    NOTE: the `$time` parameter should ALWAYS be passed in UTC, no matter what this is set to.
     *
     * @WARNING    WordPress® has a broken implementation of `$utc`. Dates with a `$format` that include a timezone char,
     *    like `O` or `T` are always represented with the blog's timezone (even when `$utc` is TRUE).
     *    This is wrong, because `$utc` being TRUE should be indicated with a UTC timezone.
     *
     *    We fix this issue here by removing timezone chars from `$format` when `$utc` is TRUE.
     *    Once the translation is completed, we add ` UTC` onto the end as a quick fix.
     *
     *    UPDATE: upon further review, this is actually NOT a WordPress bug (not really).
     *    The WordPress `date_i18n()` function will always return what it thinks is a localized time.
     *    It doesn't actually do any time conversion on it's own. Therefore, this fix we apply is actually
     *    to make the `$utc` parameter that we use, behave as we expect it to.
     *
     *    In summary, set `$utc` to TRUE if you want a UTC time from this method; or FALSE (default) if you want
     *    a localized date/time returned by this function. Remember that your input `$time` parameter should always be in UTC time.
     *    This is the case anyway, since `date_default_timezone_set()` is forced to UTC by WordPress already.
     */
    public function i18n($format = '', $time = 0, $utc = false)
    {
        $format = (string) $format; // Force string.
        $time   = (integer) $time; // Force integer.

        $format = $format ? $format // A specific format?
            : get_option('date_format').' '.get_option('time_format');

        $time = $time ? abs($time) : time(); // Default time.
        $time = $utc ? $time : $time + (get_option('gmt_offset') * 3600);

        if ($utc && preg_match('/(?<!\\\\)[PIOTZe]/', $format)) {
            $format = preg_replace('/(?<!\\\\)[PIOTZe]/', '', $format);
            $format = trim(preg_replace('/\s+/', ' ', $format));
            return date_i18n($format, $time, $utc).' UTC';
        }
        return date_i18n($format, $time, $utc);
    }

    /**
     * Date translations (in UTC time). See: {@link i18n()}.
     *
     * @param string $format Date format. Same formats allowed by PHP's `date()` function.
     *                       This is optional. Defaults to: `get_option('date_format').' '.get_option('time_format')`.
     *
     * @note There are several PHP constants that can be used here, making `$format` easier to deal with.
     *    Please see: {@link http://www.php.net/manual/en/class.datetime.php#datetime.constants.types}.
     *
     * @param int $time Optional timestamp. A UNIX timestamp, based on UTC time.
     *                  This defaults to the current time if NOT passed in explicitly.
     *                  WARNING: Please make sure `$time` is based on UTC.
     *
     * @return string See: {@link i18n()}.
     */
    public function i18nUtc($format = '', $time = 0)
    {
        return $this->i18n($format, $time, true);
    }

    /**
     * Is a `strtotime()` input relative?
     *
     * @param string $string string to test here.
     *
     * @return bool `TRUE` if input string is a relative date.
     */
    public function isRelative($string)
    {
        if (!($string = trim((string) $string))) {
            return false; // Nope.
        }
        $non_relative = strtotime($string);
        $relative     = strtotime($string, time() - 100);

        return $relative !== $non_relative;
    }

    /**
     * Calculates approx time different (in human readable format).
     *
     * @param int    $from   A UTC timestamp to calculate from (i.e. start time).
     * @param int    $to     A UTC timestamp to calculate to (i.e. the end time).
     *                       If `NULL` (default value) this will default to the current time.
     * @param string $suffix Optional suffix chars.
     *                       If `NULL` (default value) this will default to ` ago`.
     *
     * @return string Approx. time different (in human readable format).
     */
    public function approxTimeDifference($from, $to = null, $suffix = null)
    {
        if (!isset($to)) { // To now?
            $to = time(); // Current time.
        }
        if (!isset($suffix)) { // Use default value?
            $suffix = ' '.__('ago', 'comment-mail');
        }
        $from   = (integer) $from; // Force integer.
        $to     = (integer) $to; // Force integer.
        $suffix = (string) $suffix; // Force string.

        $difference = $to - $from >= 0 ? $to - $from : 0;

        if ($difference < 3600) { // Less than a minute?
            $minutes = (integer) round($difference / 60);

            $since = sprintf(_n('%1$s minute', '%1$s minutes', $minutes, 'comment-mail'), $minutes);
            $since = ($minutes < 1) ? __('less than a minute', 'comment-mail') : $since;
            $since = ($minutes >= 60) ? __('about 1 hour', 'comment-mail') : $since;
        } elseif ($difference >= 3600 && $difference < 86400) {
            $hours = (integer) round($difference / 3600);

            $since = sprintf(_n('%1$s hour', '%1$s hours', $hours, 'comment-mail'), $hours);
            $since = ($hours >= 24) ? __('about 1 day', 'comment-mail') : $since;
        } elseif ($difference >= 86400 && $difference < 604800) {
            $days = (integer) round($difference / 86400);

            $since = sprintf(_n('%1$s day', '%1$s days', $days, 'comment-mail'), $days);
            $since = ($days >= 7) ? __('about 1 week', 'comment-mail') : $since;
        } elseif ($difference >= 604800 && $difference < 2592000) {
            $weeks = (integer) round($difference / 604800);

            $since = sprintf(_n('%1$s week', '%1$s weeks', $weeks, 'comment-mail'), $weeks);
            $since = ($weeks >= 4) ? __('about 1 month', 'comment-mail') : $since;
        } elseif ($difference >= 2592000 && $difference < 31556926) {
            $months = (integer) round($difference / 2592000);

            $since = sprintf(_n('%1$s month', '%1$s months', $months, 'comment-mail'), $months);
            $since = ($months >= 12) ? __('about 1 year', 'comment-mail') : $since;
        } else {
            // We use years here (default case handler).

            $years = (integer) round($difference / 31556926);
            $since = sprintf(_n('%1$s year', '%1$s years', $years, 'comment-mail'), $years);
        }
        return $since.$suffix; // Human readable time difference calculation.
    }
}
