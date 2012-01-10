<?php
require dirname(__FILE__).'/../init.php';

switch($_GET['task']){
	case 'lockedTopics':
		$bibliographie_title = 'Locked topics';
		bibliographie_history_append_step('maintenance', 'Locked topics');
?>

<h2>Maintenance</h2>
<h3>Locked topics</h3>
<?php
		$lockedTopics = bibliographie_topics_get_locked_topics();

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$topics = csv2array($_POST['topics'], 'int');
			$lockedTopics = bibliographie_maintenance_lock_topics($topics);

			'<p class="notice">'.((int) $lockedTopics).' have been locked!</p>';

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
		<td><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showTopic&amp;topic_id=<?php echo (int) $topic->topic_id?>)?>"><?php echo bibliographie_topics_parse_name($topic->topic_id, array('linkProfile' => true))?></td>
		<td><?php echo htmlspecialchars($topic->description)?></td>
		<td><a href="javascript:;" onclick="bibliographie_maintenance_unlock_topic(<?php echo (int) $topic->topic_id?>"><?php echo bibliographie_icon_get('lock-open')?></a></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}else
			echo '<p class="notice">There are no locked topics!</p>';
?>

<h2>Maintenance</h2>
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
	bibliographie_topics_input_tokenized('topics', 'topicsContainer', []);
});
	/* ]]> */
</script>
<?php
	break;

	case 'parseLog':
		bibliographie_history_append_step('maintenance', 'Parse log');
		$bibliographie_title = 'Parse log';
?>

<h2>Maintenance</h2>
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
		<th>Meta</th>
		<th>Data</th>
	</tr>
<?php
				$logContent = file(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile']);

				$categoryIcons = array (
					'authors' => 'user',
					'maintenance' => 'cog',
					'notes' => 'note',
					'publications' => 'page-white-text',
					'tags' => 'tag-blue',
					'topics' => 'folder'
				);

				$actionIcons = array (
					'createTopic' => 'folder-add',
					'createTopicRelation' => 'table-relationship',
					'editTopic' => 'folder-edit',
					'editAuthor' => 'user-edit',
					'createAuthor' => 'user-create',
					'lockTopic' => 'lock',
					'unlockTopic' => 'lock-open',
					'editPublication' => 'page-white-edit',
					'createPublication' => 'page-white-create',
					'createTag' => 'tag-blue-add',
					'addTopic' => 'folder-add',
					'removeTopic' => 'folder-delete',
					'deleteAuthor' => 'user-delete',
					'createPublication' => 'page-white-add',
					'mergeAuthors' => 'arrow-join',
					'createNote' => 'note-add',
					'editNote' => 'note-edit'
				);

				foreach($logContent as $logRow){
					$logRow = json_decode($logRow, true);
					echo '<tr>';

					echo '<td>';
					echo 'logged action <strong>#', $logRow['id'], '</strong><br /><br />';
					echo '<strong>', bibliographie_icon_get($categoryIcons[$logRow['category']]), ' ', $logRow['category'], '</strong><br />';
					echo '<em>', bibliographie_icon_get($actionIcons[$logRow['action']]), ' ', $logRow['action'].'</em><br /><br />';
					echo 'by <strong>', bibliographie_user_get_name($logRow['user']).'</strong><br />';
					echo 'at <em>', $logRow['time'], '</em>';
					echo '</td>';

					echo '<td style="font-size: 0.7em; width: 500px"><pre style="overflow: scroll; width: 500px; height: 300px">', print_r(json_decode($logRow['data'], true), true), '</pre></td>';

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