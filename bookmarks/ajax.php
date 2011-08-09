<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'setBookmark':
		$text = 'An error occured!';
		if(bibliographie_bookmarks_set_bookmark($_GET['pub_id']))
			$text = bibliographie_bookmarks_print_html($_GET['pub_id']);

		echo $text;
	break;

	case 'unsetBookmark':
		$text = 'An error occured!';
		if(bibliographie_bookmarks_unset_bookmark($_GET['pub_id']))
			$text = bibliographie_bookmarks_print_html($_GET['pub_id']);

		echo $text;
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';