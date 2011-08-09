<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'unlockTopic':
		$result = bibliographie_maintenance_unlock_topic($_GET['topic_id']);
		$text = 'The topic could not be unlocked!';
		if($result)
			$text = 'The topic has been unlocked!';

		echo json_encode(array(
			'text' => $text
		));
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';