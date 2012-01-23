<?php
/**
 * Create a tag.
 * @param string $tag
 * @return mixed
 */
function bibliographie_tags_create_tag ($tag, $tag_id = null) {
	static $createTag = null;

	if($createTag === null)
		$createTag = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'tags` (
	`tag_id`,
	`tag`
) VALUES (
	:tag_id
	:tag
)');

	$return = $createTag->execute(array(
		'tag_id' => $tag_id,
		'tag' => $tag
	));

	if($return){
		$return = array(
			'tag' => $tag
		);
		if($tag_id === null)
			$return['tag_id'] = DB::getInstance()->lastInsertId();

		bibliographie_log('tags', 'createTag', json_encode($return));
		bibliographie_cache_purge('search_');
	}

	return $return;
}

/**
 * Parses the name of a tag given by id.
 * @staticvar string $tag
 * @param int $tag_id
 * @return mixed
 */
function bibliographie_tags_parse_tag ($tag_id, array $options = array()) {
	$tag = bibliographie_tags_get_data($tag_id);

	$return = false;

	if(is_object($tag)){
		$return = htmlspecialchars($tag->tag);

		if($options['linkProfile'] == true)
			$return = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/tags/?task=showTag&amp;tag_id='.((int) $tag->tag_id).'">'.$return.'</a>';
	}

	return $return;
}

/**
 * Get the data of a tag by its id.
 * @staticvar string $tag
 * @param int $tag_id
 * @return mixed
 */
function bibliographie_tags_get_data ($tag_id) {
	static $tag = null;
	$return = false;

	if(is_numeric($tag_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_data.json'));

		if($tag === null){
			$tag = DB::getInstance()->prepare('SELECT * FROM `'.BIBLIOGRAPHIE_PREFIX.'tags` WHERE `tag_id` = :tag_id');
			$tag->setFetchMode(PDO::FETCH_OBJ);
		}

		$tag->execute(array(
			'tag_id' => (int) $tag_id
		));

		if($tag->rowCount() == 1){
			$return = $tag->fetch();

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.((int) $tag_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($return));
				fclose($cacheFile);
			}
		}
	}

	return $return;
}

/**
 * Get the publications that are assigned to a tag.
 * @param int $tag_id
 * @return mixed Array on success, false on error.
 */
function bibliographie_tags_get_publications ($tag_id) {
	static $publications = null;

	$tag = bibliographie_tags_get_data($tag_id);

	$return = false;

	if(is_object($tag)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_publications.json'));

		$return = array();

		if($publications === null){
			$publications = DB::getInstance()->prepare('SELECT publications.`pub_id`, publications.`year` FROM
	`'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink` relations,
	`'.BIBLIOGRAPHIE_PREFIX.'publication` publications
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	relations.`tag_id` = :tag_id
ORDER BY
	publications.`year` DESC,
	publications.`pub_id` DESC');
			$publications->setFetchMode(PDO::FETCH_OBJ);
		}

		$publications->bindParam('tag_id', $tag->tag_id);
		$publications->execute();

		if($publications->rowCount() > 0)
			$return = $publications->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 * Get the publications that
 * @staticvar string $publications
 * @param type $tag_id
 * @param type $author_id
 * @return type
 */
function bibliographie_tags_get_publications_with_author ($tag_id, $author_id) {
	$tag = bibliographie_tags_get_data($tag_id);
	$author = bibliographie_authors_get_data($author_id);

	$return = false;

	if(is_object($tag) and is_object($author)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_author_'.$author->author_id.'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_author_'.$author->author_id.'_publications.json'));

		$tagPublications = bibliographie_tags_get_publications($tag_id);
		$authorPublications = bibliographie_authors_get_publications($author_id);

		$return = array_values(array_intersect($tagPublications, $authorPublications));

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_author_'.$author->author_id.'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $tag_id
 * @param type $topic_id
 * @return type
 */
function bibliographie_tags_get_publications_with_topic ($tag_id, $topic_id) {
	$tag = bibliographie_tags_get_data($tag_id);
	$topic = bibliographie_topics_get_data($topic_id);

	$return = false;

	if(is_object($tag) and is_object($topic)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_author_'.$topic->topic_id.'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_author_'.$topic->topic_id.'_publications.json'));

		$tagPublications = bibliographie_tags_get_publications($tag_id);
		$topicPublications = bibliographie_topics_get_publications($topic_id);

		$return = array_values(array_intersect($tagPublications, $topicPublications));

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/tag_'.$tag->tag_id.'_author_'.$topic->topic_id.'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 * Print the cloud for an array of tags.
 * @param array $tags
 * @param array $options
 */
function bibliographie_tags_print_cloud (array $tags, array $options = array()) {
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
			$count = 0;
			if(!empty($tag->count))
				$count = $tag->count;

			$tag = bibliographie_tags_get_data($tag->tag_id);

			if($count != 0)
				$tag->count = $count;
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

function bibliographie_tags_populate_input ($tags) {
	$prePopulateTags = array();

	if(!empty($tags)){
		if(is_csv($tags, 'int')){
			$tags = csv2array($tags, 'int');
			foreach($tags as $tag)
				$prePopulateTags[] = array (
					'id' => $tag,
					'name' => bibliographie_tags_parse_tag($tag)
				);
		}
	}

	return $prePopulateTags;
}

function bibliographie_tags_search_tags ($query, $expandedQuery = '') {
	$return = array();

	if(mb_strlen($query) >= 1){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_tags_'.md5($query).'_'.md5($expandedQuery).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_tags_'.md5($query).'_'.md5($expandedQuery).'.json'));

		$tags = DB::getInstance()->prepare('SELECT
	`tag_id`,
	`tag`,
	`relevancy`
FROM (
	SELECT
		`tag_id`,
		`tag`,
		IF(`innerRelevancy` = 0, 1, `innerRelevancy`) AS `relevancy`
	FROM (
		SELECT
			`tag_id`,
			`tag`,
			MATCH(
				`tag`
			) AGAINST (
				:expanded_query
			) AS `innerRelevancy`
		FROM
			`'.BIBLIOGRAPHIE_PREFIX.'tags`

	) fullTextSearch
) likeMatching
WHERE
	`relevancy` > 0 AND
	`tag` LIKE "%'.trim(DB::getInstance()->quote($query), '\'').'%"
ORDER BY
	`relevancy` DESC,
	LENGTH(`tag`),
	`tag` ASC,
	`tag_id`');
		$tags->execute(array(
			'expanded_query' => $expandedQuery
		));

		if($tags->rowCount() > 0)
			$return = $tags->fetchAll(PDO::FETCH_OBJ);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_tags_'.md5($query).'_'.md5($expandedQuery).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}