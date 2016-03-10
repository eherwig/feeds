CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%yfeed_stream` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `namespace` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `type_params` text NULL,
    `title` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `etag` varchar(255) NOT NULL,
    `last_modified` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%yfeed_response` (
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
    PRIMARY KEY (`id`),
    KEY `stream_id` (`stream_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
