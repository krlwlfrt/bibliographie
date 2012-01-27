<?php
$bibliographie_admin_log_category_icons = array (
	'authors' => 'user',
	'maintenance' => 'cog',
	'notes' => 'note',
	'publications' => 'page-white-text',
	'tags' => 'tag-blue',
	'topics' => 'folder'
);

$bibliographie_admin_log_action_icons = array (
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

/**
 * Lock a topic by its ID.
 * @param int $topic_id
 * @return bool True on success, false otherwise.
 */
function bibliographie_admin_lock_topics (array $topics) {
	static $lockTopic = null;

	$lockedTopics = (int) 0;

	try {
		if(!($lockTopic instanceof PDOStatement))
			$lockTopic = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'lockedtopics` (`topic_id`) VALUES (:topic_id)');

		$higherTransaction = DB::getInstance()->inTransaction();
		if(!$higherTransaction)
			DB::getInstance()->beginTransaction();

		foreach($topics as $topic_id)
			if(!in_array($topic_id, bibliographie_topics_get_locked_topics()) and $lockTopic->execute(array('topic_id' => (int) $topic_id))){
				$lockedTopics++;
				bibliographie_log('topics', 'lockTopic', json_encode(array('topic_id' => (int) $topic_id)));
			}

		if(!$higherTransaction)
			DB::getInstance()->commit();

		if($lockedTopics > 0)
			bibliographie_cache_purge('topics_locked');
	} catch (PDOException $e) {
		DB::getInstance()->rollBack();
		echo '<p>An error occured while locking topics.! '.$e->getMessage().'</p>';
		return false;
	}

	return $lockedTopics;
}

/**
 * Unlock a topic by its ID.
 * @param int $topic_id
 * @return bool True on succes, false otherwise.
 */
function bibliographie_admin_unlock_topic ($topic_id) {
	static $deleteLock = null;

	$return = false;

	if(!empty($topic_id) and is_numeric($topic_id)){
		if(!($deleteLock instanceof PDOStatement))
			$deleteLock = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'lockedtopics`
WHERE
	`topic_id` = :topic_id
LIMIT 1');

		$data = array (
			'topic_id' => (int) $topic_id
		);

		$return = $deleteLock->execute($data);

		if($return){
			bibliographie_cache_purge('topics_locked');
			bibliographie_log('topics', 'unlockTopic', json_encode($data));
		}
	}

	return $return;
}

function bibliographie_admin_log_parse ($logContent) {
	global $bibliographie_admin_log_action_icons, $bibliographie_admin_log_category_icons;
	if(is_array($logContent) and count($logContent) > 0){
?>

<table class="dataContainer">
	<tr>
		<th>Meta</th>
		<th>Data</th>
	</tr>
<?php
	foreach($logContent as $logRow){
		$logRow = json_decode($logRow, true);
		echo '<tr>';

		echo '<td>';
		echo 'logged action <strong>#', $logRow['id'], '</strong><br /><br />';
		echo '<strong>', bibliographie_icon_get($bibliographie_admin_log_category_icons[$logRow['category']]), ' ', $logRow['category'], '</strong><br />';
		echo '<em>', bibliographie_icon_get($bibliographie_admin_log_action_icons[$logRow['action']]), ' ', $logRow['action'].'</em><br /><br />';
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