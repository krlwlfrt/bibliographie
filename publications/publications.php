<?php
function bibliographie_publications_parse_data ($publication_id, $style = 'standard', $textOnly = false) {
	if(is_numeric($publication_id) and strpos($style, '..') === false and strpos($style, '/') === false){
		if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication_id.'_'.$style.'.txt'))
			return file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication_id.'_'.$style.'.txt');

		$publication = mysql_query("SELECT * FROM `a2publication` WHERE `pub_id` = ".((int) $publication_id));
		if(mysql_num_rows($publication) == 1){
			$publication = mysql_fetch_assoc($publication);

			$settings = array();
			$parsedPublication = (string) '';
			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'/'.$publication['pub_type'].'.txt')){
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'/'.$publication['pub_type'].'.txt'));
				$settings = parse_ini_file(BIBLIOGRAPHIE_ROOT_PATH.'/styles/'.$style.'/settings.ini', true);
			}else{
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/styles/standard/'.$publication['pub_type'].'.txt'));
				$settings = parse_ini_file(BIBLIOGRAPHIE_ROOT_PATH.'/styles/standard/settings.ini', true);
			}

			$authors = mysql_query("SELECT * FROM
	`a2publicationauthorlink` relations,
	`a2author` authors
WHERE
	relations.`pub_id` = ".((int) $publication['pub_id'])." AND
	relations.`author_id` = authors.`author_id` AND
	relations.`is_editor` = 'N'
ORDER BY authors.`surname`, authors.`firstname`");

			$parsedAuthors = (string) '';
			$i = (int) 0;
			while($author = mysql_fetch_object($authors)){
				if(!empty($parsedAuthors))
					if(mysql_num_rows($authors) == 2 or ($i + 1) == mysql_num_rows($authors))
						$parsedAuthors .= $settings['authors']['authorDividerLast'];
					else
						$parsedAuthors .= $settings['authors']['authorDivider'];

				if(!empty($author->von))
					$author->surname = $author->von.' '.$author->surname;
				if(!empty($author->jr))
					$author->surname = $author->surname.' '.$author->jr;

				$parsedAuthor = (string) '';
				if($settings['authors']['nameOrder'] == 'surnamesFirst')
					$parsedAuthor = $author->surname.$settings['authors']['nameDivider'].$author->firstname;
				else
					$parsedAuthor = $author->firstname.' '.$author->surname;

				if(!$textOnly)
					$parsedAuthor = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&author_id='.$author->author_id.'">'.$parsedAuthor.'</a>';

				$parsedAuthors .= $parsedAuthor;

				$i++;
			}

			$parsedPublication = str_replace('[authors]', $parsedAuthors, $parsedPublication);

			if($settings['title']['titleStyle'] == 'italic')
				$publication['title'] = '<em>'.$publication['title'].'</em>';

			if(!$textOnly)
				$publication['title'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&pub_id='.$publication['pub_id'].'">'.$publication['title'].'</a>';

			foreach($publication as $key => $value){
				if(empty($value))
					$value = '<span class="error">The required field <em>'.$key.'</em> is missing!</span>';

				$parsedPublication = str_replace('['.$key.']', $value, $parsedPublication);
			}

			$parsedPublication = '<strong>'.$publication['pub_type'].'</strong> '.$parsedPublication;

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication['pub_id'].'_'.$style.'.txt', 'w+');
				fwrite($cacheFile, $parsedPublication);
				fclose($cacheFile);
			}

			return $parsedPublication;
		}
	}

	return false;
}