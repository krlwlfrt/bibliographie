
			</div>

			<br style="clear: both" />

			<div id="footer">
				<strong>user</strong>: <?php echo $_SERVER['PHP_AUTH_USER']?>,
				<strong>duration</strong>: <?php echo round(microtime(true) - BIBLIOGRAPHIE_SCRIPT_START, 6)?>s,
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/?purgeCache=1">purge cache</a>,
				<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/maintenance/?task=about">about</a>
			</div>
		</div>

		<div id="dialogContainer"></div>
	</body>
</html>