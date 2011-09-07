<?php
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

/**
 * Create a new topic.
 * @param string $name The name of the topic	.
 * @param string $description The description of the topic.
 * @param string $url The URL of the topic.
 * @return boolean True on success or false otherwise.
 */
function bibliographie_topics_create_topic ($name, $description, $url, $topics) {
	$return = _mysql_query("INSERT INTO `a2topics` (
	`name`,
	`description`,
	`url`
) VALUES (
	'".mysql_real_escape_string(stripslashes($name))."',
	'".mysql_real_escape_string(stripslashes($description))."',
	'".mysql_real_escape_string(stripslashes($url))."'
)");

	$topic_id = mysql_insert_id();

	if(count($topics) > 0)
		foreach($topics as $parentTopic)
			_mysql_query("INSERT INTO `a2topictopiclink` (`source_topic_id`, `target_topic_id`) VALUES (".((int) $topic_id).", ".((int) $parentTopic).")");

	$data = json_encode(array(
		'topic_id' => (int) $topic_id,
		'name' => $name,
		'description' => $description,
		'url' => $url,
		'topics' => $topics
	));

	if($return)
		bibliographie_log('topics', 'createTopic', $data);

	return $return;
}

/**
 * Get a list of subtopics recursively with their own subtopics and so on.
 * @param int $topic_id The id of a topic.
 * @return mixed An array on success or error otherwise.
 */
function bibliographie_topics_get_subtopics ($topic_id) {
	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics.json'));

		$subtopics = _mysql_query("SELECT `source_topic_id` FROM `a2topictopiclink` WHERE `target_topic_id` = ".((int) $topic_id));

		$subtopicsArray = array();
		while($subtopic = mysql_fetch_object($subtopics)){
			$subtopicsArray[] = $subtopic->source_topic_id;
			$subtopicsArray = array_merge($subtopicsArray, bibliographie_topics_get_subtopics($subtopic->source_topic_id));
		};

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics.json', 'w+');
			fwrite($cacheFile, json_encode($subtopicsArray));
			fclose($cacheFile);
		}

		return $subtopicsArray;
	}

	return false;
}

/**
 * Parses the children of a topic and their data.
 * @param int $topic_id The id of a topic.
 * @return mixed An array on success or false otherwise.
 */
function bibliographie_topics_parse_subtopics ($topic_id) {
	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json'));

		$topics = _mysql_query("SELECT `topic_id`, `name`, `amount_of_subtopics` FROM
	`a2topictopiclink` relations,
	`a2topics` topics
LEFT JOIN (
	SELECT `target_topic_id`, COUNT(*) AS `amount_of_subtopics` FROM `a2topictopiclink` GROUP BY `target_topic_id`
) AS subtopics ON topics.`topic_id` = subtopics.`target_topic_id`
WHERE
	relations.`source_topic_id` = topics.`topic_id` AND
	relations.`target_topic_id` = ".((int) $topic_id)."
ORDER BY
	topics.`name`");
		$cache = array();

		if(mysql_num_rows($topics) > 0){
			$i = (int) 0;
			while($topic = mysql_fetch_object($topics)){
				if($topic->amount_of_subtopics === null)
					$topic->amount_of_subtopics = 0;

				$cache[$i] = new stdClass();
				$cache[$i]->topic_id = $topic->topic_id;
				$cache[$i]->name = $topic->name;
				$cache[$i]->amount_of_subtopics = $topic->amount_of_subtopics;
				$i++;
			}
		}

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json', 'w+');
			fwrite($cacheFile, json_encode($cache));
			fclose($cacheFile);
		}

		return $cache;
	}

	return false;
}

/**
 *
 * @param type $topic_id
 * @param type $name
 * @param type $description
 * @param type $url
 * @param type $topics
 */
function bibliographie_topics_edit_topic ($topic_id, $name, $description, $url, $topics) {
	_mysql_query("DELETE FROM `a2topictopiclink` WHERE `source_topic_id` = ".((int) $topic_id)." LIMIT ".count(bibliographie_topics_get_parent_topics($topic_id)));

	$return = _mysql_query("UPDATE `a2topics` SET
	`name`= '".mysql_real_escape_string(stripslashes($name))."',
	`description` = '".mysql_real_escape_string(stripslashes($description))."',
	`url` = '".mysql_real_escape_string(stripslashes($url))."'
WHERE
	`topic_id` = ".((int) $topic_id)."
LIMIT 1");

	if(count($topics) > 0)
		foreach($topics as $parentTopic)
			_mysql_query("INSERT INTO `a2topictopiclink` (`source_topic_id`, `target_topic_id`) VALUES (".((int) $topic_id).", ".((int) $parentTopic).")");

	$data = json_encode(array(
		'topic_id' => (int) $topic_id,
		'name' => $name,
		'description' => $description,
		'url' => $url,
		'topics' => $topics
	));

	if($return)
		bibliographie_log('topics', 'editTopic', $data);

	bibliographie_purge_cache('topic_'.((int) $topic_id));

	return $return;
}

/**
 * Get the data of a topic.
 * @param int $topic_id The id of a topic.
 * @return mixed Object on success or false otherwise.
 */
function bibliographie_topics_get_data ($topic_id, $type = 'object') {
	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json')){
			$assoc = false;
			if($type == 'assoc')
				$assoc = true;

			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json'), $assoc);
		}

		$topic = _mysql_query("SELECT `topic_id`, `name`, `description`, `url` FROM `a2topics` WHERE `topic_id` = ".((int) $topic_id));
		if(mysql_num_rows($topic) == 1){
			if($type == 'object')
				$topic = mysql_fetch_object($topic);
			else
				$topic = mysql_fetch_assoc($topic);

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($topic));
				fclose($cacheFile);
			}

			return $topic;
		}
	}

	return false;
}

/**
 * Get a list of locked topics.
 */
function bibliographie_topics_get_locked_topics () {
	if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json'));

	$topicsArray = array();
	$topics = _mysql_query("SELECT `topic_id` FROM `lockedtopics` ORDER BY `topic_id`");
	if(mysql_num_rows($topics)){
		while($topic = mysql_fetch_object($topics))
			$topicsArray[] = $topic->topic_id;
	}

	if(BIBLIOGRAPHIE_CACHING){
		$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json', 'w+');
		fwrite($cacheFile, json_encode($topicsArray));
		fclose($cacheFile);
	}

	return $topicsArray;
}

/**
 * Get a list of publications for a specific topic and/or its subtopics.
 * @param int $topic_id
 * @param mixed $includeSubtopics
 * @return mixed
 */
function bibliographie_topics_get_publications ($topic_id, $includeSubtopics) {
	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json'));

		$mysqlString = '';
		if($includeSubtopics){
			$subtopicsArray = bibliographie_topics_get_subtopics($topic_id);

			if(count($subtopicsArray) > 0)
				foreach($subtopicsArray as $subtopic)
					$mysqlString .= " OR relations.`topic_id` = ".((int) $subtopic);
		}

		$publicationsResult = _mysql_query("SELECT publications.`pub_id`, publications.`year` FROM
	`a2topicpublicationlink` relations,
	`a2publication` publications
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	(relations.`topic_id` = ".((int) $topic_id).$mysqlString.")
ORDER BY
	publications.`year` DESC");
		$publicationsArray = array();
		while($publication = mysql_fetch_object($publicationsResult))
			if(!in_array($publication->pub_id, $publicationsArray))
				$publicationsArray[] = $publication->pub_id;

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($publicationsArray));
			fclose($cacheFile);
		}

		return $publicationsArray;
	}

	return false;
}

/**
 * Get the name of a topic by its id.
 * @param int $topic_id
 * @return mixed Name of a topic or false on error.
 */
function bibliographie_topics_topic_by_id ($topic_id) {
	$data = bibliographie_topics_get_data($topic_id);
	if($data){
		return $data->name;
	}

	return false;
}

/**
 *
 * @param type $topic_id
 * @return type
 */
function bibliographie_topics_get_parent_topics ($topic_id, $recursive = false) {
	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json'));

		$topic = bibliographie_topics_get_data($topic_id);

		if(is_object($topic)){
			$return = array();

			$parentTopics = _mysql_query("SELECT `target_topic_id` FROM
		`a2topictopiclink` relations,
		`a2topics` topics
	WHERE
		`source_topic_id` = ".((int) $topic->topic_id)." AND
		relations.`target_topic_id` = topics.`topic_id`
	ORDER BY `name`");

			if(mysql_num_rows($parentTopics) > 0){
				while($parentTopic = mysql_fetch_object($parentTopics)){
					$return[] = $parentTopic->target_topic_id;

					if($recursive == true)
						$return = array_merge($return, bibliographie_topics_get_parent_topics($parentTopic->target_topic_id, true));
				}
			}

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json', 'w+');
				fwrite($cacheFile, json_encode($return));
				fclose($cacheFile);
			}

			return array_unique($return);
		}
	}

	return false;
}

/**
 *
 * @param type $topic_id
 * @param type $options
 */
function bibliographie_topics_parse_name ($topic_id, $options = array()) {
	if(is_numeric($topic_id)){
		$topic = bibliographie_topics_get_data($topic_id);

		if(is_object($topic)){
			$topic->name = htmlspecialchars($topic->name);
			if($options['linkProfile'] == true and $topic->topic_id != 1)
				$topic->name = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $topic->topic_id).'">'.$topic->name.'</a>';

			return $topic->name;
		}
	}

	return false;
}