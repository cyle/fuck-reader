<?php

// star a post

$login_required = true;
require_once('../www-includes/login_check.php');

if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post starred
	$post_id = (int) $_GET['pid'] * 1;
	$star = $mysqli->query("INSERT INTO users_star_posts (user_id, post_id, tsc) VALUES ($current_user_id, $post_id, UNIX_TIMESTAMP())");
	echo 'ok';
} else {
	die('error: dunno what do');
}

?>