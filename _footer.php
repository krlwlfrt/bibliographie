
			</div>
			<br style="clear: both" />
			<div id="footer">
				user: <?php echo $_SERVER['PHP_AUTH_USER']?>,
				duration: <?php echo round(microtime(true)-BIBLIOGRAPHIE_SCRIPT_START, 5)?>s,
				<?php echo count($bibliographie_database_queries)?> queries took <?php echo bibliographie_database_total_query_time()?>s
			</div>
<?php
if(count($bibliographie_database_queries) > 0 and BIBLIOGRAPHIE_DATABASE_DEBUG != false){
	echo '<h2>Database queries</h2><table class="dataContainer" style="font-size: 0.7em">';
	foreach($bibliographie_database_queries as $no => $query){
		if((BIBLIOGRAPHIE_DATABASE_DEBUG == 'errors' and !empty($query['error'])) or BIBLIOGRAPHIE_DATABASE_DEBUG == 'all'){
			$query['query'] = '<strong>'.$query['query'].'</strong>';
			if(!empty($query['error']))
				$query['query'] .= '<br /><span class="error">'.$query['error'].'</span>';
			if(!empty($query['callStack']))
				$query['query'] .= '<br />'.$query['callStack'];

			echo '<tr>';
			echo '<td>#'.($no + 1).'</td>';
			echo '<td>'.$query['query'].'</td>';
			echo '<td>'.$query['time'].'</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
}
?>

		</div>
		<div id="dialogContainer"></div>
		<script type="text/javascript">
	/* <![CDATA[ */
var globTimeouts = Array();
var globDelay = 500;

function delayRequest (functionName, params) {
	if(globTimeouts[functionName] != null){
		// clear existing timeout to prevent not wanted queries
		clearTimeout(globTimeouts[functionName]);
		globTimeouts[functionName] = null;
	}

	var call = functionName+'(';
	for(var i = 0; i <= params.length - 1; i++){
		if(i != 0)
			call += ', ';

		call += '"'+params[i]+'"';
	}
	call += ')';

	globTimeouts[functionName] = setTimeout(call, globDelay);
}
	/* ]]> */
		</script>
	</body>
</html>