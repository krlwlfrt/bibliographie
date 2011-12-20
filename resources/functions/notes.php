<?php
function bibliographie_notes_search_notes ($query, $expandedQuery = '') {
	static $notes = null;

	$return = array();

	if(mb_strlen($query) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_notes_'.bibliographie_user_get_id().'_'.md5($query).'_'.md5($expandedQuery).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_notes_'.bibliographie_user_get_id().'_'.md5($query).'_'.md5($expandedQuery).'.json'));

		if(!($notes instanceof PDOStatement))
			$notes = DB::getInstance()->prepare('SELECT
	`note_id`,
	`pub_id`,
	`user_id`,
	`text`,
	`relevancy`
FROM (
	SELECT
		`note_id`,
		`pub_id`,
		`user_id`,
		`text`,
		IF(`innerRelevancy` = 0, 1, `innerRelevancy`) AS `relevancy`
	FROM (
		SELECT
			`note_id`,
			`pub_id`,
			`user_id`,
			`text`,
			MATCH(
				`text`
			) AGAINST (
				:expanded_query
			) AS `innerRelevancy`
		FROM
			`'.BIBLIOGRAPHIE_PREFIX.'notes`

	) fullTextSearch
) likeMatching
WHERE
	`relevancy` > 0 AND
	`user_id` = :user_id AND
	`text` LIKE :query
ORDER BY
	`relevancy` DESC,
	LENGTH(`text`),
	`text` ASC,
	`note_id` ASC');

		$notes->execute(array(
			'expanded_query' => $expandedQuery,
			'query' => '%'.$query.'%',
			'user_id' => (int) bibliographie_user_get_id()
		));

		if($notes->rowCount() > 0)
			$return = $notes->fetchAll(PDO::FETCH_OBJ);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_notes_'.bibliographie_user_get_id().'_'.md5($query).'_'.md5($expandedQuery).'.json', 'w+');
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

function bibliographie_notes_get_publications_from_notes (array $notes) {
	$return = array();

	if(count($notes) > 0){
		foreach($notes as $note)
			if(!empty($note->pub_id) and is_numeric($note->pub_id))
				$return[] = $note->pub_id;
	}

	return $return;
}

function bibliographie_notes_get_data ($note_id) {
	static $note = null;

	$return = false;

	if(is_numeric($note_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/note_'.((int) $note_id.'.json')))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/note_'.((int) $note_id.'.json')));

		if(!($note instanceof PDOStatement)){
			$note = DB::getInstance()->prepare('SELECT
	`note_id`,
	`pub_id`,
	`user_id`,
	`text`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
WHERE
	`note_id` = :note_id
LIMIT 1');
			$note->setFetchMode(PDO::FETCH_OBJ);
		}

		$note->execute(array(
			'note_id' => (int) $note_id
		));

		if($note->rowCount() == 1){
			$return = $note->fetch();

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/note_'.((int) $note_id.'.json'), 'w+');
				fwrite($cacheFile, json_encode($return));
				fclose($cacheFile);
			}
		}
	}

	return $return;
}

function bibliographie_notes_print_note ($note_id) {
	$return = false;

	$note = bibliographie_notes_get_data($note_id);

	if(is_object($note)){
		$return .= '<div class="bibliographie_notes_note">';
		$return .= '<div class="bibliographie_notes_actions">';
		$return .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/notes/?task=noteEditor&amp;note_id='.((int) $note->note_id).'">'.bibliographie_icon_get('note-edit').'</a>';
		$return .= '<a href="javascript:;" onclick="bibliographie_notes_confirm_delete('.((int) $note->note_id).')">'.bibliographie_icon_get('note-delete').'</a>';
		$return .= '</div>';
		$return .= $note->text;
		$return .= '<div class="bibliographie_notes_publication_link">'.bibliographie_publications_parse_data($note->pub_id).'</div>';
		$return .= '</div>';
	}

	return $return;
}

function bibliographie_notes_create_note ($pub_id, $text) {
	static $createNote = null;

	$return = false;

	$publication = bibliographie_publications_get_data($pub_id);

	if(is_object($publication)){
		if(!($createNote instanceof PDOStatement))
			$createNote = DB::getInstance()->prepare('INSERT
INTO
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
(
	`pub_id`,
	`user_id`,
	`text`
) VALUES (
	:pub_id,
	:user_id,
	:text
)');
		$data = array (
			'pub_id' => (int) $publication->pub_id,
			'user_id' => (int) bibliographie_user_get_id(),
			'text' => $text
		);
		if($createNote->execute($data)){
			bibliographie_cache_purge('search_notes_'.bibliographie_user_get_id());
			bibliographie_cache_purge('notes_'.((int) bibliographie_user_get_id()));
			bibliographie_log('notes', 'createNote', json_encode($data));
			$return = true;
		}
	}

	return $return;
}

function bibliographie_notes_edit_note ($note_id, $text) {
	static $editNote = null;

	$return = false;

	$note = bibliographie_notes_get_data($note_id);

	if(is_object($note)){
		if(!($editNote instanceof PDOStatement))
			$editNote = DB::getInstance()->prepare('UPDATE
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
SET
	`text` = :text
WHERE
	`note_id` = :note_id
LIMIT
	1');
		$data = array (
			'note_id' => (int) $note->note_id,
			'text' => $text
		);
		if($editNote->execute($data)){
			$data['textBefore'] = $note->text;

			bibliographie_cache_purge('search_notes_'.bibliographie_user_get_id());
			bibliographie_cache_purge('notes_'.((int) bibliographie_user_get_id()));
			bibliographie_cache_purge('note_'.((int) $note->note_id));
			bibliographie_log('notes', 'editNote', json_encode($data));

			$return = true;
		}
	}

	return $return;
}

function bibliographie_notes_delete_note ($note_id) {
	static $deleteNote = null;

	$return = false;

	$note = bibliographie_notes_get_data($note_id);

	if(is_object($note)){
		if(!($deleteNote instanceof PDOStatement))
			$deleteNote = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'notes`
WHERE
	`note_id` = :note_id
LIMIT
	1');

		$return = $deleteNote->execute(array(
			'note_id' => (int) $note->note_id
		));

		if($return){
			bibliographie_cache_purge('search_notes_'.bibliographie_user_get_id());
			bibliographie_cache_purge('notes_'.((int) bibliographie_user_get_id()));
			bibliographie_cache_purge('note_'.((int) $note->note_id));
			bibliographie_log('notes', 'deleteNote', json_encode($note));
		}
	}

	return $return;
}