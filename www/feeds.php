<?php

$current_user_id = 1;

require_once('../www-includes/dbconn_mysql.php');

require_once('../www-includes/functions.php');

if (isset($_GET['read']) && trim($_GET['read']) == 'yup') {
	$just_unread = false;
} else {
	$just_unread = true;
}

// get list of user's feeds
$users_feeds = getUsersFeeds($current_user_id);

require_once('head.php');
?>
<body id="feeds">
<?php require_once('header.php'); ?>
<?php require_once('sidebar.php'); ?>
<div id="main-column">
<?php

if ($users_feeds == false || count($users_feeds) == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	$all_posts = getAllPosts($current_user_id, $just_unread);
	foreach ($all_posts as $post) {
		postBit($post, $users_feeds);
	}
}

?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>