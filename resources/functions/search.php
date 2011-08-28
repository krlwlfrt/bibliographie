<?php
$bibliographie_search_queries_suffixes = array (
	's',
	'en',
	'n',
	'er'
);

$bibliographie_search_queries_umlaut_substitutes = array (
	'ä,ae',
	'ä,a',
	'ö,oe',
	'ö,o',
	'ü,ue',
	'ü,u',
	'ß,sz',
	'ß,ss',
	'ß,s',
	'ph,f',
	'ie,y',
	'ie,i',
	'ks,x',
	'v,w',
	'v,f',
	'll,l',
	'pp,p',
	'nn,n',
	'k,c',
	'ei,ai'
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

function bibliographie_search_expand_query ($q, $options = array(), $iteration = 1) {
	global $bibliographie_search_queries_suffixes, $bibliographie_search_queries_umlaut_substitutes;

	$expandedQuery = (string) '';
	$words = explode(' ', $q);

	foreach($words as $word){
		if($options['suffixes'])
			foreach($bibliographie_search_queries_suffixes as $suffix){
				$removed = preg_replace('~'.$suffix.'$~', '', $word);

				if($removed != $word)
					$expandedQuery .= ' '.$removed;
				else
					$expandedQuery .= ' '.$word.$suffix;

			}

		if($options['plurals'])
			foreach(bibliographie_search_get_plurals() as $singular => $plural){
				if(mb_strtolower($word) == mb_strtolower($singular))
					$expandedQuery .= ' '.$plural;
				if(mb_strtolower($word) == mb_strtolower(plural))
					$expandedQuery .= ' '.$singular;
			}

		if($options['umlauts']){
			foreach($bibliographie_search_queries_umlaut_substitutes as $pair){
				list($umlaut, $equivalent) = explode(',', $pair);

				$substitute = str_replace($umlaut, $equivalent, $word);
				if($substitute != $word)
					$expandedQuery .= ' '.$substitute;

				$substitute = str_replace($equivalent, $umlaut, $word);
				if($substitute != $word)
					$expandedQuery .= ' '.$substitute;
			}
		}
	}

	if($iteration < $options['repeat'])
		$expandedQuery = bibliographie_search_expand_query($expandedQuery, $options, ($iteration + 1));

	/**
	 * Remove duplicates and return expanded query string.
	 */
	return $q.implode(' ', array_values(array_flip(array_flip(explode(' ', $expandedQuery)))));
}