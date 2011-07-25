<?php
function bibliographie_publications_parse_data ($publication, $style = 'standard') {
	if(is_int($publication))
		$publication = mysql_fetch_object(mysql_query("SELECT * FROM `a2publication` WHERE `pub_id` = ".((int) $publication)));

	if(strpos($style, '..') === false and strpos($style, '/') === false){
		if(is_object($publication)){
			/**if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication->pub_id.'_'.$style.'.txt')){
				return file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication->pub_id.'_'.$style.'.txt');
			}*/

			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'_'.$publication->pub_type.'.txt'))
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'/'.$publication->pub_type.'.txt'));
			else
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/styles/standard/'.$publication->pub_type.'.txt'));

			$authors = mysql_query("SELECT * FROM
	`a2publicationauthorlink` relations,
	`a2author` authors
WHERE
	relations.`pub_id` = ".((int) $publication->pub_id)." AND
	relations.`author_id` = authors.`author_id` AND
	relations.`is_editor` = 'N'
ORDER BY authors.`surname`, authors.`firstname`");

			$parsedAuthors = (string) '';
			$i = (int) 0;
			while($author = mysql_fetch_object($authors)){
				if(!empty($parsedAuthors))
					if(mysql_num_rows($authors) == 2 or ($i + 1) == mysql_num_rows($authors))
						$parsedAuthors .= ' and ';
					else
						$parsedAuthors .= ', ';

				if(!empty($author->von))
					$author->surname = $author->von.' '.$author->surname;
				if(!empty($author->jr))
					$author->surname = $author->surname.' '.$author->jr;

				$parsedAuthors .= $author->firstname.' '.$author->surname;

				$i++;
			}

			$parsedPublication = str_replace('[authors]', $parsedAuthors, $parsedPublication);
			$parsedPublication = str_replace('[title]', $publication->title, $parsedPublication);
			$parsedPublication = str_replace('[year]', $publication->year, $parsedPublication);

			$parsedPublication = str_replace('[journal]', $publication->journal, $parsedPublication);
			$parsedPublication = str_replace('[booktitle]', $publication->booktitle, $parsedPublication);

			$parsedPublication = str_replace('[pages]', $publication->pages, $parsedPublication);
			$parsedPublication = str_replace('[publisher]', $publication->publisher, $parsedPublication);

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication->pub_id.'_'.$style.'.txt', 'w+');
				fwrite($cacheFile, $parsedPublication);
				fclose($cacheFile);
			}

			return '<strong>'.$publication->pub_type.'</strong> '.$parsedPublication;
		}
	}

	return false;
}