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
		bibliographie_log('tags', 'create', json_encode($data));
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
function bibliographie_tags_get_publications ($tag_id) {
	if(is_numeric($tag_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_publications.json'));

		$publicationsResult = mysql_query("SELECT publications.`pub_id`, publications.`year` FROM
	`a2publicationtaglink` relations,
	`a2publication` publications
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	relations.`tag_id` = ".((int) $tag_id)."
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