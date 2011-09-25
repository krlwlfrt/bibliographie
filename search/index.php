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
$title = 'Search';
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

	$('input').charmap();
	$('#bibliographie_charmap').dodge();
});
	/* ]]> */
</script>
<?php
		bibliographie_charmap_print_charmap();
	break;

	case 'showPublications':
		$publications = bibliographie_publications_get_cached_list($_GET['publicationsList']);
		if(is_array($publications) and count($publications) > 0){
			bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=showPublications&amp;publicationsList='.htmlspecialchars($_GET['publicationsList']), $_GET['bookmarkBatch']);
		}
	break;

	case 'simpleSearch':
		if(mb_strlen($_GET['q']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			if($_GET['noQueryExpansion'] != '1'){
				$_searchTerms = array_unique(explode(' ', bibliographie_search_expand_query($_GET['q'])));
				usort($_searchTerms, function ($a, $b) {
					if(mb_strlen($a) == mb_strlen($b))
						return 0;

					return(mb_strlen($a) < mb_strlen($b)) ? 1 : -1;
				});
				$searchTerms = array();
				foreach($_searchTerms as $searchTerm)
					if(mb_strlen($searchTerm) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS)
						$searchTerms[] = $searchTerm;
					else
						break;
?>

<em style="float: right; font-size: 0.8em;">expanded to <?php echo count($searchTerms)?> words, <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/?task=simpleSearch&amp;q=<?php echo htmlspecialchars($_GET['q'])?>&amp;noQueryExpansion=1">use exact matching</a></em>
<?php
			}else{
				$searchTerms = explode(' ', $_GET['q']);
?>

<em style="float: right; font-size: 0.8em;"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/?task=simpleSearch&amp;q=<?php echo htmlspecialchars($_GET['q'])?>">use query expansion</a></em>
<?php
			}

			$_SESSION['search_query'] = $_GET['q'].' '.implode(' ', $searchTerms);
			$highlightTerms = json_encode(array_values($searchTerms));

			if(in_array($_GET['category'], $bibliographie_search_categories)){
				$title = 'Simple search '.htmlspecialchars($_GET['category']);
?>

<h3 id="bibliographie_search_<?php echo htmlspecialchars($_GET['category'])?>_title"><?php echo $title?></h3>
<div id="bibliographie_search_<?php echo htmlspecialchars($_GET['category'])?>_container"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" /> searching...</div>

<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	bibliographie_search_simple('<?php echo htmlspecialchars($_GET['category'])?>', 0, '<?php echo $_GET['q']?>', '<?php echo $_GET['noQueryExpansion']?>', <?php echo $highlightTerms?>);
});
	/* ]]> */
</script>
<?php
			}else{
				$title = 'Simple search';

				$text = (string) '';
				echo '<ul style="display: none">'.PHP_EOL;
				foreach($bibliographie_search_categories as $category){
					$categoryTitle = mb_strtoupper(mb_substr($category, 0, 1)).mb_substr($category, 1);

					echo '<li id="bibliographie_search_'.htmlspecialchars($category).'_link" style="display: none;"><a href="#bibliographie_search_'.htmlspecialchars($category).'_title">'.htmlspecialchars($categoryTitle).'</a></li>'.PHP_EOL;

					$text .= '<h3 id="bibliographie_search_'.htmlspecialchars($category).'_title" style="display: none">'.htmlspecialchars($categoryTitle).'</h3>'
						.'<div id="bibliographie_search_'.htmlspecialchars($category).'_container" style="display: none" class="bibliographie_search_container"></div>'.PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
				echo $text;
?>

<p id="bibliographie_search_result_is_empty" style="display: none;" class="error">Sorry, but your search did not give any results!</p>
<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	$.each(<?php echo json_encode($bibliographie_search_categories)?>, function (dummy, category) {
		bibliographie_search_simple(category, 1, '<?php echo $_GET['q']?>', '<?php echo $_GET['noQueryExpansion']?>', <?php echo $highlightTerms?>);
	});

	$('#bibliographie_search_result_is_empty').ajaxStop(function () {
		if($('.bibliographie_search_container:visible').length == 0){
			$(this).show('slow');
		}
	});
});
	/* ]]> */
</script>
<?php
				bibliographie_bookmarks_print_javascript();
			}
		}else
				echo '<p class="error">Your search query was too short! You have to input at least '.BIBLIOGRAPHIE_SEARCH_MIN_CHARS.' chars.</p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';