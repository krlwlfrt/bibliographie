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
						else{
							$precheck = false;
							$precheckError = 'Authors current data is unequal to the logged state before.';
						}

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
						if(count(array_diff(bibliographie_authors_get_publications($delete->author_id), csv2array($data->publications)) == 0)
							and is_object($into)
							and is_object($delete))
							if(count(csv2array($data->publications)) == $data->publicationsAffected)
								$result = bibliographie_maintenance_merge_authors($data->into, $data->delete);
							else{
								$precheck = false;
								$precheckError = 'Publications that were affected by the merge, were unequal to the publications that should be affected!';
							}
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

				}elseif($row->category == 'publications'){
					if($row->action == 'createPublication')
						$result = bibliographie_publications_create_publication($data->pub_type, $data->author, $data->editor, $data->title, $data->month, $data->year, $data->booktitle, $data->chapter, $data->series, $data->journal, $data->volume, $data->number, $data->edition, $data->publisher, $data->location, $data->howpublished, $data->organization, $data->institution, $data->school, $data->address, $data->pages, $data->note, $data->abstract, $data->userfields, $data->bibtex_id, $data->isbn, $data->issn, $data->doi, $data->url, $data->topics, $data->tags, $data->pub_id, $data->user_id);

					elseif($row->action == 'editPublication'){
						$publication = bibliographie_publications_get_data($data->pub_id);

						if(is_object($publication))
							$result = bibliographie_publications_edit_publication ($data->pub_id, $data->pub_type, $data->author, $data->editor, $data->title, $data->month, $data->year, $data->booktitle, $data->chapter, $data->series, $data->journal, $data->volume, $data->number, $data->edition, $data->publisher, $data->location, $data->howpublished, $data->organization, $data->institution, $data->school, $data->address, $data->pages, $data->note, $data->abstract, $data->userfields, $data->bibtex_id, $data->isbn, $data->issn, $data->doi, $data->url, $data->topics, $data->tags);
						else
							$precheck = false;

					}elseif($row->action == 'addTopic'){
						$topic = bibliographie_topics_get_data($data->topic_id);
						$publications = bibliographie_topics_get_publications($data->topic_id);

						if(is_object($topic) and array_diff($publications, $data->publicationsBefore) == 0)
							$result = bibliographie_publications_add_topic($data->publicationsToAdd, $data->topic_id);
						else
							$precheck = false;

					}elseif($row->action == 'addTag'){
						$tag = bibliographie_tags_get_data($data->tag_id);
						$publications = bibliographie_tags_get_publications($data->tag_id);
						if(is_object($tag) and array_diff($publications, $data->publicationsBefore) == 0)
							$result = bibliographie_publications_add_tag($data->publicationsToAdd, $data->tag_id);
						else
							$precheck = false;

					}elseif($row->action == 'removeTopic'){
						$topic = bibliographie_topics_get_data($data->topic_id);
						$publications = bibliographie_topics_get_publications($data->topic_id);
						if(is_object($topic) and array_diff($publications, $data->publicationsBefore) == 0)
							$result = bibliographie_publications_remove_topic($data->publicationsToRemove, $data->topic_id);
						else
							$precheck = false;

					}elseif($row->action == 'removeTag'){
						$tag = bibliographie_tags_get_data($data->tag_id);
						$publications = bibliographie_topics_get_publications($data->tag_id);
						if(is_object($tag) and array_diff($publications, $data->publicationsBefore) == 0)
							$result = bibliographie_publications_remove_tag($data->publicationsToRemove, $data->tag_id);
						else
							$precheck = false;

					}elseif($row->action == 'deletePublication'){
						$dataDeleted = $data->dataDeleted;
						$publication = bibliographie_publications_get_data($dataDeleted->pub_id);
						$publication->author = bibliographie_publications_get_authors($dataDeleted->pub_id);
						$publication->editor = bibliographie_publications_get_editors($dataDeleted->pub_id);
						$publication->topics = bibliographie_publications_get_topics($dataDeleted->pub_id);
						$publication->tags = bibliographie_publications_get_tags($dataDeleted->pub_id);

						if($publication == $dataDeleted and count(bibliographie_notes_get_notes_of_publication($dataDeleted->pub_id)) == 0)
							$result = bibliographie_publications_delete_publication($dataDeleted->pub_id);
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
					echo '<p class="error">#'.$row->id.': An error occurred while trying to apply logged change ('.$data->category.', '.$data->action.').</p>';
					break;
				}
				if($precheck === false){
					echo '<p class="error">#'.$row->id.': Data precheck was unsuccessfull.</p>';
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