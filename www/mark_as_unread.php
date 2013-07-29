<?php

// mark a post (or all posts in a feed or all unread posts entirely) as unread

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');
require_once('../www-includes/dbconn_redis.php');

if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post unread
	$post_id = (int) $_GET['pid'] * 1;
	$unmark = $mysqli->query("DELETE FROM users_read_posts WHERE user_id=$current_user_id AND post_id=$post_id");
	$unmark_redis = $redis->srem('postsread:'.$current_user_id, $post_id);
	
	// need to figure out how to increment the users' individual feed count in redis
	
	echo 'ok';
} else {
	die('error: dunno what do');
}

?>