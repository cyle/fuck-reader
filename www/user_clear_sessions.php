<?php

// clear all sessions for the currently active user

// then go to logout screen

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

$delete_sessions = $mysqli->query("DELETE FROM user_sessions WHERE user_id=$current_user_id");

header('Location: /logout/');

?>