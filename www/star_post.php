<?php

// star a post

$current_user_id = 1;

if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post read
	$post_id = (int) $_GET['pid'] * 1;
} else {
	die('error: dunno what do');
}

?>