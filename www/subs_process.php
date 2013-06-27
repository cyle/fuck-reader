<?php

// do things with subscriptions!

$login_required = true;
require_once('../www-includes/login_check.php');

if (!isset($_REQUEST['a']) || trim($_REQUEST['a']) == '') {
	die('dunno what to do');
}

$action = trim($_REQUEST['a']);

//echo '<pre>'.print_r($_REQUEST, true).'</pre>';

require_once('../www-includes/dbconn_mysql.php');

if ($action == 'n') { // add a new feed to the current user
	
	// make sure new feeds don't collide with existing feed rows
	// if feed URL already exists, just add feed_id to user
	
	$feed_url = trim($_POST['feed']);
	
	if (substr($feed_url, 0, 4) != 'http' && substr($feed_url, 0, 5) != 'https') {
		die('the feed URL must start with http or https, sorry, try again.');
	}
	
	if (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
		die('the feed URL provided does not validate as a proper URL, sorry, try again');
	}
	
	$feed_url_db = "'".$mysqli->escape_string($feed_url)."'";
	
	$check_for_feed = $mysqli->query("SELECT feed_id FROM feeds WHERE feed_url=$feed_url_db");
	
	$brand_new_feed = false;
	
	if ($check_for_feed->num_rows > 0) {
		//echo 'feed already exists, just adding users_feeds record'."\n";
		$feed_row = $check_for_feed->fetch_assoc();
		$feed_id = $feed_row['feed_id'];
		// do they already have a subscription? if so, forget it
		$check_for_existing_subscription = $mysqli->query("SELECT row_id FROM users_feeds WHERE user_id=$current_user_id AND feed_id=$feed_id");
		//if (!$check_for_existing_subscription) { die('mysql error on check existing subscription: '.$mysqli->error); }
		if ($check_for_existing_subscription->num_rows > 0) {
			//echo 'nevermind, they already have it'."\n";
			die('it looks like you are already subscribed to that feed, lol.');
		}
	} else { // if not -- add it as a new feed!
		//echo 'new feed! how exciting.'."\n";
		$add_new_feed = $mysqli->query("INSERT INTO feeds (feed_url, tsc, tsu) VALUES ($feed_url_db, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		//if (!$add_new_feed) { die('mysql error on add new feed: '.$mysqli->error); }
		$feed_id = $mysqli->insert_id;
		$brand_new_feed = true;
	}
	
	$add_new_user_feed = $mysqli->query("INSERT INTO users_feeds (user_id, feed_id, tsc) VALUES ($current_user_id, $feed_id, UNIX_TIMESTAMP())");
	//if (!$add_new_user_feed) { die('mysql error on add new user feed row: '.$mysqli->error); }
	
	if ($brand_new_feed) {
		header('Location: /subs/?new_success');
	} else {
		header('Location: /subs/?added_success');
	}
	
} else if ($action == 'd') { // delete these feeds from user
	
	// in $_REQUEST['feed-id'] array or number
	
	// should probably add something in here sometime to validate that the user is deleting feeds they are subscribed to
	// so they can't random delete other peoples' feeds
	
	$feed_ids = array();
	
	if (is_array($_REQUEST['feed-id'])) {
		foreach ($_REQUEST['feed-id'] as $feed_id) {
			$feed_ids[] = (int) $feed_id * 1;
		}
		unset($feed_id);
		$feed_ids = array_unique($feed_ids);
	} else if (is_numeric($_REQUEST['feed-id'])) {
		$feed_ids[] = (int) $_REQUEST['feed-id'] * 1;
	} else {
		die('dunno what feed to delete');
	}
	
	foreach ($feed_ids as $feed_id) {
		$delete = $mysqli->query("DELETE FROM users_feeds WHERE user_id=$current_user_id AND feed_id=$feed_id");
		$post_ids = array();
		$get_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE feed_id=$feed_id");
		while ($post_row = $get_post_ids->fetch_assoc()) {
			$post_ids[] = $post_row['post_id'];
		}
		if (count($post_ids) > 0) {
			$unread = $mysqli->query("DELETE FROM users_read_posts WHERE user_id=$current_user_id AND post_id IN (".implode(',', $post_ids).")");
			$starred = $mysqli->query("DELETE FROM users_star_posts WHERE user_id=$current_user_id AND post_id IN (".implode(',', $post_ids).")");
		}
	}
	
	header('Location: /subs/?delete_success');
	
} else if ($action == 'u') { // mark all of these feeds as UNREAD
	
	// just delete rows with this user and this feed(s) from unread table
	
	// in $_REQUEST['feed-id'] array or number
	
	$feed_ids = array();
	
	if (is_array($_REQUEST['feed-id'])) {
		foreach ($_REQUEST['feed-id'] as $feed_id) {
			$feed_ids[] = (int) $feed_id * 1;
		}
		unset($feed_id);
		$feed_ids = array_unique($feed_ids);
	} else if (is_numeric($_REQUEST['feed-id'])) {
		$feed_ids[] = (int) $_REQUEST['feed-id'] * 1;
	} else {
		die('dunno what feed to unread');
	}
	
	foreach ($feed_ids as $feed_id) {
		$post_ids = array();
		$get_post_ids = $mysqli->query("SELECT post_id FROM posts WHERE feed_id=$feed_id");
		while ($post_row = $get_post_ids->fetch_assoc()) {
			$post_ids[] = $post_row['post_id'];
		}
		if (count($post_ids) > 0) {
			$unread = $mysqli->query("DELETE FROM users_read_posts WHERE user_id=$current_user_id AND post_id IN (".implode(',', $post_ids).")");
		}
	}
	
	header('Location: /subs/?unread_success');
	
} else {
	die('dunno what to do');
}

?>