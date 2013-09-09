<?php

// cleanup script

/*

do the following:
	- find posts more than 30 days old that are not starred by anyone
		- fetch their IDs
		- delete them
		- delete all read/unread data attached to them
	- find feeds that are no longer subscribed to by anyone
		- for their posts, fetch their IDs, then also delete read/unread data attached to them
		- delete them, and their posts
	- delete duplicate "user X and read post Y" rows

*/

if (php_sapi_name() !== 'cli') {
	die('sorry, this can only be run via the command line.');
}

$post_time_limit = 30; // how many days to keep posts around if they are not starred by anyone

echo 'FUCK READER Cleanup Script'."\n";
echo 'Starting: '.date(DATE_RFC822)."\n";

require_once('dbconn_mysql.php');


/*

	get old posts...

*/

$get_old_posts = $mysqli->query('SELECT post_id FROM posts WHERE ts < '.strtotime('-'.$post_time_limit.' days').' AND post_id NOT IN (SELECT post_id FROM users_star_posts)');
if ($get_old_posts->num_rows > 0) {
	echo 'Found '.$get_old_posts->num_rows.' posts over '.$post_time_limit.' days old that have not been starred by anyone.'."\n";
	
	//$old_post_ids = array();
	
	// now delete them and their read/unread data
	echo 'Deleting old posts...'."\n";
	
	while ($old_post_row = $get_old_posts->fetch_assoc()) {
		//$old_post_ids[] = $old_post_row['post_id'] * 1;
		$delete_read_data = $mysqli->query('DELETE FROM users_read_posts WHERE post_id = ' . $old_post_row['post_id']);
		if (!$delete_read_data) { echo 'database error deleting old users_read_posts data: '.$mysqli->error."\n"; }
		$delete_posts = $mysqli->query('DELETE FROM posts WHERE post_id = ' . $old_post_row['post_id']);
		if (!$delete_posts) { echo 'database error deleting old posts data: '.$mysqli->error."\n"; }
		echo '.';
	}
	
	echo "done \n";
	
} else {
	echo 'Found no posts that are over '.$post_time_limit.' days old and have not been starred by anyone.'."\n";
}

//unset($old_post_ids);


/*

	get orphaned feeds...

*/

$get_orphaned_feeds = $mysqli->query('SELECT feed_id, feed_title FROM feeds WHERE feed_id NOT IN (SELECT feed_id FROM users_feeds)');
if ($get_orphaned_feeds->num_rows > 0) {
	
	echo 'Found '.$get_orphaned_feeds->num_rows.' feeds not being subscribed to by any user.'."\n";
	
	$orphaned_feed_ids = array();
	
	while ($orphaned_feed_row = $get_orphaned_feeds->fetch_assoc()) {
		$orphaned_feed_ids[] = $orphaned_feed_row['feed_id'] * 1;
		echo 'Orphaned feed: #'.$orphaned_feed_row['feed_id'].' - '.$orphaned_feed_row['feed_title']."\n";
	}
	
	// get their post IDs
	$get_orphaned_posts = $mysqli->query('SELECT post_id FROM posts WHERE feed_id IN ('.implode(', ', $orphaned_feed_ids).')');
	if ($get_orphaned_posts->num_rows > 0) {
		
		echo 'Found '.$get_orphaned_posts->num_rows.' posts under the orphaned feeds.'."\n";
		
		$orphaned_post_ids = array();
		
		while ($orphaned_post_row = $get_orphaned_posts->fetch_assoc()) {
			$orphaned_post_ids[] = $orphaned_post_row['post_id'] * 1;
		}
		
		// delete the posts under that feed
		// delete the read and star data under that feed based on post IDs
		echo 'Deleting those posts...'."\n";
	
		$delete_read_data = $mysqli->query('DELETE FROM users_read_posts WHERE post_id IN ('.implode(', ', $orphaned_post_ids).')');
		if (!$delete_read_data) { echo 'database error deleting orphaned users_read_posts data: '.$mysqli->error."\n"; }
		
		$delete_star_data = $mysqli->query('DELETE FROM users_star_posts WHERE post_id IN ('.implode(', ', $orphaned_post_ids).')');
		if (!$delete_star_data) { echo 'database error deleting orphaned users_star_posts data: '.$mysqli->error."\n"; }
		
		$delete_posts = $mysqli->query('DELETE FROM posts WHERE post_id IN ('.implode(', ', $orphaned_post_ids).')');
		if (!$delete_posts) { echo 'database error deleting orphaned posts data: '.$mysqli->error."\n"; }
		
	} else {
		echo 'No posts found under the orphaned feeds.'."\n";	
	}
	
	// finally, delete the feed row
	$delete_feed = $mysqli->query('DELETE FROM feeds WHERE feed_id IN ('.implode(', ', $orphaned_feed_ids).')');
	if (!$delete_feed) { echo 'database error deleting orphaned feeds data: '.$mysqli->error."\n"; }
	
} else {
	echo 'Found no orphaned feeds.'."\n";
}

/*

	remove duplicate read and star data

*/







/*

	that's all, folks!

*/

echo 'Finishing: '.date(DATE_RFC822)."\n";
echo 'All done!'."\n";

?>