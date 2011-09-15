<?php
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
function bibliographie_icon_get ($name) {
	return '<span class="silk-icon silk-icon-'.htmlspecialchars($name).'"> </span>';
}

/**
 * Write an action into the log.
 * @param string $category The category the action was done in.
 * @param string $action The action itself.
 * @param mixed $data Some kind of JSON representation from json_encode()
 */
function bibliographie_log ($category, $action, $data) {
	$logFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/logs/log_'.date('W_Y').'.log', 'a+');
	$time = date('r');

	_mysql_query("INSERT INTO `bibliographie_log` (
	`log_file`,
	`log_time`
) VALUES (
	'".mysql_real_escape_string(stripslashes('log_'.date('W_Y').'.log'))."',
	'".mysql_real_escape_string(stripslashes($time))."'
)");

	echo mysql_error();

	$addFile = json_encode(array(
		'id' => mysql_insert_id(),
		'time' => $time,
		'category' => $category,
		'action' => $action,
		'data' => $data
	));

	fwrite($logFile, $addFile.PHP_EOL);

	fclose($logFile);
}

/**
 * Takes an array of strings and prints them as errors, e.g. for form validation.
 * @param array $errors Array of errors.
 */
function bibliographie_print_errors ($errors) {
	foreach($errors as $error)
		echo '<p class="error">'.htmlspecialchars($error).'</p>';
}

/**
 * Purge the cache for a specific pattern.
 * @param string $pattern Pattern for files that shall be deleted.
 */
function bibliographie_purge_cache ($pattern) {
	if(mb_strpos($pattern, '..') === false and mb_strpos($pattern, '/') === false){
		$files = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/cache');
		foreach($files as $file)
			if(preg_match('~.*'.preg_quote($pattern, '~').'.*~', $file))
				unlink(BIBLIOGRAPHIE_ROOT_PATH.'/cache/'.$file);
	}
}

/**
 * Print the page navigation and calculate parameters that are needed for page navigation.
 * @param string $baseLink Baselink before appending the page variable.
 * @param int $amountOfItems Amount of items that shall be orderd on pages.
 * @return array Array of parameters that are needed for page navigation.
 */
function bibliographie_print_pages ($amountOfItems, $baseLink) {
	/**
	 * Set standard values.
	 */
	$page = 1;
	$perPage = 100;
	$pages = ceil($amountOfItems / $perPage);

	/**
	 * Adjust to user request.
	 */
	if(is_numeric($_GET['page']) and $_GET['page'] >= 1 and $_GET['page'] <= $pages)
		$page = ((int) $_GET['page']);

	/**
	 * Calulate offset for mysql queries.
	 */
	$offset = ($page - 1) * $perPage;

	if(mb_strpos($baseLink, '?') !== false)
		$baseLink .= '&';
	else
		$baseLink .= '?';

	/**
	 * Print page navigation.
	 */
	if($pages > 1){
		echo '<p class="bibliographie_pages"><strong>Pages</strong>: ';
		for($i = 1; $i <= $pages; $i++){
			$virtualOffset = ($i - 1) * $perPage;

			$virtualEnd = $virtualOffset + $perPage;
			if($virtualEnd > $amountOfItems)
				$virtualEnd = $amountOfItems;

			if($i != $page)
				echo '<a href="'.$baseLink.'page='.$i.'">['.($virtualOffset + 1).'-'.$virtualEnd.']</a> ';
			else
				echo '<strong>['.($virtualOffset + 1).'-'.$virtualEnd.']</strong> ';
		}
		echo '</p>';
	}

	return array (
		'page' => $page,
		'perPage' => $perPage,
		'pages' => $pages,
		'offset' => $offset
	);
}

/**
 * Check if a name of a user is in the database.
 * @param string $name
 * @return bool True on success, false otherwise.
 */
function bibliographie_user_get_id ($name = null) {
	static $cache = array();

	if(empty($name))
		$name = $_SERVER['PHP_AUTH_USER'];

	if(empty($cache[$name])){
		$user = _mysql_query("SELECT * FROM `a2users` WHERE `login` = '".mysql_real_escape_string(stripslashes($name))."'");
		if(mysql_num_rows($user)){
			$user = mysql_fetch_object($user);
			if($user->login == $name)
				$cache[$name] = $user->user_id;
		}
	}

	return $cache[$name];
}

function bibliographie_user_get_name ($user_id) {
	if(is_numeric($user_id)){
		$user = _mysql_query("SELECT * FROM `a2users` WHERE `user_id` = ".((int) $user_id));

		if(mysql_num_rows($user) == 1){
			$user = mysql_fetch_object($user);
			return $user->login;
		}
	}

	return 'bibliographie';
}

/**
 * Create an HTML-snippet that represents a dialog.
 * @param string $id ID of the div.
 * @param string $title Title of the dialog.
 * @param string $text Text of the dialog.
 */
function bibliographie_dialog_create ($id, $title, $text) {
	echo '<div id="'.$id.'" title="'.$title.'" class="ui-dialog">'.$text.'</div>';
}

function _mysql_query($query) {
	global $bibliographie_database_queries;

	$timer = microtime(true);
	$return = mysql_query($query);

	$error = (string) '';
	if(mysql_errno() != 0)
		$error = mysql_errno().': '.mysql_error();

	$callStack = debug_backtrace();
	$function = (string) '';
	if(!empty($callStack[1]['function'])){
		$args = (string) '';
		if(is_array($callStack[1]['args']) and count($callStack[1]['args']))
			$args = implode(',', $callStack[1]['args']);
		$function = ' in '.$callStack[1]['function'].'('.$args.')';
	}

	$bibliographie_database_queries[] = array (
		'query' => htmlspecialchars($query),
		'time' => round(microtime(true) - $timer, 5),
		'error' => $error,
		'callStack' => 'from '.$callStack[0]['file'].':'.$callStack[0]['line'].$function
	);

	return $return;
}

function bibliographie_database_total_query_time () {
	global $bibliographie_database_queries;

	$time = 0;
	foreach($bibliographie_database_queries as $query)
		$time += $query['time'];

	return $time;
}

/**
 * Include all needed functions...
 */
require dirname(__FILE__).'/authors.php';
require dirname(__FILE__).'/bookmarks.php';
require dirname(__FILE__).'/maintenance.php';
require dirname(__FILE__).'/publications.php';
require dirname(__FILE__).'/search.php';
require dirname(__FILE__).'/tags.php';
require dirname(__FILE__).'/topics.php';

require dirname(__FILE__).'/../lib/BibTex.php';