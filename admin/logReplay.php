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
				$data = json_decode($row->data);

				$result = false;
				$precheck = true;
				$precheckError = (string) '';

				if($row->category == 'authors'){
					if($row->action == 'editAuthor'){
						$dataAfter = $data->dataAfter;
						$author = bibliographie_authors_get_data($dataAfter->author_id);

						if($author == $data->dataBefore)
							$result = bibliographie_authors_edit_author($dataAfter->author_id, $dataAfter->firstname, $dataAfter->von, $dataAfter->surname, $dataAfter->jr, $dataAfter->email, $dataAfter->url, $dataAfter->institute);
						else
							$precheck = false;

					}elseif($row->action == 'createAuthor')
						$result = bibliographie_authors_create_author ($data->firstname, $data->von, $data->surname, $data->jr, $data->email, $data->url, $data->institute, $data->author_id);

					elseif($row->action == 'deleteAuthor'){
						$dataDeleted = $data->dataDeleted;
						$author = bibliographie_authors_get_data($dataDeleted->author_id);

						if($author == $dataDeleted)
							$result = bibliographie_authors_delete($dataDeleted->author_id);
						else
							$precheck = false;
					}

				}elseif($row->category == 'maintenance'){
					if($row->action == 'Updating database scheme'){
						if(BIBLIOGRAPHIE_DATABASE_VERSION == $data->schemeVersion - 1)
							$result = bibliographie_database_update($data->schemeVersion, $data->query, $data->description);
						else
							$precheck = false;
					}

					elseif($row->action == 'mergeAuthors'){
						$into = bibliographie_authors_get_data($data->into);
						$delete = bibliographie_authors_get_data($data->delete);
						if(count(array_diff(bibliographie_authors_get_publications($delete), csv2array($data->publications)) == 0)
							and count(csv2array($data->publications)) == $data->publicationsAffected
							and is_object($into)
							and is_object($delete))
							$result = bibliographie_maintenance_merge_authors($data->into, $data->delete);
						else
							$precheck = false;
					}

				}elseif($row->category == 'notes'){
					if($row->action == 'createNote'){
						$user = bibliographie_user_get_name($data->user_id);
						$publication = bibliographie_publications_get_data($data->pub_id);

						if($user != 'bibliographie' and is_object($publication))
							$result = bibliographie_notes_create_note ($data->pub_id, $data->text, $data->note_id, $data->user_id);
						else
							$precheck = false;

					}elseif($row->action == 'editNote'){
						$note = bibliographie_notes_get_data($data->note_id);
						if(is_object($note))
							$result = bibliographie_notes_edit_note ($data->note_id, $data->text);
						else
							$precheck = false;

					}elseif($row->action == 'deleteNote'){
						$dataDeleted = $data->dataDeleted;
						$note = bibliographie_notes_get_data($dataDeleted->note_id);
						if(is_object($note) and $dataDeleted == $note)
							$result = bibliographie_notes_delete_note($dataDeleted->note_id);
						else
							$precheck = false;
					}

				}elseif($row->category == 'tags'){
					if($row->action == 'createTag')
						$result = bibliographie_tags_create_tag($data->tag, $data->tag_id);

				}elseif($row->category == 'topics'){
					if($row->action == 'unlockTopic')
						$result = bibliographie_admin_unlock_topic($data->topic_id);

					elseif($row->action == 'lockTopic')
						$result = bibliographie_admin_lock_topics(array($data->topic_id));

					elseif($row->action == 'createTopic')
						$result = bibliographie_topics_create_topic($data->name, $data->description, $data->url, $data->topics, $data->topic_id);

					elseif($row->action == 'editTopic'){
						$topic = bibliographie_topics_get_data($data->topic_id);
						$dataAfter = $data->dataAfter;

						if(is_object($topic) and $topic == $data->dataBefore)
							$result = bibliographie_topics_edit_topic($dataAfter->topic_id, $dataAfter->name, $dataAfter->description, $dataAfter->url, $dataAfter->topics);
					}
				}

				/**
				 * In case some change is left out set the id correctly to ensure consistency.
				 */
				DB::getInstance()->query('UPDATE `'.BIBLIOGRAPHIE_PREFIX.'log` SET `log_id` = '.((int) $row->id).' ORDER BY `log_id` DESC LIMIT 1');

				if($result !== false)
					echo '<p class="success">#'.$row->id.': '.$row->category.' '.$row->action.' was successfull!</p>';
				else{
					echo '<p class="error">#'.$row->id.' An error occurred while trying to apply logged change.</p>';
					break;
				}
				if($precheck === false){
					echo '<p class="error">#'.$row->id.' Data precheck was unsuccessfull.</p>';
					if(!empty($precheckError))
						echo '<p>'.$precheckError.'</p>';
					break;
				}
			}
			break;

		default:
		case 'check':
			echo '<h2>Check changes</h2>',
				'<p class="notice">This is a list of changes that will be written to the database. Please check them and remove those that you do not want from the files. When you are done checking you can run the log replay by pressing the button in the bottom right corner.</p>';
			bibliographie_admin_log_parse($gap);
?>

<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/admin/logReplay.php?task=replay">Start replay</a>
<?php
			break;
	}
}else
	echo '<p class="success">Nothing to do here. Database log and file log are up to date!</p>';
?>

		</div>
	</body>
</html>