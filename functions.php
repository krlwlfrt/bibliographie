<?php
if(!file_exists(dirname(__FILE__).'/config.php'))
	exit('Sorry, but we have no config file!');

require dirname(__FILE__).'/config.php';

if(mysql_connect(BIBLIOGRAPHIE_MYSQL_HOST, BIBLIOGRAPHIE_MYSQL_USER, BIBLIOGRAPHIE_MYSQL_PASSWORD))
	if(mysql_select_db(BIBLIOGRAPHIE_MYSQL_DATABASE))
		define('BIBLIOGRAPHIE_MYSQL_CONNECTED', true);

if(!defined('BIBLIOGRAPHIE_MYSQL_CONNECTED'))
	exit('Sorry, but we have no access to the database.');

ob_start();