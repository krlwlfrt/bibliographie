<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

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

		if(mb_strlen($_GET['query']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			$topics = _mysql_query("SELECT * FROM (SELECT `topic_id`, `name`, (MATCH(`name`, `description`) AGAINST ('".mysql_real_escape_string(stripslashes(bibliographie_search_expand_query($_GET['query'])))."')) AS `relevancy` FROM `a2topics`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC");

			if(mysql_num_rows($topics) > 0){
				while($topic = mysql_fetch_object($topics))
					$result[] = array (
						'id' => $topic->topic_id,
						'name' => $topic->name,
						'subtopics' => count(bibliographie_topics_get_subtopics($topic->topic_id, false))
					);
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';