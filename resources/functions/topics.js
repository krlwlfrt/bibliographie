function bibliographie_topics_toggle_visibility_of_subtopics (topic_id, repeat_id) {
	if($('#topic_'+topic_id+'_'+repeat_id+'_subtopics').is(':visible')){
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').hide();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}else{
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').show();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}
}

function bibliographie_topics_toggle_visiblity_of_all (expand) {
	if(expand == true){
		$('.topic_subtopics').show();
		$('.topic span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}else{
		$('.topic_subtopics').hide();
		$('.topic span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}
}

function bibliographie_topics_show_subgraph (topic) {
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

function bibliographie_topics_input_tokenized (field, container, prePopulate) {
	$('#'+field).tokenInput(bibliographie_web_root+'/topics/ajax.php?task=searchTopics', {
		'searchDelay': bibliographie_request_delay,
		'minChars': 1,
		'preventDuplicates': true,
		'theme': 'facebook',
		'prePopulate': prePopulate,
		'noResultsText': 'Results are in the container to the right!',
		'queryParam': 'query',
		onResult: function (results) {
			$('#'+container).html('<div style="margin-bottom: 10px;"><strong>Topics search result</strong></div>');
			if(results.length > 0){
				$.each(results, function (key, value) {
					var selected = false;
					var topicsArray = $('#topics').tokenInput('get')

					$.each(topicsArray, function (selectedKey, selectedValue) {
						if(selectedValue.name == value.name)
							selected = true;
					});

					var str = '';
					str = '<div>';
					if(value.subtopics > 0)
						str = str + '<a href="javascript:;" onclick="bibliographie_topics_show_subgraph(\''+value.id+'\')" style="float: right;"><span class="silk-icon silk-icon-sitemap"></span> graph</a>';

					if(selected){
						str = str += '<span class="silk-icon silk-icon-tick"></span> <em>'+value.name+'</em> is selected.';
					}else{
						str = str + '<a href="javascript:;" onclick="$(\'#topics\').tokenInput(\'add\', {id:\''+value.id+'\',name:\''+value.name+'\'})" style="float: right;"><span class="silk-icon silk-icon-add"></span> add</a>';
						str = str + '<em>'+value.name+'</em>';
					}
					str = str + '</div>';

					$('#'+container).append(str);
				});
				$('#bibliographie_charmap').hide();
			}else
				$('#'+container).append('No results for search!');

			return Array();
		}
	});
}

function bibliographie_topics_check_name (name, topic_id) {
	$.ajax({
		url: bibliographie_web_root+'/topics/ajax.php',
		data: {
			'task': 'checkName',
			'name': name,
			'topic_id': topic_id
		},
		dataType: 'json',
		success: function (json) {
			if(json.results.length > 0){
				$('#bibliographie_charmap').hide();
				if($('#similarNameContainer').is(':visible') == false)
					$('#similarNameContainer').show();

				var str = '';

				str += '<em style="float: right"><a href="javascript:;" onclick="$(\'#similarNameContainer .bibliographie_similarity_list_container\').toggle(\'fast\');">Click to toggle!</a></em>';
				str += '<span class="silk-icon silk-icon-exclamation"></span> Found at least <strong>'+json.results.length+'</strong> similar names';
				str += '<div class="bibliographie_similarity_list_container">';

				$.each(json.results, function (key, value) {
					str += '<div>';
					str += '<a href="'+bibliographie_web_root+'/topics/?task=showTopic&amp;topic_id='+value.topic_id+'"><span class="silk-icon silk-icon-folder" title="Show topic"></a>';
					str += ' <a href="'+bibliographie_web_root+'/topics/?task=topicEditor&amp;topic_id='+value.topic_id+'"><span class="silk-icon silk-icon-folder-edit" title="Edit topic"></a>';
					str += ' <strong>'+value.name+'</strong>';
					if(value.description != '' && value.description != null)
						str += ' <em>'+value.description+'</em>';
					str += '</div>';
				});

				str += '</div>';

				$('#similarNameContainer').html(str);
			}else
				$('#similarNameContainer').hide();
		}
	})
}

function bibliographie_topics_confirm_delete (topic_id) {
	$.ajax({
		'url': bibliographie_web_root+'/topics/ajax.php',
		'data': {
			'task': 'deleteTopicConfirm',
			'topic_id': topic_id
		},
		'success': function (html) {
			$('#dialogContainer').append(html);
			$('#deleteTopicConfirm_'+topic_id).dialog({
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