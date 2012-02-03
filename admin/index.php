<?php
require dirname(__FILE__).'/../init.php';

echo '<h2>Administration</h2>';
switch($_GET['task']){
	case 'lockedTopics':
		$bibliographie_title = 'Locked topics';
		bibliographie_history_append_step('admin', 'Locked topics');
?>

<h3>Locked topics</h3>
<?php
		$lockedTopics = bibliographie_topics_get_locked_topics();

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$topics = csv2array($_POST['topics'], 'int');
			$lockedTopics = bibliographie_admin_lock_topics($topics);

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
		<td><a href="javascript:;" onclick="bibliographie_admin_unlock_topic(<?php echo (int) $topic->topic_id?>)"><?php echo bibliographie_icon_get('lock-open', 'Unlock topic')?></a></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}else
			echo '<p class="notice">There are no locked topics!</p>';
?>

<h2>Admin</h2>
<h3>Lock topics</h3>
<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/admin/?task=lockedTopics" method="post" onsubmit="return bibliographie_topics_check_submit_status()">
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
		bibliographie_history_append_step('admin', 'Parse log');
		$bibliographie_title = 'Parse log';
?>

<h3>Parse logs</h3>
<?php
		$logContent = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/logs', true);
		if(count($logContent) > 2){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/admin/'?>" method="get">
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

		if(!empty($_GET['logFile']) and mb_strpos($_GET['logFile'], '..') === false and mb_strpos($_GET['logFile'], '/') === false and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile'])){
			bibliographie_admin_log_parse(file(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile']));
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';