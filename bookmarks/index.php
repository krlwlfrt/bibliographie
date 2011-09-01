<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

?>

<h2>Bookmarks</h2>
<?php
switch($_GET['task']){
	case 'exportBookmarks':
?>

<h3>Export bookmkars</h3>
<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/ajax.php?task=exportBookmarks&amp;target=bibTex">BibTex</a><br />
<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/ajax.php?task=exportBookmarks&amp;target=rtf">RTF</a>
<?php
	break;

	case 'clearBookmarks':
		bibliographie_bookmarks_clear_bookmarks();

	case 'showBookmarks':
		$title = 'List of my bookmarks';
?>

<span style="float: right">
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=exportBookmarks">Export</a>
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=clearBookmarks">Clear</a> all bookmarks
</span>
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