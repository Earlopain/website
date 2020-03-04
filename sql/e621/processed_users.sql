CREATE TABLE `processed_users` (
  `user_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `processed_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
