
			</div>
			<br style="clear: both" />
			<div id="footer">
				user: <?php echo $_SERVER['PHP_AUTH_USER']?>,
				duration: <?php echo round(microtime(true)-BIBLIOGRAPHIE_SCRIPT_START, 5)?>
			</div>
		</div>
		<div id="jQueryLoading"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" width="16" height="11" />&nbsp;Actions pending <span id="jQueryLoadingAmount"></span></div>
		<div id="dialogContainer"></div>
		<script type="text/javascript">
	/* <![CDATA[ */
var jQueryLoading = 0;

$('#jQueryLoading').bind('ajaxSend', function(event, jqXHR, ajaxOptions) {
	$('body').css('cursor', 'wait');
	if(jQueryLoading == 0)
		$(this).show();
	jQueryLoading++;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');
	$.jGrowl('Sending AJAX query to: <em>'+ajaxOptions.url+'</em>');
}).bind('ajaxComplete', function(){
	$('body').css('cursor', 'auto');
	jQueryLoading--;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');

	if(jQueryLoading == 0)
		$(this).hide('fade');
});
$('#jQueryLoading').hide('slow');

$.jGrowl.defaults.position = 'bottom-right';
$.jGrowl.defaults.life = 10000;
jQuery.ajaxSetup({
	cache: false
});

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