<?php

// star a post

$login_required = true;
require_once('../www-includes/login_check.php');

if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post unstarred
	$post_id = (int) $_GET['pid'] * 1;
	$unstar = $mysqli->query("DELETE FROM users_star_posts WHERE user_id=$current_user_id AND post_id=$post_id");
	echo 'ok';
} else {
	die('error: dunno what do');
}

?>