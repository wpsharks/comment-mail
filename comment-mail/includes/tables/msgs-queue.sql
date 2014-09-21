CREATE TABLE IF NOT EXISTS `%%prefix%%msgs_queue` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key.',
  `subject` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Subject line.',
  `body` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Message body.',
  `insertion_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Insertion time. UNIX timestamp.',
  PRIMARY KEY (`ID`) COMMENT 'Primary key.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;