function bibliographie_notes_confirm_delete (note_id) {
	$.ajax({
		'url': bibliographie_web_root+'/notes/ajax.php',
		'data': {
			'task': 'deleteNoteConfirm',
			'note_id': note_id
		},
		'success': function (html) {
			$('#dialogContainer').append(html);
			$('#deleteNoteConfirm_'+note_id).dialog({
				'width': 400,
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