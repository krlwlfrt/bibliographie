<?php

$bibliographie_search_queries_suffixes = array(
  's',
  'es',
  'ies',
  'ed',
  'en',
  'ing',
  'er',
  'est',
  'n\'t',
  'ism',
  'ist',
  'ful',
  'able',
  'ation',
  'ness',
  'ment',
  'ify',
  'fy',
  'ity',
  'noun',
  'ly',
  'ise',
  'ize',
  's',
  'n',
  'er',
  'ic',
  'ication',
  'al',
  'in'
);

$bibliographie_search_queries_umlaut_substitutes = array(
  'ä,ae',
  'ä,a',
  'ö,oe',
  'ö,o',
  'ü,ue',
  'ü,u',
  'ß,sz',
  'ß,ss',
  'ß,s',
  'f,ph',
  'y,ie',
  'i,ie',
  'x,ks',
  'v,w',
  'v,f',
  'k,c',
  'ei,ai'
);

/**
 *
 * @return type
 */
function bibliographie_search_get_plurals() {
  static $sap = null;

  if (BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH . '/cache/singulars_and_plurals.json'))
    return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH . '/cache/singulars_and_plurals.json'), true);

  $return = array();
  if (!($sap instanceof PDOStatement)) {
    $sap = DB::getInstance()->prepare('SELECT `singular`, `plural` FROM `' . BIBLIOGRAPHIE_PREFIX . 'singulars_and_plurals`');
    $sap->setFetchMode(PDO::FETCH_OBJ);
  }

  if ($sap->rowCount() > 0) {
    $result = $sap->fetchAll();

    foreach ($result as $pair)
      $return[$pair->singular] = $pair->plural;

    if (BIBLIOGRAPHIE_CACHING) {
      $cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH . '/cache/singulars_and_plurals.json', 'w+');
      fwrite($cacheFile, json_encode($return));
      fclose($cacheFile);
    }
  }

  return $return;
}

/**
 * Uses several rules to expand search queries.
 * @global array $bibliographie_search_queries_suffixes
 * @global array $bibliographie_search_queries_umlaut_substitutes
 * @param string $q
 * @param array $_options
 * @param int $iteration
 * @return string
 */
function bibliographie_search_expand_query($q, $_options = array(), $iteration = 1) {
  global $bibliographie_search_queries_suffixes, $bibliographie_search_queries_umlaut_substitutes;

  if (empty($q))
    return '';

  $expandedQuery = (string) '';
  $words = preg_split('~[\s\-\,]~', $q);

  $options = array(
    'suffixes' => true,
    'plurals' => true,
    'umlauts' => true
  );

  foreach ($options as $key => $value)
    if (!empty($_options[$key]) and $value != $_options[$key])
      $options[$key] = $_options[$key];

  foreach ($words as $word) {
    if ($iteration == 1) {
      /**
       * Change every char with its successor and add this as new word.
       * We do this to iron out minor typos.
       */
      for ($i = 0; $i < mb_strlen($word) - 1; $i++)
        $expandedQuery .= ' ' . mb_substr($word, 0, $i) . mb_substr($word, $i + 1, 1) . mb_substr($word, $i, 1) . mb_substr($word, $i + 2);
    } elseif ($iteration == 2) {
      /**
       * Remove and add doubled chars.
       */
      for ($i = 0; $i < mb_strlen($word) - 1; $i++)
        if (mb_substr($word, $i, 1) == mb_substr($word, $i + 1, 1))
          $expandedQuery .= ' ' . mb_substr($word, 0, $i) . mb_substr($word, $i, 1) . mb_substr($word, $i + 2);
        else
          $expandedQuery .= ' ' . mb_substr($word, 0, $i) . mb_substr($word, $i, 1) . mb_substr($word, $i);
    }elseif ($iteration == 3) {
      if ($options['suffixes']) {
        /**
         * Try to remove known suffixes. If a known suffix is removed attach all other known suffixes.
         */
        foreach (array_unique($bibliographie_search_queries_suffixes) as $suffix) {
          $rootStem = preg_replace('~' . $suffix . '$~', '', $word);

          if ($rootStem != $word) {
            $expandedQuery .= ' ' . $rootStem;
            foreach (array_unique($bibliographie_search_queries_suffixes) as $innerSuffix)
              $expandedQuery .= ' ' . $rootStem . $innerSuffix;
          }
          else
            $expandedQuery .= ' ' . $word . $suffix;
        }
      }

      /**
       * If we find an irregular plural of a singular add the singular or if we find the singular add the irregular plural.
       * Irregular plural means that its not (only) generated via suffixes.
       */
      if ($options['plurals'] and count(bibliographie_search_get_plurals()) > 0) {
        foreach (bibliographie_search_get_plurals() as $singular => $plural) {
          if (mb_strtolower($word) == mb_strtolower($singular))
            $expandedQuery .= ' ' . $plural;
          if (mb_strtolower($word) == mb_strtolower(plural))
            $expandedQuery .= ' ' . $singular;
        }
      }


      /**
       * Replace umlauts with their periphrasis and some chars with likely sounding chars.
       */
      if ($options['umlauts']) {
        $umlaut = (string) '';
        $equivalent = (string) '';
        foreach ($bibliographie_search_queries_umlaut_substitutes as $pair) {
          list($umlaut, $equivalent) = explode(',', $pair);

          $substitute = str_replace($umlaut, $equivalent, $word);
          if ($substitute != $word)
            $expandedQuery .= ' ' . $substitute;

          $substitute = str_replace($equivalent, $umlaut, $word);
          if ($substitute != $word)
            $expandedQuery .= ' ' . $substitute;
        }
      }
    }
  }

  /**
   * Call the function recursively 2 times after initial call to get all the rules.
   */
  if ($iteration < 3)
    $expandedQuery = bibliographie_search_expand_query($q . $expandedQuery, $options, ($iteration + 1));

  /**
   * Remove duplicates and return expanded query string.
   */
  return $q . ' ' . implode(' ', array_unique(explode(' ', $q . ' ' . $expandedQuery)));
}

/**
 * Extend a publication object with data for solr index.
 * @param stdClass $publication The publication you want to extend.
 * @return mixed Extended publication or something else.
 */
function bibliographie_search_extend_publication($publication) {
  if (is_object($publication)) {
    $publication->authors = array();
    $publication->tags = array();
    $publication->topics = array();

    $authors = bibliographie_publications_get_authors($publication->pub_id);
    if (count($authors) > 0) {
      for ($i = 0; $i < count($authors); $i++) {
        $authors[$i] = bibliographie_authors_parse_data($authors[$i]);
      }
      $publication->authors = $authors;
    }

    $tags = bibliographie_publications_get_tags($publication->pub_id);
    if (count($tags) > 0) {
      for ($i = 0; $i < count($tags); $i++) {
        $tag = bibliographie_tags_get_data($tags[$i]);
        $tags[$i] = $tag->tag;
      }
      $publication->tags = $tags;
    }

    $topics = bibliographie_publications_get_topics($publication->pub_id);
    if (count($topics) > 0) {
      for ($i = 0; $i < count($topics); $i++) {
        $topic = bibliographie_topics_get_data($topics[$i]);
        $topics[$i] = $topic->name;
      }
      $publication->topics = $topics;
    }
  }

  return $publication;
}