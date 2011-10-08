<?php
/* @var $db PDO */
require dirname(__FILE__).'/../init.php';

$bibliographie_title = 'Notes';
?>

<h2>Notes</h2>
<?php
switch($_GET['task']){
	case 'showNotes':
?>

<h3>List of notes</h3>
<p class="notice">This is a list of your notes sorted by currency.</p>
<?php
		bibliographie_history_append_step('notes', 'List of notes');
		$bibliographie_title = 'List of notes';

		$notes = $db->prepare('SELECT `note_id`, `pub_id`, `text` FROM `a2notes` WHERE `user_id` = :user_id ORDER BY `note_id` DESC');
		$notes->bindParam('user_id', bibliographie_user_get_id());
		$notes->execute();

		if($notes->rowCount() > 0){
			$notes->setFetchMode(PDO::FETCH_OBJ);
			$notesArray = $notes->fetchAll();

			foreach($notesArray as $note){
?>

<div id="bibliographie_note_<?php echo (int) $note->note_id?>" class="bibliographie_note">
	<?php echo $note->text?>
	<div class="bibliographie_note_publication_link"><?php echo bibliographie_publications_parse_title($note->pub_id, array('linkProfile' => true))?></div>
</div>
<?php
			}
		}else
			echo '<p class="notice">You do not have any notes!</p>';
	break;
}

require dirname(__FILE__).'/../close.php';