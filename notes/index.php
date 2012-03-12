<?php
require dirname(__FILE__).'/../init.php';

$bibliographie_title = 'Notes';
?>

<h2>Notes</h2>
<?php
switch($_GET['task']){
	case 'deleteNote':
		$note = bibliographie_notes_get_data($_GET['note_id']);

		if(is_object($note)){
			if($note->user_id == bibliographie_user_get_id()){
				echo '<h3>Deleting note</h3>';
				if(bibliographie_notes_delete_note($note->note_id))
					echo '<p class="success">The note was deleted!</p>';
				else
					echo '<p class="error">An error occurred!</p>';

				break;
			}
		}

		echo '<h3>Error</h3><p class="error">Either this isn\'t your note or no note with this ID exists!</p>';
	break;

	case 'noteEditor':
		bibliographie_history_append_step('notes', 'Note editor');

		$note = bibliographie_notes_get_data($_GET['note_id']);
		$publication = null;

		if(!is_object($note)){
			$publication = bibliographie_publications_get_data($_GET['pub_id']);
			if(is_object($publication)){
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					if(!empty($_POST['text'])){
						if(bibliographie_notes_create_note($publication->pub_id, $_POST['text'])){
							echo '<p class="success">Your note was created successfully!</p>';
							echo 'You can now <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&amp;pub_id='.((int) $publication->pub_id).'">return to the publication</a>.';
							break;
						}else
							echo '<p class="error">An error occurred!</p>';
					}
				}

				echo '<p class="notice">Your about to add a note to the following publication:</p>'.bibliographie_publications_print_list(array($publication->pub_id));
				echo '<form action="'.BIBLIOGRAPHIE_WEB_ROOT.'/notes/?task=noteEditor&amp;pub_id='.((int) $publication->pub_id).'" method="post">';
			}
		}elseif(is_object($note)){
			$publication = bibliographie_publications_get_data($note->pub_id);
			if(is_object($publication)){
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					if(!empty($_POST['text'])){
						if(bibliographie_notes_edit_note($note->note_id, $_POST['text'])){
							echo '<p class="success">Your note was edited successfully!</p>';
							echo 'You can now <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&amp;pub_id='.((int) $publication->pub_id).'">return to the publication</a>.';
							break;
						}else
							echo '<p class="error">An error occurred!</p>';
					}
				}

				echo '<p class="notice">Your about to edit a note to the following publication:</p>'.bibliographie_publications_print_list(array($publication->pub_id));
				echo '<form action="'.BIBLIOGRAPHIE_WEB_ROOT.'/notes/?task=noteEditor&amp;note_id='.((int) $note->note_id).'" method="post">';
				$_POST = (array) $note;
			}
		}

		if(is_object($publication)){
?>

	<div class="unit">
		<label for="text" class="block">Note</label>
		<textarea id="text" name="text" rows="10" cols="10" style="width: 100%"><?php echo htmlspecialchars($_POST['text'])?></textarea>
	</div>

	<div class="submit"><input type="submit" value="save" /></div>
</form>
<?php
		}
	break;

	default:
	case 'showNotes':
?>

<h3>List of publications with notes</h3>
<?php
		bibliographie_history_append_step('notes', 'List of notes');

		$publicationsWithNotes = bibliographie_publications_sort(bibliographie_notes_get_publications_with_notes(), 'title');

		if(count($publicationsWithNotes) > 0){
			foreach($publicationsWithNotes as $pub_id){
				$notes = bibliographie_publications_get_notes($pub_id);
				foreach($notes as $note)
					echo bibliographie_notes_print_note($note->note_id);
			}
		}else
			echo '<p class="notice">You do not have any notes!</p>';
	break;
}

require dirname(__FILE__).'/../close.php';