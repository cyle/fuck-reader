<?php

// make a new goddamn user

if (!isset($_POST['i']) || trim($_POST['i']) == '') {
	die('you forgot to put in a goddamn invite code, jeez.');
}

if (!isset($_POST['e']) || trim($_POST['e']) == '') {
	die('you forgot to put in a goddamn email address, jeez.');
}

if (!filter_var(trim($_POST['e']), FILTER_VALIDATE_EMAIL)) {
	die('the email address you put in is invalid, jeez.');
}

if (!isset($_POST['p1']) || trim($_POST['p1']) == '') {
	die('you forgot to put in your password, jeez.');
}

if (!isset($_POST['p2']) || trim($_POST['p2']) == '') {
	die('you forgot to put in your password again, jeez.');
}

if (trim($_POST['p1']) != trim($_POST['p2'])) {
	die('your passwords do not match, goddamn.');
}

if (strlen(trim($_POST['p1'])) < 6) {
	die('your password should be more than 6 characters, jeez.');
}

require_once('../www-includes/account_tools.php');

require_once('../www-includes/dbconn_mysql.php');

// check flood control
$flood_control_script_name = "'".$mysqli->escape_string('register')."'";
$has_flood_control_limit = false;
$attempt_ip_db = "'".$mysqli->escape_string(trim($_SERVER['REMOTE_ADDR']))."'";
$check_flood_control = $mysqli->query("SELECT * FROM flood_control WHERE script=$flood_control_script_name AND ipaddr=$attempt_ip_db");
if ($check_flood_control->num_rows > 0) {
	$has_flood_control_limit = true;
	$flood_control_info = $check_flood_control->fetch_assoc();
	if ($flood_control_info['tsc'] < (time() - 1800)) {
		// it was over half an hour ago -- remove it
		$delete_flood_control = $mysqli->query('DELETE FROM flood_control WHERE id='.$flood_control_info['id']);
		$has_flood_control_limit = false;
	} else {
		$update_flood_control = $mysqli->query('UPDATE flood_control SET attempts=attempts+1 WHERE id='.$flood_control_info['id']);
		if ($flood_control_info['attempts'] > 20) {
			die('Sorry, you have tried to register unsuccessfully way too many times. Please try again in a half hour or so.');
		}
	}
}


// check invite code...
$invite_code_db = "'".$mysqli->escape_string(trim($_POST['i']))."'";
$check_invite_code = $mysqli->query("SELECT * FROM user_invites WHERE invite_code=$invite_code_db AND is_used=0");
if ($check_invite_code->num_rows == 0) {
	if (!$has_flood_control_limit) {
		$insert_flood_control = $mysqli->query("INSERT INTO flood_control (ipaddr, attempts, script, tsc) VALUES ($attempt_ip_db, 1, $flood_control_script_name, UNIX_TIMESTAMP())");
	}
	die('sorry, your invite code is already used or otherwise invalid.');
} else {
	// invite code checked out, clear flood control, if there was any
	if ($has_flood_control_limit) {
		$delete_flood_control = $mysqli->query('DELETE FROM flood_control WHERE id='.$flood_control_info['id']);
	}
}

// ok, it's cool, make a new user

$new_user_email_db = "'".$mysqli->escape_string(trim($_POST['e']))."'";

// check to see if email already in use
$check_for_email = $mysqli->query("SELECT user_id FROM users WHERE email=$new_user_email_db");
if ($check_for_email->num_rows > 0) {
	die('sorry, but that email address appears to already be in use.');
}

$pwd_salt = substr(get_key(256), 0, 22); // make a new 22-character salt
$new_user_pwd_hash = crypt(trim($_POST['p1']), '$2y$12$' . $pwd_salt);
$new_user_pwd_hash_db = "'".$mysqli->escape_string($new_user_pwd_hash)."'";

$new_user_row = $mysqli->query("INSERT INTO users (email, pwrdlol, tsc, tsu) VALUES ($new_user_email_db, $new_user_pwd_hash_db, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
if (!$new_user_row) {
	die('error creating new user: '.$mysqli->error);
}

$new_user_id = $mysqli->insert_id;

$code_used = $mysqli->query("UPDATE user_invites SET is_used=1, by_new_user=$new_user_id, used_ts=UNIX_TIMESTAMP() WHERE invite_code=$invite_code_db");

header('Location: /login/?register_success');

?>