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
?>

<span style="float: right"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=clearBookmarks">Clear all bookmarks</a></span>
<h3>List of my bookmarks</h3>
<?php
		$allPublications = mysql_num_rows(mysql_query("SELECT * FROM
	`a2userbookmarklists` bookmarks,
	`a2publication` publications
WHERE
	publications.`pub_id` = bookmarks.`pub_id` AND
	bookmarks.`user_id` = ".((int) bibliographie_user_get_id($_SERVER['PHP_AUTH_USER']))));

		if($allPublications > 0){
?>

<p class="notice">In total you have set <?php echo $allPublications?> bookmark(s)!</p>
<?php
			$pageData = bibliographie_print_pages(BIBLIOGRAPHIE_WEB_ROOT.'/bookmarks/?task=showBookmarks', $allPublications);

			$publications = mysql_query("SELECT * FROM
	`a2userbookmarklists` bookmarks,
	`a2publication` publications
WHERE
	publications.`pub_id` = bookmarks.`pub_id` AND
	bookmarks.`user_id` = ".((int) bibliographie_user_get_id($_SERVER['PHP_AUTH_USER']))."
	ORDER BY
		publications.`year` DESC
	LIMIT ".$pageData['offset'].", ".$pageData['perPage']);

			$lastYear = null;
			while($publication = mysql_fetch_object($publications)){
				if($publication->year != $lastYear)
					echo '<h4>Publications in '.((int) $publication->year).'</h4>';

				echo '<div id="publication_container_'.((int) $publication->pub_id).'" class="bibliographie_publication';
					if(bibliographie_bookmarks_check_publication($publication->pub_id))
						echo ' bibliographie_publication_bookmarked';
					echo '">'.bibliographie_bookmarks_print_html($publication->pub_id).bibliographie_publications_parse_data($publication->pub_id).'</div>';

				$lastYear = $publication->year;
			}

			bibliographie_print_pages(BIBLIOGRAPHIE_WEB_ROOT.'/bookmarks/?task=showBookmarks', $allPublications);
			bibliographie_bookmarks_print_javascript();
		}else
			echo '<p class="error">You have not set any bookmarks!</p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';