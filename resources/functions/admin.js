function bibliographie_admin_unlock_topic (topic_id) {
	$.ajax({
		url: bibliographie_web_root+'/admin/ajax.php',
		data: {
			'task': 'unlockTopic',
			'topic_id': topic_id
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text);
			if(json.status == 'success')
				$('#topic_'+topic_id).remove();
		}
	})
}