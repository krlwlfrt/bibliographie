<?php
if(!file_exists(dirname(__FILE__).'/../config.php'))
	exit('Sorry, but we have no config file!');
require dirname(__FILE__).'/../config.php';

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_LOG_USING_REPLAY', true);

require BIBLIOGRAPHIE_ROOT_PATH.'/resources/functions/general.php';
$logCount_database = (int) DB::getInstance()->query('SELECT MAX(`log_id`) AS `log_count` FROM `'.BIBLIOGRAPHIE_PREFIX.'log`')->fetch(PDO::FETCH_COLUMN, 0);
$logCount_file = 0;
if(scandir(BIBLIOGRAPHIE_ROOT_PATH.'/logs') > 2)
	$logCount_file = (int) json_decode(end(file(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.end(scandir(BIBLIOGRAPHIE_ROOT_PATH.'/logs')))))->id;
?><!DOCTYPE html>
<html>
	<head>
		<title>Log replay | bibliographie</title>
		<link rel="shortcut icon" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/favicon.png" type="image/png" />
		<link rel="icon" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/favicon.png" type="image/png" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/all.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/silk-icons.css" />
	</head>
	<body>
		<div id="wrapper">
			<div id="header"><h1>Log replay</h1></div>
<?php
if($logCount_file > $logCount_database){
	$gap = array();
	foreach(scandir(BIBLIOGRAPHIE_ROOT_PATH.'/logs') as $logFile){
		if($logFile == '.' or $logFile == '..')
			continue;

		foreach(file(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$logFile) as $row){
			$row = json_decode($row);
			if($row->id > $logCount_database)
				$gap[] = json_encode($row);
		}
	}

	switch($_GET['task']){
		case 'replay':
			foreach($gap as $row){
				$row = json_decode($row);
				$row->data = json_decode($row->data);

				$result = false;
				$precheck = true;

				if($row->category == 'authors'){
					if($row->action == 'editAuthor'){
						print_r($row->data);
						/*$dataAfter = $row->data->dataAfter;
						$author = bibliographie_authors_get_data($row->data->dataBefore->author_id);
						if($author == $row->data->dataBefore){
							$result = bibliographie_authors_edit_author($data->author_id, $data->firstname, $data->von, $data->surname, $data->jr, $data->email, $data->url, $data->institute);
						}else{
							echo '<p class="error">#'.$row->id.' Data precheck was unsuccessfull!</p>';
							break;
						}*/
					}
				}elseif($row->category == 'topics'){
					if($row->action == 'unlockTopic'){
						$result = bibliographie_admin_unlock_topic($row->data->topic_id);
					}elseif($row->action == 'lockTopic'){
						$result = bibliographie_admin_lock_topics(array($row->data->topic_id));
					}
				}

				if($result !== false)
					echo '<p class="success">#'.$row->id.': '.$row->category.' '.$row->action.' was successfull!</p>';
				else{
					echo '<p class="error">#'.$row->id.' An error occurred while trying to apply logged change.</p>';
					break;
				}
				if($precheck === false){
					echo '<p class="error">#'.$row->id.' Data precheck was unsuccessfull.</p>';
					break;
				}
			}
			break;

		default:
		case 'check':
			echo '<h2>Check changes</h2>',
				'<p class="notice">This is a list of changes that will be written to the database. Please check them and remove those that you do not want from the files. When you are done checking you can run the log replay by pressing the button in the bottom right corner.</p>';
			bibliographie_admin_log_parse($gap);
			break;
	}
}else
	echo '<p class="success">Nothing to do here. Database log and file log are up to date!</p>';
?>

		</div>
	</body>
</html>