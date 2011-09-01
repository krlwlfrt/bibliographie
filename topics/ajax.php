<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

$title = 'An error occured!';
$text = 'An error occured!';
$status = 'error';
switch($_GET['task']){
	case 'getSubgraph':
		$topic = bibliographie_topics_get_topic_data($_GET['topic_id']);
		if(is_object($topic)){
			ob_clean();
			$walkedBy = array();
			bibliographie_topics_traverse($topic->topic_id, 1, $walkedBy, 'select');
			$text = '<div class="bibliographie_topics_topic_graph">'.ob_get_clean().'</div>';
			$text .= "<script type=\"text/javascript\">
	/* <![CDATA[ */
function bibliographie_topics_toggle_visibility_of_subtopics (topic_id, repeat_id) {
	if($('#topic_'+topic_id+'_'+repeat_id+'_subtopics').is(':visible')){
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').hide();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}else{
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').show();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}
}
	/* ]]> */
</script>";

			$title = 'Topic subgraph for '.htmlspecialchars($topic->name);
			ob_start();
		}

		echo bibliographie_dialog_create('selectFromTopicSubgraph', $title, $text);
	break;

	case 'searchTopics':
		$result = array();

		if(mb_strlen($_GET['query']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			$topics = mysql_query("SELECT * FROM (SELECT `topic_id`, `name`, (MATCH(`name`, `description`) AGAINST ('".mysql_real_escape_string(stripslashes(bibliographie_search_expand_query($_GET['query'])))."')) AS `relevancy` FROM `a2topics`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC");

			if(mysql_num_rows($topics) > 0){
				while($topic = mysql_fetch_object($topics))
					$result[] = array('id' => $topic->topic_id, 'name' => $topic->name);
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';