<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

$title = 'An error occured!';
$text = 'An error occured!';
$status = 'error';
switch($_GET['task']){
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

		if(mb_strlen($_GET['query']) >= 1){
			$topics = DB::getInstance()->prepare('SELECT `id`, `name`, `relevancy` FROM (
	SELECT
		`topic_id` AS `id`,
		`name`,
		MATCH(
			`name`,
			`description`
		) AGAINST (
			:expanded_query
		) AS `relevancy`
	FROM
		`a2topics`
) fullTextSearch
WHERE
	`relevancy` > 0 OR
	`name` LIKE "%'.trim(DB::getInstance()->quote($_GET['query']), '\'').'%"
ORDER BY
	`relevancy` DESC,
	LENGTH(`name`) ASC,
	`name`');
			$topics->setFetchMode(PDO::FETCH_OBJ);
			$topics->execute(array(
				'expanded_query' => bibliographie_search_expand_query($_GET['query'])
			));

			if($topics->rowCount() > 0){
				$_result = $topics->fetchAll();

				foreach($_result as $key => $row){
					if(mb_strlen($row->name) > mb_strlen($_GET['query']) + 5 and $row->relevancy < 1)
						break;

					$result[$key] = array (
						'id' => (int) $row->id,
						'name' => htmlspecialchars($row->name).' ('.$row->relevancy.')',
						'subtopics' => count(bibliographie_topics_get_subtopics($row->id, false))
					);
				}
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

			$similarTitles = DB::getInstance()->prepare("SELECT * FROM (
	SELECT `topic_id`, `name`, `description`, (`searchRelevancy` * 10 - (ABS(LENGTH(`name`) - LENGTH(:name) / 2))) AS `relevancy`  FROM (
		SELECT `topic_id`, `name`, `description`, (MATCH(`name`, `description`) AGAINST (:name IN NATURAL LANGUAGE MODE)) AS `searchRelevancy`
		FROM `a2topics`
		WHERE `topic_id` != :topic_id
	) fullTextSearch
) calculatedRelevancy
WHERE
	`relevancy` > 0
ORDER BY
	`relevancy` DESC
LIMIT
	100");

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