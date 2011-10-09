<?php
/**
 * Get the document from output buffer.
 */
$document = ob_get_clean();

/**
 * If document shall be output with html body then do it.
 */
if(BIBLIOGRAPHIE_OUTPUT_BODY){
	ob_start();

	require dirname(__FILE__).'/_header.php';
	echo $document;
	require dirname(__FILE__).'/_footer.php';

	$document = ob_get_clean();
}

/**
 * Attach the from=history_identifier to every link in the document and output the document.
 */
echo preg_replace_callback(
	array (
		'~\<(a)(\s)(href)\=\"([^"]*)\"~',
		'~\<(form)(\s)(action)\=\"([^"]*)\"~'
	),
	'bibliographie_history_rewrite_links',
	$document
);

/**
 * Close the mysql connection(s).
 */
mysql_close();
DB::close();