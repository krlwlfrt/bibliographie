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

function bibliographie_topics_input_tokenized (field, container, prePopulate) {
	$('#'+field).tokenInput(bibliographie_web_root+'/topics/ajax.php?task=searchTopics', {
		'searchDelay': bibliographie_request_delay,
		'minChars': bibliographie_search_min_chars,
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
						str = str + '<a href="javascript:;" onclick="bibliographie_publications_show_subgraph(\''+value.id+'\')" style="float: right;"><span class="silk-icon silk-icon-sitemap"></span> graph</a>';

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