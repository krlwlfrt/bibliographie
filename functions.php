<?php
ob_start();
if(!isset($_SERVER['PHP_AUTH_USER'])){
	header('WWW-Authenticate: Basic realm="My Realm"');
	header('HTTP/1.0 401 Unauthorized');
	exit('You are not logged in!');
}

if(!file_exists(dirname(__FILE__).'/config.php'))
	exit('Sorry, but we have no config file!');
require dirname(__FILE__).'/config.php';

if(!is_dir(dirname(__FILE__).'/cache'))
	mkdir(dirname(__FILE__).'/cache', 0755);

if(mysql_connect(BIBLIOGRAPHIE_MYSQL_HOST, BIBLIOGRAPHIE_MYSQL_USER, BIBLIOGRAPHIE_MYSQL_PASSWORD))
	if(mysql_select_db(BIBLIOGRAPHIE_MYSQL_DATABASE))
		define('BIBLIOGRAPHIE_MYSQL_CONNECTED', true);
if(!defined('BIBLIOGRAPHIE_MYSQL_CONNECTED'))
	exit('Sorry, but we have no access to the database.');

define('BIBLIOGRAPHIE_SCRIPT_START', microtime(true));

$title = 'bibliographie';

/**
 * Check if a string is an url.
 * @param string $url String that shall be checked
 * @return bool Wether the string was an URL or not.
 */
function is_url ($url) {
	return preg_match('!(([\w]+:)?//)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?!', $url);
}

function bibliographie_get_icon ($name) {
	return '<span class="silk-icon silk-icon-'.htmlspecialchars($name).'"> </span>';
}