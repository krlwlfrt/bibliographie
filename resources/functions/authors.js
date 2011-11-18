function bibliographie_authors_check_name (firstname, surname, author_id) {
	$.ajax({
		url: bibliographie_web_root+'/authors/ajax.php',
		data: {
			'task': 'searchAuthors',
			'q': firstname+' '+surname,
			'author_id': author_id
		},
		dataType: 'json',
		success: function (json) {
			var results = json.length;
			var cutter = 2 * Math.ceil(Math.log(json.length));
			if(cutter < 10)
				cutter = 10;

			if(results > 0){
				$('#similarNameContainer').html('<div style="margin-bottom: 10px;">Showing <strong>'+results+' similar names</strong>.</div>').show('fast');
				$.each(json, function (dummy, value) {
					var str = '';
					str += '<div>';
					str += '<a href="'+bibliographie_web_root+'/authors/?task=authorEditor&amp;author_id='+value.id+'" style="float: right;"><span class="silk-icon silk-icon-pencil"></span></a>';
					str += '<a href="'+bibliographie_web_root+'/authors/?task=showAuthor&amp;author_id='+value.id+'"><span class="silk-icon silk-icon-user"></span> '+value.name+'</a>';
					str += '</div>'

					$('#similarNameContainer').append(str);
				});

			}else
				$('#similarNameContainer').html('No results!');

			$('#bibliographie_charmap').hide();
		}
	})
}

function bibliographie_authors_input_tokenized (field, prePopulate) {
	$('#'+field).tokenInput(bibliographie_web_root+'/authors/ajax.php?task=searchAuthors', {
		'searchDelay': bibliographie_request_delay,
		'minChars': bibliographie_search_min_chars,
		'preventDuplicates': true,
		'prePopulate': prePopulate
	});
}

function bibliographie_authors_get_publications_for_authors_set (authors, query) {
	setLoading('#bibliographie_search_results');
	$.ajax({
		'url': bibliographie_web_root+'/search/ajax.php',
		'data': {
			'task': 'authorSets',
			'authors': authors,
			'query': query
		},
		success: function (html) {
			$('#bibliographie_search_results').html(html);
		}
	})
}

function bibliographie_authors_get_co_authors (field, container) {
	setLoading('#'+container);
	$.ajax({
		'url': bibliographie_web_root+'/search/ajax.php',
		'data': {
			'task': 'coAuthors',
			'selectedAuthors': $('#'+field).tokenInput('get')
		},
		'dataType': 'json',
		'success': function (json) {
			$('#bibliographie_charmap').hide();
			if(json.length > 0){
				$('#'+container).html('<div style="margin-bottom: 5px;">Found <strong>'+json.length+' co-authors</strong> for this set of authors.</div>').show();
				$.each(json, function (dummy, value) {
					$('#'+container).append('<div><a href="javascript:;" onclick="$(\'#authors\').tokenInput(\'add\', {\'id\': '+value.id+', \'name\': \''+value.name+'\'});" style="float: right;"><span class="silk-icon silk-icon-add"></span></a> '+value.name+'</div>');
				});
			}else{
				$('#'+container).html('<em>No more co-authors were found!</em>').show();
			}
		}
	})
}

function bibliographie_authors_confirm_delete (author_id) {
	$.ajax({
		'url': bibliographie_web_root+'/authors/ajax.php',
		'data': {
			'task': 'deletePersonConfirm',
			'author_id': author_id
		},
		'success': function (html) {
			$('#dialogContainer').append(html);
			$('#deletePersonConfirm_'+author_id).dialog({
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