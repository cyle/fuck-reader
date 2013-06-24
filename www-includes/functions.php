<?php

// the necessary functions



// no "unread" functionality yet




require_once('dbconn_mysql.php');

function getUsersFeeds($user_id = 0) {
	// get feed IDs for user, return array
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$users_feeds = array();
	
	global $mysqli;
	
	$get_feeds = $mysqli->query('SELECT * FROM feeds WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') ORDER BY feed_title ASC, feed_id ASC');
	
	if ($get_feeds->num_rows == 0) {
		return array();
	} else {
		while ($feed_row = $get_feeds->fetch_assoc()) {
			$users_feeds[$feed_row['feed_id']] = $feed_row['feed_title'];
		}
		return $users_feeds;
	}
	
}

function getFeedPosts($user_id = 0, $feed_id = 0, $just_unread = true) {
	// get a feed's posts for a user, possibly get all of them
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$feed_id = (int) $feed_id * 1;
	$feed_posts = array();
	
	global $mysqli;
	
	if ($just_unread == true) {
		$get_posts = $mysqli->query('SELECT posts.* FROM posts WHERE posts.feed_id='.$feed_id.' AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.') ORDER BY posts.post_pubdate DESC LIMIT 25');
	} else {
		$get_posts = $mysqli->query('SELECT posts.*, users_read_posts.row_id AS is_read FROM posts LEFT JOIN users_read_posts ON users_read_posts.post_id=posts.post_id AND users_read_posts.user_id='.$user_id.' WHERE posts.feed_id='.$feed_id.' ORDER BY posts.post_pubdate DESC LIMIT 25');
	}
	
	if ($get_posts->num_rows == 0) {
		return array();
	} else {
		while ($post = $get_posts->fetch_assoc()) {
			$feed_posts[] = $post;
		}
		return $feed_posts;
	}
	
}

function getAllPosts($user_id = 0, $just_unread = true) {
	// get all feeds' posts for user, possibly get all of them
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$feeds_posts = array();
	
	global $mysqli;
	
	if ($just_unread == true) {
		$get_posts = $mysqli->query('SELECT * FROM posts WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.') ORDER BY posts.post_pubdate DESC LIMIT 25');
	} else {
		$get_posts = $mysqli->query('SELECT
posts.*, users_read_posts.row_id AS is_read
FROM posts
LEFT JOIN users_read_posts ON users_read_posts.post_id=posts.post_id
AND users_read_posts.user_id='.$user_id.'
WHERE posts.feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.')
ORDER BY posts.post_pubdate DESC 
LIMIT 25');
	}
	
	if ($get_posts->num_rows == 0) {
		return array();
	} else {
		while ($post = $get_posts->fetch_assoc()) {
			$feeds_posts[] = $post;
		}
		return $feeds_posts;
	}
}

function postBit($post = array(), $users_feeds = array()) {
	// return post item for feed
	
	echo '<div class="post '.((isset($post['is_read'])) ? 'read': '').'" data-post-id="'.$post['post_id'].'">';
	echo '<h3 class="post-title"><a href="'.$post['post_permalink'].'" target="_blank">'.(($post['post_title'] != null) ? htmlspecialchars(strip_tags($post['post_title']), ENT_NOQUOTES, 'UTF-8') : 'Untitled').'</a></h3>';
	echo '<div class="post-byline">Published <span class="post-pubdate">'.date('F jS, Y g:iA', $post['post_pubdate']).'</span> on <span class="post-feed-source">'.$users_feeds[$post['feed_id']].'</span></div>';
	echo '<div class="post-content" style="display:none;">';
	//echo strip_tags($post['post_content'], '<p><blockquote><a><b><i><em><strong>');
	$post_body = new DOMDocument('1.0', 'UTF-8');
	error_reporting(0);
	$post_body->loadHTML('<?xml encoding="UTF-8">' . $post['post_content']);
	foreach ($post_body->childNodes as $item) {
		if ($item->nodeType == XML_PI_NODE) {
			$post_body->removeChild($item); // remove hack
		}
	}
	unset($item);
	error_reporting(1);
	$post_body->encoding = 'UTF-8';
	echo $post_body->saveHTML();
	echo '<div class="post-readmore"><a href="'.$post['post_permalink'].'" target="_blank" class="post-readmore-link">Go to original &raquo;</a></div>';
	echo '</div>';
	echo '</div>';
	
}

?>