<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

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
		if(mb_strlen($_GET['q']) >= 3){
			$tags = mysql_query("SELECT * FROM `a2tags` WHERE `tag` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' ORDER BY `tag`");
			if(mysql_num_rows($tags))
				while($tag = mysql_fetch_object($tags)){
					$result[] = array (
						'id' => $tag->tag_id,
						'name' => $tag->tag
					);
				}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';