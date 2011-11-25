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
	),
	'inbook' => array (
		array (
			'author,editor',
			'title',
			'chapter',
			'pages',
			'publisher',
			'type',
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
	'incollection' => array (
		array (
			'author',
			'title',
			'booktitle',
			'publisher',
			'year'
		),
		array (
			'editor',
			'volume',
			'number',
			'type',
			'series',
			'edition',
			'chapter',
			'pages',
			'address',
			'month',
			'note'
		)
	),
	'inproceedings' => array (
		array (
			'author',
			'title',
			'booktitle',
			'year'
		),
		array (
			'editor',
			'volume',
			'number',
			'organization',
			'series',
			'pages',
			'publisher',
			'address',
			'month',
			'note'
		)
	),
	'manual' => array (
		array (
			'title'
		),
		array (
			'author',
			'organization',
			'address',
			'edition',
			'month',
			'year',
			'note'
		)
	),
	'masterthesis' => array (
		array (
			'author',
			'title',
			'school',
			'year'
		),
		array (
			'address',
			'month',
			'note',
			'type'
		)
	),
	'misc' => array (
		array (),
		array (
			'author',
			'title',
			'howpublished',
			'month',
			'year',
			'note'
		)
	),
	'phdthesis' => array (
		array (
			'author',
			'title',
			'school',
			'year'
		),
		array (
			'address',
			'month',
			'note',
			'type'
		)
	),
	'proceedings' => array (
		array (
			'title',
			'year'
		),
		array (
			'editor',
			'publisher',
			'volume',
			'number',
			'organization',
			'series',
			'address',
			'month',
			'note'
		)
	),
	'techreport' => array (
		array (
			'author',
			'title',
			'institution',
			'year'
		),
		array (
			'type',
			'number',
			'address',
			'month',
			'note'
		)
	),
	'unpublished' => array (
		array (
			'author',
			'title',
			'note'
		),
		array (
			'month',
			'year'
		)
	)
);

$bibliographie_publication_data = array (
	'pub_type' => 'Publication type',

	'year' => 'Year',
	'month' => 'Month',

	'booktitle' => 'Booktitle',
	'chapter' => 'Chapter',
	'series' => 'Series',
	'journal' => 'Journal',
	'volume' => 'Volume',
	'number' => 'Number',
	'edition' => 'Edition',
	'publisher' => 'Publisher',
	'location' => 'Location',
	'howpublished' => 'How published',
	'organization' => 'Organization',
	'institution' => 'Institution',
	'school' => 'School',
	'address' => 'Address',
	'pages' => 'Pages',
	'note' => 'Note',
	'abstract' => 'Abstract',
	'userfields' => 'User fields',
	'bibtex_id' => 'BibTex ID',
	'isbn' => 'ISBN',
	'issn' => 'ISSN',
	'doi' => 'DOI',
	'url' => 'URL',
	'user_id' => 'Added by',

	'authors' => 'Authors',
	'editors' => 'Editors',
	'topics' => 'Topics',
	'tags' => 'Tags'
);

/**
 * Get the data of a publication.
 * @param int $publication_id
 * @param string $type
 * @return mixed
 */
function bibliographie_publications_get_data ($publication_id, $type = 'object') {
	static $publication = null;

	$return = false;

	if(is_numeric($publication_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json')){
			$assoc = false;
			if($type == 'assoc')
				$assoc = true;

			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json'), $assoc);
		}

		if($publication == null)
			$publication = DB::getInstance()->prepare("SELECT * FROM `a2publication` WHERE `pub_id` = :pub_id");

		$publication->bindParam('pub_id', $publication_id);
		$publication->execute();

		if($publication->rowCount() == 1){
			if($type == 'object')
				$publication->setFetchMode(PDO::FETCH_OBJ);
			else
				$publication->setFetchMode(PDO::FETCH_ASSOC);

			$return = $publication->fetch();

			if(BIBLIOGRAPHIE_CACHING){
				$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json', 'w+');
				fwrite($cacheFile, json_encode($return));
				fclose($cacheFile);
			}
		}
	}

	return $return;
}

/**
 * Parse the data of a publication.
 * @param int $publication_id
 * @param string $style
 * @param bool $textOnly
 * @return string
 */
function bibliographie_publications_parse_data ($publication_id, $style = 'standard', array $options = array()){
	static $parserFiles = array(), $parserSettings = array();

	$publication = (array) bibliographie_publications_get_data($publication_id);

	$return = false;

	if(is_array($publication) and strpos($style, '..') === false and strpos($style, '/') === false){
		/**
		 * Set file extension.
		 */
		$fileExtension = 'html';
		if($options['plainText'] == true)
			$fileExtension = 'txt';

		/**
		 * Serialize options for filename.
		 */
		$optionsSerialized = md5('');
		if(count($optionsSerialized) > 0)
			$optionsSerialized = md5(implode(',', array_keys($options)).';'.implode(',', array_values($options)));

		/**
		 * Return cached result if possible.
		 */
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication['pub_id']).'_parsed_'.$style.'_'.$optionsSerialized.'.'.$fileExtension))
			return file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication['pub_id']).'_parsed_'.$style.'_'.$optionsSerialized.'.'.$fileExtension);

		/**
		 * If no fallback file exists cancel parsing.
		 */
		if(!file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/standard/'.$publication['pub_type'].'.txt'))
			return '<p class="error">Standard parser file for publication type <em>'.htmlspecialchars($publication['pub_type']).'</em> for style <em>'.htmlspecialchars($style).'</em> is missing!</p>';

		/**
		 * Cache settings and parser files in memory to skip reading from files over and over.
		 */
		if(!array_key_exists($style, $parserFiles) or !array_key_exists($publication['pub_type'], $parserFiles[$style])){
			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/'.$style.'/'.$publication['pub_type'].'.txt')){
				$parserFiles[$style][$publication['pub_type']] = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/'.$style.'/'.$publication['pub_type'].'.txt'));
				$parserSettings[$style] = parse_ini_file(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/'.$style.'/settings.ini', true);
			}else{
				$parserFiles[$style][$publication['pub_type']] = strip_tags(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/standard/'.$publication['pub_type'].'.txt'));
				$parserSettings[$style] = parse_ini_file(BIBLIOGRAPHIE_ROOT_PATH.'/resources/styles/standard/settings.ini', true);
			}
		}

		$parsedPublication = $parserFiles[$style][$publication['pub_type']];
		$settings = $parserSettings[$style];

		$authorsArray = bibliographie_publications_get_authors($publication['pub_id']);
		$parsedAuthors = (string) '';
		if(is_array($authorsArray) and count($authorsArray) > 0){
			foreach($authorsArray as $i => $author){
				if(!empty($parsedAuthors))
					if(count($authorsArray) == 2 or ($i + 1) == count($authorsArray))
						$parsedAuthors .= $settings['authors']['authorDividerLast'];
					else
						$parsedAuthors .= $settings['authors']['authorDivider'];

				$author = bibliographie_authors_get_data($author);

				if(!empty($author->von))
					$author->surname = $author->von.' '.$author->surname;
				if(!empty($author->jr))
					$author->surname = $author->surname.' '.$author->jr;

				$parsedAuthor = (string) '';
				if($settings['authors']['nameOrder'] == 'surnamesFirst')
					$parsedAuthor = $author->surname.$settings['authors']['nameDivider'].$author->firstname;
				else
					$parsedAuthor = $author->firstname.' '.$author->surname;

				if($options['noLinks'] != true)
					$parsedAuthor = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&author_id='.$author->author_id.'">'.$parsedAuthor.'</a>';

				$parsedAuthors .= $parsedAuthor;
			}
		}else
			$parsedAuthors = '<span style="font-size: 0.8em;" class="error">!authors missing!</span>';

		$parsedPublication = str_replace('[authors]', $parsedAuthors, $parsedPublication);

		if($settings['title']['titleStyle'] == 'italic')
			$publication['title'] = '<em>'.$publication['title'].'</em>';

		if($options['noLinks'] != true)
			$publication['title'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&pub_id='.$publication['pub_id'].'">'.$publication['title'].'</a>';

		if(empty($publication['pages']) and !empty($publication['firstpage']) and !empty($publication['lastPage']))
			$publication['pages'] = ((int) $publication['firstpage']).'-'.((int) $publication['lastpage']);

		if(!empty($publication['journal']) and $options['noLinks'] != true)
			$publication['journal'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=journal&amp;container='.htmlspecialchars($publication['journal']).'">'.htmlspecialchars($publication['journal']).'</a>';

		if(!empty($publication['booktitle']) and $options['noLinks'] != true)
			$publication['booktitle'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=book&amp;container='.htmlspecialchars($publication['booktitle']).'">'.htmlspecialchars($publication['booktitle']).'</a>';

		foreach($publication as $key => $value){
			if(empty($value))
				$value = '<span style="font-size: 0.8em;" class="error">!'.$key.' missing!</span>';

			$parsedPublication = str_replace('['.$key.']', $value, $parsedPublication);
		}

		if($options['plainText'])
			$parsedPublication = strip_tags($parsedPublication);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.$publication_id.'_parsed_'.$style.'_'.$optionsSerialized.'.'.$fileExtension, 'w+');
			fwrite($cacheFile, $parsedPublication);
			fclose($cacheFile);
		}

		$return = $parsedPublication;
	}

	return $return;
}

/**
 * Parse the publications given in a list.
 * @param array $publications
 * @param string $type
 * @return type
 */
function bibliographie_publications_parse_list (array $publications, $type = 'html') {
	if(count($publications) > 0){
		if(!in_array($type, array('html', 'text')))
			$type = 'html';

		$newLine = '<br />'.PHP_EOL;
		$options = array (
			'noLinks' => true
		);
		if($type == 'text'){
			header('Content-Type: text/plain; charset=UTF-8');
			$newLine = PHP_EOL;
			$options = array (
				'plainText' => true
			);
		}

		foreach($publications as $publication)
			echo bibliographie_publications_parse_data($publication, 'standard', $options).$newLine;
	}

	return false;
}

/**
 * Print a list of publications.
 * @param array $publications
 * @param string $baseLink
 * @param string $bookmarkBatch
 * @param bool $showBookmarkingLink
 */
function bibliographie_publications_print_list (array $publications, $baseLink, $bookmarkBatch = null, $showBookmarkingLink = true, $onlyPublication = false){
	if(count($publications) > 0){
		/**
		 * In case we have gaps in the array keys fetch it new.
		 */
		$publications = array_values($publications);

		if($bookmarkBatch == 'add'){
			$bookmarks = bibliographie_bookmarks_set_bookmarks_for_list($publications);
			echo '<p class="notice">'.$bookmarks.' publications have been bookmarked! '.(count($publications) - $bookmarks).' publications in the shown list were bookmarked already.</p>';
		}elseif($bookmarkBatch == 'remove'){
			$bookmarks = bibliographie_bookmarks_unset_bookmarks_for_list($publications);
			echo '<p class="notice">The bookmarks of '.$bookmarks.' publications were deleted! '.(count($publications) - $bookmarks).' publications in the shown list weren\'t bookmarked.</p>';
		}

		$pageData = bibliographie_print_pages(count($publications), $baseLink);
		$exportList = bibliographie_publications_cache_list($publications);

		if(!$onlyPublication){
			echo '<p class="bibliographie_operations">';
			if(count($publications) > 1)
				echo '<span style="float: left">List contains '.count($publications).' publication(s)...</span>';

			if(count($publications) > 1 and $showBookmarkingLink){
				echo ' <a href="'.$baseLink.'&amp;bookmarkBatch=add"><em>'.bibliographie_icon_get('star').' Bookmark</em></a>';
				echo ' <a href="'.$baseLink.'&amp;bookmarkBatch=remove"><em>'.bibliographie_icon_get('cross').' Unbookmark</em></a>';
			}

			echo ' <a href="javascript:;" onclick="bibliographie_publications_export_choose_type(\''.bibliographie_publications_cache_list($publications).'\')"><em>'.bibliographie_icon_get('page-white-go').' Export</em></a>';

			echo ' <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=batchOperations&amp;list='.bibliographie_publications_cache_list($publications).'">'.bibliographie_icon_get('page-white-stack').' Batch</a>';
			echo '</p>';
		}

		$lastYear = null;
		$ceiling = $pageData['offset'] + $pageData['perPage'];
		if($ceiling > count($publications))
			$ceiling = count($publications);

		for($i = $pageData['offset']; $i < $ceiling; $i++){
			$publication = bibliographie_publications_get_data($publications[$i]);

			if($publication->year != $lastYear and !$onlyPublication)
				echo '<h4>Publications in '.((int) $publication->year).'</h4>';

			echo '<div id="publication_container_'.((int) $publication->pub_id).'" class="bibliographie_publication';
			if(bibliographie_bookmarks_check_publication($publication->pub_id))
				echo ' bibliographie_publication_bookmarked';
			echo '">'.bibliographie_bookmarks_print_html($publication->pub_id);
			echo bibliographie_publications_parse_data($publication->pub_id).'</div>';

			$lastYear = $publication->year;
		}

		if($pageData['pages'] > 1)
			bibliographie_print_pages(count($publications), $baseLink);

		bibliographie_bookmarks_print_javascript();
	}else
		echo '<p class="error">List of publications is empty...</p>';
}

/**
 *
 * @staticvar string $persons
 * @param type $publication_id
 * @param string $type
 * @param string $order
 * @return type
 */
function bibliographie_publications_get_persons ($publication_id, $is_editor = 'authors', $order = 'rank') {
	static $persons = null;

	$publication = bibliographie_publications_get_data($publication_id);
	$return = false;

	if(is_object($publication)){
		$return = array();

		/**
		 * Check options and reset them to defaults if they are not allowed.
		 */
		if(!in_array($order, array('rank', 'name')))
			$order = 'rank';
		if(!in_array($is_editor, array('authors', 'editors')))
			$is_editor = 'authors';

		/**
		 * Return cache content if possible.
		 */
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_'.$is_editor.'_'.$order.'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_'.$is_editor.'_'.$order.'.json'));

		/**
		 * Translate options to appropriate mysql data.
		 */
		$_order = (string) '';
		$_is_editor = (string) '';
		if($order == 'rank')
			$_order = '`rank`';
		elseif($order == 'name')
			$_order = '`surname`, `firstname`';
		if($is_editor == 'authors')
			$_is_editor = 'N';
		elseif($is_editor == 'editors')
			$_is_editor = 'Y';

		if($persons === null)
			$persons = DB::getInstance()->prepare("SELECT data.`author_id` FROM
		`a2publicationauthorlink` link,
		`a2author` data
	WHERE
		link.`pub_id` = :pub_id AND
		link.`author_id` = data.`author_id` AND
		link.`is_editor` = :is_editor
	ORDER BY ".$_order);

		$persons->execute(array(
			'pub_id' => (int) $publication->pub_id,
			'is_editor' => $_is_editor
		));

		if($persons->rowCount() > 0)
			$return = $persons->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_'.$is_editor.'_'.$order.'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $publication_id
 * @param type $order
 * @return type
 */
function bibliographie_publications_get_authors ($publication_id, $order = 'rank') {
	return bibliographie_publications_get_persons($publication_id, 'authors', $order);
}

/**
 *
 * @param type $publication_id
 * @param type $order
 * @return type
 */
function bibliographie_publications_get_editors ($publication_id, $order = 'rank') {
	return bibliographie_publications_get_persons($publication_id, 'editors', $order);
}

/**
 *
 * @staticvar string $tags
 * @param type $publication_id
 * @return type
 */
function bibliographie_publications_get_tags ($publication_id) {
	static $tags = null;

	$publication = bibliographie_publications_get_data($publication_id);

	$return = false;
	if(is_object($publication)){
		$return = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication->pub_id).'_tags.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication->pub_id).'_tags.json'));

		if($tags === null)
			$tags = DB::getInstance()->prepare("SELECT `tag_id` FROM `a2publicationtaglink` WHERE `pub_id` = :pub_id");

		$tags->bindParam('pub_id', $publication->pub_id);
		$tags->execute();

		if($tags->rowCount() > 0)
			$return = $tags->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication->pub_id).'_tags.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $publication_id
 * @return type
 */
function bibliographie_publications_get_topics ($publication_id) {
	static $topics = null;

	$publication = bibliographie_publications_get_data($publication_id);
	$return = false;

	if(is_object($publication)){
		$return = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication->pub_id).'_topics.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication->pub_id).'_topics.json'));

		if($topics === null)
			$topics = DB::getInstance()->prepare("SELECT `topic_id` FROM `a2topicpublicationlink` WHERE `pub_id` = :pub_id");
		$topics->bindParam('pub_id', $publication->pub_id);
		$topics->execute();

		if($topics->rowCount() > 0)
			$return = $topics->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication->pub_id).'_topics.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $pub_type
 * @param array $author
 * @param array $editor
 * @param type $title
 * @param type $month
 * @param type $year
 * @param type $booktitle
 * @param type $chapter
 * @param type $series
 * @param type $journal
 * @param type $volume
 * @param type $number
 * @param type $edition
 * @param type $publisher
 * @param type $location
 * @param type $howpublished
 * @param type $organization
 * @param type $institution
 * @param type $school
 * @param type $address
 * @param type $pages
 * @param type $note
 * @param type $abstract
 * @param type $userfields
 * @param type $bibtex_id
 * @param type $isbn
 * @param type $issn
 * @param type $doi
 * @param type $url
 * @param array $topics
 * @param array $tags
 * @param type $user_id
 * @return type
 */
function bibliographie_publications_create_publication ($pub_type, array $author, array $editor, $title, $month, $year, $booktitle, $chapter, $series, $journal, $volume, $number, $edition, $publisher, $location, $howpublished, $organization, $institution, $school, $address, $pages, $note, $abstract, $userfields, $bibtex_id, $isbn, $issn, $doi, $url, array $topics, array $tags, $user_id = null) {
	if($user_id == null)
		$user_id = bibliographie_user_get_id ();

	$return = mysql_query("INSERT INTO `a2publication` (
	`pub_type`,
	`user_id`,
	`title`,
	`month`,
	`year`,
	`booktitle`,
	`chapter`,
	`series`,
	`journal`,
	`volume`,
	`number`,
	`edition`,
	`publisher`,
	`location`,
	`howpublished`,
	`organization`,
	`institution`,
	`school`,
	`address`,
	`pages`,
	`note`,
	`abstract`,
	`userfields`,
	`bibtex_id`,
	`isbn`,
	`issn`,
	`doi`,
	`url`
) VALUES (
	'".mysql_real_escape_string(stripslashes($pub_type))."',
	'".((int) $user_id)."',
	'".mysql_real_escape_string(stripslashes($title))."',
	'".mysql_real_escape_string(stripslashes($month))."',
	".((int) $year).",
	'".mysql_real_escape_string(stripslashes($booktitle))."',
	'".mysql_real_escape_string(stripslashes($chapter))."',
	'".mysql_real_escape_string(stripslashes($series))."',
	'".mysql_real_escape_string(stripslashes($journal))."',
	'".mysql_real_escape_string(stripslashes($volume))."',
	'".mysql_real_escape_string(stripslashes($number))."',
	'".mysql_real_escape_string(stripslashes($edition))."',
	'".mysql_real_escape_string(stripslashes($publisher))."',
	'".mysql_real_escape_string(stripslashes($location))."',
	'".mysql_real_escape_string(stripslashes($howpublished))."',
	'".mysql_real_escape_string(stripslashes($organization))."',
	'".mysql_real_escape_string(stripslashes($institution))."',
	'".mysql_real_escape_string(stripslashes($school))."',
	'".mysql_real_escape_string(stripslashes($address))."',
	'".mysql_real_escape_string(stripslashes($pages))."',
	'".mysql_real_escape_string(stripslashes($note))."',
	'".mysql_real_escape_string(stripslashes($abstract))."',
	'".mysql_real_escape_string(stripslashes($userfields))."',
	'".mysql_real_escape_string(stripslashes($bibtex_id))."',
	'".mysql_real_escape_string(stripslashes($isbn))."',
	'".mysql_real_escape_string(stripslashes($issn))."',
	'".mysql_real_escape_string(stripslashes($doi))."',
	'".mysql_real_escape_string(stripslashes($url))."'
)");

	$pub_id = mysql_insert_id();

	if(count($author) > 0 and !empty($author[0])){
		$rank = (int) 1;
		foreach($author as $author_id)
			mysql_query("INSERT INTO `a2publicationauthorlink` (`pub_id`, `author_id`, `rank`, `is_editor`) VALUES (".((int) $pub_id).", ".((int) $author_id).", ".((int) $rank++).", 'N')");
	}

	if(count($editor) > 0 and !empty($editor[0])){
		$rank = (int) 1;
		foreach($editor as $editor_id)
			mysql_query("INSERT INTO `a2publicationauthorlink` (`pub_id`, `author_id`, `rank`, `is_editor`) VALUES (".((int) $pub_id).", ".((int) $editor_id).", ".((int) $rank++).", 'Y')");
	}

	if(count($topics) > 0 and !empty($topics[0]))
		foreach($topics as $topic_id)
			mysql_query("INSERT INTO `a2topicpublicationlink` (`topic_id`, `pub_id`) VALUES (".((int) $topic_id).", ".((int) $pub_id).")");

	if(count($tags) > 0 and !empty($tags[0]))
		foreach($tags as $tag_id)
			mysql_query("INSERT INTO `a2publicationtaglink` (`pub_id`, `tag_id`) VALUES (".((int) $pub_id).", ".((int) $tag_id).")");

	$data = array(
		'pub_id' => (int) $pub_id,
		'pub_type' => $pub_type,
		'user_id' => (int) $user_id,
		'title' => $title,
		'month' => $month,
		'year' => (int) $year,
		'booktitle' => $booktitle,
		'chapter' => $chapter,
		'series' => $series,
		'journal' => $journal,
		'volume' => $volume,
		'number' => $number,
		'edition' => $edition,
		'publisher' => $publisher,
		'location' => $location,
		'howpublished' => $howpublished,
		'organization' => $organization,
		'institution' => $institution,
		'school' => $school,
		'address' => $address,
		'pages' => $pages,
		'note' => $note,
		'abstract' => $abstract,
		'userfields' => $userfields,
		'bibtex_id' => $bibtex_id,
		'isbn' => $isbn,
		'issn' => $issn,
		'doi' => $doi,
		'url' => $url,

		'author' => $author,
		'editor' => $editor,
		'topics' => $topics,
		'tags' => $tags
	);

	bibliographie_purge_cache('publications');
	bibliographie_purge_cache('tags');

	if($return){
		bibliographie_log('publications', 'createPublication', json_encode($data));
		return $data;
	}

	return $return;
}

/**
 *
 * @param type $pub_id
 * @param type $pub_type
 * @param array $author
 * @param array $editor
 * @param type $title
 * @param type $month
 * @param type $year
 * @param type $booktitle
 * @param type $chapter
 * @param type $series
 * @param type $journal
 * @param type $volume
 * @param type $number
 * @param type $edition
 * @param type $publisher
 * @param type $location
 * @param type $howpublished
 * @param type $organization
 * @param type $institution
 * @param type $school
 * @param type $address
 * @param type $pages
 * @param type $note
 * @param type $abstract
 * @param type $userfields
 * @param type $isbn
 * @param type $issn
 * @param type $doi
 * @param type $url
 * @param array $topics
 * @param array $tags
 * @return type
 */
function bibliographie_publications_edit_publication ($pub_id, $pub_type, array $author, array $editor, $title, $month, $year, $booktitle, $chapter, $series, $journal, $volume, $number, $edition, $publisher, $location, $howpublished, $organization, $institution, $school, $address, $pages, $note, $abstract, $userfields, $bibtex_id, $isbn, $issn, $doi, $url, array $topics, array $tags) {

	mysql_query("DELETE FROM `a2publicationauthorlink` WHERE `pub_id` = ".((int) $pub_id)." LIMIT ".(count(bibliographie_publications_get_authors($pub_id))+count(bibliographie_publications_get_editors($pub_id))));
	mysql_query("DELETE FROM `a2topicpublicationlink` WHERE `pub_id` = ".((int) $pub_id)." LIMIT ".count(bibliographie_publications_get_topics($pub_id)));
	mysql_query("DELETE FROM `a2publicationtaglink` WHERE `pub_id` = ".((int) $pub_id)." LIMIT ".count(bibliographie_publications_get_tags($pub_id)));

	$return = mysql_query("UPDATE `a2publication` SET
	`pub_type` = '".mysql_real_escape_string(stripslashes($pub_type))."',
	`title` = '".mysql_real_escape_string(stripslashes($title))."',
	`month` = '".mysql_real_escape_string(stripslashes($month))."',
	`year` = ".((int) $year).",
	`booktitle` = '".mysql_real_escape_string(stripslashes($booktitle))."',
	`chapter` = '".mysql_real_escape_string(stripslashes($chapter))."',
	`series` = '".mysql_real_escape_string(stripslashes($series))."',
	`journal` = '".mysql_real_escape_string(stripslashes($journal))."',
	`volume` = '".mysql_real_escape_string(stripslashes($volume))."',
	`number` = '".mysql_real_escape_string(stripslashes($number))."',
	`edition` = '".mysql_real_escape_string(stripslashes($edition))."',
	`publisher` = '".mysql_real_escape_string(stripslashes($publisher))."',
	`location` = '".mysql_real_escape_string(stripslashes($location))."',
	`howpublished` = '".mysql_real_escape_string(stripslashes($howpublished))."',
	`organization` = '".mysql_real_escape_string(stripslashes($organization))."',
	`institution` = '".mysql_real_escape_string(stripslashes($institution))."',
	`school` = '".mysql_real_escape_string(stripslashes($school))."',
	`address` = '".mysql_real_escape_string(stripslashes($address))."',
	`pages` = '".mysql_real_escape_string(stripslashes($pages))."',
	`note` = '".mysql_real_escape_string(stripslashes($note))."',
	`abstract` = '".mysql_real_escape_string(stripslashes($abstract))."',
	`userfields` = '".mysql_real_escape_string(stripslashes($userfields))."',
	`bibtex_id` = '".mysql_real_escape_string(stripslashes($bibtex_id))."',
	`isbn` = '".mysql_real_escape_string(stripslashes($isbn))."',
	`issn` = '".mysql_real_escape_string(stripslashes($issn))."',
	`doi` = '".mysql_real_escape_string(stripslashes($doi))."',
	`url` = '".mysql_real_escape_string(stripslashes($url))."'
WHERE
	`pub_id` = ".((int) $pub_id)."
LIMIT 1");

	if(count($author) > 0 and !empty($author[0])){
		$rank = (int) 1;
		foreach($author as $author_id)
			mysql_query("INSERT INTO `a2publicationauthorlink` (`pub_id`, `author_id`, `rank`, `is_editor`) VALUES (".((int) $pub_id).", ".((int) $author_id).", ".((int) $rank++).", 'N')");
	}

	if(count($editor) > 0 and !empty($editor[0])){
		$rank = (int) 1;
		foreach($editor as $editor_id)
			mysql_query("INSERT INTO `a2publicationauthorlink` (`pub_id`, `author_id`, `rank`, `is_editor`) VALUES (".((int) $pub_id).", ".((int) $editor_id).", ".((int) $rank++).", 'Y')");
	}

	if(count($topics) > 0 and !empty($topics[0]))
		foreach($topics as $topic_id)
			mysql_query("INSERT INTO `a2topicpublicationlink` (`topic_id`, `pub_id`) VALUES (".((int) $topic_id).", ".((int) $pub_id).")");

	if(count($tags) > 0 and !empty($tags[0]))
		foreach($tags as $tag_id)
			mysql_query("INSERT INTO `a2publicationtaglink` (`pub_id`, `tag_id`) VALUES (".((int) $pub_id).", ".((int) $tag_id).")");

	$data = json_encode(array(
		'pub_id' => (int) $pub_id,
		'pub_type' => $pub_type,
		'user_id' => (int) $user_id,
		'title' => $title,
		'month' => $month,
		'year' => (int) $year,
		'booktitle' => $booktitle,
		'chapter' => $chapter,
		'series' => $series,
		'journal' => $journal,
		'volume' => $volume,
		'number' => $number,
		'edition' => $edition,
		'publisher' => $publisher,
		'location' => $location,
		'howpublished' => $howpublished,
		'organization' => $organization,
		'institution' => $institution,
		'school' => $school,
		'address' => $address,
		'pages' => $pages,
		'note' => $note,
		'abstract' => $abstract,
		'userfields' => $userfields,
		'bibtex_id' => $bibtex_id,
		'isbn' => $isbn,
		'issn' => $issn,
		'doi' => $doi,
		'url' => $url,

		'author' => $author,
		'editor' => $editor,
		'topics' => $topics,
		'tags' => $tags
	));

	if($return){
		bibliographie_log('publications', 'editPublication', $data);
		$return = $data;
	}

	bibliographie_purge_cache('publication_'.((int) $pub_id));
	bibliographie_purge_cache('publications');

	return $return;
}

/**
 *
 * @param string $listID
 * @return array
 */
function bibliographie_publications_get_cached_list ($listID) {
	if(strpos($listID, '..') === FALSE and strpos($listID, '/') === FALSE){
		$publications = array();

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/export_list_'.$listID.'.json') and is_array(json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/export_list_'.$listID.'.json'))))
			$publications = json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/export_list_'.$listID.'.json'));

		if(is_array($_SESSION['export_list_'.$listID]))
			$publications = $_SESSION['export_list_'.$listID];

		return $publications;
	}

	return false;
}

/**
 *
 * @param array $publications
 * @return type
 */
function bibliographie_publications_cache_list (array $publications) {
	$publicationsJSON = json_encode(array_values($publications));

	$listCached = false;
	if(BIBLIOGRAPHIE_CACHING){
		$file = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/export_list_'.md5($publicationsJSON).'.json', 'w+');
		fwrite($file, $publicationsJSON);
		fclose($file);

		$listCached = true;
	}

	if(!$listCached)
		$_SESSION['export_list_'.md5($publicationsJSON)] = $publications;

	return md5($publicationsJSON);
}

/**
 *
 * @param array $publications
 * @param type $orderBy
 * @return array
 */
function bibliographie_publications_sort (array $publications, $orderBy) {
	return $publications;
}

/**
 *
 * @param type $pub_id
 * @param array $options
 * @return string
 */
function bibliographie_publications_parse_title ($pub_id, array $options = array()) {
	$publication = bibliographie_publications_get_data($pub_id);
	$return = false;

	if(is_object($publication)){
		$return = htmlspecialchars($publication->title);

		if($options['linkProfile'] == true)
			$return = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&amp;pub_id='.((int) $publication->pub_id).'">'.$return.'</a>';
	}

	return $return;
}

/**
 * Adds a topic to a list of publications.
 * @staticvar string $addLink
 * @param array $publications
 * @param int $topic_id
 * @return mixed False on error, an array otherwise.
 */
function bibliographie_publications_add_topic (array $publications, $topic_id) {
	static $addLink = null;

	$topic = bibliographie_topics_get_data($topic_id);

	$return = false;

	if(is_array($publications) and is_object($topic)){
		$topicsPublications = bibliographie_topics_get_publications($topic->topic_id);

		// Remove those from the list that have the topic already.
		$publications = array_diff($publications, $topicsPublications);

		if($addLink === null)
			$addLink = DB::getInstance()->prepare('INSERT INTO `a2topicpublicationlink` (
	`topic_id`,
	`pub_id`
) VALUES (
	:topic_id,
	:pub_id
)');

		$addedPublications = array();
		if(count($publications) > 0){
			foreach($publications as $pub_id){
				if($addLink->execute(array (
					'topic_id' => (int) $topic->topic_id,
					'pub_id' => (int) $pub_id
				))){
					$addedPublications[] = $pub_id;
					bibliographie_purge_cache('publication_'.((int) $pub_id));
				}
			}
		}

		$return = array (
			'topic_id' => (int) $topic->topic_id,
			'publicationsBefore' => $topicsPublications,
			'publicationsToAdd' => $publications,
			'publicationsAdded' => $addedPublications
		);

		if(count($addedPublications) > 0){
			bibliographie_purge_cache('topic_'.((int) $topic->topic_id));
			bibliographie_log('publications', 'addTopic', json_encode($return));
		}
	}

	return $return;
}

/**
 * Removes a topic from a list of publications.
 * @staticvar string $removeLink
 * @param array $publications
 * @param int $topic_id
 * @return mixed False on error, an array otherwise.
 */
function bibliographie_publications_remove_topic (array $publications, $topic_id) {
	static $removeLink = null;

	$topic = bibliographie_topics_get_data($topic_id);

	$return = false;

	if(is_object($topic)){
		$topicsPublications = bibliographie_topics_get_publications($topic->topic_id);

		// Only keep those publications to remove from the topic that are actually in the topic.
		$publications = array_values(array_intersect($topicsPublications, $publications));

		if($removeLink === null)
			$removeLink = DB::getInstance()->prepare('DELETE FROM
	`a2topicpublicationlink`
WHERE
	FIND_IN_SET(`pub_id`, :list) AND `topic_id` = :topic_id');

		if($removeLink->execute(array(
			'list' => array2csv($publications),
			'topic_id' => (int) $topic->topic_id
		)))
			$return = array (
				'topic_id' => (int) $topic->topic_id,
				'publicationsBefore' => $topicsPublications,
				'publicationsToRemove' => $publications
			);

		if(is_array($return)){
			bibliographie_purge_cache('topic_'.((int) $topic->topic_id));
			bibliographie_purge_cache('publication_');
			bibliographie_log('publications', 'removeTopic', json_encode($return));
		}
	}

	return $return;
}

/**
 *
 */
function bibliographie_publications_add_tag (array $publications, $tag_id) {
	static $addLink = null;

	$tag = bibliographie_tags_get_data($tag_id);

	$return = false;

	if(is_object($tag)){
		$tagsPublications = bibliographie_tags_get_publications($tag->tag_id);

		$publications = array_diff($publications, $tagsPublications);

		if($addLink === null)
			$addLink = DB::getInstance()->prepare('INSERT INTO `a2publicationtaglink` (
	`pub_id`,
	`tag_id`
) VALUES (
	:pub_id,
	:tag_id
)');

		$addedPublications = array();
		if(count($publications) > 0){
			foreach($publications as $pub_id){
				if($addLink->execute(array (
					'tag_id' => (int) $tag->tag_id,
					'pub_id' => (int) $pub_id
				))){
					$addedPublications[] = $pub_id;
					bibliographie_purge_cache('publication_'.((int) $pub_id));
				}
			}
		}

		$return = array (
			'tag_id' => (int) $tag->tag_id,
			'publicationsBefore' => $tagsPublications,
			'publicationsToAdd' => $publications,
			'publicationsAdded' => $addedPublications
		);

		if(count($addedPublications) > 0){
			bibliographie_purge_cache('tag_'.((int) $tag->tag_id));
			bibliographie_log('publications', 'addTag', json_encode($return));
		}
	}

	return $return;
}

/**
 *
 */
function bibliographie_publications_remove_tag (array $publications, $tag_id) {
	static $removeLink = null;

	$tag = bibliographie_tags_get_data($tag_id);

	$return = false;

	if(is_object($tag)){
		$tagsPublications = bibliographie_tags_get_publications($tag->tag_id);

		$publications = array_values(array_intersect($tagsPublications, $publications));

		if($removeLink === null)
			$removeLink = DB::getInstance()->prepare('DELETE FROM
	`a2publicationtaglink`
WHERE
	FIND_IN_SET(`pub_id`, :list) AND `tag_id` = :tag_id');

		if($removeLink->execute(array(
			'list' => array2csv($publications),
			'tag_id' => (int) $tag->tag_id
		)))
			$return = array (
				'tag_id' => (int) $tag->tag_id,
				'publicationsBefore' => $topicsPublications,
				'publicationsToRemove' => $publications
			);

		if(is_array($return)){
			bibliographie_purge_cache('tag_'.((int) $topic->topic_id));
			bibliographie_purge_cache('publication_');
			bibliographie_log('publications', 'removeTag', json_encode($return));
		}
	}

	return $return;
}