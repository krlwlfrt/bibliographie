<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Topics</h2>
<?php
switch($_GET['task']){
	case 'createRelation':
		$title = 'Create relation';
		$created = false;
?>

<h3>Create relation</h3>
<p class="notice">On this page you can create relations between topics. You just have to search for two topics, select them and hit save!</p>
<?php
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(empty($_POST['select_searchTopicOne']) or !is_numeric($_POST['select_searchTopicOne']) or empty($_POST['select_searchTopicTwo']) or !is_numeric($_POST['select_searchTopicTwo']))
				$errors[] = 'You have to select two topics!';

			if($_POST['select_searchTopicOne'] == $_POST['select_searchTopicTwo'])
				$errors[] = 'You can\'t select the same topic twice!';

			if(count($errors) == 0){
				if(bibliographie_topics_create_relation($_POST['select_searchTopicOne'], $_POST['select_searchTopicTwo'])){
					echo '<p class="success">Relation was created!</p>';
					$created = true;
				}else
					echo '<p class="error">Relation was not created!</p>';
			}else
				bibliographie_print_errors($errors);
		}

		if(!$created){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=createRelation'?>" method="post" onsubmit="return bibliographie_topics_check_submit_status()">
	<div class="unit">
		<div style="float: right; width: 50%">
			<label for="searchTopicTwo" class="block">Subordinated topic</label>
			<input type="text" id="searchTopicTwo" name="searchTopicTwo" style="width: 100%;" />
			<div id="result_searchTopicTwo"></div>
		</div>
		<label for="searchTopicOne" class="block">Parent topic</label>
		<input type="text" id="searchTopicOne" name="searchTopicOne" style="width: 45%;" />
		<div id="result_searchTopicOne" style="width: 45%"></div>

		<br style="clear: both;"/>
	</div>
	<div class="submit">
		<input type="submit" value="save" />
	</div>
</form>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_topics_check_submit_status () {
	if($('#select_searchTopicOne').length == 1 && $('#select_searchTopicTwo').length == 1){
		if($('#select_searchTopicOne').val() > 0 && $('#select_searchTopicTwo').val() > 0 && $('#select_searchTopicOne').val() != $('#select_searchTopicTwo').val())
			return true;

		alert('You have to select two distinct topics!');
		return false;
	}

	alert('You have to search for the two topics that you want to relate!');
	return false;
}

function bibliographie_topics_search_topic_for_relation (event) {
	if(event.target.id == 'searchTopicOne' || event.target.id == 'searchTopicTwo'){
		$.ajax({
			url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/topics/ajax.php'?>',
			data: {
				'task': 'searchTopic',
				'id': event.target.id,
				'value': event.target.value
			},
			success: function (html) {
				$('#result_'+event.target.id).html(html);
			}
		});
	}
}

$('#searchTopicTwo, #searchTopicOne').change(function(event) {
	bibliographie_topics_search_topic_for_relation(event);
}).keyup(function(event) {
	bibliographie_topics_search_topic_for_relation(event);
});
	/* ]]> */
</script>
<?php
		}
	break;

	case 'createTopic':
		$title = 'Create topic';
?>

<h3>Create topic</h3>
<p class="notice">On this page you can create a topic. Just fill at least the field for the title and hit save!</p>
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
				bibliographie_print_errors($errors);
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
				$mysqlString = '`topic_id` = '.((int) $topic->topic_id);
				foreach($subtopicsArray as $subtopic)
					$mysqlString .= " OR  `topic_id` = ".((int) $subtopic);
				$indirectPublications = mysql_num_rows(mysql_query("SELECT * FROM `a2topicpublicationlink` WHERE ".$mysqlString));
			}

			if(in_array($topic->topic_id, bibliographie_topics_get_locked_topics()))
				echo '<p class="error">This topic is locked against editing. If you want to edit something regarding this topic please contact your admin!</p>';
?>

<h3>Topic: <?php echo htmlspecialchars($topic->name)?></h3>
<ul>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showPublications&topic_id=<?php echo $topic->topic_id?>">Show publications (<?php echo $directPublications?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showPublications&topic_id=<?php echo $topic->topic_id?>&includeSubtopics=1">Show publications including all subtopics (<?php echo $indirectPublications?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showGraph&topic_id=<?php echo $topic->topic_id?>">Show subgraph</a></li>
</ul>
<?php
		}
	break;

	case 'showPublications':
		$topic = bibliographie_topics_get_topic_data($_GET['topic_id']);
		if($topic){
			$includeSubtopics = '';
			$mysqlString = '';
			
			if($_GET['includeSubtopics'] == 1){
				$subtopicsArray = bibliographie_topics_get_subtopics($topic->topic_id);
				if(count($subtopicsArray) > 0){
					foreach($subtopicsArray as $subtopic)
						$mysqlString .= " OR relations.`topic_id` = ".((int) $subtopic);

					$includeSubtopics = '&includeSubtopics=1';
				}
			}
?>

<h3>Publications assigned to <?php echo htmlspecialchars($topic->name)?></h3>
<?php
			$allPublications = mysql_num_rows(mysql_query("SELECT * FROM
	`a2topicpublicationlink` relations,
	`a2publication` publications
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	(relations.`topic_id` = ".((int) $_GET['topic_id']).$mysqlString.")"));

			if($allPublications > 0){
				$pageData = bibliographie_print_pages(BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showPublications&topic_id='.((int) $_GET['topic_id']).$includeSubtopics, $allPublications);

				$publications = mysql_query("SELECT * FROM
		`a2topicpublicationlink` relations,
		`a2publication` publications
	WHERE
		publications.`pub_id` = relations.`pub_id` AND
		(relations.`topic_id` = ".((int) $_GET['topic_id']).$mysqlString.")
	ORDER BY
		publications.`year` DESC
	LIMIT ".$pageData['offset'].", ".$pageData['perPage']);

				$lastYear = null;
				while($publication = mysql_fetch_object($publications)){
					if($publication->year != $lastYear)
						echo '<h4>Publications in '.((int) $publication->year).'</h4>';

					echo '<p class="bibliographie_publication">'.bibliographie_publications_parse_data($publication->pub_id).'</p>';

					$lastYear = $publication->year;
				}

				bibliographie_print_pages(BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showPublications&topic_id='.((int) $_GET['topic_id']).$includeSubtopics, $allPublications);
			}else
				echo '<p class="error">No publications are assigned to this topic!</p>';
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