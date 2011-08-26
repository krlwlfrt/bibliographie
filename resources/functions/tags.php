<?php
/**
 * Create a tag.
 * @param type $tag
 * @return type
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
 * @param type $tag_id
 * @return type
 */
function bibliographie_tags_tag_by_id ($tag_id) {
	$tag_result = mysql_query("SELECT * FROM `a2tags` WHERE `tag_id` = ".((int) $tag_id));
	if(mysql_num_rows($tag_result)){
		$tag = mysql_fetch_object($tag_result);
		return $tag->tag;
	}

	return false;
}