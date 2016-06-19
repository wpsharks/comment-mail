CREATE TABLE IF NOT EXISTS `%%prefix%%queue` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key.',
  `sub_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Subscription ID from the subs table.',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID from the wp_users table',
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Post ID from the wp_posts table.',
  `comment_parent_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment parent ID from the wp_comments table.',
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment ID from the wp_comments table.',
  `insertion_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Insertion time. UNIX timestamp.',
  `last_update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last update time. UNIX timestamp.',
  `hold_until_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Hold until time. UNIX timestamp.',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_entry` (`sub_id`,`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;