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

/**
 *
 * @return type
 */
function bibliographie_maintenance_get_unsimilar_groups () {
	$return = array();

	$groups = DB::getInstance()->prepare('SELECT `group` FROM `'.BIBLIOGRAPHIE_PREFIX.'unsimilar_groups_of_authors`');
	$groups->execute();

	if($groups->rowCount() > 0){
		$groups = $groups->fetchAll(PDO::FETCH_COLUMN, 0);
		foreach($groups as $group)
			$return[] = csv2array($group, 'int');
	}

	return $return;
}

function bibliographie_maintenance_print_author_profile ($author_id, $group_id = null) {

	$person = bibliographie_authors_get_data($author_id);
	if(is_object($person)){
		echo '<em class="person_id" style="float: right; font-size: 0.8em;">'.((int) $person->author_id).'</em>';
		echo bibliographie_authors_parse_data($person->author_id, array('linkProfile' => true)).'<br />';
		if(is_numeric($group_id))
			echo '<em class="group_id" style="float: right; font-size: 0.8em;">'.((int) $group_id).'</em>';

		if(!empty($person->email))
			echo '<strong>Mail</strong>: '.htmlspecialchars($person->email).'<br />';
		if(!empty($person->url))
			echo '<strong>URL</strong>: '.htmlspecialchars($person->url).'<br />';
		if(!empty($person->institute))
			echo '<strong>Institute</strong>: '.htmlspecialchars($person->institute).'<br />';

		echo '<ul><li><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showPublications&amp;author_id='.((int) $person->author_id).'&amp;asEditor=0">Publications as author ('.count(bibliographie_authors_get_publications($person->author_id)).')</a></li>';
		echo '<li><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showPublications&amp;author_id='.((int) $person->author_id).'&amp;asEditor=1">Publications as editor ('.count(bibliographie_authors_get_publications($person->author_id, true)).')</a></li></ul>';
	}
}