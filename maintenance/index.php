<?php
require '../init.php';

$bibliographie_consistency_checks = array (
	'authors' => array (
		'charsetArtifacts' => 'Authors with charset artifacts',
		'withoutPublications' => 'Authors without publications'
	),

	'links' => array (
		'thatPointNowhere' => 'Links that point nowhere'
	),

	'publications' => array (
		'withoutTopic' => 'Publications without topic assignment',
		'withoutTag' => 'Publications without tag assigment'
	),

	'topics' => array (
		'loosenedSubgraphs' => 'Subgraphs that got loose',
		'doubledNames' => 'Topic names that occurr more than once'
	)
);

switch($_GET['task']){
	case 'mergePersons':
?>

<h2>Maintenance</h2>
<h3>Merge persons</h3>
<div id="bibliographie_maintenance_merge_container">
	<div id="bibliographie_maintenance_merge_into"><?php echo bibliographie_icon_get('flag-green')?> Person to merge into...</div>
	<em style="display: block; font-size: 0.8em; text-align: center"><a href="javascript:;" onclick="bibliographie_maintenance_merge_persons()"><?php echo bibliographie_icon_get('arrow-merge')?> Merge person below into person above!</a></em>
	<div id="bibliographie_maintenance_merge_delete"><?php echo bibliographie_icon_get('flag-red')?> Person to be deleted...</div>
</div>
<div id="bibliographie_maintenance_select_persons">You can either start by searching for authors below or you can let bibliographie search for similiar authors. This process could take a moment, depending on your database.</div>
<h4>Search persons</h4>
<div id="bibliographie_maintenance_search_persons" style="clear: both;">
	<input type="text" id="searchPersons" style="font-size: 1.2em; padding: 5px; width: 40%;" />
	<em>Type in a query or <a href="javascript:;" onclick="bibliographie_maintenance_get_similar_persons()"><?php echo bibliographie_icon_get('find')?> get similar persons!</a></em>
</div>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_maintenance_mark_unsimilar (group, group_id) {
	$.ajax({
		'url': bibliographie_web_root+'/maintenance/ajax.php',
		'data': {
			'task': 'markUnsimilar',
			'group': group
		},
		'dataType': 'json',
		'success': function (json) {
			if(json.status == 'success')
				$('#group_'+group_id).remove();
			else
				alert('An error occured!');
		}
	})
}

function bibliographie_maintenance_merge_persons () {
	if(confirm('Do you really want to merge the 2 selected authors?\n\This step _can not_ be undone!!!')){
		var into = $('#bibliographie_maintenance_merge_into em.person_id');
		var into_group = $('#bibliographie_maintenance_merge_into em.group_id');
		var del = $('#bibliographie_maintenance_merge_delete em.person_id');
		var del_group = $('#bibliographie_maintenance_merge_delete em.group_id');

		if(into.length == 1 && del.length == 1){
			into = parseInt($(into).html());
			into_group = parseInt($(into_group).html());
			del = parseInt($(del).html());
			del_group = parseInt($(del_group).html());
			if(into != del){
				$.ajax({
					'url': bibliographie_web_root+'/maintenance/ajax.php',
					'data': {
						'task': 'mergePersons',
						'into': into,
						'into_group': into_group,
						'delete': del
					},
					'dataType': 'html',
					'success': function (html) {
						$('#bibliographie_maintenance_merge_into').html(html);
						$('#person_'+del_group+'_'+del).remove();
						if($('#group_'+del_group+' ul').children().length == 1)
							$('#group_'+del_group).remove();
						$('#bibliographie_maintenance_merge_delete').html('<em>Please see above for information on merging process!</em>');
					}
				});
			}else
				alert('You have to select two distinct persons!');
		}else
			alert('You have to select two persons!');
	}
}

function bibliographie_maintenance_position_person (person_id, group_id, position) {
	if(position != 'into' && position != 'delete')
		return;

	$.ajax({
		'url': bibliographie_web_root+'/maintenance/ajax.php',
		'data': {
			'task': 'positionPerson',
			'person_id': person_id,
			'group_id': group_id
		},
		'dataType': 'html',
		'success': function (html) {
			$('#bibliographie_maintenance_merge_'+position).html(html);
		}
	});
}

function bibliographie_maintenance_get_similar_persons () {
	$.ajax({
		'url': bibliographie_web_root+'/maintenance/ajax.php',
		'data': {
			'task': 'similarPersons'
		},
		'dataType': 'json',
		'success': function (json) {
			$('#bibliographie_maintenance_select_persons').empty();
			$.each(json, function (group_id, group) {
				$('#bibliographie_maintenance_select_persons').append('<div id="group_'+group_id+'" class="bibliographie_maintenance_person_groups"></div>');
				var groupStr = '';
				var str = '';
				$.each(group, function(person_id, person){
					str += '<li id="person_'+group_id+'_'+person.id+'">\n\
<a href="javascript:;" onclick="bibliographie_maintenance_position_person('+person.id+', '+group_id+', \'into\')"><span class="silk-icon silk-icon-flag-green" title="Put into merge position 1"></span></a>\n\
<a href="javascript:;" onclick="bibliographie_maintenance_position_person('+person.id+', '+group_id+', \'delete\')"><span class="silk-icon silk-icon-flag-red" title="Put into merge position 2"></span></a>\n\
'+person.name+'</li>';
					if(groupStr != '')
						groupStr += ',';
					groupStr += person.id;
				});
				$('#group_'+group_id).append('<em style="float: right; font-size: 0.8em;"><a href="javascript:;" onclick="bibliographie_maintenance_mark_unsimilar(\''+groupStr+'\', '+group_id+');">Mark as unsimilar!</a></em><strong>Group #'+(group_id + 1)+'</strong><ul></ul>');
				$('#group_'+group_id+' ul').append(str);
			});
		}
	});
}

function bibliographie_maintenance_search_persons (q) {
	$.ajax({
		'url': bibliographie_web_root+'/authors/ajax.php',
		'data': {
			'task': 'searchAuthors',
			'q': q
		},
		'dataType': 'json',
		'success': function (json) {
			if(json.length == 0){
				$('#bibliographie_maintenance_select_persons').html('<span class="notice">Sorry, no persons where found!</span>')
			}else{
				$('#bibliographie_maintenance_select_persons').html('<div id="group_0" class="bibliographie_maintenance_person_groups"><ul></ul></div>');
				$.each(json, function(person_id, person){
					$('#group_0 ul').append('<li id="person_0_'+person.id+'">\n\
	<a href="javascript:;" onclick="bibliographie_maintenance_position_person('+person.id+', 0, \'into\')"><span class="silk-icon silk-icon-flag-green" title="Put into merge position 1"></span></a>\n\
	<a href="javascript:;" onclick="bibliographie_maintenance_position_person('+person.id+', 0, \'delete\')"><span class="silk-icon silk-icon-flag-red" title="Put into merge position 2"></span></a>\n\
	'+person.name+'</li>');
				});
			}
		}
	});
}

$(function () {
	$('#searchPersons').on('keyup mouseup change', function (e) {
		delayRequest('bibliographie_maintenance_search_persons', Array($('#searchPersons').val()));
	});
});
	/* ]]> */
</script>
<?php
	break;

	case 'consistencyChecks':
		bibliographie_history_append_step('maintenance', 'Consistency checks');
?>

<h2>Maintenance</h2>
<a href="javascript:;" onclick="bibliographie_maintenance_run_all_checks()" style="float: right;"><?php echo bibliographie_icon_get('tick')?> Run all checks...</a>
<h3>Consistency checks</h3>
<?php
		foreach($bibliographie_consistency_checks as $category => $categoryChecks){
			foreach($categoryChecks as $checkID => $checkTitle){
?>

<h4><?php echo $checkTitle?></h4>
<div id="<?php echo $category.'_'.$checkID?>" style="border: 1px solid #aaa; max-height: 300px; min-height: 50px; overflow-y: scroll;">
	<a href="javascript:;" onclick="bibliographie_maintenance_run_consistency_check('<?php echo $category.'_'.$checkID?>')"><?php echo bibliographie_icon_get('tick')?> Run this check!</a>
</div>
<?php
			}

		}
?>

<script type="text/javascript">
	/* <![CDATA[ */
var bibliographie_maintenance_consistency_checks = <?php echo json_encode($bibliographie_consistency_checks)?>;
	/* ]]> */
</script>
<?php
	break;

	default:
	case 'about':
		$cacheSize = 0;
		foreach(scandir(BIBLIOGRAPHIE_ROOT_PATH.'/cache') as $object){
			if(is_dir(BIBLIOGRAPHIE_ROOT_PATH.'/cache/'.$object))
				continue;

			$cacheSize += filesize(BIBLIOGRAPHIE_ROOT_PATH.'/cache/'.$object);
		}
?>

<h2>About bibliographie</h2>
<p>
	Find the project on <a href="https://github.com/animungo/bibliographie">GitHub</a>.<br />
	Learn more about the author <a href="http://www.animungo.de/">Karl-Philipp Wulfert</a>.
</p>
<h3>Status</h3>
<p>
	You are on database scheme <strong>version <?php echo BIBLIOGRAPHIE_DATABASE_VERSION?></strong>.<br />
	Your database contains the following amount of data...
	<ul>
		<li><?php echo DB::getInstance()->query('SELECT COUNT(*) FROM `'.BIBLIOGRAPHIE_PREFIX.'author`')->fetch(PDO::FETCH_COLUMN, 0)?> authors</li>
		<li><?php echo DB::getInstance()->query('SELECT COUNT(*) FROM `'.BIBLIOGRAPHIE_PREFIX.'notes`')->fetch(PDO::FETCH_COLUMN, 0)?> notes</li>
		<li><?php echo DB::getInstance()->query('SELECT COUNT(*) FROM `'.BIBLIOGRAPHIE_PREFIX.'publication`')->fetch(PDO::FETCH_COLUMN, 0)?> publications</li>
		<li><?php echo DB::getInstance()->query('SELECT COUNT(*) FROM `'.BIBLIOGRAPHIE_PREFIX.'tags`')->fetch(PDO::FETCH_COLUMN, 0)?> tags</li>
		<li><?php echo DB::getInstance()->query('SELECT COUNT(*) FROM `'.BIBLIOGRAPHIE_PREFIX.'topics`')->fetch(PDO::FETCH_COLUMN, 0)?> topics</li>
		<li><?php echo DB::getInstance()->query('SELECT COUNT(*) FROM `'.BIBLIOGRAPHIE_PREFIX.'users`')->fetch(PDO::FETCH_COLUMN, 0)?> users</li>
	</ul>
	The cache currently contains <strong><?php echo round($cacheSize / 1024 /1024, 2)?> MByte</strong> of data.<br />
	You can <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/?task=about&amp;purgeCache=1">purge the cache</a> now.
</p>
<h3>Libraries</h3>
<ul>
	<li>(JS) <a href="http://jquery.com">jQuery</a> &amp; <a href="">jQuery UI</a></li>
	<li>(JS) <a href="https://github.com/malsup/blockui/">jQuery BlockUI</a></li>
	<li>(JS) <a href="http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html">jQuery Highlight</a></li>
	<li>(JS) <a href="https://github.com/loopj/jquery-tokeninput">jQuery TokenInput</a></li>
	<li>(PHP) <a href="http://pear.php.net/package/Structures_BibTex">Structures_Bibtex</a> (heavily modified, to work without PEAR)</li>
	<li>(CSS) Silk Icons Sprite</li>
</ul>
<?php
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';