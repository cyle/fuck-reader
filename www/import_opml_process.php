<?php

// upload OPML XML file, process it, then delete it

$current_user_id = 1;

if (!isset($_FILES['f'])) {
	die('error: no file uploaded');
}

$file_handle = $_FILES['f'];

$opml_xml = simplexml_load_file($file_handle['tmp_name']);

if ($opml_xml == false) {
	die('error: malformed OPML file, or something like that');
}

//echo '<pre>'.print_r($opml_xml, true).'</pre>';

if (isset($opml_xml->body) && isset($opml_xml->body->outline)) {

	require_once('../www-includes/dbconn_mysql.php');
	
	echo '<pre>';
	
	foreach ($opml_xml->body->outline as $feed) { // go through all the feeds in the XML file
		
		if (!isset($feed['type']) || strtolower($feed['type']) != 'rss') {
			continue;
		}
		
		//echo '<pre>'.print_r($feed_url_db, true).'</pre>';
		echo 'attempting to add '.$feed['title']."\n";
		
		$feed_url_db = "'".$mysqli->escape_string($feed['xmlUrl'])."'";
		$feed_homeurl_db = "'".$mysqli->escape_string($feed['htmlUrl'])."'";
		$feed_title_db = "'".$mysqli->escape_string($feed['title'])."'";
		
		// check to see if this feed already exists -- if so, just add a record into users_feeds
		$check_for_feed = $mysqli->query("SELECT feed_id FROM feeds WHERE feed_url = $feed_url_db");
		if (!$check_for_feed) { die('mysql error on check for feeds existence: '.$mysqli->error); }
		if ($check_for_feed->num_rows > 0) {
			echo 'feed already exists, just adding users_feeds record'."\n";
			$feed_row = $check_for_feed->fetch_assoc();
			$feed_id = $feed_row['feed_id'];
			// do they already have a subscription? if so, forget it
			$check_for_existing_subscription = $mysqli->query("SELECT row_id FROM users_feeds WHERE user_id=$current_user_id AND feed_id=$feed_id");
			if (!$check_for_existing_subscription) { die('mysql error on check existing subscription: '.$mysqli->error); }
			if ($check_for_existing_subscription->num_rows > 0) {
				echo 'nevermind, they already have it'."\n";
				continue;
			}
		} else { // if not -- add it as a new feed!
			echo 'new feed! how exciting.'."\n";
			$add_new_feed = $mysqli->query("INSERT INTO feeds (feed_url, feed_title, feed_homeurl, tsc, tsu) VALUES ($feed_url_db, $feed_title_db, $feed_homeurl_db, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
			if (!$add_new_feed) { die('mysql error on add new feed: '.$mysqli->error); }
			$feed_id = $mysqli->insert_id;
		}
		
		$add_new_user_feed = $mysqli->query("INSERT INTO users_feeds (user_id, feed_id, tsc) VALUES ($current_user_id, $feed_id, UNIX_TIMESTAMP())");
		if (!$add_new_user_feed) { die('mysql error on add new user feed row: '.$mysqli->error); }
		
		echo 'users_feeds record added'."\n";
		
	}
	
	echo 'ALL DONE!'."\n";
	
	echo '</pre>';
	
} else {
	die('error: malformed OPML file, or something like that');
}

unlink($file_handle['tmp_name']);

header('Location: /feeds/');

?>