<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

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

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/?task=authorSets" method="get">
	<div class="unit">
		<div id="coAuthorsContainer" class="bibliographie_similarity_container" style="float: right; max-height: 200px; overflow-y: scroll; width: 40%;"></div>
		<label for="authors" class="block">Authors</label>
		<input type="text" id="authors" name="authors" />

		<label for="query" class="block" style="clear: both;">Query</label>
		<input type="text" id="query" name="query" style="width: 100%" value="<?php echo htmlspecialchars($_GET['query'])?>" />
	</div>

	<div class="submit"><input type="hidden" name="task" value="authorSets" /><input type="submit" value="search" /></div>
</form>
<?php
		bibliographie_charmap_print_charmap();
?>

<div id="bibliographie_search_results">
<?php
		$query = '';
		if(!empty($_GET['authors'])){
			if(is_csv($_GET['authors'], 'int')){
				$authors = explode(',', $_GET['authors']);

				if(count($authors) == 1)
					echo '<p class="notice">You just entered one author and get therefore a list of the publications of '.bibliographie_authors_parse_data($authors[0], array('linkProfile' => true)).'!</p>';

				$publications = array();
				foreach($authors as $author){
					if(count($publications) > 0)
						$publications = array_intersect($publications, bibliographie_authors_get_publications($author));
					else
						$publications = bibliographie_authors_get_publications($author);
				}

				if(!empty($_GET['query']) and count($publications) > 0){
					$mysql_string = "";
					foreach($publications as $publication){
						if(!empty($mysql_string))
							$mysql_string .= " OR ";

						$mysql_string .= "`pub_id` = ".((int) $publication);
					}

					$query = bibliographie_search_expand_query($_GET['query']);
					$publicationsResult = _mysql_query("SELECT * FROM (SELECT `pub_id`, `title`, (MATCH(`title`, `abstract`, `note`) AGAINST ('".mysql_real_escape_string(stripslashes($query))."')) AS `relevancy` FROM `a2publication` WHERE ".$mysql_string.") fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `title`");

					if(mysql_num_rows($publicationsResult) > 0){
						$publications = array();
						while($publication = mysql_fetch_object($publicationsResult))
							$publications[] = $publication->pub_id;
					}else
						echo '<p class="notice">There were no results for your query string! Showing all publications for this set of authors instead...</p>';
				}

				if(count($publications) > 0){
					bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=authorSets&amp;authors='.$_GET['authors'], $_GET['bookmarkBatch']);
				}else
					echo '<p class="notice">No publications were found for this set of authors!</p>';

			}
		}
?>

</div>

<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	function bibliographie_authors_get_co_authors (field, container) {
		setLoading('#'+container);
		$.ajax({
			url: bibliographie_web_root+'/search/ajax.php',
			data: {
				'task': 'coAuthors',
				'selectedAuthors': $('#'+field).tokenInput('get')
			},
			dataType: 'json',
			success: function (json) {
				$('#'+container).html('<div style="margin-bottom: 10px;">Found '+json.length+' co authors.</div>').show();
				$.each(json, function (dummy, value) {
					$('#'+container).append('<div><a href="javascript:;" onclick="$(\'#authors\').tokenInput(\'add\', {\'id\': '+value.id+', \'name\': \''+value.name+'\'});" style="float: right;"><span class="silk-icon silk-icon-add"></span></a> '+value.name+'</div>');
				});
			}
		})
	}

	$('#authors').tokenInput(bibliographie_web_root+'/authors/ajax.php?task=searchAuthors', {
		'searchDelay': bibliographie_request_delay,
		'minChars': bibliographie_search_min_chars,
		'preventDuplicates': true,
		'prePopulate': <?php echo json_encode(bibliographie_authors_populate_input($_GET['authors']))?>,
		'onAdd': function (item) {
			bibliographie_authors_get_co_authors('authors', 'coAuthorsContainer');
		},
		'onDelete': function (item) {
			bibliographie_authors_get_co_authors('authors', 'coAuthorsContainer');
		}
	});

	$('input').charmap();
	$('#bibliographie_charmap').dodge();
	$('#bibliographie_search_results').highlight(<?php echo json_encode(explode(' ', $query))?>);
});
	/* ]]> */
</script>
<?php
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