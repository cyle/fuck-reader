<?php

$current_user_id = 1;

require_once('../www-includes/dbconn_mysql.php');

$this_page = 'settings';

require_once('head.php');
?>
<body id="feeds">
<?php require_once('header.php'); ?>
<div id="sidebar">
<ul>
<li><a href="/feeds/">Go back to feeds</a></li>
<li><a href="/settings/" class="active">Settings</a></li>
</ul>
</div>
<div id="main-column">
<form action="/import_opml_process.php" method="post" enctype="multipart/form-data">
<p>Import Google Reader OPML File: <input type="file" name="f" /> <input type="submit" value="upload &raquo;" /></p>
</form>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>