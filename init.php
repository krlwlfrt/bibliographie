<?php
require dirname(__FILE__).'/resources/functions/general.php';

define('BIBLIOGRAPHIE_SCRIPT_START', microtime(true));

ob_start();
session_start();

/**
 * Check for config file.
 */
if(!file_exists(dirname(__FILE__).'/config.php'))
	bibliographie_exit('Config file missing!', 'Sorry, but we have no config file!');
require dirname(__FILE__).'/config.php';

/**
 * Initialize variable for database query stats.
 */
$bibliographie_database_queries = array();

$bibliographie_history_path_identifier = '';

/**
 * Check mysql connection.
 */
if(@mysql_connect(BIBLIOGRAPHIE_MYSQL_HOST, BIBLIOGRAPHIE_MYSQL_USER, BIBLIOGRAPHIE_MYSQL_PASSWORD))
	if(@mysql_select_db(BIBLIOGRAPHIE_MYSQL_DATABASE))
		define('BIBLIOGRAPHIE_MYSQL_CONNECTED', true);

try {
	$db = new PDO('mysql:host='.BIBLIOGRAPHIE_MYSQL_HOST.';dbname='.BIBLIOGRAPHIE_MYSQL_DATABASE, BIBLIOGRAPHIE_MYSQL_USER, BIBLIOGRAPHIE_MYSQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8') );
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	bibliographie_exit('No database connection', 'Sorry, but we have no connection to the database! '.$e->getMessage());
}

if(!defined('BIBLIOGRAPHIE_MYSQL_CONNECTED'))
	bibliographie_exit('No database connection', 'Sorry, but we have no connection to the database!');


/**
 * Initialize UTF-8.
 */
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

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

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'bibliographie_log'")) == 0){
	echo '<!DOCTYPE html><html lang="de"><head><title>Initialize database</title></head><body><h1>Initialize database</h1>';

	if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'a2publication'")) == 1){
		/**
		 * Aigaion scheme is present... Just convert it, to the bibliographie scheme...
		 */

		if(empty($_GET['makeScheme'])){
			echo '<p>You have the aigaion scheme. We need to make a few tiny little changes for bibliographie.</p>';
			echo '<p><a href="?makeScheme=1">Do it now!</a></p>';
		}elseif($_GET['makeScheme'] == 1){
			// DROP TABLE `a2aigaiongeneral`, `a2availablerights`, `a2changehistory`, `a2config`, `a2grouprightsprofilelink`, `a2logintegration`, `a2rightsprofilerightlink`, `a2rightsprofiles`, `a2usergrouplink`, `a2userrights`;
			mysql_query("ALTER TABLE `a2keywords` RENAME TO `a2tags`, CHANGE COLUMN `keyword_id` `tag_id` INT(10) NOT NULL AUTO_INCREMENT FIRST, CHANGE COLUMN `keyword` `tag` MEDIUMTEXT NOT NULL AFTER `tag_id`;");
			mysql_query("ALTER TABLE `a2publicationkeywordlink` RENAME TO `a2publicationtaglink`, CHANGE COLUMN `keyword_id` `tag_id` INT(10) NOT NULL AFTER `pub_id`;");
			mysql_query("ALTER TABLE `a2publication` ADD FULLTEXT INDEX `fulltext` (`title`, `abstract`, `note`);");
			mysql_query("ALTER TABLE `a2publication` ADD FULLTEXT INDEX `fulltext_title` (`title`);");
			mysql_query("ALTER TABLE `a2publication` ADD FULLTEXT INDEX `fulltext_journal` (`journal`);");
			mysql_query("ALTER TABLE `a2publication` ADD FULLTEXT INDEX `fulltext_booktitle` (`booktitle`);");
			mysql_query("ALTER TABLE `a2publication` ADD COLUMN `missingFields` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0' AFTER `pages`;");
			mysql_query("ALTER TABLE `a2topics` ADD FULLTEXT INDEX `fulltext` (`name`, `description`);");
			mysql_query("ALTER TABLE `a2author` ADD FULLTEXT INDEX `fulltext` (`surname`, `firstname`);");
			mysql_query("ALTER TABLE `a2tags` ADD FULLTEXT INDEX `fulltext` (`tag`);");

			mysql_query("CREATE TABLE `bibliographie_log` (
	`log_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`log_file` TEXT NOT NULL,
	`log_time` TEXT NOT NULL,
	PRIMARY KEY (`log_id`)
) COLLATE='utf8_general_ci' ENGINE=MyISAM;");

			mysql_query("CREATE TABLE `lockedtopics` (
	`topic_id` INT(10) UNSIGNED NOT NULL
) COLLATE='utf8_general_ci' ENGINE=MyISAM;");

			mysql_query("CREATE TABLE `singulars_and_plurals` (
	`ln` VARCHAR(2) NOT NULL DEFAULT 'en' COLLATE 'utf8_general_ci',
	`singular` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`plural` TINYTEXT NOT NULL COLLATE 'utf8_general_ci'
) COLLATE='utf8_general_ci' ENGINE=MyISAM ROW_FORMAT=DEFAULT;");

			echo '<p>Scheme has been modified!!!</p>';
		}
	}else{
		/**
		 * Aigaion scheme is not present... Create it completely new.
		 */

		if(empty($_GET['makeScheme'])){
			echo '<p>You don\'t seem to have an appropriate database scheme at all. Do you want it to be created now?</p>';
			echo '<p><a href="?makeScheme=1">Do it now!</a></p>';
		}elseif($_GET['makeScheme'] == 1)
			mysql_query("CREATE TABLE IF NOT EXISTS `a2attachments` (
  `pub_id` int(10) unsigned NOT NULL DEFAULT '0',
  `location` varchar(255) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `ismain` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mime` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `isremote` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `att_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `derived_read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  PRIMARY KEY (`att_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2author` (
  `author_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `surname` varchar(255) NOT NULL DEFAULT '',
  `von` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `institute` varchar(255) NOT NULL DEFAULT '',
  `specialchars` enum('FALSE','TRUE') NOT NULL DEFAULT 'FALSE',
  `cleanname` varchar(255) NOT NULL DEFAULT '',
  `jr` varchar(255) DEFAULT '',
  PRIMARY KEY (`author_id`),
  FULLTEXT KEY `fulltext` (`surname`,`firstname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2notecrossrefid` (
  `note_id` int(10) NOT NULL DEFAULT '0',
  `xref_id` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2notes` (
  `note_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pub_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `rights` enum('public','private') NOT NULL DEFAULT 'public',
  `text` mediumtext,
  `read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `derived_read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2publication` (
  `pub_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `year` varchar(127) NOT NULL DEFAULT '',
  `actualyear` varchar(127) NOT NULL DEFAULT '',
  `title` mediumtext NOT NULL,
  `bibtex_id` varchar(255) NOT NULL DEFAULT '',
  `report_type` varchar(255) NOT NULL DEFAULT '',
  `pub_type` enum('Article','Book','Booklet','Inbook','Incollection','Inproceedings','Manual','Mastersthesis','Misc','Phdthesis','Proceedings','Techreport','Unpublished') DEFAULT NULL,
  `survey` tinyint(1) NOT NULL DEFAULT '0',
  `mark` int(11) NOT NULL DEFAULT '5',
  `series` varchar(127) NOT NULL DEFAULT '',
  `volume` varchar(127) NOT NULL DEFAULT '',
  `publisher` varchar(127) NOT NULL DEFAULT '',
  `location` varchar(127) NOT NULL DEFAULT '',
  `issn` varchar(32) NOT NULL DEFAULT '',
  `isbn` varchar(32) NOT NULL DEFAULT '',
  `firstpage` varchar(10) NOT NULL DEFAULT '0',
  `lastpage` varchar(10) NOT NULL DEFAULT '0',
  `journal` varchar(255) NOT NULL DEFAULT '',
  `booktitle` varchar(255) NOT NULL DEFAULT '',
  `number` varchar(255) NOT NULL DEFAULT '',
  `institution` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `chapter` varchar(127) NOT NULL DEFAULT '',
  `edition` varchar(255) NOT NULL DEFAULT '',
  `howpublished` varchar(255) NOT NULL DEFAULT '',
  `month` varchar(255) NOT NULL DEFAULT '',
  `organization` varchar(255) NOT NULL DEFAULT '',
  `school` varchar(255) NOT NULL DEFAULT '',
  `note` mediumtext NOT NULL,
  `abstract` mediumtext NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `doi` varchar(255) NOT NULL DEFAULT '',
  `crossref` varchar(255) NOT NULL DEFAULT '',
  `namekey` varchar(255) NOT NULL DEFAULT '',
  `userfields` mediumtext NOT NULL,
  `specialchars` enum('FALSE','TRUE') NOT NULL DEFAULT 'FALSE',
  `cleanjournal` varchar(255) NOT NULL DEFAULT '',
  `cleantitle` varchar(255) NOT NULL DEFAULT '',
  `read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `derived_read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `cleanauthor` text,
  `pages` varchar(255) NOT NULL DEFAULT '',
  `missingFields` smallint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pub_id`),
  FULLTEXT KEY `fulltext_title` (`title`),
  FULLTEXT KEY `fulltext_journal` (`journal`),
  FULLTEXT KEY `fulltext_booktitle` (`booktitle`),
  FULLTEXT KEY `fulltext` (`title`,`abstract`,`note`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2publicationauthorlink` (
  `pub_id` int(10) unsigned NOT NULL DEFAULT '0',
  `author_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rank` int(10) unsigned NOT NULL DEFAULT '1',
  `is_editor` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`pub_id`,`author_id`,`is_editor`),
  KEY `pub_id` (`pub_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2publicationtaglink` (
  `pub_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  PRIMARY KEY (`pub_id`,`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2tags` (
  `tag_id` int(10) NOT NULL AUTO_INCREMENT,
  `tag` mediumtext NOT NULL,
  `cleankeyword` text NOT NULL,
  PRIMARY KEY (`tag_id`),
  FULLTEXT KEY `fulltext` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2topicpublicationlink` (
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pub_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`,`pub_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2topics` (
  `topic_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `url` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `derived_read_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL DEFAULT 'intern',
  `cleanname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`topic_id`),
  KEY `name` (`name`),
  FULLTEXT KEY `fulltext` (`name`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2topictopiclink` (
  `source_topic_id` int(10) NOT NULL DEFAULT '0',
  `target_topic_id` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Hierarchy of topics; typed relations';");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2userbookmarklists` (
  `user_id` int(10) NOT NULL,
  `pub_id` int(10) NOT NULL,
  PRIMARY KEY (`user_id`,`pub_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2userpublicationmark` (
  `pub_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mark` enum('','1','2','3','4','5') NOT NULL DEFAULT '3',
  `hasread` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`pub_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2users` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL DEFAULT 'darkdefault',
  `password_invalidated` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `newwindowforatt` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `summarystyle` varchar(255) NOT NULL DEFAULT 'author',
  `authordisplaystyle` varchar(255) NOT NULL DEFAULT 'vlf',
  `liststyle` smallint(6) NOT NULL DEFAULT '0',
  `login` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `initials` varchar(10) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `betweenname` varchar(10) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `csname` varchar(10) DEFAULT NULL,
  `abbreviation` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT NULL,
  `u_rights` tinyint(2) NOT NULL DEFAULT '0',
  `lastreviewedtopic` int(10) NOT NULL DEFAULT '1',
  `type` enum('group','anon','normal','external') NOT NULL DEFAULT 'normal',
  `lastupdatecheck` int(10) unsigned NOT NULL DEFAULT '0',
  `exportinbrowser` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `utf8bibtex` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `language` varchar(20) NOT NULL DEFAULT 'english',
  `similar_author_test` varchar(20) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `a2usertopiclink` (
  `collapsed` int(2) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `topic_id` int(10) NOT NULL DEFAULT '0',
  `star` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `bibliographie_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_file` text NOT NULL,
  `log_time` text NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `lockedtopics` (
  `topic_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("CREATE TABLE IF NOT EXISTS `singulars_and_plurals` (
  `ln` varchar(2) NOT NULL DEFAULT 'en',
  `singular` tinytext NOT NULL,
  `plural` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		mysql_query("INSERT INTO `a2users` (`login`) VALUES ('".mysql_real_escape_string(stripslashes($_SERVER['PHP_AUTH_USER']))."');");
		echo '<p>Scheme has been created!!!</p>';
	}
	echo '</body></html>';
	exit();
}

if(!bibliographie_user_get_id())
	bibliographie_exit('Account missing!', 'Sorry, but you do not have an account for bibliographie!');

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
 * Set standard title for header.
 */
$title = 'bibliographie';

set_exception_handler('bibliographie_exception_handler');
set_error_handler('bibliographie_error_handler');