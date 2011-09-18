function bibliographie_publications_show_fields (selectedType) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php',
		data: {
			'task': 'getFields',
			'type': selectedType
		},
		dataType: 'json',
		success: function (json) {
			if(json != ''){
				$('div.bibtex').hide();
				/**
				 * Hide all input fields that are representing BibTex fields...
				 */
				$.each($('input.bibtex, textarea.bibtex, select.bibtex'), function (key, element) {
					$(this).hide().removeClass('bibtexObligatory');
					$('label[for="'+$(this).attr('id')+'"]').hide();
				});

				/**
				 * Hide all collapsible fields...
				 */
				$.each($('input.collapsible, textarea.collapsible, select.collapsible'), function (key, element) {
					$(this).hide();
				});

				$('#authorContainer').hide();
				$('#editorContainer').hide();

				/**
				 * Hide all marks for obligatory fields and all links to show optional fields in field labels.
				 */
				$('label span, label a').remove();

				$('#authorOrEditorNotice').hide();

				$.each(json, function(key, value){
					if(value.field == 'author,editor'){
						$('#authorOrEditorNotice').show();
						$('#authorContainer').show();
						$('#editorContainer').show();
					}else{
						if(value.field == 'author')
							$('#authorContainer').show();
						if(value.field == 'editor')
							$('#editorContainer').show();

						$('label[for="'+value.field+'"]').show().parent().show();

						if(value.flag == 0){
							$('label[for="'+value.field+'"]').prepend('<span class="silk-icon silk-icon-asterisk-yellow"></span> ');
							$('#'+value.field).addClass('bibtexObligatory');
						}
					}
				});

				$.each($('input.bibtex, textarea.bibtex, select.bibtex, input.collapsible, textarea.collapsible, select.collapsible'), function (key, element) {
					if($(this).hasClass('bibtexObligatory') || $(this).val() != ''){
						if($(this).val() != '' && $('label[for='+$(this).attr('id')+']').is(':visible') == false)
							$('label[for='+$(this).attr('id')+']').show().prepend('<span style="background: transparent; color: #f00; font-weight: normal"><span class="silk-icon silk-icon-bug"></span> Field is filled, also it\'s not allowed for this publication type!</span> ');
						$(this).show();
					}else
						$('label[for="'+$(this).attr('id')+'"]').prepend('<a href="javascript:;" onclick="$(\'#'+$(this).attr('id')+'\').show(\'fast\', function () {$(this).focus();}); $(this).remove();"><span class="silk-icon silk-icon-arrow-down"></span> unfold</a> ');
				});
			}else
				$.jGrowl('Something bad happened! Could not fetch the field specifications for the publication type.');
		}
	});
}

function bibliographie_publications_create_person (firstname, von, surname, jr, role) {
	if(role != 'author' && role != 'editor')
		return;

	$.ajax({
		url: bibliographie_web_root+'/authors/ajax.php',
		data: {
			'task': 'createPerson',
			'firstname': firstname,
			'von': von,
			'surname': surname,
			'jr': jr
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text);
			if(json.status == 'success')
				$('#'+role).tokenInput('add', {id: json.autor_id, name: json.name});
		}
	})
}

function bibliographie_publications_create_person_form (role) {
	if(role != 'author' && role != 'editor')
		return;

	$.ajax({
		url: bibliographie_web_root+'/authors/ajax.php',
		data: {
			task: 'createPersonForm'
		},
		success: function (html) {
			$('#dialogContainer').append(html);
			$('#createPersonForm').dialog({
				width: 400,
				buttons: {
					'Create & add': function () {
						bibliographie_publications_create_person($('#firstname').val(), $('#von').val(), $('#surname').val(), $('#jr').val(), role);
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				close: function () {
					$(this).remove();
				}
			});
			$('input, textarea').charmap();
		}
	})
}

function bibliographie_publications_create_tag () {
	tagName = window.prompt('Please enter the tag you want to create!');

	if(tagName == null)
		return null;

	if(tagName == '')
		return $.jGrowl('You have to enter something to add a new tag!');

	$.ajax({
		url: bibliographie_web_root+'/tags/ajax.php',
		data: {
			'task': 'createTag',
			'tag': tagName
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text);
			if(json.status == 'success')
				$('#tags').tokenInput('add', {id: json.tag_id, name: json.tag});
		}
	})
}

function bibliographie_publications_show_subgraph (topic) {
	$.ajax({
		url: bibliographie_web_root+'/topics/ajax.php',
		data: {
			'task': 'getSubgraph',
			'topic_id': topic
		},
		success: function (html) {
			$('#dialogContainer').append(html);
			$('#selectFromTopicSubgraph').dialog({
				width: 600,
				modal: true,
				buttons: {
					'Ok': function () {
						$(this).dialog('close');
					}
				},
				close: function () {
					$(this).remove();
				}
			});
		}
	});
}

function bibliographie_publications_check_title (title) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php',
		data: {
			'task': 'checkTitle',
			'title': title,
			'pub_id': pub_id
		},
		dataType: 'json',
		success: function (json) {
			if(json.results.length > 0){
				$('#similarTitleContainer').html('<div style="margin-bottom: 10px;">Showing <strong>'+json.results.length+' most similar titles</strong> ('+json.count+' search results)</div>');
				$.each(json.results, function (key, value) {
					$('#similarTitleContainer')
						.append('<div style="margin-top: 5px;">')
						.append('<a href="'+bibliographie_web_root+'/publications/?task=showPublication&amp;pub_id='+value.pub_id+'"><span class="silk-icon silk-icon-page-white-text"></a>')
						.append(' <a href="'+bibliographie_web_root+'/publications/?task=publicationEditor&amp;pub_id='+value.pub_id+'"><span class="silk-icon silk-icon-page-white-edit"></a>')
						.append(' '+value.title+'</div>');
				});
				if($('#similarTitleContainer').is(':visible') == false)
					$('#similarTitleContainer').show('slow');
			}else
				$('#similarTitleContainer').hide();
		}
	})
}

function bibliographie_publications_fetch_data_proceed (data) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php?task=fetchData_proceed',
		data: data,
		type: 'POST',
		success: function (html) {
			$('#fetchData_container').html(html);
		}
	})
}

function bibliographie_publications_check_data_approve_entry (outerID) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'approveEntry',
			'outerID': outerID
		},
		dataType: 'json',
		success: function (json) {
			$('#checkData_entryResult_'+outerID).html(json.text);
			if(json.status == 'success')
				$('#checkData_entry_'+outerID+' .innerData').remove();
		}
	});
}

function bibliographie_publications_check_data_create_person (role, outerID, innerID, first, von, last, jr) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'createPerson',
			'role': role,
			'outerID': outerID,
			'innerID': innerID,
			'first': first,
			'von': von,
			'last': last,
			'jr': jr
		},
		success: function (html) {
			$('#checkData_'+role+'Result_'+outerID+'_'+innerID).html(html);
		}
	});
}

function bibliographie_publications_check_data_approve_person (role, outerID, innerID) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'approvePerson',
			'role': role,
			'outerID': outerID,
			'innerID': innerID,
			'personID': $('#checkData_'+role+'Select_'+outerID+'_'+innerID).val()
		},
		success: function (html) {
			$('#checkData_'+role+'Result_'+outerID+'_'+innerID).html(html);
		}
	});
}

function bibliographie_publications_export_choose_type (exportList) {
	$.ajax({
		'url': bibliographie_web_root+'/publications/ajax.php',
		'data': {
			'task': 'exportChooseType',
			'exportList': exportList
		},
		success: function (html) {
			$('#dialogContainer').append(html);
			$('#exportChooseType_'+exportList).dialog({
				width: 500,
				modal: true,
				buttons: {
					'Close': function () {
						$(this).dialog('close');
					}
				},
				close: function () {
					$(this).remove();
				}
			});
		}
	})
}