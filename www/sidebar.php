<div id="sidebar">
<?php
if (!isset($users_feeds) || $users_feeds == false || count($users_feeds) == 0) {
	?>
	<ul>
	<li>No feeds to show you! Add one!</li>
	<li><a href="/settings/">Settings</a></li>
	</ul>
	<?php
} else {
	?>
	<ul>
	<li><a href="/feeds/"<?php echo ((isset($just_unread) && $just_unread == true && !isset($selected_feed_id)) ? ' class="active"' : ''); ?>>Show All Unread Posts</a></li>
	<li><a href="/feeds/all/"<?php echo ((isset($just_unread) && $just_unread == false && !isset($selected_feed_id)) ? ' class="active"': ''); ?>>Show All Posts, even Read</a></li>
	<li><a href="/starred/">Show My Starred Posts</a></li>
	<ul>
	<?php
	foreach ($users_feeds as $feed_id => $feed_title) {
		echo '<li>';
		echo '<a href="/feed/'.$feed_id.'/" '.((isset($selected_feed_id) && $feed_id == $selected_feed_id) ? 'class="active"': '').'>'.$feed_title.'</a> (<a href="/feed/'.$feed_id.'/all/" '.((isset($selected_feed_id) && $feed_id == $selected_feed_id && isset($just_unread) && $just_unread == false) ? 'class="active"': '').'>All Items</a>)';
		echo '</li>'."\n";
	}
	?>
	</ul>
	<li><a href="/settings/">Settings</a></li>
	</ul>
	<?php
}
?>
</div>