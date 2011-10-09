
			</div>

			<br style="clear: both" />

			<div id="footer">
				<strong>user</strong>: <?php echo $_SERVER['PHP_AUTH_USER']?>,
				<strong>duration</strong>: <?php echo round(microtime(true) - BIBLIOGRAPHIE_SCRIPT_START, 6)?>s<?php
if(!DB::status())
	echo ', <strong>cached content only</strong>';
?>

			</div>
		</div>

		<div id="dialogContainer"></div>
	</body>
</html>