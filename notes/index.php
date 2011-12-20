<?php
require dirname(__FILE__).'/../init.php';

$bibliographie_title = 'Notes';
?>

<h2>Notes</h2>
<?php
switch($_GET['task']){
	case 'showNotes':
?>

<h3>List of publications with notes</h3>
<?php
		bibliographie_history_append_step('notes', 'List of notes');

		$publicationsWithNotes = bibliographie_publications_sort(bibliographie_notes_get_publications_with_notes(), 'title');

		if(count($publicationsWithNotes) > 0){
			foreach($publicationsWithNotes as $pub_id){
				$notes = bibliographie_notes_get_notes_of_publication($pub_id);
				foreach($notes as $note)
					echo bibliographie_notes_print_note($note->note_id);
			}
		}else
			echo '<p class="notice">You do not have any notes!</p>';
	break;
}

require dirname(__FILE__).'/../close.php';