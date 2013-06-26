<?php

$login_required = true;
require_once('../www-includes/login_check.php');

require_once('../www-includes/dbconn_mysql.php');

$this_page = 'settings';

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