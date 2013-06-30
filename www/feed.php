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

$current_page = 1;
$posts_per_page = 25;
$posts_offset = ($current_page - 1) * $posts_per_page;

// get list of user's feeds
$users_feeds = getUsersFeeds($current_user_id);

$page_title = 'feed';

require_once('head.php'); 
?>
<body id="feed" class="mobile-hide-sidebar">
<?php require_once('header.php'); ?>
<?php require_once('sidebar.php'); ?>
<div id="main-column">
<?php

if ($users_feeds == false || count($users_feeds) == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	$all_posts = getFeedPosts($current_user_id, $selected_feed_id, $just_unread, $posts_per_page, $posts_offset);
	?>
	<p class="feed-utils">
	<span class="mobile-feed-list-toggle"><a href="#" class="toggle-feed-list">Show Feed List</a><br /></span>
	<span class="feed-title"><?php echo getFeedTitle($selected_feed_id); ?></span> - 
	<?php if ($just_unread) { ?><a href="/feed/<?php echo $selected_feed_id; ?>/all/">Show All Posts</a><?php } else { ?><a href="/feed/<?php echo $selected_feed_id; ?>/">Show Unread Posts</a><?php } ?>
	<?php if (count($all_posts) > 0 && $just_unread) { ?> - <a href="/read/feed/<?php echo $selected_feed_id; ?>/<?php echo time(); ?>/">Mark Every Single Goddamn Post in This Feed as <b>Read</b></a><?php } ?>
	</p>
	<?php
	if (count($all_posts) == 0) {
		echo '<p>No posts to show you, you read them all.</p>';
	} else {
		echo '<div class="feed-list">';
		foreach ($all_posts as $post) {
			postBit($post, $users_feeds);
		}
		if (count($all_posts) >= $posts_per_page) {
			echo '<div><a class="nav-next" href="/feed/'.$selected_feed_id.'/'.(($just_unread == false) ? 'all/' : '').'more/'.($current_page+1).'/'.$posts_per_page.'/">load more</a></div>'."\n";
		}
		echo '</div>';
	}
	
}

?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>