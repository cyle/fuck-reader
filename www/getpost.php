<?php

// take in post ID, give back post JSON
// if given a user ID, verify it's them, and mark the post as read for them

if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
	die(json_encode(array('error' => 'no post ID given, dunno what to look up')));
}

$post_id = (int) $_GET['pid'] * 1;

require_once('../www-includes/dbconn_mysql.php');

$get_post = $mysqli->query('SELECT * FROM posts WHERE post_id='.$post_id);
if (!$get_post || $get_post->num_rows == 0) {
	die(json_encode(array('error' => 'no post found with that ID, sorry')));
}

$post = $get_post->fetch_assoc();

$post_json = array();
$post_json['title'] = $post['post_title'];
$post_json['guid'] = $post['post_guid'];
$post_json['url'] = $post['post_permalink'];
$post_json['author'] = $post['post_byline'];
$post_json['published'] = $post['post_pubdate'];

$get_post_content = $mysqli->query('SELECT * FROM posts_content WHERE post_id='.$post_id);
if (!$get_post_content || $get_post_content->num_rows == 0) {
	die(json_encode(array('error' => 'no post content found with that ID, sorry')));
}

$post_content = $get_post_content->fetch_assoc();

$post_final_body = '';
$post_body = new DOMDocument('1.0', 'UTF-8');
error_reporting(0);
$post_body->loadHTML('<?xml encoding="UTF-8">' . $post_content['post_content']);
foreach ($post_body->childNodes as $item) {
	if ($item->nodeType == XML_PI_NODE) {
		$post_body->removeChild($item); // remove hack
	}
}
unset($item);
error_reporting(1);
$post_body->encoding = 'UTF-8';
$post_final_body .= $post_body->saveHTML();
$post_final_body .= "\n";
$post_final_body .= '<div class="post-readmore"><a href="'.$post['post_permalink'].'" target="_blank" class="post-readmore-link">Go to original &raquo;</a></div>'."\n";

$post_json['content'] = $post_final_body;

echo json_encode($post_json);

// mark as read, if user is logged in
require_once('../www-includes/login_check.php');

if (isset($current_user_id) && is_numeric($current_user_id)) {
	// do not do this if they are already read
	$check_for_if_read = $mysqli->query("SELECT row_id FROM users_read_posts WHERE user_id=$current_user_id AND post_id=$post_id");
	if ($check_for_if_read->num_rows == 0) {
		$mark = $mysqli->query("INSERT INTO users_read_posts (user_id, post_id, tsc) VALUES ($current_user_id, $post_id, UNIX_TIMESTAMP())");
	}
}

?>