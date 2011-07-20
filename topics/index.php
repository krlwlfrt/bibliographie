<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
require dirname(__FILE__).'/topics.php';

require BIBLIOGRAPHIE_ROOT_PATH.'/_header.php';

$time = microtime(true);
bibliographie_topics_traverse(1);
echo microtime(true)-$time;

require BIBLIOGRAPHIE_ROOT_PATH.'/_footer.php';