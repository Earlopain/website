CREATE TABLE `posts` (
  `id` mediumint(8) unsigned NOT NULL,
  `md5` char(32) DEFAULT NULL,
  `json` json DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `file` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_UN` (`md5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
