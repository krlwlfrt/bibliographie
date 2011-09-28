<?php
/**
 * Create a tag.
 * @param string $tag
 * @return int
 */
function bibliographie_tags_create_tag ($tag) {
	$return = mysql_query("INSERT INTO `a2tags` (
	`tag`
) VALUES (
	'".mysql_real_escape_string(stripslashes($tag))."'
)");

	$data = array(
		'tag_id' => mysql_insert_id(),
		'tag' => $tag
	);

	if($return){
		bibliographie_log('tags', 'createTag', json_encode($data));
		return $data;
	}

	return $return;
}

/**
 * Get the name of a tag by its id.
 * @param int $tag_id
 * @return mixed String on success, false on error.
 */
function bibliographie_tags_tag_by_id ($tag_id) {
	$tag_result = mysql_query("SELECT * FROM `a2tags` WHERE `tag_id` = ".((int) $tag_id));
	if(mysql_num_rows($tag_result)){
		$tag = mysql_fetch_object($tag_result);
		return $tag->tag;
	}

	return false;
}

/**
 * Get the data of a tag by its id.
 * @param int $tag_id
 * @param string $type Object or assoc.
 * @return mixed Object or assoc on success, false on error.
 */
function bibliographie_tags_get_data ($tag_id, $type = 'object') {
	if(is_numeric($tag_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_data.json')){
			$assoc = false;
			if($type == 'assoc')
				$assoc = true;

			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_data.json'), $assoc);
		}

		$tag = mysql_query("SELECT * FROM `a2tags` WHERE `tag_id` = ".((int) $tag_id));
		if(mysql_num_rows($tag) == 1){
			if($type == 'object')
				$tag = mysql_fetch_object($tag);
			else
				$tag = mysql_fetch_assoc($tag);

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($tag));
				fclose($cacheFile);
			}

			return $tag;
		}
	}

	return false;
}

/**
 * Get the publications that are assigned to a tag.
 * @param int $tag_id
 * @return mixed Array on success, false on error.
 */
function bibliographie_tags_get_publications ($tag_id, $options = array()) {
	if(is_numeric($tag_id)){

		/*if(BIBLIOGRAPHIE_CACHING){
			if(is_numeric($options['author_id']) and bibliographie_authors_get_data($options['author_id']) and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_author_'.((int) $options['author_id']).'_publications.json'))
				return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_author_'.((int) $options['author_id']).'_publications.json'));

			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_publications.json'))
				return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_publications.json'));
		}*/

		$where_clause = (string) "";
		$add_table = (string) "";
		if(is_numeric($options['author_id']) and bibliographie_authors_get_data($options['author_id'])){
			$add_table .= ", `a2publicationauthorlink` authors ";
			$where_clause = " AND publications.`pub_id` = authors.`pub_id` AND authors.`author_id` = ".((int) $options['author_id'])." ";
		}elseif(is_numeric($options['topic_id']) and bibliographie_topics_get_data($options['topic_id'])){
			$add_table .= ", `a2topicpublicationlink` topics ";
			$where_clause = " AND publications.`pub_id` = topics.`pub_id` AND topics.`topic_id` = ".((int) $options['topic_id'])." ";
		}

		$publicationsResult = mysql_query("SELECT publications.`pub_id`, publications.`year` FROM
	`a2publicationtaglink` relations,
	`a2publication` publications".$add_table."
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	relations.`tag_id` = ".((int) $tag_id).$where_clause."
ORDER BY
	publications.`year` DESC");

		$publicationsArray = array();
		while($publication = mysql_fetch_object($publicationsResult))
			if(!in_array($publication->pub_id, $publicationsArray))
				$publicationsArray[] = $publication->pub_id;

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($publicationsArray));
			fclose($cacheFile);
		}

		return $publicationsArray;
	}

	return false;
}

function bibliographie_tags_print_cloud ($tags, $options = array()) {
	if(is_array($tags) and count($tags) > 0){
		$query = (string) '';
		if(is_numeric($options['author_id']) and bibliographie_authors_get_data($options['author_id']))
			$query = '&amp;author_id='.((int) $options['author_id']);
		elseif(is_numeric($options['topic_id']) and bibliographie_topics_get_data($options['topic_id']))
			$query = '&amp;topic_id='.((int) $options['topic_id']);
?>

	<div id="bibliographie_tag_cloud" style="border: 1px solid #aaa; border-radius: 20px; font-size: 0.8em; text-align: center; padding: 20px;">
<?php
		foreach($tags as $tag){
			$tag = bibliographie_tags_get_data($tag->tag_id);
			/**
			 * Converges against BIBLIOGRAPHIE_TAG_SIZE_FACTOR.
			 */
			$size = BIBLIOGRAPHIE_TAG_SIZE_FACTOR * $tag->count / ($tag->count + BIBLIOGRAPHIE_TAG_SIZE_FLATNESS);
			$size = ($size < BIBLIOGRAPHIE_TAG_SIZE_MINIMUM) ? BIBLIOGRAPHIE_TAG_SIZE_MINIMUM : $size;
?>

	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/?task=showTag&amp;tag_id=<?php echo $tag->tag_id.$query?>" style="font-size: <?php echo round($size, 2).'px'?>; line-height: <?php echo $size.'px'?>;padding: 10px; text-transform: lowercase;" title="<?php echo $tag->count?> publications"><?php echo $tag->tag?></a>
<?php
		}
?>

</div>
<?php
	}
}

function bibliographie_tags_parse_tag ($tag_id, $options = array()) {
	if(is_numeric($tag_id)){
		$tag = bibliographie_tags_get_data($tag_id);

		if(is_object($tag)){
			$tag->tag = htmlspecialchars($tag->tag);

			if($options['linkProfile'] == true)
				$tag->tag = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/tags/?task=showTag&amp;tag_id='.((int) $tag->tag_id).'">'.$tag->tag.'</a>';

			return $tag->tag;
		}
	}

	return false;
}