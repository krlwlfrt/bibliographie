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
	static $author = null;

	if($author_id !== null)
		$author_id = (int) $author_id;

	if($author === null)
		$author = DB::getInstance()->exec('INSERT INTO `a2author` (
	`author_id`,
	`firstname`,
	`von`,
	`surname`,
	`jr`,
	`email`,
	`url`,
	`institute`
) VALUES (
	:author_id,
	:firstname,
	:von,
	:surname,
	:jr,
	:email,
	:url,
	:institute
)');

	$author->bindParam('author_id', $author_id);
	$author->bindParam('firstname', $firstname);
	$author->bindParam('von', $von);
	$author->bindParam('surname', $surname);
	$author->bindParam('jr', $jr);
	$author->bindParam('email', $email);
	$author->bindParam('url', $url);
	$author->bindParam('institute', $institute);

	$return = $author->execute();

	if($author_id === null)
		$author_id = DB::getInstance()->lastInsertId();

	if($return){
		$return = array(
			'author_id' => $author_id,
			'firstname' => $firstname,
			'von' => $von,
			'surname' => $surname,
			'jr' => $jr,
			'email' => $email,
			'url' => $url,
			'institute' => $institute
		);
		bibliographie_log('authors', 'createAuthor', json_encode($return));
	}

	return $return;
}

/**
 *
 * @param type $author_id
 * @param type $firstname
 * @param type $von
 * @param type $surname
 * @param type $jr
 * @param type $email
 * @param type $url
 * @param type $institute
 * @return type
 */
function bibliographie_authors_edit_author ($author_id, $firstname, $von, $surname, $jr, $email, $url, $institute) {
	$dataBefore = (array) bibliographie_authors_get_data($author_id);
	if(is_array($dataBefore)){
		if($firstname != $dataBefore['firstname']
			or $von != $dataBefore['von']
			or $surname != $dataBefore['surname']
			or $jr != $dataBefore['jr']
			or $email != $dataBefore['email']
			or $url != $dataBefore['url']
			or $institute != $dataBefore['institute']){

			mysql_query("UPDATE `a2author` SET
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
 * @staticvar string $author
 * @param type $author_id
 * @param type $type
 * @return type
 */
function bibliographie_authors_get_data ($author_id) {
	static $author = null;

	$return = false;

	if(is_numeric($author_id)){
		$author_id = (int) $author_id;

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.$author_id.'_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.$author_id.'_data.json'));

		if($author === null){
			$author = DB::getInstance()->prepare("SELECT
	`author_id`,
	`firstname`,
	`von`,
	`surname`,
	`jr`,
	`email`,
	`url`,
	`institute`
FROM
	`a2author`
WHERE
	`author_id` = :author_id");
			$author->setFetchMode(PDO::FETCH_OBJ);
		}

		$author->bindParam('author_id', $author_id);
		$author->execute();

		if($author->rowCount() == 1)
			$return = $author->fetch();

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.$author_id.'_data.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
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
 * @staticvar string $publications
 * @param type $author_id
 * @param type $is_editor
 * @return type
 */
function bibliographie_authors_get_publications ($author_id, $is_editor = 0) {
	static $publications = null;

	$author = bibliographie_authors_get_data($author_id);
	$return = false;

	if(is_object($author)){
		$return = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author->author_id).'_'.((int) $is_editor).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author->author_id).'_'.((int) $is_editor).'_publications.json'));

		$_is_editor = 'Y';
		if($is_editor == 0)
			$_is_editor = 'N';

		if($publications === null)
			$publications = DB::getInstance()->prepare('SELECT publications.`pub_id` FROM
	`a2publication` publications,
	`a2publicationauthorlink` relations
WHERE
	publications.`pub_id` = relations.`pub_id` AND
	relations.`author_id` = :author_id AND
	relations.`is_editor` = :is_editor
ORDER BY
	publications.`year` DESC');

		$publications->bindParam('author_id', $author->author_id);
		$publications->bindParam('is_editor', $_is_editor);
		$publications->execute();

		if($publications->rowCount() > 0)
			$return = $publications->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author->author_id).'_'.((int) $is_editor).'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
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
 * @staticvar string $tags
 * @param type $author_id
 * @return array
 */
function bibliographie_authors_get_tags ($author_id) {
	static $tags = null;

	$author = bibliographie_authors_get_data($author_id);
	$return = false;

	if(is_object($author)){
		$return = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author->author_id).'_tags.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author->author_id).'_tags.json'));

		$publications = array_values(array_unique(array_merge(bibliographie_authors_get_publications($author->author_id, 0), bibliographie_authors_get_publications($author->author_id, 1))));

		if(count($publications) > 0){
			$publications = array2csv($publications);

			if($tags === null){
				$tags = DB::getInstance()->prepare('SELECT
	data.`tag`,
	link.`tag_id`,
	COUNT(*) AS `count`
FROM
	`a2publicationtaglink` link
LEFT JOIN (
	SELECT * FROM `a2tags`
)
AS
	data
ON
	link.`tag_id` = data.`tag_id`
WHERE
	FIND_IN_SET(link.`pub_id`, :set)
GROUP BY
	data.`tag_id`
ORDER BY
	data.`tag`');
				$tags->setFetchMode(PDO::FETCH_OBJ);
			}

			$tags->bindParam('set', $publications);
			$tags->execute();

			if($tags->rowCount() > 0)
				$return = $tags->fetchAll();
		}

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.$author->author_id.'_tags.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}