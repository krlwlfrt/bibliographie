<?php
$document = ob_get_clean();

$replacements = array (
	'~\<(a)(\s)(href)\=\"([^"]*)\"~',
	'~\<(form)(\s)(action)\=\"([^"]*)\"~'
);

if(BIBLIOGRAPHIE_OUTPUT_BODY){
	ob_start();

	require dirname(__FILE__).'/_header.php';
	echo $document;
	require dirname(__FILE__).'/_footer.php';

	$document = ob_get_clean();
}

echo preg_replace_callback($replacements, 'bibliographie_history_rewrite_links', $document);

mysql_close();
$db = null;