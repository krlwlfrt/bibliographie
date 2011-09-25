<?php
/**
 * Creates an author and returns false or the data of the created author.
 * @param string $firstname
 * @param string $von
 * @param string $surname
 * @param string $jr
 * @param string $email
 * @param string $url
 * @param string $institute
 * @return mixed False or array of data on success.
 */
function bibliographie_authors_create_author ($firstname, $von, $surname, $jr, $email, $url, $institute, $author_id = null) {
	if($author_id === null)
		$author_id = 'NULL';
	else
		$author_id = (int) $author_id;

	$return = _mysql_query("INSERT INTO `a2author` (
	`author_id`,
	`firstname`,
	`von`,
	`surname`,
	`jr`,
	`email`,
	`url`,
	`institute`
) VALUES (
	".$author_id.",
	'".mysql_real_escape_string(stripslashes($firstname))."',
	'".mysql_real_escape_string(stripslashes($von))."',
	'".mysql_real_escape_string(stripslashes($surname))."',
	'".mysql_real_escape_string(stripslashes($jr))."',
	'".mysql_real_escape_string(stripslashes($email))."',
	'".mysql_real_escape_string(stripslashes($url))."',
	'".mysql_real_escape_string(stripslashes($institute))."'
)");

	if($author_id == 'NULL')
		$author_id = mysql_insert_id();

	$data = array(
		'author_id' => $author_id,
		'firstname' => $firstname,
		'von' => $von,
		'surname' => $surname,
		'jr' => $jr,
		'email' => $email,
		'url' => $url,
		'institute' => $institute
	);

	if($return)
		bibliographie_log('authors', 'createAuthor', json_encode($data));

	return $data;
}

function bibliographie_authors_edit_author ($author_id, $firstname, $von, $surname, $jr, $email, $url, $institute) {
	$dataBefore = bibliographie_authors_get_data($author_id, 'assoc');
	if(is_array($dataBefore)){
		if($firstname != $dataBefore['firstname']
			or $von != $dataBefore['von']
			or $surname != $dataBefore['surname']
			or $jr != $dataBefore['jr']
			or $email != $dataBefore['email']
			or $url != $dataBefore['url']
			or $institute != $dataBefore['institute']){

			_mysql_query("UPDATE `a2author` SET
	`firstname` = '".mysql_real_escape_string(stripslashes($firstname))."',
	`von` = '".mysql_real_escape_string(stripslashes($von))."',
	`surname` = '".mysql_real_escape_string(stripslashes($surname))."',
	`jr` = '".mysql_real_escape_string(stripslashes($jr))."',
	`email` = '".mysql_real_escape_string(stripslashes($email))."',
	`url` = '".mysql_real_escape_string(stripslashes($url))."',
	`institute` = '".mysql_real_escape_string(stripslashes($institute))."'
WHERE
	`author_id` = ".((int) $dataBefore['author_id'])."
LIMIT 1");
		}

		$data = array(
			'dataBefore' => $dataBefore,
			'dataAfter' => array(
				'author_id' => $dataBefore['author_id'],
				'firstname' => $firstname,
				'von' => $von,
				'surname' => $surname,
				'jr' => $jr,
				'email' => $email,
				'url' => $url,
				'institute' => $institute
			)
		);

		if($data['dataBefore'] != $data['dataAfter']){
			bibliographie_log('authors', 'editAuthor', json_encode($data));
			bibliographie_purge_cache('author_'.((int) $dataBefore['author_id']));
		}

		return $data;
	}
}

/**
 *
 * @param type $author_id
 * @param type $type
 * @return type
 */
function bibliographie_authors_get_data ($author_id, $type = 'object') {
	if(is_numeric($author_id)){
		$assoc = false;
		if($type == 'assoc')
			$assoc = true;

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_data.json'), $assoc);

		$author = _mysql_query("SELECT `author_id`, `firstname`, `von`, `surname`, `jr`, `email`, `url`, `institute` FROM `a2author` WHERE `author_id` = ".((int) $author_id));
		if(mysql_num_rows($author) == 1){
			if($assoc)
				$author = mysql_fetch_assoc($author);
			else
				$author = mysql_fetch_object($author);

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($author));
				fclose($cacheFile);
			}

			return $author;
		}
	}

	return false;
}

/**
 *
 * @param type $author
 * @param type $options
 * @return string
 */
function bibliographie_authors_parse_data ($author, $options = array()) {
	if(is_numeric($author))
		$author = bibliographie_authors_get_data($author);

	if(is_object($author)){
		if($options['forBibTex'] == true)
			return array (
				'first' => $author->firstname,
				'von' => $author->von,
				'last' => $author->surname,
				'jr' => $author->jr
			);

		$author->surname = '<strong>'.$author->surname.'</strong>';

		if(!empty($author->von))
			$author->surname = $author->von.' '.$author->surname;

		if(!empty($author->jr))
			$author->surname = $author->surname.' '.$author->jr;

		if($options['splitNames'] == true)
			return array('firstname' => $author->firstname, 'surname' => $author->surname);

		$return = $author->surname.', '.$author->firstname;

		if($options['firstnameFirst'] == true)
			$return = $author->firstname.' '.$author->surname;

		if($options['linkProfile'] == true)
			$return = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.$author->author_id.'">'.$return.'</a>';

		return $return;
	}

	return false;
}

/**
 *
 * @param type $author_id
 * @param type $editor
 * @return type
 */
function bibliographie_authors_get_publications ($author_id, $editor = 0) {
	if(is_numeric($author_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_'.((int) $editor).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_'.((int) $editor).'_publications.json'));

		if($editor == 0)
			$mysql_editor = 'N';
		else
			$mysql_editor = 'Y';

		$publicationsResult = _mysql_query("SELECT publications.`pub_id` FROM
		`a2publicationauthorlink` relations,
		`a2publication` publications
	WHERE
		publications.`pub_id` = relations.`pub_id` AND
		relations.`author_id` = ".((int) $author_id)." AND
		relations.`is_editor` = '".mysql_real_escape_string(stripslashes($mysql_editor))."'
	ORDER BY
		publications.`year` DESC");

		$publicationsArray = array();
		while($publication = mysql_fetch_object($publicationsResult))
			$publicationsArray[] = $publication->pub_id;

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_'.((int) $editor).'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($publicationsArray));
			fclose($cacheFile);
		}

		return $publicationsArray;
	}

	return false;
}

/**
 *
 * @param type $string
 * @return type
 */
function bibliographie_authors_populate_input ($string) {
	if(is_csv($string, 'int')){
		$authors = csv2array($string, 'int');
		if(count($authors) > 0){
			$populate = array();
			foreach($authors as $author)
				$populate[] = array (
					'id' => $author,
					'name' => bibliographie_authors_parse_data($author)
				);

			return $populate;
		}
	}

	return array();
}

/**
 *
 * @param type $author_id
 * @return type
 */
function bibliographie_authors_get_tags ($author_id) {
	$return = array();

	if(is_numeric($author_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_tags.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_tags.json'));

		$author = bibliographie_authors_get_data($author_id);

		if(is_object($author)){
			$publications = array_unique(array_merge(bibliographie_authors_get_publications($author->author_id, 0), bibliographie_authors_get_publications($author->author_id, 1)));

			if(count($publications) > 0){
				$tags = _mysql_query("SELECT *, COUNT(*) AS `count` FROM `a2publicationtaglink` link LEFT JOIN (
			SELECT * FROM `a2tags`
		) AS data ON link.`tag_id` = data.`tag_id` WHERE FIND_IN_SET(link.`pub_id`, '".implode(',', $publications)."') GROUP BY data.`tag_id` ORDER BY data.`tag`");

				if(mysql_num_rows($tags))
					while($tag = mysql_fetch_object($tags))
						$return[] = $tag;

				if(BIBLIOGRAPHIE_CACHING){
					$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.$author->author_id.'_tags.json', 'w+');
					fwrite($cacheFile, json_encode($return));
					fclose($cacheFile);
				}
			}
		}
	}

	return $return;
}