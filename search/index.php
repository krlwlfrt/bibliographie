<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

$bibliographie_search_categories = array(
	'topics',
	'authors',
	'publications',
	'tags',
	'journals',
	'books'
);
?>

<h2>Search</h2>
<?php
$bibliographie_title = 'Search';
switch($_GET['task']){
	case 'authorSets':
?>

<p class="notice">Select two or more authors and optionally provide a query string to search!</p>
<div class="unit">
	<div id="coAuthorsContainer" class="bibliographie_similarity_container" style="float: right; max-height: 200px; overflow-y: scroll; width: 40%;"></div>
	<label for="authors" class="block">Authors</label>
	<input type="text" id="authors" name="authors" />

	<label for="query" class="block" style="clear: both;">Query</label>
	<input type="text" id="query" name="query" style="width: 100%" value="<?php echo htmlspecialchars($_GET['query'])?>" />
</div>
<div id="bibliographie_search_results"></div>
<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	$('#authors').tokenInput(bibliographie_web_root+'/authors/ajax.php?task=searchAuthors', {
		'searchDelay': bibliographie_request_delay,
		'minChars': bibliographie_search_min_chars,
		'preventDuplicates': true,
		'prePopulate': null,
		'onAdd': function (item) {
			bibliographie_authors_get_co_authors('authors', 'coAuthorsContainer');
			bibliographie_authors_get_publications_for_authors_set($('#authors').val(), $('#query').val());
		},
		'onDelete': function (item) {
			if($('#authors').tokenInput('get').length > 0){
				bibliographie_authors_get_co_authors('authors', 'coAuthorsContainer');
				bibliographie_authors_get_publications_for_authors_set($('#authors').val(), $('#query').val());
			}else{
				$('#coAuthorsContainer').hide();
				$('#bibliographie_search_results').empty();
			}
		}
	});

	$('#query').bind('keyup change', function () {
		delayRequest('bibliographie_authors_get_publications_for_authors_set', Array($('#authors').val(), $('#query').val()));
	});

	$('#content input').charmap();
});
	/* ]]> */
</script>
<?php
		bibliographie_charmap_print_charmap();
	break;

	case 'showPublications':
		$publications = bibliographie_publications_get_cached_list($_GET['publicationsList']);
		if(is_array($publications) and count($publications) > 0){
			echo bibliographie_publications_print_list(
				$publications,
				BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=showPublications&amp;publicationsList='.htmlspecialchars($_GET['publicationsList'])
			);
		}
	break;

	case 'simpleSearch':
		if(mb_strlen($_GET['q']) >= 1){
			$timer = microtime(true);

			$searchResults = array();
			$expandedQuery = bibliographie_search_expand_query($_GET['q']);

			if(empty($_GET['category']) or $_GET['category'] == 'authors' and mb_strlen($_GET['q']) > BIBLIOGRAPHIE_SEARCH_MIN_CHARS)
				$searchResults['authors'] = bibliographie_authors_search_authors($_GET['q']);

			if(empty($_GET['category']) or $_GET['category'] == 'notes' and mb_strlen($_GET['q']) > BIBLIOGRAPHIE_SEARCH_MIN_CHARS)
				$searchResults['notes'] = bibliographie_notes_search_notes($_GET['q'], $expandedQuery);

			if(empty($_GET['category']) or $_GET['category'] == 'books')
				$searchResults['books'] = bibliographie_publications_search_books($_GET['q'], $expandedQuery);

			if(empty($_GET['category']) or $_GET['category'] == 'journals')
				$searchResults['journals'] = bibliographie_publications_search_journals($_GET['q'], $expandedQuery);

			if(empty($_GET['category']))
				$searchResults['publications'] = bibliographie_publications_search_publications($_GET['q'], $expandedQuery);
			if($_GET['category'] == 'publications')
				$searchResults['publications'] = bibliographie_publications_sort(bibliographie_publications_search_publications($_GET['q'], $expandedQuery), 'year');



			if(empty($_GET['category']) or $_GET['category'] == 'tags')
				$searchResults['tags'] = bibliographie_tags_search_tags($_GET['q'], $expandedQuery);

			if(empty($_GET['category']) or $_GET['category'] == 'topics')
				$searchResults['topics'] = bibliographie_topics_search_topics($_GET['q'], $expandedQuery);

			$timer = microtime(true) - $timer;


			$options = array('linkProfile' => true);
			$toc = (string) '';
			$str = (string) '';
			$limit = (int) -1;

			if(!in_array($_GET['category'], array('authors', 'books', 'journals', 'notes', 'publications', 'tags', 'topics'))){
				bibliographie_history_append_step('search', 'Search for "'.htmlspecialchars($_GET['q']).'"');
				$limit = 30;
			}else{
				bibliographie_history_append_step('search', 'Search in '.$_GET['category'].' for "'.htmlspecialchars($_GET['q']).'"');
				$limit = -1;
			}


			foreach($searchResults as $category => $results){
				if(count($results) > 0){
					$str .= '<h3 id="bibliographie_search_results_'.$category.'">'.ucfirst($category).'</h3>';
					$toc .= '<li><a href="#bibliographie_search_results_'.$category.'">'.ucfirst($category).'</a> ('.count($results).' results)</li>';

					if(in_array($category, array('authors', 'books', 'journals', 'notes', 'tags', 'topics'))){
						$i = (int) 0;

						if(count($results) > $limit and $limit != -1)
							$str .= 'Found <strong>'.count($results).' '.$category.'</strong> of which '.$limit.' are shown. <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=simpleSearch&amp;category='.$category.'&amp;q='.htmlspecialchars($_GET['q']).'">Show all found '.$category.'!</a>';
						else
							$str .= 'Found <strong>'.count($results).' '.$category.'</strong> shown by relevancy.';

						foreach($results as $row){
							if(++$i == $limit and $limit != -1)
								break;

							$str .= '<div class="bibliographie_search_result">';
							if($category == 'authors')
								$str .= bibliographie_authors_parse_data($row->author_id, $options);
							elseif($category == 'books')
								$str .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=book&container='.htmlspecialchars($row->booktitle).'">'.$row->booktitle.'</a>, '.$row->count.' article(s)';
							elseif($category == 'journals')
								$str .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=journal&container='.htmlspecialchars($row->journal).'">'.$row->journal.'</a>, '.$row->count.' publication(s)';
							elseif($category == 'notes')
								$str .= $row->text.'<br/><em style="font-size: 0.8em">'.bibliographie_publications_parse_data($row->pub_id).'</em>';
							elseif($category == 'tags')
								$str .= bibliographie_tags_parse_tag($row->tag_id, $options);
							elseif($category == 'topics')
								$str .= bibliographie_topics_parse_name($row->topic_id, $options);
							$str .= '</div>';
						}
					}elseif($category == 'publications'){
						if(count($results) > $limit and $limit != -1)
							$results = array_slice($results, 0, $limit);

						$str .= bibliographie_publications_print_list($results, '', array('onlyPublications' => true));
					}
				}
			}

			if(!empty($toc)){
				echo '<em style="float: right; font-size: 0.8em;">'.round($timer, 6).'s, '.count(explode(' ', $expandedQuery)).' words</em>';
				echo '<ul>'.$toc.'</ul>';
			}
			if(!empty($str)){
				echo '<div id="bibliographie_search_results">'.$str.'</div>';
?>

<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	$('#bibliographie_search_results').highlight(<?php echo json_encode(explode(' ', $expandedQuery))?>);
});
	/* ]]> */
</script>
<?php
			}
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';