<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

$text = 'An error occurred!';
$status = 'error';
switch($_GET['task']){
	case 'consistencyChecks':
		switch($_GET['consistencyCheckID']){
			case 'authors_charsetArtifacts':
				$result = _mysql_query("SELECT * FROM `a2author` WHERE CONCAT(`firstname`, `von`, `surname`, `jr`) NOT REGEXP '^([abcdefghijklmnopqrstuvwxyzäöüßáéíóúàèìòùç[.full-stop.][.\'.][.hyphen.][.space.]]*)\$' ORDER BY `surname`, `firstname`");

				if(mysql_num_rows($result) > 0){
					echo '<strong style="display: block;">Found '.mysql_num_rows($result).' authors...</strong>';
					while($person = mysql_fetch_object($result))
						echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.((int) $person->author_id).'">'.htmlspecialchars($person->von.' '.$person->surname.' '.$person->jr.', '.$person->firstname).'</a><br />';
				}else
					echo '<p class="success">No authors with charset artifacts.</p>';
			break;

			case 'publications_withoutTopic':
				$result = _mysql_query("SELECT `pub_id` FROM `a2publication` WHERE `pub_id` NOT IN (SELECT `pub_id` FROM `a2topicpublicationlink`)");

				if(mysql_num_rows($result) > 0){
					$publications = array();
					while($publication = mysql_fetch_object($result))
						$publications[] = $publication->pub_id;

					bibliographie_publications_print_list($publications, '', null, false);
				}
			break;

			case 'publications_withoutTag':
				$result = _mysql_query("SELECT `pub_id` FROM `a2publication` WHERE `pub_id` NOT IN (SELECT `pub_id` FROM `a2publicationtaglink`)");

				if(mysql_num_rows($result) > 0){
					$publications = array();
					while($publication = mysql_fetch_object($result))
						$publications[] = $publication->pub_id;

					bibliographie_publications_print_list($publications, '', null, false);
				}
			break;

			case 'topics_loosenedSubgraphs':
				$result = _mysql_query("SELECT `topic_id`, `name` FROM `a2topics` WHERE `topic_id` NOT IN (SELECT `source_topic_id` FROM `a2topictopiclink`) AND `topic_id` != 1");

				if(mysql_num_rows($result) > 0){
					while($topic = mysql_fetch_object($result))
						echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $topic->topic_id).'">'.htmlspecialchars($topic->name).'<br />';
				}else
					echo '<p class="success">No loosened graphs!</p>';
			break;

			case 'topics_doubledNames':
				$result = _mysql_query("SELECT * FROM (SELECT *, COUNT(*) AS `count` FROM `a2topics` GROUP BY `name`) counts WHERE `count` > 1 ORDER BY `name`");

				if(mysql_num_rows($result)){
					while($topic = mysql_fetch_object($result)){
						echo $topic->name.' '.$topic->count.'<br />';
					}
				}
			break;
		}
	break;
	case 'unlockTopic':
		$result = bibliographie_maintenance_unlock_topic($_GET['topic_id']);
		$text = 'The topic could not be unlocked!';
		if($result){
			$text = 'The topic has been unlocked!';
			$status = 'success';
		}

		echo json_encode(array(
			'status' => $status,
			'text' => $text
		));
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';