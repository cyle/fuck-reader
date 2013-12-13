<?php

// give everyone X keys to invite people with
$keys_to_give = 3;
$key_bit_depth = 160;

require_once('../../www-includes/dbconn_mysql.php');
require_once('../../www-includes/account_tools.php');

$get_users = $mysqli->query('SELECT user_id FROM users');
while ($user = $get_users->fetch_assoc()) {
	$user_id = $user['user_id'];
	for ($i = 0; $i < $keys_to_give; $i++) {
		$new_key = get_key($key_bit_depth);
		$new_key_db = "'".$mysqli->escape_string($new_key)."'";
		$insert_new_key_for_user = $mysqli->query("INSERT INTO user_invites (invite_code, owner_id, tsc) VALUES ($new_key_db, $user_id, UNIX_TIMESTAMP())");
		if (!$insert_new_key_for_user) { die('database error: '.$mysqli->error); }
	}
	echo '<p>gave user #'.$user_id.' '.$keys_to_give.' new keys!</p>'."\n";
}


?>