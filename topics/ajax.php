<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

$title = 'An error occured!';
$text = 'An error occured!';
$status = 'error';
switch($_GET['task']){
	case 'getSubgraph':
		$topic = bibliographie_topics_get_topic_data($_GET['topic_id']);
		if(is_object($topic)){
			ob_clean();
			$walkedBy = array();
			bibliographie_topics_traverse($topic->topic_id, 1, $walkedBy, 'select');
			$text = '<div class="bibliographie_topics_topic_graph">'.ob_get_clean().'</div>';
			$text .= "<script type=\"text/javascript\">
	/* <![CDATA[ */
function bibliographie_topics_toggle_visibility_of_subtopics (topic_id, repeat_id) {
	if($('#topic_'+topic_id+'_'+repeat_id+'_subtopics').is(':visible')){
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').hide();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}else{
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').show();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}
}
	/* ]]> */
</script>";

			$title = 'Topic subgraph for '.htmlspecialchars($topic->name);
			ob_start();
		}

		echo bibliographie_dialog_create('selectFromTopicSubgraph', $title, $text);
	break;
	case 'searchTopicJSON':
		$result = array();
		if(mb_strlen($_GET['q']) >= 3){
			$topics = mysql_query("SELECT * FROM `a2topics` WHERE `name` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' ORDER BY `name`");

			if(mysql_num_rows($topics) > 0)
				while($topic = mysql_fetch_object($topics))
					$result[] = array('id' => $topic->topic_id, 'name' => $topic->name);
		}

		echo json_encode($result);
	break;

	case 'searchTopic':
		if(in_array($_GET['id'], array('searchTopicOne', 'searchTopicTwo'))){
			$topics = mysql_query("SELECT * FROM `a2topics` WHERE `name` LIKE '%".mysql_real_escape_string(stripslashes($_GET['value']))."%' ORDER BY `name`");
			if(mysql_num_rows($topics) > 0 and !empty($_GET['value'])){
?>

<label for="select_<?php echo $_GET['id']?>" class="block"><?php echo mysql_num_rows($topics)?> Result(s)</label>
<select id="select_<?php echo $_GET['id']?>" name="select_<?php echo $_GET['id']?>" style="width: 100%">
	<option value="0">Please choose...</option>
<?php
			while($topic = mysql_fetch_object($topics)){
				if($topic->topic_id == 1){
					$topic->name .= ' (Symbolic head of graph!)';
				}
?>

	<option value="<?php echo $topic->topic_id?>"><?php echo htmlspecialchars($topic->name)?></option>
<?php
			}
?>

</select>
<?php
			}else
				echo '<p class="error">Sorry, your search did not give any results!</p>';
		}
	break;
	default:
		echo 'WORKING';
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';