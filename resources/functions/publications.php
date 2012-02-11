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
function bibliographie_publications_get_data ($publication_id) {
	static $publication = null;

	$return = false;

	if(is_numeric($publication_id)){
		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publication_'.((int) $publication_id).'_data.json'));

		if($publication == null){
			$publication = DB::getInstance()->prepare("SELECT * FROM `".BIBLIOGRAPHIE_PREFIX."publication` WHERE `pub_id` = :pub_id");
			$publication->setFetchMode(PDO::FETCH_OBJ);
		}

		$publication->bindParam('pub_id', $publication_id);
		$publication->execute();

		if($publication->rowCount() == 1){
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
	$return = false;
	if(count($publications) > 0){
		$return = (string) '';
		if(!in_array($type, array('html', 'text')))
			$type = 'html';

		$options = array (
			'noLinks' => true
		);
		if($type == 'text'){
			$options = array (
				'plainText' => true
			);
		}

		foreach($publications as $publication)
			$return .= bibliographie_publications_parse_data($publication, 'standard', $options).PHP_EOL.PHP_EOL;
	}

	return nl2br(htmlspecialchars($return));
}

/**
 * Print a list of publications.
 * @param array $publications
 * @param array $options
 */
function bibliographie_publications_print_list (array $publications, $baseLink = '', array $options = array())	{
	$return = (string) '';
	if(count($publications) > 0){
		if(!empty($_GET['orderBy']))
			$options['orderBy'] = $_GET['orderBy'];

		$options = bibliographie_options_compare(
			array (
				'onlyPublications'	=> (bool) false,
				'bookmarkingLink'		=> (bool) true,
				'orderBy'				=> array (
					'default'	=> (string) 'year',
					'possible'	=> array (
						'year',
						'title'
					)
				)
			),
			$options
		);

		// Clear gaps between array indices...
		$publications = bibliographie_publications_sort($publications, $options['orderBy']);

		// Apply bookmark batch operations...
		if($_GET['bookmarkBatch'] == 'add')
			$return .= '<p class="notice">'.bibliographie_bookmarks_set_bookmarks_for_list($publications).' publications have been bookmarked!.</p>';
		elseif($_GET['bookmarkBatch'] == 'remove')
			$return .= '<p class="notice">The bookmarks of '.bibliographie_bookmarks_unset_bookmarks_for_list($publications).' publications were deleted!</p>';

		$pageData = bibliographie_pages_calculate(count($publications));
		$return .= bibliographie_pages_print($pageData, bibliographie_link_append_param($baseLink, 'orderBy='.$options['orderBy']));
		$exportHash = bibliographie_publications_cache_list($publications);

		if(!$options['onlyPublications'] and count($publications) > 1){
			$return .= '<p class="bibliographie_operations">';

			$return .= '<span style="float: left">List contains <strong>'.count($publications).' publication</strong>(s)...</span>';

			if($options['bookmarkingLink']){
				$return .= ' <a href="'.$baseLink.'&amp;bookmarkBatch=add"><em>'.bibliographie_icon_get('star').' Bookmark</em></a>';
				$return .= ' <a href="'.$baseLink.'&amp;bookmarkBatch=remove"><em>'.bibliographie_icon_get('cross').' Unbookmark</em></a>';
			}

			$return .= ' <a href="javascript:;" onclick="bibliographie_publications_export_choose_type(\''.$exportHash.'\')"><em>'.bibliographie_icon_get('page-white-go').' Export</em></a>';
			$return .= ' <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=batchOperations&amp;list='.$exportHash.'"><em>'.bibliographie_icon_get('page-white-stack').' Batch</em></a>';

			$return .= ' <span id="bibliographie_publications_order_'.$exportHash.'" class="bibliographie_publications_order_trigger">
	'.bibliographie_icon_get('table').' Order
	<span style="display: none" id="bibliographie_publications_order_'.$exportHash.'_selector" class="bibliographie_publications_order_selector bibliographie_layers_closing_by_click">
		<a href="'.bibliographie_link_append_param($baseLink, 'orderBy=year').'">'.bibliographie_icon_get('clock').' Year</a>
		<a href="'.bibliographie_link_append_param($baseLink, 'orderBy=title').'">'.bibliographie_icon_get('text-heading1').' Title</a>
	</span>
</span>';

			$return .= '</p>';
		}

		$cutter = null;
		for($i = $pageData['offset']; $i < $pageData['ceiling']; $i++){
			$publication = (array) bibliographie_publications_get_data($publications[$i]);

			if(!$options['onlyPublications'] and count($publications) > 1){
				if($options['orderBy'] == 'year' and $cutter != $publication['year']){
					$cutter = $publication['year'];
					$return .= '<h4>Publications in '.((int) $cutter).'</h4>';
				}elseif($options['orderBy'] == 'title' and $cutter != mb_strtoupper(mb_substr($publication['title'], 0, 1))){
					$cutter = mb_substr($publication['title'], 0, 1);
					$return .= '<h4>Publications that start with '.$cutter.'</h4>';
				}
			}

			$return .= '<div id="publication_container_'.((int) $publication['pub_id']).'" class="bibliographie_publication';
			if(bibliographie_bookmarks_check_publication($publication['pub_id']))
				$return .= ' bibliographie_publication_bookmarked';
			$return .= '">'.bibliographie_bookmarks_print_html($publication['pub_id']);
			$return .= bibliographie_publications_parse_data($publication['pub_id']).'</div>';
		}

		$return .= bibliographie_pages_print($pageData, bibliographie_link_append_param($baseLink, 'orderBy='.$options['orderBy']));

		bibliographie_bookmarks_print_javascript();
	}else
		$return .= '<p class="error">List of publications is empty...</p>';

	return $return;
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
			$persons = DB::getInstance()->prepare('SELECT data.`author_id` FROM
		`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink` link,
		`'.BIBLIOGRAPHIE_PREFIX.'author` data
	WHERE
		link.`pub_id` = :pub_id AND
		link.`author_id` = data.`author_id` AND
		link.`is_editor` = :is_editor
	ORDER BY '.$_order);

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
			$tags = DB::getInstance()->prepare('SELECT `tag_id` FROM `'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink` WHERE `pub_id` = :pub_id');

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
			$topics = DB::getInstance()->prepare('SELECT `topic_id` FROM `'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink` WHERE `pub_id` = :pub_id');
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
function bibliographie_publications_create_publication ($pub_type, array $author, array $editor, $title, $month, $year, $booktitle, $chapter, $series, $journal, $volume, $number, $edition, $publisher, $location, $howpublished, $organization, $institution, $school, $address, $pages, $note, $abstract, $userfields, $bibtex_id, $isbn, $issn, $doi, $url, array $topics, array $tags, $pub_id = null, $user_id = null) {
	static
		$createPublication = null,
		$linkAuthors = null,
		$linkEditors = null,
		$linkTopics = null,
		$linkTags = null;

	$return = false;

	try {
		sort($tags);
		sort($topics);

		$higherTransaction = DB::getInstance()->inTransaction();
		if(!$higherTransaction)
			DB::getInstance()->beginTransaction();

		if($user_id == null)
			$user_id = bibliographie_user_get_id ();

		if(!($createPublication instanceof PDOStatement))
			$createPublication = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'publication` (
		`pub_id`,
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
		:pub_id,
		:pub_type,
		:user_id,
		:title,
		:month,
		:year,
		:booktitle,
		:chapter,
		:series,
		:journal,
		:volume,
		:number,
		:edition,
		:publisher,
		:location,
		:howpublished,
		:organization,
		:institution,
		:school,
		:address,
		:pages,
		:note,
		:abstract,
		:userfields,
		:bibtex_id,
		:isbn,
		:issn,
		:doi,
		:url
	)');

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
			'url' => $url
		);

		$return = $createPublication->execute($data);

		if($pub_id === null)
			$pub_id = (int) DB::getInstance()->lastInsertId();

		$data = array_merge($data, array(
			'pub_id' => $pub_id,
			'author' => $author,
			'editor' => $editor,
			'topics' => $topics,
			'tags' => $tags
		));

		if(count($author) > 0 and !empty($author[0])){
			$rank = (int) 1;
			foreach($author as $linkAuthor){
				$linkAuthor = bibliographie_authors_get_data($linkAuthor);
				if(is_object($linkAuthor)){
					if(!($linkAuthors instanceof PDOStatement))
						$linkAuthors = DB::getInstance()->prepare('INSERT INTO
		`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink`
	(
		`pub_id`,
		`author_id`,
		`rank`,
		`is_editor`
	) VALUES (
		:pub_id,
		:author_id,
		:rank,
		"N"
	)');
					$linkAuthors->execute(array(
						'pub_id' => (int) $data['pub_id'],
						'author_id' => (int) $linkAuthor->author_id,
						'rank' => (int) $rank++
					));
					bibliographie_cache_purge('author_'.((int) $linkAuthor->author_id));
				}
			}
		}

		if(count($editor) > 0 and !empty($editor[0])){
			$rank = (int) 1;
			foreach($editor as $linkEditor){
				$linkEditor = bibliographie_authors_get_data($linkEditor);
				if(is_object($linkEditor)){
					if(!($linkEditors instanceof PDOStatement))
						$linkEditors = DB::getInstance()->prepare('INSERT INTO
		`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink`
	(
		`pub_id`,
		`author_id`,
		`rank`,
		`is_editor`
	) VALUES (
		:pub_id,
		:author_id,
		:rank,
		"Y"
	)');
					$linkEditors->execute(array(
						'pub_id' => (int) $data['pub_id'],
						'author_id' => (int) $linkEditor->author_id,
						'rank' => (int) $rank++
					));
					bibliographie_cache_purge('author_'.((int) $linkEditor->author_id));
				}
			}
		}

		if(count($topics) > 0 and !empty($topics[0]))
			foreach($topics as $linkTopic){
				$linkTopic = bibliographie_topics_get_data($linkTopic);
				if(is_object($linkTopic)){
					if(!($linkTopics instanceof PDOStatement))
						$linkTopics = DB::getInstance()->prepare('INSERT INTO
	`'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink`
(
	`topic_id`,
	`pub_id`
) VALUES (
	:topic_id,
	:pub_id
)');
					$linkTopics->execute(array(
						'topic_id' => (int) $linkTopic->topic_id,
						'pub_id' => (int) $data['pub_id']
					));
					bibliographie_cache_purge('topic_'.((int) $linkTopic->topic_id));
				}
			}

		if(count($tags) > 0 and !empty($tags[0])){
			foreach($tags as $linkTag){
				$linkTag = bibliographie_tags_get_data($linkTag);
				if(is_object($linkTag)){
					if(!($linkTags instanceof PDOStatement))
						$linkTags = DB::getInstance()->prepare('INSERT INTO
	`'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink`
(
	`pub_id`,
	`tag_id`
) VALUES (
	:pub_id,
	:tag_id
)');
					$linkTags->execute(array(
						'pub_id' => (int) $data['pub_id'],
						'tag_id' => (int) $linkTag->tag_id
					));
				}
			}
		}

		if(!$higherTransaction)
			DB::getInstance()->commit();

		if($return){
			bibliographie_cache_purge('search_publications_');
			bibliographie_log('publications', 'createPublication', json_encode($data));
			$return = $data;
		}
	} catch (PDOException $e) {
		DB::getInstance()->rollBack();
		$return = false;
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
	static
		$editPublication = null,
		$linkAuthors = null,
		$linkEditors = null,
		$linkTopics = null,
		$unlinkTopics = null,
		$linkTags = null,
		$unlinkTags = null;

	$return = false;

	$publication = bibliographie_publications_get_data($pub_id);

	if(is_object($publication)){
		try {
			sort($tags);
			sort($topics);

			$higherTransaction = DB::getInstance()->inTransaction();

			if(!higherTransaction)
				DB::getInstance()->beginTransaction();

			if(!($editPublication instanceof PDOStatement))
				$editPublication = DB::getInstance()->prepare('UPDATE `'.BIBLIOGRAPHIE_PREFIX.'publication` SET
	`pub_type` = :pub_type,
	`title` = :title,
	`month` = :month,
	`year` = :year,
	`booktitle` = :booktitle,
	`chapter` = :chapter,
	`series` = :series,
	`journal` = :journal,
	`volume` = :volume,
	`number` = :number,
	`edition` = :edition,
	`publisher` = :publisher,
	`location` = :location,
	`howpublished` = :howpublished,
	`organization` = :organization,
	`institution` = :institution,
	`school` = :school,
	`address` = :address,
	`pages` = :pages,
	`note` = :note,
	`abstract` = :abstract,
	`userfields` = :userfields,
	`bibtex_id` = :bibtex_id,
	`isbn` = :isbn,
	`issn` = :issn,
	`doi` = :doi,
	`url` = :url
WHERE
	`pub_id` = :pub_id
LIMIT 1');

			$data = array (
				'pub_id' => (int) $pub_id,
				'pub_type' => $pub_type,
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
				'url' => $url
			);

			$return = $editPublication->execute($data);

			$data = array_merge($data, array(
				'author' => $author,
				'editor' => $editor,
				'topics' => $topics,
				'tags' => $tags
			));

			DB::getInstance()->exec('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink`
WHERE
	`pub_id` = '.DB::getInstance()->quote((int) $publication->pub_id).'
LIMIT '.((int) count(bibliographie_publications_get_authors($publication->pub_id)) + count(bibliographie_publications_get_editors($publication->pub_id))));

			if(count($author) > 0 and !empty($author[0])){
				$rank = (int) 1;
				foreach($author as $linkAuthor){
					$linkAuthor = bibliographie_authors_get_data($linkAuthor);
					if(is_object($linkAuthor)){
						if(!($linkAuthors instanceof PDOStatement))
							$linkAuthors = DB::getInstance()->prepare('INSERT INTO
			`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink`
		(
			`pub_id`,
			`author_id`,
			`rank`,
			`is_editor`
		) VALUES (
			:pub_id,
			:author_id,
			:rank,
			"N"
		)');
						$linkAuthors->execute(array(
							'pub_id' => (int) $data['pub_id'],
							'author_id' => (int) $linkAuthor->author_id,
							'rank' => (int) $rank++
						));
						bibliographie_cache_purge('author_'.((int) $linkAuthor->author_id));
					}
				}
			}

			if(count($editor) > 0 and !empty($editor[0])){
				$rank = (int) 1;
				foreach($editor as $linkEditor){
					$linkEditor = bibliographie_authors_get_data($linkEditor);
					if(is_object($linkEditor)){
						if(!($linkEditors instanceof PDOStatement))
							$linkEditors = DB::getInstance()->prepare('INSERT INTO
			`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink`
		(
			`pub_id`,
			`author_id`,
			`rank`,
			`is_editor`
		) VALUES (
			:pub_id,
			:author_id,
			:rank,
			"Y"
		)');
						$linkEditors->execute(array(
							'pub_id' => (int) $data['pub_id'],
							'author_id' => (int) $linkEditor->author_id,
							'rank' => (int) $rank++
						));
						bibliographie_cache_purge('author_'.((int) $linkEditor->author_id));
					}
				}
			}

			$unlinkedTopics = array_values(array_diff(bibliographie_publications_get_topics($publication->pub_id), $topics));
			$linkedTopics = array_values(array_diff($topics, bibliographie_publications_get_topics($publication->pub_id)));
			if(count($unlinkedTopics) > 0){
				$unlinkTopics = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink`
WHERE
	`pub_id` = :pub_id AND
	`topic_id` = :topic_id
LIMIT
	1');
				foreach($unlinkedTopics as $topic_id){
					$unlinkTopics->execute(array(
						'pub_id' => (int) $publication->pub_id,
						'topic_id' => (int) $topic_id
					));
					bibliographie_cache_purge('topic_'.((int) $topic_id));
				}
			}

			if(count($linkedTopics) > 0 and !empty($linkedTopics[0]))
				foreach($linkedTopics as $linkTopic){
					$linkTopic = bibliographie_topics_get_data($linkTopic);
					if(is_object($linkTopic)){
						if(!($linkTopics instanceof PDOStatement))
							$linkTopics = DB::getInstance()->prepare('INSERT INTO
		`'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink`
	(
		`topic_id`,
		`pub_id`
	) VALUES (
		:topic_id,
		:pub_id
	)');
						$linkTopics->execute(array(
							'topic_id' => (int) $linkTopic->topic_id,
							'pub_id' => (int) $data['pub_id']
						));
						bibliographie_cache_purge('topic_'.((int) $linkTopic->topic_id));
					}
				}

			$unlinkedTags = array_values(array_diff(bibliographie_publications_get_tags($publication->pub_id), $tags));
			$linkedTags = array_values(array_diff($tags, bibliographie_publications_get_tags($publication->pub_id)));
			if(count($unlinkedTags) > 0){
				$unlinkTags = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink`
WHERE
	`pub_id` = :pub_id AND
	`tag_id` = :tag_id
LIMIT
	1');
				foreach($unlinkedTags as $tag_id){
					$unlinkTags->execute(array(
						'pub_id' => (int) $publication->pub_id,
						'tag_id' => (int) $tag_id
					));
					bibliographie_cache_purge('tag_'.((int) $tag_id));
				}
			}

			if(count($linkedTags) > 0 and !empty($linkedTags[0])){
				foreach($linkedTags as $linkTag){
					$linkTag = bibliographie_tags_get_data($linkTag);
					if(is_object($linkTag)){
						if(!($linkTags instanceof PDOStatement))
							$linkTags = DB::getInstance()->prepare('INSERT INTO
		`'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink`
	(
		`pub_id`,
		`tag_id`
	) VALUES (
		:pub_id,
		:tag_id
	)');
						$linkTags->execute(array(
							'pub_id' => (int) $data['pub_id'],
							'tag_id' => (int) $linkTag->tag_id
						));
					}
				}
			}

			if(!higherTransaction)
				DB::getInstance()->commit();

			if($return){
				bibliographie_cache_purge('publication_'.((int) $pub_id));
				bibliographie_cache_purge('search_publications_');
				bibliographie_log('publications', 'editPublication', json_encode($data));
				$return = $data;
			}
		} catch (PDOException $e){
			DB::getInstance()->rollBack();
			$return = false;
		}
	}

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
	static
		$orderPublications = null,
		$completions = array (
			'year' => ' WHERE FIND_IN_SET(`pub_id`, :publications) ORDER BY `year` DESC, `title` ASC',
			'title' => ' WHERE FIND_IN_SET(`pub_id`, :publications) ORDER BY `title` ASC, `year` DESC'
		);

	$return = $publications;

	if(count($publications) > 0 and in_array($orderBy, array('year', 'title'))){
		$exportHash = bibliographie_publications_cache_list($publications);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publications_'.$exportHash.'_ordered_'.$orderBy.'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publications_'.$exportHash.'_ordered_'.$orderBy.'.json'));

		if($orderPublications[$orderBy] === null)
			$orderPublications[$orderBy] = DB::getInstance()->prepare('SELECT `pub_id` FROM `'.BIBLIOGRAPHIE_PREFIX.'publication`'.$completions[$orderBy]);

		$orderPublications[$orderBy]->execute(array(
			'publications' => array2csv($publications)
		));

		if($orderPublications[$orderBy]->rowCount() > 0)
			$return = $orderPublications[$orderBy]->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/publications_'.$exportHash.'_ordered_'.$orderBy.'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
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
			$addLink = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink` (
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
					bibliographie_cache_purge('publication_'.((int) $pub_id));
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
			bibliographie_cache_purge('topic_'.((int) $topic->topic_id));
			bibliographie_cache_purge('search_');
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
	`'.BIBLIOGRAPHIE_PREFIX.'topicpublicationlink`
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
			bibliographie_cache_purge('topic_'.((int) $topic->topic_id));
			bibliographie_cache_purge('publication_');
			bibliographie_cache_purge('search_');
			bibliographie_log('publications', 'removeTopic', json_encode($return));
		}
	}

	return $return;
}

/**
 *
 * @staticvar null $addLink
 * @param array $publications
 * @param type $tag_id
 * @return type
 */
function bibliographie_publications_add_tag (array $publications, $tag_id) {
	static $addLink = null;

	$tag = bibliographie_tags_get_data($tag_id);

	$return = false;

	if(is_object($tag)){
		$tagsPublications = bibliographie_tags_get_publications($tag->tag_id);

		$publications = array_diff($publications, $tagsPublications);

		if($addLink === null)
			$addLink = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink` (
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
					bibliographie_cache_purge('publication_'.((int) $pub_id));
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
			bibliographie_cache_purge('tag_'.((int) $tag->tag_id));
			bibliographie_cache_purge('search_');
			bibliographie_log('publications', 'addTag', json_encode($return));
		}
	}

	return $return;
}

/**
 *
 * @staticvar null $removeLink
 * @param array $publications
 * @param type $tag_id
 * @return type
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
	`'.BIBLIOGRAPHIE_PREFIX.'publicationtaglink`
WHERE
	FIND_IN_SET(`pub_id`, :list) AND `tag_id` = :tag_id');

		if($removeLink->execute(array(
			'list' => array2csv($publications),
			'tag_id' => (int) $tag->tag_id
		)))
			$return = array (
				'tag_id' => (int) $tag->tag_id,
				'publicationsBefore' => $tagsPublications,
				'publicationsToRemove' => $publications
			);

		if(is_array($return)){
			bibliographie_cache_purge('tag_'.((int) $topic->topic_id));
			bibliographie_cache_purge('publication_');
			bibliographie_cache_purge('search_');
			bibliographie_log('publications', 'removeTag', json_encode($return));
		}
	}

	return $return;
}

/**
 *
 * @param type $query
 * @param type $expandedQuery
 * @return type
 */
function bibliographie_publications_search_publications ($query, $expandedQuery = '') {
	$return = array();

	if(mb_strlen($query) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_publications_'.md5($query).'_'.md5($expandedQuery).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_publications_'.md5($query).'_'.md5($expandedQuery).'.json'));

		preg_match('~from\:([0-9]{4})~', $query, $fromYear);
		preg_match('~to\:([0-9]{4})~', $query, $toYear);
		preg_match('~in\:([0-9]{4})~', $query, $inYear);

		$addQuery = '';
		if(isset($fromYear[1]) and mb_strlen($fromYear[1]) == 4)
			$addQuery .= ' AND `year` >= '.((int) $fromYear[1]);
		if(isset($toYear[1]) and mb_strlen($toYear[1]) == 4)
			$addQuery .= ' AND `year` <= '.((int) $toYear[1]);
		if(isset($inYear[1]) and mb_strlen($inYear[1]) == 4)
			$addQuery = ' AND `year` = '.((int) $inYear[1]);

		$publications = DB::getInstance()->prepare('SELECT
	`pub_id`,
	`title`,
	`relevancy`,
	`year`
FROM (
	SELECT
		`pub_id`,
		`title`,
		MATCH(`title`, `abstract`, `note`) AGAINST (:expanded_query) AS `relevancy`,
		`year`
	FROM
		`'.BIBLIOGRAPHIE_PREFIX.'publication`
) fullTextSearch
WHERE
	`relevancy` > 0'.$addQuery.'
ORDER BY
	`relevancy` DESC,
	`title`');
		$publications->execute(array(
			'expanded_query' => $expandedQuery
		));
		if($publications->rowCount() > 0)
			$return = $publications->fetchAll(PDO::FETCH_COLUMN, 0);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_publications_'.md5($query).'_'.md5($expandedQuery).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $query
 * @param type $expandedQuery
 * @return type
 */
function bibliographie_publications_search_books ($query, $expandedQuery = '') {
	$return = array();

	if(mb_strlen($query) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_books_'.md5($query).'_'.md5($expandedQuery).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_books_'.md5($query).'_'.md5($expandedQuery).'.json'));

		$books = DB::getInstance()->prepare('SELECT
	`booktitle`,
	`count`
FROM (
	SELECT
		`booktitle`,
		COUNT(*) AS `count`,
		MATCH(`booktitle`) AGAINST (:expanded_query) AS `relevancy`
	FROM
		`'.BIBLIOGRAPHIE_PREFIX.'publication`
	GROUP
		BY `booktitle`
) fullTextSearch
WHERE
	`relevancy` > 0
ORDER BY
	`relevancy` DESC,
	`booktitle`');
		$books->execute(array(
			'expanded_query' => $expandedQuery
		));

		if($books->rowCount() > 0)
			$return = $books->fetchAll(PDO::FETCH_OBJ);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_books_'.md5($query).'_'.md5($expandedQuery).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $query
 * @param type $expandedQuery
 * @return type
 */
function bibliographie_publications_search_journals ($query, $expandedQuery = '') {
	$return = array();

	if(mb_strlen($query) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
		if(empty($expandedQuery))
			$expandedQuery = bibliographie_search_expand_query($query);

		if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_journals_'.md5($query).'_'.md5($expandedQuery).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_journals_'.md5($query).'_'.md5($expandedQuery).'.json'));

		$books = DB::getInstance()->prepare('SELECT
	`journal`,
	`count`
FROM (
	SELECT
		`journal`,
		COUNT(*) AS `count`,
		MATCH(`journal`) AGAINST (:expanded_query) AS `relevancy`
	FROM
		`'.BIBLIOGRAPHIE_PREFIX.'publication`
	GROUP
		BY `journal`
) fullTextSearch
WHERE
	`relevancy` > 0
ORDER BY
	`relevancy` DESC,
	`journal`');
		$books->execute(array(
			'expanded_query' => $expandedQuery
		));

		if($books->rowCount() > 0)
			$return = $books->fetchAll(PDO::FETCH_OBJ);

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/search_journals_'.md5($query).'_'.md5($expandedQuery).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @staticvar null $deletePublication
 * @param type $pub_id
 * @return type
 */
function bibliographie_publications_delete_publication ($pub_id) {
	static $deletePublication = null;

	$publication = bibliographie_publications_get_data($pub_id);
	$return = false;

	if(is_object($publication)){
		$notes = bibliographie_notes_get_notes_of_publication($pub_id);

		if(!($deletePublication instanceof PDOStatement))
			$deletePublication = DB::getInstance()->prepare('DELETE FROM `'.BIBLIOGRAPHIE_PREFIX.'publication` WHERE `pub_id` = :pub_id LIMIT 1');

		$return = $deletePublication->execute(array(
			'pub_id' => (int) $publication->pub_id
		));

		if($return){
			bibliographie_cache_purge();
			bibliographie_log('publications', 'deletePublication', json_encode(array('dataDeleted' => $publication)));
		}
	}

	return $return;
}

/**
 *
 * @staticvar null $attachments
 * @param type $pub_id
 * @return type
 */
function bibliographie_publications_get_attachments ($pub_id) {
	static $attachments = null;

	$publication = bibliographie_publications_get_data($pub_id);

	$return = false;

	if(is_object($publication)){
		$return = array();

		if(!($attachments instanceof PDOStatement))
			$attachments = DB::getInstance()->prepare('SELECT
	`att_id`,
	`name`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'attachments`
WHERE
	`pub_id` = :pub_id
ORDER BY
	`name`');

		$attachments->execute(array(
			'pub_id' => $publication->pub_id
		));

		if($attachments->rowCount() > 0)
			$return = $attachments->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	return $return;
}