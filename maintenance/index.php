<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
//require dirname(__FILE__).'/maintenance.php';
?>

<h2>Maintenance</h2>
<?php

switch($_GET['task']){
	case 'parseLog':
	default:
?>

<h3>Parse logs</h3>
<?php
		$logContent = scandir(BIBLIOGRAPHIE_ROOT_PATH.'/logs', true);
		if(count($logContent > 2)){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/maintenance/'?>" method="get">
	<div class="unit">
		<input type="hidden" id="task" name="task" value="parseLog" />
		<label for="logFile" class="block">Choose log file</label>
		<select id="logFile" name="logFile" style="width: 45%">
<?php
			foreach($logContent as $logFile){
				if($logFile == '.' or $logFile == '..')
					continue;

				echo '<option value="'.htmlspecialchars($logFile).'">'.htmlspecialchars($logFile).'</option>';
			}
?>

		</select>
	</div>
	<div class="submit">
		<input type="submit" value="show" />
	</div>
</form>
<?php
		}

		if(!empty($_GET['logFile'])){
			if(mb_strpos($_GET['logFile'], '..') === false and mb_strpos($_GET['logFile'], '/') === false and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile'])){
?>

<table class="dataContainer">
	<tr>
		<th style="width: 30%">Time</th>
		<th style="width: 10%">Category</th>
		<th style="width: 10%">Action</th>
		<th style="width: 50%">Data</th>
	</tr>
<?php
				$logContent = file(BIBLIOGRAPHIE_ROOT_PATH.'/logs/'.$_GET['logFile']);
				foreach($logContent as $logRow){
					$logRow = json_decode($logRow);
					echo '<tr>';
					echo '<td>'.$logRow->time.'</td>';
					echo '<td>'.$logRow->category.'</td>';
					echo '<td>'.$logRow->action.'</td>';
					echo '<td><pre>'.print_r(json_decode($logRow->data), true).'</pre></td>';
					echo '</tr>';
				}
?>

</table>
<?php
			}
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';