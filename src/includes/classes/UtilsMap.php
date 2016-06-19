<?php
/**
 * Map Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Map Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsMap extends AbsBase
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
     * ISO-3166-1 country code to full name.
     *
     * @since 141111 First documented version.
     *
     * @param string $code Country code.
     *
     * @return string Full name; else original code.
     */
    public function countryName($code)
    {
        $code = trim(strtoupper((string) $code));
        $iso  = $this->iso31661();

        return $code && !empty($iso[$code]) ? $iso[$code] : $code;
    }

    /**
     * ISO-3166-2 region code in the US to full name.
     *
     * @since 141111 First documented version.
     *
     * @param string $code Region code.
     *
     * @return string Full name; else original code.
     */
    public function usRegionName($code)
    {
        $code = trim(strtoupper((string) $code));
        $iso  = $this->iso31662Us();

        return $code && !empty($iso[$code]) ? $iso[$code] : $code;
    }

    /**
     * ISO-3166-2 region code in CA to full name.
     *
     * @since 141111 First documented version.
     *
     * @param string $code Region code.
     *
     * @return string Full name; else original code.
     */
    public function caRegionName($code)
    {
        $code = trim(strtoupper((string) $code));
        $iso  = $this->iso31662Ca();

        return $code && !empty($iso[$code]) ? $iso[$code] : $code;
    }

    /**
     * ISO-3166-1 country codes/names.
     *
     * @since 141111 First documented version.
     *
     * @return string ISO-3166-1 country codes/names.
     */
    public function iso31661()
    {
        if (!is_null($iso = &$this->staticKey(__FUNCTION__))) {
            return $iso; // Already cached this.
        }
        $iso          = []; // Initialize.
        $iso_db       = file_get_contents(dirname(__DIR__).'/databases/iso-3166-1.txt');
        $iso_db_lines = preg_split('/['."\r\n\t".']+/', $iso_db, null, PREG_SPLIT_NO_EMPTY);

        foreach ($iso_db_lines as $_iso_db_line) {
            list($_name, $_code)     = explode(';', $_iso_db_line, 2);
            $iso[strtoupper($_code)] = ucwords(strtolower($_name));
        }
        unset($_iso_db_line, $_name, $_code); // Housekeeping.

        return $iso; // Cached this now.
    }

    /**
     * ISO-3166-2 region codes/names in the US.
     *
     * @since 141111 First documented version.
     *
     * @return string ISO-3166-2 region codes/names in the US.
     */
    public function iso31662Us()
    {
        if (!is_null($iso = &$this->staticKey(__FUNCTION__))) {
            return $iso; // Already cached this.
        }
        $iso          = []; // Initialize.
        $iso_db       = file_get_contents(dirname(__DIR__).'/databases/iso-3166-2-us.txt');
        $iso_db_lines = preg_split('/['."\r\n\t".']+/', $iso_db, null, PREG_SPLIT_NO_EMPTY);

        foreach ($iso_db_lines as $_iso_db_line) {
            list($_name, $_code)     = explode(';', $_iso_db_line, 2);
            $iso[strtoupper($_code)] = ucwords(strtolower($_name));
        }
        unset($_iso_db_line, $_name, $_code); // Housekeeping.

        return $iso; // Cached this now.
    }

    /**
     * ISO-3166-2 region codes/names in CA.
     *
     * @since 141111 First documented version.
     *
     * @return string ISO-3166-2 region codes/names in CA.
     */
    public function iso31662Ca()
    {
        if (!is_null($iso = &$this->staticKey(__FUNCTION__))) {
            return $iso; // Already cached this.
        }
        $iso          = []; // Initialize.
        $iso_db       = file_get_contents(dirname(__DIR__).'/databases/iso-3166-2-ca.txt');
        $iso_db_lines = preg_split('/['."\r\n\t".']+/', $iso_db, null, PREG_SPLIT_NO_EMPTY);

        foreach ($iso_db_lines as $_iso_db_line) {
            list($_name, $_code)     = explode(';', $_iso_db_line, 2);
            $iso[strtoupper($_code)] = ucwords(strtolower($_name));
        }
        unset($_iso_db_line, $_name, $_code); // Housekeeping.

        return $iso; // Cached this now.
    }
}
