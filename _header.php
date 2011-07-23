<!DOCTYPE html>
<html lang="de">
	<head>
		<title><?php echo strip_tags($title)?> | bibliographie</title>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/all.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/silk-icons.css" />
		<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
	</head>


	<body id="top">
		<div id="wrapper">
			<div id="header"><h1>bibliographie</h1></div>

			<div id="menu">
				<h3>Topics</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=showGraph"><?php echo bibliographie_get_icon('sitemap')?> Show topic graph</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=createTopic"><?php echo bibliographie_get_icon('folder-add')?> Create topic</a>

				<h3>Authors</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=showList"><?php echo bibliographie_get_icon('group')?> Show authors</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=createAuthor"><?php echo bibliographie_get_icon('user-add')?> Create author</a>

				<h3>Maintenance</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=parseLog"><?php echo bibliographie_get_icon('time-linemarker')?> Parse log</a>
			</div>

			<div id="content">