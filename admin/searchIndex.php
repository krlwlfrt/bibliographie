<?php

require '../init.php';

switch ($_GET['task']) {
  case 'publicationIndex':
    echo '<h2>Make publication index</h2>';
    $publications_result = DB::getInstance()->query('SELECT
  `pub_id`,
  `user_id`,
  `year`,
  `actualyear`,
  `title`,
  `bibtex_id`,
  `report_type`,
  `pub_type`,
  `survey`,
  `mark`,
  `series`,
  `volume`,
  `publisher`,
  `location`,
  `issn`,
  `isbn`,
  `firstpage`,
  `lastpage`,
  `journal`,
  `booktitle`,
  `number`,
  `institution`,
  `address`,
  `chapter`,
  `edition`,
  `howpublished`,
  `month`,
  `organization`,
  `school`,
  `note`,
  `abstract`,
  `url`,
  `doi`,
  `crossref`,
  `namekey`,
  `pages`
FROM `' . BIBLIOGRAPHIE_PREFIX . 'publication` ORDER BY `pub_id`');

    if ($publications_result->rowCount() > 0) {
      $publications = $publications_result->fetchAll(PDO::FETCH_OBJ);

      $publication = new stdClass();
      foreach ($publications as $i => $publication) {
        $publication = bibliographie_search_extend_publication($publication);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:9200/bibliographie/publications/' . $publication->pub_id . '?pretty=true');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($publication));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        echo '<h3 class="notice">Publication #' . $publication->pub_id . ': ' . bibliographie_publications_parse_title($publication->pub_id) . '</h3>';
        echo '<pre>' . $result . '</pre>';

        //close connection
        curl_close($ch);
      }
    } else {
      echo '<h3 class="error">No publications in database!</h3>';
    }
    break;

  default:
    echo '<h2>Solr</h2>',
    '<p>Here you can make initial indexes!</p>',
    '<ul>',
    '<li><a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/admin/searchIndex.php?task=publicationIndex">Make index for publications</a></li>',
    '</ul>';
}

require BIBLIOGRAPHIE_ROOT_PATH . '/close.php';