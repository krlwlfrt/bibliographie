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
var consistencyChecks = <?php echo json_encode($bibliographie_consistency_checks)?>;
var runChecks = Array();

function bibliographie_maintenance_run_all_checks () {
	$.each(consistencyChecks, function (category, checks){
		$.each(checks, function (dummy, checkID) {
			bibliographie_maintenance_run_consistency_check(category+'_'+checkID);
		});
	});
}

function bibliographie_maintenance_run_consistency_check (id) {
	if($.inArray(id, runChecks) == -1){
		runChecks.push(id);

		$.ajax({
			url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/ajax.php',
			data: {
				'task': 'consistencyChecks',
				'consistencyCheckID': id
			},
			success: function (html) {
				$('#'+id).html(html);
			}
		})
	}
}


	/* ]]> */
</script>
<?php
	break;

	case 'ToDo':
		$title = 'ToDo list';
?>

<h4>Topics</h4>
<ul>
	<li>Detect circles</li>
	<li>Detect loosened graphs: <code>SELECT * FROM `a2topics` WHERE `topic_id` NOT IN (SELECT `source_topic_id` FROM `a2topictopiclink`)</code></li>
</ul>

<h4>Notes</h4>
<ul>
	<li>private Notizen</li>
</ul>

<h4>Parsing</h4>
<ul>
	<li>Handle number and volume as equivalents.</li>
	<li>Handle booktile and journal as equivalents.</li>
</ul>

<h4>Maintenance</h4>
<ul>
	<li>Detect wrong coded authors by searching for entries which do not REGEXP against [:alpha:].</li>
	<li>Datenbank aus Log wiederherstellen...</li>
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
				$topic = bibliographie_topics_get_topic_data($topic);
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
				$topic = bibliographie_topics_get_topic_data($topic);
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
		<label for="topics" class="block">Search for topics to lock.</label>
		<input type="text" id="topics" name="topics" style="width: 100%" />
	</div>
	<div class="submit"><input type="submit" value="Lock selected topics!" /></div>
</form>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_maintenance_unlock_topic (topic_id) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/maintenance/ajax.php'?>',
		data: {
			'task': 'unlockTopic',
			'topic_id': topic_id
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text);
			if(json.status == 'success')
				$('#topic_'+topic_id).remove();
		}
	})
}

$(function () {
	$('#topics').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/ajax.php?task=searchTopics', {
		searchDelay: 500,
		minChars: <?php echo ((int) BIBLIOGRAPHIE_SEARCH_MIN_CHARS)?>,
		preventDuplicates: true,
		theme: 'facebook',
		queryParam: 'query'
	});
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

	case 'consistencyChecks':

	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';