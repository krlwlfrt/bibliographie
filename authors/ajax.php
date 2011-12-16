<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

$text = (string) 'An error occured!';
$status = (string) 'error';

switch($_GET['task']){
	case 'deletePersonConfirm':
		$person = bibliographie_authors_get_data($_GET['author_id']);

		if(is_object($person)){
			$publications = array_unique(array_merge(bibliographie_authors_get_publications($person->author_id, false), bibliographie_authors_get_publications($person->author_id, true)));
			if(count($publications) == 0){
				$text = 'You are about to delete <em>'.bibliographie_authors_parse_data($person->author_id).'</em>. If you are sure, click "delete" below!'
					.'<p class="success"><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=deleteAuthor&amp;author_id='.((int) $person->author_id).'">'.bibliographie_icon_get('user-delete').' Delete!</a></p>'
					.'If you dont want to delete the person, press "cancel" below!';
			}else
				$text = '<p class="error"><em>'.bibliographie_authors_parse_data($person->author_id).'</em> has '.count($publications).' publications and can therefore not be deleted!</p>';
		}

		bibliographie_dialog_create('deletePersonConfirm_'.((int) $_GET['author_id']), 'Confirm delete', $text);
	break;

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
			'name' => $name
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

		$searchAuthors = bibliographie_authors_search_authors($_GET['q']);
		if(count($searchAuthors) > 0){
			foreach($searchAuthors as $author)
				$result[] = array (
					'id' => $author->author_id,
					'name' => bibliographie_authors_parse_data($author->author_id)
				);
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';