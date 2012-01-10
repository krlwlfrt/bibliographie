<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require dirname(__FILE__).'/../init.php';

$text = 'An error occurred!';
$status = 'error';
switch($_GET['task']){
	case 'unlockTopic':
		$result = bibliographie_admin_unlock_topic($_GET['topic_id']);
		$text = 'The topic could not be unlocked!';
		if($result){
			$text = 'The topic has been unlocked!';
			$status = 'success';
		}

		echo json_encode(array(
			'status' => $status,
			'text' => $text
		));
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';