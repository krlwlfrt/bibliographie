<?php
/**
 * Traverse the topic graph from a given starting point.
 * @global int $bibliographie_topics_graph_depth Global variable to determine the maximal depth of the graph.
 * @param int $topic The id of a topic.
 * @param int $depth Used internally.
 * @param int $walkedBy Used internally to mark yet traversed topics.
 */
function bibliographie_topics_traverse ($topic, $depth = 1, &$walkedBy = array()) {
	global $bibliographie_topics_graph_depth;

	if($depth > $bibliographie_topics_graph_depth)
		$bibliographie_topics_graph_depth = $depth;

	$subtopics = bibliographie_topics_parse_subtopics($topic);

	if(count($subtopics) > 0){
		echo '<ul>'.PHP_EOL;
		foreach($subtopics as $topic){
			if(!array_key_exists($topic->topic_id, $walkedBy))
				$walkedBy[$topic->topic_id] = 1;
			else
				$walkedBy[$topic->topic_id]++;

			$topic->name = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&topic_id='.$topic->topic_id.'">'.$topic->name.'</a>';

			echo '<li>';
			if($topic->amount_of_subtopics > 0){
				echo '<a href="javascript:;" id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'" class="topic" onclick="bibliographie_topics_toggle_visibility_of_subtopics('.((int) $topic->topic_id).', '.$walkedBy[$topic->topic_id].')">';
				echo '<span class="silk-icon silk-icon-bullet-toggle-plus"> </span></a> '.$topic->name;

				echo '<div id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'_subtopics" class="topic_subtopics" style="display: none">';
				bibliographie_topics_traverse($topic->topic_id, ($depth + 1), $walkedBy);
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
 * @param string $name The name of the topic.
 * @param string $description The description of the topic.
 * @param string $url The URL of the topic.
 * @return boolean True on success or false otherwise.
 */
function bibliographie_topics_create_topic ($name, $description, $url) {
	$return = mysql_query("INSERT INTO `a2topics` (
	`name`,
	`description`,
	`url`
) VALUES (
	'".mysql_real_escape_string(stripslashes($name))."',
	'".mysql_real_escape_string(stripslashes($description))."',
	'".mysql_real_escape_string(stripslashes($url))."'
)");

	$data = json_encode(array(
		'topic_id' => mysql_insert_id(),
		'name' => $name,
		'description' => $description,
		'url' => $url
	));

	if($return)
		bibliographie_log('topics', 'createTopic', $data);

	return $return;
}

/**
 * Get a list of subtopics recursively with their own subtopics and so on.
 * @param int $topic The id of a topic.
 * @return mixed An array on success or error otherwise.
 */
function bibliographie_topics_get_subtopics ($topic) {
	if(is_numeric($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic).'_subtopics.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic).'_subtopics.json'));

		$subtopics = mysql_query("SELECT * FROM `a2topictopiclink` relations
WHERE relations.`target_topic_id` = ".((int) $topic));

		$subtopicsArray = array();
		while($subtopic = mysql_fetch_object($subtopics)){
			$subtopicsArray[] = $subtopic->source_topic_id;
			$subtopicsArray = array_merge($subtopicsArray, bibliographie_topics_get_subtopics($subtopic->source_topic_id));
		};

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic).'_subtopics.json', 'w+');
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

		$topics = mysql_query("SELECT * FROM
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
 * Create a relation between two topics. The topics have to be distinct.
 * @param int $parent ID of parent topic.
 * @param int $child ID of child topic.
 * @return bool True on success or false otherwise.
 */
function bibliographie_topics_create_relation ($parent, $child) {
	if(!empty($parent) and is_numeric($parent) and !empty($child) and is_numeric($child) and $parent != $child){
		$return = mysql_query("INSERT INTO `a2topictopiclink` (`target_topic_id`, `source_topic_id`) VALUES (".((int) $parent).", ".((int) $child).")");
		if($return){
			bibliographie_purge_cache('topic_'.((int) $parent));
			bibliographie_log('topics', 'createTopicRelation', json_encode(array('parent' => ((int) $parent), 'child' => ((int) $child))));
		}
		return $return;
	}
	return false;
}

/**
 * Get the data of a topic.
 * @param int $topic_id The id of a topic.
 * @return mixed Object on success or false otherwise.
 */
function bibliographie_topics_get_topic_data ($topic_id) {
	if(is_numeric($topic_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_data.json'));

		$topic = mysql_query("SELECT * FROM `a2topics` WHERE `topic_id` = ".((int) $_GET['topic_id']));
		if(mysql_num_rows($topic) == 1){
			$topic = mysql_fetch_object($topic);

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