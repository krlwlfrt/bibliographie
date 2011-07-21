<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
require dirname(__FILE__).'/topics.php';
?>

<h2>Topics</h2>

<?php
switch($_GET['task']){
	case 'showGraph':
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

		//1982

		if(!file_exists($cacheFile) or $_GET['ditchCache'] == 1){
			$cache = bibliographie_topics_traverse($top);

			$file = fopen($cacheFile, 'w+');
			fwrite($file, json_encode($cache));
			fclose($file);
		}else{
			$cache = json_decode(file_get_contents($cacheFile));
			echo '<p>This is the cached version with timestamp '.date('r', filemtime($cacheFile)).'.<br /><a href="?task=showGraph&ditchCache=1&top='.$top.'">Reload from database.</a></p>';
			bibliographie_topics_traverse_cache($cache);
		}

		echo '<p>depth: '.$bibliographie_topics_graph_depth.'</p>';
	break;

	default:
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