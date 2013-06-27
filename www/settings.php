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