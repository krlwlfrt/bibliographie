<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

$text = (string) 'An error occured!';
$status = (string) 'error';

switch($_GET['task']){
	case 'createTag':
		$tag_id = (int) 0;
		$tag = (string) '';

		if(!empty($_GET['tag'])){
			$data = bibliographie_tags_create_tag($_GET['tag']);
			if(is_array($data) and is_numeric($data['tag_id'])){
				$text = 'Tag has been created!';
				$status = 'success';
				$tag_id = $data['tag_id'];
				$tag = $data['tag'];
			}
		}else
			$text = 'You have to fill a tag to create one!';

		echo json_encode(array(
			'text' => $text,
			'status' => $status,
			'tag_id' => $tag_id,
			'tag' => $tag
		));
	break;

	case 'searchTags':
		$result = array();

		if(mb_strlen($_GET['q']) >= 1){
			$tags = DB::getInstance()->prepare('SELECT
	`id`,
	`tag`,
	`relevancy`
FROM (
	SELECT
		`tag_id` AS `id`,
		`tag`,
		MATCH(`tag`) AGAINST (:query) AS `relevancy`
	FROM
		`a2tags`
) fullTextSearch
WHERE
	`relevancy` > 0 OR
	`tag` LIKE "%'.trim(DB::getInstance()->quote($_GET['q']), '\'').'%"
ORDER BY
	`relevancy` DESC,
	LENGTH(`tag`) ASC,
	`tag`
LIMIT
	50');
			$tags->setFetchMode(PDO::FETCH_OBJ);
			$tags->execute(array(
				'query' => bibliographie_search_expand_query($_GET['q'])
			));

			if($tags->rowCount() > 0){
				$_result = $tags->fetchAll();
				foreach($_result as $row)
					$result[] = array (
						'id' => $row->id,
						'name' => $row->tag
					);
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';