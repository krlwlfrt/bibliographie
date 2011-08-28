<?php
$bibliographie_queries_removals = array (
	's',
	'en',
	'n',
	'er'
);

function bibliographie_search_generate_alternate_queries ($q) {
	global $bibliographie_queries_removals;
	
	$newQueries = array();
	$words = explode(' ', $_GET['q']);
	for($i = 0; $i <= count($words) - 1; $i++){
		foreach($bibliographie_queries_removals as $remove){
			$alternateWords = $words;
			$alternateWords[$i] = preg_replace('~'.$remove.'$~', '', $words[$i]);

			if(implode(' ', $alternateWords) != $_GET['q'])
				$newQueries[] = implode(' ', $alternateWords);
		}
	}

	return $newQueries;
}