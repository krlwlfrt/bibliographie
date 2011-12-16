function bibliographie_tags_input_tokenized (field, prePopulate) {
	$('#'+field).tokenInput(bibliographie_web_root+'/tags/ajax.php?task=searchTags', {
		searchDelay: bibliographie_request_delay,
		minChars: 1,
		preventDuplicates: true,
		theme: 'facebook',
		prePopulate: prePopulate,
		onResult: function (results) {
			alert(results.length);
			$('#tags_tagNotExisting').empty();
			$('#bibliographie_charmap').hide();

			if(results.length == 0)
				$('#tags_tagNotExisting').html('Tag <strong>'+$('#token-input-tags').val()+'</strong> is not existing. <a href="javascript:;" onclick="bibliographie_tags_create_tag(\''+$('#token-input-tags').val()+'\');">Create it here!</a>');

			return results;
		}
	});
}

function bibliographie_tags_create_tag (tagName) {
	if(tagName == null)
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
			if(json.status == 'success'){
				$('#tags').tokenInput('add', {id: json.tag_id, name: json.tag});
				$('#tags_tagNotExisting').empty().hide();
			}
		}
	})

	return true;
}