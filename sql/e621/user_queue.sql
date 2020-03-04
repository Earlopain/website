CREATE TABLE `user_queue` (
  `counter` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`counter`),
  KEY `user_queue_FK` (`user_id`),
  CONSTRAINT `user_queue_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
