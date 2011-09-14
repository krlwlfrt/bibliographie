<?php
require dirname(__FILE__).'/resources/functions/general.php';

define('BIBLIOGRAPHIE_SCRIPT_START', microtime(true));

ob_start();
session_start();

/**
 * Check for config file.
 */
if(!file_exists(dirname(__FILE__).'/config.php'))
	exit('<!DOCTYPE html><html lang="de"><title>Config file missing!</title></head><body><h1>Config file missing!</h1><p>Sorry, but we have no config file!</p></body></html>');
require dirname(__FILE__).'/config.php';

/**
 * Check mysql connection.
 */
if(@mysql_connect(BIBLIOGRAPHIE_MYSQL_HOST, BIBLIOGRAPHIE_MYSQL_USER, BIBLIOGRAPHIE_MYSQL_PASSWORD))
	if(@mysql_select_db(BIBLIOGRAPHIE_MYSQL_DATABASE))
		define('BIBLIOGRAPHIE_MYSQL_CONNECTED', true);

if(!defined('BIBLIOGRAPHIE_MYSQL_CONNECTED'))
	exit('<!DOCTYPE html><html lang="de"><title>Config file missing!</title></head><body><h1>No database connection</h1><p>Sorry, but we have no connection to the database!</p></body></html>');


/**
 * Initialize UTF-8.
 */
_mysql_query("SET NAMES 'utf8'");
_mysql_query("SET CHARACTER SET 'utf8'");
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

/**
 * Check authentication.
 */
if(!isset($_SERVER['PHP_AUTH_USER'])){
	header('WWW-Authenticate: Basic realm="bibliographie"');
	header('HTTP/1.0 401 Unauthorized');
	exit('You are not logged in!');
}

if(!bibliographie_user_get_id())
	exit('<!DOCTYPE html><html lang="de"><title>Account missing!</title></head><body><h1>Account missing!</h1><p>Sorry, but you do not have an account for bibliographie!</p></body></html>');

/**
 * Check for necessary directories.
 */
if(!is_dir(dirname(__FILE__).'/cache'))
	mkdir(dirname(__FILE__).'/cache', 0755);
if(!is_dir(dirname(__FILE__).'/logs'))
	mkdir(dirname(__FILE__).'/logs', 0755);

/**
 * If requested set the caching to false.
 */
if($_GET['ignoreCache'] == 1)
	define('BIBLIOGRAPHIE_CACHING', false);

if($_GET['purgeCache'] == 1)
	foreach(scandir(BIBLIOGRAPHIE_ROOT_PATH.'/cache') as $file)
		if($file != '.' and $file != '..')
			unlink(BIBLIOGRAPHIE_ROOT_PATH.'/cache/'.$file);

/**
 * Make sure contents of cache are renewed every second day.
 */
foreach(scandir(dirname(__FILE__).'/cache') as $object){
	if($object == '.' or $object == '..')
		continue;

	if(filemtime(dirname(__FILE__).'/cache/'.$object) + (60 * 60 * 24 * 2) < time())
		unlink(dirname(__FILE__).'/cache/'.$object);
}

if(!defined('BIBLIOGRAPHIE_OUTPUT_BODY'))
	define('BIBLIOGRAPHIE_OUTPUT_BODY', true);

/**
 * Initialize variable for database query stats.
 */
$bibliographie_database_queries = array();

/**
 * Set standard title for header.
 */
$title = 'bibliographie';