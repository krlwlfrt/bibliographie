<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Topics</h2>
<?php
switch($_GET['task']){
	case 'topicEditor':
		$title = 'Topic editor';
?>

<h3>Create topic</h3>
<?php
		$done = false;
		$topic = null;

		if(!empty($_GET['topic_id']))
			$topic = bibliographie_topics_get_topic_data($_GET['topic_id'], 'assoc');

		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			if(is_array($topic)){
				$_POST = $topic;

				$topics = bibliographie_topics_get_parent_topics($_GET['topic_id']);
				if(is_array($topics) and count($topics) > 0)
					$_POST['topics'] = implode(',', $topics);
			}else
				$_POST['topics'] = 1;
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(empty($_POST['name']))
				$errors[] = 'You did not fill a name!';

			if(!empty($_POST['url']) and !is_url($_POST['url']))
				$errors[] = 'The URL you filled is not valid.';

			$topics = explode(',', $_POST['topics']);

			if(count($errors) == 0){
				if(is_array($topic)){
					if(bibliographie_topics_edit_topic($topic['topic_id'], $_POST['name'], $_POST['description'], $_POST['url'], $topics)){
						echo '<p class="success">Topic has been edited.</p>';
						echo 'You can <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=topicEditor&amp;topic_id='.$topic['topic_id'].'">return to the editor</a> or view the <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.$topic['topic_id'].'">topic page</a>.';
						$done = true;
					}else
						echo '<p class="success">Topic could not be edited.</p>';
				}else{
					if(bibliographie_topics_create_topic($_POST['name'], $_POST['description'], $_POST['url'], $topics)){
						echo '<p class="success">Topic has been created.</p>';
						$done = true;
					}else
						echo '<p class="error">Topic could not be created!</p>';
				}
			}else
				bibliographie_print_errors($errors);
		}

		if(!$done){
			$prePopulateTopics = array();

			/**
			 * Fill the prePropulateTopics array.
			 */
			if(!empty($_POST['topics'])){
				if(preg_match('~[0-9]+(\,[0-9]+)*~', $_POST['topics'])){
					$topics = explode(',', $_POST['topics']);
					foreach($topics as $parentTopic){
						$prePopulateTopics[] = array (
							'id' => $parentTopic,
							'name' => bibliographie_topics_topic_by_id($parentTopic)
						);
					}
				}
			}
?>

<p class="notice">On this page you can create a topic. Just fill at least the field for the title and hit save!</p>
<?php
			if(is_array($topic)){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=topicEditor&amp;topic_id='.$topic['topic_id']?>" method="post">
<?php
			}else{
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=topicEditor'?>" method="post">
<?php
			}
?>

	<div class="unit">
		<div style="float: right; width: 50%">
			<label for="url" class="block">URL</label>
			<input type="text" id="url" name="url" value="<?php echo htmlspecialchars($_POST['url'])?>" style="width: 100%" />
		</div>

		<label for="name" class="block">Name*</label>
		<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'])?>" style="width: 45%" />
	</div>

	<div class="unit">
		<label for="description" class="block">Description</label>
		<textarea id="description" name="description" rows="6" cols="40" style="width: 100%"><?php echo htmlspecialchars($_POST['description'])?></textarea>
	</div>

	<div class="unit">
		<label for="topics" class="block">Parent topics</label>
		<div id="topicsContainer" style="background: #fff; border: 1px solid #aaa; color: #000; float: right; font-size: 0.8em; padding: 5px; width: 45%;"><em>Search for a topic in the left container!</em></div>
		<input type="text" id="topics" name="topics" style="width: 100%" value="<?php echo htmlspecialchars($_POST['topics'])?>" />
		<br style="clear: both" />
	</div>

	<div class="submit">
		<input type="submit" value="save" />
	</div>
</form>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_publications_show_subgraph (topic) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/ajax.php',
		data: {
			'task': 'getSubgraph',
			'topic_id': topic
		},
		success: function (html) {
			$('#dialogContainer').append(html);
			$('#selectFromTopicSubgraph').dialog({
				width: 600,
				modal: true,
				buttons: {
					'Ok': function () {
						$(this).dialog('close');
					}
				},
				close: function () {
					$(this).remove();
				}
			});
		}
	});
}

$(function () {
	$('#topics').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/ajax.php?task=searchTopicJSON', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		theme: 'facebook',
		prePopulate: <?php echo json_encode($prePopulateTopics)?>,
		noResultsText: 'Results are in the container to the right!',
		onResult: function (results) {
			$('#topicsContainer').html('<div style="margin-bottom: 10px;"><strong>Topics search result</strong></div>');
			if(results.length > 0){
				$.each(results, function (key, value) {
					var selected = false;
					var topicsArray = $('#topics').tokenInput('get')

					$.each(topicsArray, function (selectedKey, selectedValue) {
						if(selectedValue.name == value.name)
							selected = true;
					});

					if(selected){
						$('#topicsContainer')
							.append('<div>')
							.append('<a href="javascript:;" onclick="bibliographie_publications_show_subgraph(\''+value.id+'\')" style="float: right;"><span class="silk-icon silk-icon-sitemap"></span> graph</a>')
							.append('<span class="silk-icon silk-icon-tick"></span> <em>'+value.name+'</em> is selected.</div>');
					}else{
						$('#topicsContainer')
							.append('<div>')
							.append('<a href="javascript:;" onclick="$(\'#topics\').tokenInput(\'add\', {id:\''+value.id+'\',name:\''+value.name+'\'})" style="float: right;"><span class="silk-icon silk-icon-add"></span> add</a>')
							.append('<a href="javascript:;" onclick="bibliographie_publications_show_subgraph(\''+value.id+'\')" style="float: right;"><span class="silk-icon silk-icon-sitemap"></span> graph</a>')
							.append('<em>'+value.name+'</em>')
							.append('</div>');
					}
				});
			}else
				$('#topicsContainer').append('No results for search!');

			return Array();
		}
	});
});
	/* ]]> */
</script>
<?php
		}
	break;

	case 'showTopic':
		$topic = bibliographie_topics_get_topic_data($_GET['topic_id']);
		if($topic){
			$title = 'Topic: '.htmlspecialchars($topic->name);

			if(in_array($topic->topic_id, bibliographie_topics_get_locked_topics()))
				echo '<p class="error">This topic is locked against editing. If you want to edit something regarding this topic please contact your admin!</p>';

			if(!empty($topic->description))
				$topic->description = '<p>'.htmlspecialchars($topic->description).'</p>';
?>

<em style="float: right"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=topicEditor&amp;topic_id=<?php echo $topic->topic_id?>">Edit topic</a></em>
<h3>Topic: <?php echo htmlspecialchars($topic->name)?></h3><?php echo $topic->description?>
<ul>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showPublications&topic_id=<?php echo $topic->topic_id?>">Show publications (<?php echo count(bibliographie_topics_get_publications($_GET['topic_id'], false))?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showPublications&topic_id=<?php echo $topic->topic_id?>&includeSubtopics=1">Show publications including all subtopics (<?php echo count(bibliographie_topics_get_publications($_GET['topic_id'], true))?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showGraph&topic_id=<?php echo $topic->topic_id?>">Show subgraph</a></li>
</ul>
<?php
		}
	break;

	case 'showPublications':
		$topic = bibliographie_topics_get_topic_data($_GET['topic_id']);
		if($topic){
			$includeSubtopics = '';
			if($_GET['includeSubtopics'] == 1)
				$includeSubtopics = '&includeSubtopics=1';
?>

<span style="float: right">
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showPublications&topic_id=<?php echo ((int) $_GET['topic_id']).$includeSubtopics?>&bookmarkBatch=add">Bookmark</a>
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showPublications&topic_id=<?php echo ((int) $_GET['topic_id']).$includeSubtopics?>&bookmarkBatch=remove">Unbookmark</a>
	all
</span>
<h3>Publications assigned to <?php echo htmlspecialchars($topic->name)?></h3>
<?php
			$publications = bibliographie_topics_get_publications($topic->topic_id, ((bool) $_GET['includeSubtopics']));
			if(count($publications) > 0)
				bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showPublications&topic_id='.((int) $_GET['topic_id']).$includeSubtopics, $_GET['bookmarkBatch']);
			else
				echo '<p class="error">No publications are assigned to this topic!</p>';
		}
	break;

	case 'showGraph':
	default:
		$bibliographie_topics_graph_depth = (int) 1;

		$top = (int) 1;
		$title = 'Topic graph';
		if(!empty($_GET['topic_id']) and is_numeric($_GET['topic_id']) and $_GET['topic_id'] != '1'){
			$topic = mysql_query("SELECT * FROM `a2topics` WHERE `topic_id` = ".((int) $_GET['topic_id']));
			if(mysql_num_rows($topic) == 1){
				$topic = mysql_fetch_object($topic);

				$top = (int) $topic->topic_id;
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