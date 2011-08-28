
			</div>
			<br style="clear: both" />
			<div id="footer">
				user: <?php echo $_SERVER['PHP_AUTH_USER']?>,
				duration: <?php echo round(microtime(true)-BIBLIOGRAPHIE_SCRIPT_START, 5)?>
			</div>
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