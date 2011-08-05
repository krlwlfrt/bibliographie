<?php
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'searchTopic':
		if(in_array($_GET['id'], array('searchTopicOne', 'searchTopicTwo'))){
			$topics = mysql_query("SELECT * FROM `a2topics` WHERE `name` LIKE '%".mysql_real_escape_string(stripslashes($_GET['value']))."%' ORDER BY `name`");
			if(mysql_num_rows($topics) > 0 and !empty($_GET['value'])){
?>

<label for="select_<?php echo $_GET['id']?>" class="block"><?php echo mysql_num_rows($topics)?> Result(s)</label>
<select id="select_<?php echo $_GET['id']?>" name="select_<?php echo $_GET['id']?>" style="width: 100%">
	<option value="0">Please choose...</option>
<?php
			while($topic = mysql_fetch_object($topics)){
				if($topic->topic_id == 1){
					$topic->name .= ' (Symbolic head of graph!)';
				}
?>

	<option value="<?php echo $topic->topic_id?>"><?php echo htmlspecialchars($topic->name)?></option>
<?php
			}
?>

</select>
<?php
			}else
				echo '<p class="error">Sorry, your search did not give any results!</p>';
		}
	break;
	default:
		echo 'WORKING';
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';