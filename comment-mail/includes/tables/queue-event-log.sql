CREATE TABLE IF NOT EXISTS `%%prefix%%queue_event_log` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key.',
  `queue_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Queue entry ID from the `queue` table. The entry may or may not still exist.',
  `sub_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Subscriber ID from the `subs` table. The subscriber may or may not still exist.',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID from the `wp_users` table, if applicable. The user may or may not still exist.',
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Post ID from the `wp_posts` table. The post may or may not still exist.',
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment ID from the `wp_comments` table, if applicable. The comment may or may not still exist.',
  `fname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'First name at the time of the event.',
  `lname` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last name at the time of the event.',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email address at the time of the event.',
  `ip` varchar(39) COLLATE utf8_unicode_ci NOT NULL COMMENT 'IP address at the time of the event.',
  `event` enum('invalidated', 'notified') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event type (name).',
  `note_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Optional note during processing.',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Event time. Unix timestamp.',
  PRIMARY KEY (`ID`) COMMENT 'Primary key.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;