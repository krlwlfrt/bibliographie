<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

?>

<h2>Search</h2>
<?php
$title = 'Search';
switch($_GET['task']){
	case 'simpleSearch':
		$title = 'Simple search';

		if(mb_strlen($_GET['q']) >= 3){
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
function bibliographie_search_simple (category) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/ajax.php',
		data: {
			'task': 'simpleSearch',
			'category': category,
			'q': '<?php echo htmlspecialchars($_GET['q'])?>'
		},
		success: function (html) {
			$('#simpleSearch_'+category).html(html);
		}
	})
}

$(function () {
	bibliographie_search_simple('topics');
	bibliographie_search_simple('authors');
	bibliographie_search_simple('publications');
	bibliographie_search_simple('tags');
});
	/* ]]> */
</script>
<?php
			bibliographie_bookmarks_print_javascript();
		}else
			echo '<p class="error">Please enter at least 3 chars.';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';