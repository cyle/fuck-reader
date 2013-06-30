<?php

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

$page_title = 'settings';

require_once('head.php');
?>
<body id="settings-page">
<?php require_once('header.php'); ?>
<?php require_once('mini_sidebar.php'); ?>
<div id="main-column">

<div class="settings-section">
<h3>Import Feeds from Google Reader</h3>
<form action="/import_opml_process.php" method="post" enctype="multipart/form-data">
<p>Import Google Reader OPML File (subscriptions.xml): <input type="file" name="f" /> <input type="submit" value="upload &raquo;" /></p>
</form>
</div>

<div class="settings-section">
<h3>Defaults</h3>
<p>Not done yet, sorry.</p>
<!--
will have:
	default posts per page
	default posts sorting order
-->
</div>

<div class="settings-section">
<h3>Invite Codes</h3>
<p>Use these to invite more people to the site.</p>
<?php
$get_invites = $mysqli->query("SELECT * FROM user_invites WHERE owner_id=$current_user_id");
if ($get_invites->num_rows > 0) {
	?>
	<table class="fucked">
	<tr><th>Code</th><th>Already used?</th></tr>
	<?php
	while ($invite = $get_invites->fetch_assoc()) {
		echo '<tr><td>'.$invite['invite_code'].'</td><td>'.(($invite['is_used'] == 1) ? 'Yup' : 'Nope').'</td></tr>';
	}
	?>
	</table>
	<?php
} else {
	echo '<p>No invites to give you, sorry.</p>';
}
?>
</div>

<div class="settings-section">
<h3>Export Your Data</h3>
<p>Not done yet, sorry.</p>
</div>

<div class="settings-section">
<h3>Your Active Sessions</h3>
<?php
$get_active_sessions = $mysqli->query("SELECT session_id FROM user_sessions WHERE user_id=$current_user_id");
?>
<p>You currently have <?php echo $get_active_sessions->num_rows; ?> active sessions.</p>
<p><a href="/user/clear/sessions/">Click here to clear them all and force re-login.</a></p>
<p>Do this if you've lost track of where you're logged in.</p>
</div>

<div class="settings-section">
<h3>Change Your Password</h3>
<p>Note: this will invalidate all of your currently active sessions, forcing you to re-login.</p>
<form action="/user/change/password/" method="post" id="change-pwd-form">
<table>
<tr><td>Current password:</td><td><input type="password" name="cp" /></td></tr>
<tr><td>New password:</td><td><input type="password" name="p1" /></td></tr>
<tr><td>New password (again):</td><td><input type="password" name="p2" /></td></tr>
<tr><td colspan="2"><input type="submit" value="Change it &raquo;" /></td></tr>
</table>
</form>
</div>

<div class="settings-section">
<h3>Delete Fucking Everything</h3>
<p>Not done yet, sorry.</p>
<!--
will have:
	delete star data
	delete read data
	delete fucking everything, for serious, as if i never existed
-->
</div>

</div>
<?php require_once('footer.php'); ?>
</body>
</html>