<?php

// login page if they are not already logged in

require_once('../www-includes/login_check.php');

// otherwise, wtf? send them to /feeds/
if (isset($current_user) && isset($current_user['loggedin']) && $current_user['loggedin'] == true) {
	header('Location: /feeds/');
	die();
}

require_once('head.php');
?>
<body id="login" onload="document.getElementById('start-here').focus()">
<div id="login-prompt">
<h1>fuck reader</h1>
<?php
if (isset($_GET['register_success'])) {
	?>
	<p><span class="registered">Oh, it looks like you registered. Log in now.</span></p>
	<?php
}
?>
<form action="/login/" method="post">
<p><input tabindex="1" id="start-here" name="e" type="email" placeholder="you@fuck.off" /></p>
<p><input tabindex="2" name="p" type="password" /></p>
<p><input tabindex="3" type="submit" value="log in &raquo;" /></p>
</form>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>