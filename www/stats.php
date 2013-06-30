<?php

// show some stats

/*

show:

	- total number of feeds
	- total number of posts
	- average number of posts-per-feed
	- number of starred items

*/

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

$page_title = 'stats';

require_once('head.php');
?>
<body id="template">
<?php require_once('header.php'); ?>
<?php require_once('mini_sidebar.php'); ?>
<div id="main-column">

<h3>Fuck Reader Stats</h3>

<table class="fucked">
<tr><td style="text-align:right;">Total number of feeds:</td><td>
<?php
$get_total_feeds = $mysqli->query('SELECT count(feed_id) AS feed_count FROM feeds');
$total_feeds_result = $get_total_feeds->fetch_assoc();
echo $total_feeds_result['feed_count'];
?>
</td></tr>
<tr><td style="text-align:right;">Total number of posts:</td><td>
<?php
$get_total_posts = $mysqli->query('SELECT count(post_id) AS post_count FROM posts');
$total_posts_result = $get_total_posts->fetch_assoc();
echo $total_posts_result['post_count'];
?>
</td></tr>
<tr><td style="text-align:right;">Average posts per feed:</td><td>
<?php
echo number_format(($total_posts_result['post_count']/$total_feeds_result['feed_count']), 2);
?>
</td></tr>
<tr><td style="text-align:right;">Number of starred items:</td><td>
<?php
$get_total_stars = $mysqli->query('SELECT count(row_id) AS star_count FROM users_star_posts');
$total_stars_result = $get_total_stars->fetch_assoc();
echo $total_stars_result['star_count'];
?>
</td></tr>
</table>

</div>
<?php require_once('footer.php'); ?>
</body>
</html>