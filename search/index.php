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
<div id="simpleSearch_topics"></div>

<h3>Authors</h3>
<div id="simpleSearch_authors"></div>

<h3>Publications</h3>
<div id="simpleSearch_publications"></div>

<h3>Tags</h3>
<div id="simpleSearch_tags"></div>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_search_simple (category, q) {
	if(category == null)
		return;

	var quiet = 1;
	if(q == null){
		q = '<?php echo htmlspecialchars($_GET['q'])?>';
		quiet = 0;
	}

	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/ajax.php',
		data: {
			'task': 'simpleSearch',
			'category': category,
			'q': q,
			'quiet': quiet
		},
		success: function (html) {
			$('#simpleSearch_'+category).append(html);
		}
	})
}

$(function () {
	bibliographie_search_simple('topics');
	bibliographie_search_simple('authors');
});
	/* ]]> */
</script>
<?php
		}else
			echo '<p class="error">Please enter at least 3 chars.';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';