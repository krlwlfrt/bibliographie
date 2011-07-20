<?php
function bibliographie_topics_traverse ($startTopic, $maxdepth = -1, $depth = 0, $walkedBy = array()) {
	$topics = mysql_query("SELECT * FROM
	`a2topictopiclink` relations,
	`a2topics` topics
WHERE
	relations.`source_topic_id` = topics.`topic_id` AND
	relations.`target_topic_id` = ".((int) $startTopic));

	if(mysql_num_rows($topics) > 0){
		echo '<ul>'.PHP_EOL;
		while($topic = mysql_fetch_object($topics)){
			echo '<li>'.$topic->name;
			if(!in_array($topic->topic_id, $walkedBy)){
				if(($depth + 1) < $maxdepth or $maxdepth == -1)
					bibliographie_topics_traverse($topic->topic_id, $maxdepth, ($depth + 1), $walkedBy);
			}else
				echo '<strong>Walked by already!</strong>';
			echo '</li>'.PHP_EOL;

			$walkedBy[] = $topic->topic_id;
		}
		echo '</ul>'.PHP_EOL;
	}
}