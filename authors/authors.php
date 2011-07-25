<?php
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

	$data = json_encode(array(
		'author_id' => mysql_insert_id(),
		'firstname' => $firstname,
		'von' => $von,
		'surname' => $surname,
		'jr' => $jr,
		'email' => $email,
		'url' => $url,
		'institute' => $institute
	));

	if($return)
		bibliographie_log('authors', 'create', $data);

	return $return;
}

function bibliographie_authors_parse_data ($author, $options = array()) {
	if(is_int($author))
		$author = mysql_fetch_object(mysql_query("SELECT * FROM `a2author` WHERE `author_id` = ".((int) $author)));

	if(is_object($author)){
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