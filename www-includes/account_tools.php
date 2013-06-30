<?php

// generate a random key from /dev/random
function get_key($bit_length = 128) {
	$fp = @fopen('/dev/urandom','rb'); // should be /dev/random but it's too slow
	if ($fp !== FALSE) {
		$key = substr(base64_encode(@fread($fp,($bit_length + 7) / 8)), 0, (($bit_length + 5) / 6)  - 2);
		@fclose($fp);
		$key = str_replace(array('+', '/'), array('0', 'X'), $key);
		return $key;
	}
	return null;
}

?>