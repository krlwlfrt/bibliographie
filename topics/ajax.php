<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
require '../init.php';

$title = 'An error occured!';
$text = 'An error occured!';
$status = 'error';
switch($_GET['task']){
	case 'deleteTopicConfirm':
		$topic = bibliographie_topics_get_data($_GET['topic_id']);

		if(is_object($topic)){
			$parentTopics = bibliographie_topics_get_parent_topics($topic->topic_id);
			$subTopics = bibliographie_topics_get_subtopics($topic->topic_id);

			if(count($parentTopics) == 0 and count($subTopics) == 0){
				$text = 'You are about to delete <em>'.bibliographie_topics_parse_name($topic->topic_id).'</em>. If you are sure, click "delete" below!'
					.'<p class="success"><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=deleteTopic&amp;topic_id='.((int) $topic->topic_id).'">'.bibliographie_icon_get('folder-delete').' Delete!</a></p>'
					.'If you dont want to delete the topic, press "cancel" below!';
			}else
				$text = '<p class="error"><em>'.bibliographie_topics_parse_name($topic->topic_id).'</em> has parent- or subtopics and can therefore not be deleted!</p>';
		}

		bibliographie_dialog_create('deleteTopicConfirm_'.((int) $_GET['topic_id']), 'Confirm delete', $text);
	break;

	case 'getSubgraph':
		$topic = bibliographie_topics_get_data($_GET['topic_id']);
		if(is_object($topic)){
			ob_clean();
			$walkedBy = array();
			bibliographie_topics_traverse($topic->topic_id, 1, $walkedBy, 'select');
			$text = '<div class="bibliographie_topics_topic_graph">'.ob_get_clean().'</div>';
			$title = 'Topic subgraph for '.htmlspecialchars($topic->name);
			ob_start();
		}

		echo bibliographie_dialog_create('selectFromTopicSubgraph', $title, $text);
	break;

	case 'searchTopics':
		$result = array();

		$searchTopics = bibliographie_topics_search_topics($_GET['query']);
		if(count($searchTopics) > 0){
			foreach($searchTopics as $topic){
				if(mb_strlen($topic->name) > mb_strlen($_GET['query']) + 5 and $topic->relevancy < 1)
					break;

				$result[] = array (
					'id' => (int) $topic->topic_id,
					'name' => bibliographie_topics_parse_name($topic->topic_id),
					'subtopics' => count(bibliographie_topics_get_subtopics($topic->topic_id, false))
				);
			}
		}

		echo json_encode($result);
	break;

	case 'checkName':
		$result = array(
			'count' => 0,
			'results' => array(),
			'status' => 'error'
		);

		if(mb_strlen($_GET['name']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			$result['status'] = 'success';

			$expandedName = $_GET['name'];

			$topic_id = 0;
			if(is_numeric($_GET['topic_id']))
				$topic_id = (int) $_GET['topic_id'];

			$similarTitles = DB::getInstance()->prepare('SELECT * FROM (
	SELECT `topic_id`, `name`, `description`, (`searchRelevancy` * 10 - (ABS(LENGTH(`name`) - LENGTH(:name) / 2))) AS `relevancy`  FROM (
		SELECT `topic_id`, `name`, `description`, (MATCH(`name`, `description`) AGAINST (:name IN NATURAL LANGUAGE MODE)) AS `searchRelevancy`
		FROM `'.BIBLIOGRAPHIE_PREFIX.'topics`
		WHERE `topic_id` != :topic_id
	) fullTextSearch
) calculatedRelevancy
WHERE
	`relevancy` > 0
ORDER BY
	`relevancy` DESC
LIMIT
	100');

			$similarTitles->bindParam('name', $expandedName);
			$similarTitles->bindParam('topic_id', $topic_id);
			$similarTitles->execute();

			$results = array();
			$result['count'] = $similarTitles->rowCount();

			if($result['count'] > 0){
				$similarTitles->setFetchMode(PDO::FETCH_OBJ);
				$result['results'] = $similarTitles->fetchAll();
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';