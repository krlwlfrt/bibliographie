<?php
$document = ob_get_clean();
require dirname(__FILE__).'/_header.php';
echo $document;
require dirname(__FILE__).'/_footer.php';
mysql_close();