function bibliographie_attachments_confirm_delete (att_id) {
	$.ajax({
		'url': bibliographie_web_root+'/publications/ajax.php',
		'data': {
			'task': 'deleteAttachmentConfirm',
			'att_id': att_id
		},
		'success': function (html) {
			$('#dialogContainer').append(html);
			$('#deleteAttachmentConfirm_'+att_id).dialog({
				'width': 600,
				'buttons': {
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				'close': function () {
					$(this).remove();
				},
				'modal': true
			})
		}
	})
}