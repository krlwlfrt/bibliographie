<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require dirname(__FILE__).'/../init.php';

$text = 'An error occurred!';
$status = 'error';
switch($_GET['task']){
	case 'deleteNoteConfirm':
		$note = bibliographie_notes_get_data($_GET['note_id']);

		if(is_object($note)){
			if($note->user_id == bibliographie_user_get_id()){
				$text = 'You are about to delete your note. If you are sure, click "delete" below!'
					.'<p class="success"><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/notes/?task=deleteNote&amp;note_id='.((int) $note->note_id).'">'.bibliographie_icon_get('note_delete').' Delete!</a></p>'
					.'If you dont want to delete the note, press "cancel" below!';
			}
		}

		bibliographie_dialog_create('deleteNoteConfirm_'.((int) $_GET['note_id']), 'Confirm delete', $text);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';