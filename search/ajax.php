<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'simpleSearch':
		if($_GET['category'] == 'topics'){
			/**
			 * Here we try to add plurals and singulars.
			 */
			$expandedQuery = $_GET['q'].bibliographie_search_expand_query($_GET['q'], 2);

			if(mb_strlen($_GET['q']) >= 3){
				$searchResults = mysql_query("SELECT * FROM (SELECT `topic_id`, `name`, `description`, `url`, (MATCH(`name`, `description`) AGAINST ('".mysql_real_escape_string(stripslashes($expandedQuery))."')) AS `relevancy` FROM `a2topics`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC");

				if(mysql_num_rows($searchResults) > 0){
					$i = (int) 0;
					$limit = ceil(log(mysql_num_rows($searchResults), 2) + 1) * 2;

					while($topic = mysql_fetch_object($searchResults) and $i < $limit){
						$text .= '<div class="searchResult">';
						if(!empty($topic->url))
							$text .= '<em style="float: right">'.htmlspecialchars($topic->url).'</em>';
						$text .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.$topic->topic_id.'" style="display: block">'.htmlspecialchars($topic->name).'</a>';
						if(!empty($topic->description))
							$text .= '<em>'.htmlspecialchars($topic->description).'</em>';
						$text .= '</div>';
						$i++;
					}

					echo '<div>Showing '.$i.' results of a total of '.mysql_num_rows($searchResults).' found topics for query <strong>'.htmlspecialchars($_GET['q']).'</strong>.</div>'.$text;
				}else
					echo '<div>There were no results for your search with query <strong>'.htmlspecialchars($_GET['q']).'</strong>.</div>';
			}else
				echo '<p class="error">Search query was too short!</p>';
		}

		if($_GET['category'] == 'authors'){
			/**
			 * For names we just remove and add suffixes.
			 */
			$expandedQuery = $_GET['q'].bibliographie_search_expand_query($_GET['q'], 1);

			if(mb_strlen($_GET['q']) >= 3){
				$searchResults = mysql_query("SELECT * FROM (SELECT `author_id`, `von`, `surname`, `jr`, `firstname`, `url`, (MATCH(`surname`, `firstname`) AGAINST ('".mysql_real_escape_string(stripslashes($expandedQuery))."')) AS `relevancy` FROM `a2author`) fullTextSearch WHERE `relevancy` > 0 ORDER BY `relevancy` DESC");

				if(mysql_num_rows($searchResults) > 0){
					$i = (int) 0;
					$limit = ceil(log(mysql_num_rows($searchResults), 2) + 1) * 2;
					$text = (string) '';

					while($author = mysql_fetch_object($searchResults) and $i < $limit){
						$text .= '<div class="searchResult">';
						if(!empty($author->url))
							$text .= '<em style="float: right">'.htmlspecialchars($author->url).'</em>';
						$text .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.$author->author_id.'" style="display: block">'.bibliographie_authors_parse_data($author->author_id).'</a>';
						if(!empty($topic->email))
							$text .= '<em>'.htmlspecialchars($topic->email).'</em>';
						$text .= '</div>';
						$i++;
					}

					echo 'Showing '.$i.' results of a total of '.mysql_num_rows($searchResults).' found authors for query <strong>'.htmlspecialchars($_GET['q']).'</strong>.'.$text;
				}else
					echo '<div>There were no results for your search with query <strong>'.htmlspecialchars($_GET['q']).'</strong>.</div>';
			}else
				echo '<p class="error">Search query was too short!</p>';
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';