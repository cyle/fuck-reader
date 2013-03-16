<?php

// go through all of the feeds, collect the latest shit, and put it into the database

$debug = false;

if ($debug) { echo '<pre>'; }

require_once('dbconn_mysql.php');

$get_feeds = $mysqli->query('SELECT * FROM feeds ORDER BY feed_id ASC');

while ($feed = $get_feeds->fetch_assoc()) {
	
	$feed_id = (int) $feed['feed_id'] * 1;
	
	$ch = curl_init($feed['feed_url']);
	
	if ($debug) { echo 'getting '.$feed['feed_url']."\n"; }
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	curl_setopt($ch, CURLOPT_USERAGENT, 'CYLESOFT FUCKREADER LOLOLOLOL');
	
	$feed_xml_string = curl_exec($ch);
	curl_close($ch);
	
	if ($feed_xml_string == false) {
		echo 'error getting xml on feed #'.$feed_id.' - '.$feed['feed_title'].' - '.$feed['feed_url']."\n";
		continue;
	}
	
	error_reporting(0);
	$feed_xml = simplexml_load_string($feed_xml_string);
	error_reporting(1);
	
	//var_dump($feed_xml->channel);
	
	if ($feed_xml == false) {
		
		echo 'error parsing xml on feed #'.$feed_id.' - '.$feed['feed_title'].' - '.$feed['feed_url']."\n";
		continue;
		
	} else if (isset($feed_xml->entry)) { // atom feed - http://en.wikipedia.org/wiki/Atom_(standard)
		
		//echo 'atom feed'."\n";
		
		$feed_title = trim($feed_xml->title);
				
		foreach ($feed_xml->entry as $feed_item) {
			
			$post_title = (string) $feed_item->title;
			$post_url = (string) $feed_item->link[0]['href'];
			$post_guid = (string) $feed_item->id;
			if (isset($feed_item->updated)) {
				$post_published = strtotime($feed_item->updated);
			} else if (isset($feed_item->published)) {
				$post_published = strtotime($feed_item->published);
			} else {
				$post_published = time();
			}
			if ($post_published > time()) {
				$post_published = time();
			}
			$post_author = (string) $feed_item->author->name;
			if (isset($feed_item->content)) {
				$post_content = (string) $feed_item->content;
			} else {
				$post_content = (string) $feed_item->summary;
			}
			
			if ($debug) {
				echo $post_title."\n";
				echo $post_url."\n";
				echo $post_guid."\n";
				echo $post_published."\n";
				echo $post_author."\n";
				echo htmlentities($post_content)."\n";
			}
			
			$post_content_chksum = sha1($post_content);
			
			// if this checksum does not exist, add the post
			$check_for_post = $mysqli->query("SELECT post_id FROM posts WHERE feed_id=$feed_id AND chksum='$post_content_chksum'");
			if ($check_for_post->num_rows == 0) {
				// add the post
				$post_title_db = "'".trim($mysqli->escape_string($post_title))."'";
				$post_url_db = "'".trim($mysqli->escape_string($post_url))."'";
				$post_guid_db = "'".trim($mysqli->escape_string($post_guid))."'";
				$post_published_db = "'".trim($mysqli->escape_string($post_published))."'";
				$post_author_db = "'".trim($mysqli->escape_string($post_author))."'";
				$post_content_db = "'".trim($mysqli->escape_string($post_content))."'";
				
				$add_post = $mysqli->query("INSERT INTO posts (feed_id, post_title, post_guid, post_permalink, post_content, post_byline, post_pubdate, ts, chksum) VALUES ($feed_id, $post_title_db, $post_guid_db, $post_url_db, $post_content_db, $post_author_db, $post_published_db, UNIX_TIMESTAMP(), '$post_content_chksum')");
				if (!$add_post) {
					die('mysql error: '.$mysqli->error);
				}
			}
			
		}
		
	} else if (isset($feed_xml->channel) && isset($feed_xml->channel->item)) { // rss feed - http://www.rssboard.org/rss-specification
		
		//echo 'rss feed'."\n";
		
		$feed_title = trim($feed_xml->channel->title);
				
		foreach ($feed_xml->channel->item as $feed_item) {
			
			$post_title = (string) $feed_item->title;
			$post_url = (string) $feed_item->link;
			if (isset($feed_item->guid)) {
				$post_guid = (string) $feed_item->guid;
			} else {
				$post_guid = (string) $feed_item->link;
			}
			$post_published = strtotime($feed_item->pubDate);
			if ($post_published > time()) {
				$post_published = time();
			}
			if (isset($feed_item->author)) {
				$post_author = (string) $feed_item->author;
			} else {
				$post_author = null;
			}
			
			$post_content = (string) $feed_item->description;
			
			if ($debug) {
				echo $post_title."\n";
				echo $post_url."\n";
				echo $post_guid."\n";
				echo $post_published."\n";
				echo $post_author."\n";
				echo htmlentities($post_content)."\n";
			}
			
			$post_content_chksum = sha1($post_content);
			
			// if this checksum does not exist, add the post
			$check_for_post = $mysqli->query("SELECT post_id FROM posts WHERE feed_id=$feed_id AND chksum='$post_content_chksum'");
			if ($check_for_post->num_rows == 0) {
				// add the post
				$post_title_db = "'".trim($mysqli->escape_string($post_title))."'";
				$post_url_db = "'".trim($mysqli->escape_string($post_url))."'";
				$post_guid_db = "'".trim($mysqli->escape_string($post_guid))."'";
				$post_published_db = "'".trim($mysqli->escape_string($post_published))."'";
				$post_author_db = "'".trim($mysqli->escape_string($post_author))."'";
				$post_content_db = "'".trim($mysqli->escape_string($post_content))."'";
				
				$add_post = $mysqli->query("INSERT INTO posts (feed_id, post_title, post_guid, post_permalink, post_content, post_byline, post_pubdate, ts, chksum) VALUES ($feed_id, $post_title_db, $post_guid_db, $post_url_db, $post_content_db, $post_author_db, $post_published_db, UNIX_TIMESTAMP(), '$post_content_chksum')");
				if (!$add_post) {
					die('mysql error: '.$mysqli->error);
				}
			}
		}
		
	}
	
	if (!isset($feed['feed_title'])) {
		$update_feed_title = $mysqli->query("UPDATE feeds SET feed_title='".$mysqli->escape_string(strip_tags($feed_title))."' WHERE feed_id=$feed_id");
		if (!$update_feed_title) {
			die('could not update feed title');
		}
	}
	
	$update_feed_tsu = $mysqli->query("UPDATE feeds SET tsu=UNIX_TIMESTAMP() WHERE feed_id=$feed_id");
	if (!$update_feed_tsu) {
		die('could not update feed last updated');
	}
				
}

if ($debug) { echo '</pre>'; }

?>