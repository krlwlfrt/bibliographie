<?php
/**
 ******************************
 ********** DATA MANIPULATION
 ******************************
 */

/**
 * Create a new topic.
 * @param string $name The name of the topic	.
 * @param string $description The description of the topic.
 * @param string $url The URL of the topic.
 * @return boolean True on success or false otherwise.
 */
function bibliographie_topics_create_topic ($name, $description, $url, array $topics, $topic_id = null) {
	static $topic = null, $createRelations = null;
	$return = false;

	if($topic === null)
		$topic = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'topics` (
	`topic_id`, `name`, `description`, `url`
) VALUES (
	:topic_id, :name, :description, :url
)');

	$return = $topic->execute(array(
		'topic_id' => (int) $topic_id,
		'name' => $name,
		'description' => $description,
		'url' => $url
	));

	if($topic_id === null)
		$topic_id = DB::getInstance()->lastInsertId();

	if($return and !empty($topic_id)){
		if(count($topics) > 0){
			if($createRelations == null)
				$createRelations = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'topictopiclink` (`source_topic_id`, `target_topic_id`) VALUES (:topic_id, :parent_topic)');

			foreach($topics as $parentTopic){
				$createRelations->execute(array(
					'topic_id' => (int) $topic_id,
					'parent_topic' => (int) $parentTopic
				));

				bibliographie_cache_purge('topic_'.((int) $parentTopic).'_');
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
		bibliographie_cache_purge('search_');
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
	$dataBefore = (array) bibliographie_topics_get_data($topic_id);

	if(is_array($dataBefore)){
		try {
			DB::getInstance()->beginTransaction();
			/**
			 * Get subtopics recursively and direct parent topics.
			 */
			$subTopics = bibliographie_topics_get_subtopics($dataBefore['topic_id'], true);
			$subTopics[] = $dataBefore['topic_id'];

			$dataBefore['topics'] = bibliographie_topics_get_parent_topics($dataBefore['topic_id']);

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
				$deleteLink = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'topictopiclink`
WHERE
	`source_topic_id` = :topic_id AND
	`target_topic_id` = :deleteTopicLink
LIMIT 1');

				foreach($deleteTopicLinks as $deleteTopicLink){
					$deleteLink->execute(array(
						'topic_id' => (int) $dataBefore['topic_id'],
						'deleteTopicLink' => (int) $deleteTopicLink
					));
					bibliographie_cache_purge('topic_'.$deleteTopicLink.'_');
				}
			}

			/**
			 * Update the topic data itself if any change was made.
			 */
			if($name != $dataBefore['name'] or $description != $dataBefore['description'] or $url != $dataBefore['url']){
				$updateData = DB::getInstance()->prepare('UPDATE `'.BIBLIOGRAPHIE_PREFIX.'topics` SET
			`name`= :name,
			`description` = :description,
			`url` = :url
		WHERE
			`topic_id` = :topic_id
		LIMIT 1');
				$updateData->execute(array(
					'name' => $name,
					'description' => $description,
					'url' => $url,
					'topic_id' => (int) $dataBefore['topic_id']
				));
			}

			/**
			 * Add links to topics that were not in the list of parent topics before.
			 */
			$addTopicLinks = array_diff($safeTopics, $dataBefore['topics']);
			if(count($addTopicLinks) > 0){
				$addLink = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'topictopiclink` (
	`source_topic_id`,
	`target_topic_id`
) VALUES (
	:topic_id,
	:addTopic
)');
				foreach($safeTopics as $addTopic){
					if(!in_array($addTopic, $dataBefore['topics'])){
						$addLink->execute(array(
							'topic_id' => (int) $dataBefore['topic_id'],
							'addTopic' => (int) $addTopic
						));
						bibliographie_cache_purge('topic_'.((int) $addTopic).'_');
					}
				}
			}

			$data = array(
				'dataBefore' => $dataBefore,
				'dataAfter' => array (
					'topic_id' => (int) $dataBefore['topic_id'],
					'name' => $name,
					'description' => $description,
					'url' => $url,
					'topics' => $safeTopics
				)
			);

			if($data['dataBefore'] != $data['dataAfter']){
				foreach($data['dataBefore']['topics'] as $topic_id)
					bibliographie_cache_purge('topic_'.((int) $topic_id).'_');

				bibliographie_log('topics', 'editTopic', json_encode($data));
				bibliographie_cache_purge('topic_'.((int) $dataBefore['topic_id']).'_');
			}

			DB::getInstance()->commit();
			bibliographie_cache_purge('search_');
			return $data;

		} catch (PDOException $e) {
			DB::getInstance()->rollBack();
			bibliographie_exit('Database error', 'There was an error while saving changes! '.$e->getMessage());
		}
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
 * @param int $topic_id The id of a topic.
 * @param string $type
 * @return mixed Object on success or false otherwise.
 */
function bibliographie_topics_get_data ($topic_id, $type = 'object') {
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
			$topic = DB::getInstance()->prepare('SELECT `topic_id`, `name`, `description`, `url` FROM `'.BIBLIOGRAPHIE_PREFIX.'topics` WHERE `topic_id` = :topic_id');

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

	if(is_object($topic)){
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
	static $parentTopics = null;

	$topic = bibliographie_topics_get_data($topic_id);
	$return = false;

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'.json'));

		$return = array();

		if($parentTopics == null){
			$parentTopics = DB::getInstance()->prepare('SELECT `target_topic_id` FROM
	`'.BIBLIOGRAPHIE_PREFIX.'topictopiclink` relations,
	`'.BIBLIOGRAPHIE_PREFIX.'topics` topics
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
 * @param int $topic_id The id of a topic.
 * @param bool $recursive Wether or not to fetch all subtopics recursively or just the direct children.
 * @return mixed An array on success or error otherwise.
 */
function bibliographie_topics_get_subtopics ($topic_id, $recursive = false) {
	static $subtopics = null;

	$topic = bibliographie_topics_get_data($topic_id);
	$return = false;

	if(is_object($topic)){
		$return = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'_subtopics.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $recursive).'_subtopics.json'));

		if($subtopics === null){
			$subtopics = DB::getInstance()->prepare('SELECT `source_topic_id` FROM `'.BIBLIOGRAPHIE_PREFIX.'topictopiclink` WHERE `target_topic_id` = :topic_id');
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
 * @param int $topic_id The id of a topic.
 * @return mixed An array on success or false otherwise.
 */
function bibliographie_topics_parse_subtopics ($topic_id) {
	static $subtopics = null;

	$topic = bibliographie_topics_get_data($topic_id);

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_subtopics_data.json'));

		if($subtopics === null)
			$subtopics = DB::getInstance()->prepare('SELECT `topic_id`, `name`, `amount_of_subtopics` FROM
	`'.BIBLIOGRAPHIE_PREFIX.'topictopiclink` relations,
	`'.BIBLIOGRAPHIE_PREFIX.'topics` topics
LEFT JOIN (
	SELECT `target_topic_id`, COUNT(*) AS `amount_of_subtopics` FROM `'.BIBLIOGRAPHIE_PREFIX.'topictopiclink` GROUP BY `target_topic_id`
) AS subtopics ON topics.`topic_id` = subtopics.`target_topic_id`
WHERE
	relations.`source_topic_id` = topics.`topic_id` AND
	relations.`target_topic_id` = :topic_id
ORDER BY
	topics.`name`');

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
	static $publications = null;

	$topic = bibliographie_topics_get_data($topic_id);
	$return = false;

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_publications.json'));

		$return = array();

		if($publications === null){
			$publications = DB::getInstance()->prepare('SELECT publications.`pub_id`, publications.`year` FROM
	`'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink` relations,
	`'.BIBLIOGRAPHIE_PREFIX.'publication` publications
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
	static $tags = null;

	$return = false;

	$topic = bibliographie_topics_get_data($topic_id);

	if(is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_tags.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topic_'.((int) $topic_id).'_'.((int) $includeSubtopics).'_tags.json'));

		$publications = bibliographie_topics_get_publications($topic->topic_id, $includeSubtopics);

		if(count($publications) > 0){
			if($tags === null){
				$tags = DB::getInstance()->prepare('SELECT
	`tag_id`,
	COUNT(*) AS `count`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink` link
WHERE
	FIND_IN_SET(link.`pub_id`, :set)
GROUP BY
	`tag_id`');
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
	static $topics = null;

	$return = array();

	if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/topics_locked.json'));

	if($topics === null){
		$topics = DB::getInstance()->prepare('SELECT `topic_id` FROM `'.BIBLIOGRAPHIE_PREFIX.'lockedtopics` ORDER BY `topic_id`');
		$topics->setFetchMode(PDO::FETCH_OBJ);
	}

	$topics->execute();

	if($topics->rowCount() > 0)
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

/**
 *
 * @param type $topics
 * @return type
 */
function bibliographie_topics_populate_input ($topics) {
	$prePopulateTopics = array();
	if(!empty($topics)){
		if(preg_match('~[0-9]+(\,[0-9]+)*~', $topics)){
			$topics = csv2array($topics, 'int');
			foreach($topics as $topic){
				$prePopulateTopics[] = array (
					'id' => $topic,
					'name' => bibliographie_topics_parse_name($topic)
				);
			}
		}
	}

	return $prePopulateTopics;
}

function bibliographie_topics_search_topics ($query, $expandedQuery = '') {
	$return = array();

	if(mb_strlen($query) >= 1){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_topics_'.md5($query).'_'.md5($expandedQuery).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_topics_'.md5($query).'_'.md5($expandedQuery).'.json'));

		$topics = DB::getInstance()->prepare('SELECT
	`topic_id`,
	`name`,
	`relevancy`
FROM (
	SELECT
		`topic_id`,
		`name`,
		IF(`innerRelevancy` = 0, 1, `innerRelevancy`) AS `relevancy`
	FROM (
		SELECT
			`topic_id`,
			`name`,
			MATCH(
				`name`,
				`description`
			) AGAINST (
				:expanded_query
			) AS `innerRelevancy`
		FROM
			`'.BIBLIOGRAPHIE_PREFIX.'topics`

	) fullTextSearch
) likeMatching
WHERE
	`relevancy` > 0 AND
	`name` LIKE "%'.trim(DB::getInstance()->quote($query), '\'').'%"
ORDER BY
	`relevancy` DESC,
	LENGTH(`name`),
	`name` ASC,
	`topic_id`');

		$topics->execute(array(
			'expanded_query' => $expandedQuery
		));

		if($topics->rowCount() > 0)
			$return = $topics->fetchAll(PDO::FETCH_OBJ);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_topics_'.md5($query).'_'.md5($expandedQuery).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}