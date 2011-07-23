<?php
function bibliographie_authors_create_author ($firstname, $von, $surname, $jr, $email, $url, $institute) {
	return mysql_query("INSERT INTO `a2author` (
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
}