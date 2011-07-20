<?php
function bibliographie_topics_traverse ($startTopic, $maxdepth = -1, $depth = 0, $walkedBy = array()) {
	$topics = mysql_query("SELECT * FROM
	`a2topictopiclink` relations,
	`a2topics` topics
LEFT JOIN (
	SELECT `target_topic_id`, COUNT(*) AS `amount_of_subtopics` FROM `a2topictopiclink` GROUP BY `target_topic_id`
) AS subtopics ON topics.`topic_id` = subtopics.`target_topic_id`
WHERE
	relations.`source_topic_id` = topics.`topic_id` AND
	relations.`target_topic_id` = ".((int) $startTopic));

	if(mysql_num_rows($topics) > 0){
		echo '<ul>'.PHP_EOL;
		while($topic = mysql_fetch_object($topics)){
			if($topic->amount_of_subtopics === null)
				$topic->amount_of_subtopics = 0;

			echo '<li>';
			if(!in_array($topic->topic_id, $walkedBy)){
				if($topic->amount_of_subtopics > 0){
					echo '<a href="javascript:;" id="topic_'.((int) $topic->topic_id).'" onclick="$(\'#topic_'.((int) $topic->topic_id).'_subtopics\').toggle()">'.$topic->name.'</a>';
					echo '<div id="topic_'.((int) $topic->topic_id).'_subtopics" class="topic_subtopics">';
					if(($depth + 1) < $maxdepth or $maxdepth == -1)
						bibliographie_topics_traverse($topic->topic_id, $maxdepth, ($depth + 1), $walkedBy);
					echo '</div>';
				}else
					echo $topic->name;
			}else
				echo $topic->name.' (Sorry but we did walk by this topic yet earlier in the graph.)';
			echo '</li>'.PHP_EOL;

			$walkedBy[] = $topic->topic_id;
		}
		echo '</ul>'.PHP_EOL;
	}
}