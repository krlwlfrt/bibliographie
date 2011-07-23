<?php
function bibliographie_topics_traverse ($startTopic, $maxdepth = -1, $depth = 0, &$walkedBy = array()) {
	global $bibliographie_topics_graph_depth;
	$cache = array();
	$i = (int) 0;

	if($depth > $bibliographie_topics_graph_depth)
		$bibliographie_topics_graph_depth = $depth;

	$topics = mysql_query("SELECT * FROM
	`a2topictopiclink` relations,
	`a2topics` topics
LEFT JOIN (
	SELECT `target_topic_id`, COUNT(*) AS `amount_of_subtopics` FROM `a2topictopiclink` GROUP BY `target_topic_id`
) AS subtopics ON topics.`topic_id` = subtopics.`target_topic_id`
WHERE
	relations.`source_topic_id` = topics.`topic_id` AND
	relations.`target_topic_id` = ".((int) $startTopic)."
ORDER BY
	topics.`name`");

	if(mysql_num_rows($topics) > 0){
		echo '<ul>'.PHP_EOL;
		while($topic = mysql_fetch_object($topics)){
			if($topic->amount_of_subtopics === null)
				$topic->amount_of_subtopics = 0;

			$cache[$i] = array (
				'topic_id' => $topic->topic_id,
				'name' => htmlentities($topic->name)
			);

			if(!array_key_exists($topic->topic_id, $walkedBy))
				$walkedBy[$topic->topic_id] = 1;
			else
				$walkedBy[$topic->topic_id]++;

			echo '<li>';

			if($topic->amount_of_subtopics > 0){
				echo '<a href="javascript:;" id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'" onclick="$(\'#topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'_subtopics\').toggle()">'.$topic->name.'</a>';
				echo '<div id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'_subtopics" class="topic_subtopics">';
				if(($depth + 1) < $maxdepth or $maxdepth == -1)
					$cache[$i]['subtopics'] = bibliographie_topics_traverse($topic->topic_id, $maxdepth, ($depth + 1), $walkedBy);
				echo '</div>';
			}else
				echo $topic->name;

			echo '</li>'.PHP_EOL;

			$i++;
		}
		echo '</ul>'.PHP_EOL;
	}

	return $cache;
}

function bibliographie_topics_traverse_cache ($cache, $depth = 0, &$walkedBy = array()) {
	global $bibliographie_topics_graph_depth;

	if($depth > $bibliographie_topics_graph_depth)
		$bibliographie_topics_graph_depth = $depth;

	if(count($cache) > 0){
		echo '<ul>'.PHP_EOL;
		foreach($cache as $topic){
			$topic->amount_of_subtopics = 0;
			if(isset($topic->subtopics))
				$topic->amount_of_subtopics = count($topic->subtopics);

			if(!array_key_exists($topic->topic_id, $walkedBy))
				$walkedBy[$topic->topic_id] = 1;
			else
				$walkedBy[$topic->topic_id]++;

			echo '<li>';
			if($topic->amount_of_subtopics > 0){
				echo '<a href="javascript:;" id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'" onclick="$(\'#topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'_subtopics\').toggle()">'.$topic->name.'</a>';
				echo '<div id="topic_'.((int) $topic->topic_id).'_'.$walkedBy[$topic->topic_id].'_subtopics" class="topic_subtopics">';
				bibliographie_topics_traverse_cache($topic->subtopics, ($depth + 1), $walkedBy);
				echo '</div>';
			}else
				echo $topic->name;
			echo '</li>'.PHP_EOL;

			$i++;
		}
		echo '</ul>'.PHP_EOL;
	}
}

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
		bibliographie_log('topics', 'create', $data);

	return $return;
}