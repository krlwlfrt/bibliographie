<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'getFields':
		$result = array();
		if(array_key_exists(mb_strtolower($_GET['type']), $bibliographie_publication_fields)){
			foreach($bibliographie_publication_fields[mb_strtolower($_GET['type'])] as $flag => $fields){
				foreach($fields as $field)
					$result[] = array('field'=>$field,'flag'=>$flag);
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';