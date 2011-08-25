<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'searchAuthors':
		$result = array();
		if(mb_strlen($_GET['q']) >= 3){
			$authors = mysql_query("SELECT * FROM `a2author` WHERE `firstname` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' OR `surname` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' ORDER BY `surname`, `firstname`");
			if(mysql_num_rows($authors))
				while($author = mysql_fetch_object($authors)){
					$result[] = array (
						'id' => $author->author_id,
						'name' => bibliographie_authors_parse_data($author)
					);
				}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';