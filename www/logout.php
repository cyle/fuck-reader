<?php

// oh fuck, log em out...

$_COOKIE = array();
unset($_COOKIE);
setcookie('fucksession', '', time() - 3600);
setcookie('fucksession', '', time() - 3600, '/', 'fuckreader.com');

header('Location: /');
die();

?>