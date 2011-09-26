<?php
/**
 ******************************
 ********** DATA MANIPULATION
 ******************************
 */

/**
 * Create a new topic.
 * @global PDO $db
 * @param string $name The name of the topic	.
 * @param string $description The description of the topic.
 * @param string $url The URL of the topic.
 * @return boolean True on success or false otherwise.
 */
function bibliographie_topics_create_topic ($name, $description, $url, array $topics, $topic_id = null) {
	global $db;
	static $topic = null, $createRelations = null;
	$return = false;

	if($topic === null)
		$topic = $db->prepare('INSERT INTO `a2topics` (
	`topic_id`, `name`, `description`, `url`
) VALUES (
	:topic_id, :name, :description, :url
)');

	$topic->bindParam('topic_id', $topic_id);
	$topic->bindParam('name', $name);
	$topic->bindParam('description', $description);
	$topic->bindParam('url', $url);

	$topic->execute();

	if($topic_id === null)
		$topic_id = $db->lastInsertId();

	if(!empty($topic_id)){
		if(count($topics) > 0){
			if($createRelations == null)
				$createRelations = $db->prepare('INSERT INTO `a2topictopiclink` (`source_topic_id`, `target_topic_id`) VALUES (:topic_id, :parent_topic)');

			foreach($topics as $parentTopic){
				$createRelations->bindParam('topic_id', $topic_id);
				$createRelations->bindParam('parent_topic', $parentTopic);
				$createRelations->execute();

				bibliographie_purge_cache('topic_'.((int) $parentTopic).'_');
			}
		}

		$return = array(
			'topic_id' => (int) $topic_id,
			'name' => $name,
			'description' => $description,
			'url' => $url,
			'topics' => $topics
		);

		if(is_array($return))
			bibliographie_log('topics', 'createTopic', json_encode($return));
	}

	return $return;
}

/**
 * Edit an existing topic.
 * @param int $topic_id
 * @param string $name
 * @param string $description
 * @param string $url
 * @param array $topics
 */
function bibliographie_topics_edit_topic ($topic_id, $name, $description, $url, array $topics) {
	$dataBefore = bibliographie_topics_get_data($topic_id, 'assoc');

	if(is_array($dataBefore)){
		/**
		 * Get subtopics recursively and direct parent topics.
		 */
		$subTopics = bibliographie_topics_get_subtopics($topic_id, true);
		$subTopics[] = $topic_id;

		$dataBefore['topics'] = bibliographie_topics_get_parent_topics($topic_id);

		/**
		 * Sort the topics array to avoid redundant updating.
		 */
		natsort($topics);
		natsort($dataBefore['topics']);
		$topics = array_values($topics);
		$dataBefore['topics'] = array_values($dataBefore['topics']);

		/**
		 * Check for potential circles... Exclude those topics that would produce a circle from the list of parent topics.
		 */
		$safeTopics = array_diff($topics, $subTopics);
		if(count($safeTopics) != count($topics)){
			echo '<p class="error">There was at least one parent topic that would have produced a circle. Those topics were left out!</p>';
			echo '<strong>Those are the topics, that have been left out:</strong><ul>';
			foreach(array_diff($topics, $safeTopics) as $topic)
				echo '<li>'.bibliographie_topics_parse_name($topic, array('linkProfile' => true)).'</li>';
			echo '</ul>';
		}

		/**
		 * Delete the links to topics that are no longer in the list of parent topics.
		 */
		$deleteTopicLinks = array_diff($dataBefore['topics'], $safeTopics);
		if(count($deleteTopicLinks) > 0){
			foreach($deleteTopicLinks as $deleteTopicLink){
				bibliographie_purge_cache('topic_'.$deleteTopicLink.'_');
				_mysql_query("DELETE FROM `a2topictopiclink` WHERE `source_topic_id` = ".((int) $topic_id)." AND `target_topic_id` = ".((int) $deleteTopicLink));
			}
		}

		/**
		 * Update the topic data itself if any change was made.
		 */
		if($name != $dataBefore['name'] or $description != $dataBefore['description'] or $url != $dataBefore['url']){
			$return = _mysql_query("UPDATE `a2topics` SET
		`name`= '".mysql_real_escape_string(stripslashes($name))."',
		`description` = '".mysql_real_escape_string(stripslashes($description))."',
		`url` = '".mysql_real_escape_string(stripslashes($url))."'
	WHERE
		`topic_id` = ".((int) $topic_id)."
	LIMIT 1");
		}

		/**
		 * Add links to topics that were not in the list of parent topics before.
		 */
		$addTopicLinks = array_diff($safeTopics, $dataBefore['topics']);
		if(count($addTopicLinks) > 0){
			foreach($safeTopics as $addTopic){
				if(!in_array($addTopic, $dataBefore['topics'])){
					_mysql_query("INSERT INTO `a2topictopiclink` (`source_topic_id`, `target_topic_id`) VALUES (".((int) $topic_id).", ".((int) $addTopic).")");
					bibliographie_purge_cache('topic_'.((int) $addTopic).'_');
				}
			}
		}

		$data = array(
			'dataBefore' => $dataBefore,
			'dataAfter' => array (
				'topic_id' => (int) $topic_id,
				'name' => $name,
				'description' => $description,
				'url' => $url,
				'topics' => $safeTopics
			)
		);

		if($data['dataBefore'] != $data['dataAfter']){
			bibliographie_log('topics', 'editTopic', json_encode($data));
			bibliographie_purge_cache('topic_'.((int) $topic_id).'_');
		}

		return $data;
	}

	return false;
}

/**
 ******************************
 ********** DATA RETRIEVAL
 ******************************
 */

/**
 * Get the data of a topic.
 * @global PDO $db
 * @param int $topic_id The id of a topic.
 * @param string $type
 * @return mixed Object on success or false otherwise.
 */
function bibliographie_topics_get_data ($topic_id, $type = 'object') {
	global $db;
	static $topic = null;

	$return = false;

	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json')){
			$assoc = false;
			if($type == 'assoc')
				$assoc = true;

			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json'), $assoc);
		}

		if($topic === null)
			$topic = $db->prepare("SELECT `topic_id`, `name`, `description`, `url` FROM `a2topics` WHERE `topic_id` = :topic_id");

		$topic->bindParam(':topic_id', $topic_id);
		$topic->execute();

		if($topic->rowCount() > 0){
			if($type == 'object')
				$topic->setFetchMode(PDO::FETCH_OBJ);
			else
				$topic->setFetchMode(PDO::FETCH_ASSOC);

			$return = $topic->fetch();

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($return));
				fclose($cacheFile);
			}
		}
	}

	return $return;
}

/**
 * Parses the name of a topic by its id.
 * @param int $topic_id
 * @param array $options array('linkProfile')
 * @return mixed String on succcess or false otherwise.
 */
function bibliographie_topics_parse_name ($topic_id, $options = array()) {
	$topic = bibliographie_topics_get_data($topic_id);

	if(is_object($topic_id)){
		$topic->name = htmlspecialchars($topic->name);

		if($options['linkProfile'] == true and $topic->topic_id != 1)
			$topic->name = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $topic->topic_id).'">'.$topic->name.'</a>';

		return $topic->name;
	}

	return false;
}

/**
 * Get the parent topics of a topic.
 * @param int $topic_id
 * @return mixed Array on success of false otherwise.
 */
function bibliographie_topics_get_parent_topics ($topic_id, $recursive = false) {
	global $db;
	static $parentTopics = null;

	$topic = bibliographie_topics_get_data($topic_id);
	$return = false;

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json'));

		$return = array();

		if($parentTopics == null){
			$parentTopics = $db->prepare('SELECT `target_topic_id` FROM
	`a2topictopiclink` relations,
	`a2topics` topics
WHERE
	relations.`source_topic_id` = :topic_id AND
	relations.`target_topic_id` = topics.`topic_id`
ORDER BY topics.`name`');
			$parentTopics->setFetchMode(PDO::FETCH_OBJ);
		}

		$parentTopics->bindParam('topic_id', $topic->topic_id);
		$parentTopics->execute();

		if($parentTopics->rowCount() > 0){
			$return = $parentTopics->fetchAll(PDO::FETCH_COLUMN, 0);

			if($recursive == true)
				foreach($return as $parentTopic)
					$return = array_merge($return, bibliographie_topics_get_parent_topics($parentTopic, true));
		}

		/**
		 * Guarantee that we have no topic twice...
		 */
		$return = array_unique($return);
		sort($return);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 * Get a list of subtopics recursively with their own subtopics and so on.
 * @global PDO $db
 * @param int $topic_id The id of a topic.
 * @param bool $recursive Wether or not to fetch all subtopics recursively or just the direct children.
 * @return mixed An array on success or error otherwise.
 */
function bibliographie_topics_get_subtopics ($topic_id, $recursive = false) {
	global $db;
	static $subtopics = null;

	$topic = bibliographie_topics_get_data($topic_id);
	$return = false;

	if(is_object($topic)){
		$return = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'_subtopics.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'_subtopics.json'));

		if($subtopics === null){
			$subtopics = $db->prepare('SELECT `source_topic_id` FROM `a2topictopiclink` WHERE `target_topic_id` = :topic_id');
			$subtopics->setFetchMode(PDO::FETCH_OBJ);
		}

		$subtopics->bindParam('topic_id', $topic->topic_id);
		$subtopics->execute();

		if($subtopics->rowCount() > 0){
			$return = $subtopics->fetchAll(PDO::FETCH_COLUMN, 0);

			if($recursive)
				foreach($return as $subtopic)
					$return = array_merge($return, bibliographie_topics_get_subtopics($subtopic, true));
		}

		$return = array_unique($return);
		sort($return);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'_subtopics.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 * Parses the children of a topic and their data.
 * @global PDO $db
 * @param int $topic_id The id of a topic.
 * @return mixed An array on success or false otherwise.
 */
function bibliographie_topics_parse_subtopics ($topic_id) {
	global $db;
	static $subtopics = null;

	$topic = bibliographie_topics_get_data($topic_id);

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json'));

		if($subtopics === null)
			$subtopics = $db->prepare("SELECT `topic_id`, `name`, `amount_of_subtopics` FROM
	`a2topictopiclink` relations,
	`a2topics` topics
LEFT JOIN (
	SELECT `target_topic_id`, COUNT(*) AS `amount_of_subtopics` FROM `a2topictopiclink` GROUP BY `target_topic_id`
) AS subtopics ON topics.`topic_id` = subtopics.`target_topic_id`
WHERE
	relations.`source_topic_id` = topics.`topic_id` AND
	relations.`target_topic_id` = :topic_id
ORDER BY
	topics.`name`");

		$subtopics->bindParam(':topic_id', $topic_id);
		$subtopics->execute();

		$subtopicsArray = array();
		if($subtopics->rowCount() > 0){
			$subtopics->setFetchMode(PDO::FETCH_OBJ);
			$subtopicsArray = $subtopics->fetchAll();
		}

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json', 'w+');
			fwrite($cacheFile, json_encode($subtopicsArray));
			fclose($cacheFile);
		}

		return $subtopicsArray;
	}

	return false;
}

/**
 * Get a list of publications for a specific topic and/or its subtopics.
 * @param int $topic_id
 * @param mixed $includeSubtopics
 * @return mixed
 */
function bibliographie_topics_get_publications ($topic_id, $includeSubtopics = false) {
	global $db;
	static $publications = null;

	$topic = bibliographie_topics_get_data($topic_id);
	$return = false;

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json'));

		$return = array();

		if($publications === null){
			$publications = $db->prepare('SELECT publications.`pub_id`, publications.`year` FROM
	`a2topicpublicationlink` relations,
	`a2publication` publications
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	FIND_IN_SET(relations.`topic_id`, :set)
GROUP BY
	publications.`pub_id`
ORDER BY
	publications.`year` DESC,
	publications.`pub_id` DESC');
			$publications->setFetchMode(PDO::FETCH_OBJ);
		}

		$topics = array($topic->topic_id);
		if($includeSubtopics === true)
			$topics = array_merge($topics, bibliographie_topics_get_subtopics($topic->topic_id));

		$publications->bindParam('set', implode(',', $topics));
		$publications->execute();

		if($publications->rowCount() > 0)
			$return = $publications->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $topic_id
 * @param type $includeSubtopics
 * @return type
 */
function bibliographie_topics_get_tags ($topic_id, $includeSubtopics = true) {
	/**
	 * TODO: Gets very slow for many publications, needs optimization!
	 */
	global $db;
	static $tags = null;

	$return = false;

	$topic = bibliographie_topics_get_data($topic_id);

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_tags.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_tags.json'));

		$publications = bibliographie_topics_get_publications($topic->topic_id, $includeSubtopics);

		if(count($publications) > 0){
			if($tags === null){
				$tags = $db->prepare("SELECT
	`tag_id`,
	COUNT(*) AS `count`
FROM
	`a2publicationtaglink` link
WHERE
	FIND_IN_SET(link.`pub_id`, :set)
GROUP BY
	`tag_id`");
				$tags->setFetchMode(PDO::FETCH_OBJ);
			}

			$tags->bindParam('set', implode(',', $publications));
			$tags->execute();

			if($tags->rowCount() > 0)
				$return = $tags->fetchAll();

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_tags.json', 'w+');
				fwrite($cacheFile, json_encode($return));
				fclose($cacheFile);
			}
		}
	}

	return $return;
}

/**
 * Get a list of locked topics.
 * @return array Gives an array of locked topics.
 */
function bibliographie_topics_get_locked_topics () {
	global $db;
	static $topics = null;

	$return = array();

	if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json'));

	if($topics === null){
		$topics = $db->prepare("SELECT `topic_id` FROM `lockedtopics` ORDER BY `topic_id`");
		$topics->setFetchMode(PDO::FETCH_OBJ);
	}

	$topics->execute();

	$return = $topics->fetchAll(PDO::FETCH_COLUMN, 0);;

	if(BIBLIOGRAPHIE_CACHING){
		$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json', 'w+');
		fwrite($cacheFile, json_encode($return));
		fclose($cacheFile);
	}

	return $return;
}

/**
 ******************************
 ********** DATA OUTPUT
 ******************************
 */

/**
 * Traverse the topic graph from a given starting point.
 * @global int $bibliographie_topics_graph_depth Global variable to determine the maximal depth of the graph.
 * @param int $topic The id of a topic.
 * @param int $depth Used internally.
 * @param int $walkedBy Used internally to mark yet traversed topics.
 */
function bibliographie_topics_traverse ($topic_id, $depth = 1, &$walkedBy = array(), $usage = 'print') {
	global $bibliographie_topics_graph_depth;

	if($depth > $bibliographie_topics_graph_depth)
		$bibliographie_topics_graph_depth = $depth;

	$subtopics = bibliographie_topics_parse_subtopics($topic_id);

	if(count($subtopics) > 0){
		echo '<ul>'.PHP_EOL;
		foreach($subtopics as $topic){
			if(!array_key_exists($topic->topic_id, $walkedBy))
				$walkedBy[$topic->topic_id] = 1;
			else
				$walkedBy[$topic->topic_id]++;

			if($usage == 'print')
				$topic->name = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&topic_id='.$topic->topic_id.'">'.$topic->name.'</a>';
			else
				$topic->name = '<a href="javascript:;" onclick="$(\'#topics\').tokenInput(\'add\', {id:\''.$topic->topic_id.'\',name:\''.$topic->name.'\'})"><span class="silk-icon silk-icon-add"></span></a> '.$topic->name;

			echo '<li>';
			if($topic->amount_of_subtopics > 0){
				echo '<a href="javascript:;" id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'" class="topic" onclick="bibliographie_topics_toggle_visibility_of_subtopics('.((int) $topic->topic_id).', '.$walkedBy[$topic->topic_id].')">';
				echo '<span class="silk-icon silk-icon-bullet-toggle-plus"> </span></a> '.$topic->name;
				echo '<div id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'_subtopics" class="topic_subtopics" style="display: none">';
				bibliographie_topics_traverse($topic->topic_id, ($depth + 1), $walkedBy, $usage);
				echo '</div>';
			}else
				echo '<span class="silk-icon-equivalent"> </span> '.$topic->name;
			echo '</li>'.PHP_EOL;
		}
		echo '</ul>'.PHP_EOL;
	}else
		echo '<p class="error">Graph is empty!</p>';
}