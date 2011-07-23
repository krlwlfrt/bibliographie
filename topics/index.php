<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
require dirname(__FILE__).'/topics.php';
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

	case 'showGraph':
	default:
		$bibliographie_topics_graph_depth = (int) 0;

		$top = (int) 1;
		$cacheFile = BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph.json';
		$title = 'Topic graph';
		if(!empty($_GET['top']) and is_numeric($_GET['top']) and $_GET['top'] != '1'){
			$topic = mysql_query("SELECT * FROM `a2topics` WHERE `topic_id` = ".((int) $_GET['top']));
			if(mysql_num_rows($topic) == 1){
				$topic = mysql_fetch_object($topic);

				$top = (int) $_GET['top'];
				$cacheFile = BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph_'.$top.'.json';
				$title = 'Topic subgraph for <em>'.$topic->name.'</em>';
			}
		}

		echo '<a href="javascript:;" onclick="$(\'.topic_subtopics\').show();">Open</a> '.
			'<a href="javascript:;"onclick="$(\'.topic_subtopics\').hide();">Close</a> '.
			'all subtopics';
		echo '<h3>'.$title.'</h3>';

		if(!file_exists($cacheFile) or $_GET['ditchCache'] == 1){
			$cache = bibliographie_topics_traverse($top);

			$file = fopen($cacheFile, 'w+');
			chmod($cacheFile, 0755);
			fwrite($file, json_encode($cache));
			fclose($file);
		}else{
			$cache = json_decode(file_get_contents($cacheFile));
			echo '<p>This is the cached version with timestamp '.date('r', filemtime($cacheFile)).'.<br /><a href="?task=showGraph&ditchCache=1&top='.((int) $top).'">Reload from database.</a></p>';
			bibliographie_topics_traverse_cache($cache);
		}

		echo '<p>depth: '.$bibliographie_topics_graph_depth.'</p>';
	break;
}
?>

<script type="text/javascript">
	/* <![CDATA[ */
$(function(){
	$('.topic_subtopics').hide();
});
	/* <![CDATA[ */
</script>
<?php

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';