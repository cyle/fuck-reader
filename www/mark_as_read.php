<?php

// mark a post (or all posts in a feed or all unread posts entirely) as read

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');
require_once('../www-includes/dbconn_redis.php');

if (isset($_GET['ts']) && is_numeric($_GET['ts'])) {
	$before_ts = (int) $_GET['ts'] * 1;
} else {
	$before_ts = 'UNIX_TIMESTAMP()';
}

if (isset($_GET['all']) && trim($_GET['all']) == 'yup') {
	// declare all of the users' unread posts read
	// get all posts for this user older than NOW that are unread by the user
	$get_all_unread_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE ts < $before_ts AND feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id=$current_user_id) AND post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id=$current_user_id)");
	// mark all of those posts as read
	while ($unread_post = $get_all_unread_post_ids->fetch_assoc()) {
		// insert into mysql
		$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, ".$unread_post['post_id'].", UNIX_TIMESTAMP())");
		// insert into redis
		$mark_redis = $redis->sadd('postsread:'.$current_user_id, $unread_post['post_id']);
		
		// need to figure out how to decrement the users' individual feeds counts in redis
		
	}
	//echo 'ok';
	header('Location: /feeds/'); // send user back to /feeds/ for now
} else if (isset($_GET['fid']) && is_numeric($_GET['fid'])) {
	// declare all of the users' unread posts from this feed ID read
	$feed_id = (int) $_GET['fid'] * 1;
	// get all posts from this feed older than NOW that are unread by the user
	$get_feeds_unread_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE ts < $before_ts AND feed_id=$feed_id AND post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id=$current_user_id)");
	// mark all of those posts as read
	$num_posts_marked_read = 0;
	while ($unread_post = $get_feeds_unread_post_ids->fetch_assoc()) {
		// insert into mysql
		$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, ".$unread_post['post_id'].", UNIX_TIMESTAMP())");
		// insert into redis
		$mark_redis = $redis->sadd('postsread:'.$current_user_id, $unread_post['post_id']);
		$num_posts_marked_read++;
	}
	// decrement the unread count of this user's associated unread-for-this-feed cache in redis
	$decr_redis = $redis->decrby('counts:'.$current_user_id.':'.$feed_id.':unread', $num_posts_marked_read);
	//echo 'ok';
	header('Location: /feed/'.$feed_id.'/'); // send user back to /feeds/ for now
} else if (isset($_GET['d']) && trim($_GET['d']) != '') {
	// declare all posts for a certain day as read!
	if (preg_match('/^(\d+)-(\d+)-(\d+)$/', trim($_GET['d'])) == false || strtotime(trim($_GET['d'])) == false) {
		die('invalid date given');
	}
	$the_date = strtotime(trim($_GET['d']));
	$the_date_base = date('Y-m-d', $the_date);
	$the_date_start = $the_date_base . ' 12:00:00 AM';
	$the_date_end = $the_date_base . ' 11:59:59 PM';
	$the_date_start_db = strtotime($the_date_start);
	$the_date_end_db = strtotime($the_date_end);
	$get_dates_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id=$current_user_id) AND post_pubdate >= $the_date_start_db AND post_pubdate < $the_date_end_db");
	// mark all of those posts as read
	while ($unread_post = $get_dates_post_ids->fetch_assoc()) {
		// insert into mysql
		$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, ".$unread_post['post_id'].", UNIX_TIMESTAMP())");
		// insert into redis
		$mark_redis = $redis->sadd('postsread:'.$current_user_id, $unread_post['post_id']);
		
		// need to figure out how to decrement the users' individual feeds counts in redis
		
	}
	//echo 'ok';
	header('Location: /feeds/'.date('Y-m-d', $the_date).'/'); // send user back to /feeds/ for now
} else if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post read
	$post_id = (int) $_GET['pid'] * 1;
	// insert into mysql
	$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, $post_id, UNIX_TIMESTAMP())");
	// insert into redis
	$mark_redis = $redis->sadd('postsread:'.$current_user_id, $post_id);
	
	// need to figure out how to decrement the users' individual feeds counts in redis
	
	echo 'ok';
} else {
	die('error: dunno what do');
}

?>