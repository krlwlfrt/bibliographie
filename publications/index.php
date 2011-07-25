<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'showPublication':
	default:
		echo bibliographie_publications_parse_data(27324, 'standard');
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';