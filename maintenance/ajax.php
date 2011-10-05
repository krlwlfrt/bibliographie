<?php
/* @var $db PDO */
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

$text = 'An error occurred!';
$status = 'error';
switch($_GET['task']){
	case 'consistencyChecks':
		switch($_GET['consistencyCheckID']){
			case 'authors_charsetArtifacts':
				$authors = $db->prepare('SELECT `author_id` FROM `a2author` WHERE CONCAT(`firstname`, `von`, `surname`, `jr`) NOT REGEXP "^([abcdefghijklmnopqrstuvwxyzäöüßáéíóúàèìòùç[.full-stop.][.\'.][.hyphen.][.space.]]*)\$" ORDER BY `surname`, `firstname`');
				$authors->setFetchMode(PDO::FETCH_OBJ);
				$authors->execute();

				if($authors->rowCount() > 0){
					echo '<p class="error">Found '.$authors->rowCount().' authors with charset artifacts!</p>';
					$authorIDs = $authors->fetchAll(PDO::FETCH_COLUMN, 0);

					echo '<table class="dataContainer">';
					echo '<tr><th> </th><th>Name</th></tr>';

					foreach($authorIDs as $author_id){
						$author = bibliographie_authors_get_data($author_id);

						echo '<tr>';
						echo '<td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=authorEditor&amp;author_id='.((int) $author->author_id).'">'.bibliographie_icon_get('user-edit').'</a></td>';
						echo '<td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.((int) $author->author_id).'">'.bibliographie_authors_parse_data($author->author_id, array('linkProfile' => true)).'</a></td>';
						echo '</tr>';
					}

					echo '</table>';
				}else
					echo '<p class="success">No authors with charset artifacts.</p>';
			break;

			case 'authors_withoutPublications':
				$authorIDs = array();
				$relationIDs = array();

				$authors = $db->prepare('SELECT `author_id` FROM `a2author`');
				$authors->setFetchMode(PDO::FETCH_OBJ);
				$authors->execute();

				if($authors->rowCount() > 0)
					$authorIDs = $authors->fetchAll(PDO::FETCH_COLUMN, 0);

				$relations = $db->prepare("SELECT `author_id` FROM `a2publicationauthorlink` GROUP BY `author_id`");
				$relations->setFetchMode(PDO::FETCH_OBJ);
				$relations->execute();

				if($relations->rowCount() > 0)
					$relationIDs = $relations->fetchAll(PDO::FETCH_COLUMN, 0);

				$authorsWithoutPublications = array_values(array_diff($authorIDs, $relationIDs));

				if(count($authorsWithoutPublications) > 0){
					echo '<p class="error">Found <strong>'.count($authorsWithoutPublications).' authors without publications.</strong>';
					echo '<table class="dataContainer">';
					echo '<tr>';
					echo '<th style="width: 5%"></th>';
					echo '<th style="width: 95%">Name</th>';
					echo '</tr>';
					foreach($authorsWithoutPublications as $author_id){
						echo '<tr>';
						echo '<td>'.bibliographie_icon_get('user-delete').'</td>';
						echo '<td>'.bibliographie_authors_parse_data($author_id, array('linkProfile' => true)).'</td>';
						echo '</tr>';
					}
					echo '</table>';
				}else
					echo '<p class="success">No authors without publications were found.</p>';
			break;

			case 'publications_withoutTopic':
				$publicationsArray = array();
				$publicationLinksArray = array();

				$publications = $db->prepare("SELECT `pub_id` FROM `a2publication` GROUP BY `pub_id`");
				$publications->setFetchMode(PDO::FETCH_OBJ);
				$publications->execute();
				if($publications->rowCount() > 0)
					$publicationsArray = $publications->fetchAll(PDO::FETCH_COLUMN, 0);

				$publicationLinks = $db->prepare("SELECT `pub_id` FROM `a2topicpublicationlink` GROUP BY `pub_id`");
				$publicationLinks->setFetchMode(PDO::FETCH_OBJ);
				$publicationLinks->execute();
				if($publicationLinks->rowCount() > 0)
					$publicationLinksArray = $publicationLinks->fetchAll(PDO::FETCH_COLUMN, 0);

				$publicationsList = array_values(array_diff($publicationsArray, $publicationLinksArray));
				bibliographie_publications_print_list($publicationsList, '', null, false);
			break;

			case 'publications_withoutTag':
				$publicationsArray = array();

				$publications = $db->prepare('SELECT `pub_id` FROM `a2publication` WHERE `pub_id` NOT IN (SELECT `pub_id` FROM `a2publicationtaglink`)');
				$publications->setFetchMode(PDO::FETCH_OBJ);
				$publications->execute();

				if($publications->rowCount() > 0){
					$publicationsArray = $publications->fetchAll(PDO::FETCH_COLUMN, 0);
					bibliographie_publications_print_list($publicationsArray, '', null, false);
				}else
					echo '<p class="success">No publications without a tag assignment were found.';
			break;

			case 'topics_loosenedSubgraphs':
				$topicsArray = array();
				$topicLinksArray = array();

				$topics = $db->prepare('SELECT `topic_id` FROM `a2topics` WHERE `topic_id` != 1');
				$topics->execute();
				if($topics->rowCount() > 0)
					$topicsArray = $topics->fetchAll(PDO::FETCH_COLUMN, 0);

				$topicLinks = $db->prepare('SELECT `source_topic_id` FROM `a2topictopiclink`');
				$topicLinks->execute();
				if($topicLinks->rowCount() > 0)
					$topicLinksArray = $topicLinks->fetchAll(PDO::FETCH_COLUMN, 0);

				$topics = array_diff($topicsArray, $topicLinksArray);

				if(count($topics) > 0){
					echo '<p class="error">Found '.count($topics).' topics without parent topic!</p>';
					echo '<table class="dataContainer"><tr><th style="width: 5%"> </th><th>Name</th></tr>';

					foreach($topics as $topic)
						echo '<tr><td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=topicEditor&amp;topic_id='.((int) $topic).'">'.bibliographie_icon_get('folder-edit').'</td><td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $topic).'">'.bibliographie_topics_parse_name($topic, array('linkProfile' => true)).'</td></tr>';
					echo '</table>';
				}else
					echo '<p class="success">Did not find loosened graphs!</p>';
			break;

			case 'topics_doubledNames':
				$doubledTopicNames = $db->prepare("SELECT * FROM (
	SELECT *, COUNT(*) AS `count` FROM `a2topics` GROUP BY `name`
) counts
WHERE
	`count` > 1
ORDER BY
	`name`");
				$doubledTopicNames->execute();

				if($doubledTopicNames->rowCount() > 0){
					$doubledTopicNames->setFetchMode(PDO::FETCH_OBJ);
					$topics = $doubledTopicNames->fetchAll();

					echo '<p class="error">Found '.$doubledTopicNames->rowCount().' topics with doubled names.</p>';
					echo '<table class="dataContainer"><tr><th>Topic name</th> <th>Count</th></tr>';
					foreach($topics as $topic)
						echo '<tr><td>'.$topic->name.'</td><td>'.$topic->count.'</td></tr>';
					echo '</table>';
				}else
					echo '<p class="success">Found no doubled topic names.</p>';
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