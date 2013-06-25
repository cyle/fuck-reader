<?php

// load more posts

$current_user_id = 1;

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
	$all_posts = getAllPosts($current_user_id, $just_unread, $howmany, $offset);
	foreach ($all_posts as $post) {
		postBit($post, $users_feeds);
	}
	echo '<div class="nav-next"><a href="/feeds/more/'.($page+1).'/'.$howmany.'/">load more</a></div>'."\n";
}

?>