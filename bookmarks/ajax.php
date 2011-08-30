<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'exportToBibTex':
		$bookmarks = bibliographie_bookmarks_get_bookmarks();
		if(count($bookmarks) > 0){
			$mysql_string = "";

			foreach($bookmarks as $bookmark){
				if(!empty($mysql_string))
					$mysql_string .= " OR ";

				$mysql_string .= "`pub_id` = ".((int) $bookmark);
			}

			$bookmarkResult = mysql_query("SELECT `pub_id`, `pub_type`, `bibtex_id`, `address`, `booktitle`, `chapter`, `edition`, `howpublished`, `institution`, `journal`, `month`, `note`, `number`, `organization`, `pages`, `publisher`, `school`, `series`, `title`, `url`, `volume`, `year` FROM `a2publication` WHERE ".$mysql_string." ORDER BY `title`");
			if(mysql_num_rows($bookmarkResult) > 0){
				$bibtex = new Structures_BibTex(array(
					'stripDelimiter' => true,
					'validate' => true,
					'unwrap' => true,
					'removeCurlyBraces' => true,
					'extractAuthors' => true
				));

				while($publication = mysql_fetch_assoc($bookmarkResult)){
					$publication['entryType'] = $publication['pub_type'];
					if(empty($publication['bibtex_id']))
						$publication['bibtex_id'] = md5($publication['title']);
					$publication['cite'] = $publication['bibtex_id'];

					$authors = bibliographie_publications_get_authors($publication['pub_id']);
					$editors = bibliographie_publications_get_editors($publication['pub_id']);

					unset($publication['pub_id'], $publication['pub_type'], $publication['bibtex_id']);

					if(is_array($authors) and count($authors) > 0)
						foreach($authors as $author)
							$publication['author'][] = bibliographie_authors_parse_data($author, array('forBibTex' => true));

					if(is_array($editors) and count($editors) > 0)
						foreach($editors as $editor)
							$publication['editor'][] = bibliographie_authors_parse_data($editor, array('forBibTex' => true));

					$_publication = array();
					foreach($publication as $key => $field)
						if(!empty($field))
							$_publication[$key] = $field;

					$bibtex->data[] = $_publication;
				}

				header('Content-Type: text/plain; charset=UTF-8');
				echo $bibtex->bibtex();
			}
		}
	break;

	case 'setBookmark':
		$text = 'An error occured!';
		if(bibliographie_bookmarks_set_bookmark($_GET['pub_id']))
			$text = bibliographie_bookmarks_print_html($_GET['pub_id']);

		echo $text;
	break;

	case 'unsetBookmark':
		$text = 'An error occured!';
		if(bibliographie_bookmarks_unset_bookmark($_GET['pub_id']))
			$text = bibliographie_bookmarks_print_html($_GET['pub_id']);

		echo $text;
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';