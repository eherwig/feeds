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
     * @return rex_yfeed_stream_abstract
     */
    public static function get($id)
    {
        $id = (int)$id;
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::table() . ' WHERE `id` = :id LIMIT 1', ['id' => $id]);

        if (!$sql->getRows()) {
            return null;
        }

        $data = $sql->getArray()[0];
        if (empty($data['type'])) {
            throw new rex_exception('Unexpected yfeed stream type');
        }

        $type = $data['type'];
        $streams = self::getSupportedStreams();
        if (!isset($streams[$type])) {
            throw new rex_exception('The yfeed stream type is not supported');
        }

        /** @var rex_yfeed_stream_abstract $stream */
        $stream = new $streams[$type]();
        if (isset($data['type_params'])) {
            $stream->setTypeParams(json_decode($data['type_params'], true));
        }
        $stream->setStreamId($data['id']);
        $stream->setEtag($data['etag']);
        $stream->setLastModified($data['last_modified']);

        return $stream;
    }


    public static function table()
    {
        return rex::getTable('yfeed_stream');
    }

    public static function getSupportedStreams()
    {
        static $loaded = false;

        if ($loaded) {
            return self::$streams;
        }

        $files = glob(__DIR__ . '/stream/' . '*.php');
        if ($files) {
            foreach ($files as $file) {
                $type = substr(basename($file), 0, -4);
                self::$streams[$type] = 'rex_yfeed_stream_'.$type;
            }
        }
        $loaded = true;

        return self::$streams;
    }

    public static function addStream($class, $type = null)
    {
        $type = $type ?: str_replace(['rex_', 'yfeed_', 'stream_'], '', $class);
        self::$streams[$type] = $class;
    }
}
