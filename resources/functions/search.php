<?php
$bibliographie_search_queries_suffixes = array (
	's',
	'en',
	'n',
	'er'
);

function bibliographie_search_get_plurals () {
	if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/singulars_and_plurals.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/singulars_and_plurals.json'), true);

	$return = array();
	$result = mysql_query("SELECT * FROM `singulars_and_plurals`");
	if(mysql_num_rows($result) > 0){
		while($pair = mysql_fetch_object($result))
			$return[$pair->singular] = $pair->plural;

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/singulars_and_plurals.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}

		return $return;
	}

	$return = array();
}

function bibliographie_search_expand_query ($q, $expansionStrength = 1) {
	global $bibliographie_search_queries_suffixes;

	$expandedQuery = (string) '';
	$words = explode(' ', $_GET['q']);

	foreach($words as $word){
		if($expansionStrength == 1)
			foreach($bibliographie_search_queries_suffixes as $suffix){
				$removed = preg_replace('~'.$suffix.'$~', '', $word);

				if($removed != $word)
					$expandedQuery .= ' '.$removed;
				else
					$expandedQuery .= ' '.$word.$suffix;

			}

		if($expansionStrength == 2)
			foreach(bibliographie_search_get_plurals() as $singular => $plural){
				if(mb_strtolower($word) == mb_strtolower($singular))
					$expandedQuery .= ' '.$plural;
				if(mb_strtolower($word) == mb_strtolower(plural))
					$expandedQuery .= ' '.$singular;
			}
	}

	return $expandedQuery;
}