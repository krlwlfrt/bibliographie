<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'searchTags':
		$result = array();
		if(mb_strlen($_GET['q']) >= 3){
			$tags = mysql_query("SELECT * FROM `a2keywords` WHERE `keyword` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' ORDER BY `keyword`");
			if(mysql_num_rows($tags))
				while($tag = mysql_fetch_object($tags)){
					$result[] = array (
						'id' => $tag->keyword_id,
						'name' => $tag->keyword
					);
				}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';