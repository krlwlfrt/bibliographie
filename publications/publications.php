<?php
function bibliographie_publications_parse_data ($publication, $style) {
	if(is_int($publication))
		$publication = mysql_fetch_object(mysql_query("SELECT * FROM `a2publication` WHERE `pub_id` = ".((int) $publication)));

	if(strpos($style, '..') === false and strpos($style, '/') === false){
		if(is_object($publication)){
			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication->pub_id.'_'.$style.'.txt')){
				return '<strong>CACHED</strong> '.file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication->pub_id.'_'.$style.'.txt');
			}

			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'_'.$publication->pub_type.'.txt'))
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'/'.$publication->pub_type.'.txt'));
			else
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/styles/standard/'.$publication->pub_type.'.txt'));

			$authors = mysql_query("SELECT * FROM
	`a2publicationauthorlink` publications,
	`a2author` authors
WHERE
	publications.`pub_id` = ".((int) $publication->pub_id)." AND
	publications.`author_id` = authors.`author_id`
ORDER BY authors.`surname`, authors.`firstname`");

			$parsedAuthors = (string) '';
			while($author = mysql_fetch_object($authors)){
				if(!empty($parsedAuthors))
					$parsedAuthors .= ' and ';

				if(!empty($author->von))
					$author->surname = $author->von.' '.$author->surname;
				if(!empty($author->jr))
					$author->surname = $author->surname.' '.$author->jr;

				$parsedAuthors .= $author->firstname.' '.$author->surname;
			}

			$parsedPublication = str_replace('[authors]', $parsedAuthors, $parsedPublication);
			$parsedPublication = str_replace('[title]', $publication->title, $parsedPublication);
			$parsedPublication = str_replace('[year]', $publication->year, $parsedPublication);
			$parsedPublication = str_replace('[journal]', $publication->journal, $parsedPublication);
			$parsedPublication = str_replace('[pages]', $publication->pages, $parsedPublication);

			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication->pub_id.'_'.$style.'.txt', 'w+');
			fwrite($cacheFile, $parsedPublication);
			fclose($cacheFile);

			return $parsedPublication;
		}
	}

	return false;
}