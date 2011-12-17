<?php
function bibliographie_notes_search_notes ($query, $expandedQuery = '') {
	$return = array();

	if(mb_strlen($query) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_notes_'.bibliographie_user_get_id().'_'.md5($query).'_'.md5($expandedQuery)))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_notes_'.bibliographie_user_get_id().'_'.md5($query).'_'.md5($expandedQuery)));

		$notes = DB::getInstance()->prepare('SELECT
	`note_id`,
	`pub_id`,
	`user_id`,
	`text`
FROM (
	SELECT
		`note_id`,
		`pub_id`,
		`user_id`,
		`text`,
		MATCH(`text`) AGAINST (:expanded_query) AS `relevancy`
	FROM
		`'.BIBLIOGRAPHIE_PREFIX.'notes`
) fullTextSerach
WHERE
	`relevancy` > 0 AND
	`user_id` = :user_id
ORDER BY
	`relevancy`,
	`text`,
	`note_id`');

		$notes->execute(array(
			'expanded_query' => $expandedQuery,
			'user_id' => (int) bibliographie_user_get_id()
		));

		if($notes->rowCount() > 0)
			$return = $notes->fetchAll(PDO::FETCH_OBJ);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_notes_'.bibliographie_user_get_id().'_'.md5($query).'_'.md5($expandedQuery), 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

function bibliographie_notes_get_publications_with_notes () {
	static $notes = null;

	if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/notes_'.((int) bibliographie_user_get_id()).'_publications_with_notes.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/notes_'.((int) bibliographie_user_get_id()).'_publications_with_notes.json'));

	$return = array();

	if($notes === null)
		$notes = DB::getInstance()->prepare('SELECT
	`pub_id`,
	`note_id`,
	`user_id`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
WHERE
	`user_id` = :user_id
GROUP BY
	`pub_id`
ORDER BY
	`note_id`');

	$notes->execute(array(
		'user_id' => (int) bibliographie_user_get_id()
	));

	if($notes->rowCount() > 0)
		$return = $notes->fetchAll(PDO::FETCH_COLUMN, 0);

	if(BIBLIOGRAPHIE_CACHING){
		$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/notes_'.((int) bibliographie_user_get_id()).'_publications_with_notes.json', 'w+');
		fwrite($cacheFile, json_encode($return));
		fclose($cacheFile);
	}

	return $return;
}

function bibliographie_notes_get_notes_of_publication ($pub_id) {
	static $notes = null;

	$return = array();

	if($notes === null)
		$notes = DB::getInstance()->prepare('SELECT
	`note_id`,
	`pub_id`,
	`user_id`,
	`text`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
WHERE
	`pub_id` = :pub_id AND
	`user_id` = :user_id
ORDER BY
	`note_id`');

	$notes->execute(array(
		'pub_id' => (int) $pub_id,
		'user_id' => (int) bibliographie_user_get_id()
	));

	if($notes->rowCount() > 0)
		$return = $notes->fetchAll(PDO::FETCH_OBJ);

	return $return;
}