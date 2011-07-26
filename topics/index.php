<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Topics</h2>
<?php
switch($_GET['task']){
	case 'createTopic':
		$title = 'Create topic';
?>

<h3>Create topic</h3>
<?php
		$created = false;
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(empty($_POST['name']))
				$errors[] = 'You did not fill a name!';

			if(!empty($_POST['url']) and !is_url($_POST['url']))
				$errors[] = 'The URL you filled is not valid.';

			if(count($errors) == 0){
				if(bibliographie_topics_create_topic($_POST['name'], $_POST['description'], $_POST['url'])){
					echo '<p class="success">Topic has been created!</p>';
					$created = true;
				}else
					echo '<p class="error">Topic could not have been created. '.mysql_error().'</p>';
			}else
				foreach($errors as $error)
					echo '<p class="error">'.$error.'</p>';
		}

		if(!$created){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=createTopic'?>" method="post">
	<div class="unit">
		<label for="name" class="block">Name*</label>
		<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'])?>" style="width: 100%" />
	</div>
	<div class="unit">
		<label for="description" class="block">Description</label>
		<textarea id="description" name="description" rows="6" cols="40" style="width: 100%"><?php echo htmlspecialchars($_POST['description'])?></textarea>
	</div>
	<div class="unit">
		<label for="url" class="block">URL</label>
		<input type="text" id="url" name="url" value="<?php echo htmlspecialchars($_POST['url'])?>" style="width: 100%" />
	</div>
	<div class="submit">
		<input type="submit" value="save" />
	</div>
</form>
<?php
		}
	break;

	case 'showTopic':
		$topic = mysql_query("SELECT * FROM `a2topics` WHERE `topic_id` = ".((int) $_GET['topic_id']));
		if(mysql_num_rows($topic) == 1){
			$topic = mysql_fetch_object($topic);
			$title = 'Topic: '.htmlspecialchars($topic->name);

			$directPublications = mysql_num_rows(mysql_query("SELECT * FROM `a2topicpublicationlink` WHERE `topic_id` = ".((int) $topic->topic_id)));
			$indirectPublications = (int) 0;

			$subtopicsArray = bibliographie_topics_get_subtopics($topic->topic_id);
			if(count($subtopicsArray) > 0){
				$mysqlString = '';
				foreach($subtopicsArray as $subtopic){
					if(!empty($mysqlString))
						$mysqlString .= " OR ";

					$mysqlString .= "`topic_id` = ".((int) $subtopic);
				}
				$indirectPublications = mysql_num_rows(mysql_query("SELECT * FROM `a2topicpublicationlink` WHERE ".$mysqlString));
			}

?>

<h3>Topic: <?php echo htmlspecialchars($topic->name)?></h3>
<ul>
	<li><a href="">Show publications (<?php echo $directPublications?>)</a></li>
	<li><a href="">Show publications in subtopics (<?php echo $indirectPublications?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showGraph&topic_id=<?php echo $topic->topic_id?>">Show subgraph</a></li>
</ul>
<?php
		}
	break;

	case 'showGraph':
	default:
		$bibliographie_topics_graph_depth = (int) 1;

		$top = (int) 1;
		$cacheFile = BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph.json';
		$title = 'Topic graph';
		if(!empty($_GET['topic_id']) and is_numeric($_GET['topic_id']) and $_GET['topic_id'] != '1'){
			$topic = mysql_query("SELECT * FROM `a2topics` WHERE `topic_id` = ".((int) $_GET['topic_id']));
			if(mysql_num_rows($topic) == 1){
				$topic = mysql_fetch_object($topic);

				$top = (int) $_GET['topic_id'];
				$cacheFile = BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph_'.$top.'.json';
				$title = 'Topic subgraph for <em>'.$topic->name.'</em>';
			}
		}

		echo '<span style="float: right"><a href="javascript:;" onclick="bibliographie_topics_toggle_visiblity_of_all(true)">Open</a> '.
			'<a href="javascript:;" onclick="bibliographie_topics_toggle_visiblity_of_all(false)">Close</a> '.
			'all subtopics</span>';
		echo '<h3>'.$title.'</h3>';

		echo '<div class="bibliographie_topics_topic_graph">';
		bibliographie_topics_traverse($top);
		echo '</div>';

		echo '<p>depth: '.$bibliographie_topics_graph_depth.'</p>';
	break;
}
?>

<script type="text/javascript">
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

function bibliographie_topics_toggle_visiblity_of_all (expand) {
	if(expand == true){
		$('.topic_subtopics').show();
		$('.topic span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}else{
		$('.topic_subtopics').hide();
		$('.topic span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}
}
	/* <![CDATA[ */
</script>
<?php

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';