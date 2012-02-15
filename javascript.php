<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
define('BIBLIOGRAPHIE_ROOT_PATH', '.');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

header('Content-Type: application/javascript');

$files = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/resources/lib/');
foreach($files as $file)
	if($file != '.' and $file != '..' and preg_match('~\.js$~', $file))
		echo '/* lib/'.$file.' */'.PHP_EOL.file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/resources/lib/'.$file).PHP_EOL.PHP_EOL;

$files = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/resources/functions/');
foreach($files as $file)
	if($file != '.' and $file != '..' and preg_match('~\.js$~', $file))
		echo '/* functions/'.$file.' */'.PHP_EOL.file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/resources/functions/'.$file).PHP_EOL.PHP_EOL;

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';