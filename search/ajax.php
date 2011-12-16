<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

switch($_GET['task']){
	case 'coAuthors':
		$result = array();

		if(is_array($_GET['selectedAuthors']) and count($_GET['selectedAuthors']) > 0){
			$selectedAuthors = array();
			foreach($_GET['selectedAuthors'] as $selectedAuthor)
				$selectedAuthors[] = (int) $selectedAuthor['id'];

			$publications = array();
			foreach($selectedAuthors as $author){
				if(count($publications) > 0)
					$publications = array_intersect($publications, bibliographie_authors_get_publications($author));
				else
					$publications = bibliographie_authors_get_publications($author);
			}

			$coAuthors = DB::getInstance()->prepare('SELECT
	authors.`author_id` AS `id`,
	`surname`,
	`firstname`,
	`is_editor`
FROM `'.BIBLIOGRAPHIE_PREFIX.'author` authors, (
	SELECT
		`author_id`,
		`is_editor`
	FROM
		`'.BIBLIOGRAPHIE_PREFIX.'publicationauthorlink` WHERE FIND_IN_SET(`pub_id`, :publications)
) links
WHERE
	authors.`author_id` = links.`author_id` AND
	links.`is_editor` = "N" AND
	NOT FIND_IN_SET(authors.`author_id`, :authors)
GROUP BY
	`id`
ORDER BY
	`surname`,
	`firstname`');

			$coAuthors->setFetchMode(PDO::FETCH_OBJ);
			$coAuthors->execute(array(
				'publications' => array2csv($publications),
				'authors' => array2csv($selectedAuthors)
			));

			if($coAuthors->rowCount() > 0){
				$result = $coAuthors->fetchAll();
				foreach($result as $key => $row)
					$result[$key] = array(
						'id' => $row->id,
						'name' => bibliographie_authors_parse_data($row->id)
					);
			}
		}

		echo json_encode($result);
	break;

	case 'authorSets':
		if(!empty($_GET['authors'])){
			if(is_csv($_GET['authors'], 'int')){
				$authors = csv2array($_GET['authors'], 'int');

				if(count($authors) > 1){
					$publications = array();
					foreach($authors as $author){
						if(count($publications) > 0)
							$publications = array_intersect($publications, bibliographie_authors_get_publications($author));
						else
							$publications = bibliographie_authors_get_publications($author);
					}

					if(!empty($_GET['query']) and count($publications) > 0){
						$publications = DB::getInstance()->prepare('SELECT *
FROM (
	SELECT
		`pub_id`,
		`title`,
		MATCH(`title`, `abstract`, `note`) AGAINST (:query) AS `relevancy`
	FROM `'.BIBLIOGRAPHIE_PREFIX.'publication`
	WHERE
		FIND_IN_SET(`pub_id`, :publications)
) fullTextSearch
WHERE
	`relevancy` > 0
ORDER BY
	`relevancy` DESC,
	`title`');
						$publications->execute(array(
							'query' => bibliographie_search_expand_query($_GET['query']),
							'publications' => array2csv($publications)
						));

						if($publications->rowCount() > 0)
							$publications = $publications->fetchAll(PDO::FETCH_COLUMN, 0);
						else
							echo '<p class="notice">There were no results for your query string! Showing all publications for this set of authors instead...</p>';
					}

					if(count($publications) > 0){
						echo bibliographie_publications_print_list(
							$publications,
							BIBLIOGRAPHIE_WEB_ROOT.'/search/?task=authorSets&amp;authors='.$_GET['authors']
						);
					}else
						echo '<p class="notice">No publications were found for this set of authors!</p>';

				}else
					echo '<p class="notice">To see a list of publications for '.bibliographie_authors_parse_data($authors[0], array('linkProfile' => true)).' visit his/her profile!</p>';
			}
		}else
			echo '<p class="notice">You have to select at least two authors to search!</p>';
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';