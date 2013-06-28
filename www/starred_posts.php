<?php

// get starred posts

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

require_once('../www-includes/functions.php');

// get list of user's feeds
$users_feeds = getUsersFeeds($current_user_id);

$page_title = 'starred';

require_once('head.php');
?>
<body id="starred">
<?php require_once('header.php'); ?>
<?php require_once('sidebar.php'); ?>
<div id="main-column">
<?php

if ($users_feeds == false || count($users_feeds) == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	$starred_posts = getStarredPosts($current_user_id);
	if (count($starred_posts) == 0) {
		echo '<p>No posts to show you, you do not have any starred posts.</p>';
	} else {
		echo '<div class="feed-list">';
		foreach ($starred_posts as $post) {
			postBit($post, $users_feeds);
		}
		echo '<div class="nav-next"><a href="/starred/more/2/25/">load more</a></div>'."\n";
		echo '</div>';
	}
}

?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>