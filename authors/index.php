<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
require dirname(__FILE__).'/authors.php';

?>

<h2>Authors</h2>
<?php

switch($_GET['task']){
	case 'showList':
?>

<h3>List of authors</h3>
<?php
		$authors = mysql_query("SELECT * FROM `a2author` ORDER BY `surname`, `firstname`");
		if(mysql_num_rows($authors) > 0){
?>

<table class="dataContainer">
	<tr>
		<th>Surname</th>
		<th>Firstname</th>
	</tr>
<?php
			while($author = mysql_fetch_object($authors)){
				$author->surname = '<strong>'.$author->surname.'</strong>';

				if(!empty($author->von))
					$author->surname = $author->von.' '.$author->surname;

				if(!empty($author->jr))
					$author->surname = $author->surname.' '.$author->jr;
?>

	<tr>
		<td><?php echo $author->surname?></td>
		<td><?php echo $author->firstname?></td>
	</tr>
<?php
			}
			echo '</table>';
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';