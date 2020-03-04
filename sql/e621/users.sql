CREATE TABLE `users` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `user_name` varchar(18) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_UN` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
