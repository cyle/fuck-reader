<?php

$current_user_id = 1;
$users_feeds_ids = array();
$users_feeds = array();

// get user's feeds

// get posts from feeds in pubdate order

require_once('../www-includes/dbconn_mysql.php');

require_once('head.php');
?>
<body id="feeds">
<?php require_once('header.php'); ?>
<div id="sidebar">
<?php
// get list of user's feeds
$get_feeds = $mysqli->query('SELECT * FROM feeds WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$current_user_id.') ORDER BY feed_title ASC, feed_id ASC');
if (!$get_feeds) { 
	die('mysql error: '.$mysqli->error);
} else if ($get_feeds->num_rows == 0) {
	?>
	<p>No feeds to show you! Add one!</p>
	<?php
} else {
	?>
	<ul>
	<li><a href="/feeds/" class="active">Show All Unread Items</a></li>
	<?php
	while ($feed_row = $get_feeds->fetch_assoc()) {
		echo '<li>';
		echo '<a href="/feed/'.$feed_row['feed_id'].'/">'.$feed_row['feed_title'].'</a>';
		echo '</li>'."\n";
		$users_feeds_ids[] = $feed_row['feed_id'];
		$users_feeds[$feed_row['feed_id']] = $feed_row['feed_title'];
	}
	?>
	</ul>
	<?php
}
?>
</div>
<div id="main-column">
<?php

if ($get_feeds->num_rows == 0) {
	?>
	<p>No feed posts to show you!</p>
	<?php
} else {
	$get_posts = $mysqli->query('SELECT * FROM posts WHERE feed_id IN ('.implode(',', $users_feeds_ids).') ORDER BY post_pubdate DESC LIMIT 25');
	while ($post = $get_posts->fetch_assoc()) {
		echo '<div class="post">';
		echo '<h3><a href="'.$post['post_permalink'].'" target="_blank">'.$post['post_title'].'</a></h3>';
		echo '<div class="post-byline">Published <span class="post-pubdate">'.date('F jS, Y g:iA', $post['post_pubdate']).'</span> on <span class="post-feed-source">'.$users_feeds[$post['feed_id']].'</span></div>';
		//echo '<div class="post-content">'.$post['post_content'].'</div>';
		echo '</div>';
	}
}

?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>