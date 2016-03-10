<?php

/**
 * This file is part of the YFeed package.
 *
 * @author (c) Yakamara Media GmbH & Co. KG
 * @author thomas.blum@redaxo.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class rex_yfeed_helper
{
    /**
     * @param  int $timestamp
     * @return bool
     */
    public static function isValidTimeStamp($timestamp)
    {
        // if the code is run on a 32bit system, loosely check if the timestamp is a valid numeric
        if (PHP_INT_SIZE == 4 && is_numeric($timestamp)) {
            return true;
        }
        if (!is_numeric($timestamp)) {
            return false;
        }
        if (intval($timestamp) != $timestamp) {
            return false;
        }
        if (!($timestamp <= PHP_INT_MAX && $timestamp >= ~PHP_INT_MAX)) {
            return false;
        }
        return true;
    }
}
