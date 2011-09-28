<?php
/**
 * Lock a topic by its ID.
 * @param int $topic_id
 * @return bool True on success, false otherwise.
 */
function bibliographie_maintenance_lock_topic ($topic_id) {
	if(!empty($topic_id) and is_numeric($topic_id)){
		$return = mysql_query("INSERT INTO `lockedtopics` (`topic_id`) VALUES (".((int) $topic_id).")");

		if($return){
			bibliographie_purge_cache('topics_locked');
			bibliographie_log('topics', 'lockTopic', json_encode(array('topic_id' => ((int) $topic_id))));
		}

		return $return;
	}

	return false;
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