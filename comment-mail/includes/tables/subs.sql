CREATE TABLE IF NOT EXISTS `%%prefix%%subs` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key.',
  `key` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'A unique, unguessable, non-numeric, caSe-insensitive key (20 chars max).',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID from the `wp_users` table. Leave this empty (zero) for commenters that are not stored as users.',
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Post ID from the `wp_posts` table. A comment subscription is always associated with a specific post ID.',
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment ID from the `wp_comments` table. Applicable only when a subscription is to a specific comment. Otherwise, leave empty (zero) to indicate the subscription is for all comments/replies associated w/ the`post_id`.',
  `deliver` enum('asap','hourly','daily','weekly') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'asap' COMMENT 'Delivery cycle.',
  `fname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'First name. If we have a `user_id`, use value from `wp_usermeta` table.',
  `lname` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last name. If we have a `user_id`, use value from `wp_usermeta` table.',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email address. If we have a `user_id`, use value from `wp_users` table.',
  `insertion_ip` varchar(39) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Insertion IP address.',
  `last_ip` varchar(39) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known IP address.',
  `status` enum('unconfirmed','subscribed','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unconfirmed' COMMENT 'Current subscription status.',
  `insertion_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Row insertion time. UNIX timestamp.',
  `last_update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last row update time. Unix timestamp.',
  PRIMARY KEY (`ID`) COMMENT 'Primary key.',
  UNIQUE KEY `unique_key` (`key`) COMMENT 'Forces a unique key.',
  UNIQUE KEY `unique_subscription` (`user_id`,`post_id`,`comment_id`,`email`) COMMENT 'Identifies a unique subscription.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;