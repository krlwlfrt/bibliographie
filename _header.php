<!DOCTYPE html>
<html lang="de">
	<head>
		<title><?php echo strip_tags($title)?> | bibliographie</title>

		<!-- 3rd party libs -->
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/silk-icons.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/token-input.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/token-input-facebook.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery-ui.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.jgrowl.css" />

		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.jgrowl.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.tokeninput.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.highlight.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery-plugins.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/lib/jquery.jrumble.js"></script>

		<!-- bibliographie stuff -->
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/all.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/css/charmap.css" />
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/authors.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/charmap.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/dodge.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/general.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/maintenance.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/publications.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/search.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/js/topics.js"></script>
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
						<button id="searchSubmit"><span class="silk-icon silk-icon-find"></span></button>
					</div>
				</form>

				<h1>bibliographie</h1>
				<div id="mouse_movement"></div>
			</div>

			<script type="text/javascript">
				/* <![CDATA[ */
var jQueryLoading = 0;

var bibliographie_web_root = '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>';
var bibliographie_search_min_chars = <?php echo BIBLIOGRAPHIE_SEARCH_MIN_CHARS?>;
var bibliographie_request_delay = 500;

$.jGrowl.defaults.position = 'bottom-right';
$.jGrowl.defaults.life = 10000;

$('#jQueryLoading').bind('ajaxSend', function(event, jqXHR, ajaxOptions) {
	$('body').css('cursor', 'wait');
	if(jQueryLoading == 0)
		$(this).show();
	jQueryLoading++;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');
	//$.jGrowl('Sending AJAX query to: <em>'+ajaxOptions.url+'</em>');
}).bind('ajaxComplete', function(){
	$('body').css('cursor', 'auto');
	jQueryLoading--;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');

	if(jQueryLoading == 0)
		$(this).hide('fade');
}).bind('ajaxError', function (event, jqXHR, ajaxSettings, thrownError) {
	alert('Request to '+ajaxSettings.url+' failed!');
});

jQuery.ajaxSetup({
	cache: false
});

/**
 * Enable expected behaviour by sending the placeholder content if no input was provided...
 */
$('#search').bind('submit', function (event) {
	if($('#q').val() == '' && $('#q').attr('placeholder') != '')
		$('#q').val($('#q').attr('placeholder'));
});
				/* ]]> */
			</script>

			<div id="menu">
				<h3>Browse</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=showGraph"><?php echo bibliographie_icon_get('sitemap')?> Topic graph</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=showList"><?php echo bibliographie_icon_get('group')?> Authors</a>
				<br />
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/bookmarks/?task=showBookmarks"><?php echo bibliographie_icon_get('star')?> Bookmarks</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/tags/?task=showCloud"><?php echo bibliographie_icon_get('tag-blue')?> Tags</a>


				<h3>Add data</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/publications/?task=publicationEditor"><?php echo bibliographie_icon_get('page-white-add')?> Publication</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/publications/?task=fetchData"><?php echo bibliographie_icon_get('page-white-get')?> Use source</a>
				<br />
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=topicEditor"><?php echo bibliographie_icon_get('folder-add')?> Topic</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=authorEditor"><?php echo bibliographie_icon_get('user-add')?> Author</a>


				<h3>Maintenance</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=consistencyChecks"><?php echo bibliographie_icon_get('database')?> Consistency checks</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=lockedTopics"><?php echo bibliographie_icon_get('lock')?> Lock topics</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=parseLog"><?php echo bibliographie_icon_get('time-linemarker')?> Parse log</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=ToDo"><?php echo bibliographie_icon_get('page-white-text')?> ToDo</a>
			</div>

			<div id="content">