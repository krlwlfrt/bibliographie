<?php
$document = ob_get_clean();

if(BIBLIOGRAPHIE_OUTPUT_BODY){
	ob_start();

	require dirname(__FILE__).'/_header.php';
	echo $document;
	require dirname(__FILE__).'/_footer.php';

	$document = ob_get_clean();

	$document = preg_replace_callback('~\<(a)(\s)(href)\=\"([^"]*)\"~', 'bibliographie_history_rewrite_links', $document);

	echo $document;
}else
	echo $document;

mysql_close();