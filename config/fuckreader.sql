--
-- Database: `fuckreader`
--
CREATE DATABASE IF NOT EXISTS `fuckreader` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `fuckreader`;

-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE `feeds` (
  `feed_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_url` text NOT NULL,
  `feed_title` varchar(250) DEFAULT NULL,
  `feed_homeurl` varchar(250) DEFAULT NULL,
  `tsc` int(11) NOT NULL,
  `tsu` int(11) NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `flood_control`
--

CREATE TABLE `flood_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddr` varchar(64) NOT NULL,
  `script` varchar(250) NOT NULL,
  `attempts` int(11) NOT NULL,
  `tsc` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ipaddr` (`ipaddr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL,
  `post_title` varchar(250) DEFAULT NULL,
  `post_guid` varchar(250) NOT NULL,
  `post_permalink` text NOT NULL,
  `post_content` text NOT NULL,
  `post_byline` varchar(250) DEFAULT NULL,
  `post_pubdate` int(11) NOT NULL,
  `ts` int(11) NOT NULL,
  `chksum` varchar(50) NOT NULL,
  PRIMARY KEY (`post_id`),
  KEY `feed_id_index` (`feed_id`),
  KEY `chksum_index` (`chksum`),
  KEY `ts_index` (`ts`),
  KEY `post_title_index` (`post_title`),
  KEY `post_guid_index` (`post_guid`),
  KEY `major_index` (`ts`,`feed_id`,`post_id`),
  KEY `retrieval_index` (`feed_id`,`post_id`),
  KEY `pubdate_index` (`post_pubdate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts_content`
--

CREATE TABLE `posts_content` (
  `content_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) unsigned NOT NULL,
  `post_content` text NOT NULL,
  PRIMARY KEY (`content_id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uname` varchar(250) DEFAULT NULL,
  `pwrdlol` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `tsc` int(11) NOT NULL,
  `tsu` int(11) NOT NULL,
  `lastaccess` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_feeds`
--

CREATE TABLE `users_feeds` (
  `row_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `feed_id` int(11) unsigned NOT NULL,
  `tsc` int(11) NOT NULL,
  PRIMARY KEY (`row_id`),
  KEY `user_id_index` (`user_id`),
  KEY `feed_id_index` (`feed_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_read_posts`
--

CREATE TABLE `users_read_posts` (
  `row_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `post_id` int(11) unsigned NOT NULL,
  `tsc` int(11) NOT NULL,
  PRIMARY KEY (`row_id`),
  UNIQUE KEY `user_read_key` (`user_id`,`post_id`),
  KEY `user_id_index` (`user_id`),
  KEY `post_id_index` (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_star_posts`
--

CREATE TABLE `users_star_posts` (
  `row_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `post_id` int(11) unsigned NOT NULL,
  `tsc` int(11) NOT NULL,
  PRIMARY KEY (`row_id`),
  UNIQUE KEY `user_star_key` (`user_id`,`post_id`),
  KEY `user_id_index` (`user_id`),
  KEY `post_id_index` (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_invites`
--

CREATE TABLE `user_invites` (
  `invite_id` int(11) NOT NULL AUTO_INCREMENT,
  `invite_code` varchar(255) NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `by_new_user` int(11) unsigned DEFAULT NULL,
  `tsc` int(11) NOT NULL DEFAULT '0',
  `used_ts` int(11) DEFAULT NULL,
  PRIMARY KEY (`invite_id`),
  UNIQUE KEY `invite_code_unique` (`invite_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_key` varchar(255) DEFAULT NULL,
  `session_secret` varchar(255) DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `expires` int(11) NOT NULL,
  `ts` int(11) NOT NULL,
  KEY `session_key_index` (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
