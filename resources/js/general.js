var bibliographie_request_timeouts = Array();

function delayRequest (functionName, params) {
	if(bibliographie_request_timeouts[functionName] != null){
		// clear existing timeout to prevent not wanted queries
		clearTimeout(bibliographie_request_timeouts[functionName]);
		bibliographie_request_timeouts[functionName] = null;
	}

	var call = functionName+'(';
	for(var i = 0; i <= params.length - 1; i++){
		if(i != 0)
			call += ', ';

		call += '"'+params[i]+'"';
	}
	call += ')';

	bibliographie_request_timeouts[functionName] = setTimeout(call, bibliographie_request_delay);
}