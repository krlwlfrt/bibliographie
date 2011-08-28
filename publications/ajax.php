<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'checkTitle':
		$result = array(
			'count' => 0,
			'results' => array(),
			'status' => 'error'
		);

		if(mb_strlen($_GET['title']) >= 3){
			$result['status'] = 'success';

			$searchResults = mysql_query("SELECT * FROM (SELECT `pub_id`, `title`, (MATCH(`title`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['title']))."' IN NATURAL LANGUAGE MODE)) AS `relevancy` FROM `a2publication`) fullTextSearch WHERE `pub_id` != ".((int) $_GET['pub_id'])." AND `relevancy` > 0 ORDER BY `relevancy` DESC");

			$results = array();
			if(mysql_num_rows($searchResults) > 0){
				$result['count'] = mysql_num_rows($searchResults);
				while($publication = mysql_fetch_object($searchResults) and count($results) < ceil(log(mysql_num_rows($searchResults), 2) + 1) * 2){
					if(mb_strtolower($publication->title) == mb_strtolower($_GET['title']))
						$publication->title = '<strong>'.$publication->title.'</strong>';
					$results[] = $publication;
				}
				$result['results'] = $results;
			}
		}

		echo json_encode($result);
	break;

	case 'getFields':
		$result = array();
		if(array_key_exists(mb_strtolower($_GET['type']), $bibliographie_publication_fields)){
			foreach($bibliographie_publication_fields[mb_strtolower($_GET['type'])] as $flag => $fields){
				foreach($fields as $field)
					$result[] = array('field'=>$field,'flag'=>$flag);
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';