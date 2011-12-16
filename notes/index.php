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
		$bibliographie_title = 'List of notes';

		$publicationsWithNotes = DB::getInstance()->prepare('SELECT
	`pub_id`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
WHERE
	`user_id` = :user_id
GROUP BY
	`pub_id` DESC');
		$publicationsWithNotes->execute(array(
			'user_id' => (int) bibliographie_user_get_id()
		));

		if($publicationsWithNotes->rowCount() > 0){
			$publicationsWithNotes = $publicationsWithNotes->fetchAll(PDO::FETCH_COLUMN, 0);
			$publicationsWithNotes = bibliographie_publications_sort($publicationsWithNotes, 'title');
?>

<table class="dataContainer">
	<tr>
		<th>Publication</th>
		<th>Notes</th>
	</tr>
<?php
			foreach($publicationsWithNotes as $pub_id){
?>

	<tr>
		<td><?php echo bibliographie_publications_parse_title($pub_id)?></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}else
			echo '<p class="notice">You do not have any notes!</p>';
	break;
}

require dirname(__FILE__).'/../close.php';