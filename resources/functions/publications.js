/*****************************************
 ********** PUBLICATION EDITOR ***********
 ****************************************/

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
				$('#'+role).tokenInput('add', {'id': json.author_id, 'name': json.name});
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

function bibliographie_publications_check_title (title, pub_id) {
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
				$('#bibliographie_charmap').hide();
				if($('#similarTitleContainer').is(':visible') == false)
					$('#similarTitleContainer').show();

				var str = '';

				str += '<em style="float: right"><a href="javascript:;" onclick="$(\'#similarTitleContainer .bibliographie_similarity_list_container\').toggle(\'fast\');">Click to toggle!</a></em>';
				str += '<span class="silk-icon silk-icon-exclamation"></span> Found at least <strong>'+json.results.length+'</strong> similar titles';
				str += '<div class="bibliographie_similarity_list_container">';

				$.each(json.results, function (key, value) {
					str += '<div>';
					str += '<a href="'+bibliographie_web_root+'/publications/?task=showPublication&amp;pub_id='+value.pub_id+'"><span class="silk-icon silk-icon-page-white-text"></a>';
					str += ' <a href="'+bibliographie_web_root+'/publications/?task=publicationEditor&amp;pub_id='+value.pub_id+'"><span class="silk-icon silk-icon-page-white-edit"></a>';
					str += ' '+value.title+'</div>';
				});

				str += '</div>';

				$('#similarTitleContainer').html(str);
			}else
				$('#similarTitleContainer').hide();
		}
	})
}

function bibliographie_publications_check_required_fields () {
	var filledEverything = true;
	var notFilled = '';

	$.each($('.bibtexObligatory'), function (ix, element) {
		if($(element).val() == ''){
			filledEverything = false;
			if(notFilled != '')
				notFilled += ', ';
			notFilled += $(element).attr('id');
		}
	});

	if(!filledEverything)
		return confirm('You did not fill these required fields: '+notFilled+'\nDo want to proceed anyway?');

	return true;
}

/*****************************************
 ********** PUBLICATION EXPORT ***********
 ****************************************/

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

/*****************************************
 ********** PUBLICATION DATA FETCHING ****
 ****************************************/

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

function bibliographie_publications_search_author_for_approval (role, person) {
	$.ajax({
		'url': bibliographie_web_root+'/authors/ajax.php',
		'data': {
			'task': 'searchAuthors',
			'q': person.name
		},
		'dataType': 'json',
		'success': function (json) {
			if(json.length > 0){
				$('#bibliographie_checkData_personResult_'+person.htmlID)
					.html('<select id="bibliographie_checkData_personSelect_'+person.htmlID+'" style="width: 45%;"></select>')
					.append('<br /><a href="javascript:;" onclick="bibliographie_publications_check_data_approve_person('+person.entryID+', \''+role+'\', '+person.personID+')"><span class="silk-icon silk-icon-tick"></span> Approve '+role+'</a>')
					.append(' <a href="javascript:;" onclick="bibliographie_publications_check_data_create_person('+person.entryID+', \''+role+'\', '+person.personID+', \''+person.first+'\', \''+person.von+'\', \''+person.last+'\', \''+person.jr+'\')"><span class="silk-icon silk-icon-user-add"></span> Create and approve '+role+'</a>');

				$.each(json, function (dummy, searchResult) {
					$('#bibliographie_checkData_personSelect_'+person.htmlID).append('<option value="'+searchResult.id+'">'+searchResult.name+'</option>');
				});
			}else
				$('#bibliographie_checkData_personResult_'+person.htmlID)
					.html('<strong><span class="silk-icon silk-icon-cross"></span> No person could be found in the database!</strong><br />')
					.append('<a href="javascript:;" onclick="bibliographie_publications_check_data_create_person('+person.entryID+', \''+role+'\', '+person.personID+', \''+person.first+'\', \''+person.von+'\', \''+person.last+'\', \''+person.jr+'\')"><span class="silk-icon silk-icon-user-add"></span> Create person</a>');
		}
	});
}

function bibliographie_publications_check_data_approve_person (entryID, role, personID) {
	$.ajax({
		'url': bibliographie_web_root+'/publications/ajax.php',
		'data': {
			'task': 'checkData',
			'subTask': 'approvePerson',
			'entryID': entryID,
			'role': role,
			'personID': personID,
			'selectedPerson': $('#bibliographie_checkData_personSelect_'+entryID+'_'+role+'_'+personID).val()
		},
		'success': function (html) {
			$('#bibliographie_checkData_personResult_'+entryID+'_'+role+'_'+personID)
				.html(html)
				.append('<br /><a href="javascript:;" onclick="bibliographie_publications_check_data_undo_approval('+entryID+', \''+role+'\', '+personID+')"><span class="silk-icon silk-icon-arrow-undo"></span> Undo approval!</a>');

			bibliographie_checkData_searchPersons[entryID][role][personID].approved = true;
		}
	});
}

function bibliographie_publications_check_data_undo_approval (entryID, role, personID) {
	setLoading('#bibliographie_checkData_personResult_'+entryID+'_'+role+'_'+personID);

	$.ajax({
		'url': bibliographie_web_root+'/publications/ajax.php',
		'data': {
			'task': 'checkData',
			'subTask': 'undoApproval',
			'entryID': entryID,
			'role': role,
			'personID': personID
		}
	});

	bibliographie_checkData_searchPersons[entryID][role][personID].approved = false;
	bibliographie_publications_search_author_for_approval(role, bibliographie_checkData_searchPersons[entryID][role][personID]);
}

function bibliographie_publications_check_data_create_person (entryID, role, personID, first, von, last, jr) {
	$.ajax({
		'url': bibliographie_web_root+'/publications/ajax.php',
		'data': {
			'task': 'checkData',
			'subTask': 'createPerson',
			'entryID': entryID,
			'role': role,
			'personID': personID,
			'first': first,
			'von': von,
			'last': last,
			'jr': jr
		},
		success: function (html) {
			$('#bibliographie_checkData_personResult_'+entryID+'_'+role+'_'+personID)
				.html(html)
				.append('<br /><a href="javascript:;" onclick="bibliographie_publications_check_data_undo_approval('+entryID+', \''+role+'\', '+personID+')"><span class="silk-icon silk-icon-arrow-undo"></span> Undo approval!</a>');
		}
	});
}

function bibliographie_publications_check_data_approve_entry (entryID) {
	$.ajax({
		url: bibliographie_web_root+'/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'approveEntry',
			'entryID': entryID
		},
		dataType: 'json',
		success: function (json) {
			$('#bibliographie_checkData_approvalResult_'+entryID).html(json.text);
			if(json.status == 'success')
				$('#bibliographie_checkData_entry_'+entryID+' .bibliographie_checkData_persons').hide('slow', function () {$(this).remove()});
		}
	});
}

function bibliographie_publications_check_data_approve_all (entryID) {
	$('#bibliographie_checkData_entry_'+entryID).one('ajaxComplete', function () {
		bibliographie_publications_check_data_approve_entry(entryID);
	});

	$.each(bibliographie_checkData_searchPersons[entryID], function (role, persons) {
		$.each(persons, function (personID, person) {
			if($('#bibliographie_checkData_personSelect_'+entryID+'_'+role+'_'+personID).is('select'))
				bibliographie_publications_check_data_approve_person(entryID, role, personID);
			else
				if(person.approved == false)
					bibliographie_publications_check_data_create_person (entryID, role, personID, person.first, person.von, person.last, person.jr);
		})
	})
}