CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%yfeed` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `params` text NULL,
    `url` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `table` varchar(255) NOT NULL,
    `field_feed_id` varchar(255) NOT NULL,
    `field_uid` varchar(255) NOT NULL,
    `field_title` varchar(255) NOT NULL,
    `field_content` text NOT NULL,
    `field_content_raw` text NOT NULL,
    `field_url` varchar(255) NOT NULL,
    `field_date` varchar(255) NOT NULL,
    `field_author` varchar(255) NOT NULL,
    `field_language` varchar(255) NOT NULL,
    `field_enclosure_url` varchar(255) NOT NULL,
    `field_enclosure_type` varchar(255) NOT NULL,
    `etag` varchar(255) NOT NULL,
    `last_modified` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

