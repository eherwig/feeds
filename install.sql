CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%yfeed_stream` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `namespace` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `type_params` text NULL,
    `title` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `etag` varchar(255) NOT NULL,
    `last_modified` varchar(255) NOT NULL,
    `status` tinyint(1) NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%yfeed_item` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `stream_id` int(10) unsigned NOT NULL,
    `uid` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `content_raw` text NOT NULL,
    `url` varchar(255) NOT NULL,
    `date` datetime NOT NULL,
    `author` varchar(255) NOT NULL,
    `language` varchar(255) NOT NULL,
    `media` longtext NOT NULL,
    `raw` text NOT NULL,
    `status` tinyint(1) NOT NULL DEFAULT 1,
    `changed_by_user` tinyint(1) NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY `stream_id` (`stream_id`)
        REFERENCES `%TABLE_PREFIX%yfeed_stream`(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
