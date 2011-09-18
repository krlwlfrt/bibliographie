<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Maintenance</h2>
<?php

$bibliographie_consistency_checks = array (
	'authors' => array (
		'charsetArtifacts'
	),

	'publications' => array (
		'withoutTopic',
		'withoutTag'
	),

	'topics' => array (
		'loosenedSubgraphs',
		'doubledNames'
	)
);

switch($_GET['task']){
	case 'consistencyChecks':
?>

<a href="javascript:;" onclick="bibliographie_maintenance_run_all_checks()">Run all checks...</a>
<?php
		foreach($bibliographie_consistency_checks as $category => $categoryChecks){
?>

<h3><?php echo $category?></h3>
<?php
			foreach($categoryChecks as $check){
?>

<h4><?php echo $check?></h4>
<div id="<?php echo $category.'_'.$check?>"><a href="javascript:;" onclick="bibliographie_maintenance_run_consistency_check('<?php echo $category.'_'.$check?>')">Run this check!</a></div>
<?php
			}

		}
?>

<script type="text/javascript">
	/* <![CDATA[ */
var bibliographie_maintenance_consistency_checks = <?php echo json_encode($bibliographie_consistency_checks)?>;
	/* ]]> */
</script>
<?php
	break;

	case 'ToDo':
		$title = 'ToDo list';
?>

<h4>Publication editor</h4>
<ul>
	<li>Auf fehlende Felder einmal hinweisen, und beim zweiten speichern ignorieren ...</li>
</ul>

<h4>Notes</h4>
<ul>
	<li>private Notizen</li>
</ul>

<h4>Import</h4>
<ul>
	<li>Autoren-approval rückgängig machen... Und neu approven...</li>
	<li>Alle Autoren einer Publikation approven</li>
	<li>Quellen: Amazon und PubMED</li>
	<li>Möglichst buttons statt links</li>
</ul>

<h4>Parsing</h4>
<ul>
	<li>Handle number and volume as equivalents.</li>
	<li>Handle booktile and journal as equivalents.</li>
</ul>

<h4>Maintenance</h4>
<ul>
	<li>Datenbank aus Log wiederherstellen...</li>
</ul>

<h4>Suche</h4>
<ul>
	<li>Zeiträume</li>
</ul>
<?php
	break;
	case 'lockedTopics':
		$title = 'Locked topics';
?>

<h3>Locked topics</h3>
<?php
		$lockedTopics = bibliographie_topics_get_locked_topics();

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			echo '<ul>';
			foreach(explode(',', $_POST['topics']) as $topic){
				$topic = bibliographie_topics_get_data($topic);
				if(is_object($topic)){
					if(in_array($topic->topic_id, $lockedTopics))
						echo '<li class="notice">The topic <em>'.htmlspecialchars($topic->name).'</em> was in the list of locked tables yet.</li>';

					else{
						$result = bibliographie_maintenance_lock_topic($topic->topic_id);

						if($result)
							echo '<li class="success">The topic <em>'.htmlspecialchars($topic->name).'</em> was added to the list of locked tables.</li>';
						else
							echo '<li class="error">The topic <em>'.htmlspecialchars($topic->name).'</em> could not be added to the list of locked tables!</li>';
					}
				}
			}

			echo '</ul>';
			$lockedTopics = bibliographie_topics_get_locked_topics();
		}

		if(count($lockedTopics) > 0){
?>

<table class="dataContainer">
	<tr>
		<th>Name</th>
		<th>Description</th>
		<th style="width: 16px"></th>
	</tr>
<?php
			foreach($lockedTopics as $topic){
				$topic = bibliographie_topics_get_data($topic);
?>

	<tr id="topic_<?php echo $topic->topic_id?>">
		<td><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showTopic&topic_id=<?php echo ((int) $topic->topic_id)?>)?>"><?php echo htmlspecialchars($topic->name)?></td>
		<td><?php echo htmlspecialchars($topic->description)?></td>
		<td><a href="javascript:;" onclick="bibliographie_maintenance_unlock_topic(<?php echo ((int) $topic->topic_id)?>)"><?php echo bibliographie_icon_get('lock-open')?></a></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}else
			echo '<p class="notice">There are no locked topics!</p>';
?>

<h3>Lock topics</h3>
<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/?task=lockedTopics" method="post" onsubmit="return bibliographie_topics_check_submit_status()">
	<div class="unit">
		<label for="topics" class="block">Topics</label>
		<div id="topicsContainer" style="background: #fff; border: 1px solid #aaa; color: #000; float: right; font-size: 0.8em; padding: 5px; width: 45%;"><em>Search for a topic in the left container!</em></div>
		<input type="text" id="topics" name="topics" style="width: 100%" value="<?php echo htmlspecialchars($_POST['topics'])?>" tabindex="1" />
		<br style="clear: both" />
	</div>
	<div class="submit"><input type="submit" value="Lock selected topics!" /></div>
</form>

<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	bibliographie_publications_topic_input_tokenized('topics', 'topicsContainer', []);
});
	/* ]]> */
</script>
<?php
	break;

	case 'parseLog':
	default:
		$title = 'Parse log';
?>

<h3>Parse logs</h3>
<?php
		$logContent = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/logs', true);
		if(count($logContent) > 2){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/maintenance/'?>" method="get">
	<div class="unit">
		<input type="hidden" id="task" name="task" value="parseLog" />
		<label for="logFile" class="block">Choose log file</label>
		<select id="logFile" name="logFile" style="width: 45%">
<?php
			foreach($logContent as $logFile){
				if($logFile == '.' or $logFile == '..')
					continue;

				echo '<option value="'.htmlspecialchars($logFile).'">'.htmlspecialchars($logFile).'</option>';
			}
?>

		</select>
	</div>
	<div class="submit"><input type="submit" value="show" /></div>
</form>
<?php
		}else
			echo '<p class="error">We have no log files!</p>';

		if(!empty($_GET['logFile'])){
			if(mb_strpos($_GET['logFile'], '..') === false and mb_strpos($_GET['logFile'], '/') === false and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile'])){
?>

<table class="dataContainer">
	<tr>
		<th style="width: 35%">Classification</th>
		<th style="width: 25%">Action</th>
		<th style="width: 40%">Data</th>
	</tr>
<?php
				$logContent = file(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile']);
				$categoryIcons = array (
					'topics' => 'folder',
					'authors' => 'user'
				);
				$actionIcons = array (
					'createTopic' => 'folder-add',
					'createTopicRelation' => 'table-relationship',
					'lockTopic' => 'lock',
					'unlockTopic' => 'lock-open'
				);
				foreach($logContent as $logRow){

					$logRow = json_decode($logRow);
					echo '<tr>';
					echo '<td>logged action <strong>#'.$logRow->id.'</strong><br />';
					echo '<em>'.$logRow->time.'</em></td>';
					echo '<td><strong>'.bibliographie_icon_get($categoryIcons[$logRow->category]).' '.$logRow->category.'</strong><br />';
					echo ''.bibliographie_icon_get($actionIcons[$logRow->action]).' '.$logRow->action.'</td>';
					echo '<td><pre>'.print_r(json_decode($logRow->data), true).'</pre></td>';
					echo '</tr>';
				}
?>

</table>
<?php
			}
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';