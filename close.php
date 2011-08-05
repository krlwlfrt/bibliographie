<?php
$document = ob_get_clean();

if(BIBLIOGRAPHIE_OUTPUT_BODY){
	require dirname(__FILE__).'/_header.php';
	echo $document;
	require dirname(__FILE__).'/_footer.php';
}else
	echo $document;

mysql_close();