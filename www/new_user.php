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

require_once('../www-includes/dbconn_mysql.php');

// check invite code...
$invite_code_db = "'".$mysqli->escape_string(trim($_POST['i']))."'";
$check_invite_code = $mysqli->query("SELECT * FROM user_invites WHERE invite_code=$invite_code_db AND is_used=0");
if ($check_invite_code->num_rows == 0) {
	die('sorry, your invite code is already used or otherwise invalid.');
}

// ok, it's cool, make a new user

$new_user_email_db = "'".$mysqli->escape_string(trim($_POST['e']))."'";

// check to see if email already in use
$check_for_email = $mysqli->query("SELECT user_id FROM users WHERE email=$new_user_email_db");
if ($check_for_email->num_rows > 0) {
	die('sorry, but that email address appears to already be in use.');
}

$pwd_salt = 'BnvOuRHx6CKhiOZUm0BmKH';
$new_user_pwd_hash = crypt(trim($_POST['p1']), '$2y$12$' . $pwd_salt);
$new_user_pwd_hash_db = "'".$mysqli->escape_string($new_user_pwd_hash)."'";

$new_user_row = $mysqli->query("INSERT INTO users (email, pwrdlol, tsc, tsu) VALUES ($new_user_email_db, $new_user_pwd_hash_db, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
if (!$new_user_row) {
	die('error creating new user: '.$mysqli->error);
}

$new_user_id = $mysqli->insert_id;

$code_used = $mysqli->query("UPDATE user_invites SET is_used=1, by_new_user=$new_user_id WHERE invite_code=$invite_code_db");

header('Location: /login/?register_success');

?>