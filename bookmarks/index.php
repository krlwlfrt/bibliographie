<?php
require dirname(__FILE__).'/../init.php';
$bibliographie_title = 'Bookmarks';
?>

<h2>Bookmarks</h2>
<?php
switch($_GET['task']){
	case 'clearBookmarks':
		bibliographie_history_append_step('bookmarks', 'Clearing bookmarks');
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
		bibliographie_history_append_step('bookmarks', 'Showing bookmarks');
?>

<span style="float: right"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=clearBookmarks">Clear</a> all bookmarks</span>
<h3>List of my bookmarks</h3>
<?php
		$publications = bibliographie_bookmarks_get_bookmarks();
		if(count($publications) > 0){
?>

<p class="notice">In total you have set <?php echo count($publications)?> bookmark(s)!</p>
<?php
			bibliographie_publications_print_list(
				$publications,
				BIBLIOGRAPHIE_WEB_ROOT.'/bookmarks/?task=showBookmarks',
				array (
					'bookmarkingLink' => false
				)
			);
		}else
			echo '<p class="notice">You have not set any bookmarks!</p>';
	break;
}

require dirname(__FILE__).'/../close.php';