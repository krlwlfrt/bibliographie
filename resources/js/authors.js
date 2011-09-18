function bibliographie_authors_check_name (firstname, surname) {
	$.ajax({
		url: bibliographie_web_root+'/authors/ajax.php',
		data: {
			'task': 'searchAuthors',
			'q': firstname+' '+surname
		},
		dataType: 'json',
		success: function (json) {
			var results = json.length;
			var cutter = 2 * Math.ceil(Math.log(json.length));
			if(cutter < 10)
				cutter = 10;
			if(results > 0){
				$('#similarNameContainer').html('<div style="margin-bottom: 10px;">Showing <strong>'+cutter+' most similar names</strong> ('+results+' search results)</div>').show('fast');
				var i = 0;

				$.each(json, function (dummy, value) {
					var str = '<div';
					if(++i > cutter)
						str = str + ' style="display: none;"';
					str = str + '>';
					str = str + value.name;
					str = str + '</div>';

					$('#similarNameContainer').append(str);
				})
			}else
				$('#similarNameContainer').html('No results!');
			$('#bibliographie_charmap').hide();
		}
	})
}