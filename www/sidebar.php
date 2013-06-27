<div id="sidebar">
<?php
if (!isset($users_feeds) || $users_feeds == false || count($users_feeds) == 0) {
	?>
	<ul>
	<li>No feeds to show you! Add one!</li>
	<li><a href="/subs/">Manage Subscriptions</a></li>
	<li><a href="/settings/">Change Your Settings</a></li>
	<li><a href="/help/">Help</a></li>
	</ul>
	<?php
} else {
	?>
	<ul>
	<li><a href="/feeds/"<?php echo ((isset($just_unread) && $just_unread == true && !isset($selected_feed_id)) ? ' class="active"' : ''); ?>>Show All <span id="unread-feeds-count"><?php echo getAllUnreadCount($current_user_id); ?></span> Unread Posts</a></li>
	<li><a href="/feeds/all/"<?php echo ((isset($just_unread) && $just_unread == false && !isset($selected_feed_id)) ? ' class="active"': ''); ?>>Show All Posts, even Read</a></li>
	<li><a href="/starred/">Show Only Starred Posts</a></li>
	<li>Your Feeds:</li>
	<ul>
	<?php
	foreach ($users_feeds as $feed_id => $feed_title) {
		$unread_count = getFeedUnreadCount($current_user_id, $feed_id);
		echo '<li>';
		//echo '['.$unread_count.'] <a href="/feed/'.$feed_id.'/" '.((isset($selected_feed_id) && $feed_id == $selected_feed_id) ? 'class="active"': '').'>'.$feed_title.'</a> (<a href="/feed/'.$feed_id.'/all/" '.((isset($selected_feed_id) && $feed_id == $selected_feed_id && isset($just_unread) && $just_unread == false) ? 'class="active"': '').'>All Items</a>)';
		echo '['.((isset($selected_feed_id) && $feed_id == $selected_feed_id) ? '<span id="unread-feed-count">' : '').$unread_count.((isset($selected_feed_id) && $feed_id == $selected_feed_id) ? '</span>' : '').'] <a href="/feed/'.$feed_id.'/" '.((isset($selected_feed_id) && $feed_id == $selected_feed_id) ? 'class="active"': '').'>'.$feed_title.'</a>';
		echo '</li>'."\n";
	}
	?>
	</ul>
	<li><a href="/subs/">Manage Subscriptions</a></li>
	<li><a href="/settings/">Change Your Settings</a></li>
	<li><a href="/help/">Help</a></li>
	</ul>
	<?php
}
?>
</div>