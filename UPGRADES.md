# Upgrade/migration procedures

## from commit 9761af to c166097

Run the following in MySQL or MariaDB, whatever you're using, in the `fuckreader` database:

    CREATE TABLE `posts_content` (`content_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `post_id` int(11) unsigned NOT NULL, `post_content` text NOT NULL, PRIMARY KEY (`content_id`), KEY `post_id` (`post_id`) ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    INSERT INTO posts_content (post_id, post_content) SELECT post_id, post_content FROM posts ORDER BY post_id ASC;
    ALTER TABLE posts DROP COLUMN post_content;

That's it!