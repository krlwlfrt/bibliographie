<?php
/**
 * Lock a topic by its ID.
 * @param int $topic_id
 * @return bool True on success, false otherwise.
 */
function bibliographie_maintenance_lock_topics (array $topics) {
	static $lockTopic = null;

	$lockedTopics = (int) 0;

	try {
		if(!($lockTopic instanceof PDOStatement))
			$lockTopic = DB::getInstance()->prepare('INSERT INTO `lockedtopics` (`topic_id`) VALUES (:topic_id)');

		DB::getInstance()->beginTransaction();

		foreach($topics as $topic_id)
			if(!in_array($topic_id, bibliographie_topics_get_locked_topics()) and $lockTopic->execute(array('topic_id' => (int) $topic_id))){
				$lockedTopics++;
				bibliographie_log('topics', 'lockTopic', json_encode(array('topic_id' => (int) $topic_id)));
			}

		DB::getInstance()->commit();

		if($lockedTopics > 0)
			bibliographie_purge_cache('topics_locked');
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
function bibliographie_maintenance_unlock_topic ($topic_id) {
	if(!empty($topic_id) and is_numeric($topic_id)){
		mysql_query("DELETE FROM `lockedtopics` WHERE `topic_id` = ".((int) $topic_id)." LIMIT 1");

		$return = (bool) mysql_affected_rows();

		if($return){
			bibliographie_purge_cache('topics_locked');
			bibliographie_log('topics', 'unlockTopic', json_encode(array('topic_id' => ((int) $topic_id))));
		}

		return $return;
	}

	return false;
}