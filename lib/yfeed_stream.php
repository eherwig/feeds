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

class rex_yfeed_stream
{
    private static $streams = [];


    /**
     * @param int $id
     * @return self
     */
    public static function get($id)
    {
        $id = (int)$id;
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::table() . ' WHERE `id` = :id LIMIT 1', ['id' => $id]);

        if (!$sql->getRows()) {
            return null;
        }

        $stream = $sql->getArray()[0];
        if (!isset($stream['type'])) {
            throw new rex_exception('Unexpected yfeed stream type');
        }

        if ($stream['type'] != '') {
            $streamClass = 'rex_yfeed_stream_' . $stream['type'];
            $streams = self::getSupportedStreams();
            if (!isset($streams[$streamClass])) {
                throw new rex_exception('The yfeed stream type is not supported');
            }

            $streamInstance = new $streamClass();
            if (isset($stream['type_params'])) {
                $streamParams = [];
                $params = json_decode($stream['type_params'], true);
                if (isset($params[$streamClass])) {
                    foreach ($params[$streamClass] as $key => $value) {
                        $streamParams[str_replace($streamClass . '_', '', $key)] = $value;
                        unset($streamParams[$key]);
                    }
                }
                $streamInstance->setTypeParams($streamParams);
            }
            $streamInstance->setStreamId($stream['id']);
            $streamInstance->setEtag($stream['etag']);
            $streamInstance->setLastModified($stream['last_modified']);
        }

        return $streamInstance;
    }


    public static function table()
    {
        return rex::getTable('yfeed_stream');
    }

    public static function getSupportedStreams()
    {
        $dirs = [
            __DIR__ . '/streams/',
        ];

        $streams = [];
        foreach ($dirs as $dir) {
            $files = glob($dir . 'yfeed_stream_*.php');
            if ($files) {
                foreach ($files as $file) {
                    $streams[self::getStreamClass($file)] = self::getStreamName($file);
                }
            }
        }

        foreach (self::$streams as $class) {
            $streams[$class] = str_replace(['rex_', 'yfeed_', 'stream_'], '', $class);
        }

        return $streams;
    }

    public static function addStream($class)
    {
        self::$streams[] = $class;
    }

    private static function getStreamName($streamFile)
    {
        return str_replace(
            ['yfeed_', 'stream_', '.php'],
            '',
            basename($streamFile)
        );
    }

    private static function getStreamClass($streamFile)
    {
        return 'rex_' . str_replace(
            '.php',
            '',
            basename($streamFile)
        );
    }

}
