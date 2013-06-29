<?php

// load more posts

$login_required = true;
require_once('../www-includes/login_check.php');

if (isset($_GET['num']) && is_numeric($_GET['num'])) {
	$howmany = (int) $_GET['num'] * 1;
} else {
	die('dunno how many');
}

if (isset($_GET['page']) && is_numeric($_GET['page'])) {
	$page = (int) $_GET['page'] * 1;
} else {
	die('dunno what page');
}

if (isset($_GET['read']) && trim($_GET['read']) == 'yup') {
	$just_unread = false;
} else {
	$just_unread = true;
}

if (isset($_GET['starred']) && trim($_GET['starred']) == 'yup') {
	$just_starred = true;
} else {
	$just_starred = false;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$selected_feed_id = (int) $_GET['id'] * 1;
} else {
	$selected_feed_id = 0;
}

$offset = $howmany * ($page - 1);

require_once('../www-includes/dbconn_mysql.php');

require_once('../www-includes/functions.php');

// get list of user's feeds
$users_feeds = getUsersFeeds($current_user_id);

if ($users_feeds == false || count($users_feeds) == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	
	if ($just_starred) {
		$all_posts = getStarredPosts($current_user_id, $howmany, $offset);
	} else {
		if ($selected_feed_id > 0) {
			$all_posts = getFeedPosts($current_user_id, $selected_feed_id, $just_unread, $howmany, $offset);
		} else {
			$all_posts = getAllPosts($current_user_id, $just_unread, $howmany, $offset);
		}
	}
	
	if (count($all_posts) > 0) {
		foreach ($all_posts as $post) {
			postBit($post, $users_feeds);
		}
		if (count($all_posts) >= $howmany) {
			if ($selected_feed_id > 0) {
				echo '<div class="nav-next"><a href="/feed/'.$selected_feed_id.'/'.(($just_unread == false) ? 'all/' : '').'more/'.($page+1).'/'.$howmany.'/">load more</a></div>'."\n";
			} else {
				echo '<div><a class="nav-next" href="/feeds/'.(($just_unread == false) ? 'all/' : '').'more/'.($page+1).'/'.$howmany.'/">load more</a></div>'."\n";	
			}
		}
	}
}

?>