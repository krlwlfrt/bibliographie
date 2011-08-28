<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

?>

<h2>Search</h2>
<?php
$title = 'Search';
switch($_GET['task']){
	case 'simpleSearch':
		if(mb_strlen($_GET['q']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			if(in_array($_GET['category'], array('topics', 'authors', 'publications', 'tags'))){
				$title = 'Simple search '.htmlspecialchars($_GET['category']);
?>

<h3><?php echo $title?></h3>
<div id="simpleSearch_<?php echo htmlspecialchars($_GET['category'])?>"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" /> searching...</div>

<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	bibliographie_search_simple('<?php echo htmlspecialchars($_GET['category'])?>', 0);
});
	/* ]]> */
</script>
<?php
			}else{
				$title = 'Simple search';
?>

<h3>Topics</h3>
<div id="simpleSearch_topics"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" /> searching...</div>

<h3>Authors</h3>
<div id="simpleSearch_authors"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" /> searching...</div>

<h3>Publications</h3>
<div id="simpleSearch_publications"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" /> searching...</div>

<h3>Tags</h3>
<div id="simpleSearch_tags"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" /> searching...</div>

<script type="text/javascript">
	/* <![CDATA[ */
$(function () {
	bibliographie_search_simple('topics', 1);
	bibliographie_search_simple('authors', 1);
	bibliographie_search_simple('publications', 1);
	bibliographie_search_simple('tags', 1);
});
	/* ]]> */
</script>
<?php
				bibliographie_bookmarks_print_javascript();
			}
?>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_search_simple (category, limit) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/ajax.php',
		data: {
			'task': 'simpleSearch',
			'category': category,
			'q': '<?php echo htmlspecialchars($_GET['q'])?>',
			'limit': limit
		},
		success: function (html) {
			$('#simpleSearch_'+category).html(html);
		}
	})
}
	/* ]]> */
</script>
<?php
		}else
				echo '<p class="error">Your search query was too short! You have to input at least '.BIBLIOGRAPHIE_SEARCH_MIN_CHARS.' chars.</p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';