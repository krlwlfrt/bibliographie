<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

$text = (string) 'An error occured!';
$status = (string) 'error';

switch($_GET['task']){
	case 'createPerson':
		$author_id = (int) 0;
		$name = (string) '';

		if(!empty($_GET['firstname']) and !empty($_GET['surname'])){
			$data = bibliographie_authors_create_author($_GET['firstname'], $_GET['von'], $_GET['surname'], $_GET['jr'], '', '', '');
			if(is_array($data) and is_numeric($data['author_id'])){
				$status = 'success';
				$text = 'Author was created successfully!';
				$author_id = $data['author_id'];
				$name = bibliographie_authors_parse_data($data['author_id']);
			}
		}else
			$text = 'You have to fill at least first- and surname!';

		echo json_encode(array(
			'text' => $text,
			'status' => $status,
			'author_id' => $author_id,
			'name' => $name,
			'print_r' => print_r($_GET, true)
		));
	break;

	case 'createPersonForm':
		$text = '<label for="firstname" class="block">Firstname*</label>';
		$text .= '<input type="text" id="firstname" name="firstname" style="width: 100%" />';

		$text .= '<div style="float: right; width: 50%"><label for="jr" class="block">jr-part</label>';
		$text .= '<input type="text" id="jr" name="jr" style="width: 100%" /></div>';

		$text .= '<label for="von" class="block">von-part</label>';
		$text .= '<input type="text" id="von" name="von" style="width: 45%" />';

		$text .= '<label for="surname" class="block">Surname*</label>';
		$text .= '<input type="text" id="surname" name="surname" style="width: 100%" />';

		bibliographie_dialog_create('createPersonForm', 'Create person', $text);
	break;

	case 'searchAuthors':
		$result = array();
		if(mb_strlen($_GET['q']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			$options = array('suffixes' => true, 'plurals' => false, 'umlauts' => true);
			
			$expandedQuery = bibliographie_search_expand_query($_GET['q'], $options);

			$authors = _mysql_query("SELECT * FROM (SELECT `author_id`, (MATCH(`surname`, `firstname`) AGAINST ('".mysql_real_escape_string(stripslashes($expandedQuery))."')) AS `relevancy` FROM `a2author`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC");

			if(mysql_num_rows($authors)){
				while($author = mysql_fetch_object($authors)){
					$result[] = array (
						'id' => $author->author_id,
						'name' => bibliographie_authors_parse_data($author->author_id)
					);
				}
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';