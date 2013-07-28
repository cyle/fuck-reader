<?php

/*

	this relies on the php-redis client, here: https://github.com/sash/php-redis
		to be installed in the PHP include path somewhere

*/

require_once('Redis.php');
$redis = new Redis('localhost', 6379);

?>