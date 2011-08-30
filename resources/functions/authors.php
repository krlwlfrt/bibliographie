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
function bibliographie_authors_create_author ($firstname, $von, $surname, $jr, $email, $url, $institute) {
	$return = mysql_query("INSERT INTO `a2author` (
	`firstname`,
	`von`,
	`surname`,
	`jr`,
	`email`,
	`url`,
	`institute`
) VALUES (
	'".mysql_real_escape_string(stripslashes($firstname))."',
	'".mysql_real_escape_string(stripslashes($von))."',
	'".mysql_real_escape_string(stripslashes($surname))."',
	'".mysql_real_escape_string(stripslashes($jr))."',
	'".mysql_real_escape_string(stripslashes($email))."',
	'".mysql_real_escape_string(stripslashes($url))."',
	'".mysql_real_escape_string(stripslashes($institute))."'
)");

	$data = array(
		'author_id' => mysql_insert_id(),
		'firstname' => $firstname,
		'von' => $von,
		'surname' => $surname,
		'jr' => $jr,
		'email' => $email,
		'url' => $url,
		'institute' => $institute
	);

	if($return){
		bibliographie_log('authors', 'create', json_encode($data));
		return $data;
	}

	return $return;
}

function bibliographie_authors_parse_data ($author, $options = array()) {
	if(is_numeric($author))
		$author = mysql_fetch_object(mysql_query("SELECT * FROM `a2author` WHERE `author_id` = ".((int) $author)));

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

		if($options['firstnameFirst'] == true)
			return $author->firstname.' '.$author->surname;

		if($options['splitNames'] == true)
			return array('firstname' => $author->firstname, 'surname' => $author->surname);

		return $author->surname.', '.$author->firstname;
	}

	return false;
}

function bibliographie_authors_get_list () {

}

function bibliographie_authors_get_publications ($author_id) {
	if(is_numeric($author_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_publications.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_publications.json'));

		$publicationsResult = mysql_query("SELECT publications.`pub_id` FROM
		`a2publicationauthorlink` relations,
		`a2publication` publications
	WHERE
		publications.`pub_id` = relations.`pub_id` AND
		relations.`author_id` = ".((int) $author_id)." AND
		relations.`is_editor` = 'N'
	ORDER BY
		publications.`year` DESC");

		$publicationsArray = array();
		while($publication = mysql_fetch_object($publicationsResult))
			$publicationsArray[] = $publication->pub_id;

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/author_'.((int) $author_id).'_publications.json', 'w+');
			fwrite($cacheFile, json_encode($publicationsArray));
			fclose($cacheFile);
		}

		return $publicationsArray;
	}

	return false;
}