<?php

// star a post

$login_required = true;
require_once('../www-includes/login_check.php');

if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	// declare this individual post read
	$post_id = (int) $_GET['pid'] * 1;
} else {
	die('error: dunno what do');
}

?>