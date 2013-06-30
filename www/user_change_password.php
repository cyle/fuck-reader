<?php

// check current password

// set new password

$login_required = true;
require_once('../www-includes/login_check.php');

if (!isset($_POST['cp']) || trim($_POST['cp']) == '') {
	die('you forgot to put in your current password, jeez.');
}

if (!isset($_POST['p1']) || trim($_POST['p1']) == '') {
	die('you forgot to put in your new password, jeez.');
}

if (!isset($_POST['p2']) || trim($_POST['p2']) == '') {
	die('you forgot to put in your new password again, jeez.');
}

if (trim($_POST['p1']) != trim($_POST['p2'])) {
	die('your new passwords do not match, goddamn.');
}

if (strlen(trim($_POST['p1'])) < 6) {
	die('your new password should be more than 6 characters, jeez.');
}

require_once('../www-includes/dbconn_mysql.php');

require_once('../www-includes/account_tools.php');

$get_user_row = $mysqli->query("SELECT * FROM users WHERE user_id=$current_user_id");
$current_user_row = $get_user_row->fetch_assoc();

// check password
if (crypt(trim($_POST['cp']), $current_user_row['pwrdlol']) != $current_user_row['pwrdlol']) {
	die('Your current password was incorrect, please try again.');
}

// okay, cool -- set new password
$pwd_salt = substr(get_key(256), 0, 22); // make a new 22-character salt
$new_user_pwd_hash = crypt(trim($_POST['p1']), '$2y$12$' . $pwd_salt);
$new_user_pwd_hash_db = "'".$mysqli->escape_string($new_user_pwd_hash)."'";
$update_users_password = $mysqli->query("UPDATE users SET pwrdlol=$new_user_pwd_hash_db WHERE user_id=$current_user_id");
if (!$update_users_password) { die('database error: '.$mysqli->error); }

// now clear sessions
$delete_sessions = $mysqli->query("DELETE FROM user_sessions WHERE user_id=$current_user_id");

// okay, done -- now log out
header('Location: /logout/');

?>