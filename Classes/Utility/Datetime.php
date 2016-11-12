<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************/

/**
 * Utilities to manage dates and time
 */
class Tx_TerFe2_Utility_Datetime
{

    /**
     * @var string
     */
    protected static $dateTimeFormat = 'H:i d-m-Y';


    /**
     * Set date / time format
     *
     * @param string $format Format of date / time
     * @return void
     */
    public static function setDateTimeFormat($format)
    {
        self::$dateTimeFormat = $format;
    }


    /**
     * Convert date / time string to timestamp
     *
     * @param string $string Date / time
     * @return integer Unix timestamp
     */
    public static function getTimestampFromDate($string)
    {
        // Try with strtotime
        $timestamp = strtotime($string);

        // Try TYPO3 standard date / time input format
        if ($timestamp === FALSE) {
            $timestamp = DateTime::createFromFormat(self::$dateTimeFormat, $string);
        }

        // Try configured date / time input format
        if ($timestamp === FALSE) {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
            $timestamp = DateTime::createFromFormat($format, $string);
        }

        // Try US date / time input format
        if ($timestamp === FALSE || !empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'])) {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'] . ' m-d-Y';
            $timestamp = DateTime::createFromFormat($format, $string);
        }

        return (int)$timestamp;
    }


    /**
     * Convert a timestamp to date string
     *
     * @param intger $timestamp The timstamp to format
     * @return string The date
     */
    public static function getDateFromTimestamp($timestamp)
    {
        return date(self::$dateTimeFormat, (int)$timestamp);
    }

}

?>