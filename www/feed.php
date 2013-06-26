<?php

$current_user_id = 1;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	die('Sorry, no feed ID given.');
}

if (isset($_GET['read']) && trim($_GET['read']) == 'yup') {
	$just_unread = false;
} else {
	$just_unread = true;
}

$selected_feed_id = (int) $_GET['id'] * 1;

// get posts from feed in pubdate order

require_once('../www-includes/dbconn_mysql.php');

require_once('../www-includes/functions.php');

// get list of user's feeds
$users_feeds = getUsersFeeds($current_user_id);

require_once('head.php'); 
?>
<body id="feed">
<?php require_once('header.php'); ?>
<?php require_once('sidebar.php'); ?>
<div id="main-column" class="feed-list">
<?php

if ($users_feeds == false || count($users_feeds) == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	?>
	<p class="mark-all-as-read"><a href="/read/feed/<?php echo $selected_feed_id; ?>/">Mark Every Single Goddamn Post in This Feed as <b>Read</b></a></p>
	<?php
	$all_posts = getFeedPosts($current_user_id, $selected_feed_id, $just_unread);
	if (count($all_posts) == 0) {
		echo '<p>No posts to show you, sorry.</p>';
	} else {
		foreach ($all_posts as $post) {
			postBit($post, $users_feeds);
		}
		echo '<div class="nav-next"><a href="/feed/'.$selected_feed_id.'/'.(($just_unread == false) ? 'all/' : '').'more/2/25/">load more</a></div>'."\n";
	}
	
}

?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>