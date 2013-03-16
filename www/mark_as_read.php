<?php

// mark a post (or all posts in a feed or all unread posts entirely) as read

$current_user_id = 1;

require_once('../www-includes/dbconn_mysql.php');

if (isset($_GET['all']) && trim($_GET['all']) == 'yup') {
	// declare all of the users' unread posts read
	
} else if (isset($_GET['fid']) && is_numeric($_GET['fid'])) {
	// declare all of the users' unread posts from this feed ID read
	$feed_id = (int) $_GET['fid'] * 1;
} else if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post read
	$post_id = (int) $_GET['pid'] * 1;
	$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, $post_id, UNIX_TIMESTAMP())");
	echo 'ok';
} else {
	die('error: dunno what do');
}

?>