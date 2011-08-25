<?php
$bibliographie_publication_types = array (
	'Article',
	'Book',
	'Booklet',
	'Inbook',
	'Incollection',
	'Inproceedings',
	'Manual',
	'Masterthesis',
	'Misc',
	'Phdthesis',
	'Proceedings',
	'Techreport',
	'Unpublished'
);

$bibliographie_publication_months = array (
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July',
	'August',
	'September',
	'October',
	'November',
	'December'
);

$bibliographie_publication_fields = array (
	'article' => array (
		array (
			'author',
			'title',
			'journal',
			'year'
		),
		array (
			'volume',
			'number',
			'pages',
			'month',
			'note'
		)
	),
	'book' => array (
		array (
			'author,editor',
			'title',
			'publisher',
			'year'
		),
		array (
			'volume',
			'number',
			'series',
			'address',
			'edition',
			'month',
			'note'
		)
	),
	'booklet' => array (
		array (
			'title'
		),
		array (
			'author',
			'howpublished',
			'address',
			'month',
			'year',
			'note'
		)
	)
);

/**
 * Get the data of a publication.
 * @param int $publication_id
 * @param string $type
 * @return mixed
 */
function bibliographie_publications_get_data ($publication_id, $type = 'object') {
	if(is_numeric($publication_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json')){
			$assoc = false;
			if($type == 'assoc')
				$assoc = true;

			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json'), $assoc);
		}

		$publication = mysql_query("SELECT * FROM `a2publication` WHERE `pub_id` = ".((int) $publication_id));
		if(mysql_num_rows($publication) == 1){
			if($type == 'object')
				$publication = mysql_fetch_object($publication);
			else
				$publication = mysql_fetch_assoc($publication);

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($publication));
				fclose($cacheFile);
			}

			return $publication;
		}
	}

	return false;
}

/**
 * Parse the data of a publication.
 * @param int $publication_id
 * @param string $style
 * @param bool $textOnly
 * @return string
 */
function bibliographie_publications_parse_data ($publication_id, $style = 'standard', $textOnly = false) {
	if(is_numeric($publication_id) and strpos($style, '..') === false and strpos($style, '/') === false){
		$fileExtension = 'html';
		if($textOnly)
			$fileExtension = 'txt';

		/**
		 * Return cached result if possible.
		 */
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication_id.'_parsed_'.$style.'.'.$fileExtension))
			return file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication_id.'_parsed_'.$style.'.'.$fileExtension);

		/**
		 * Get data of publication.
		 */
		$publication = bibliographie_publications_get_data($publication_id, 'assoc');
		if($publication){
			$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/standard/'.$publication['pub_type'].'.txt'));
			$settings = parse_ini_file(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/standard/settings.ini', true);
			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/'.$style.'/'.$publication['pub_type'].'.txt')){
				$parsedPublication = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/'.$style.'/'.$publication['pub_type'].'.txt'));
				$settings = parse_ini_file(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/'.$style.'/settings.ini', true);
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

				$parsedAuthors .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&author_id='.$author->author_id.'">'.$parsedAuthor.'</a>';

				$i++;
			}

			$parsedPublication = str_replace('[authors]', $parsedAuthors, $parsedPublication);

			if($settings['title']['titleStyle'] == 'italic')
				$publication['title'] = '<em>'.$publication['title'].'</em>';

			$publication['title'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&pub_id='.$publication['pub_id'].'">'.$publication['title'].'</a>';

			if(empty($publication['pages']) and !empty($publication['firstpage']) and !empty($publication['lastPage']))
				$publication['pages'] = ((int) $publication['firstpage']).'-'.((int) $publication['lastpage']);

			foreach($publication as $key => $value){
				if(empty($value))
					$value = '<span style="font-size: 0.8em;" class="error">!'.$key.' missing!</span>';

				$parsedPublication = str_replace('['.$key.']', $value, $parsedPublication);
			}

			if($textOnly)
				$parsedPublication = strip_tags($parsedPublication);

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication['pub_id'].'_parsed_'.$style.'.'.$fileExtension, 'w+');
				fwrite($cacheFile, $parsedPublication);
				fclose($cacheFile);
			}

			return $parsedPublication;
		}
	}

	return false;
}

/**
 * Print a list of publications.
 * @param array $publications
 * @param string $baseLink
 */
function bibliographie_publications_print_list (array $publications, $baseLink, $bookmarkBatch = null){
	if($bookmarkBatch == 'add'){
		$bookmarks = bibliographie_bookmarks_set_bookmarks_for_list($publications);
		echo '<p class="notice">'.$bookmarks.' publications have been bookmarked! '.(count($publications) - $bookmarks).' publications were bookmarked already.</p>';
	}elseif($bookmarkBatch == 'remove'){
		$bookmarks = bibliographie_bookmarks_unset_bookmarks_for_list($publications);
		echo '<p class="notice">The bookmarks of '.$bookmarks.' publications were deleted! '.(count($publications) - $bookmarks).' publications weren\'t bookmarked.</p>';
	}

	$pageData = bibliographie_print_pages(count($publications), $baseLink);

	$lastYear = null;
	$ceiling = $pageData['offset'] + $pageData['perPage'];
	if($ceiling > count($publications))
		$ceiling = count($publications);

	for($i = $pageData['offset']; $i < $ceiling; $i++){
		$publication = bibliographie_publications_get_data($publications[$i]);

		if($publication->year != $lastYear)
			echo '<h4>Publications in '.((int) $publication->year).'</h4>';

		echo '<div id="publication_container_'.((int) $publication->pub_id).'" class="bibliographie_publication';
		if(bibliographie_bookmarks_check_publication($publication->pub_id))
			echo ' bibliographie_publication_bookmarked';
		echo '">'.bibliographie_bookmarks_print_html($publication->pub_id).bibliographie_publications_parse_data($publication->pub_id).'</div>';

		$lastYear = $publication->year;
	}

	if($pageData['pages'] > 1)
		bibliographie_print_pages(count($publications), $baseLink);

	bibliographie_bookmarks_print_javascript();
}