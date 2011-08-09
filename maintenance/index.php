<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Maintenance</h2>
<?php

switch($_GET['task']){
	case 'lockedTopics':
?>

<h3>Locked topics</h3>
<?php
		$lockedTopics = bibliographie_topics_get_locked_topics();

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$topic = bibliographie_topics_get_topic_data($_POST['select_searchTopicOne']);
			if($topic){
				if(in_array($topic->topic_id, $lockedTopics)){
					echo '<p class="success">The topic <em>'.htmlspecialchars($topic->name).'</em> was in the list of locked tables yet.</p>';
				}else{
					$result = bibliographie_maintenance_lock_topic($topic->topic_id);
					if($result){
						echo '<p class="success">The topic <em>'.htmlspecialchars($topic->name).'</em> was added to the list of locked tables.</p>';
						$lockedTopics = bibliographie_topics_get_locked_topics();
					}else
						echo '<p class="error">The topic <em>'.htmlspecialchars($topic->name).'</em> could not be added to the list of locked tables!</p>';
				}
			}
		}

		if(count($lockedTopics) > 0){
?>

<p class="notice">This is a list of locked topics. You can unlock them if you want to!</p>
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

	<tr>
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

<h3>Lock topic</h3>
<p class="notice">With this form you can add a topic to the list of locked topics.</p>
<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/?task=lockedTopics" method="post" onsubmit="return bibliographie_topics_check_submit_status()">
	<div class="unit">
		<label for="searchTopicOne" class="block">Search topic</label>
		<input type="text" id="searchTopicOne" name="searchTopicOne" style="width: 100%" />

		<div id="result_searchTopicOne"></div>
	</div>
	<div class="submit"><input type="submit" value="save" /></div>
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
			alert(json.text);
		}
	})
}

function bibliographie_maintenance_check_submit_status () {
	if($('#select_searchTopicOne').length == 1 && $('#select_searchTopicOne').val() > 0)
		return true;

	alert('You have to select a topic that you want to lock!');
	return false;
}

function bibliographie_maintenance_search_topic_for_locking (event) {
	if(event.target.id == 'searchTopicOne'){
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

$('#searchTopicOne').change(function(event) {
	bibliographie_maintenance_search_topic_for_locking(event);
}).keyup(function(event) {
	bibliographie_maintenance_search_topic_for_locking(event);
});
	/* ]]> */
</script>
<?php
	break;

	case 'parseLog':
	default:
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
	<div class="submit">
		<input type="submit" value="show" />
	</div>
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