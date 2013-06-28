<?php

// see if user is currently logged in

// check for cookie

// check for session based on cookie

// if not, parse username/password
// based on https://gist.github.com/dzuelke/972386

#Generate a random key from /dev/random
function get_key($bit_length = 128){
	$fp = @fopen('/dev/random','rb');
	if ($fp !== FALSE) {
		$key = substr(base64_encode(@fread($fp,($bit_length + 7) / 8)), 0, (($bit_length + 5) / 6)  - 2);
		@fclose($fp);
		return $key;
	}
	return null;
}

// the defaults:
$current_user = array();
$current_user['loggedin'] = false;

if (isset($_COOKIE['fucksession']) && trim($_COOKIE['fucksession']) != '') { // user has a session already?
	
	require_once('dbconn_mysql.php');
	
	$fuck_session_id = trim($_COOKIE['fucksession']);
	$fuck_session_id_db = "'".$mysqli->escape_string($fuck_session_id)."'";
	
	$check_for_session = $mysqli->query("SELECT * FROM user_sessions WHERE session_id=$fuck_session_id_db AND expires > UNIX_TIMESTAMP()");
	if ($check_for_session->num_rows == 1) {
		// oh snap -- they have a session!
		$fuck_session_row = $check_for_session->fetch_assoc();
		$current_user_id = $fuck_session_row['user_id'];
		$current_user['loggedin'] = true;
		$current_user['user_id'] = $current_user_id;
		$new_session_key_expires = time() + (60*60*24*30);
		setcookie('fucksession', $fuck_session_id, $new_session_key_expires, '/', 'fuckreader.com');
		$update_session_expiry = $mysqli->query("UPDATE user_sessions SET expires=$new_session_key_expires WHERE session_id=$fuck_session_id_db");
		if ($_SERVER['PHP_SELF'] == 'login.php') {
			header('Location: /feeds/');
			die();
		}
	}
	
} else if (isset($_POST['e']) && isset($_POST['p'])) { // user is trying to log in?
	
	require_once('dbconn_mysql.php');
		
	require_once('../config/login_config.php');
	$pwd_attempt_hash = crypt(trim($_POST['p']), '$2y$12$' . $pwd_salt);
	
	$users_email_db = "'".$mysqli->escape_string(trim($_POST['e']))."'";
	$pwd_attempt_hash_db = "'".$mysqli->escape_string($pwd_attempt_hash)."'";
	
	$check_for_user = $mysqli->query("SELECT * FROM users WHERE email=$users_email_db AND pwrdlol=$pwd_attempt_hash_db");
	if ($check_for_user->num_rows == 1) {
		// ok, cool
		$current_user_row = $check_for_user->fetch_assoc();
		$current_user['loggedin'] = true;
		$current_user['user_id'] = (int) $current_user_row['user_id'] * 1;
		$current_user_id = $current_user['user_id'];
		$new_session_key = get_key(256);
		$new_session_key_expires = time() + (60*60*24*30);
		setcookie('fucksession', $new_session_key, $new_session_key_expires, '/', 'fuckreader.com');
		
		// write session to database
		$new_session_key_db = "'".$mysqli->escape_string($new_session_key)."'";
		$new_session_row = $mysqli->query("INSERT INTO user_sessions (session_id, user_id, expires) VALUES ($new_session_key_db, $current_user_id, $new_session_key_expires)");
		
		// logged in, cool -- send to /feeds/
		header('Location: /feeds/');
		die();
	} else {
		die('Could not find that email and/or wrong password, sorry. Try again, I guess.');
	}
				
} else if (isset($login_required) && $login_required == true) {
	
	header('Location: /login/');
	die();
	
}

?>