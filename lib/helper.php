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
     * @param int $timestamp
     *
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

    /**
     * Generates the data uri for a remote resource
     *
     * @param string $url
     *
     * @return string
     */
    public static function getDataUri($url)
    {
        $response = rex_socket::factoryUrl($url)->doGet();
        $mimeType = $response->getHeader('content-type');
        $uri = 'data:'.$mimeType;

        if (0 === strpos($mimeType, 'text/')) {
            $uri .= ','.rawurlencode($response->getBody());
        } else {
            $uri .= ';base64,'.base64_encode($response->getBody());
        }

        return $uri;
    }
}
