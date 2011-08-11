<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

?>

<h2>Bookmarks</h2>
<?php
switch($_GET['task']){
	case 'clearBookmarks':
		bibliographie_bookmarks_clear_bookmarks();

	case 'showBookmarks':
		$title = 'List of my bookmarks';
?>

<span style="float: right"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=clearBookmarks">Clear all bookmarks</a></span>
<h3>List of my bookmarks</h3>
<?php
		$publications = bibliographie_bookmarks_get_bookmarks();
		if(count($publications) > 0){
?>

<p class="notice">In total you have set <?php echo count($publications)?> bookmark(s)!</p>
<?php
			bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showPublications&topic_id='.((int) $_GET['topic_id']).$includeSubtopics, $_GET['bookmarkBatch']);
		}else
			echo '<p class="error">You have not set any bookmarks!</p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';