<?php

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

$page_title = 'subscriptions';

require_once('head.php');
?>
<body id="subs-page">
<?php require_once('header.php'); ?>
<?php require_once('mini_sidebar.php'); ?>
<div id="main-column">

	<h3>Subscriptions!</h3>
		
	<?php if (isset($_GET['new_success'])) { ?><p class="success">Added new subscription successfully. It's a brand new feed, so it may take 5 minutes or so to sync up.</p><?php } ?>
	<?php if (isset($_GET['added_success'])) { ?><p class="success">Added new subscription successfully. It should show up in your feed list with unread posts immediately.</p><?php } ?>
	<?php if (isset($_GET['unread_success'])) { ?><p class="success">Marked selected feed(s) as unread.</p><?php } ?>
	<?php if (isset($_GET['delete_success'])) { ?><p class="success">Deleted selected feed(s) from your subscriptions.</p><?php } ?>
	
	<p>If you have a Google Reader OPML subscriptions.xml file you'd like to import, head over to <a href="/settings/">/settings/</a></p>
	
	<form action="/subs/process/" method="post">
	<p>Make sure you're adding the actual link to the site's <i>feed</i>, btw. Not just a link to a site you like.</p>
	<p>This accepts standard RSS 1.0, 1.1, 2.0, and Atom feeds.</p>
	<p>Add a new feed: <input type="hidden" name="a" value="n" /><input type="url" name="feed" /> <input type="submit" value="add &raquo;" /></p>
	</form>
	
	
	<?php
	
	$get_feeds = $mysqli->query('SELECT * FROM feeds WHERE feed_id IN (SELECT feed_id FROM users_feeds WHERE user_id='.$current_user_id.') ORDER BY feed_title ASC, feed_id ASC');
	
	if ($get_feeds->num_rows > 0) {
		?>
	<form action="/subs/process/" method="post">
	<table>
	<tr><th></th><th>Name</th><th>URL</th><th>Options</th></tr>
	<?php
		while ($feed = $get_feeds->fetch_assoc()) {
			echo '<tr>';
			echo '<td><input type="checkbox" value="'.$feed['feed_id'].'" name="feed-id[]" /></td>';
			echo '<td>'.$feed['feed_title'].'</td>';
			echo '<td>'.$feed['feed_homeurl'].'</td>';
			echo '<td><a href="/subs/process/d/'.$feed['feed_id'].'/">delete</a> / <a href="/subs/process/u/'.$feed['feed_id'].'/">mark all unread</a></td>';
			echo '</tr>'."\n";
		}
	?>
	</table>
	<p>With selected: <select name="a"><option value="0">Please select...</option><option value="d">Delete</option><option value="u">Mark all unread</option></select> <input type="submit" value="Do it &raquo;" /></p>
	</form>
	<?php
	} // end if you even have feeds
	?>

</div>
<?php require_once('footer.php'); ?>
</body>
</html>