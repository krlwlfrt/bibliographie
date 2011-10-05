<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

?>

<h2>Bookmarks</h2>
<?php
switch($_GET['task']){
	case 'clearBookmarks':
?>

<h3>Clearing bookmarks</h3>
<?php
		$clearedBookmarks = bibliographie_bookmarks_clear_bookmarks();
		if($clearedBookmarks > 0)
			echo '<p class="success">'.$clearedBookmarks.' bookmarks where cleared from the list of bookmarks!</p>';
		else
			echo '<p class="notice">List of bookmarks was empty!</p>';
	break;

	case 'showBookmarks':
		$bibliographie_title = 'List of my bookmarks';
?>

<span style="float: right"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=clearBookmarks">Clear</a> all bookmarks</span>
<h3>List of my bookmarks</h3>
<?php
		$publications = bibliographie_bookmarks_get_bookmarks();
		if(count($publications) > 0){
?>

<p class="notice">In total you have set <?php echo count($publications)?> bookmark(s)!</p>
<?php
			bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/bookmarks/?task=showBookmarks', $_GET['bookmarkBatch'], false);
		}else
			echo '<p class="error">You have not set any bookmarks!</p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';