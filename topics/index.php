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

		if(!file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph.json') or $_GET['ditchCache'] == 1){
			$cache = bibliographie_topics_traverse(1);

			$file = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph.json', 'w+');
			fwrite($file, json_encode($cache));
			fclose($file);
		}else{
			$cache = json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph.json'));
			echo '<p>This is the cached version with timestamp '.date('r', filemtime(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bibliographie_topics_graph.json')).'.<br /><a href="?task=showGraph&ditchCache=1">Reload from database.</a></p>';
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