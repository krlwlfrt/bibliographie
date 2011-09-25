<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

switch($_GET['task']){
	case 'coAuthors':
		if(is_array($_GET['selectedAuthors']) and count($_GET['selectedAuthors']) > 0){
			$selectedAuthors = array();
			foreach($_GET['selectedAuthors'] as $selectedAuthor)
				$selectedAuthors[] = ((int) $selectedAuthor['id']);

			$publications = array();
			foreach($selectedAuthors as $author){
				if(count($publications) > 0)
					$publications = array_intersect($publications, bibliographie_authors_get_publications($author));
				else
					$publications = bibliographie_authors_get_publications($author);
			}

			$authorsResult = _mysql_query("SELECT * FROM `a2author` authors, (
	SELECT `author_id`, `is_editor` FROM `a2publicationauthorlink` WHERE FIND_IN_SET(`pub_id`, '".implode(',', $publications)."')
) links
WHERE
	authors.`author_id` = links.`author_id` AND
	links.`is_editor` = 'N' AND
	NOT FIND_IN_SET(authors.`author_id`, '".implode(',', $selectedAuthors)."')
ORDER BY `surname`, `firstname`");

			echo mysql_error();

			$authors = array();
			if(mysql_num_rows($authorsResult) > 0){
				$_authors = array();
				while($author = mysql_fetch_object($authorsResult))
					$_authors[] = $author->author_id;

				foreach(array_unique($_authors) as $author)
					$authors[] = array(
						'id' => $author,
						'name' => bibliographie_authors_parse_data($author)
					);
			}

			echo json_encode($authors);
		}
	break;

	case 'authorSets':
		if(!empty($_GET['authors'])){
			if(is_csv($_GET['authors'], 'int')){
				$authors = explode(',', $_GET['authors']);

				if(count($authors) > 1){
					$publications = array();
					foreach($authors as $author){
						if(count($publications) > 0)
							$publications = array_intersect($publications, bibliographie_authors_get_publications($author));
						else
							$publications = bibliographie_authors_get_publications($author);
					}

					if(!empty($_GET['query']) and count($publications) > 0){
						$publications = implode(',', $publications);

						$query = bibliographie_search_expand_query($_GET['query']);

						$publicationsResult = _mysql_query("SELECT * FROM (SELECT `pub_id`, `title`, (MATCH(`title`, `abstract`, `note`) AGAINST ('".mysql_real_escape_string(stripslashes($query))."')) AS `relevancy` FROM `a2publication` WHERE FIND_IN_SET(`pub_id`, '".$publications."')) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `title`");

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

				}else
					echo '<p class="notice">To see a list of publications of '.bibliographie_authors_parse_data($authors[0], array('linkProfile' => true)).' visit his/her profile!</p>';
			}
		}else
			echo '<p class="notice">You have to select at least two authors to search!</p>';
	break;

	case 'simpleSearch':
		if(mb_strlen($_GET['q']) >= BIBLIOGRAPHIE_SEARCH_MIN_CHARS){
			$searchResults = null;
			$expandedeQuery = (string) '';

			$publications = array();

			$searchTimer = microtime(true);
			switch($_GET['category']){
				case 'topics':
					$searchResults = _mysql_query("SELECT * FROM (SELECT `topic_id`, `name`, `description`, `url`, (MATCH(`name`, `description`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2topics`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `name`");
				break;

				case 'authors':
					$options['plurals'] = true;
					$searchResults = _mysql_query("SELECT * FROM (SELECT `author_id`, `surname`, (MATCH(`surname`, `firstname`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2author`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `surname`, `author_id`");
				break;

				case 'publications':
					$searchResults = _mysql_query("SELECT * FROM (SELECT `pub_id`, `title`, (MATCH(`title`, `abstract`, `note`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2publication`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `title`");

					if(mysql_num_rows($searchResults)){
						$result = _mysql_query("SELECT `pub_id` FROM (SELECT `pub_id`, `year`, (MATCH(`title`, `abstract`, `note`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2publication`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `year` DESC");

						while($publication = mysql_fetch_object($result))
							$publications[] = $publication->pub_id;
					}
				break;

				case 'tags':
					$searchResults = _mysql_query("SELECT * FROM (SELECT `tag_id`, `tag`, (MATCH(`tag`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2tags`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC");
				break;

				case 'journals':
					$searchResults = _mysql_query("SELECT * FROM (SELECT `journal`, COUNT(*) AS `count`, (MATCH(`journal`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2publication` GROUP BY `journal`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `journal`");
				break;

				case 'books':
					$searchResults = _mysql_query("SELECT * FROM (SELECT `booktitle`, COUNT(*) AS `count`, (MATCH(`booktitle`) AGAINST ('".mysql_real_escape_string(stripslashes($_SESSION['search_query']))."')) AS `relevancy` FROM `a2publication` GROUP BY `booktitle`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC, `booktitle`");
				break;
			}
			echo '<em style="float: right; font-size: 0.8em;">query '.round(microtime(true) - $searchTimer, 5).'s';

			if(mysql_num_rows($searchResults) > 0){
				$i = (int) 0;
				$limit = -1;
				if($_GET['limit'] == 1)
					$limit = ceil(log(mysql_num_rows($searchResults), 2) + 1) * 2;
				$text = (string) '';

				while($row = mysql_fetch_object($searchResults) and ($i < $limit or $limit == -1)){
					switch($_GET['category']){
						case 'topics':
							$text .= '<div class="searchResult">';
							if(!empty($row->url))
								$text .= '<em style="float: right">'.htmlspecialchars($row->url).'</em>';
							$text .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $row->topic_id).'" style="display: block">'.htmlspecialchars($row->name).'</a>';
							if(!empty($row->description))
								$text .= '<em>'.htmlspecialchars($row->description).'</em>';
							$text .= '</div>';
						break;

						case 'authors':
							$text .= '<div class="searchResult">';
							$text .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.((int) $row->author_id).'" style="display: block">'.bibliographie_authors_parse_data($row->author_id).'</a>';
							$text .= '</div>';
						break;

						case 'publications':
							$text .= '<div id="publication_container_'.((int) $row->pub_id).'" class="bibliographie_publication';
							if(bibliographie_bookmarks_check_publication($row->pub_id))
								$text .= ' bibliographie_publication_bookmarked';
							$text .= '">'.bibliographie_bookmarks_print_html($row->pub_id).bibliographie_publications_parse_data($row->pub_id).'</div>';
						break;

						case 'journals':
							$text .= '<div class="searchResult">';
							$text .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=journal&amp;container='.htmlspecialchars($row->journal).'" style="display: block">'.htmlspecialchars($row->journal).'</a>';
							$text .= '</div>';
						break;

						case 'books':
							$text .= '<div class="searchResult">';
							$text .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=book&amp;container='.htmlspecialchars($row->booktitle).'" style="display: block">'.htmlspecialchars($row->booktitle).'</a>';
							$text .= '</div>';
						break;

						case 'tags':
							$text .= '<div class="searchResult"><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/tags/?task=showTag&amp;tag_id='.((int) $row->tag_id).'" style="display: block">'.htmlspecialchars($row->tag).'</a></div>';
						break;
					}

					$i++;
				}

				echo ', output '.round(microtime(true) - $searchTimer, 5).'s</em>';

				echo '<div id="bibliographie_search_'.htmlspecialchars($_GET['category']).'_result">Showing ';
				echo '<strong>'.$i.' result</strong>(s) of ';
				echo '<strong><span id="bibliographie_search_'.htmlspecialchars($_GET['category']).'_results_count">'.mysql_num_rows($searchResults).'</span> found '.htmlspecialchars($_GET['category']).'</strong> for query ';
				echo '<strong>'.htmlspecialchars($_GET['q']).'</strong>. ';
				if($limit != -1 and $i < mysql_num_rows($searchResults)){
					if($_GET['category'] == 'publications'){
						$publicationsList = bibliographie_publications_cache_list($publications);
						echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=showPublications&amp;publicationsList='.$publicationsList.'">Show all results!</a>';
					}else
						echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=simpleSearch&amp;category='.htmlspecialchars($_GET['category']).'&amp;q='.htmlspecialchars($_GET['q']).'&amp;noQueryExpansion='.((int) $_GET['noQueryExpansion']).'">Show all results!</a>';
				}
				echo '</div>';
				echo PHP_EOL.$text;


			}else
				echo '</em><div>There were no '.htmlspecialchars($_GET['category']).' for your search with query <strong>'.htmlspecialchars($_GET['q']).'</strong>.</div>';

		}else
			echo '<p class="error">Your search query <em>'.htmlspecialchars($_GET['q']).'</em> was too short! You have to input at least '.BIBLIOGRAPHIE_SEARCH_MIN_CHARS.' chars. </p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';