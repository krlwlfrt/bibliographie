<?php
require '../init.php';

$bibliographie_search_categories = array(
  'topics',
  'authors',
  'publications',
  'tags',
  'journals',
  'books'
);
?>

<h2>Search</h2>
<?php
$bibliographie_title = 'Search';
switch ($_GET['task']) {
  case 'authorSets':
    ?>

    <p class="notice">Select two or more authors and optionally provide a query string to search!</p>
    <div class="unit">
      <div id="coAuthorsContainer" class="bibliographie_similarity_container" style="float: right; max-height: 200px; overflow-y: scroll; width: 40%;"></div>
      <label for="authors" class="block">Authors</label>
      <input type="text" id="authors" name="authors" />

      <label for="query" class="block" style="clear: both;">Query</label>
      <input type="text" id="query" name="query" style="width: 100%" value="<?php echo htmlspecialchars($_GET['query']) ?>" />
    </div>
    <div id="bibliographie_search_results"></div>
    <script type="text/javascript">
      /* <![CDATA[ */
      $(function() {
        $('#authors').tokenInput(bibliographie_web_root + '/authors/ajax.php?task=searchAuthors', {
          'searchDelay': bibliographie_request_delay,
          'minChars': bibliographie_search_min_chars,
          'preventDuplicates': true,
          'prePopulate': null,
          'onAdd': function(item) {
            bibliographie_authors_get_co_authors('authors', 'coAuthorsContainer');
            bibliographie_authors_get_publications_for_authors_set($('#authors').val(), $('#query').val());
          },
          'onDelete': function(item) {
            if ($('#authors').tokenInput('get').length > 0) {
              bibliographie_authors_get_co_authors('authors', 'coAuthorsContainer');
              bibliographie_authors_get_publications_for_authors_set($('#authors').val(), $('#query').val());
            } else {
              $('#coAuthorsContainer').hide();
              $('#bibliographie_search_results').empty();
            }
          }
        });

        $('#query').bind('keyup change', function() {
          delayRequest('bibliographie_authors_get_publications_for_authors_set', Array($('#authors').val(), $('#query').val()));
        });

        $('#content input').charmap();
      });
      /* ]]> */
    </script>
    <?php
    bibliographie_charmap_print_charmap();
    break;

  case 'showPublications':
    $publications = bibliographie_publications_get_cached_list($_GET['publicationsList']);
    if (is_array($publications) and count($publications) > 0) {
      echo bibliographie_publications_print_list(
        $publications, BIBLIOGRAPHIE_WEB_ROOT . '/search/?task=showPublications&amp;publicationsList=' . htmlspecialchars($_GET['publicationsList'])
      );
    }
    break;

  case 'simpleSearch':
    if (mb_strlen($_GET['q']) >= 1) {
      $timer = microtime(true);

      $searchResults = array(
        'publications' => array(),
        'topics' => array(),
        'tags' => array(),
        'authors' => array(),
        'bookmarks' => array(),
        'notes' => array(),
        'journals' => array(),
        'books' => array()
      );
      $expandedQuery = bibliographie_search_expand_query($_GET['q']);

      if (empty($_GET['category']) or $_GET['category'] == 'publications')
        $searchResults['publications'] = bibliographie_publications_search_publications($_GET['q'], $expandedQuery);

      if (empty($_GET['category']) or $_GET['category'] == 'topics')
        $searchResults['topics'] = bibliographie_topics_search_topics($_GET['q'], $expandedQuery);

      if (empty($_GET['category']) or $_GET['category'] == 'tags')
        $searchResults['tags'] = bibliographie_tags_search_tags($_GET['q'], $expandedQuery);

      if (empty($_GET['category']) or $_GET['category'] == 'authors')
        $searchResults['authors'] = bibliographie_authors_search_authors($_GET['q']);

      if (empty($_GET['category']) or $_GET['category'] == 'notes') {
        $searchResults['notes'] = bibliographie_notes_search_notes($_GET['q'], $expandedQuery);
        if (count(bibliographie_notes_get_publications_with_notes()) > 0) {
          $publications = array_intersect(bibliographie_publications_search_publications($_GET['q'], $expandedQuery), bibliographie_notes_get_publications_with_notes());
          if (count($publications) > 0)
            foreach ($publications as $publication)
              foreach (bibliographie_publications_get_notes($publication) as $note)
                $searchResults['notes'][] = $note;
        }
      }

      if (empty($_GET['category']) or $_GET['category'] == 'bookmarks')
        $searchResults['bookmarks'] = array_values(array_intersect($searchResults['publications'], bibliographie_bookmarks_get_bookmarks()));

      if (empty($_GET['category']) or $_GET['category'] == 'journals')
        $searchResults['journals'] = bibliographie_publications_search_journals($_GET['q'], $expandedQuery);

      if (empty($_GET['category']) or $_GET['category'] == 'books')
        $searchResults['books'] = bibliographie_publications_search_books($_GET['q'], $expandedQuery);

      $timer = microtime(true) - $timer;

      $toc = (string) '';
      $str = (string) '';
      $limit = (int) -1;

      if (!in_array($_GET['category'], array('authors', 'books', 'journals', 'notes', 'publications', 'tags', 'topics'))) {
        bibliographie_history_append_step('search', 'Search for "' . htmlspecialchars($_GET['q']) . '"');
        $limit = 10;
      } else {
        bibliographie_history_append_step('search', 'Search in ' . $_GET['category'] . ' for "' . htmlspecialchars($_GET['q']) . '"');
        $limit = -1;
      }


      foreach ($searchResults as $category => $results) {
        if (count($results) > 0) {
          $str .= '<h3 id="bibliographie_search_results_' . $category . '">' . ucfirst($category) . '</h3>';
          $toc .= '<li><a href="#bibliographie_search_results_' . $category . '">' . ucfirst($category) . '</a> (' . count($results) . ' results)</li>';

          if ($category == 'notes')
            $str .= '<a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/publications/?task=batchOperations&amp;list=' . bibliographie_publications_cache_list(bibliographie_notes_get_publications_from_notes($searchResults['notes']), true) . '" style="float: right">' . bibliographie_icon_get('page-white-stack') . ' Batch publications</a>';

          if (in_array($category, array('authors', 'books', 'journals', 'notes', 'tags', 'topics'))) {
            $i = (int) 0;
            $options = array('linkProfile' => true);

            if (count($results) > $limit and $limit != -1)
              $str .= 'Found <strong>' . count($results) . ' ' . $category . '</strong> of which the first ' . $limit . ' are shown. <a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/search/?task=simpleSearch&amp;category=' . $category . '&amp;q=' . htmlspecialchars($_GET['q']) . '">Show all found ' . $category . '!</a>';
            else
              $str .= 'Found <strong>' . count($results) . ' ' . $category . '</strong> shown by relevancy.';

            foreach ($results as $row) {
              if (++$i == $limit and $limit != -1)
                break;

              if ($category == 'authors') {
                $row = bibliographie_authors_get_data($row->author_id);
                $str .= '<div class="bibliographie_search_result">' . bibliographie_authors_parse_data($row->author_id, $options);
                if (!empty($row->email))
                  $str .= ' <em style="font-size: 0.8em;">' . htmlspecialchars($row->email) . '</em>';
                if (!empty($row->url))
                  $str .= '<br /><em style="font-size: 0.8em;"><a href="' . htmlspecialchars($row->url) . '">' . htmlspecialchars($row->url) . '</a></em>';
                $str .= '</div>';
              }elseif ($category == 'books')
                $str .= '<div class="bibliographie_search_result">
	<a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/publications/?task=showContainer&amp;type=book&container=' . htmlspecialchars($row->booktitle) . '">' . $row->booktitle . '</a>, ' . $row->count . ' article(s)
</div>';

              elseif ($category == 'journals')
                $str .= '<div class="bibliographie_search_result">
	<a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/publications/?task=showContainer&amp;type=journal&container=' . htmlspecialchars($row->journal) . '">' . $row->journal . '</a>, ' . $row->count . ' publication(s)
</div>';

              elseif ($category == 'notes')
                $str .= bibliographie_notes_print_note($row->note_id);


              elseif ($category == 'tags')
                $str .= '<div class="bibliographie_search_result">' . bibliographie_tags_parse_tag($row->tag_id, $options) . '</div>';


              elseif ($category == 'topics') {
                $row = bibliographie_topics_get_data($row->topic_id);
                $str .= '<div class="bibliographie_search_result">' . bibliographie_topics_parse_name($row->topic_id, $options);
                if (!empty($row->description))
                  $str .= '<br /><em style="0.8em">' . htmlspecialchars($row->description) . '</em>';
                $str .= '</div>';
              }
            }
          }elseif ($category == 'publications' or $category == 'bookmarks') {
            $options = array();

            if ($_GET['category'] == 'publications' or $_GET['category'] == 'bookmarks') {
              $options['orderBy'] = 'year';
            } else {
              if (count($results) > $limit and $limit != -1) {
                $str .= 'Found <strong>' . count($results) . ' ' . $category . '</strong> of which the first ' . $limit . ' are shown. <a href="' . BIBLIOGRAPHIE_WEB_ROOT . '/search/?task=simpleSearch&amp;category=publications&amp;q=' . htmlspecialchars($_GET['q']) . '">Show all found ' . $category . '!</a>';
                $results = array_slice($results, 0, $limit);
              }
              else
                $str .= 'Found <strong>' . count($results) . ' ' . $category . '</strong> shown by relevancy.';

              $options['onlyPublications'] = true;
            }

            $str .= bibliographie_publications_print_list($results, BIBLIOGRAPHIE_WEB_ROOT . '/search/?task=simpleSearch&amp;category=publications&amp;q=' . htmlspecialchars($_GET['q']), $options);
          }
        }
      }

      if (!empty($toc)) {
        echo '<em style="float: right; font-size: 0.8em;">' . round($timer, 6) . 's, ' . count(explode(' ', $expandedQuery)) . ' words</em>';
        echo '<ul>' . $toc . '</ul>';
      }
      if (!empty($str)) {
        echo '<div id="bibliographie_search_results">' . $str . '</div>';
        ?>

        <script type="text/javascript">
          /* <![CDATA[ */
          $(function() {
            $('#bibliographie_search_results').highlight(<?php echo json_encode(explode(' ', $expandedQuery)) ?>);
          });
          /* ]]> */
        </script>
        <?php
      }
    }
    break;
}

require BIBLIOGRAPHIE_ROOT_PATH . '/close.php';