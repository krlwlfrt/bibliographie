<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'showPublication':
		$publication = mysql_query("SELECT * FROM `a2publication` WHERE `pub_id` = ".((int) $_GET['pub_id']));

		if(mysql_num_rows($publication) == 1){
			$publication = mysql_fetch_object($publication);
?>

<h3><?php echo htmlspecialchars($publication->title)?></h3>
<?php
			echo bibliographie_publications_parse_data($publication->pub_id);
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';