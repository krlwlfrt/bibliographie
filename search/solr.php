<?php

require '../init.php';

switch ($_GET['task']) {
  case 'publications2json':
    $publications_result = DB::getInstance()->query('SELECT `pub_id`, `user_id`, `year`, `actualyear`, `title`, `bibtex_id`, `report_type`, `pub_type`, `survey`, `mark`, `series`, `volume`, `publisher`, `location`, `issn`, `isbn`, `firstpage`, `lastpage`, `journal`, `booktitle`, `number`, `institution`, `address`, `chapter`, `edition`, `howpublished`, `month`, `organization`, `school`, `note`, `abstract`, `url`, `doi`, `crossref`, `namekey`, `pages` FROM `bibliographie`.`publication` ORDER BY `pub_id`');

    if ($publications_result->rowCount() > 0) {
      $publications = $publications_result->fetchAll(PDO::FETCH_OBJ);

      for ($i = 0; $i < count($publications); $i++) {
        $publications[$i] = bibliographie_search_solr_extend_publication($publications[$i]);
      }

      if (file_put_contents(BIBLIOGRAPHIE_ROOT_PATH . '/cache/solr.publications.json', json_encode($publications), LOCK_EX))
        echo '<h2 class="success">Document has been written</h2>',
        '<p>Document can be found at <code>' . BIBLIOGRAPHIE_ROOT_PATH . '/cache/solr.publications.json</code></p>';
    }
    break;

  default:
    echo '<h2>Solr</h2>',
    '<p>Here you can build initial index files for your solr instance!</p>',
    '<ul>',
    '<li><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/search/solr.php?task=publications2json">Generate index file for publications</a></li>',
    '</ul>';
}

require BIBLIOGRAPHIE_ROOT_PATH . '/close.php';