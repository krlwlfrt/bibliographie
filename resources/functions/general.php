<?php
class DB {
	private static
		$instance = null,
		$inTransaction = false;

	/**
	 * Set __construct() and __clone() to private to avoid singleton misuse.
	 */
	private function __construct () {}
	private function __clone () {}

	/**
	 * Establishes a database connection and returns a PDO database object.
	 * @return PDO
	 */
	public static function getInstance () {
		if(!self::$instance){
			try {
				self::$instance = new PDO('mysql:host='.BIBLIOGRAPHIE_MYSQL_HOST.';dbname='.BIBLIOGRAPHIE_MYSQL_DATABASE, BIBLIOGRAPHIE_MYSQL_USER, BIBLIOGRAPHIE_MYSQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
				self::$instance->exec('SET CHARACTER SET utf8');
				self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				bibliographie_exit('No database connection', 'Sorry, but we have no connection to the database!<br />'.$e->getMessage());
			}
		}

		return self::$instance;
	}

	/**
	 * Check if in a transaction or not.
	 * @return bool
	 */
	public static function inTransaction () {
		return self::$inTransaction;
	}

	/**
	 * If not in a transaction, begin one.
	 */
	public static function beginTransaction () {
		if(!self::$inTransaction)
			self::getInstance()->beginTransaction();
		self::$inTransaction = true;
	}

	/**
	 * If in a transaction commit it.
	 */
	public static function commit () {
		if(self::$inTransaction)
			self::getInstance()->commit();
		self::$inTransaction = false;
	}

	/**
	 * If in a transaction roll it back.
	 */
	public static function rollBack () {
		if(self::$inTransaction)
			self::getInstance()->rollBack();
		self::$inTransaction = false;
	}

	/**
	 * Close connection.
	 */
	public function close () {
		if(self::$instance)
			self::$instance = null;
	}
}

/**
 * Convert a CSV-string into an array.
 * @param string $csv
 * @return array
 */
function csv2array ($csv, $type = null) {
	$return = array();

	if(is_csv($csv, $type)){
		$return = explode(',', $csv);

		if($type == 'int')
			for($i = 0; $i < count($return); $i++)
				$return[$i] = (int) $return[$i];
	}

	return $return;
}

/**
 * Convert an array into a csv string.
 * @param array $array
 * @return string
 */
function array2csv (array $array) {
	$return = (string) '';

	if(count($array) > 0){
		foreach($array as $value){
			if(!empty($return))
				$return .= ',';

			if(!is_numeric($value) or mb_strpos($value, ',') !== false)
				$return .= "'".str_replace("'", "\'", $value)."'";
			else
				$return .= $value;
		}
	}

	return $return;
}

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
 * Check if a string is a csv list.
 * @param string $csv
 * @param string $type The type of the cs values. (int, etc.)
 * @return bool
 */
function is_csv ($csv, $type = null) {
	if(is_string($csv)){
		if(empty($csv))
			return true;

		if($type == 'int')
			return preg_match('~^[0-9]+(\,[0-9]+)*$~', $csv);

		if($type == null)
			return preg_match('~^[^\,]+(\,[^\,]+)*$~', $csv);
	}

	return false;
}

/**
 * Give the HTML-snippet for an css-sprite icon.
 * @param string $name Identification of the icon.
 * @return string HTML-snippet
 */
function bibliographie_icon_get ($name, $title = '') {
	return '<span class="silk-icon silk-icon-'.htmlspecialchars($name).'" title="'.htmlspecialchars($title).'"> </span>';
}

/**
 * Write an action into the log.
 * @param string $category The category the action was done in.
 * @param string $action The action itself.
 * @param mixed $data Some kind of JSON representation from json_encode()
 */
function bibliographie_log ($category, $action, $data) {
	static $logAccess = null;

	$time = date('r');

	if($logAccess === null)
		$logAccess = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'log` (
	`log_time`
) VALUES (
	:time
)');

	$logAccess->execute(array(
		'time' => $time
	));

	if(BIBLIOGRAPHIE_LOG_USING_REPLAY)
		return true;

	$logFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/logs/changesets/log_'.date('Y_W').'.log', 'a+');

	$addFile = json_encode(array(
		'id' => DB::getInstance()->lastInsertId(),
		'user' => bibliographie_user_get_id(),
		'time' => $time,
		'category' => $category,
		'action' => $action,
		'data' => $data
	));

	fwrite($logFile, $addFile.PHP_EOL);

	fclose($logFile);

	return true;
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
function bibliographie_cache_purge ($pattern = null) {
	if(!empty($pattern)){
		if(mb_strpos($pattern, '..') === false and mb_strpos($pattern, '/') === false){
			$files = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/cache');
			foreach($files as $file)
				if(preg_match('~.*'.preg_quote($pattern, '~').'.*~', $file))
					@unlink(BIBLIOGRAPHIE_ROOT_PATH.'/cache/'.$file);
		}
	}else{
		foreach(scandir(BIBLIOGRAPHIE_ROOT_PATH.'/cache') as $file)
			if($file != '.' and $file != '..')
				unlink(BIBLIOGRAPHIE_ROOT_PATH.'/cache/'.$file);
	}
}

/**
 * Print the page navigation and calculate parameters that are needed for page navigation.
 * @param string $baseLink Baselink before appending the page variable.
 * @param int $amountOfItems Amount of items that shall be orderd on pages.
 * @return array Array of parameters that are needed for page navigation.
 */
function bibliographie_pages_calculate ($items) {
	/**
	 * Set standard values.
	 */
	$page = 1;
	$perPage = 100;
	$pages = ceil($items / $perPage);

	/**
	 * Adjust to user request.
	 */
	if(is_numeric($_GET['page']) and $_GET['page'] >= 1 and $_GET['page'] <= $pages)
		$page = ((int) $_GET['page']);

	/**
	 * Calulate offset for mysql queries.
	 */
	$offset = ($page - 1) * $perPage;

	return array (
		'items' => $items,
		'page' => $page,
		'perPage' => $perPage,
		'pages' => $pages,
		'offset' => $offset,
		'ceiling' => min($items, $offset + $perPage)
	);
}

function bibliographie_pages_print ($pageData, $baseLink) {
	$str = (string) '';

	if($pageData['pages'] > 1){
		$str .= '<p class="bibliographie_pages"><strong>Pages</strong>: ';
		for($i = 1; $i <= $pageData['pages']; $i++){
			$virtualOffset = ($i - 1) * $pageData['perPage'];

			$virtualEnd = $virtualOffset + $pageData['perPage'];
			if($virtualEnd > $pageData['items'])
				$virtualEnd = $pageData['items'];

			if($i != $pageData['page'])
				$str .= '<a href="'.bibliographie_link_append_param($baseLink, 'page='.$i).'">['.($virtualOffset + 1).'-'.$virtualEnd.']</a> ';
			else
				$str .= '<strong>['.($virtualOffset + 1).'-'.$virtualEnd.']</strong> ';
		}
		$str .= '</p>';
	}

	return $str;
}

/**
 * Check if a name of a user is in the database.
 * @param string $name
 * @return bool True on success, false otherwise.
 */
function bibliographie_user_get_id ($name = null) {
	static $cache = array(), $checkUser = null;

	$return = false;

	if(empty($name))
		$name = $_SERVER['PHP_AUTH_USER'];

	if(empty($cache[$name])){
		if($checkUser == null){
			$checkUser = DB::getInstance()->prepare('SELECT `user_id`, `login` FROM `'.BIBLIOGRAPHIE_PREFIX.'users` WHERE `login` = :login');
			$checkUser->setFetchMode(PDO::FETCH_OBJ);
		}

		$checkUser->execute(array(
			'login' => $name
		));

		if($checkUser->rowCount() == 1){
			$user = $checkUser->fetch();

			if($user->login == $name)
				$cache[$name] = $user->user_id;
		}
	}

	if(!empty($cache[$name]))
		$return = $cache[$name];

	return $return;
}

/**
 * Get the name of a user by his/her ID.
 * @param int $user_id
 * @return string
 */
function bibliographie_user_get_name ($user_id) {
	static $checkUser = null;

	if(is_numeric($user_id)){
		if($checkUser === null){
			$checkUser = DB::getInstance()->prepare('SELECT `login` FROM `'.BIBLIOGRAPHIE_PREFIX.'users` WHERE `user_id` = :user_id LIMIT 1');
			$checkUser->setFetchMode(PDO::FETCH_OBJ);
		}

		$checkUser->execute(array(
			'user_id' => (int) $user_id
		));

		if($checkUser->rowCount() == 1){
			$user = $checkUser->fetch();

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

function bibliographie_exit ($title, $message) {
	ob_end_clean();
?><!DOCTYPE html>
<html lang="en">
	<head><title><?php echo htmlspecialchars($title)?> | bibliographie</title></head>
	<body>
		<h1><?php echo htmlspecialchars($title)?></h1>
		<p><?php echo $message?></p>
	</body>
</html>
<?php
	exit();
}

function bibliographie_options_compare (array $options, array $_options) {
	foreach($options as $key => $value){
		if(is_array($options[$key])){
			if(in_array($_options[$key], $options[$key]['possible'], true))
				$options[$key] = $_options[$key];
			else
				$options[$key] = $options[$key]['default'];
		}elseif($options[$key] != $_options[$key] and gettype($options[$key]) == gettype($_options[$key]))
			$options[$key] = $_options[$key];
	}

	return $options;
}

function bibliographie_link_append_param ($link, $param, $encode = true) {
	if(mb_strpos($link, '?') === false)
		return $link.'?'.$param;

	if($encode)
		return $link.'&amp;'.$param;

	return $link.'&'.$param;
}

function bool2img ($bool) {
	if($bool)
		return bibliographie_icon_get('tick');

	return bibliographie_icon_get('cross');
}

function bibliographie_database_update ($version, $query, $description) {
	$return = (bool) DB::getInstance()->exec($query);
	DB::getInstance()->exec('UPDATE `'.BIBLIOGRAPHIE_PREFIX.'settings` SET `value` = '.DB::getInstance()->quote($version).' WHERE `key` = "DATABASE_VERSION"');
	if($return)
		bibliographie_log('maintenance', 'Updating database scheme', json_encode(array(
			'schemeVersion' => $version,
			'query' => $query,
			'description' => $description
		)));
	return $return;
}

/**
 * Include all needed functions...
 */
require dirname(__FILE__).'/admin.php';
require dirname(__FILE__).'/attachments.php';
require dirname(__FILE__).'/authors.php';
require dirname(__FILE__).'/bookmarks.php';
require dirname(__FILE__).'/charmap.php';
require dirname(__FILE__).'/errors.php';
require dirname(__FILE__).'/history.php';
require dirname(__FILE__).'/maintenance.php';
require dirname(__FILE__).'/notes.php';
require dirname(__FILE__).'/publications.php';
require dirname(__FILE__).'/ris.php';
require dirname(__FILE__).'/schemeUpdates.php';
require dirname(__FILE__).'/search.php';
require dirname(__FILE__).'/tags.php';
require dirname(__FILE__).'/topics.php';

/**
 * Include libraries...
 */
require dirname(__FILE__).'/../lib/upload.class.php';

require dirname(__FILE__).'/../lib/BibTex.php';
require dirname(__FILE__).'/../lib/LibRIS/RISReader.php';
require dirname(__FILE__).'/../lib/LibRIS/RISWriter.php';
require dirname(__FILE__).'/../lib/LibRIS/RISTags.php';

/**
 * Set error and exception handling for uncaught errors and exceptions.
 */
set_exception_handler(array('bibliographie_error_handler', 'exceptions'));
set_error_handler(array('bibliographie_error_handler', 'errors'));
register_shutdown_function(array('bibliographie_error_handler', 'fatal_errors'));