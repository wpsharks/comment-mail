<?php
/**
 * Array Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Array Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsArray extends AbsBase
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
     * Unique values deeply (preserving keys).
     *
     * @since 141111 First documented version.
     *
     * @param array $array An input array.
     *
     * @return array The output array, containing only unique array values deeply.
     *
     * @note  Resource pointers CANNOT be serialized, and will therefore be lost (i.e. corrupted)
     *    when/if they're nested deeply inside the input array. Resources NOT nested deeply, DO remain intact (this is fine).
     *    Only resource pointers nested deeply are lost via `serialize()`.
     *
     * @see   \array_unique()
     */
    public function uniqueDeep(array $array)
    {
        if (!$array) { // Nothing to do.
            return $array;
        }
        foreach ($array as $_key => &$_value) {
            if (!is_resource($_value)) {
                $_value = serialize($_value);
            }
        }
        unset($_key, $_value); // Housekeeping.

        $array = array_unique($array);

        foreach ($array as $_key => &$_value) {
            if (!is_resource($_value)) {
                $_value = unserialize($_value);
            }
        }
        unset($_key, $_value); // Housekeeping.

        return $array; // Unique deep.
    }

    /**
     * Prepend a key/value pair onto an array.
     *
     * @since 141111 First documented version.
     *
     * @param array $array An input array; by reference.
     * @param string|int New    array key; string or integer.
     * @param mixed $value New array value.
     *
     * @throws \exception If the input `$key` is not an integer|string.
     * @return int Like {@link \array_unshift()}, returns the new number of elements.
     *
     *
     * @see   \array_unshift()
     */
    public function unshiftAssoc(array &$array, $key, $value)
    {
        if (!is_integer($key) && !is_string($key)) {
            throw new \exception(__('Invalid `$key` arg.', 'comment-mail'));
        }
        unset($array[$key]); // Unset first.

        $array       = array_reverse($array, true);
        $array[$key] = $value; // Add to the end here.
        $array       = array_reverse($array, true);

        return count($array); // New number of elements.
    }

    /**
     * Shuffles an array (preserving keys).
     *
     * @since 141111 First documented version.
     *
     * @param array $array An input array; by reference.
     *
     * @return bool Like {@link \shuffle()}, this returns `TRUE`.
     *
     * @see   \shuffle()
     */
    public function shuffleAssoc(array &$array)
    {
        if (!$array) { // Nothing to do.
            return true;
        }
        $_shuffled = [];
        $_keys     = array_keys($array);
        shuffle($_keys); // Keys only.

        foreach ($_keys as $_key) {
            $_shuffled[$_key] = $array[$_key];
        }
        $array = $_shuffled; // Overwrite existing.
        unset($_shuffled, $_keys, $_key); // Housekeeping.

        return true; // Always returns `TRUE`.
    }

    /**
     * Shuffles an array deeply (preserving keys).
     *
     * @since 141111 First documented version.
     *
     * @param array $array An input array; by reference.
     *
     * @return bool Like {@link \shuffle()}, this returns `TRUE`.
     *
     * @see   \shuffle()
     * @see   shuffleAssoc()
     */
    public function shuffleAssocDeep(array &$array)
    {
        if (!$array) { // Nothing to do.
            return true;
        }
        $this->shuffleAssoc($array);

        foreach ($array as $_key => &$_value) {
            if (is_array($_value)) {
                $this->shuffleAssocDeep($_value);
            }
        }
        unset($_key, $_value); // Housekeeping.

        return true; // Always returns `TRUE`.
    }

    /**
     * Removes `NULL` key/values.
     *
     * @since 141111 First documented version.
     *
     * @param array $array An input array to work from.
     *
     * @return array Keys preserved; `NULL` key/values removed though.
     */
    public function removeNulls(array $array)
    {
        if (!$array) { // Nothing to do.
            return $array;
        }
        foreach ($array as $_key => &$_value) {
            if (is_null($_value)) {
                unset($array[$_key]);
            }
        }
        unset($_key, $_value); // Housekeeping.

        return $array; // No `NULL` values.
    }

    /**
     * Removes `NULL` key/values deeply.
     *
     * @since 141111 First documented version.
     *
     * @param array $array An input array to work from.
     *
     * @return array Keys preserved; `NULL` key/values removed though.
     */
    public function removeNullsDeep(array $array)
    {
        if (!$array) { // Nothing to do.
            return $array;
        }
        $array = $this->removeNulls($array);

        foreach ($array as $_key => &$_value) {
            if (is_array($_value)) {
                $_value = $this->removeNullsDeep($_value);
            }
        }
        unset($_key, $_value); // Housekeeping.

        return $array; // `NULL` values removed deeply.
    }
}
