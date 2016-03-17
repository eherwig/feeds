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
$sql = rex_sql::factory();

$sql->setQuery('
    CREATE TABLE IF NOT EXISTS `' . rex::getTable('yfeed_stream') . '` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `namespace` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `type_params` text NULL,
        `title` varchar(255) NOT NULL,
        `image` varchar(255) NOT NULL,
        `etag` varchar(255) NOT NULL,
        `last_modified` varchar(255) NOT NULL,
        `createuser` varchar(255) NOT NULL,
        `updateuser` varchar(255) NOT NULL,
        `createdate` datetime NOT NULL,
        `updatedate` datetime NOT NULL,
        `revision` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
');

$sql->setQuery('
    CREATE TABLE IF NOT EXISTS `' . rex::getTable('yfeed_response') . '` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `stream_id` int(10) unsigned NOT NULL,
        `uid` varchar(255) NOT NULL,
        `title` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `content_raw` text NOT NULL,
        `url` varchar(255) NOT NULL,
        `date` varchar(255) NOT NULL,
        `author` varchar(255) NOT NULL,
        `language` varchar(255) NOT NULL,
        `media` varchar(255) NOT NULL,
        `raw` text NOT NULL,
        `changed_by_user` tinyint(1) NOT NULL,
        `createuser` varchar(255) NOT NULL,
        `updateuser` varchar(255) NOT NULL,
        `createdate` datetime NOT NULL,
        `updatedate` datetime NOT NULL,
        `revision` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`),
        KEY `stream_id` (`stream_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
');
