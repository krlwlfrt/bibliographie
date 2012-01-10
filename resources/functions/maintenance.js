var bibliographie_maintenance_yet_run_checks = Array();

function bibliographie_maintenance_run_all_checks () {
	$.each(bibliographie_maintenance_consistency_checks, function (category, checks){
		$.each(checks, function (checkID, checkTitle) {
			bibliographie_maintenance_run_consistency_check(category+'_'+checkID);
		});
	});
}

function bibliographie_maintenance_run_consistency_check (id) {
	if($.inArray(id, bibliographie_maintenance_yet_run_checks) == -1){
		bibliographie_maintenance_yet_run_checks.push(id);

		$.ajax({
			url: bibliographie_web_root+'/maintenance/ajax.php',
			data: {
				'task': 'consistencyChecks',
				'consistencyCheckID': id
			},
			success: function (html) {
				$('#'+id).html(html);
			}
		})
	}
}