<?php
/* @var $db PDO */
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

$text = 'An error occurred!';
$status = 'error';
switch($_GET['task']){
	case 'consistencyChecks':
		switch($_GET['consistencyCheckID']){
			case 'authors_charsetArtifacts':
				$authors = $db->prepare("SELECT * FROM `a2author`
WHERE
	CONCAT(`firstname`, `von`, `surname`, `jr`) NOT REGEXP '^([abcdefghijklmnopqrstuvwxyzäöüßáéíóúàèìòùç[.full-stop.][.\'.][.hyphen.][.space.]]*)\$'
ORDER BY
	`surname`,
	`firstname`");
				$authors->execute();
				$authors->setFetchMode(PDO::FETCH_OBJ);

				if($authors->rowCount() > 0){
					echo '<p class="error">Found '.$authors->rowCount().' authors with charset artifacts!</p>';

					echo '<table class="dataContainer">';
					echo '<tr><th> </th><th>Name</th></tr>';

					while($person = $authors->fetch()){
						echo '<tr>';
						echo '<td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=authorEditor&amp;author_id='.((int) $person->author_id).'">'.bibliographie_icon_get('user-edit').'</a></td>';
						echo '<td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.((int) $person->author_id).'">'.htmlspecialchars($person->von.' '.$person->surname.' '.$person->jr.', '.$person->firstname).'</a></td>';
						echo '</tr>';
					}

					echo '</table>';
				}else
					echo '<p class="success">No authors with charset artifacts.</p>';
			break;

			case 'publications_withoutTopic':
				$publicationsArray = array();
				$publicationLinksArray = array();

				$publications = $db->prepare("SELECT `pub_id` FROM `a2publication` GROUP BY `pub_id`");
				$publications->execute();
				$publications->setFetchMode(PDO::FETCH_OBJ);
				while($publication = $publications->fetch())
					$publicationsArray[] = $publication->pub_id;

				$publicationLinks = $db->prepare("SELECT `pub_id` FROM `a2topicpublicationlink` GROUP BY `pub_id`");
				$publicationLinks->execute();
				$publicationLinks->setFetchMode(PDO::FETCH_OBJ);
				while($publication = $publicationLinks->fetch())
					$publicationLinksArray[] = $publication->pub_id;

				$publicationsList = array_values(array_diff($publicationsArray, $publicationLinksArray));
				bibliographie_publications_print_list($publicationsList, '', null, false);
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
				$result = _mysql_query("SELECT `topic_id`, `name` FROM `a2topics` WHERE `topic_id` NOT IN (SELECT `source_topic_id` AS `topic_id` FROM `a2topictopiclink`) AND `topic_id` != 1");

				if(mysql_num_rows($result) > 0){
					while($topic = mysql_fetch_object($result))
						echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $topic->topic_id).'">'.htmlspecialchars($topic->name).'<br />';
				}else
					echo '<p class="success">No loosened graphs!</p>';
			break;

			case 'topics_doubledNames':
				$doubledNames = $db->prepare("SELECT * FROM (
	SELECT *, COUNT(*) AS `count` FROM `a2topics` GROUP BY `name`
) counts
WHERE
	`count` > 1
ORDER BY
	`name`");
				$doubledNames->execute();

				if($doubledNames->rowCount() > 0){
					$doubledNames->setFetchMode(PDO::FETCH_OBJ);
					echo '<table class="dataContainer">';
					echo '<tr><th>Topic name</th> <th>Count</th></tr>';

					while($topic = $doubledNames->fetch())
						echo '<tr><td>'.$topic->name.'</td><td>'.$topic->count.'</td></tr>';
					echo '</table>';
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