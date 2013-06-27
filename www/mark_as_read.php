<?php

// mark a post (or all posts in a feed or all unread posts entirely) as read

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

if (isset($_GET['all']) && trim($_GET['all']) == 'yup') {
	// declare all of the users' unread posts read
	// get all posts for this user older than NOW that are unread by the user
	$get_all_unread_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE ts < UNIX_TIMESTAMP() AND post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id=$current_user_id)");
	// mark all of those posts as read
	while ($unread_post = $get_all_unread_post_ids->fetch_assoc()) {
		$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, ".$unread_post['post_id'].", UNIX_TIMESTAMP())");
	}
	//echo 'ok';
	header('Location: /feeds/'); // send user back to /feeds/ for now
	
} else if (isset($_GET['fid']) && is_numeric($_GET['fid'])) {
	// declare all of the users' unread posts from this feed ID read
	$feed_id = (int) $_GET['fid'] * 1;
	// get all posts from this feed older than NOW that are unread by the user
	$get_feeds_unread_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE ts < UNIX_TIMESTAMP() AND feed_id=$feed_id AND post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id=$current_user_id)");
	// mark all of those posts as read
	while ($unread_post = $get_feeds_unread_post_ids->fetch_assoc()) {
		$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, ".$unread_post['post_id'].", UNIX_TIMESTAMP())");
	}
	//echo 'ok';
	header('Location: /feed/'.$feed_id.'/'); // send user back to /feeds/ for now
} else if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post read
	$post_id = (int) $_GET['pid'] * 1;
	$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, $post_id, UNIX_TIMESTAMP())");
	echo 'ok';
} else {
	die('error: dunno what do');
}

?>