<?php

$login_required = true;
require_once('../www-includes/login_check.php');

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

$page_title = 'feed';

require_once('head.php'); 
?>
<body id="feed">
<?php require_once('header.php'); ?>
<?php require_once('sidebar.php'); ?>
<div id="main-column">
<?php

if ($users_feeds == false || count($users_feeds) == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	$all_posts = getFeedPosts($current_user_id, $selected_feed_id, $just_unread);
	?>
	<p class="feed-utils">
	<?php if ($just_unread) { ?><a href="/feed/<?php echo $selected_feed_id; ?>/all/">Show All Posts</a><?php } else { ?><a href="/feed/<?php echo $selected_feed_id; ?>/">Show Unread Posts</a><?php } ?>
	<?php if (count($all_posts) > 0 && $just_unread) { ?> - <a href="/read/feed/<?php echo $selected_feed_id; ?>/">Mark Every Single Goddamn Post in This Feed as <b>Read</b></a><?php } ?>
	</p>
	<?php
	if (count($all_posts) == 0) {
		echo '<p>No posts to show you, you read them all.</p>';
	} else {
		echo '<div class="feed-list">';
		foreach ($all_posts as $post) {
			postBit($post, $users_feeds);
		}
		echo '<div class="nav-next"><a href="/feed/'.$selected_feed_id.'/'.(($just_unread == false) ? 'all/' : '').'more/2/25/">load more</a></div>'."\n";
		echo '</div>';
	}
	
}

?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>