<?php

$current_user_id = 1;

require_once('../www-includes/dbconn_mysql.php');

$this_page = 'subscriptions';

require_once('head.php');
?>
<body id="subs-page">
<?php require_once('header.php'); ?>
<?php require_once('mini_sidebar.php'); ?>
<div id="main-column">

	<h3>Subscriptions!</h3>
	
	<p>Add a new feed: <input type="url" /></p>
	
	<form action="/subs/process/" method="post">
	<table>
	<tr><th></th><th>Name</th><th>URL</th><th>Options</th></tr>
	<?php
	
	$get_feeds = $mysqli->query('SELECT * FROM feeds WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$current_user_id.') ORDER BY feed_title ASC, feed_id ASC');
	
	while ($feed = $get_feeds->fetch_assoc()) {
		echo '<tr>';
		echo '<td><input type="checkbox" value="'.$feed['feed_id'].'" name="feed-id[]" /></td>';
		echo '<td>'.$feed['feed_title'].'</td>';
		echo '<td>'.$feed['feed_homeurl'].'</td>';
		echo '<td><a href="#">delete</a> / <a href="#">mark all unread</a></td>';
		echo '</tr>'."\n";
	}
	
	?>
	</table>
	
	<p>With selected: <select name="a"><option value="0">Please select...</option><option value="d">Delete</option><option value="u">Mark all unread</option></select> <input type="submit" value="Do it &raquo;" /></p>
	
	</form>

</div>
<?php require_once('footer.php'); ?>
</body>
</html>