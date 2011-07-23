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

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

header('Content-Type: text/html; charset=UTF-8');

$title = 'bibliographie';

/**
 * Check if a string is an url.
 * @param string $url String that shall be checked.
 * @return bool Wether the string was an URL or not.
 */
function is_url ($url) {
	return preg_match('!(([\w]+:)?//)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?!', $url);
}

/**
 * Check if a string is a mail address.
 * @param string $mail String that shall be checked.
 * @return bool Wether the string was a mail address or not.
 */
function is_mail ($mail) {
	if(strlen($mail) <= 340)
		return preg_match("~^[a-z0-9!$'*+\-_]+(\.[a-z0-9!$'*+\-_]+)*@([a-z0-9]+(-+[a-z0-9]+)*\.)+([a-z]{2}|aero|arpa|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|travel)$~i", $mail);

	return FALSE;
}

/**
 * Give the HTML-snippet for an css-sprite icon.
 * @param string $name Identification of the icon.
 * @return string HTML-snippet
 */
function bibliographie_get_icon ($name) {
	return '<span class="silk-icon silk-icon-'.htmlspecialchars($name).'"> </span>';
}