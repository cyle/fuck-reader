<?php

// see if user is currently logged in

// check for cookie

// check for session based on cookie

// if not, parse username/password
// based on https://gist.github.com/dzuelke/972386

require_once('account_tools.php');

// the defaults:
$current_user = array();
$current_user['loggedin'] = false;

$flood_control_script_name = 'login';

if (isset($_COOKIE['fucksession']) && trim($_COOKIE['fucksession']) != '') { // user has a session already?
	
	require_once('dbconn_mysql.php');
	
	$fuck_session_complete_token = trim($_COOKIE['fucksession']);
	if (strpos($fuck_session_complete_token, ':') === false) {
		// using the old session ID
		header('Location: /logout/');
		die();
	} else {
		// using the new session key/secret system
		$fuck_session_pieces = explode(':', $fuck_session_complete_token);
		$fuck_session_key_db = "'".$mysqli->escape_string($fuck_session_pieces[0])."'";
		$check_for_session = $mysqli->query("SELECT * FROM user_sessions WHERE session_key=$fuck_session_key_db AND expires > UNIX_TIMESTAMP()");
		if ($check_for_session->num_rows == 1) {
			// oh snap -- they might have a session if the secret matches!
			$fuck_session_row = $check_for_session->fetch_assoc();
			// validate secret
			if (crypt($fuck_session_pieces[1], $fuck_session_row['session_secret']) != $fuck_session_row['session_secret']) {
				// something is fucked, try again!
				header('Location: /logout/');
				die();
			}
			// ok, they're cool
			$current_user_id = $fuck_session_row['user_id'];
			$current_user['loggedin'] = true;
			$current_user['user_id'] = $current_user_id;
			$new_session_key_expires = time() + (60*60*24*30);
			setcookie('fucksession', $fuck_session_complete_token, $new_session_key_expires, '/', 'fuckreader.com');
			$update_session_expiry = $mysqli->query("UPDATE user_sessions SET expires=$new_session_key_expires WHERE session_key=$fuck_session_key_db AND user_id=$current_user_id");
			// update last access time
			$update_last_access = $mysqli->query("UPDATE users SET lastaccess=UNIX_TIMESTAMP() WHERE user_id=$current_user_id");
			if ($_SERVER['PHP_SELF'] == 'login.php') {
				header('Location: /feeds/');
				die();
			}
		} else {
			// session is expired, make them log in again!
			header('Location: /logout/');
			die();
		}
	}
	
	
} else if (isset($_POST['e']) && isset($_POST['p'])) { // user is trying to log in?
	
	require_once('dbconn_mysql.php');
	
	$flood_control_script_name = "'".$mysqli->escape_string($flood_control_script_name)."'";
	
	// login flood control
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
				die('Sorry, you have tried to log in unsuccessfully way too many times. Please try again in a half hour or so.');
			}
		}
	}
		
	$users_email_db = "'".$mysqli->escape_string(trim($_POST['e']))."'";
	
	$check_for_user = $mysqli->query("SELECT * FROM users WHERE email=$users_email_db");
	if ($check_for_user->num_rows == 1) {
		$current_user_row = $check_for_user->fetch_assoc();
		
		// check password
		if (crypt(trim($_POST['p']), $current_user_row['pwrdlol']) != $current_user_row['pwrdlol']) {
			if (!$has_flood_control_limit) {
				$insert_flood_control = $mysqli->query("INSERT INTO flood_control (ipaddr, attempts, script, tsc) VALUES ($attempt_ip_db, 1, $flood_control_script_name, UNIX_TIMESTAMP())");
			}
			die('Your password was incorrect, please try again.');
		} else {
			// password checked out, clear flood control, if there was any
			if ($has_flood_control_limit) {
				$delete_flood_control = $mysqli->query('DELETE FROM flood_control WHERE id='.$flood_control_info['id']);
			}
		}
		
		// ok, cool
		$current_user['loggedin'] = true;
		$current_user['user_id'] = (int) $current_user_row['user_id'] * 1;
		$current_user_id = $current_user['user_id'];
		$new_session_key = get_key(256);
		$new_session_secret = get_key(256);
		$new_session_complete_token = $new_session_key.':'.$new_session_secret;
		$new_session_secret_salt = substr(get_key(256), 0, 22); // make a new 22-character salt
		$new_session_secret_hash = crypt($new_session_secret, '$2y$12$' . $new_session_secret_salt);
		$new_session_key_expires = time() + (60*60*24*30);
		setcookie('fucksession', $new_session_complete_token, $new_session_key_expires, '/', 'fuckreader.com');
		
		// write session to database
		$new_session_key_db = "'".$mysqli->escape_string($new_session_key)."'";
		$new_session_secret_hash_db = "'".$mysqli->escape_string($new_session_secret_hash)."'";
		$new_session_row = $mysqli->query("INSERT INTO user_sessions (session_key, session_secret, user_id, expires, ts) VALUES ($new_session_key_db, $new_session_secret_hash_db, $current_user_id, $new_session_key_expires, UNIX_TIMESTAMP())");
				
		// this will change everyone's password to a new hash with a new unique salt...
		/*
		$pwd_salt = substr(get_key(256), 0, 22); // make a new 22-character salt
		$new_user_pwd_hash = crypt(trim($_POST['p']), '$2y$12$' . $pwd_salt);
		$new_user_pwd_hash_db = "'".$mysqli->escape_string($new_user_pwd_hash)."'";
		$update_users_password = $mysqli->query("UPDATE users SET pwrdlol=$new_user_pwd_hash_db WHERE user_id=$current_user_id");
		*/
		
		// update last access time
		$update_last_access = $mysqli->query("UPDATE users SET lastaccess=UNIX_TIMESTAMP() WHERE user_id=$current_user_id");
		
		// logged in, cool -- send to /feeds/
		header('Location: /feeds/');
		die();
	} else {
		if (!$has_flood_control_limit) {
			$insert_flood_control = $mysqli->query("INSERT INTO flood_control (ipaddr, attempts, script, tsc) VALUES ($attempt_ip_db, 1, $flood_control_script_name, UNIX_TIMESTAMP())");
		}
		die('Could not find that email address, sorry. Try again, I guess.');
	}
				
} else if (isset($login_required) && $login_required == true) {
	
	header('Location: /login/');
	die();
	
}

?>