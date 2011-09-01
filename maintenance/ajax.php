<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

$text = 'An error occurred!';
$status = 'error';
switch($_GET['task']){
	case 'consistencyChecks':
		switch($_GET['consistencyCheckID']){
			case 'authors_charsetArtifacts':
				$result = mysql_query("SELECT * FROM `a2author` WHERE CONCAT(`firstname`, `von`, `surname`, `jr`) NOT REGEXP '^([abcdefghijklmnopqrstuvwxyzäöüßáéíóúàèìòù[.full-stop.][.\'.][.hyphen.][.space.]]*)\$'");

				if(mysql_num_rows($result) > 0){
					echo '<strong style="display: block;">Found '.mysql_num_rows($result).' authors...</strong>';
					while($person = mysql_fetch_object($result))
						echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.$person->author_id.'">'.$person->von.' '.$person->surname.' '.$person->jr.', '.$person->firstname.'</a><br />';
				}
			break;
			case 'topics_loosenedSubgraphs':
				$result = mysql_query("SELECT * FROM `a2topics` WHERE `topic_id` NOT IN (SELECT `source_topic_id` FROM `a2topictopiclink`)");

				while($topic = mysql_fetch_object($result))
					echo $topic->name.'<br />';
			break;
		}
	break;
	case 'unlockTopic':
		$result = bibliographie_maintenance_unlock_topic($_GET['topic_id']);
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