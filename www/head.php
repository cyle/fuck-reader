<?php
header('Content-type: text/html; charset=utf-8');
ini_set('default_charset', 'utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<?php
if (isset($page_title) && $page_title != '') {
	$page_title = str_replace('-', ' ', $page_title);
	echo '<title>'.strtoupper($page_title).' - FUCK READER</title>'."\n";
} else {
	echo '<title>FUCK READER</title>'."\n";
}
?>
<meta name="viewport" content="width=device-width">
<link href="//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic|PT+Sans+Narrow:700|PT+Serif:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
<link href="/css/fuck.css" rel="stylesheet" type="text/css" />
<link href="/css/fuckup.css" rel="stylesheet" type="text/css" />
</head>