CREATE TABLE `user_favs` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `post_id` mediumint(8) unsigned NOT NULL,
  `position` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`post_id`),
  KEY `user_favs_FK_1` (`post_id`),
  CONSTRAINT `user_favs_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_favs_FK_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
