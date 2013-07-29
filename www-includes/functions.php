<?php

// the necessary functions

$necessary_posts_columns = 'posts.post_id, posts.feed_id, posts.post_title, posts.post_guid, posts.post_permalink, posts.post_byline, posts.post_pubdate, posts.ts';

require_once('dbconn_mysql.php');
require_once('dbconn_redis.php');

function getAllUnreadCount($user_id = 0, $use_redis = false) {
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
		
	if ($use_redis) {
		global $redis;
		$unread_count = $redis->get('counts:'.$user_id.':all:unread');
		return $unread_count * 1;
	} else {
		global $mysqli;
		$get_count = $mysqli->query('SELECT count(post_id) AS postcount FROM posts WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.')');
		$count_result = $get_count->fetch_assoc();
		return $count_result['postcount'] * 1;
	}
	
}

function getFeedUnreadCount($user_id = 0, $feed_id = 0, $use_redis = false) {
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	if (!isset($feed_id) || $feed_id == 0 || !is_numeric($feed_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$feed_id = (int) $feed_id * 1;
		
	if ($use_redis) {
		global $redis;
		$unread_count = $redis->get('counts:'.$user_id.':'.$feed_id.':unread');
		return $unread_count * 1;
	} else {
		global $mysqli;
		$get_count = $mysqli->query('SELECT count(post_id) AS postcount FROM posts WHERE feed_id='.$feed_id.' AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.')');
		$count_result = $get_count->fetch_assoc();
		return $count_result['postcount'] * 1;
	}
	
}

function getDateAllUnreadCount($user_id = 0, $the_date = null, $use_redis = false) {
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	if (!isset($the_date) || trim($the_date) == '') {
		return false;
	}
	
	if (!is_numeric($the_date)) {
		$the_date = strtotime($the_date);
	}
	
	if (!$the_date) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	
	$the_date_base = date('Y-m-d', $the_date);
	$the_date_start = $the_date_base . ' 12:00:00 AM';
	$the_date_end = $the_date_base . ' 11:59:59 PM';
	$the_date_start_db = strtotime($the_date_start);
	$the_date_end_db = strtotime($the_date_end);
	
	global $mysqli, $redis;
	
	if ($use_redis) {
		$users_read_posts = $redis->smembers('postsread:'.$user_id);
		$already_read_posts = implode(',', $users_read_posts);
	} else {
		$already_read_posts = 'SELECT post_id FROM users_read_posts WHERE user_id='.$user_id;
	}
	
	$get_count = $mysqli->query('SELECT count(post_id) AS postcount FROM posts WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN ('.$already_read_posts.') AND post_pubdate >= '.$the_date_start_db.' AND post_pubdate < '.$the_date_end_db.'');
	$count_result = $get_count->fetch_assoc();
	
	return $count_result['postcount'];
	
}

function getOldestUnreadPost($user_id = 0, $use_redis = true) {
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	
	global $mysqli, $redis;
	
	if ($use_redis) {
		$users_read_posts = $redis->smembers('postsread:'.$user_id);
		$already_read_posts = implode(',', $users_read_posts);
	} else {
		$already_read_posts = 'SELECT post_id FROM users_read_posts WHERE user_id='.$user_id;
	}
	
	$get_oldest_unread = $mysqli->query('SELECT post_pubdate FROM posts WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN ('.$already_read_posts.') ORDER BY post_pubdate ASC LIMIT 1');
	if ($get_oldest_unread->num_rows == 0) {
		return 0;
	} else {
		$oldest_unread_result = $get_oldest_unread->fetch_assoc();
		return $oldest_unread_result['post_pubdate'];
	}
	
}

function getUsersFeeds($user_id = 0) {
	// get feed IDs for user, return array
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$users_feeds = array();
	
	global $mysqli;
	
	$get_feeds = $mysqli->query('SELECT feed_id, feed_title FROM feeds WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') ORDER BY feed_title ASC, feed_id ASC');
	
	if ($get_feeds->num_rows == 0) {
		return array();
	} else {
		while ($feed_row = $get_feeds->fetch_assoc()) {
			$users_feeds[$feed_row['feed_id']] = $feed_row['feed_title'];
		}
		return $users_feeds;
	}
	
}

function getStarredPosts($user_id = 0, $howmany = 25, $offset = 0, $use_redis = false) {
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$feed_posts = array();
	
	global $mysqli, $redis, $necessary_posts_columns;
	
	if ($use_redis) {
		$users_read_posts = $redis->smembers('postsread:'.$user_id);
		foreach ($users_read_posts as &$read_post_id) {
			$read_post_id = $read_post_id * 1;
		}
		unset($read_post_id);
		$users_star_posts = $redis->smembers('stars:'.$user_id);
		$already_star_posts = implode(',', $users_star_posts);
		$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts 
			WHERE posts.post_id IN ('.$already_star_posts.') 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				$post['starred'] = true;
				if (in_array($post['post_id'], $users_read_posts)) {
					$post['is_read'] = true;
				}
				$feed_posts[] = $post;
			}
		}
	} else {
		$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_read_posts.row_id AS is_read FROM posts 
			LEFT JOIN users_read_posts ON users_read_posts.post_id=posts.post_id AND users_read_posts.user_id='.$user_id.' 
			WHERE posts.post_id IN (SELECT post_id FROM users_star_posts WHERE user_id='.$user_id.') 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				$post['starred'] = true;
				$feed_posts[] = $post;
			}
		}
	}
	
	return $feed_posts;
	
}

function getFeedPosts($user_id = 0, $feed_id = 0, $just_unread = true, $howmany = 25, $offset = 0, $use_redis = false) {
	// get a feed's posts for a user, possibly get all of them
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	if (!isset($feed_id) || $feed_id == 0 || !is_numeric($feed_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$feed_id = (int) $feed_id * 1;
	$feed_posts = array();
	
	global $mysqli, $redis, $necessary_posts_columns;
	
	if ($use_redis) {
		$users_read_posts = $redis->smembers('postsread:'.$user_id);
		foreach ($users_read_posts as &$read_post_id) {
			$read_post_id = $read_post_id * 1;
		}
		unset($read_post_id);
		$users_star_posts = $redis->smembers('stars:'.$user_id);
		foreach ($users_star_posts as &$star_post_id) {
			$star_post_id = $star_post_id * 1;
		}
		unset($star_post_id);
		if ($just_unread == true) {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts 
			WHERE posts.feed_id='.$feed_id.' AND posts.post_id NOT IN ('.implode(',', $users_read_posts).') 
			GROUP BY post_id
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		} else {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts 
			WHERE posts.feed_id='.$feed_id.' 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		}
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				if ($just_unread) {
					$post['is_read'] = true;
				} else if ($just_unread == false && in_array($post['post_id'], $users_read_posts)) {
					$post['is_read'] = true;
				}
				if (in_array($post['post_id'], $users_star_posts)) {
					$post['starred'] = true;
				}
				$feed_posts[] = $post;
			}
			return $feed_posts;
		}
	} else {
		if ($just_unread == true) {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_star_posts.row_id AS starred 
			FROM posts 
			LEFT JOIN users_star_posts ON users_star_posts.post_id=posts.post_id AND users_star_posts.user_id='.$user_id.' 
			WHERE posts.feed_id='.$feed_id.' AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.') 
			GROUP BY post_id
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		} else {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_read_posts.row_id AS is_read, users_star_posts.row_id AS starred 
			FROM posts 
			LEFT JOIN users_read_posts ON users_read_posts.post_id=posts.post_id AND users_read_posts.user_id='.$user_id.' 
			LEFT JOIN users_star_posts ON users_star_posts.post_id=posts.post_id AND users_star_posts.user_id='.$user_id.' 
			WHERE posts.feed_id='.$feed_id.' 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		}
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				$feed_posts[] = $post;
			}
			return $feed_posts;
		}
	}
	
}

function getAllPosts($user_id = 0, $just_unread = true, $howmany = 25, $offset = 0, $use_redis = false) {
	// get all feeds' posts for user, possibly get all of them
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	$feeds_posts = array();
	
	global $mysqli, $redis, $necessary_posts_columns;
	
	if ($use_redis) {
		
		$users_read_posts = $redis->smembers('postsread:'.$user_id);
		foreach ($users_read_posts as &$read_post_id) {
			$read_post_id = $read_post_id * 1;
		}
		unset($read_post_id);
		$users_star_posts = $redis->smembers('stars:'.$user_id);
		foreach ($users_star_posts as &$star_post_id) {
			$star_post_id = $star_post_id * 1;
		}
		unset($star_post_id);
		
		if ($just_unread == true) {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts 
			WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN ('.implode(',', $users_read_posts).') 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		} else {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts
			WHERE posts.feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.')
			GROUP BY post_id
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		}
		
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				if ($just_unread) {
					$post['is_read'] = true;
				} else if ($just_unread == false && in_array($post['post_id'], $users_read_posts)) {
					$post['is_read'] = true;
				}
				if (in_array($post['post_id'], $users_star_posts)) {
					$post['starred'] = true;
				}
				$feed_posts[] = $post;
			}
			return $feed_posts;
		}
		
	} else {
		if ($just_unread == true) {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_star_posts.row_id AS starred 
			FROM posts 
			LEFT JOIN users_star_posts ON users_star_posts.post_id=posts.post_id AND users_star_posts.user_id='.$user_id.' 
			WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.') 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		} else {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_read_posts.row_id AS is_read, users_star_posts.row_id AS starred 
			FROM posts
			LEFT JOIN users_read_posts ON users_read_posts.post_id=posts.post_id AND users_read_posts.user_id='.$user_id.' 
			LEFT JOIN users_star_posts ON users_star_posts.post_id=posts.post_id AND users_star_posts.user_id='.$user_id.' 
			WHERE posts.feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.')
			GROUP BY post_id
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		}
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				$feeds_posts[] = $post;
			}
			return $feeds_posts;
		}
	}
}

function getAllPostsByDate($user_id = 0, $the_date = null, $just_unread = true, $howmany = 25, $offset = 0, $use_redis = false) {
	
	if (!isset($user_id) || $user_id == 0 || !is_numeric($user_id)) {
		return false;
	}
	
	if (!isset($the_date) || trim($the_date) == '') {
		return false;
	}
	
	if (!is_numeric($the_date)) {
		$the_date = strtotime($the_date);
	}
	
	if (!$the_date) {
		return false;
	}
	
	$user_id = (int) $user_id * 1;
	
	$the_date_base = date('Y-m-d', $the_date);
	$the_date_start = $the_date_base . ' 12:00:00 AM';
	$the_date_end = $the_date_base . ' 11:59:59 PM';
	$the_date_start_db = strtotime($the_date_start);
	$the_date_end_db = strtotime($the_date_end);
	
	$feeds_posts = array();
	
	global $mysqli, $redis, $necessary_posts_columns;
	
	if ($use_redis) {
		$users_read_posts = $redis->smembers('postsread:'.$user_id);
		foreach ($users_read_posts as &$read_post_id) {
			$read_post_id = $read_post_id * 1;
		}
		unset($read_post_id);
		$users_star_posts = $redis->smembers('stars:'.$user_id);
		foreach ($users_star_posts as &$star_post_id) {
			$star_post_id = $star_post_id * 1;
		}
		unset($star_post_id);
		if ($just_unread == true) {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts 
			WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN ('.implode(',', $users_read_posts).') 
			AND posts.post_pubdate >= '.$the_date_start_db.' AND posts.post_pubdate < '.$the_date_end_db.' 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		} else {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.'
			FROM posts
			WHERE posts.feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') 
			AND posts.post_pubdate >= '.$the_date_start_db.' AND posts.post_pubdate < '.$the_date_end_db.' 
			GROUP BY post_id
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		}
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				if ($just_unread) {
					$post['is_read'] = true;
				} else if ($just_unread == false && in_array($post['post_id'], $users_read_posts)) {
					$post['is_read'] = true;
				}
				if (in_array($post['post_id'], $users_star_posts)) {
					$post['starred'] = true;
				}
				$feed_posts[] = $post;
			}
			return $feed_posts;
		}
	} else {
		if ($just_unread == true) {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_star_posts.row_id AS starred 
			FROM posts 
			LEFT JOIN users_star_posts ON users_star_posts.post_id=posts.post_id AND users_star_posts.user_id='.$user_id.' 
			WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') AND posts.post_id NOT IN (SELECT post_id FROM users_read_posts WHERE user_id='.$user_id.') 
			AND posts.post_pubdate >= '.$the_date_start_db.' AND posts.post_pubdate < '.$the_date_end_db.' 
			GROUP BY post_id 
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		} else {
			$get_posts = $mysqli->query('SELECT '.$necessary_posts_columns.', users_read_posts.row_id AS is_read, users_star_posts.row_id AS starred 
			FROM posts
			LEFT JOIN users_read_posts ON users_read_posts.post_id=posts.post_id AND users_read_posts.user_id='.$user_id.' 
			LEFT JOIN users_star_posts ON users_star_posts.post_id=posts.post_id AND users_star_posts.user_id='.$user_id.' 
			WHERE posts.feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$user_id.') 
			AND posts.post_pubdate >= '.$the_date_start_db.' AND posts.post_pubdate < '.$the_date_end_db.' 
			GROUP BY post_id
			ORDER BY posts.post_pubdate DESC 
			LIMIT '.$howmany.' OFFSET '.$offset);
		}
		if ($get_posts->num_rows == 0) {
			return array();
		} else {
			while ($post = $get_posts->fetch_assoc()) {
				$feeds_posts[] = $post;
			}
			return $feeds_posts;
		}
	}
}

function postBit($post = array(), $users_feeds = array()) {
	// return post item for feed
	echo '<div class="post'.((isset($post['is_read'])) ? ' read': ' unread').''.((isset($post['starred'])) ? ' starred': '').'" id="post-'.$post['post_id'].'" data-feed-id="'.$post['feed_id'].'" data-post-id="'.$post['post_id'].'">'."\n";
	echo '<h3 class="post-header"><span class="star-this-post" title="star/unstar this post">&#10029;</span> <span class="mark-this-post" title="mark as read/unread">&#10004;</span> <a class="post-title" href="'.$post['post_permalink'].'" target="_blank">'.(($post['post_title'] != null) ? htmlspecialchars(strip_tags($post['post_title']), ENT_NOQUOTES, 'UTF-8') : 'Untitled').'</a></h3>'."\n";
	echo '<div class="post-byline">Published <span class="post-pubdate">'.date('F jS, Y g:iA', $post['post_pubdate']).'</span> on <span class="post-feed-source">'.$users_feeds[$post['feed_id']].'</span></div>'."\n";
	echo '<div class="post-content" style="display:none;" id="post-content-'.$post['post_id'].'">'."\n";
	// post content used to go here!
	echo '</div>'."\n";
	echo '</div>'."\n";
}

function getFeedTitle($feed_id = 0) {
	if (!isset($feed_id) || $feed_id == 0 || !is_numeric($feed_id)) {
		return false;
	}
	
	$feed_id = (int) $feed_id * 1;
	
	global $mysqli;
	
	$get_feed_title = $mysqli->query("SELECT feed_title FROM feeds WHERE feed_id=$feed_id");
	if (!$get_feed_title || $get_feed_title->num_rows == 0) {
		return false;
	} else {
		$feed_title_result = $get_feed_title->fetch_assoc();
		return $feed_title_result['feed_title'];
	}
}

?>