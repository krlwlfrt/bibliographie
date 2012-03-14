<!DOCTYPE html>
<html lang="de">
	<head>
		<title><?php echo strip_tags($bibliographie_title)?> | bibliographie</title>

		<link rel="shortcut icon" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/favicon.png" type="image/png" />
		<link rel="icon" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/favicon.png" type="image/png" />

		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/silk-icons.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/token-input.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/token-input-facebook.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery-ui.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.jgrowl.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/all.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/charmap.css" />

		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery-ui.js"></script>

		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.tokeninput.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.blockUI.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.fileupload.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.iframe-transport.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.jgrowl.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.highlight.js"></script>

		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/zClip/jquery.zclip.min.js"></script>

		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery-plugins.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/admin.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/attachments.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/authors.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/charmap.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/dodge.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/general.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/maintenance.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/notes.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/publications.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/tags.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/functions/topics.js"></script>
	</head>

	<body id="top">
		<div id="jQueryLoading" style="display: none;"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" width="16" height="11" />&nbsp;Actions pending <span id="jQueryLoadingAmount"></span></div>

		<div id="wrapper">
			<div id="header">
				<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/" method="get" id="search">
					<div>
						<div id="complexSearches">
							<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/?task=authorSets">Search author sets</a>
						</div>

						<input type="hidden" name="task" value="simpleSearch" />
						<input type="text" id="q" name="q" style="width: 50%" placeholder="<?php echo htmlspecialchars($_GET['q'])?>" />
						<button id="searchSubmit"><?php echo bibliographie_icon_get('find')?></button>
					</div>
				</form>

				<h1>
					<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/favicon.png" type="image/png" height="32" width="32" />ibliographie</a>
				</h1>
				<div id="mouse_movement"></div>
			</div>

			<div id="menu">
				<h3><?php echo bibliographie_icon_get('find')?> Browse</h3>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=showGraph"><?php echo bibliographie_icon_get('sitemap')?> Topic graph</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showList"><?php echo bibliographie_icon_get('group')?> Authors</a>
				<br />
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/bookmarks/?task=showBookmarks"><?php echo bibliographie_icon_get('star')?> Bookmarks</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/?task=showCloud"><?php echo bibliographie_icon_get('tag-blue')?> Tags</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/notes/?task=showNotes"><?php echo bibliographie_icon_get('note')?> Notes</a>


				<h3><?php echo bibliographie_icon_get('add')?> Add data</h3>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor"><?php echo bibliographie_icon_get('page-white-add')?> Publication</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=fetchData"><?php echo bibliographie_icon_get('page-white-get')?> Use source</a>
				<br />
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/?task=topicEditor"><?php echo bibliographie_icon_get('folder-add')?> Topic</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=authorEditor"><?php echo bibliographie_icon_get('user-add')?> Author</a>


				<h3><?php echo bibliographie_icon_get('cog')?> Maintenance</h3>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/?task=mergePersons"><?php echo bibliographie_icon_get('arrow-join')?> Merge persons</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/?task=consistencyChecks"><?php echo bibliographie_icon_get('database')?> Consistency checks</a>


				<h3><?php echo bibliographie_icon_get('cog')?> Administration</h3>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/admin/?task=lockedTopics"><?php echo bibliographie_icon_get('lock')?> Lock topics</a>
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/admin/?task=parseLog"><?php echo bibliographie_icon_get('time-linemarker')?> Parse log</a>
			</div>

			<div id="content">
<?php
bibliographie_history_parse();
?>

				<script type="text/javascript">
				/* <![CDATA[ */
/**
 * Transfer script variables to javascript and set other options.
 */
var bibliographie_web_root = '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>';
var bibliographie_search_min_chars = <?php echo BIBLIOGRAPHIE_SEARCH_MIN_CHARS?>;

$(function () {
	$.jGrowl.defaults.position = 'bottom-left';
	$.jGrowl.defaults.life = 10000;

	/**
	 * Set ajax specific options to enable the best experience with ajax functionality.
	 */
	$('#jQueryLoading').bind('ajaxSend', function(e, x, o) {
		// Set cursor to hour glass.
		$('body').css('cursor', 'wait');

		// Show the loading image and set the ui blocking timeout.
		if(bibliographie_loading == 0){
			$(this).show();
			bibliographie_ajax_timeout = setTimeout('bibliographie_ajax_block_ui();', 4000);
		}

		// Increase the loading counter.
		$('#jQueryLoadingAmount').html('('+(++bibliographie_loading)+')');
	}).bind('ajaxComplete', function(){
		// Reset cursor to normal pointer.
		$('body').css('cursor', 'auto');

		// Decrease loading counter.
		$('#jQueryLoadingAmount').html('('+(--bibliographie_loading)+')');

		// If the loading count touches zero hide the loading image an unblock the ui.
		if(bibliographie_loading == 0){
			$(this).hide('slow');
			$.unblockUI();
			clearTimeout(bibliographie_ajax_timeout);
		}
	}).bind('ajaxError', function (e, x, o, err) {
		$.jGrowl('Request to '+o.url+' failed!\n\n'+x.responseText);
	});

	$('body').on('click', function (event) {
		$('.bibliographie_layers_closing_by_click').hide();
	});

	/**
	 * Disable caching and attach the from for history to ajax calls.
	 */
	$.ajaxSetup({
		cache: false,
		data: {
			'from': '<?php echo $bibliographie_history_path_identifier?>'
		}
	});

	/**
	* Attach the from for history to forms with method="get".
	*/
	$.each($('form'), function (ix, element) {
		if($(element).attr('method') == 'get')
			$(element).append('<input type="hidden" name="from" value="<?php echo $bibliographie_history_path_identifier?>" />');
	});

	/*
	 * Toggle the history container on clicking.
	 */
	$('#bibliographie_history').bind('click', function () {
		$('#bibliographie_history .history_steps').toggle('fast');
	})

	/**
	 * Enable expected behaviour by sending the placeholder content if no input was provided...
	 */
	$('#search').bind('submit', function (event) {
		if($('#q').val() == '' && $('#q').attr('placeholder') != '')
			$('#q').val($('#q').attr('placeholder'));
	});

	/**
	 * Add "isDirty?" functionality...
	 */
	window.onbeforeunload = function() {
		if(bibliographie_editor_is_dirty)
			return 'You have unsaved changes that will be lost.\nReturn by pressing "Cancel" or accept by pressing "OK".';
	};
	$('form').submit(function (){
		bibliographie_editor_is_dirty = false;
		setTimeout('bibliographie_editor_is_dirty = true;', bibliographie_request_delay);
	});
	$('form input, form select, form textarea').bind('change', function () {
		bibliographie_editor_is_dirty = true;
	});
});
				/* ]]> */
			</script>