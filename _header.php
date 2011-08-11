<!DOCTYPE html>
<html lang="de">
	<head>
		<title><?php echo strip_tags($title)?> | bibliographie</title>

		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/all.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/silk-icons.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/jquery.jgrowl.css" />

		<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/javascript/jquery.jgrowl.js"></script>
	</head>


	<body id="top">
		<div id="wrapper">
			<div id="header"><h1>bibliographie</h1></div>

			<div id="menu">
				<h3>Topics</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=showGraph"><?php echo bibliographie_icon_get('sitemap')?> Show topic graph</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=createTopic"><?php echo bibliographie_icon_get('folder-add')?> Create topic</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=createRelation"><?php echo bibliographie_icon_get('table-relationship')?> Create relation</a>

				<h3>Authors</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=showList"><?php echo bibliographie_icon_get('group')?> Show authors</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=createAuthor"><?php echo bibliographie_icon_get('user-add')?> Create author</a>

				<h3>Publications</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/publications/?task=createPublication"><?php echo bibliographie_icon_get('page-white-add')?> Create publication</a>

				<h3>Bookmarks</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/bookmarks/?task=showBookmarks"><?php echo bibliographie_icon_get('tag-blue')?> Show bookmarks</a>

				<h3>Maintenance</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=ToDo"><?php echo bibliographie_icon_get('page-white-text')?> ToDo</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=lockedTopics"><?php echo bibliographie_icon_get('lock')?> Locked topics</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=parseLog"><?php echo bibliographie_icon_get('time-linemarker')?> Parse log</a>
			</div>

			<div id="content">