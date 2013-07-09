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
	<li><a href="/feeds/"<?php echo ((!isset($the_date) && isset($just_unread) && $just_unread == true && !isset($selected_feed_id)) ? ' class="active"' : ''); ?>>Show All <span id="unread-feeds-count"><?php echo getAllUnreadCount($current_user_id); ?></span> Unread Posts</a></li>
	<li><a href="/feeds/all/"<?php echo ((isset($just_unread) && $just_unread == false && !isset($selected_feed_id)) ? ' class="active"': ''); ?>>Show All Posts, even Read</a></li>
	<li><a href="/starred/">Show Only Starred Posts</a></li>
	<li>Your Feeds By Day:</li>
	<ul>
		<?php
		$oldest_date = getOldestUnreadPost($current_user_id);
		if ($oldest_date > 0) {
			$first_day_ts = strtotime(date('Y-m-d', $oldest_date) . ' 12:00:00 AM');
			for ($current_ts = $first_day_ts; $current_ts < time(); $current_ts += 86400) {
				echo '<li>['.((isset($the_date) && date('Y-m-d', $the_date) == date('Y-m-d', $current_ts)) ? '<span id="unread-date-count">' : '').getDateAllUnreadCount($current_user_id, $current_ts).((isset($the_date) && date('Y-m-d', $the_date) == date('Y-m-d', $current_ts)) ? '</span>': '').'] <a href="/feeds/'.date('Y-m-d', $current_ts).'/"'.((isset($the_date) && date('Y-m-d', $the_date) == date('Y-m-d', $current_ts)) ? ' class="active"': '').'>'.date('m-d-Y', $current_ts).'</a></li>'."\n";
			}
		}
		?>
	</ul>
	<li>Your Feeds:</li>
	<ul id="list-of-feeds">
	<?php
	foreach ($users_feeds as $feed_id => $feed_title) {
		$unread_count = getFeedUnreadCount($current_user_id, $feed_id);
		echo '<li'.(($unread_count == 0) ? ' class="zero-feed"': '').'>';
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