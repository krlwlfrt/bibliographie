<?php
$bibliographie_history_icons = array (
	'authors' => 'user',
	'bookmarks' => 'star',
	'generic' => 'error',
	'maintenance' => 'wrench',
	'notes' => 'note',
	'publications' => 'page-white-text',
	'search' => 'find',
	'tags' => 'tag-blue',
	'topics' => 'folder'
);

/**
 *
 * @global type $bibliographie_history_path_identifier
 * @global array $bibliographie_history_icons
 */
function bibliographie_history_parse () {
	global $bibliographie_history_path_identifier, $bibliographie_history_icons;

	echo '<div id="bibliographie_history">';
	echo '<em>Click to toggle!</em>';
	$str = (string) '<strong>Navigation history</strong>';

	if(empty($bibliographie_history_path_identifier) or $bibliographie_history_path_identifier == $_GET['from']){
		bibliographie_history_append_step('generic', 'Generic task');
		$str .= ' '.bibliographie_icon_get('bug', 'Title of page not yet set');
	}

	$str .= '<div class="history_steps">';

	$parent = $bibliographie_history_path_identifier;
	$step = null;
	$goBack = null;

	$i = (int) 1;
	do {
		$step = $_SESSION['bibliographie_history_path'][$parent];
		$parent = $step['parent'];

		$str .= '<div>'.bibliographie_icon_get($bibliographie_history_icons[$step['category']], $step['category']).' ';
		if($i != 1 and !empty($step['parent']) and $step['category'] != 'generic' and $step['redoable'])
			$str .= '<a href="'.$step['url'].'">'.htmlspecialchars($step['description']).'</a>';
		else
			$str .= htmlspecialchars($step['description']);
		$str .= '</div>';

		if($i == 2 and !empty($step['parent']))
			$goBack = $step;
		if(++$i == 100)
			$str .= '<p class="notice">The history goes further, but the parsing stops here!</p>';
	} while(!empty($step['parent']) and $i < 100);

	if($goBack != null)
		echo '<a href="'.$goBack['url'].'" title="'.htmlspecialchars($goBack['description']).'">'.bibliographie_icon_get('arrow-left', 'Go back').'</a> ';

	echo $str.'</div></div>';
}

/**
 *
 * @global type $bibliographie_history_path_identifier
 * @param type $category
 * @param type $description
 */
function bibliographie_history_append_step ($category, $description, $redoable = true) {
	global $bibliographie_history_path_identifier;

	$thisStep = array (
		'category' => $category,
		'description' => $description,
		'task' => $_GET['task'],
		'url' => $_SERVER['REQUEST_URI'],
		'parent' => $_GET['from'],
		'method' => $_SERVER['REQUEST_METHOD'],
		'redoable' => $redoable
	);

	$history = (string) '';
	$parent = $_GET['from'];
	$lastStep = null;
	$firstStep = null;
	$i = (int) 0;
	do {
		if($lastStep === null)
			$firstStep = $_SESSION['bibliographie_history_path'][$parent];

		$lastStep = $_SESSION['bibliographie_history_path'][$parent];
		$parent = $lastStep['parent'];
		$history .= $parent;
	} while(!empty($lastStep['parent']));

	if($firstStep != $thisStep){
		$bibliographie_history_path_identifier = sha1($history.$thisStep['parent']);
		$_SESSION['bibliographie_history_path'][$bibliographie_history_path_identifier] = $thisStep;
	}
}

/**
 *
 * @global type $bibliographie_history_path_identifier
 * @param type $matches
 * @return type
 */
function bibliographie_history_rewrite_links ($matches) {
	global $bibliographie_history_path_identifier;

	if(empty($bibliographie_history_path_identifier))
		bibliographie_history_append_step('generic', 'Generic AJAX task');

	$link = $matches[4];

	if($link != 'javascript:;'){
		$link = explode('#', $link);

		if(!empty($link[1]))
			$link[1] = '#'.$link[1];

		if(!empty($link[0])){
			$connector = '&amp;';
			if(mb_strpos($link[0], '?') === false)
				$connector = '?';

			if(mb_strpos($link[0], 'from') === false)
				$link = $link[0].$connector.'from='.$bibliographie_history_path_identifier.$link[1];
			else
				$link = $link[0];


		}elseif(empty($link[0]) and !empty($link[1])){
			$link = $link[1];


		}else{
			$link = '?from='.$bibliographie_history_path_identifier;
		}
	}

	return '<'.$matches[1].$matches[2].$matches[3].'="'.$link.'"';
}