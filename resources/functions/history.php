<?php
$bibliographie_history_icons = array (
	'topics' => 'folder',
	'authors' => 'user',
	'generic' => 'error'
);

function bibliographie_history_parse () {
	global $bibliographie_history_path_identifier, $bibliographie_history_icons;

	$parent = $bibliographie_history_path_identifier;
	if(empty($parent))
		$parent = $_GET['from'];
	$step = null;

	echo '<div id="bibliographie_history">';
	echo '<em>Click to toggle!</em>';
	echo '<strong>Navigation history</strong>';
	echo '<div class="history_steps">';

	$i = (int) 1;
	do {
		$step = $_SESSION['bibliographie_history_path'][$parent];
		$parent = $step['parent'];

		if($i != 1 and !empty($step['parent']) and $step['category'] != 'generic')
			$step['description'] = '<a href="'.$step['url'].'">'.$step['description'].'</a>';

		echo '<div>'.bibliographie_icon_get($bibliographie_history_icons[$step['category']]).' '.$step['description'].' ('.$step['method'].')</div>';
		$i++;
	} while(!empty($step['parent']) and $i < 100);

	echo '</div></div>';
}

function bibliographie_history_append_step ($category, $description) {
	global $bibliographie_history_path_identifier;

	$thisStep = array (
		'category' => $category,
		'description' => $description,
		'task' => $_GET['task'],
		'url' => $_SERVER['REQUEST_URI'],
		'parent' => $_GET['from'],
		'method' => $_SERVER['REQUEST_METHOD']
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

function bibliographie_history_rewrite_links ($matches) {
	global $bibliographie_history_path_identifier;

	if(empty($bibliographie_history_path_identifier) or $bibliographie_history_path_identifier == $_GET['from'])
		bibliographie_history_append_step('generic', 'Action not named...');

	if($matches[4] != 'javascript:;'){
		$connector = '&amp;';
		if(mb_strpos($matches[4], '?') === false)
			$connector = '?';

		if(mb_strpos($matches[4], 'from') === false)
			$matches[4] .= $connector.'from='.$bibliographie_history_path_identifier;
	}

	return '<'.$matches[1].$matches[2].$matches[3].'="'.$matches[4].'"';
}