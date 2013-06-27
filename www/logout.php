<?php

// oh fuck, log em out...

if (isset($_COOKIE['fucksession']) && trim($_COOKIE['fucksession']) != '') { // user has a session already?
	// delete the saved session token from the database
	require_once('../www-includes/dbconn_mysql.php');
	$fuck_session_id = trim($_COOKIE['fucksession']);
	$fuck_session_id_db = "'".$mysqli->escape_string($fuck_session_id)."'";
	$delete_session = $mysqli->query("DELETE FROM user_sessions WHERE session_id=$fuck_session_id_db");
}

$_COOKIE = array();
unset($_COOKIE);
setcookie('fucksession', '', time() - 3600);
setcookie('fucksession', '', time() - 3600, '/', 'fuckreader.com');

header('Location: /');
die();

?>