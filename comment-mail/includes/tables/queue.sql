CREATE TABLE IF NOT EXISTS `%%prefix%%queue` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key.',
  `sub_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Subscriber ID from the `subs` table.',
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment ID from the `wp_comments` table.',
  `insertion_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Insertion time. UNIX timestamp.',
  PRIMARY KEY (`ID`) COMMENT 'Primary key.',
  UNIQUE KEY `unique_entry` (`sub_id`,`comment_id`) COMMENT 'Unique queue entry.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;